<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"]; // id reservasi
    $aksi = $_POST["aksi"];

    if ($aksi == "terima") {
        if (hitung_baris("SELECT id_reservasi FROM reservasi WHERE id_reservasi='$id' AND status='menunggu'") == 0) {
            set_pesan("Reservasi tidak dalam status menunggu.", "error");
        } else {
            $query = "UPDATE pembayaran SET status_verifikasi='valid', catatan_admin=NULL
                      WHERE id_reservasi='$id' AND status_verifikasi='menunggu'";
            mysqli_query($koneksi, $query);
            if (mysqli_affected_rows($koneksi) == 1) {
                mysqli_query($koneksi, "UPDATE reservasi SET status='dikonfirmasi' WHERE id_reservasi='$id'");
                set_pesan("Pembayaran diterima. Reservasi dikonfirmasi.");
            } else {
                set_pesan("Tidak ada pembayaran yang menunggu verifikasi.", "error");
            }
        }
    } else if ($aksi == "tolak") {
        $catatan = trim($_POST["catatan"]);
        if ($catatan == "") {
            $catatan = "Bukti pembayaran tidak sesuai.";
        }
        $query = "UPDATE pembayaran SET status_verifikasi='ditolak', catatan_admin='$catatan'
                  WHERE id_reservasi='$id' AND status_verifikasi='menunggu'";
        mysqli_query($koneksi, $query);
        if (mysqli_affected_rows($koneksi) == 1) {
            set_pesan("Pembayaran ditolak. Pengguna diminta mengunggah ulang.");
        } else {
            set_pesan("Tidak ada pembayaran yang menunggu verifikasi.", "error");
        }
    }
}
header("Location: reservasi.php");
exit;
