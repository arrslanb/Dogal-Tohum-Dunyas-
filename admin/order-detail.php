<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

if (!isset($_GET['id'])) { header("Location: orders.php"); exit; }
$order_id = $_GET['id'];
$message = "";
$msgType = "";

// --- 📥 NETGSM SMS FONKSİYONU ---
function sendOrderSMS($phone, $message) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if(strlen($phone) == 11 && substr($phone, 0, 1) == '0') {
        $phone = substr($phone, 1);
    }

    $api_user = "NETGSM_KULLANICI_ADI"; // Burayı doldur
    $api_pass = "NETGSM_SIFRE";         // Burayı doldur
    $api_header = "MESAJ_BASLIGI";      // Burayı doldur

    $msg = urlencode($message);
    $url = "https://api.netgsm.com.tr/sms/send/get/?usercode=$api_user&password=$api_pass&gsmno=$phone&message=$msg&msgheader=$api_header";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// --- GÜNCELLEME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $new_status = $_POST['status'];
    $tracking_code = trim($_POST['tracking_code']);
    $cargo_company = isset($_POST['cargo_company']) ? $_POST['cargo_company'] : ''; 

    try {
        $sql = "UPDATE orders SET status = ?, tracking_code = ?, cargo_company = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($sql);
        
        if ($updateStmt->execute([$new_status, $tracking_code, $cargo_company, $order_id])) {
            
            // --- 📱 SMS TETİKLEYİCİ ---
            $infoStmt = $pdo->prepare("SELECT u.email, u.phone, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
            $infoStmt->execute([$order_id]);
            $custInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);

            if($custInfo && !empty($custInfo['phone'])) {
                $smsText = "";
                $orderNum = 10000 + $order_id;

                switch ($new_status) {
                    case 'preparing': $smsText = "Sayın {$custInfo['full_name']}, #$orderNum nolu siparişiniz hazırlanmaya başlandı. 🌱"; break;
                    case 'shipped': $smsText = "Müjde! #$orderNum nolu siparişiniz $cargo_company ile kargoya verildi. Takip No: $tracking_code 🚚"; break;
                    case 'completed': $smsText = "Siparişiniz teslim edildi. Bizi tercih ettiğiniz için teşekkürler, bol hasatlar! 🏠✨"; break;
                    case 'cancelled': $smsText = "Siparişiniz maalesef iptal edilmiştir. Ücret iadesi bankanıza bağlı olarak yansıyacaktır. ❌"; break;
                }

                if($smsText != "") {
                    sendOrderSMS($custInfo['phone'], $smsText);
                }

                // --- 📧 SİPARİŞ DURUM MAİLİ (EKLEME YAPILAN KISIM) ---
                if (($new_status == 'shipped' || $new_status == 'completed') && !empty($custInfo['email'])) {
                    $phpMailerPath = '../includes/PHPMailer/PHPMailer.php';
                    if(file_exists($phpMailerPath)) {
                        require_once '../includes/PHPMailer/Exception.php';
                        require_once '../includes/PHPMailer/PHPMailer.php';
                        require_once '../includes/PHPMailer/SMTP.php';

                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'rosadellacan@gmail.com'; 
                            $mail->Password   = 'smsuepcqiaodpylq'; 
                            $mail->SMTPSecure = 'ssl'; 
                            $mail->Port       = 465;
                            $mail->CharSet    = 'UTF-8';

                            $mail->setFrom('rosadellacan@gmail.com', 'Doğal Tohum Dünyası');
                            $mail->addAddress($custInfo['email'], $custInfo['full_name']);
                            $mail->isHTML(true);

                            if ($new_status == 'shipped') {
                                $mail->Subject = "Siparişiniz Kargoya Verildi! 🌱📦";
                                $mailContent = "<h2>Merhaba {$custInfo['full_name']},</h2><p>Siparişiniz kargoya verildi!</p><p><strong>Kargo:</strong> $cargo_company<br><strong>Takip No:</strong> $tracking_code</p>";
                            } else {
                                $mail->Subject = "Siparişiniz Teslim Edildi! 🌻";
                                $mailContent = "<h2>Siparişiniz Ulaştı!</h2><p>Tohumlarınız size teslim edildi. Bereketli olsun!</p>";
                            }
                            $mail->Body = $mailContent;
                            $mail->send();
                        } catch (Exception $e) { /* Hata varsa sessizce devam et */ }
                    }
                }
            }

            $message = "Sipariş güncellendi ve müşteriye bildirimler gönderildi! ✅";
            $msgType = "success";
        } else {
            $message = "Güncelleme yapılamadı! ❌";
            $msgType = "error";
        }
    } catch (PDOException $e) {
        $message = "Hata: " . $e->getMessage();
        $msgType = "error";
    }
}

// Verileri Çek
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { die("Sipariş bulunamadı."); }

$itemsStmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$order['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sipariş #<?php echo $order_id; ?> Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' }, fontFamily: { 'sans': ['Poppins', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-gray-100 font-sans pb-10 text-gray-800">

<div class="bg-nature-dark text-white p-4 sticky top-0 z-50 shadow-md flex justify-between items-center lg:hidden">
    <a href="orders.php" class="flex items-center gap-2 font-bold text-sm bg-green-800 px-3 py-2 rounded">
        <span>⬅</span> Geri
    </a>
    <span class="font-bold text-lg">Sipariş #<?php echo $order_id; ?></span>
    <a href="order-print.php?id=<?php echo $order['id']; ?>" target="_blank" class="bg-white text-nature-dark px-3 py-2 rounded text-xs font-bold">
        🖨️ Yazdır
    </a>
</div>

<div class="flex h-full">
    <div class="bg-nature-dark text-white w-64 flex-shrink-0 hidden lg:flex flex-col h-screen sticky top-0">
        <div class="h-16 flex items-center justify-center border-b border-green-800"><span class="text-2xl font-bold tracking-wider">DoğalPanel</span></div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📊 Panel</a>
                <a href="orders.php" class="block px-4 py-2 bg-green-800 rounded text-white font-bold transition">📦 Siparişler</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🌱 Ürünler</a>
                <a href="../index.php" class="block px-4 py-2 mt-10 hover:bg-green-700 rounded text-green-200 transition">🌐 Siteye Dön</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-8 max-w-6xl mx-auto w-full">
        <div class="hidden lg:flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Sipariş Detayı <span class="text-nature-green">#<?php echo $order_id; ?></span></h1>
            <div class="flex gap-3">
                <a href="orders.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition">Geri Dön</a>
                <a href="order-print.php?id=<?php echo $order['id']; ?>" target="_blank" class="bg-nature-dark text-white px-4 py-2 rounded hover:bg-green-800 transition shadow">Fiş Yazdır</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-lg font-bold text-center <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 lg:order-2">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden sticky top-20">
                    <div class="bg-nature-dark text-white p-4 font-bold flex items-center gap-2">
                        <span>⚡</span> Sipariş İşlemleri
                    </div>
                    <div class="p-6">
                        <form action="" method="POST" class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Sipariş Durumu</label>
                                <div class="relative">
                                    <select name="status" class="w-full appearance-none bg-gray-50 border-2 border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-nature-green font-bold transition">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>🟡 Onay Bekliyor</option>
                                        <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>🔵 Hazırlanıyor</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🟣 Kargoya Verildi</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>🟢 Teslim Edildi</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>🔴 İptal Edildi</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Kargo Firması</label>
                                <select name="cargo_company" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 h-12 transition focus:border-nature-green">
                                    <option value="">Seçiniz...</option>
                                    <?php 
                                        $companies = ["Yurtiçi Kargo", "Aras Kargo", "MNG Kargo", "PTT Kargo", "Sürat Kargo", "UPS Kargo"];
                                        $currentCargo = isset($order['cargo_company']) ? $order['cargo_company'] : '';
                                        foreach($companies as $comp) {
                                            $selected = ($currentCargo == $comp) ? 'selected' : '';
                                            echo "<option value='$comp' $selected>$comp</option>";
                                        }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Takip Kodu</label>
                                <input type="text" name="tracking_code" value="<?php echo isset($order['tracking_code']) ? $order['tracking_code'] : ''; ?>" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 font-mono text-lg h-12 focus:ring-2 focus:ring-nature-green focus:border-transparent outline-none transition" 
                                       placeholder="Takip No Giriniz">
                            </div>

                            <button type="submit" name="update_order" class="w-full bg-nature-green text-white font-bold py-4 rounded-xl shadow-md hover:bg-nature-dark active:scale-95 transition-transform flex items-center justify-center gap-2 text-lg">
                                 Güncelle ve Bildirim Gönder
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 lg:order-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-700">🛒 Sipariş İçeriği</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach($items as $item): ?>
                        <div class="p-4 flex items-center gap-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg border border-gray-200 flex-shrink-0 overflow-hidden">
                                <?php if(!empty($item['product_image'])): ?>
                                    <img src="../uploads/<?php echo $item['product_image']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">Resim Yok</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-sm md:text-base"><?php echo $item['product_name']; ?></h4>
                                <div class="text-sm text-gray-500 mt-1">
                                    <?php echo $item['quantity']; ?> Adet x <?php echo number_format($item['price'], 2); ?> ₺
                                </div>
                            </div>
                            <div class="font-bold text-nature-dark text-base md:text-lg">
                                <?php echo number_format($item['price'] * $item['quantity'], 2); ?> ₺
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bg-gray-50 p-4 text-right border-t border-gray-100">
                        <span class="text-gray-600 text-sm mr-2">Genel Toplam:</span>
                        <span class="text-2xl font-extrabold text-nature-dark"><?php echo number_format($order['total_price'], 2); ?> ₺</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-700">👤 Müşteri & Teslimat</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex flex-col md:flex-row md:justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">Ad Soyad</p>
                                <p class="font-medium text-gray-800 text-lg"><?php echo $user['full_name']; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">Telefon</p>
                                <a href="tel:<?php echo $user['phone']; ?>" class="font-medium text-blue-600 hover:underline text-lg"><?php echo $user['phone']; ?></a>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">E-Posta</p>
                                <a href="mailto:<?php echo $user['email']; ?>" class="font-medium text-blue-600 hover:underline"><?php echo $user['email']; ?></a>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Teslimat Adresi</p>
                            <p class="text-gray-700 leading-relaxed bg-gray-50 p-3 rounded border border-gray-200">
                                <?php echo !empty($order['address']) ? nl2br($order['address']) : '<span class="text-red-400 italic">Adres bilgisi bulunamadı.</span>'; ?>
                            </p>
                        </div>
                        <?php if(!empty($order['order_note'])): ?>
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1 text-orange-500">Sipariş Notu</p>
                            <p class="text-gray-700 italic bg-orange-50 p-3 rounded border border-orange-100">
                                "<?php echo $order['order_note']; ?>"
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>