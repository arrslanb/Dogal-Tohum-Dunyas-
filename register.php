<?php
require_once 'includes/header.php';

// Zaten giriş yapmışsa ana sayfaya at
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$message = "";
$msgType = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']); 
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        
        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($password_confirm)) {
            $message = "Lütfen tüm alanları doldurun.";
            $msgType = "error";
        } elseif ($password !== $password_confirm) {
            $message = "Şifreler birbiriyle eşleşmiyor. Lütfen kontrol edin.";
            $msgType = "error";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Bu e-posta adresi zaten kayıtlı. Lütfen giriş yapın.";
                $msgType = "error";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
                    $message = "Kayıt Başarılı! Giriş sayfasına yönlendiriliyorsunuz...";
                    $msgType = "success";
                    
                    // --- 📧 MAİL VERİSİNİ SESSİONA YAZ ---
                    $_SESSION['send_welcome_email'] = [
                        'email' => $email,
                        'name' => $name
                    ];
                    
                    echo "<script>setTimeout(function(){ window.location.href='login.php'; }, 2000);</script>";
                } else {
                    $message = "Bir sorun oluştu, kayıt yapılamadı.";
                    $msgType = "error";
                }
            }
        }
    } catch (PDOException $e) {
        $message = "Sistem Hatası: " . $e->getMessage();
        $msgType = "error";
    }
}
?>

<div class="min-h-screen flex bg-white">
    <div class="hidden lg:block lg:w-1/2 relative bg-gray-900">
        <img src="https://images.unsplash.com/photo-1492496913980-501348b61469?auto=format&fit=crop&w=1000&q=80" alt="Doğal Tarım" class="absolute inset-0 w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 flex flex-col justify-center px-12 text-white">
            <h2 class="text-4xl font-bold mb-6">Doğallığa İlk Adım 🌱</h2>
            <p class="text-lg text-gray-200 leading-relaxed">
                "Bir tohum ekmek, geleceğe inanmaktır." <br>
                Doğal Tohum ailesine katılarak genetiği korunmuş, sağlıklı ve %100 yerli tohumlara ulaşın.
            </p>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16 bg-gray-50">
        <div class="max-w-md w-full">
            <div class="text-center lg:text-left mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900">Hesap Oluşturun</h2>
            </div>

            <?php if($message): ?>
                <div class="mb-6 p-4 rounded-lg text-sm font-bold <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="space-y-5" action="" method="POST" autocomplete="off">
                <input type="text" style="display:none">
                <input type="password" style="display:none">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ad Soyad</label>
                    <input name="name" type="text" required autocomplete="new-name" class="w-full px-4 py-3 rounded-xl border outline-none shadow-sm" placeholder="Adınız ve Soyadınız">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">E-Posta Adresi</label>
                    <input name="email" type="email" required autocomplete="new-email" class="w-full px-4 py-3 rounded-xl border outline-none shadow-sm" placeholder="ornek@mail.com">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Telefon Numarası</label>
                    <input name="phone" type="text" required autocomplete="new-phone" class="w-full px-4 py-3 rounded-xl border outline-none shadow-sm" placeholder="0555 123 45 67">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Şifre</label>
                        <input name="password" type="password" required autocomplete="new-password" class="w-full px-4 py-3 rounded-xl border outline-none shadow-sm" placeholder="******">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Şifre (Tekrar)</label>
                        <input name="password_confirm" type="password" required autocomplete="new-password" class="w-full px-4 py-3 rounded-xl border outline-none shadow-sm" placeholder="******">
                    </div>
                </div>
                <button type="submit" class="w-full bg-nature-dark text-white font-bold py-4 rounded-xl hover:bg-nature-green transition shadow-lg mt-2">
                    Ücretsiz Kayıt Ol
                </button>
            </form>
            <div class="mt-8 text-center text-sm text-gray-600">
                Zaten bir hesabınız var mı? <a href="login.php" class="font-bold text-nature-green hover:underline">Giriş Yap</a>
            </div>
        </div>
    </div>
</div>

<?php 
// --- 📧 ARKA PLANDA MAİL GÖNDERME KODU ---
if(isset($_SESSION['send_welcome_email'])) {
    $emailData = $_SESSION['send_welcome_email'];
    if(file_exists('includes/PHPMailer/PHPMailer.php')) {
        require_once 'includes/PHPMailer/Exception.php';
        require_once 'includes/PHPMailer/PHPMailer.php';
        require_once 'includes/PHPMailer/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Netgsm veya Mail Servisin
            $mail->SMTPAuth = true;
            $mail->Username = 'rosadellacan@gmail.com'; 
            $mail->Password = 'smsuepcqiaodpylq'; 
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('rosadellacan@gmail.com', 'Doğal Tohum Dünyası');
            $mail->addAddress($emailData['email'], $emailData['name']);
            $mail->isHTML(true);
            $mail->Subject = "Hoş Geldin! 🌱";
            $mail->Body = "<h1>Merhaba " . $emailData['name'] . "</h1><p>Aramıza hoş geldin! Tohumlarımızla kavuşman için çok sabırsızız</p>";
            $mail->send();
        } catch (Exception $e) { }
    }
    unset($_SESSION['send_welcome_email']);
}
require_once 'includes/footer.php'; 
?>