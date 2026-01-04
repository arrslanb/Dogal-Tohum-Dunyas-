<?php
require_once 'includes/header.php';
// Mail kütüphanesi hazırlığı
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

// Güvenlik Kontrolleri
if (!isset($_SESSION['user_id'])) { echo "<script>window.location.href='login.php';</script>"; exit; }
if (empty($_SESSION['cart'])) { echo "<script>window.location.href='products.php';</script>"; exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// NULL Hatası Önleyici
$user_fullname = $user['full_name'] ?? '';
$user_phone = $user['phone'] ?? '';
$user_address = $user['address'] ?? '';

// --- 1. SEPET TOPLAMINI HESAPLA ---
$cartItems = []; 
$subTotal = 0; 

if (isset($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    if (!empty($ids)) {
        $stmtProducts = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
        $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $prod) {
            $qty = $_SESSION['cart'][$prod['id']];
            $lineTotal = $prod['price'] * $qty;
            $subTotal += $lineTotal;
            $prod['qty'] = $qty;
            $cartItems[] = $prod;
        }
    }
}

// --- 2. KUPON VE İNDİRİM HESAPLAMA (GARANTİ YÖNTEM) ---
$discountAmount = 0;
$couponCode = '';

// Yöntem A: Önce Session'daki hazır indirime bak (Sepette hesaplananı koru)
if (isset($_SESSION['discount_amount']) && $_SESSION['discount_amount'] > 0) {
    $discountAmount = floatval($_SESSION['discount_amount']);
    // Eğer session'da kod varsa onu da al, yoksa boşver
    $couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : 'KUPON';
}

// Yöntem B: Eğer Session'da indirim YOKSA ama Kod VARSA, veritabanından hesapla (Yedek plan)
if ($discountAmount == 0 && isset($_SESSION['coupon_code']) && !empty($_SESSION['coupon_code'])) {
    $couponCode = strtoupper($_SESSION['coupon_code']);
    
    // Admin panel yapına uygun sorgu
    $stmtCoupon = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmtCoupon->execute([$couponCode]);
    $coupon = $stmtCoupon->fetch(PDO::FETCH_ASSOC);

    if ($coupon) {
        $minSpend = isset($coupon['min_spend']) ? floatval($coupon['min_spend']) : 0;
        
        if ($subTotal >= $minSpend) {
            $type = $coupon['discount_type'];   // 'percent' veya 'fixed'
            $value = floatval($coupon['discount_value']);

            if ($type == 'percent') {
                $discountAmount = ($subTotal * $value) / 100;
            } else {
                $discountAmount = $value;
            }
            
            // Hesaplanan değeri session'a kaydet ki kaybolmasın
            $_SESSION['discount_amount'] = $discountAmount;
        }
    }
}

// Son Fiyat Hesapla
$finalTotal = $subTotal - $discountAmount;
if ($finalTotal < 0) { $finalTotal = 0; }
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center gap-3 mb-8">
            <a href="cart.php" class="text-gray-400 hover:text-gray-600 text-2xl transition">←</a>
            <h1 class="text-3xl font-bold text-gray-800">Ödemeyi Tamamla</h1>
        </div>

        <form action="payment-process.php" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="bg-green-100 text-nature-green w-8 h-8 flex items-center justify-center rounded-full text-sm">1</span>
                        Teslimat Bilgileri
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ad Soyad</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_fullname); ?>" required class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Telefon</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">İl</label>
                            <select name="city" id="citySelect" required class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition bg-white">
                                <option value="">Şehir Seçiniz...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">İlçe</label>
                            <input type="text" name="district" placeholder="Örn: Kadıköy" required class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Açık Adres (Mahalle, Sokak, No)</label>
                        <textarea name="address" rows="2" required class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition"><?php echo htmlspecialchars($user_address); ?></textarea>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="bg-green-100 text-nature-green w-8 h-8 flex items-center justify-center rounded-full text-sm">2</span>
                        Sipariş Notu
                    </h2>
                    <textarea name="order_note" rows="2" placeholder="Kuryeye notunuz..." class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none transition"></textarea>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="bg-green-100 text-nature-green w-8 h-8 flex items-center justify-center rounded-full text-sm">3</span>
                        Ödeme Yöntemi
                    </h2>
                    <div class="flex items-center gap-4 border-2 border-nature-green bg-green-50 p-5 rounded-xl cursor-pointer relative overflow-hidden transition hover:shadow-md">
                        <div class="absolute top-0 right-0 bg-nature-green text-white text-xs px-2 py-1 rounded-bl-lg">Seçili</div>
                        <div class="w-6 h-6 rounded-full border-4 border-nature-green flex items-center justify-center bg-white"></div>
                        <div class="flex-1">
                            <span class="font-bold text-gray-900 block">Kredi / Banka Kartı</span>
                            <span class="text-xs text-gray-500">PayTR güvencesiyle 3D Secure ödeme</span>
                        </div>
                        <div class="flex gap-2 text-2xl">💳</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Sipariş Özeti</h2>
                    
                    <div class="space-y-4 mb-6 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                        <?php foreach($cartItems as $item): ?>
                        <div class="flex items-center gap-3 border-b border-gray-50 pb-3 last:border-0">
                            <img src="uploads/<?php echo $item['image']; ?>" class="w-14 h-14 rounded-lg object-cover border bg-gray-50">
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-gray-800 line-clamp-1"><?php echo $item['name']; ?></h4>
                                <p class="text-xs text-gray-500"><?php echo $item['qty']; ?> x <?php echo $item['price']; ?> ₺</p>
                            </div>
                            <span class="font-bold text-gray-700"><?php echo number_format($item['qty'] * $item['price'], 2); ?> ₺</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-dashed border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Ara Toplam</span>
                            <span><?php echo number_format($subTotal, 2); ?> ₺</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>Kargo</span>
                            <span class="text-green-600 font-bold">Bedava</span>
                        </div>

                        <?php if($discountAmount > 0): ?>
                        <div class="flex justify-between items-center bg-green-50 p-3 rounded-lg border border-green-200 text-sm animate-pulse">
                            <div class="flex items-center gap-2">
                                <span class="bg-green-200 text-green-700 p-1 rounded">🎟️</span>
                                <span class="text-green-700 font-semibold">İndirim (<?php echo htmlspecialchars($couponCode); ?>)</span>
                            </div>
                            <span class="text-green-700 font-bold">-<?php echo number_format($discountAmount, 2); ?> ₺</span>
                        </div>
                        <?php endif; ?>

                        <div class="flex justify-between items-end border-t border-gray-200 pt-4 mt-2">
                            <span class="text-lg font-bold text-gray-800">Toplam</span>
                            <div class="text-right">
                                <?php if($discountAmount > 0): ?>
                                    <span class="block text-sm text-gray-400 line-through decoration-red-400 decoration-2"><?php echo number_format($subTotal, 2); ?> ₺</span>
                                <?php endif; ?>
                                <span class="text-2xl font-extrabold text-nature-dark"><?php echo number_format($finalTotal, 2); ?> ₺</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-nature-green text-white py-4 rounded-xl font-bold text-lg mt-6 hover:bg-nature-dark transition shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        Ödemeye Geç 🔒
                    </button>
                    
                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-400">Güvenli ödeme altyapısı ile korunmaktadır.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // 81 İlin Tam Listesi (Öncelikli İller + Alfabetik)
    const cities = [
        "İstanbul", "Ankara", "İzmir", "Kocaeli", // Öncelikli İller
        "Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Aksaray", "Amasya", "Antalya", "Ardahan", "Artvin", "Aydın", 
        "Balıkesir", "Bartın", "Batman", "Bayburt", "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", 
        "Çanakkale", "Çankırı", "Çorum", 
        "Denizli", "Diyarbakır", "Düzce", 
        "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", 
        "Gaziantep", "Giresun", "Gümüşhane", 
        "Hakkari", "Hatay", 
        "Iğdır", "Isparta", 
        "Kahramanmaraş", "Karabük", "Karaman", "Kars", "Kastamonu", "Kayseri", "Kırıkkale", "Kırklareli", "Kırşehir", "Kilis", "Konya", "Kütahya", 
        "Malatya", "Manisa", "Mardin", "Mersin", "Muğla", "Muş", 
        "Nevşehir", "Niğde", 
        "Ordu", "Osmaniye", 
        "Rize", 
        "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", 
        "Şanlıurfa", "Şırnak", 
        "Tekirdağ", "Tokat", "Trabzon", "Tunceli", 
        "Uşak", 
        "Van", 
        "Yalova", "Yozgat", 
        "Zonguldak"
    ];

    const select = document.getElementById("citySelect");
    cities.forEach(city => {
        let option = document.createElement("option");
        option.value = city;
        option.text = city;
        select.appendChild(option);
    });
</script>

<?php require_once 'includes/footer.php'; ?>