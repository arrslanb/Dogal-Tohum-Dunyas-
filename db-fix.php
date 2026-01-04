<?php
// Hataları göster
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Veritabanı Tamir Aracı 🛠️</h1>";

// Config dosyasını bulmaya çalış
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
    echo "<p style='color:green'>✅ config/db.php bulundu.</p>";
} else {
    die("<p style='color:red'>❌ config/db.php BULUNAMADI! Bu dosya index.php ile aynı yerde olmalı.</p>");
}

if (!isset($pdo)) {
    die("<p style='color:red'>❌ Veritabanına bağlanılamadı. config/db.php içindeki şifreleri kontrol et.</p>");
} else {
    echo "<p style='color:green'>✅ Veritabanı bağlantısı başarılı.</p>";
}

// Tabloyu oluştur
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;";

try {
    $pdo->exec($sql);
    echo "<h2 style='color:green'>🎉 TEBRİKLER! Cart tablosu oluşturuldu.</h2>";
    echo "<p>Artık localhost hatası çözüldü.</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Hata: " . $e->getMessage() . "</h2>";
}
?>