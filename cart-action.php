<?php
ob_start(); // Yönlendirme hatalarını önlemek için tampon başlat
session_start();

// Hata bastırma (Kullanıcıya hata gösterme, sessizce devam et)
error_reporting(0);

// Veritabanı bağlantısı (Varsa dahil et, yoksa patlama)
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
}

// Action parametresi var mı kontrol et
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ---------------------------------------------------------
// 1. DURUM: ÜRÜN SİLME (Remove)
// ---------------------------------------------------------
if ($action == 'remove' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id']; // Güvenlik için sayıya çevir

    // A) Session'dan Sil (Her zaman çalışır)
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    // B) Veritabanından Sil (Sadece üye giriş yapmışsa ve DB varsa)
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
        } catch (Exception $e) {
            // DB hatası olursa görmezden gel, session zaten sildi.
        }
    }
}

// ---------------------------------------------------------
// 2. DURUM: ADET GÜNCELLEME (Update)
// ---------------------------------------------------------
elseif ($action == 'update' && isset($_POST['quantities'])) {
    
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        // Adet 0 veya daha küçükse ürünü sil
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            
            if (isset($_SESSION['user_id']) && isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $product_id]);
                } catch (Exception $e) {}
            }
        } 
        // Adet pozitifse güncelle
        else {
            $_SESSION['cart'][$product_id] = $quantity;

            if (isset($_SESSION['user_id']) && isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
                } catch (Exception $e) {}
            }
        }
    }
}

// ---------------------------------------------------------
// 3. DURUM: SEPETİ TEMİZLE (Clear)
// ---------------------------------------------------------
elseif ($action == 'clear') {
    
    // A) Session'ı Boşalt
    unset($_SESSION['cart']);

    // B) Veritabanını Boşalt
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (Exception $e) {}
    }
}

// İşlem bitince Sepet sayfasına geri dön
header("Location: cart.php");
ob_end_flush();
exit;
?>