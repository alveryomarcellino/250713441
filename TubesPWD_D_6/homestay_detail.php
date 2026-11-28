<?php
require __DIR__ . "/includes/init.php";

global $koneksi;


$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = mysqli_prepare($koneksi, "SELECT * FROM homestay WHERE id_homestay = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$h = mysqli_fetch_assoc($result);


if (!$h) {
    http_response_code(404);
    echo "Homestay tidak ditemukan.";
    exit;
}


$masuk  = isset($_GET["masuk"]) ? $_GET["masuk"] : "";
$keluar = isset($_GET["keluar"]) ? $_GET["keluar"] : "";
$tamu   = isset($_GET["tamu"]) ? (int)$_GET["tamu"] : 0;


$malam = 0;
$total = 0;
if (tanggal_valid($masuk) && tanggal_valid($keluar) && $keluar > $masuk) {
    $malam = hitung_malam($masuk, $keluar);
    $total = $malam * $h["harga_per_malam"];
}


$foto = url_foto("homestay", $h["foto_utama"]);


$biaya = round($total * 0.05);
$grand = $total + $biaya;


$daftar_fasilitas = array_map("trim", explode(",", (string)$h["fasilitas"]));


$stmt2 = mysqli_prepare($koneksi, "SELECT AVG(u.rating) AS rata, COUNT(*) AS jumlah
    FROM ulasan u JOIN reservasi r ON u.id_reservasi = r.id_reservasi
    WHERE r.id_homestay = ?");
mysqli_stmt_bind_param($stmt2, "i", $id);
mysqli_stmt_execute($stmt2);
$rating = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo e($h["nama_homestay"]); ?> - BoBook</title>
    <link rel="stylesheet" type="text/css" href="assets/css/beranda.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/detail.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/nav.css?v=<?php echo time(); ?>" />
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
        <div class="detail-judul">
            <h1><?php echo e($h["nama_homestay"]); ?></h1>
            <span class="lokasi">&#128205; <?php echo e($h["kota"]); ?></span>
        </div>

        <div class="galeri">
            <?php if ($foto) { ?>
                <img class="galeri-besar" src="<?php echo e($foto); ?>" alt="Foto utama" />
            <?php } else { ?>
                <div class="galeri-besar foto-kosong">Belum ada foto</div>
            <?php } ?>
            <div class="galeri-kecil foto-kosong">Foto 2</div>
            <div class="galeri-kecil foto-kosong">Foto 3</div>
        </div>

        <div class="detail-grid">
            <div class="detail-info">
                <h2><?php echo e($h["nama_homestay"]); ?></h2>
                <p class="rating">
                    &#9733; <?php echo $rating["jumlah"] > 0 ? number_format($rating["rata"], 2) : "Belum ada rating"; ?>
                    (<?php echo e($rating["jumlah"]); ?> ulasan)
                </p>
                <hr />
                <p class="kecil"><?php echo e($h["alamat"]); ?>, <?php echo e($h["kota"]); ?></p>
                <p><?php echo e($h["deskripsi"]); ?></p>
                <ul class="fasilitas-list">
                    <?php foreach ($daftar_fasilitas as $f) { ?>
                        <li><?php echo e($f); ?></li>
                    <?php } ?>
                    <li><?php echo e($h["kapasitas_maks"]); ?> Tamu</li>
                </ul>
            </div>

            <div class="detail-pesan">
                <p class="harga-besar"><?php echo rupiah($h["harga_per_malam"]); ?>/malam</p>
                <?php tampil_pesan(); ?>

                <form method="get" action="homestay_detail.php">
                    <input type="hidden" name="id" value="<?php echo e($h["id_homestay"]); ?>" />

                    <label for="masuk">Check-in</label>
                    <input type="date" id="masuk" name="masuk" value="<?php echo e($masuk); ?>" />

                    <label for="keluar">Check-out</label>
                    <input type="date" id="keluar" name="keluar" value="<?php echo e($keluar); ?>" />

                    <label for="tamu">Jumlah tamu</label>
                    <input type="number" id="tamu" name="tamu" min="<?php echo e($h["kapasitas_min"]); ?>"
                        max="<?php echo e($h["kapasitas_maks"]); ?>" value="<?php echo $tamu > 0 ? e($tamu) : ""; ?>" />

                    <button type="submit">Hitung total</button>
                </form>

                <?php if ($total > 0) { ?>
                    <div class="rincian">
                        <div class="baris"><span><?php echo rupiah($h["harga_per_malam"]); ?> x <?php echo e($malam); ?> malam</span><span><?php echo rupiah($total); ?></span></div>
                        <div class="baris"><span>Biaya Layanan</span><span><?php echo rupiah($biaya); ?></span></div>
                        <hr />
                        <div class="baris total"><span>Total</span><span><?php echo rupiah($grand); ?></span></div>
                    </div>

                    <?php if (sudah_login()) { ?>
                        <!-- Form 2: kirim pesanan (method POST) -->
                        <form method="post" action="reservasi_proses.php">
                            <input type="hidden" name="id_homestay" value="<?php echo e($h["id_homestay"]); ?>" />
                            <input type="hidden" name="masuk" value="<?php echo e($masuk); ?>" />
                            <input type="hidden" name="keluar" value="<?php echo e($keluar); ?>" />
                            <input type="hidden" name="tamu" value="<?php echo e($tamu); ?>" />
                            <button type="submit">Pesan sekarang</button>
                        </form>
                    <?php } else { ?>
                        <p><a class="tombol" href="auth/login.php">Masuk dulu untuk memesan</a></p>
                    <?php } ?>
                <?php } else { ?>
                    <p class="kecil">Isi tanggal check-in dan check-out lalu klik &quot;Hitung total&quot;.</p>
                <?php } ?>
            </div>
        </div>

        <h2>Lokasi</h2>
        <iframe title="Peta homestay" class="detail-peta"
            src="https://maps.google.com/maps?q=<?php echo e($h["latitude"]); ?>,<?php echo e($h["longitude"]); ?>&amp;z=15&amp;output=embed"></iframe>

        <h2>Rekomendasi Wisata</h2>
        <div class="wisata">
            <div class="wisata-kartu">
                <div class="foto-kosong"></div>
                <p>Telogo Putri</p>
            </div>
            <div class="wisata-kartu">
                <div class="foto-kosong"></div>
                <p>Lava Tour</p>
            </div>
            <div class="wisata-kartu">
                <div class="foto-kosong"></div>
                <p>Museum Merapi</p>
            </div>
        </div>
    </div>
</body>

</html>