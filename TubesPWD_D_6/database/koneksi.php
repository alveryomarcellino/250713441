<?php
$host    = "localhost";
$user    = "root";
$pass    = "";
$nama_db = "homestay_db";

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/TubesPWD_D_6'); 
}

$koneksi = mysqli_connect($host, $user, $pass, $nama_db);

if (!$koneksi) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}
