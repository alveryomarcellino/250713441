<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

$result = mysqli_query($koneksi, "SELECT * FROM users ORDER BY created_at DESC");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Admin User - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/admin/pengguna.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="index.php">
                    <img src="../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../index.php">HomeStay</a></li>
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

    <h2>Data pengguna</h2>
    <div class="tabel-bungkus">
        <table>
            <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>Peran</th>
                <th>Terdaftar</th>
            </tr>
            <?php while ($u = mysqli_fetch_assoc($result)) {
                $foto = url_foto("profil", $u["foto_profil"]); ?>
                <tr>
                    <td><?php if ($foto) { ?>
                            <img class="avatar_user" src="<?php echo e($foto); ?>" alt="" />
                        <?php } else { ?>
                            <span class="avatar avatar-huruf"><?php echo e(strtoupper($u['nama_lengkap'][0])); ?></span>
                        <?php } ?>
                    </td>
                    <td><?php echo e($u["nama_lengkap"]); ?></td>
                    <td><?php echo e($u["username"]); ?></td>
                    <td><?php echo e($u["email"]); ?></td>
                    <td><?php echo e($u["no_hp"]); ?></td>
                    <td><?php echo e($u["role"]); ?></td>
                    <td><?php echo tanggal_indo($u["created_at"]); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>