<?php
require __DIR__ . "/../includes/init.php";
wajib_admin();

global $koneksi;

$id = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
$edit = ($id > 0);

$data = array(
    "nama_homestay" => "",
    "alamat" => "",
    "kota" => "",
    "latitude" => "",
    "longitude" => "",
    "deskripsi" => "",
    "fasilitas" => "",
    "jumlah_kamar" => 1,
    "kapasitas_min" => 1,
    "kapasitas_maks" => 2,
    "harga_per_malam" => "",
    "foto_utama" => null
);

if ($edit) {
    $result = mysqli_query($koneksi, "SELECT * FROM homestay WHERE id_homestay='$id'");
    if (mysqli_num_rows($result) == 0) {
        set_pesan("Homestay tidak ditemukan.", "error");
        header("Location: homestay.php");
        exit;
    }
    $data = mysqli_fetch_assoc($result);
}

$error = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $foto_lama = $data["foto_utama"];
    $kolom = array(
        "nama_homestay",
        "alamat",
        "kota",
        "latitude",
        "longitude",
        "deskripsi",
        "fasilitas",
        "jumlah_kamar",
        "kapasitas_min",
        "kapasitas_maks",
        "harga_per_malam"
    );

    foreach ($kolom as $k) {
        $data[$k] = trim($_POST[$k]);
    }

    $data["foto_utama"] = $foto_lama;

    if ($data["nama_homestay"] == "" || $data["alamat"] == "" || $data["kota"] == "") {
        $error[] = "Nama, alamat, dan kota wajib diisi.";
    }
    if (!is_numeric($data["latitude"]) || $data["latitude"] < -90 || $data["latitude"] > 90) {
        $error[] = "Latitude harus angka antara -90 sampai 90.";
    }
    if (!is_numeric($data["longitude"]) || $data["longitude"] < -180 || $data["longitude"] > 180) {
        $error[] = "Longitude harus angka antara -180 sampai 180.";
    }
    if (
        (int)$data["jumlah_kamar"] < 1 || (int)$data["kapasitas_min"] < 1 ||
        (int)$data["kapasitas_maks"] < (int)$data["kapasitas_min"]
    ) {
        $error[] = "Jumlah kamar dan kapasitas tidak valid (kapasitas maks. harus >= min.).";
    }
    if (!is_numeric($data["harga_per_malam"]) || $data["harga_per_malam"] <= 0) {
        $error[] = "Harga per malam harus lebih dari 0.";
    }

    $file_foto = isset($_FILES["foto"]) ? $_FILES["foto"] : null;
    $hasil = simpan_foto($file_foto, "homestay", "bb");

    $foto_baru = $hasil[0];
    $err_foto = $hasil[1];

    if ($err_foto) {
        $error[] = $err_foto;
    }

    if (count($error) == 0) {
        $nama = $data["nama_homestay"];
        $alamat = $data["alamat"];
        $kota = $data["kota"];
        $lat = (float)$data["latitude"];
        $lng = (float)$data["longitude"];
        $deskripsi = $data["deskripsi"];
        $fasilitas = $data["fasilitas"];
        $kamar = (int)$data["jumlah_kamar"];
        $kap_min = (int)$data["kapasitas_min"];
        $kap_maks = (int)$data["kapasitas_maks"];
        $harga = (float)$data["harga_per_malam"];

        $set = "nama_homestay='$nama', alamat='$alamat', kota='$kota',
                latitude='$lat', longitude='$lng', deskripsi='$deskripsi',
                fasilitas='$fasilitas', jumlah_kamar='$kamar', kapasitas_min='$kap_min',
                kapasitas_maks='$kap_maks', harga_per_malam='$harga'";
        if ($foto_baru) {
            $set .= ", foto_utama='$foto_baru'";
        }

        if ($edit) {
            $query = "UPDATE homestay SET $set WHERE id_homestay='$id'";
        } else {
            $query = "INSERT INTO homestay SET $set";
        }
        $result = mysqli_query($koneksi, $query);

        if ($foto_baru && $edit) {
            hapus_foto("homestay", $foto_lama);
        }
        if ($edit) {
            set_pesan("Homestay berhasil diperbarui.");
        } else {
            set_pesan("Homestay berhasil ditambahkan.");
        }
        header("Location: homestay.php");
        exit;
    }
}

$foto = url_foto("homestay", $data["foto_utama"]);
$judul = $edit ? "Ubah Homestay" : "Tambah Homestay";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo e($judul); ?> - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/admin/homestay_form.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="index.php">
                    <img src="../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../index.php">HomeStay</a></li>
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
                    <li><a class="logout-link" href="../auth/logout.php">Keluar</a></li>
                <?php } else { ?>
                    <li><a href="auth/register.php">Masuk</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>


    <div class="form-kotak form-lebar">
        <h2><?php echo e($judul); ?></h2>
        <?php foreach ($error as $pesan): ?>
            <div class="pesan pesan-error"><?php echo e($pesan); ?></div>
        <?php endforeach; ?>
        <form method="post" action="homestay_form.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo e($id); ?>" />

            <label for="nama_homestay">Nama homestay</label>
            <input type="text" id="nama_homestay" name="nama_homestay" value="<?php echo e($data["nama_homestay"]); ?>" />

            <label for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" value="<?php echo e($data["alamat"]); ?>" />

            <div class="baris-2">
                <div>
                    <label for="kota">Kota/kabupaten</label>
                    <input type="text" id="kota" name="kota" value="<?php echo e($data["kota"]); ?>" />
                </div>
                <div>
                    <label for="harga_per_malam">Harga per malam (Rp)</label>
                    <input type="number" id="harga_per_malam" name="harga_per_malam" min="1" value="<?php echo e($data["harga_per_malam"]); ?>" />
                </div>
            </div>

            <div class="baris-2">
                <div>
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" value="<?php echo e($data["latitude"]); ?>" />
                </div>
                <div>
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" value="<?php echo e($data["longitude"]); ?>" />
                </div>
            </div>
            <p class="kecil">Tips: klik kanan lokasi di Google Maps lalu salin angka koordinatnya.</p>

            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" cols="40"><?php echo e($data["deskripsi"]); ?></textarea>

            <label for="fasilitas">Fasilitas (pisahkan dengan koma)</label>
            <input type="text" id="fasilitas" name="fasilitas" value="<?php echo e($data["fasilitas"]); ?>" />

            <div class="baris-2">
                <div>
                    <label for="jumlah_kamar">Jumlah kamar</label>
                    <input type="number" id="jumlah_kamar" name="jumlah_kamar" min="1" value="<?php echo e($data["jumlah_kamar"]); ?>" />
                </div>
                <div>
                    <label for="kapasitas_min">Kapasitas minimum</label>
                    <input type="number" id="kapasitas_min" name="kapasitas_min" min="1" value="<?php echo e($data["kapasitas_min"]); ?>" />
                </div>
            </div>
            <label for="kapasitas_maks">Kapasitas maksimum</label>
            <input type="number" id="kapasitas_maks" name="kapasitas_maks" min="1" value="<?php echo e($data["kapasitas_maks"]); ?>" />

            <label for="foto">Foto utama (JPG/PNG, maks. 2 MB)</label>
            <?php if ($foto) { ?>
                <p><img src="<?php echo e($foto); ?>" alt="Foto saat ini" width="140" /></p>
            <?php } ?>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" />

            <div class="aksi">
                <button type="submit">Simpan</button>
                <a class="tombol tombol-garis" href="homestay.php">Batal</a>
            </div>
        </form>
    </div>
</body>