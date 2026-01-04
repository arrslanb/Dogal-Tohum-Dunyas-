<?php require_once 'includes/header.php'; ?>

<div class="bg-gray-50 h-screen flex flex-col items-center justify-center text-center px-4">
    <div class="bg-white p-10 rounded-2xl shadow-xl max-w-lg">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-nature-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-4">Siparişiniz Alındı! 🎉</h1>
        <p class="text-gray-600 mb-8">
            Doğal tohumları tercih ettiğiniz için teşekkür ederiz. Siparişiniz hazırlanmaya başlandı. Doğaya katkınız büyük!
        </p>

        <div class="space-y-3">
            <a href="index.php" class="block w-full bg-nature-green text-white font-bold py-3 rounded-lg hover:bg-green-700 transition">
                Ana Sayfaya Dön
            </a>
            <a href="products.php" class="block w-full bg-white border border-gray-300 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-50 transition">
                Alışverişe Devam Et
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>