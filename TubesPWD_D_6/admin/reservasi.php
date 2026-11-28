<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;
$kode = isset($_GET["kode"]) ? trim($_GET["kode"]) : "";
$where = "";
if ($kode != "") {
    $where = "WHERE r.kode_reservasi LIKE '%$kode%'";
}

$query = "SELECT r.*, u.nama_lengkap, u.no_hp, h.nama_homestay,
                 p.status_verifikasi AS status_bayar, p.metode, p.tgl_bayar, p.catatan_admin
          FROM reservasi r
          JOIN users u ON u.id_user = r.id_user
          JOIN homestay h ON h.id_homestay = r.id_homestay
          LEFT JOIN pembayaran p ON p.id_reservasi = r.id_reservasi
          $where
          ORDER BY r.created_at DESC";
$result = mysqli_query($koneksi, $query);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Data Reservasi - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/admin/reservasi.css?v=<?php echo time(); ?>" />
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


    <h2>Kelola reservasi</h2>
    <form method="get" action="reservasi.php" class="form-cari" style="justify-content:flex-start; margin-bottom:16px;">
        <div>
            <label for="kode">Cari kode reservasi</label>
            <input type="text" id="kode" name="kode" value="<?php echo e($kode); ?>" />
        </div>
        <button type="submit">Cari</button>
    </form>
    <?php if (mysqli_num_rows($result) == 0) { ?>
        <p>Tidak ada reservasi<?php echo $kode != "" ? " dengan kode tersebut" : ""; ?>.</p>
    <?php } else { ?>
        <div class="tabel-bungkus">
            <table>
                <tr>
                    <th>Kode</th>
                    <th>Pemesan</th>
                    <th>Homestay</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($r = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><strong><?php echo e($r["kode_reservasi"]); ?></strong></td>
                        <td><?php echo e($r["nama_lengkap"]); ?><br /><span class="kecil"><?php echo e($r["no_hp"]); ?></span></td>
                        <td><?php echo e($r["nama_homestay"]); ?></td>
                        <td>
                            <?php echo tanggal_indo($r["tgl_checkin"]); ?> s/d <?php echo tanggal_indo($r["tgl_checkout"]); ?><br />
                            <span class="kecil"><?php echo e($r["jumlah_tamu"]); ?> tamu</span>
                        </td>
                        <td><?php echo rupiah($r["total_harga"]); ?></td>
                        <td><span class="status status-<?php echo e($r["status"]); ?>"><?php echo e(label_status($r["status"])); ?></span></td>
                        <td>
                            <?php echo e(label_bayar($r["status_bayar"])); ?>
                            <?php if ($r["status_bayar"] != null) { ?>
                                <br /><span class="kecil"><?php echo e($r["metode"]); ?>, <?php echo tanggal_indo($r["tgl_bayar"]); ?></span>
                                <br /><a href="<?php echo BASE_URL; ?>/user/reservasi/bukti_bayar_gambar.php?id=<?php echo e($r["id_reservasi"]); ?>" target="_blank">Lihat bukti bayar</a>
                            <?php } ?>
                            <?php if ($r["status_bayar"] == "ditolak" && $r["catatan_admin"]) { ?>
                                <br /><span class="kecil">Alasan: <?php echo e($r["catatan_admin"]); ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <a class="tombol tombol-kecil tombol-garis" href="<?php echo BASE_URL; ?>/user/reservasi/bukti.php?id=<?php echo e($r["id_reservasi"]); ?>">Bukti</a>
                            <?php if ($r["status"] == "menunggu") { ?>
                                <?php if ($r["status_bayar"] == "menunggu") { ?>
                                    <form class="inline" method="post" action="pembayaran_verifikasi.php">
                                        <input type="hidden" name="id" value="<?php echo e($r["id_reservasi"]); ?>" />
                                        <input type="hidden" name="aksi" value="terima" />
                                        <button type="submit" class="tombol-kecil">Terima bayar</button>
                                    </form>
                                    <form class="inline" method="post" action="pembayaran_verifikasi.php">
                                        <input type="hidden" name="id" value="<?php echo e($r["id_reservasi"]); ?>" />
                                        <input type="hidden" name="aksi" value="tolak" />
                                        <input type="text" name="catatan" placeholder="Alasan penolakan" size="14" />
                                        <button type="submit" class="tombol-kecil tombol-bahaya">Tolak bayar</button>
                                    </form>
                                <?php } ?>
                                <form class="inline" method="post" action="reservasi_status.php" onsubmit="return confirm('Batalkan reservasi ini?');">
                                    <input type="hidden" name="id" value="<?php echo e($r["id_reservasi"]); ?>" />
                                    <input type="hidden" name="status" value="dibatalkan" />
                                    <button type="submit" class="tombol-kecil tombol-bahaya">Batalkan</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</body>