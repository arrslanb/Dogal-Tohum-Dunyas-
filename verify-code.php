<?php
require_once 'includes/header.php';

// Eğer session'da mail yoksa şifremi unuttum sayfasına geri at
if (!isset($_SESSION['reset_email'])) {
    echo "<script>window.location.href='forgot-password.php';</script>";
    exit;
}

$message = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputCode = trim($_POST['code']);
    $email = $_SESSION['reset_email'];

    // 1. Kullanıcının kodunu ve süresini kontrol et
    // Sorguyu basitleştirdik, süre kontrolünü PHP tarafında yapacağız ki hata payı kalmasın
    $stmt = $pdo->prepare("SELECT id, reset_token, reset_token_expires_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $now = date("Y-m-d H:i:s");
        
        // Kod doğru mu ve Süresi geçmemiş mi?
        if ($user['reset_token'] == $inputCode) {
            if ($user['reset_token_expires_at'] >= $now) {
                // BAŞARILI: Session'ı set et ve yönlendir
                $_SESSION['code_verified'] = true;
                echo "<script>window.location.href='reset-password.php';</script>";
                exit;
            } else {
                $message = "Girdiğiniz kodun süresi dolmuş (15 dakikayı geçti).";
                $msgType = "error";
            }
        } else {
            $message = "Girdiğiniz kod hatalı. Lütfen mailinizi kontrol edin.";
            $msgType = "error";
        }
    } else {
        $message = "Bir hata oluştu, lütfen işlemi baştan başlatın.";
        $msgType = "error";
    }
}
?>

<div class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-sm w-full border border-gray-100">
        <div class="text-center mb-6">
            <span class="text-4xl">📧</span>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Kodu Doğrula</h2>
            <p class="text-gray-500 text-sm mt-2">
                <strong><?php echo $_SESSION['reset_email']; ?></strong> adresine gelen 6 haneli kodu aşağıya girin.
            </p>
        </div>

        <?php if($message): ?>
            <div class="mb-4 p-3 rounded-lg text-sm font-bold text-center bg-red-50 text-red-600 border border-red-100">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <div class="mb-6">
                <input type="text" name="code" maxlength="6" required 
                       class="w-full text-center text-3xl tracking-[8px] font-bold border-2 border-gray-200 rounded-xl p-4 outline-none focus:border-nature-green focus:ring-4 focus:ring-green-50 transition-all"
                       placeholder="000000">
            </div>
            
            <button type="submit" class="w-full bg-nature-green text-white py-4 rounded-xl font-bold text-lg hover:bg-nature-dark shadow-lg transition transform hover:-translate-y-1">
                Kodu Onayla ve Devam Et
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <a href="forgot-password.php" class="text-gray-400 hover:text-nature-green transition">Yeniden kod gönder</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>