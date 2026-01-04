<?php
// Çıktı tamponlamayı başlat (Yönlendirme hatalarını engeller)
ob_start();
session_start();

// Hata raporlamayı kapat (Kullanıcıya karmaşık kodlar gösterme)
error_reporting(0);

// Veritabanı bağlantısı
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
} else {
    // DB yoksa bile kod patlamasın, session ile devam etsin
    $pdo = null;
}

// --- ID YAKALAMA (AKILLI KONTROL) ---
$product_id = null;

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
} elseif (isset($_POST['id'])) {
    $product_id = $_POST['id'];
} elseif (isset($_GET['id'])) {
    $product_id = $_GET['id'];
}

// Güvenlik: ID'yi tam sayıya çevir (Hacker savar)
$product_id = (int)$product_id;

// ID yoksa veya 0 ise işlem yapma, sepete geri dön
if ($product_id <= 0) {
    header("Location: products.php"); // Veya index.php
    exit;
}

// Adet kontrolü
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
if ($quantity < 1) $quantity = 1;

// --- 1. ADIM: SESSION İŞLEMLERİ (Herkes İçin) ---
// Sepet dizisi yoksa oluştur
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ürün sepette varsa artır, yoksa ekle
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// --- 2. ADIM: VERİTABANI İŞLEMLERİ (Sadece Üyeler İçin) ---
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $user_id = $_SESSION['user_id'];

        // Sepette var mı kontrol et
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // VARSA: Üstüne ekle (UPDATE)
            $update = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $update->execute([$quantity, $user_id, $product_id]);
        } else {
            // YOKSA: Yeni satır aç (INSERT)
            $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->execute([$user_id, $product_id, $quantity]);
        }
    } catch (Exception $e) {
        // DB hatası olursa siteyi bozma, sessizce devam et.
        // Session tarafı zaten işi halletti.
    }
}

// İşlem başarılı, sepete yönlendir
header("Location: cart.php");
ob_end_flush();
exit;
?>