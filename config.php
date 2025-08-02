<?php
$host = '0.0.0.0'; //Ganti dengan host db kamu
$dbname = 'perpus'; // nama db
$user = 'root'; // user db kalian
$pass = 'root'; //sesuaikan dengan password db kalian
date_default_timezone_set('Asia/Jakarta');
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>