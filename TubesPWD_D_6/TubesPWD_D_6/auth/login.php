<?php
require __DIR__ . "/../includes/init.php";

global $koneksi;
$error = "";
$identitas = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identitas = trim($_POST["identitas"]);
    $password  = $_POST["password"];

    // boleh login memakai username ATAU email
    $query = "SELECT * FROM users WHERE username='$identitas' OR email='$identitas'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password_hash"])) {
            $_SESSION["id_user"] = $user["id_user"];
            $_SESSION["nama"] = $user["nama_lengkap"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["foto"] = $user["foto_profil"];

            if ($user["role"] == "admin") {
                header("Location: " . BASE_URL . "/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit;
        }
    }
    $error = "Username/email atau password salah.";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Login - BoBook</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/auth.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="../assets/css/nav.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div id="nav">
        <div class="head-nav">
            <div class="logo">
                <a href="../index.php">
                    <img src="../assets/img/logo.png" alt="Logo BoBook" />
                </a>
            </div>
            <ul class="menu-navigasi">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../index.php">HomeStay</a></li>
                <li><a href="register.php">Masuk</a></li>
            </ul>
        </div>
    </div>

    <div class="form-kotak">
        <h2>Masuk</h2>
        <?php if ($error != "") { ?>
            <div class="pesan pesan-error"><?php echo e($error); ?></div>
        <?php } ?>
        <form method="post" action="login.php">
            <label for="identitas">Username atau email</label>
            <input type="text" id="identitas" name="identitas" value="<?php echo e($identitas); ?>" />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" />

            <div class="aksi">
                <button type="submit">Masuk</button>
                <a class="tombol tombol-garis" href="register.php">Daftar akun baru</a>
            </div>
        </form>
    </div>
</body>