<?php
ob_start(); // Yönlendirme hatası olmasın
session_start();

// Hata raporlamayı kapat (Sessiz çalışsın)
error_reporting(0);

// Veritabanı bağlantısı (DB güncellemek için şart)
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id']; // Güvenlik için sayıya çevir
    $action = $_GET['action'];

    // Sepette bu ürün var mı kontrol et
    if (isset($_SESSION['cart'][$id])) {
        
        // --- 1. AZALTMA İŞLEMİ (DECREASE) ---
        if ($action == 'decrease') {
            if ($_SESSION['cart'][$id] > 1) {
                // A) Session'da azalt
                $_SESSION['cart'][$id]--;

                // B) Veritabanında azalt (Üye ise)
                if (isset($_SESSION['user_id']) && isset($pdo)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
                        $stmt->execute([$_SESSION['user_id'], $id]);
                    } catch (Exception $e) {}
                }

            } else {
                // Sayı 1 ise ve eksiye basıldıysa SİL
                // A) Session'dan sil
                unset($_SESSION['cart'][$id]);

                // B) Veritabanından sil (Üye ise)
                if (isset($_SESSION['user_id']) && isset($pdo)) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                        $stmt->execute([$_SESSION['user_id'], $id]);
                    } catch (Exception $e) {}
                }
            }
        } 
        
        // --- 2. ARTIRMA İŞLEMİ (INCREASE) ---
        elseif ($action == 'increase') {
            // A) Session'da artır
            $_SESSION['cart'][$id]++;

            // B) Veritabanında artır (Üye ise)
            if (isset($_SESSION['user_id']) && isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $id]);
                } catch (Exception $e) {}
            }
        }
    }
}

// İşlem bitince hemen sepete geri dön
header("Location: cart.php");
ob_end_flush();
exit;
?>