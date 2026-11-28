<?php
// READ reservasi milik pengguna yang sedang login.
require_once __DIR__ . "/../../includes/init.php";
wajib_login();

global $koneksi;
$id_user = (int)$_SESSION["id_user"];
$query = "SELECT r.*, h.nama_homestay, h.kota, p.status_verifikasi AS status_bayar,
                 ul.rating AS rating_ulasan
          FROM reservasi r JOIN homestay h ON h.id_homestay = r.id_homestay
          LEFT JOIN pembayaran p ON p.id_reservasi = r.id_reservasi
          LEFT JOIN ulasan ul ON ul.id_reservasi = r.id_reservasi
          WHERE r.id_user='$id_user' ORDER BY r.created_at DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Reservasi Saya - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/reservasi.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/nav.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="../../index.php">
                    <img src="../../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../../index.php">Beranda</a></li>
                <li><a href="../../index.php">HomeStay</a></li>
                <?php if (sudah_login() && $_SESSION['role'] == 'user') { ?>
                    <li><a href="reservasi.php">Reservasi Saya</a></li>
                <?php } ?>
                <?php if (sudah_login() && $_SESSION['role'] == 'admin') { ?>
                    <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/homestay.php">Homestay</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reservasi.php">Reservasi</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/ulasan.php">Ulasan</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/pengguna.php">Pengguna</a></li>
                <?php } ?>

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
                <li><a class="logout-link" href="../../auth/logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>

    <?php tampil_pesan(); ?>
    <h2>Reservasi saya</h2>
    <?php if (mysqli_num_rows($result) == 0) { ?>
        <p>Belum ada reservasi. <a href="<?php echo BASE_URL; ?>/index.php">Cari homestay</a> dan pesan sekarang.</p>
    <?php } else { ?>
        <p class="kecil">Klik tombol <strong>Lihat bukti</strong> untuk menampilkan kode reservasi.</p>
        <div class="tabel-bungkus">
            <table>
                <tr>
                    <th>Kode &amp; bukti</th>
                    <th>Homestay</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($r = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td>
                            <strong><?php echo e($r["kode_reservasi"]); ?></strong><br />
                            <a class="tombol tombol-kecil" href="bukti.php?id=<?php echo e($r["id_reservasi"]); ?>">Lihat bukti</a>
                        </td>
                        <td><?php echo e($r["nama_homestay"]); ?><br /><span class="kecil"><?php echo e($r["kota"]); ?></span></td>
                        <td>
                            <?php echo tanggal_indo($r["tgl_checkin"]); ?> s/d <?php echo tanggal_indo($r["tgl_checkout"]); ?><br />
                            <span class="kecil"><?php echo e($r["jumlah_tamu"]); ?> tamu</span>
                        </td>
                        <td><?php echo rupiah($r["total_harga"]); ?></td>
                        <td><span class="status status-<?php echo e($r["status"]); ?>"><?php echo e(label_status($r["status"])); ?></span><br />
                            <span class="kecil"><?php echo e(label_bayar($r["status_bayar"])); ?></span>
                        </td>
                        <td>
                            <?php if ($r["status"] == "menunggu" && ($r["status_bayar"] == null || $r["status_bayar"] == "ditolak")) { ?>
                                <a class="tombol tombol-kecil" href="bayar.php?id=<?php echo e($r["id_reservasi"]); ?>"><?php echo $r["status_bayar"] == null ? "Bayar" : "Upload ulang"; ?></a>
                                <a class="tombol tombol-kecil tombol-garis" href="editReservasi.php?id=<?php echo e($r["id_reservasi"]); ?>">Ubah</a>
                            <?php } ?>
                            <?php if ($r["status"] != "dikonfirmasi") { ?>
                                <form class="inline" method="post" action="hapusReservasi.php" onsubmit="return confirm('Hapus reservasi ini?');">
                                    <input type="hidden" name="id" value="<?php echo e($r["id_reservasi"]); ?>" />
                                    <button type="submit" class="tombol-kecil tombol-bahaya">Hapus</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</body>