<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Ürünleri çek
$sql = "SELECT products.*, categories.name as category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.id 
        ORDER BY products.id DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tohum Listesi | DoğalPanel</title>
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
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📊 Gösterge Paneli</a>
                <a href="products.php" class="block px-4 py-2 bg-green-800 rounded text-white font-bold shadow-md">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📂 Kategoriler</a>
                <a href="coupons.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🎟️ Kuponlar</a>
                <a href="sliders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🖼️ Slider</a>
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition font-bold">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden relative" id="main-content">
        
        <header class="hidden md:flex h-16 bg-white shadow items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Ürün Yönetimi</div>
            <a href="../index.php" target="_blank" class="text-nature-green hover:underline text-sm font-bold">Siteyi Görüntüle →</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-700">Tohum Listesi</h2>
                <a href="product-add.php" class="w-full md:w-auto bg-nature-green text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-lg flex items-center justify-center gap-2 font-bold">
                    <span>+</span> Yeni Tohum Ekle
                </a>
            </div>

            <div class="grid grid-cols-1 gap-4 md:hidden">
                <?php foreach($products as $product): ?>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col gap-3">
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden border">
                            <?php if($product['image']): ?>
                                <img src="../uploads/<?php echo $product['image']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">Yok</div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 text-lg leading-tight mb-1"><?php echo $product['name']; ?></h3>
                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold mb-2">
                                <?php echo $product['category_name']; ?>
                            </span>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">Stok: <b class="<?php echo $product['stock'] < 10 ? 'text-red-500' : 'text-gray-800'; ?>"><?php echo $product['stock']; ?></b></span>
                                <span class="text-nature-green font-bold text-lg"><?php echo $product['price']; ?> ₺</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mt-1 pt-3 border-t border-gray-100">
                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="bg-blue-50 text-blue-600 py-2 rounded-lg text-center font-bold text-sm hover:bg-blue-100 transition">
                            ✏️ Düzenle
                        </a>
                        <a href="product-delete.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Silmek istediğine emin misin?')" class="bg-red-50 text-red-600 py-2 rounded-lg text-center font-bold text-sm hover:bg-red-100 transition">
                            🗑️ Sil
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($products)): ?>
                    <div class="text-center p-10 text-gray-500">Henüz ürün eklenmemiş.</div>
                <?php endif; ?>
            </div>

            <div class="hidden md:block bg-white shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white leading-normal">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-bold tracking-wider">
                                <th class="py-3 px-6 text-left">Resim</th>
                                <th class="py-3 px-6 text-left">Ürün Adı</th>
                                <th class="py-3 px-6 text-left">Kategori</th>
                                <th class="py-3 px-6 text-center">Stok</th>
                                <th class="py-3 px-6 text-center">Fiyat</th>
                                <th class="py-3 px-6 text-center">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php foreach($products as $product): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="py-3 px-6 text-left">
                                    <div class="w-12 h-12 rounded overflow-hidden border border-gray-200">
                                        <?php if($product['image']): ?>
                                            <img src="../uploads/<?php echo $product['image']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-xs">Yok</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-left font-bold text-gray-800"><?php echo $product['name']; ?></td>
                                <td class="py-3 px-6 text-left">
                                    <span class="bg-green-100 text-green-700 py-1 px-3 rounded-full text-xs font-bold">
                                        <?php echo $product['category_name']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center font-bold <?php echo $product['stock'] < 10 ? 'text-red-500' : ''; ?>">
                                    <?php echo $product['stock']; ?>
                                </td>
                                <td class="py-3 px-6 text-center font-bold text-nature-dark"><?php echo $product['price']; ?> ₺</td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-3">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="bg-blue-100 text-blue-600 p-2 rounded hover:bg-blue-200 transition" title="Düzenle">
                                            ✏️
                                        </a>
                                        <a href="product-delete.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Silmek istediğine emin misin?')" class="bg-red-100 text-red-600 p-2 rounded hover:bg-red-200 transition" title="Sil">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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