<?php
require_once 'includes/header.php';

// URL'den sipariş ID'sini al (Düzeltildi: oid -> order_id)
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Eğer ID yoksa anasayfaya at
if($order_id === 0) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <div class="absolute inset-0 pointer-events-none">
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
    </div>

    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-2xl relative z-10 border border-gray-100 text-center transform transition-all hover:scale-[1.01]">
        
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-green-100 animate-bounce-slow">
            <svg class="w-12 h-12 text-nature-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <div>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900 tracking-tight">
                Siparişiniz Alındı! 🥳
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Ödemeniz güvenli bir şekilde gerçekleşti. Doğal tohumlarınız hazırlanmaya başlandı bile!
            </p>
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 border-dashed">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sipariş Numarası</p>
            <p class="text-3xl font-black text-nature-dark tracking-widest select-all">
                #<?php echo $order_id; ?>
            </p>
        </div>

        <div class="space-y-4 text-left px-4">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                <span>Sipariş onayı e-posta adresine gönderildi.</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">2</span>
                <span>Kargoya verildiğinde SMS ile bildireceğiz.</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 mt-8">
            <a href="my-orders.php" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-nature-green hover:bg-nature-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg transition transform hover:-translate-y-1">
                Siparişimi Görüntüle
            </a>
            <a href="index.php" class="w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                Alışverişe Devam Et
            </a>
        </div>
    </div>
</div>

<style>
    /* Basit Konfeti Animasyonu */
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: #f2d74e;
        animation: confetti 5s infinite ease-in-out;
    }
    .confetti:nth-child(1) { left: 10%; animation-delay: 0s; background-color: #95c623; }
    .confetti:nth-child(2) { left: 20%; animation-delay: 2s; background-color: #f2d74e; }
    .confetti:nth-child(3) { left: 35%; animation-delay: 4s; background-color: #e95e28; }
    .confetti:nth-child(4) { left: 50%; animation-delay: 1s; background-color: #95c623; }
    .confetti:nth-child(5) { left: 65%; animation-delay: 3s; background-color: #f2d74e; }
    .confetti:nth-child(6) { left: 80%; animation-delay: 2.5s; background-color: #e95e28; }

    @keyframes confetti {
        0% { transform: translateY(-10px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
    .animate-bounce-slow {
        animation: bounce 2s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
        50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
    }
</style>

<?php require_once 'includes/footer.php'; ?>