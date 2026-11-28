<?php
// DELETE reservasi milik sendiri (hanya jika belum dikonfirmasi).
require_once __DIR__ . "/../../includes/init.php";
wajib_login();
global $koneksi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST["id"];
    $id_user = (int)$_SESSION["id_user"];

    // Catat dulu nama file bukti bayar (jika ada) supaya filenya ikut dihapus
    $query = "SELECT p.bukti_transfer FROM pembayaran p
              JOIN reservasi r ON r.id_reservasi = p.id_reservasi
              WHERE r.id_reservasi='$id' AND r.id_user='$id_user' AND r.status <> 'dikonfirmasi'";
    $result = mysqli_query($koneksi, $query);
    $bayar = mysqli_fetch_assoc($result);

    $query = "DELETE FROM reservasi
              WHERE id_reservasi='$id' AND id_user='$id_user' AND status <> 'dikonfirmasi'";
    $result = mysqli_query($koneksi, $query);

    // Seperti contoh materi: pastikan ada baris yangX benar-benar terhapus
    if (mysqli_affected_rows($koneksi) == 1) {
        if ($bayar) {
            hapus_foto("bukti", $bayar["bukti_transfer"]);   // baris pembayaran ikut terhapus (CASCADE)
        }
    }
}
header("Location: reservasi.php");
exit;
