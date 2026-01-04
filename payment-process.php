<?php
session_start();
require_once 'config/db.php';

// --- 📧 PHPMAILER KÜTÜPHANESİ ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

// Güvenlik
if (empty($_SESSION['cart'])) { header("Location: products.php"); exit; }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];

// 1. KULLANICI BİLGİLERİ
$stmtUser = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$user_email = $user['email'];
$user_name = $user['full_name'];

// 2. FORM VERİLERİ
$order_note = isset($_POST['order_note']) ? htmlspecialchars($_POST['order_note']) : '';
$raw_address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '';
$city = isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '';
$district = isset($_POST['district']) ? htmlspecialchars($_POST['district']) : '';
$full_address = "$raw_address - $district / $city";

// 3. SEPET HESAPLA
$cart_total = 0; 
$dbItems = [];
$ids = implode(',', array_keys($_SESSION['cart']));
$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $prod) {
    $qty = $_SESSION['cart'][$prod['id']];
    $price = $prod['price'];
    $cart_total += $price * $qty;
    $dbItems[] = ['product_id' => $prod['id'], 'quantity' => $qty, 'price' => $price, 'name' => $prod['name']];
}

// 4. İNDİRİM HESAPLA
$final_price = $cart_total;
if (isset($_SESSION['discount_amount']) && $_SESSION['discount_amount'] > 0) {
    $final_price = $cart_total - $_SESSION['discount_amount'];
}
if ($final_price < 0) { $final_price = 0; }

// 5. SİPARİŞİ KAYDET
try {
    $pdo->beginTransaction();

    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_price, address, order_note, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmtOrder->execute([$user_id, $final_price, $full_address, $order_note]);
    $order_id = $pdo->lastInsertId();

    foreach ($dbItems as $item) {
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        
        $stmtStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmtStock->execute([$item['quantity'], $item['product_id']]);
    }

    $stmtClear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmtClear->execute([$user_id]);
    unset($_SESSION['cart']);
    unset($_SESSION['discount_amount']);
    unset($_SESSION['coupon_code']);

    $pdo->commit();

    // --- 📧 MAİL GÖNDERİMİ (DÜZELTİLDİ) ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rosadellacan@gmail.com'; 
        $mail->Password   = 'smsuepcqiaodpylq'; 
        // KRİTİK DÜZELTME: 465 portu için SMTPSecure 'ssl' olmalı
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('rosadellacan@gmail.com', 'Doğal Tohum Dünyası');
        $mail->addAddress($user_email, $user_name);

        $mail->isHTML(true);
        $mail->Subject = "Siparişiniz Alındı! 🌱 - Sipariş No: #$order_id";

        $mailBody = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='background-color: #ffffff; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background-color: #059669; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin:0;'>Doğal Tohum Dünyası 🌱</h1>
                </div>
                <div style='padding: 20px; color: #333;'>
                    <h2>Merhaba $user_name, Siparişin İçin Teşekkürler!</h2>
                    <p>Sipariş numaran: <strong>#$order_id</strong></p>
                    <p>En kısa sürede ürünlerini hazırlayıp kargoya teslim edeceğiz.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p><strong>Toplam Tutar:</strong> " . number_format($final_price, 2) . " ₺</p>
                    <p><strong>Adres:</strong> $full_address</p>
                </div>
            </div>
        </div>";

        $mail->Body = $mailBody;
        $mail->send();

    } catch (Exception $e) {
        // Mail gitmezse bile ödeme ekranı beyaz kalmasın, devam etsin.
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die("<div style='color:red; text-align:center; padding:50px;'><h1>Hata!</h1><p>Sipariş oluşturulamadı.</p></div>");
}

$masked_card = "**** **** **** " . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Güvenli Ödeme</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-blue-900 p-6 text-white flex justify-between items-center">
            <h2 class="font-bold">3D Secure Doğrulama</h2>
            <span>🔒</span>
        </div>
        <div class="p-8 space-y-6">
            <div class="flex justify-between border-b pb-4"><span class="text-gray-500">İşyeri</span><span class="font-bold">Doğal Tohum</span></div>
            <div class="flex justify-between border-b pb-4"><span class="text-gray-500">Kart No</span><span class="font-mono"><?php echo $masked_card; ?></span></div>
            <div class="bg-green-50 p-4 rounded flex justify-between items-center">
                <span class="text-green-800 font-bold">Tutar</span>
                <span class="text-2xl font-bold text-green-700"><?php echo number_format($final_price, 2); ?> ₺</span>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 block mb-2">SMS Şifresi</label>
                <input type="text" value="123456" class="w-full border-2 border-gray-300 rounded-lg p-3 text-center text-xl font-bold" readonly>
            </div>
            <a href="payment-success.php?order_id=<?php echo $order_id; ?>" class="block w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-center hover:bg-blue-700 transition">Ödemeyi Onayla</a>
        </div>
    </div>
</body>
</html>