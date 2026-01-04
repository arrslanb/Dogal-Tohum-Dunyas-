<?php
session_start();
// Güvenlik: Admin değilse at
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// 1. İSTATİSTİKLERİ HESAPLA
$stmt = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'");
$totalRevenue = $stmt->fetchColumn();
if(!$totalRevenue) $totalRevenue = 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'completed' AND status != 'cancelled'");
$pendingOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
$totalUsers = $stmt->fetchColumn();

// 2. SON SİPARİŞLER (Son 5 Tane)
$stmt = $pdo->query("SELECT orders.*, users.full_name FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. KRİTİK STOK LİSTESİ (1000'den az olanlar)
$stmt = $pdo->query("SELECT * FROM products WHERE stock < 1000 ORDER BY stock ASC LIMIT 10");
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Yönetim Paneli | Doğal Tohum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' },
                    fontFamily: { 'sans': ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row h-screen overflow-hidden">

    <div class="md:hidden bg-nature-dark text-white p-4 flex justify-between items-center z-50 shadow-md">
        <span class="text-xl font-bold tracking-wider">DoğalPanel</span>
        <button id="mobileMenuBtn" class="text-white focus:outline-none p-2">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div id="sidebar" class="bg-nature-dark text-white w-64 flex-shrink-0 flex flex-col absolute md:relative inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition duration-200 ease-in-out z-40 h-full shadow-2xl md:shadow-none">
        <div class="h-16 flex items-center justify-center border-b border-green-800">
            <span class="text-2xl font-bold tracking-wider">DoğalPanel</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 bg-green-800 rounded text-white font-bold shadow-md">📊 Gösterge Paneli</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📂 Kategoriler</a>
                <a href="coupons.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🎟️ Kuponlar</a>
                <a href="sliders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🖼️ Slider (Manşet)</a>
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition font-bold">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden relative" id="main-content">
        
        <header class="hidden md:flex h-16 bg-white shadow items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Genel Bakış</div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-nature-green hover:underline text-sm font-bold">Siteyi Görüntüle →</a>
                <div class="font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-full">
                    👤 <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Yönetici'; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                
                <div class="bg-white rounded-xl p-5 shadow-sm flex items-center border border-gray-100">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <span class="text-2xl">💰</span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Toplam Ciro</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo number_format($totalRevenue, 2); ?> ₺</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm flex items-center border border-gray-100">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Aktif Sipariş</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo $pendingOrders; ?></p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm flex items-center border border-gray-100">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <span class="text-2xl">🌱</span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Toplam Ürün</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo $totalProducts; ?></p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm flex items-center border border-gray-100">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <span class="text-2xl">👥</span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Kayıtlı Müşteri</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo $totalUsers; ?></p>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white shadow-sm rounded-xl p-4 md:p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex justify-between items-center">
                        <span>Son Siparişler</span>
                        <a href="orders.php" class="text-xs bg-nature-green text-white px-2 py-1 rounded hover:bg-nature-dark transition">Tümünü Gör</a>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                            <thead class="border-b bg-gray-50">
                                <tr>
                                    <th class="py-3 px-2 rounded-tl-lg">No</th>
                                    <th class="py-3 px-2">Müşteri</th>
                                    <th class="py-3 px-2">Tutar</th>
                                    <th class="py-3 px-2 rounded-tr-lg">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentOrders as $order): ?>
                                <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                                    <td class="py-3 px-2 font-bold text-nature-dark">#<?php echo $order['id']; ?></td>
                                    <td class="py-3 px-2"><?php echo $order['full_name']; ?></td>
                                    <td class="py-3 px-2 font-bold text-green-600">
                                        <?php 
                                            $price = isset($order['total_price']) ? $order['total_price'] : (isset($order['total_amount']) ? $order['total_amount'] : 0);
                                            echo number_format($price, 2); 
                                        ?> ₺
                                    </td>
                                    <td class="py-3 px-2">
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-800">
                                            <?php echo strtoupper($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($recentOrders)) echo "<tr><td colspan='4' class='py-4 text-center text-gray-400'>Sipariş yok.</td></tr>"; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-4 md:p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center justify-between">
                        <span class="flex items-center gap-2"><span class="text-xl">🚨</span> Kritik Stok</span>
                        <a href="products.php" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">Tüm Ürünler</a>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                            <thead class="border-b bg-gray-50">
                                <tr>
                                    <th class="py-3 px-2 rounded-tl-lg">Ürün Adı</th>
                                    <th class="py-3 px-2 text-right">Stok</th>
                                    <th class="py-3 px-2 text-center rounded-tr-lg">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lowStockProducts as $prod): ?>
                                <tr class="border-b last:border-0 hover:bg-red-50 transition">
                                    <td class="py-3 px-2 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded bg-gray-100 flex-shrink-0 overflow-hidden border">
                                            <?php if(!empty($prod['image'])): ?>
                                                <img src="../uploads/<?php echo $prod['image']; ?>" class="w-full h-full object-cover">
                                            <?php endif; ?>
                                        </div>
                                        <span class="truncate max-w-[150px]" title="<?php echo $prod['name']; ?>">
                                            <?php echo $prod['name']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-right font-bold text-red-600"><?php echo $prod['stock']; ?></td>
                                    <td class="py-3 px-2 text-center">
                                        <a href="product-edit.php?id=<?php echo $prod['id']; ?>" class="text-white bg-nature-green px-3 py-1.5 rounded text-xs hover:bg-nature-dark transition shadow font-bold">
                                            + Ekle
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($lowStockProducts)) echo "<tr><td colspan='3' class='py-4 text-center text-green-600'>Stoklar gayet iyi! 👍</td></tr>"; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        const btn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('main-content');
        
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        content.addEventListener('click', () => {
            if (!sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>

</body>
</html>