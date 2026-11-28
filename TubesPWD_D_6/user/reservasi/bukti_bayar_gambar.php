<?php
// Menampilkan foto bukti pembayaran HANYA untuk pemiliknya dan admin.
// (Foto disimpan di uploads/bukti/ yang tidak bisa dibuka langsung lewat alamat file.)
require __DIR__ . "/../../includes/init.php";
wajib_login();

global $koneksi;

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0; // id reservasi
$id_user = (int)$_SESSION["id_user"];

$syarat = "p.id_reservasi='$id'";
if ($_SESSION["role"] != "admin") {
    $syarat .= " AND r.id_user='$id_user'";
}
$query = "SELECT p.bukti_transfer FROM pembayaran p
          JOIN reservasi r ON r.id_reservasi = p.id_reservasi
          WHERE $syarat";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    http_response_code(404);
    echo "Bukti pembayaran tidak ditemukan.";
    exit;
}
$row = mysqli_fetch_assoc($result);

$nama = basename($row["bukti_transfer"]);
$path = __DIR__ . "/../../uploads/bukti/" . $nama;
if (!is_file($path)) {
    http_response_code(404);
    echo "File bukti pembayaran tidak ditemukan.";
    exit;
}

$ekstensi = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
header("Content-Type: " . ($ekstensi == "png" ? "image/png" : "image/jpeg"));
header("Content-Length: " . filesize($path));
header("X-Content-Type-Options: nosniff");
readfile($path);
