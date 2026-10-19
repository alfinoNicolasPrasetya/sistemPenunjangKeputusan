<?php
$host = "localhost";
$database = "spk_fuzzy_sugeno";
$username = "root";
$password = "";

try {
  // Membuat koneksi PDO
  $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  $conn = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
  // Jika gagal, tampilkan pesan error
  die("Koneksi Gagal: " . $e->getMessage());
}
