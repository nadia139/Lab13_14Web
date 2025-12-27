<?php
// koneksi.php
$host = 'localhost';
$user = 'root';
$password = '';  // Password kosong untuk XAMPP default
$database = 'db_barang';  // NAMA DATABASE
$port = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');
?>