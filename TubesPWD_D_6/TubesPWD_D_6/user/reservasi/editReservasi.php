<?php
require __DIR__ . "/../../includes/init.php";
wajib_login();

global $koneksi;

$id = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
$id_user = (int)$_SESSION["id_user"];

// ambil data reservasi milik sendiri yang masih "menunggu"
$query = "SELECT r.*, h.nama_homestay, p.status_verifikasi AS status_bayar FROM reservasi r
          JOIN homestay h ON h.id_homestay = r.id_homestay
          LEFT JOIN pembayaran p ON p.id_reservasi = r.id_reservasi
          WHERE r.id_reservasi='$id' AND r.id_user='$id_user' AND r.status='menunggu'";
$result = mysqli_query($koneksi, $query);
if (mysqli_num_rows($result) == 0) {
    set_pesan("Reservasi tidak ditemukan atau tidak bisa diubah.", "error");
    header("Location: reservasi.php");
    exit;
}
$r = mysqli_fetch_assoc($result);

// jika bukti bayar sudah dikirim, total tidak boleh berubah lagi
if ($r["status_bayar"] == "menunggu" || $r["status_bayar"] == "valid") {
    set_pesan("Reservasi tidak bisa diubah karena bukti pembayaran sudah dikirim.", "error");
    header("Location: reservasi.php");
    exit;
}

$error = "";
$masuk = $r["tgl_checkin"];
$keluar = $r["tgl_checkout"];
$tamu = $r["jumlah_tamu"];

// proses perubahan setelah form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masuk = $_POST["masuk"];
    $keluar = $_POST["keluar"];
    $tamu = (int)$_POST["tamu"];

    $id_homestay = (int)$r["id_homestay"];
    $result = mysqli_query($koneksi, "SELECT * FROM homestay WHERE id_homestay='$id_homestay'");
    $h = mysqli_fetch_assoc($result);

    // Parameter ke-5 = id reservasi ini, supaya tidak dianggap bentrok dengan dirinya sendiri
    $error = cek_reservasi($h, $masuk, $keluar, $tamu, $id);

    if ($error == "") {
        $total = hitung_malam($masuk, $keluar) * $h["harga_per_malam"];
        $query = "UPDATE reservasi SET tgl_checkin='$masuk', tgl_checkout='$keluar',
                  jumlah_tamu='$tamu', total_harga='$total'
                  WHERE id_reservasi='$id' AND id_user='$id_user'";
        $result = mysqli_query($koneksi, $query);
        set_pesan("Reservasi berhasil diperbarui.");
        header("Location: reservasi.php");
        exit;
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Reservasi - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/editReservasi.css?v=<?php echo time(); ?>" />
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

    <div class="form-kotak">
        <h2>Ubah reservasi</h2>
        <p><?php echo e($r["nama_homestay"]); ?></p>
        <?php if ($error != "") { ?>
            <div class="pesan pesan-error"><?php echo e($error); ?></div>
        <?php } ?>
        <form method="post" action="editReservasi.php">
            <input type="hidden" name="id" value="<?php echo e($id); ?>" />
            <label for="masuk">Check-in</label>
            <input type="date" id="masuk" name="masuk" value="<?php echo e($masuk); ?>" />
            <label for="keluar">Check-out</label>
            <input type="date" id="keluar" name="keluar" value="<?php echo e($keluar); ?>" />
            <label for="tamu">Jumlah tamu</label>
            <input type="number" id="tamu" name="tamu" min="1" value="<?php echo e($tamu); ?>" />
            <div class="aksi">
                <button type="submit">Simpan perubahan</button>
                <a class="tombol tombol-garis" href="reservasi.php">Batal</a>
            </div>
        </form>
    </div>
</body>