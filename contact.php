<?php
require_once 'includes/header.php';

$message = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen verileri temizle
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $msg_content = htmlspecialchars(trim($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($msg_content)) {
        try {
            // Veritabanına Kaydet (Admin Paneli İçin)
            // Tablo adı genelde 'messages' olur. Admin panelinde bu isimde yapmıştık.
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$name, $email, $subject, $msg_content])) {
                $message = "Mesajınız bize ulaştı! En kısa sürede dönüş yapacağız. ";
                $msgType = "success";
            } else {
                $message = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
                $msgType = "error";
            }
        } catch (PDOException $e) {
            // Tablo yoksa admin tarafında oluşturulmamış olabilir
            $message = "Sistem Hatası: Mesaj gönderilemedi. (Tablo kontrolü yapın)";
            $msgType = "error";
        }
    } else {
        $message = "Lütfen tüm zorunlu alanları doldurun.";
        $msgType = "error";
    }
}
?>

<div class="relative bg-nature-dark h-64 overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1516253593875-bd7ba052fbc5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
             class="w-full h-full object-cover opacity-30">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 h-full flex flex-col justify-center items-center text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-2 drop-shadow-lg">Bizimle İletişime Geçin</h1>
        <p class="text-lg text-gray-200 max-w-2xl">
            Ata tohumları hakkında sorularınız mı var? Bahçeniz için tavsiyeye mi ihtiyacınız var? Buradayız.
        </p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col lg:flex-row">
            
            <div class="bg-nature-dark text-white p-10 lg:w-2/5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-nature-green rounded-full opacity-20 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-nature-green rounded-full opacity-20 blur-3xl"></div>

                <div>
                    <h3 class="text-2xl font-bold mb-6">İletişim Bilgileri</h3>
                    <p class="text-gray-300 mb-10 leading-relaxed">
                        Doğal tohum yolculuğunda size rehberlik etmekten mutluluk duyarız. Aşağıdaki kanallardan bize ulaşabilirsiniz.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 text-xl">📍</div>
                            <div>
                                <h4 class="font-bold">Adresimiz</h4>
                                <p class="text-sm text-gray-300 mt-1">Darıca - Gebze<br>Kocaeli, Türkiye</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 text-xl">📞</div>
                            <div>
                                <h4 class="font-bold">Telefon</h4>
                                <p class="text-sm text-gray-300 mt-1">+90 (546) 760 30 07</p>
                                <p class="text-xs text-gray-400">Hafta içi: 09:00 - 18:00</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 text-xl">✉️</div>
                            <div>
                                <h4 class="font-bold">E-Posta</h4>
                                <p class="text-sm text-gray-300 mt-1">ramazanduman@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h4 class="font-bold mb-4">Bizi Takip Edin</h4>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-nature-green transition">📸</a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-nature-green transition">🐦</a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-nature-green transition">💼</a>
                    </div>
                </div>
            </div>

            <div class="p-10 lg:w-3/5">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Bize Yazın</h3>
                <p class="text-gray-500 mb-8">Formu doldurun, ekibimiz en kısa sürede size dönsün.</p>

                <?php if($message): ?>
                    <div class="mb-6 p-4 rounded-xl text-sm font-bold <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-100'; ?> flex items-center gap-3 animate-pulse">
                        <span class="text-xl"><?php echo $msgType == 'success' ? '🚀' : '⚠️'; ?></span> 
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Adınız Soyadınız</label>
                            <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-nature-green focus:ring-2 focus:ring-green-50 outline-none transition bg-gray-50 focus:bg-white" placeholder="Örn: Ahmet Yılmaz">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">E-Posta Adresiniz</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-nature-green focus:ring-2 focus:ring-green-50 outline-none transition bg-gray-50 focus:bg-white" placeholder="ornek@mail.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Konu</label>
                        <select name="subject" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-nature-green focus:ring-2 focus:ring-green-50 outline-none transition bg-gray-50 focus:bg-white">
                            <option value="Sipariş Hakkında">📦 Sipariş Hakkında</option>
                            <option value="Tohum Bilgisi">🌱 Tohum Bilgisi</option>
                            <option value="Öneri & Şikayet">💡 Öneri & Şikayet</option>
                            <option value="Diğer">📝 Diğer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mesajınız</label>
                        <textarea name="message" rows="5" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-nature-green focus:ring-2 focus:ring-green-50 outline-none transition bg-gray-50 focus:bg-white" placeholder="Size nasıl yardımcı olabiliriz?"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-nature-green text-white font-bold py-4 rounded-xl hover:bg-nature-dark transition shadow-lg transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <span>📨</span> Mesajı Gönder
                    </button>
                </form>
            </div>

        </div>

        <div class="mt-16 rounded-2xl overflow-hidden shadow-lg border border-gray-200 h-96 grayscale hover:grayscale-0 transition duration-700">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d192697.8885055054!2d28.87209637248882!3d41.00549580922442!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14caa7040068086b%3A0xe1ccfe98bc01b0d0!2zxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1703698751234!5m2!1str!2str" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>