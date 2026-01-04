<?php
ob_start(); // Yönlendirme hatalarını önler
require_once 'includes/header.php';

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['user_id'])) {
    if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        echo "<script>window.location.href='admin/index.php';</script>";
    } else {
        echo "<script>window.location.href='index.php';</script>";
    }
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // DB bağlantısı (header.php içinde db.php yoksa diye garantiye alıyoruz)
        if (!isset($pdo)) { require_once 'config/db.php'; }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // 1. OTURUM BAŞLAT
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            // 2. SEPETİ VERİTABANINDAN ÇEK (HATA KORUMALI)
            try {
                $cartStmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
                $cartStmt->execute([$user['id']]);
                $savedCart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

                foreach ($savedCart as $item) {
                    $pid = $item['product_id'];
                    $qty = $item['quantity'];
                    if (!isset($_SESSION['cart'][$pid])) {
                        $_SESSION['cart'][$pid] = $qty;
                    }
                }
            } catch (Exception $e) {
                // Tablo yoksa bile giriş devam etsin
            }

            // 3. YÖNLENDİRME
            if($user['role'] == 'admin') {
                echo "<script>window.location.href='admin/index.php';</script>";
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit;
        } else {
            $message = "E-posta veya şifre hatalı.";
        }
    } else {
        $message = "Lütfen tüm alanları doldurun.";
    }
}
?>

<div class="min-h-screen flex bg-white">
    
    <div class="hidden lg:block lg:w-1/2 relative bg-gray-900">
        <img src="https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?q=80&w=1000&auto=format&fit=crop" alt="Doğal Yaşam" class="absolute inset-0 w-full h-full object-cover opacity-80">
        
        <div class="absolute inset-0 bg-nature-dark mix-blend-multiply opacity-40"></div>

        <div class="absolute inset-0 flex flex-col justify-center px-12 text-white z-10">
            <h2 class="text-5xl font-extrabold mb-6 drop-shadow-lg">Doğaya Dönüş ☀️</h2>
            <p class="text-xl text-gray-100 leading-relaxed font-light drop-shadow-md">
                "Toprağa dokunmak, hayata dokunmaktır." <br>
                Güneşin sıcaklığı ve suyun bereketiyle yetişen ata tohumlarımız, bahçenize hayat vermeye hazır.
            </p>
            
            <div class="mt-10 space-y-4">
                <div class="flex items-center gap-4 bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20">
                    <div class="w-12 h-12 rounded-full bg-nature-green flex items-center justify-center text-2xl shadow-lg">🌱</div>
                    <div>
                        <h4 class="font-bold text-white">Sipariş Takibi</h4>
                        <p class="text-xs text-gray-200">Kargonuz nerede anında görün.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20">
                    <div class="w-12 h-12 rounded-full bg-nature-green flex items-center justify-center text-2xl shadow-lg">💧</div>
                    <div>
                        <h4 class="font-bold text-white">Bahçivan Rehberi</h4>
                        <p class="text-xs text-gray-200">Bitkileriniz için bakım tüyoları.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16 bg-gray-50">
        <div class="max-w-md w-full">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Giriş Yapın</h2>
                <p class="mt-3 text-base text-gray-600">
                    Hesabınıza erişmek için bilgilerinizi girin.
                </p>
            </div>

            <?php if($message): ?>
                <div class="mb-6 p-4 rounded-xl text-sm font-bold bg-red-50 text-red-600 border border-red-100 flex items-center gap-3 shadow-sm">
                    <span class="text-xl">⚠️</span> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="" method="POST" autocomplete="off">
                
                <div class="group">
                    <label class="block text-sm font-bold text-gray-700 mb-2 transition group-focus-within:text-nature-green">E-Posta Adresi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">✉️</span>
                        <input name="email" type="email" required autocomplete="new-password"
                               class="w-full pl-11 pr-4 py-4 rounded-xl bg-white border border-gray-200 focus:border-nature-green focus:ring-4 focus:ring-green-50 outline-none transition-all shadow-sm font-medium text-gray-700 placeholder-gray-400" 
                               placeholder="ornek@mail.com">
                    </div>
                </div>

                <div class="group">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-gray-700 transition group-focus-within:text-nature-green">Şifre</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">🔒</span>
                        <input name="password" type="password" required autocomplete="new-password"
                               class="w-full pl-11 pr-4 py-4 rounded-xl bg-white border border-gray-200 focus:border-nature-green focus:ring-4 focus:ring-green-50 outline-none transition-all shadow-sm font-medium text-gray-700 placeholder-******">
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="forgot-password.php" class="text-sm font-bold text-nature-green hover:text-nature-dark hover:underline transition">
                        Şifrenizi mi unuttunuz?
                    </a>
                </div>

                <button type="submit" class="w-full bg-nature-dark text-white font-bold py-4 rounded-xl hover:bg-nature-green transition transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl text-lg flex items-center justify-center gap-2">
                    <span></span> Giriş Yap
                </button>
            </form>

            <div class="mt-10 text-center">
                <p class="text-gray-600">Henüz hesabınız yok mu?</p>
                <a href="register.php" class="inline-block mt-2 font-bold text-nature-green hover:text-nature-dark hover:underline transition text-lg">
                    Hemen Ücretsiz Kayıt Ol →
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>