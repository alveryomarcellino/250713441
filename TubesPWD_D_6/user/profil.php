<?php
require __DIR__ . "/../includes/init.php";

wajib_login();

global $koneksi;
$id = $_SESSION["id_user"];
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id'");
$user = mysqli_fetch_assoc($result);
$error = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama  = trim($_POST["nama_lengkap"]);
    $email = trim($_POST["email"]);
    $no_hp = trim($_POST["no_hp"]);
    $password_baru = $_POST["password_baru"];
    $konfirmasi = $_POST["konfirmasi"];

    if ($nama == "") {
        $error[] = "Nama lengkap wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Format email tidak valid.";
    }
    if ($no_hp != "" && !preg_match("/^[0-9+]{8,15}$/", $no_hp)) {
        $error[] = "Nomor HP tidak valid.";
    }

    $result = mysqli_query($koneksi, "SELECT id_user FROM users WHERE email='$email' AND id_user <> '$id'");
    if (mysqli_num_rows($result) > 0) {
        $error[] = "Email sudah dipakai pengguna lain.";
    }

    if ($password_baru != "") {
        if (strlen($password_baru) < 6) {
            $error[] = "Password baru minimal 6 karakter.";
        }
        if ($password_baru != $konfirmasi) {
            $error[] = "Konfirmasi password tidak sama.";
        }
    }

    $file_foto = isset($_FILES["foto"]) ? $_FILES["foto"] : null;
    $hasil = simpan_foto($file_foto, "profil", "user" . $id);

    $foto_baru = $hasil[0];
    $err_foto = $hasil[1];

    if ($err_foto) {
        $error[] = $err_foto;
    }

    if (count($error) == 0) {
        $query = "UPDATE users SET nama_lengkap='$nama', email='$email', no_hp='$no_hp'
                  WHERE id_user='$id'";
        mysqli_query($koneksi, $query);

        if ($password_baru != "") {
            $hash = password_hash($password_baru, PASSWORD_DEFAULT);
            mysqli_query($koneksi, "UPDATE users SET password_hash='$hash' WHERE id_user='$id'");
        }
        if ($foto_baru) {
            hapus_foto("profil", $user["foto_profil"]);
            mysqli_query($koneksi, "UPDATE users SET foto_profil='$foto_baru' WHERE id_user='$id'");
            $_SESSION["foto"] = $foto_baru;
        }
        $_SESSION["nama"] = $nama;
        header("Location: profil.php");
        exit;
    }

    $user["nama_lengkap"] = $nama;
    $user["email"] = $email;
    $user["no_hp"] = $no_hp;
}

$foto = url_foto("profil", $user["foto_profil"]);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Profil - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/profil.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="../index.php">
                    <img src="../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../index.php">HomeStay</a></li>
                <?php if (sudah_login() && $_SESSION['role'] == 'user') { ?>
                    <li><a href="reservasi/reservasi.php">Reservasi Saya</a></li>
                <?php } ?>
                <?php if (sudah_login() && $_SESSION['role'] == 'admin') { ?>
                    <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/homestay.php">Homestay</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reservasi.php">Reservasi</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/ulasan.php">Ulasan</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/pengguna.php">Pengguna</a></li>
                <?php } ?>

                <!-- kalo udah login -->
                <?php if (sudah_login()) { ?>
                    <li>
                        <a class="profil-link" href="<?php echo BASE_URL; ?>/user/profil.php">
                            <?php if (!empty($_SESSION['foto'])) { ?>
                                <img class="avatar" src="<?php echo url_foto("profil", $_SESSION['foto']) ?>" alt="Foto profil" />
                            <?php } else { ?>
                                <span class="avatar avatar-huruf"><?php echo e(strtoupper($_SESSION['nama'][0])); ?></span>
                            <?php } ?>
                            <span><?php echo e($_SESSION['nama']); ?></span>
                        </a>
                    </li>
                    <li><a class="logout-link" href="../auth/logout.php">Keluar</a></li>
                <?php } else { ?>
                    <li><a href="auth/register.php">Masuk</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="form-kotak form-lebar">
        <h2>Profil saya</h2>
        <?php foreach ($error as $pesan) { ?>
            <div class="pesan pesan-error"><?php echo e($pesan); ?></div>
        <?php } ?>
        <?php if ($foto) { ?>
            <img class="foto-profil-besar" src="<?php echo e($foto); ?>" alt="Foto profil" />
        <?php } else { ?>
            <div class="foto-profil-besar"><?php echo e(strtoupper($user["nama_lengkap"][0])); ?></div>
        <?php } ?>
        <p class="kecil">Username: <strong><?php echo e($user["username"]); ?></strong> (tidak dapat diubah) | Peran: <?php echo e($user["role"]); ?></p>

        <form method="post" action="profil.php" enctype="multipart/form-data">
            <label for="nama_lengkap">Nama lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo e($user["nama_lengkap"]); ?>" />

            <div class="baris-2">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($user["email"]); ?>" />
                </div>
                <div>
                    <label for="no_hp">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="<?php echo e($user["no_hp"]); ?>" />
                </div>
            </div>

            <label for="foto">Foto profil (JPG/PNG, maks. 2 MB)</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" />

            <div class="baris-2">
                <div>
                    <label for="password_baru">Password baru (kosongkan jika tidak diganti)</label>
                    <input type="password" id="password_baru" name="password_baru" />
                </div>
                <div>
                    <label for="konfirmasi">Ulangi password baru</label>
                    <input type="password" id="konfirmasi" name="konfirmasi" />
                </div>
            </div>

            <div class="aksi"><button type="submit">Simpan profil</button></div>
        </form>
    </div>
</body>