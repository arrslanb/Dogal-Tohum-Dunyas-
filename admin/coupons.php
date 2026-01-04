<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit; }
require_once '../config/db.php';

// KUPON SİLME
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: coupons.php"); exit;
}

// KUPON EKLEME
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(trim($_POST['code'])); // Kodu büyük harfe çevir
    $type = $_POST['type'];
    $value = $_POST['value'];
    $min_spend = $_POST['min_spend'];

    if (!empty($code) && !empty($value)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_spend) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $type, $value, $min_spend]);
            $message = "Kupon oluşturuldu: $code ✅";
        } catch (PDOException $e) {
            $message = "Bu kod zaten var!";
        }
    }
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kupon Yönetimi | DoğalPanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' }, fontFamily: { 'sans': ['Poppins', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen overflow-hidden">
    
    <div class="bg-nature-dark text-white w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-green-800"><span class="text-2xl font-bold tracking-wider">DoğalPanel</span></div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📊 Gösterge Paneli</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📂 Kategoriler</a>
                <a href="coupons.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">🎟️ Kuponlar</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">İndirim Kuponları</div>
            <a href="../index.php" target="_blank" class="text-nature-green text-sm">Siteye Git →</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            
            <?php if($message): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded border border-green-400"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yeni Kupon Oluştur</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Kupon Kodu</label>
                            <input type="text" name="code" placeholder="Örn: BAHAR20" required class="w-full border p-2 rounded uppercase font-bold text-nature-green">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">İndirim Tipi</label>
                                <select name="type" class="w-full border p-2 rounded">
                                    <option value="percent">Yüzde (%)</option>
                                    <option value="fixed">Tutar (TL)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Değer</label>
                                <input type="number" name="value" placeholder="20" required class="w-full border p-2 rounded">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Min. Sepet Tutarı (TL)</label>
                            <input type="number" name="min_spend" value="0" class="w-full border p-2 rounded">
                            <p class="text-xs text-gray-400">0 yazarsan alt limit olmaz.</p>
                        </div>
                        <button type="submit" class="w-full bg-nature-green text-white py-2 rounded font-bold hover:bg-nature-dark transition">Kuponu Kaydet</button>
                    </form>
                </div>

                <div class="col-span-2 bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal text-sm">
                        <thead class="bg-gray-100 text-gray-600 font-semibold uppercase">
                            <tr>
                                <th class="px-5 py-3 text-left">Kod</th>
                                <th class="px-5 py-3 text-center">İndirim</th>
                                <th class="px-5 py-3 text-center">Min. Sepet</th>
                                <th class="px-5 py-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($coupons as $cp): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-5 py-4 font-bold text-nature-green text-base">
                                    <span class="bg-green-100 px-2 py-1 rounded border border-green-200"><?php echo $cp['code']; ?></span>
                                </td>
                                <td class="px-5 py-4 text-center font-bold text-gray-700">
                                    <?php echo $cp['discount_type'] == 'percent' ? "%".$cp['discount_value'] : $cp['discount_value']." ₺"; ?>
                                </td>
                                <td class="px-5 py-4 text-center text-gray-600">
                                    <?php echo $cp['min_spend'] > 0 ? $cp['min_spend']." ₺" : '-'; ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="coupons.php?delete=<?php echo $cp['id']; ?>" onclick="return confirm('Silmek istiyor musun?')" class="text-red-500 hover:text-red-700 font-bold">Sil 🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($coupons)) echo "<div class='p-4 text-center text-gray-500'>Aktif kupon yok.</div>"; ?>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>