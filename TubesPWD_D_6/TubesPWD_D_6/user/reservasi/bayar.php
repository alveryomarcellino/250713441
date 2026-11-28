<?php
// Pengguna mengunggah bukti pembayaran untuk sebuah reservasi.
require __DIR__ . "/../../includes/init.php";
wajib_login();
global $koneksi;

if ($_SESSION["role"] != "user") {
    header("Location: " . BASE_URL . "/admin/reservasi.php");
    exit;
}

$id = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
$id_user = (int)$_SESSION["id_user"];

// Ambil reservasi milik sendiri beserta data pembayarannya (jika sudah ada)
$query = "SELECT r.*, h.nama_homestay, p.id_pembayaran, p.status_verifikasi,
                 p.catatan_admin, p.bukti_transfer, p.metode AS metode_lama, p.tgl_bayar AS tgl_lama
          FROM reservasi r
          JOIN homestay h ON h.id_homestay = r.id_homestay
          LEFT JOIN pembayaran p ON p.id_reservasi = r.id_reservasi
          WHERE r.id_reservasi='$id' AND r.id_user='$id_user'";
$result = mysqli_query($koneksi, $query);
if (mysqli_num_rows($result) == 0) {
    set_pesan("Reservasi tidak ditemukan.", "error");
    header("Location: reservasi.php");
    exit;
}
$r = mysqli_fetch_assoc($result);

// Hanya reservasi yang masih menunggu dan belum dinyatakan valid yang boleh dibayar
if ($r["status"] != "menunggu" || $r["status_verifikasi"] == "valid") {
    set_pesan("Reservasi ini tidak dapat dibayar (sudah dikonfirmasi atau dibatalkan).", "error");
    header("Location: bukti.php?id=" . $id);
    exit;
}

$error = array();
$metode = $r["metode_lama"] ? $r["metode_lama"] : "";
$tgl = $r["tgl_lama"] ? $r["tgl_lama"] : date("Y-m-d");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metode = $_POST["metode"];
    $jumlah = trim($_POST["jumlah_bayar"]);
    $tgl    = $_POST["tgl_bayar"];

    if (!in_array($metode, daftar_metode_bayar())) {
        $error[] = "Pilih metode pembayaran.";
    }
    if (!is_numeric($jumlah) || (float)$jumlah != (float)$r["total_harga"]) {
        $error[] = "Jumlah pembayaran harus sama dengan total reservasi (" . rupiah($r["total_harga"]) . ").";
    }
    if (!tanggal_valid($tgl) || $tgl > date("Y-m-d")) {
        $error[] = "Tanggal pembayaran tidak valid.";
    }

    // Foto bukti wajib untuk pembayaran pertama atau setelah ditolak.
    // Jika status masih "menunggu", foto boleh dikosongkan (tetap memakai foto lama).

    $file_bukti = isset($_FILES["bukti"]) ? $_FILES["bukti"] : null; // isi $__FILES ada 'name', 'type', 'tmp_name', 'error', 'size'
    $hasil = simpan_foto($file_bukti, "bukti", "bayar" . $id); // mengembalikan array [0] berupa hasil nya, dan [1] berupa error nya

    $bukti_baru = $hasil[0];
    $err_foto = $hasil[1];

    if ($err_foto) {
        $error[] = $err_foto;
    }

    $wajib_foto = ($r["id_pembayaran"] == null || $r["status_verifikasi"] == "ditolak");
    if ($wajib_foto && !$bukti_baru && !$err_foto) {
        $error[] = "Foto bukti pembayaran wajib diunggah.";
    }

    if (count($error) > 0) {
        if ($bukti_baru) {
            hapus_foto("bukti", $bukti_baru); // jangan tinggalkan file yatim
        }
    } else {
        $total = (float)$r["total_harga"];

        if ($r["id_pembayaran"] == null) {
            // Pembayaran pertama: INSERT
            $query = "INSERT INTO pembayaran SET id_reservasi='$id', metode='$metode',
                      jumlah_bayar='$total', bukti_transfer='$bukti_baru', tgl_bayar='$tgl',
                      status_verifikasi='menunggu'";
            $result = mysqli_query($koneksi, $query);
            $berhasil = (mysqli_affected_rows($koneksi) == 1);
        } else {
            // unggah ulang (setelah ditolak atau mengganti bukti): UPDATE
            $set_bukti = "";
            if ($bukti_baru) {
                $set_bukti = ", bukti_transfer='" . $bukti_baru . "'";
                hapus_foto("bukti", $r["bukti_transfer"]);
            }
            $query = "UPDATE pembayaran SET metode='$metode', jumlah_bayar='$total', tgl_bayar='$tgl',
                      status_verifikasi='menunggu', catatan_admin=NULL $set_bukti
                      WHERE id_reservasi='$id'";
            $result = mysqli_query($koneksi, $query);
            $berhasil = true;
        }

        if ($berhasil) {
            set_pesan("Bukti pembayaran terkirim. Menunggu verifikasi admin.");
            header("Location: bukti.php?id=" . $id);
            exit;
        }
        $error[] = "Bukti pembayaran gagal disimpan, silakan coba lagi.";
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bayar - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/nav.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/bayar.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="../../index.php">
                    <img src="../../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../../index.php">Beranda</a></li>
                <li><a href="../../index.php">HomeStay</a></li>
                <?php if (sudah_login() && $_SESSION['role'] == 'user') { ?>
                    <li><a href="reservasi.php">Reservasi Saya</a></li>
                <?php } ?>
                <?php if (sudah_login() && $_SESSION['role'] == 'admin') { ?>
                    <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/homestay.php">Homestay</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reservasi.php">Reservasi</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/ulasan.php">Ulasan</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/pengguna.php">Pengguna</a></li>
                <?php } ?>
                <li>
                    <a class="profil-link" href="<?php echo BASE_URL; ?>/user/profil.php">
                        <?php if (!empty($_SESSION['foto'])) { ?>
                            <img class="avatar" src="<?php echo url_foto("profil", $_SESSION['foto']) ?>" alt="Foto profil" />
                        <?php } else { ?>
                            <span class="avatar avatar-huruf"><?php echo e(strtoupper($_SESSION['nama'][0])); ?></span>
                        <?php } ?>
                        <span class="sembunyi-hp"><?php echo e($_SESSION['nama']); ?></span>
                    </a>
                </li>
                <li><a class="logout-link" href="../../auth/logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>

    <div class="form-kotak">
        <h2>Upload bukti pembayaran</h2>
        <p><strong><?php echo e($r["kode_reservasi"]); ?></strong> &#8211; <?php echo e($r["nama_homestay"]); ?><br />
            <span class="kecil"><?php echo tanggal_indo($r["tgl_checkin"]); ?> s/d <?php echo tanggal_indo($r["tgl_checkout"]); ?></span>
        </p>

        <div class="bayar-box">
            <div>Total yang harus dibayar</div>
            <div class="kode-besar"><?php echo rupiah($r["total_harga"]); ?></div>
            <div class="kecil">Transfer ke: <strong><?php echo e("BCA - 096-149-3608"); ?></strong></div>
        </div>

        <?php if ($r["status_verifikasi"] == "ditolak") { ?>
            <div class="pesan pesan-error">Pembayaran sebelumnya ditolak<?php echo $r["catatan_admin"] ? ": " . e($r["catatan_admin"]) : "."; ?> Silakan unggah bukti yang baru.</div>
        <?php } else if ($r["status_verifikasi"] == "menunggu") { ?>
            <div class="pesan pesan-ok">Bukti sudah diunggah dan menunggu verifikasi.
                <a href="bukti.php?id=<?php echo e($id); ?>" target="_blank">Lihat bukti saat ini</a>.
                Kamu bisa menggantinya di bawah.
            </div>
        <?php } ?>

        <?php foreach ($error as $pesan) { ?>
            <div class="pesan pesan-error"><?php echo e($pesan); ?></div>
        <?php } ?>

        <form method="post" action="bayar.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo e($id); ?>" />

            <label for="metode">Metode pembayaran</label>
            <select id="metode" name="metode">
                <option value="">-- pilih --</option>
                <?php foreach (daftar_metode_bayar() as $m) { ?>
                    <option value="<?php echo e($m); ?>" <?php echo $m == $metode ? ' selected="selected"' : ""; ?>><?php echo e($m); ?></option>
                <?php } ?>
            </select>

            <label for="jumlah_bayar">Jumlah dibayar (Rp)</label>
            <input type="number" id="jumlah_bayar" name="jumlah_bayar" value="<?php echo e((int)$r["total_harga"]); ?>" />

            <label for="tgl_bayar">Tanggal pembayaran</label>
            <input type="date" id="tgl_bayar" name="tgl_bayar" max="<?php echo date("Y-m-d"); ?>" value="<?php echo e($tgl); ?>" />

            <label for="bukti">Foto bukti pembayaran (JPG/PNG, maks. 2 MB)</label>
            <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png" />

            <div class="aksi">
                <button type="submit">Kirim bukti pembayaran</button>
                <a class="tombol tombol-garis" href="bukti.php?id=<?php echo e($id); ?>">Kembali</a>
            </div>
        </form>
    </div>
</body>