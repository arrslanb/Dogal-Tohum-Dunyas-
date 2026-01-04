<?php
session_start();

// 1. Yetki Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Yetkisiz giriş! Lütfen yönetici olarak giriş yapın.");
}

// 2. Veritabanı Bağlantısı (Admin klasöründe olduğumuz için bir geri çıkıyoruz)
require_once '../config/db.php';

// 3. ID Kontrolü
if (!isset($_GET['id'])) {
    die("Hata: Sipariş ID'si belirtilmedi.");
}

$order_id = intval($_GET['id']); 

try {
    // 4. Sipariş ve Müşteri Bilgilerini Çek
    // NOT: Müşteri adı users tablosundan, adres ise orders tablosundan geliyor.
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.phone, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Sipariş bulunamadı! (ID: $order_id)");
    }

    // Adres kontrolü (Veritabanından gelen adres)
    $address = !empty($order['address']) ? $order['address'] : 'Teslimat adresi girilmedi.';

    // 5. Ürünleri Çek
    // Ürün adı silinmiş olsa bile order_items tablosunda yedek isim yoksa products'tan çek
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı Hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Fişi #<?php echo 10000 + $order_id; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #000; padding: 20px; max-width: 800px; margin: 0 auto; background: white; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .info-box { display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 20px; }
        .box { width: 45%; border: 1px solid #000; padding: 10px; flex-grow: 1; }
        .box h3 { margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 14px; text-transform: uppercase; }
        .box p { margin: 5px 0; font-size: 13px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .total { text-align: right; margin-top: 20px; font-size: 20px; font-weight: bold; border-top: 2px solid #000; padding-top: 10px; }
        .footer { text-align: center; margin-top: 40px; font-size: 11px; border-top: 1px dashed #000; padding-top: 10px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            @page { margin: 1cm; }
        }
        
        .btn-print {
            padding: 12px 24px; background: #000; color: white; cursor: pointer; border: none; 
            font-size: 16px; font-weight: bold; border-radius: 5px; transition: 0.3s;
        }
        .btn-print:hover { background: #333; }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 500);">

    <div class="no-print" style="margin-bottom: 30px; text-align: center;">
        <button onclick="window.print()" class="btn-print">🖨️ YAZDIR</button>
        <button onclick="window.close()" class="btn-print" style="background: #ccc; color: #000; margin-left: 10px;">KAPAT</button>
    </div>

    <div class="header">
        <div class="logo">🌱 Doğal Tohum Dünyası</div>
        <p>Sipariş Özeti ve Teslimat Fişi</p>
        <p>Tarih: <?php echo date("d.m.Y H:i", strtotime($order['created_at'])); ?></p>
    </div>

    <div class="info-box">
        <div class="box">
            <h3>Alıcı Bilgileri</h3>
            <p><strong>Ad Soyad:</strong> <?php echo $order['full_name']; ?></p>
            <p><strong>Telefon:</strong> <?php echo $order['phone']; ?></p>
            <p><strong>E-Posta:</strong> <?php echo $order['email']; ?></p>
            <p><strong>Adres:</strong> <br><?php echo nl2br($address); ?></p>
        </div>
        <div class="box">
            <h3>Sipariş Detayı</h3>
            <p><strong>Sipariş No:</strong> #<?php echo 10000 + $order['id']; ?></p>
            <p><strong>Durum:</strong> <?php echo mb_strtoupper($order['status']); ?></p>
            <p><strong>Kargo Kodu:</strong> <?php echo !empty($order['tracking_code']) ? $order['tracking_code'] : '-'; ?></p>
            <p><strong>Kargo Firması:</strong> <?php echo !empty($order['cargo_company']) ? $order['cargo_company'] : '-'; ?></p>
            <p><strong>Ödeme:</strong> Kredi Kartı (Ödendi)</p>
        </div>
    </div>

    <?php if(!empty($order['order_note'])): ?>
    <div style="border: 1px dashed #000; padding: 10px; margin-bottom: 20px; font-size: 13px;">
        <strong>📝 Müşteri Notu:</strong> <?php echo $order['order_note']; ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Ürün Adı</th>
                <th style="text-align: center; width: 50px;">Adet</th>
                <th style="text-align: right; width: 100px;">Birim Fiyat</th>
                <th style="text-align: right; width: 100px;">Tutar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php echo isset($item['product_name']) ? $item['product_name'] : 'Ürün Silinmiş'; ?></td>
                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                <td style="text-align: right;"><?php echo number_format($item['price'], 2); ?> ₺</td>
                <td style="text-align: right;"><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ₺</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        GENEL TOPLAM: <?php echo number_format($order['total_price'], 2); ?> ₺
    </div>

    <div class="footer">
        <p>Doğayı sevdiğiniz için teşekkür ederiz!</p>
        <p>www.dogaltohumdunyasi.com | 0555 123 45 67</p>
    </div>

</body>
</html>