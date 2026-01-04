<?php require_once 'includes/header.php'; ?>

<div class="bg-gray-50 min-h-screen flex items-center justify-center py-12">
    <div class="bg-white p-12 rounded-2xl shadow-xl text-center max-w-lg">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="text-5xl">❌</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Ödeme Başarısız!</h1>
        <p class="text-gray-600 mb-8">Üzgünüz, ödeme işlemi sırasında bir hata oluştu veya işlem iptal edildi.</p>
        
        <a href="checkout.php" class="block w-full bg-red-500 text-white py-3 rounded-lg font-bold hover:bg-red-600 transition">Tekrar Dene</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>