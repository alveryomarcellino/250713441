<?php
// CREATE reservasi: menerima data dari tombol "Pesan sekarang" di homestay_detail.php
require __DIR__ . "/includes/init.php";
wajib_login();

global $koneksi;

// Halaman ini hanya boleh dibuka lewat form (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$id_homestay = isset($_POST["id_homestay"]) ? (int)$_POST["id_homestay"] : 0;
$masuk = isset($_POST["masuk"]) ? trim($_POST["masuk"]) : "";
$keluar = isset($_POST["keluar"]) ? trim($_POST["keluar"]) : "";
$tamu = isset($_POST["tamu"]) ? (int)$_POST["tamu"] : 0;
$id_user = (int)$_SESSION["id_user"];

// Alamat kembali ke halaman detail (membawa isian supaya tidak perlu mengetik ulang)
$kembali = "homestay_detail.php?id=" . $id_homestay
    . "&masuk=" . urlencode($masuk)
    . "&keluar=" . urlencode($keluar)
    . "&tamu=" . $tamu;

// Fungsi kecil: simpan pesan error lalu kembali ke halaman detail
function gagal($pesan, $kembali)
{
    set_pesan($pesan, "error");
    header("Location: " . $kembali);
    exit;
}

// 1. Ambil data homestay (prepared statement)
$stmt = mysqli_prepare($koneksi, "SELECT * FROM homestay WHERE id_homestay = ?");
mysqli_stmt_bind_param($stmt, "i", $id_homestay);
mysqli_stmt_execute($stmt);
$h = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$h) {
    set_pesan("Homestay tidak ditemukan.", "error");
    header("Location: index.php");
    exit;
}

// 2. Validasi tanggal
if (!tanggal_valid($masuk) || !tanggal_valid($keluar)) {
    gagal("Tanggal check-in dan check-out tidak valid.", $kembali);
}
if ($keluar <= $masuk) {
    gagal("Tanggal check-out harus setelah check-in.", $kembali);
}
if ($masuk < date("Y-m-d")) {
    gagal("Tanggal check-in tidak boleh sebelum hari ini.", $kembali);
}

// 3. Validasi jumlah tamu sesuai kapasitas homestay
if ($tamu < $h["kapasitas_min"] || $tamu > $h["kapasitas_maks"]) {
    gagal("Jumlah tamu harus antara " . $h["kapasitas_min"] . " sampai " . $h["kapasitas_maks"] . " orang.", $kembali);
}

// 4. Hitung total harga (sama seperti yang ditampilkan di halaman detail: malam x harga + biaya layanan 5%)
$malam = hitung_malam($masuk, $keluar);
$subtotal = $malam * $h["harga_per_malam"];
$biaya = round($subtotal * 0.05);
$total = $subtotal + $biaya;

// 5. Simpan memakai transaksi. Baris homestay dikunci (FOR UPDATE) supaya dua orang
//    tidak bisa memesan tanggal yang sama pada saat yang bersamaan.
mysqli_begin_transaction($koneksi);

$stmt = mysqli_prepare($koneksi, "SELECT id_homestay FROM homestay WHERE id_homestay = ? FOR UPDATE");
mysqli_stmt_bind_param($stmt, "i", $id_homestay);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Cek bentrok tanggal (aturan sama dengan pencarian di index.php)
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM reservasi
    WHERE id_homestay = ? AND status <> 'dibatalkan' AND tgl_checkin < ? AND tgl_checkout > ?");
mysqli_stmt_bind_param($stmt, "iss", $id_homestay, $keluar, $masuk);
mysqli_stmt_execute($stmt);

$bentrok = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($bentrok["n"] > 0) {
    mysqli_rollback($koneksi);
    gagal("Maaf, homestay sudah dipesan pada tanggal tersebut. Silakan pilih tanggal lain.", $kembali);
}

// Buat kode reservasi unik, contoh: BB-20261124-K7Q2
$kode = "";
do {
    $kode = kode_reservasi($_SESSION["id_user"]);
    $stmt = mysqli_prepare($koneksi, "SELECT id_reservasi FROM reservasi WHERE kode_reservasi = ?");
    mysqli_stmt_bind_param($stmt, "s", $kode);
    mysqli_stmt_execute($stmt);
    $ada = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
} while ($ada);

$stmt = mysqli_prepare($koneksi, "INSERT INTO reservasi
    (kode_reservasi, id_user, id_homestay, tgl_checkin, tgl_checkout, jumlah_tamu, total_harga)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "siissii", $kode, $id_user, $id_homestay, $masuk, $keluar, $tamu, $total);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_rollback($koneksi);
    gagal("Reservasi gagal disimpan. Silakan coba lagi.", $kembali);
}

mysqli_commit($koneksi);

set_pesan("Reservasi berhasil dibuat dengan kode " . $kode . ". Silakan lakukan pembayaran.");
header("Location: user/reservasi/reservasi.php");
exit;
