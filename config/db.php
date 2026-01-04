<?php
// LOCALHOST AYARLARI (Bilgisayarın İçin)
$host = 'localhost';
$dbname = 'tohum_db'; // Senin yerel veritabanı adın neyse o
$username = 'root';
$password = ''; // XAMPP'te şifre genelde boştur

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>