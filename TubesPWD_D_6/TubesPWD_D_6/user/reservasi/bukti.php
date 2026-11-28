<?php
// READ satu reservasi milik pengguna: halaman "Pesanan Anda" (bukti & kode booking)
require __DIR__ . "/../../includes/init.php";
wajib_login();

global $koneksi;
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$id_user = (int)$_SESSION["id_user"];
$is_admin = $_SESSION["role"] === "admin";

// Hanya pemilik reservasi yang boleh melihat buktinya
if ($is_admin) {
    $stmt = mysqli_prepare($koneksi, "SELECT r.*, h.nama_homestay, h.kota, h.foto_utama, h.harga_per_malam
        FROM reservasi r JOIN homestay h ON h.id_homestay = r.id_homestay
        WHERE r.id_reservasi = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    $stmt = mysqli_prepare($koneksi, "SELECT r.*, h.nama_homestay, h.kota, h.foto_utama, h.harga_per_malam
        FROM reservasi r JOIN homestay h ON h.id_homestay = r.id_homestay
        WHERE r.id_reservasi = ? AND r.id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
}
mysqli_stmt_execute($stmt);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$r) {
    set_pesan("Reservasi tidak ditemukan.", "error");
    header("Location: reservasi.php");
    exit;
}

// rincian harga: total_harga sudah termasuk biaya layanan, jadi biaya = total - (malam x harga)
$malam = hitung_malam($r["tgl_checkin"], $r["tgl_checkout"]);
$subtotal = $malam * $r["harga_per_malam"];
$biaya = max(0, $r["total_harga"] - $subtotal);
$foto = url_foto("homestay", $r["foto_utama"]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pesanan Anda - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/bukti.css?v=<?php echo time(); ?>" />
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
                        <span class="sembunyi-hp"><?php echo e($_SESSION['nama']); ?></span>
                    </a>
                </li>
                <li><a class="logout-link" href="../../auth/logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>

    <div class="bukti">

        <!-- RINGKASAN PESANAN -->
        <div class="bukti-bagian bukti-abu">
            <h1 class="bukti-judul">Pesanan Anda</h1>
            <p class="bukti-sub">Terima kasih telah memilih BoBook. Siapkan kode pemesanan berikut saat check-in.</p>

            <h2 class="bukti-nama"><?php echo e($r["nama_homestay"]); ?></h2>
            <p class="bukti-lokasi">
                <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
                <?php echo e($r["kota"]); ?>
            </p>

            <hr class="garis-putus" />

            <div class="bukti-tanggal">
                <div>
                    <p class="label">Check-In</p>
                    <p class="nilai"><?php echo e(tanggal_panjang($r["tgl_checkin"])); ?></p>
                    <p class="jam">14:00 WIB</p>
                </div>
                <div>
                    <p class="label">Check-Out</p>
                    <p class="nilai"><?php echo e(tanggal_panjang($r["tgl_checkout"])); ?></p>
                    <p class="jam">12:00 WIB</p>
                </div>
            </div>

            <hr class="garis-putus" />

            <div class="bukti-baris">
                <span class="label">Jumlah Tamu</span>
                <span class="nilai"><?php echo e($r["jumlah_tamu"]); ?> Orang</span>
            </div>
            <div class="bukti-baris">
                <span class="label">Durasi Menginap</span>
                <span class="nilai"><?php echo e($malam); ?> Malam</span>
            </div>
        </div>

        <!-- KODE BOOKING -->
        <div class="bukti-bagian bukti-abu bukti-kode" style="margin-top: 16px;">
            <p class="label">Kode Booking</p>
            <p class="kode"><?php echo e($r["kode_reservasi"]); ?></p>
            <p class="petunjuk">Tunjukkan kode ini kepada staff hotel saat check-in.</p>
            <span class="bukti-status status-<?php echo e($r["status"]); ?>"><?php echo e(label_status($r["status"])); ?></span>
        </div>

        <!-- RINCIAN PEMBAYARAN -->
        <div class="bukti-bagian bukti-putih bukti-rincian">
            <h3>Rincian Pembayaran</h3>
            <div class="bukti-baris">
                <span class="label">Harga penginapan</span>
                <span class="nilai"><?php echo rupiah($subtotal); ?>,00</span>
            </div>
            <div class="bukti-baris">
                <span class="label">Biaya Layanan (5%)</span>
                <span class="nilai"><?php echo rupiah($biaya); ?>,00</span>
            </div>
            <hr />
            <div class="bukti-baris total">
                <span class="label">Total</span>
                <span class="nilai"><?php echo rupiah($r["total_harga"]); ?>,00</span>
            </div>
        </div>

        <!-- INFORMASI CHECK-IN -->
        <div class="bukti-bagian bukti-abu bukti-info">
            <h3>Informasi Check-in</h3>
            <ul>
                <li>
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 8h.01M11 12h1v5h1" />
                    </svg>
                    Tunjukkan kode booking kepada staff.
                </li>
                <li>
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                    Waktu check-in mulai pukul 14.00 WIB.
                </li>
            </ul>
        </div>

        <!-- PENUTUP -->
        <div class="bukti-penutup">
            <p>Cari penginapan jadi lebih mudah! Temukan tempat<br />menginap terbaik untuk setiap perjalanan bersama BoBook</p>
        </div>
        <div class="bukti-aksi">
            <a href="reservasi.php">Kembali ke Reservasi Saya</a>
            <button class="tombol-garis" type="button" onclick="window.print();">Cetak bukti</button>
        </div>
    </div>
</body>

</html>