<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Siparişleri En Yeniden Eskiye Çek
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sipariş Yönetimi | DoğalPanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' }, fontFamily: { 'sans': ['Poppins', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row h-screen overflow-hidden">

    <div class="md:hidden bg-nature-dark text-white p-4 flex justify-between items-center z-50">
        <span class="text-xl font-bold tracking-wider">DoğalPanel</span>
        <button id="mobileMenuBtn" class="text-white focus:outline-none">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div id="sidebar" class="bg-nature-dark text-white w-64 flex-shrink-0 flex flex-col absolute md:relative inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition duration-200 ease-in-out z-40 h-full">
        <div class="h-16 flex items-center justify-center border-b border-green-800"><span class="text-2xl font-bold tracking-wider">DoğalPanel</span></div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📊 Panel</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 bg-green-800 rounded text-white font-bold shadow-lg">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📂 Kategoriler</a>
                <a href="coupons.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🎟️ Kuponlar</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📩 Mesajlar</a>
                <a href="../index.php" class="block px-4 py-2 mt-10 hover:bg-green-700 rounded text-green-200 transition font-bold">🌐 Siteye Git</a>
                <a href="../logout.php" class="block px-4 py-2 hover:bg-red-700 rounded text-red-300 transition">🚪 Çıkış</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden relative" id="main-content">
        
        <header class="hidden md:flex h-16 bg-white shadow items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Sipariş Yönetimi</div>
            <div class="text-sm text-gray-500"><?php echo count($orders); ?> Sipariş Bulundu</div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
            
            <div class="md:hidden space-y-4">
                <?php foreach($orders as $order): ?>
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-lg text-gray-800">#<?php echo 10000 + $order['id']; ?></span>
                        <span class="text-xs text-gray-500"><?php echo date("d.m H:i", strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-3">
                        <?php 
                            $uStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                            $uStmt->execute([$order['user_id']]);
                            $uName = $uStmt->fetchColumn();
                        ?>
                        <span class="text-sm text-gray-600 truncate max-w-[150px]"><?php echo $uName ? $uName : 'Misafir'; ?></span>
                        <span class="font-bold text-nature-dark"><?php echo number_format($order['total_price'], 2); ?> ₺</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'preparing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            // Veritabanındaki durum metnine göre renk (Türkçe/İngilizce uyumu için kontrol)
                            $st = $order['status'];
                            $cls = 'bg-gray-100 text-gray-800';
                            
                            if(strpos($st, 'Onay') !== false || $st == 'pending') $cls = $statusColors['pending'];
                            elseif(strpos($st, 'Hazır') !== false || $st == 'preparing') $cls = $statusColors['preparing'];
                            elseif(strpos($st, 'Kargo') !== false || $st == 'shipped') $cls = $statusColors['shipped'];
                            elseif(strpos($st, 'Teslim') !== false || $st == 'completed') $cls = $statusColors['completed'];
                            elseif(strpos($st, 'İptal') !== false || $st == 'cancelled') $cls = $statusColors['cancelled'];
                        ?>
                        <span class="px-2 py-1 text-xs font-bold rounded <?php echo $cls; ?>"><?php echo $order['status']; ?></span>
                        
                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="bg-nature-green text-white px-4 py-2 rounded text-sm font-bold shadow hover:bg-nature-dark">
                            Yönet →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?>
                    <div class="p-10 text-center text-gray-500">Henüz sipariş yok.</div>
                <?php endif; ?>
            </div>

            <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="px-5 py-3">No</th>
                                <th class="px-5 py-3">Müşteri</th>
                                <th class="px-5 py-3">Tutar</th>
                                <th class="px-5 py-3">Tarih</th>
                                <th class="px-5 py-3">Durum</th>
                                <th class="px-5 py-3">Kargo</th>
                                <th class="px-5 py-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr class="hover:bg-green-50 transition border-b border-gray-200">
                                <td class="px-5 py-4 text-sm font-bold text-gray-900">#<?php echo 10000 + $order['id']; ?></td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    <?php 
                                        $uStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                                        $uStmt->execute([$order['user_id']]);
                                        $uName = $uStmt->fetchColumn();
                                        echo $uName ? $uName : 'Misafir';
                                    ?>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-nature-dark"><?php echo number_format($order['total_price'], 2); ?> ₺</td>
                                <td class="px-5 py-4 text-sm text-gray-600"><?php echo date("d.m H:i", strtotime($order['created_at'])); ?></td>
                                <td class="px-5 py-4 text-sm">
                                    <?php
                                        // Renk Mantığı (Yukarıdakiyle aynı)
                                        $st = $order['status'];
                                        $cls = 'bg-gray-100 text-gray-800';
                                        if(strpos($st, 'Onay') !== false || $st == 'pending') $cls = $statusColors['pending'];
                                        elseif(strpos($st, 'Hazır') !== false || $st == 'preparing') $cls = $statusColors['preparing'];
                                        elseif(strpos($st, 'Kargo') !== false || $st == 'shipped') $cls = $statusColors['shipped'];
                                        elseif(strpos($st, 'Teslim') !== false || $st == 'completed') $cls = $statusColors['completed'];
                                        elseif(strpos($st, 'İptal') !== false || $st == 'cancelled') $cls = $statusColors['cancelled'];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $cls; ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500 font-mono">
                                    <?php echo !empty($order['tracking_code']) ? $order['tracking_code'] : '-'; ?>
                                </td>
                                <td class="px-5 py-4 text-sm text-right">
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="bg-nature-green text-white px-3 py-1 rounded hover:bg-nature-dark transition shadow-sm font-bold text-xs">
                                        Yönet
                                    </a>
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

        // Ekrana tıklayınca menüyü kapat
        content.addEventListener('click', () => {
            if (!sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>

</body>
</html>