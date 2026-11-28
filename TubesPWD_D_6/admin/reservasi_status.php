<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $status = $_POST["status"];
    if ($status == "dibatalkan") {
        $query = "UPDATE reservasi SET status='dibatalkan' WHERE id_reservasi='$id' AND status='menunggu'";
        $result = mysqli_query($koneksi, $query);
        if (mysqli_affected_rows($koneksi) == 1) {
            set_pesan("Reservasi dibatalkan.");
        } else {
            set_pesan("Status reservasi tidak berubah.", "error");
        }
    }
}
header("Location: reservasi.php");
exit;
