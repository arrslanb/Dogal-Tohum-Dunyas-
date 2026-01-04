<?php
require_once 'includes/header.php';

$code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '';

if (empty($code)) {
    echo "<script>window.location.href='my-orders.php';</script>";
    exit;
}

// Siparişi ve Kargo Firmasını Bul
$stmt = $pdo->prepare("SELECT * FROM orders WHERE tracking_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='text-center py-20'>Sipariş bulunamadı.</div>";
    require_once 'includes/footer.php';
    exit;
}

$company = isset($order['cargo_company']) ? $order['cargo_company'] : 'Yurtiçi Kargo';

// --- AKILLI LİNK OLUŞTURUCU ---
$tracking_url = "#";
switch($company) {
    case 'Yurtiçi Kargo':
        $tracking_url = "https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code=" . $code;
        $logo_color = "bg-blue-600";
        break;
    case 'Aras Kargo':
        $tracking_url = "https://kargotakip.araskargo.com.tr/mainpage.aspx?code=" . $code; 
        $logo_color = "bg-blue-800";
        break;
    case 'MNG Kargo':
        $tracking_url = "https://www.mngkargo.com.tr/gonderitakip"; 
        $logo_color = "bg-orange-500";
        break;
    case 'PTT Kargo':
        $tracking_url = "https://gonderitakip.ptt.gov.tr/Track/Verify?q=" . $code;
        $logo_color = "bg-yellow-500";
        break;
    case 'Sürat Kargo':
        $tracking_url = "https://suratkargo.com.tr/KargoTakip/?kargotakipno=" . $code;
        $logo_color = "bg-red-600";
        break;
    default:
        $tracking_url = "#";
        $logo_color = "bg-gray-600";
}

// --- DURUM GÜNCELLEME DÜZELTMESİ ---
// Admin panelindeki: pending, preparing, shipped, completed değerlerine göre kontrol
$current_status = $order['status'];

$steps = [
    [
        'status' => 'Sipariş Alındı', 
        'desc' => 'Siparişiniz bize ulaştı.', 
        'done' => true // Her zaman tamamlanmış
    ],
    [
        'status' => 'Hazırlanıyor', 
        'desc' => 'Paketiniz hazırlanıyor.', 
        // pending (onay bekliyor) değilse hazırlanmaya geçilmiştir
        'done' => ($current_status != 'pending' && $current_status != 'cancelled')
    ],
    [
        'status' => 'Kargoya Verildi', 
        'desc' => 'Kargo firmasına teslim edildi.', 
        // shipped veya completed ise kargoya verilmiştir
        'done' => ($current_status == 'shipped' || $current_status == 'completed')
    ],
    [
        'status' => 'Teslim Edildi', 
        'desc' => 'Alıcıya ulaştırıldı.', 
        // sadece completed ise teslim edilmiştir
        'done' => ($current_status == 'completed')
    ]
];
?>

<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-3xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 border border-gray-100">
            <div class="<?php echo $logo_color; ?> p-6 text-white flex justify-between items-center transition-colors duration-300">
                <div>
                    <span class="text-xs font-bold bg-white/20 px-3 py-1 rounded text-white uppercase tracking-wider">
                        <?php echo $company; ?>
                    </span>
                    <h2 class="text-2xl font-bold mt-3">Takip No: <?php echo $code; ?></h2>
                </div>
                <div class="text-5xl opacity-20">📦</div>
            </div>
            <div class="p-8 text-center">
                <p class="text-gray-600 mb-6">
                    Aşağıdaki butona tıklayarak kargonuzun anlık konumunu <strong><?php echo $company; ?></strong> resmi sitesinden görebilirsiniz.
                </p>
                
                <a href="<?php echo $tracking_url; ?>" target="_blank" class="inline-flex items-center gap-3 bg-nature-dark text-white px-8 py-4 rounded-xl font-bold hover:bg-nature-green transition shadow-lg transform hover:-translate-y-1">
                    <span>🌍</span> Resmi Siteden Canlı Takip Et
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-8 border-b pb-4">Sipariş Durumu</h3>
            <div class="relative pl-4">
                <div class="absolute left-6 top-2 bottom-4 w-1 bg-gray-100"></div>
                <?php foreach($steps as $index => $step): ?>
                <div class="relative flex items-start mb-8 last:mb-0 group">
                    <div class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-md transition transform group-hover:scale-110 
                        <?php echo $step['done'] ? 'bg-nature-green text-white' : 'bg-gray-200 text-gray-400'; ?>">
                        <?php echo $step['done'] ? '✓' : ($index + 1); ?>
                    </div>
                    <div class="ml-6 flex-1 pt-2">
                        <h4 class="text-lg font-bold <?php echo $step['done'] ? 'text-nature-dark' : 'text-gray-400'; ?>">
                            <?php echo $step['status']; ?>
                        </h4>
                        <p class="text-sm mt-1 text-gray-500"><?php echo $step['desc']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="my-orders.php" class="text-gray-500 hover:text-nature-green font-bold transition">← Geri Dön</a>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>