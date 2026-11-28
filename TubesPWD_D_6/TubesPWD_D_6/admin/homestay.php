<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

$result = mysqli_query($koneksi, "SELECT * FROM homestay ORDER BY id_homestay DESC");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Admin Homestay - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/admin/homestay.css?v=<?php echo time(); ?>" />
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

    <h2>Kelola homestay</h2>
    <p><a class="tombol" href="homestay_form.php">Tambah homestay</a></p>
    <div class="tabel-bungkus">
        <table>
            <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Kota</th>
                <th>Kapasitas</th>
                <th>Kamar</th>
                <th>Harga/malam</th>
                <th>Koordinat</th>
                <th>Aksi</th>
            </tr>
            <?php while ($h = mysqli_fetch_array($result, MYSQLI_ASSOC)): $foto = url_foto("homestay", $h["foto_utama"]); ?>
                <tr>
                    <td><?php if ($foto): ?><img src="<?php echo e($foto); ?>" alt="" width="60" /><?php endif; ?></td>
                    <td><?php echo e($h["nama_homestay"]); ?></td>
                    <td><?php echo e($h["kota"]); ?></td>
                    <td><?php echo e($h["kapasitas_min"]); ?>-<?php echo e($h["kapasitas_maks"]); ?></td>
                    <td><?php echo e($h["jumlah_kamar"]); ?></td>
                    <td><?php echo rupiah($h["harga_per_malam"]); ?></td>
                    <td class="kecil"><?php echo e($h["latitude"]); ?>, <?php echo e($h["longitude"]); ?></td>
                    <td>
                        <a class="tombol tombol-kecil tombol-garis" href="homestay_form.php?id=<?php echo e($h["id_homestay"]); ?>">Ubah</a>
                        <form class="inline" method="post" action="homestay_hapus.php" onsubmit="return confirm('Hapus homestay ini?');">
                            <input type="hidden" name="id" value="<?php echo e($h["id_homestay"]); ?>" />
                            <button type="submit" class="tombol-kecil tombol-bahaya">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>