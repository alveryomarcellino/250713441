<?php
require __DIR__ . "/../database/koneksi.php";
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, "UTF-8");
}

// Menghitung jumlah baris hasil SELECT (dipakai di dashboard admin)
function hitung_baris($query)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_num_rows($result) : 0;
}

// Menyimpan pesan sementara (flash message) di session, tampil sekali lalu hilang
function set_pesan($teks, $jenis = "sukses")
{
    $_SESSION["pesan"] = array("teks" => $teks, "jenis" => $jenis);
}

// Menampilkan pesan flash (kalau ada) lalu menghapusnya dari session
function tampil_pesan()
{
    if (isset($_SESSION["pesan"])) {
        $p = $_SESSION["pesan"];
        unset($_SESSION["pesan"]);
        echo '<div class="pesan pesan-' . e($p["jenis"]) . '">' . e($p["teks"]) . '</div>';
    }
}

function bintang($rating)
{
    $penuh = (int)round((float)$rating);
    $penuh = max(0, min(5, $penuh));
    return str_repeat("★", $penuh) . str_repeat("☆", 5 - $penuh) . " (" . number_format((float)$rating, 1) . ")";
}

function sudah_login()
{
    return isset($_SESSION["id_user"]);
}

function wajib_login()
{
    if (!sudah_login()) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
}

function wajib_admin()
{
    wajib_login();
    if ($_SESSION["role"] != "admin") {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function simpan_foto($berkas, $folder, $awalan)
{
    if (!isset($berkas) || $berkas["error"] == UPLOAD_ERR_NO_FILE) {
        return array(null, null); 
    }
    if ($berkas["error"] != UPLOAD_ERR_OK) {
        return array(null, "Upload foto gagal.");
    }
    if ($berkas["size"] > 2 * 1024 * 1024) {
        return array(null, "Ukuran foto maksimal 2 MB."); 
    }
    $info = getimagesize($berkas["tmp_name"]); 
    $boleh = array("image/jpeg" => "jpg", "image/png" => "png");
    if ($info === false || !isset($boleh[$info["mime"]])) {
        return array(null, "Foto harus berformat JPG atau PNG.");
    }
    $nama = $awalan . "." . $boleh[$info["mime"]];  

    $tujuan = __DIR__ . "/../uploads/" . $folder . "/" . $nama; 
    if (!move_uploaded_file($berkas["tmp_name"], $tujuan)) { 
        return array(null, "Foto gagal disimpan di server.");
    }
    return array($nama, null);
}

function hapus_foto($folder, $nama)
{
    if ($nama) {
        $path = __DIR__ . "/../uploads/" . $folder . "/" . $nama;
        if (is_file($path)) { 
            unlink($path);
        }
    }
}

function url_foto($folder, $nama)
{
    if ($nama) {
        return BASE_URL . "/uploads/" . $folder . "/" . $nama; 
    }
    return null;
}

function tanggal_valid($tgl)
{
    $bagian = explode("-", $tgl);

    if (count($bagian) != 3) {
        return false;
    }

    return checkdate((int)$bagian[1], (int)$bagian[2], (int)$bagian[0]);
}

function hitung_malam($masuk, $keluar)
{
    $detik_masuk  = strtotime($masuk);
    $detik_keluar = strtotime($keluar);
    $selisih_detik = $detik_keluar - $detik_masuk;

    return round($selisih_detik / 86400);
}

function rupiah($angka)
{
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

function format_jarak($km)
{
    return sprintf("%.1F", $km) . " km";
}

function tanggal_indo($tgl)
{
    return date("d-M-Y", strtotime($tgl));
}

function tanggal_panjang($tgl)
{
    $bulan = array(
        1 => "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember"
    );
    $waktu = strtotime($tgl);
    return date("j", $waktu) . " " . $bulan[(int)date("n", $waktu)] . " " . date("Y", $waktu);
}

function label_status($status)
{
    switch ($status) {
        case "menunggu":
            return "Menunggu konfirmasi";
        case "dikonfirmasi":
            return "Dikonfirmasi";
        case "dibatalkan":
            return "Dibatalkan";
        default:
            return $status;
    }
}

function label_bayar($status)
{
    switch ($status) {
        case "menunggu":
            return "Menunggu verifikasi";
        case "valid":
            return "Pembayaran valid";
        case "ditolak":
            return "Pembayaran ditolak";
        default:
            return "Belum dibayar";
    }
}

function boleh_ulasan($status, $tgl_checkout)
{
    return $status == "dikonfirmasi" && $tgl_checkout <= date("Y-m-d");
}

function kode_reservasi($id_user)
{
    global $koneksi;
    do {
        $kode = "BB-" . $id_user . mt_rand(10000, 99999);
        $result = mysqli_query($koneksi, "SELECT id_reservasi FROM reservasi WHERE kode_reservasi='$kode'");
    } while (mysqli_num_rows($result) > 0);
    return $kode;
}

function daftar_metode_bayar()
{
    return array("Transfer Bank BCA", "Transfer Bank BRI", "Transfer Bank Mandiri", "DANA", "OVO", "GoPay");
}

function cek_reservasi($h, $masuk, $keluar, $tamu, $kecuali = 0)
{
    if (!$h) return "Homestay tidak ditemukan.";
    if (!tanggal_valid($masuk) || !tanggal_valid($keluar)) return "Tanggal tidak valid.";
    if ($masuk < date("Y-m-d")) return "Tanggal check-in tidak boleh sebelum hari ini.";
    if ($keluar <= $masuk) return "Tanggal check-out harus setelah check-in.";
    if ($tamu < 1 || $tamu > $h["kapasitas_maks"]) {
        return "Jumlah tamu harus antara 1 dan " . $h["kapasitas_maks"] . " orang.";
    }
    if (!homestay_tersedia($h["id_homestay"], $masuk, $keluar, $kecuali)) {
        return "Homestay sudah dipesan pada tanggal tersebut.";
    }
    return "";
}
