<?php
require __DIR__ . "/../includes/init.php";

global $koneksi;
$error = array();
$nama = $username = $email = $no_hp = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST["nama_lengkap"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $no_hp = trim($_POST["no_hp"]);
    $password = $_POST["password"];
    $konfirmasi = $_POST["konfirmasi"];

    if (empty($nama)) {
        $error[] = "Nama lengkap wajib diisi.";
    }

    if (!preg_match("/^[A-Za-z0-9_]{4,20}$/", $username)) {
        $error[] = "Username 4-20 karakter: huruf, angka, atau garis bawah.";
    }

    if (empty($email)) {
        $error[] = "Email wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Format email tidak valid.";
    }

    if ($no_hp != "" && !preg_match("/^[0-9+]{8,15}$/", $no_hp)) {
        $error[] = "Nomor HP tidak valid.";
    }

    if (strlen($password) < 6) {
        $error[] = "Password minimal 6 karakter.";
    }

    if ($password != $konfirmasi) {
        $error[] = "Konfirmasi password tidak sama.";
    }

    if (count($error) == 0) {
        $result = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='$username'");
        if (mysqli_num_rows($result) > 0) {
            $error[] = "Username sudah dipakai.";
        }

        $result = mysqli_query($koneksi, "SELECT id_user FROM users WHERE email='$email'");
        if (mysqli_num_rows($result) > 0) {
            $error[] = "Email sudah dipakai.";
        }
    }

    if (count($error) == 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users SET nama_lengkap='$nama', username='$username',
                  email='$email', password_hash='$hash', no_hp='$no_hp', role='user'";
        $result = mysqli_query($koneksi, $query);

        if (mysqli_affected_rows($koneksi) == 1) {
            header("Location: login.php"); // direct ke login page
            exit;
        } else {
            $error[] = "Registrasi gagal, silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Register - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/auth.css?v=<?php echo time(); ?>" />
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
                <li><a href="register.php">Masuk</a></li>
            </ul>
        </div>
    </div>

    <div class="form-kotak">
        <h2>Daftar akun baru</h2>
        <?php foreach ($error as $pesan) { ?>
            <div class="pesan pesan-error"><?php echo e($pesan); ?></div>
        <?php } ?>
        <form id="form-register" method="post" action="register.php">
            <label for="nama_lengkap">Nama lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo e($nama); ?>" />

            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo e($username); ?>" />


            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" />


            <label for="no_hp">Nomor HP (opsional)</label>
            <input type="text" id="no_hp" name="no_hp" value="<?php echo e($no_hp); ?>" />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" />

            <label for="konfirmasi">Ulangi password</label>
            <input type="password" id="konfirmasi" name="konfirmasi" />


            <div class="aksi">
                <button type="submit" id="tombol-daftar">Daftar</button>
                <a class="tombol tombol-garis" href="login.php">Sudah punya akun</a>
            </div>
        </form>
    </div>
</body>