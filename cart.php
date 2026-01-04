<?php 
require_once 'includes/header.php'; 

// --- GÜVENLİK VE SENKRONİZASYON ---
if (isset($_SESSION['user_id'])) {
    if (!isset($pdo)) { require_once 'config/db.php'; }
    try {
        $uid = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
        $stmt->execute([$uid]);
        $dbItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
        foreach ($dbItems as $item) {
            $_SESSION['cart'][$item['product_id']] = $item['quantity'];
        }
    } catch (Exception $e) {}
}

// --- KUPON İŞLEMLERİ ---
$couponMessage = "";
$couponError = "";

if (isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code']));
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($coupon) {
        $tempTotal = 0;
        if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
            if($ids) {
                $stmtP = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)");
                while($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                    $tempTotal += $row['price'] * $_SESSION['cart'][$row['id']];
                }
            }
        }

        $minSpend = isset($coupon['min_spend']) ? floatval($coupon['min_spend']) : 0;

        if ($tempTotal >= $minSpend) {
            $_SESSION['coupon_code'] = $code;
            $discountVal = 0;
            if ($coupon['discount_type'] == 'percent') {
                $discountVal = ($tempTotal * floatval($coupon['discount_value'])) / 100;
            } else {
                $discountVal = floatval($coupon['discount_value']);
            }
            $_SESSION['discount_amount'] = $discountVal;
            $couponMessage = "Kupon uygulandı! 🎉";
        } else {
            $couponError = "Minimum sepet tutarı: " . $minSpend . " ₺ olmalıdır.";
        }
    } else {
        $couponError = "Geçersiz kupon kodu.";
    }
}

if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon_code']);
    unset($_SESSION['discount_amount']);
    echo "<script>window.location.href='cart.php';</script>";
    exit;
}

// --- SEPET VERİLERİNİ ÇEK ---
$emptyCart = empty($_SESSION['cart']);
$cartItems = [];
$totalPrice = 0;

if (!$emptyCart) {
    $idsArr = array_map('intval', array_keys($_SESSION['cart']));
    $ids = implode(',', $idsArr);
    
    if (!empty($ids)) {
        $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            foreach ($products as $prod) {
                $qty = $_SESSION['cart'][$prod['id']];
                $lineTotal = $prod['price'] * $qty;
                $totalPrice += $lineTotal;
                $prod['qty'] = $qty;
                $prod['line_total'] = $lineTotal;
                $cartItems[] = $prod;
            }
        } else { $emptyCart = true; }
    } else { $emptyCart = true; }
}

if (empty($cartItems)) { $emptyCart = true; }

$discountAmount = isset($_SESSION['discount_amount']) ? $_SESSION['discount_amount'] : 0;
$couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : '';

if($discountAmount > 0 && isset($_SESSION['coupon_code'])) {
    $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmtC->execute([$_SESSION['coupon_code']]);
    $cp = $stmtC->fetch(PDO::FETCH_ASSOC);
    if($cp) {
        if ($cp['discount_type'] == 'percent') {
            $discountAmount = ($totalPrice * floatval($cp['discount_value'])) / 100;
        } else {
            $discountAmount = floatval($cp['discount_value']);
        }
        $_SESSION['discount_amount'] = $discountAmount;
    }
}

$finalPrice = $totalPrice - $discountAmount;
if ($finalPrice < 0) $finalPrice = 0;
?>

<div class="bg-gray-50 min-h-screen py-6 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 md:mb-8 flex items-center gap-3">
            <span>🛒</span> Alışveriş Sepetim
        </h1>

        <?php if (!$emptyCart): ?>
            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="lg:w-2/3">
                    <div class="hidden md:block bg-white rounded-xl shadow-md overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="p-4 text-gray-600">Ürün</th>
                                    <th class="p-4 text-center text-gray-600">Fiyat</th>
                                    <th class="p-4 text-center text-gray-600">Adet</th>
                                    <th class="p-4 text-center text-gray-600">Toplam</th>
                                    <th class="p-4 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td class="p-4 flex items-center gap-4">
                                        <img src="uploads/<?php echo $item['image']; ?>" class="w-16 h-16 rounded object-cover border">
                                        <div class="max-w-[200px] break-words">
                                            <a href="product-detail.php?id=<?php echo $item['id']; ?>" class="font-bold text-gray-800 hover:text-nature-green"><?php echo $item['name']; ?></a>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center text-gray-600 whitespace-nowrap"><?php echo number_format($item['price'], 2); ?> ₺</td>
                                    <td class="p-4 text-center">
                                        <form action="cart-action.php?action=update" method="POST" class="flex items-center justify-center gap-1">
                                            <button type="submit" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo max(1, $item['qty'] - 1); ?>" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full font-bold transition flex items-center justify-center">-</button>
                                            <span class="w-8 text-center font-bold text-gray-800"><?php echo $item['qty']; ?></span>
                                            <button type="submit" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['qty'] + 1; ?>" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full font-bold transition flex items-center justify-center">+</button>
                                        </form>
                                    </td>
                                    <td class="p-4 text-center font-bold text-nature-green whitespace-nowrap"><?php echo number_format($item['line_total'], 2); ?> ₺</td>
                                    <td class="p-4 text-center">
                                        <a href="cart-action.php?action=remove&id=<?php echo $item['id']; ?>" class="text-red-400 hover:text-red-600 text-2xl font-bold transition">×</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-4">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="bg-white p-4 rounded-xl shadow-md relative border border-gray-100">
                            <a href="cart-action.php?action=remove&id=<?php echo $item['id']; ?>" class="absolute top-2 right-3 text-red-400 text-2xl font-bold">×</a>
                            <div class="flex items-center gap-4">
                                <img src="uploads/<?php echo $item['image']; ?>" class="w-20 h-20 rounded object-cover border">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-800 text-sm leading-tight mb-1"><?php echo $item['name']; ?></h4>
                                    <p class="text-nature-green font-bold text-sm mb-2"><?php echo number_format($item['price'], 2); ?> ₺ / adet</p>
                                    
                                    <div class="flex items-center justify-between mt-2">
                                        <form action="cart-action.php?action=update" method="POST" class="flex items-center gap-3">
                                            <button type="submit" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo max(1, $item['qty'] - 1); ?>" class="w-8 h-8 bg-gray-100 rounded-full font-bold">-</button>
                                            <span class="font-bold text-gray-800"><?php echo $item['qty']; ?></span>
                                            <button type="submit" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['qty'] + 1; ?>" class="w-8 h-8 bg-gray-100 rounded-full font-bold">+</button>
                                        </form>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-400 block">Toplam</span>
                                            <span class="font-bold text-nature-dark text-base"><?php echo number_format($item['line_total'], 2); ?> ₺</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <a href="products.php" class="text-gray-500 hover:text-gray-800 transition font-medium">← Alışverişe Devam Et</a>
                        <a href="cart-action.php?action=clear" class="text-red-500 font-bold hover:text-red-700 transition px-4 py-2 border border-red-100 rounded-lg bg-red-50 md:bg-transparent">Sepeti Temizle</a>
                    </div>
                </div>

                <div class="lg:w-1/3 space-y-6">
                    <div class="bg-white p-5 md:p-6 rounded-xl shadow-md border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">🎟️ İndirim Kuponu</h3>
                        <?php if($discountAmount > 0): ?>
                            <div class="bg-green-50 border border-green-200 p-3 rounded-lg flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-green-700"><?php echo $couponCode; ?></span>
                                    <span class="text-[10px] text-green-600 block uppercase font-bold tracking-wider">Aktif</span>
                                </div>
                                <a href="cart.php?remove_coupon=1" class="text-red-500 text-sm font-bold hover:underline">Kaldır</a>
                            </div>
                        <?php else: ?>
                            <form action="" method="POST" class="flex gap-2">
                                <input type="text" name="coupon_code" placeholder="KOD" class="w-full border p-2.5 rounded-lg uppercase focus:ring-1 focus:ring-nature-green outline-none text-sm">
                                <button type="submit" name="apply_coupon" class="bg-gray-800 text-white px-4 rounded-lg font-bold hover:bg-gray-700 transition text-sm">Uygula</button>
                            </form>
                            <?php if($couponMessage) echo "<p class='text-green-600 text-xs mt-2 font-medium'>$couponMessage</p>"; ?>
                            <?php if($couponError) echo "<p class='text-red-500 text-xs mt-2 font-medium'>$couponError</p>"; ?>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 sticky top-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 tracking-tight">Sipariş Özeti</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Ara Toplam</span>
                                <span><?php echo number_format($totalPrice, 2); ?> ₺</span>
                            </div>
                            
                            <?php if($discountAmount > 0): ?>
                            <div class="flex justify-between text-green-600 text-sm font-bold">
                                <span>İndirim</span>
                                <span>-<?php echo number_format($discountAmount, 2); ?> ₺</span>
                            </div>
                            <?php endif; ?>

                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Kargo</span>
                                <span class="text-green-600 font-bold uppercase text-[10px] bg-green-50 px-2 py-1 rounded">Bedava</span>
                            </div>
                            
                            <div class="border-t pt-3 flex justify-between items-center">
                                <span class="text-gray-800 font-bold">Ödenecek Tutar</span>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-nature-dark block leading-none"><?php echo number_format($finalPrice, 2); ?> ₺</span>
                                    <span class="text-[10px] text-gray-400">KDV Dahil</span>
                                </div>
                            </div>
                        </div>

                        <a href="checkout.php" class="block w-full bg-nature-green text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-nature-dark transition shadow-lg flex items-center justify-center gap-2">
                            Ödemeye Geç 🔒
                        </a>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-dashed border-gray-300">
                <div class="text-6xl mb-4">🍃</div>
                <h2 class="text-2xl font-bold text-gray-800">Sepetin henüz boş.</h2>
                <p class="text-gray-500 mt-2 mb-8">Hemen doğal tohumlarımızı incelemeye başla!</p>
                <a href="products.php" class="bg-nature-green text-white px-10 py-4 rounded-xl font-bold hover:bg-nature-dark transition shadow-lg">Tohumları Keşfet</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>