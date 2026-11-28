<?php
require __DIR__ . "/includes/init.php";

global $koneksi;
$lokasi = isset($_GET["lokasi"]) ? trim($_GET["lokasi"]) : "";
$masuk = isset($_GET["masuk"]) ? $_GET["masuk"]  : "";
$keluar = isset($_GET["keluar"]) ? $_GET["keluar"] : "";
$tamu = isset($_GET["tamu"]) ? (int)$_GET["tamu"] : 0;
$urut = isset($_GET["urut"]) ? $_GET["urut"] : "harga";
$lat = (isset($_GET["lat"]) && is_numeric($_GET["lat"])) ? (float)$_GET["lat"] : null;
$lng = (isset($_GET["lng"]) && is_numeric($_GET["lng"])) ? (float)$_GET["lng"] : null;
$punya_lokasi = ($lat !== null && $lng !== null);

// menghitung jarak (km) dari posisi kita ke homestay memakai rumus haversine 
if ($punya_lokasi) {
    $kolom_jarak = "(6371 * ACOS(LEAST(1, COS(RADIANS($lat)) * COS(RADIANS(h.latitude))
        * COS(RADIANS(h.longitude) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(h.latitude))))) AS jarak";
} else {
    $kolom_jarak = "NULL AS jarak";
}

$where = "WHERE 1=1";
if ($lokasi != "") {
    $where .= " AND (h.kota LIKE '%$lokasi%' OR h.nama_homestay LIKE '%$lokasi%'
                OR h.alamat LIKE '%$lokasi%')";
}
if ($tamu > 0) {
    $where .= " AND h.kapasitas_maks >= $tamu";
}
if (tanggal_valid($masuk) && tanggal_valid($keluar) && $keluar > $masuk) {
    // Buang homestay yang sudah dipesan pada rentang tanggal itu
    $where .= " AND h.id_homestay NOT IN (SELECT id_homestay FROM reservasi
                WHERE status <> 'dibatalkan' AND tgl_checkin < '$keluar' AND tgl_checkout > '$masuk')";
}

// Urutan hasil
switch ($urut) {
    case "jarak":
        if ($punya_lokasi) {
            $order = "jarak ASC";
        } else {
            $order = "h.harga_per_malam ASC";
        }
        break;
    default:
        $order = "h.harga_per_malam ASC";
        break;
}

$query = "SELECT h.*, $kolom_jarak FROM homestay h $where ORDER BY $order";
$result = mysqli_query($koneksi, $query);
$jumlah = mysqli_num_rows($result);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Home - BoBook</title>
    <link rel="stylesheet" type="text/css" href="assets/css/beranda.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/nav.css?v=<?php echo time(); ?>" />
    <script src="assets/js/geolocation.js"></script>
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="#homestay">HomeStay</a></li>
                <?php if (sudah_login() && $_SESSION['role'] == 'user') { ?>
                    <li><a href="user/reservasi/reservasi.php">Reservasi Saya</a></li>
                <?php } ?>
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
                    <li><a class="logout-link" href="auth/logout.php">Keluar</a></li>
                <?php } else { ?>
                    <li><a href="auth/register.php">Masuk</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div id="header1">
        <div class="sub-bagian">
            <h1 class="sub-judul">TEMUKAN TEMPAT ISTIRAHAT YANG TERASA SEPERTI RUMAH</h1>
            <p class="subtitle">Pengalaman menginap yang hangat, nyaman, dan terasa seperti rumah sendiri.</p>
            <img src="assets/img/Gambar Beranda.png" alt="Homestay Utama BoBook" class="sub-pict" />
        </div>

        <h1 class="header-search">Jelajahi homestay<br />nyaman di sekitarmu</h1>
        <div class="search-tabel">
            <p class="search-subtitle">Mau menginap dimana?</p>
            <form id="form-cari" class="form-cari" method="get" action="index.php">
                <div>
                    <label for="lokasi">Lokasi homestay</label>
                    <input type="text" id="lokasi" name="lokasi" value="<?php echo e($lokasi); ?>" />
                </div>
                <div>
                    <label for="masuk">Check-in</label>
                    <input type="date" id="masuk" name="masuk" value="<?php echo e($masuk); ?>" />
                </div>
                <div>
                    <label for="keluar">Check-out</label>
                    <input type="date" id="keluar" name="keluar" value="<?php echo e($keluar); ?>" />
                </div>
                <div>
                    <label for="tamu">Jumlah penghuni</label>
                    <input type="number" id="tamu" name="tamu" min="1" value="<?php echo $tamu > 0 ? e($tamu) : ""; ?>" />
                </div>
                <div>
                    <label for="urut">Urutkan</label>
                    <select id="urut" name="urut">
                        <option value="harga">Harga termurah</option>
                        <option value="jarak">Jarak terdekat</option>
                    </select>
                </div>
                <input type="hidden" name="lat" value="<?php echo $punya_lokasi ? e($lat) : ""; ?>" />
                <input type="hidden" name="lng" value="<?php echo $punya_lokasi ? e($lng) : ""; ?>" />
                <div class="form-aksi">
                    <button type="submit">Cari</button>
                    <button type="button" id="btn-lokasi" class="tombol-garis" onclick="cariLokasi();">Gunakan lokasi saya</button>
                </div>
            </form>
            <div id="status-lokasi"><?php echo $punya_lokasi ? "Lokasi Anda sudah terdeteksi." : ""; ?></div>
        </div>
    </div>


    <!-- Homestay Pilihan -->
    <div class="featured-section">
        <h2>Homestay Pilihan<?php echo $punya_lokasi ? " di sekitar Anda" : ""; ?> (<?php echo $jumlah; ?>)</h2>
        <div class="grid-hasil<?php echo $punya_lokasi ? " dengan-peta" : ""; ?>">
            <div>
                <?php if ($jumlah == 0) { ?>
                    <p>Tidak ada homestay yang cocok. Coba ubah lokasi, tanggal, atau jumlah penghuni.</p>
                <?php } ?>
                <?php
                // Menerima dan menampilkan data baris demi baris: mysqli_fetch_assoc + while
                while ($h = mysqli_fetch_assoc($result)) {
                    $q = http_build_query(array(
                        "id" => $h["id_homestay"],
                        "masuk" => $masuk,
                        "keluar" => $keluar,
                        "tamu" => $tamu
                    )); // mengubah daftar array menjadi teks format parameter URL
                    // id=3&masuk=2026-11-25&keluar=2026-11-27&tamu=2 
                    $foto = url_foto("homestay", $h["foto_utama"]);
                ?>
                    <div class="kartu" id="homestay">
                        <div class="foto-kosong">
                            <?php if ($foto) { ?>
                                <img src="<?php echo e($foto); ?>" alt="<?php echo e($h["nama_homestay"]); ?>" />
                            <?php } else { ?>
                                <img src="uploads/homestay/Gambar Beranda 1 .png" alt="<?php echo e($h["nama_homestay"]); ?>" />
                            <?php } ?>
                        </div>
                        <div class="info">
                            <h3><?php echo e($h["nama_homestay"]); ?></h3>
                            <p class="kecil"><?php echo e($h["alamat"]); ?>, <?php echo e($h["kota"]); ?></p>
                            <p><?php echo e($h["deskripsi"]); ?></p>
                            <p class="kecil"><?php echo e($h["fasilitas"]); ?> | <?php echo e($h["kapasitas_min"]); ?>-<?php echo e($h["kapasitas_maks"]); ?> orang | <?php echo e($h["jumlah_kamar"]); ?> kamar</p>
                            <?php if ($h["jarak"] !== null) { // kalo lokasi dinyalakan 
                            ?>
                                <span class="badge"><?php echo format_jarak($h["jarak"]); ?> dari posisi Anda</span>
                            <?php } ?>
                        </div>
                        <div class="harga">
                            <div>Mulai dari<br /><strong><?php echo rupiah($h["harga_per_malam"]); ?></strong>/malam</div>
                            <a class="tombol" href="homestay_detail.php?<?php echo e($q); ?>">Lihat &amp; pesan</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if ($punya_lokasi) { ?>
                <div class="peta">
                    <h3>Lokasi Anda</h3>
                    <!-- <iframe> berfungsi membuka halaman web lain di dalam bingkai kecil pada halaman Anda sendiri -->
                    <!-- ini untuk memuat tampilan peta dari Google Maps tanpa memerlukan API Key -->
                    <iframe title="Peta lokasi Anda" src="https://maps.google.com/maps?q=<?php echo e($lat); ?>,<?php echo e($lng); ?>&amp;z=14&amp;output=embed"></iframe>
                    <!-- &amp;z=14 Parameter z (zoom) mengatur tingkat perbesaran peta.  -->
                    <!-- &amp;output=embed: parameter wajib agar Google Maps menampilkan mode peta tanam (embed) yang bersih tanpa bilah menu atas Google -->
                </div>
            <?php } ?>
        </div>
    </div>



    <!-- Footer -->
    <div id="footer">
        <img src="assets/img/logo.png" alt="BoBook Watermark" class="footer-logo" />
        <p class="footer-tagline">Cari Penginapan jadi lebih mudah! Temukan tempat menginap terbaik untuk setiap
            Perjalanan bersama BoBook</p>
    </div>
</body>

</html>