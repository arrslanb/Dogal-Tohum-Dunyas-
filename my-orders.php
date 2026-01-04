<?php
require_once 'includes/header.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının siparişlerini çek
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-50 min-h-screen py-6 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Siparişlerim 📦</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Geçmiş siparişlerinizi buradan takip edebilirsiniz.</p>
            </div>
            <a href="products.php" class="w-full md:w-auto text-center bg-white text-nature-dark px-6 py-3 rounded-xl border border-gray-200 font-bold hover:bg-nature-green hover:text-white transition shadow-sm text-sm">
                🛍️ Alışverişe Devam Et
            </a>
        </div>

        <?php if(count($orders) > 0): ?>
            
            <div class="hidden md:block bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-xs tracking-wider">
                            <th class="px-6 py-4 font-bold">Sipariş No</th>
                            <th class="px-6 py-4 font-bold">Tarih</th>
                            <th class="px-6 py-4 font-bold">Tutar</th>
                            <th class="px-6 py-4 font-bold text-center">Durum</th>
                            <th class="px-6 py-4 font-bold text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($orders as $order): ?>
                        <tr class="hover:bg-green-50/50 transition duration-150">
                            <td class="px-6 py-4 font-bold text-gray-800">
                                #<?php echo 10000 + $order['id']; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">
                                <?php echo date("d.m.Y H:i", strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-nature-dark">
                                <?php echo number_format($order['total_price'], 2); ?> ₺
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                    $statusIcon = '🕒';
                                    if($order['status'] == 'Onaylandı') { $statusClass = 'bg-blue-100 text-blue-700'; $statusIcon = '✅'; }
                                    if($order['status'] == 'Kargoda') { $statusClass = 'bg-purple-100 text-purple-700'; $statusIcon = '🚚'; }
                                    if($order['status'] == 'Teslim Edildi') { $statusClass = 'bg-green-100 text-green-700'; $statusIcon = '🏠'; }
                                    if($order['status'] == 'İptal') { $statusClass = 'bg-red-100 text-red-700'; $statusIcon = '❌'; }
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $statusClass; ?> whitespace-nowrap">
                                    <?php echo $statusIcon . ' ' . $order['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if(!empty($order['tracking_code'])): ?>
                                    <a href="track-order.php?code=<?php echo $order['tracking_code']; ?>" 
                                       class="inline-flex items-center gap-2 bg-nature-green text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-nature-dark transition shadow-md">
                                        <span>🚚</span> Takip
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 font-medium">Hazırlanıyor</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-4">
                <?php foreach($orders as $order): ?>
                <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs text-gray-400 block mb-1 uppercase font-bold tracking-widest">Sipariş No</span>
                            <span class="font-black text-gray-800 text-lg">#<?php echo 10000 + $order['id']; ?></span>
                        </div>
                        <div class="text-right">
                            <?php
                                $statusClass = 'bg-gray-100 text-gray-600';
                                $statusIcon = '🕒';
                                if($order['status'] == 'Onaylandı') { $statusClass = 'bg-blue-100 text-blue-700'; $statusIcon = '✅'; }
                                if($order['status'] == 'Kargoda') { $statusClass = 'bg-purple-100 text-purple-700'; $statusIcon = '🚚'; }
                                if($order['status'] == 'Teslim Edildi') { $statusClass = 'bg-green-100 text-green-700'; $statusIcon = '🏠'; }
                                if($order['status'] == 'İptal') { $statusClass = 'bg-red-100 text-red-700'; $statusIcon = '❌'; }
                            ?>
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase <?php echo $statusClass; ?>">
                                <?php echo $statusIcon . ' ' . $order['status']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-3 border-t border-b border-gray-50 mb-4">
                        <div>
                            <span class="text-xs text-gray-400 block uppercase font-bold tracking-widest">Tarih</span>
                            <span class="text-gray-700 text-sm font-medium"><?php echo date("d.m.Y H:i", strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400 block uppercase font-bold tracking-widest">Tutar</span>
                            <span class="text-nature-dark font-black text-lg"><?php echo number_format($order['total_price'], 2); ?> ₺</span>
                        </div>
                    </div>

                    <?php if(!empty($order['tracking_code'])): ?>
                        <a href="track-order.php?code=<?php echo $order['tracking_code']; ?>" 
                           class="w-full flex items-center justify-center gap-3 bg-nature-green text-white py-3 rounded-xl text-sm font-bold shadow-lg shadow-green-100 active:scale-95 transition-transform">
                            <span>🚚</span> Kargom Nerede?
                        </a>
                    <?php else: ?>
                        <div class="w-full text-center py-3 bg-gray-50 rounded-xl text-gray-400 text-xs font-bold uppercase tracking-widest">
                            Kargo Takip No Bekleniyor
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                <span class="text-7xl block mb-4 animate-bounce">🛒</span>
                <h3 class="text-2xl font-bold text-gray-800">Henüz Siparişiniz Yok</h3>
                <p class="text-gray-500 mt-2 mb-8">Doğal tohumlarla tanışmak için harika bir gün!</p>
                <a href="products.php" class="bg-nature-green text-white px-8 py-3 rounded-xl font-bold hover:bg-nature-dark transition shadow-lg inline-block">
                    Hemen Alışverişe Başla
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>