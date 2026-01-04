<?php
session_start();
require_once 'config/db.php';

// Güvenlik: Giriş yapmayan favori ekleyemez
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'toggle'; // toggle: varsa sil yoksa ekle

if ($product_id > 0) {
    // Önce bu ürün zaten listede mi?
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetchColumn();

    if ($action == 'remove') {
        // Kesin silme komutu (Favorilerim sayfasındaki çöp kutusu için)
        if ($exists) {
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $del->execute([$user_id, $product_id]);
        }
    } else {
        // Toggle (Ürün detaydaki kalp butonu için)
        if ($exists) {
            // Varsa çıkar
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $del->execute([$user_id, $product_id]);
        } else {
            // Yoksa ekle
            $add = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $add->execute([$user_id, $product_id]);
        }
    }
}

// İşlem bitince geldiği sayfaya geri dön
if(isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: wishlist.php");
}
exit;
?>