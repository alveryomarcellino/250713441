<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];

    if (hitung_baris("SELECT id_reservasi FROM reservasi WHERE id_homestay='$id'") > 0) {
        set_pesan("Homestay tidak bisa dihapus karena sudah memiliki reservasi.", "error");
    } else {
        $result = mysqli_query($koneksi, "SELECT foto_utama FROM homestay WHERE id_homestay='$id'");
        $h = mysqli_fetch_assoc($result);

        // Lalu jalankan penghapusan, dan cek jumlah baris yang terhapus
        $result = mysqli_query($koneksi, "DELETE FROM homestay WHERE id_homestay='$id'");
        if (mysqli_affected_rows($koneksi) == 1) {
            if ($h) hapus_foto("homestay", $h["foto_utama"]);
            set_pesan("Homestay dihapus.");
        } else {
            set_pesan("Homestay gagal dihapus.", "error");
        }
    }
}
header("Location: homestay.php");
exit;
