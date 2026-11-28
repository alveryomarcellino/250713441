<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

$n_homestay  = hitung_baris("SELECT id_homestay FROM homestay");
$n_pengguna  = hitung_baris("SELECT id_user FROM users WHERE role='user'");
$n_reservasi = hitung_baris("SELECT id_reservasi FROM reservasi");
$n_menunggu  = hitung_baris("SELECT id_reservasi FROM reservasi WHERE status='menunggu'");
$n_verifikasi = hitung_baris("SELECT id_pembayaran FROM pembayaran WHERE status_verifikasi='menunggu'");

$query = "SELECT r.*, u.nama_lengkap, h.nama_homestay
          FROM reservasi r
          JOIN users u ON u.id_user = r.id_user
          JOIN homestay h ON h.id_homestay = r.id_homestay
          ORDER BY r.created_at DESC LIMIT 5";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Dashboard - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/admin/dashboard.css?v=<?php echo time(); ?>" />
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

    <h2>Dashboard admin</h2>
    <div class="kartu-angka">
        <div class="angka"><strong><?php echo e($n_homestay); ?></strong>Homestay</div>
        <div class="angka"><strong><?php echo e($n_pengguna); ?></strong>Pengguna terdaftar</div>
        <div class="angka"><strong><?php echo e($n_reservasi); ?></strong>Total reservasi</div>
        <div class="angka"><strong><?php echo e($n_menunggu); ?></strong>Menunggu konfirmasi</div>
        <div class="angka"><strong><?php echo e($n_verifikasi); ?></strong>Bukti bayar perlu diperiksa</div>
    </div>

    <h3>Reservasi terbaru</h3>
    <?php if (mysqli_num_rows($result) == 0) { ?>
        <p>Belum ada reservasi.</p>
    <?php } else { ?>
        <div class="tabel-bungkus">
            <table>
                <tr>
                    <th>Pemesan</th>
                    <th>Homestay</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                <?php while ($r = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo e($r["nama_lengkap"]); ?></td>
                        <td><?php echo e($r["nama_homestay"]); ?></td>
                        <td><?php echo tanggal_indo($r["tgl_checkin"]); ?></td>
                        <td><?php echo tanggal_indo($r["tgl_checkout"]); ?></td>
                        <td><?php echo rupiah($r["total_harga"]); ?></td>
                        <td><span class="status status-<?php echo e($r["status"]); ?>"><?php echo e(label_status($r["status"])); ?></span></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
    <p><a class="tombol" href="reservasi.php">Kelola semua reservasi</a></p>
</body>