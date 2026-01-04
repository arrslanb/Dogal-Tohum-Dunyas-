<?php
ob_start(); // Yönlendirme hatalarını önler
session_start();

// Veritabanı bağlantısı (DB'den silmek için şart)
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
}

// ID gelmiş mi kontrol et
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id']; // Güvenlik için sayıya çevir

    // 1. SESSION'DAN SİL (Herkes için)
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    // 2. VERİTABANINDAN SİL (Sadece Üyeler için)
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        try {
            $user_id = $_SESSION['user_id'];
            
            // O kullanıcının sepetinden o ürünü sil
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } catch (Exception $e) {
            // Hata olursa sessiz kal, session zaten sildi.
        }
    }
}

// Sepete geri dön
header("Location: cart.php");
ob_end_flush();
exit;
?>