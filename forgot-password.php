<?php
require_once 'includes/header.php';

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$message = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        // E-posta var mı kontrol et
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 6 haneli rastgele kod üret
            $resetCode = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime('+15 minutes'));

            // Veritabanına kaydet
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $update->execute([$resetCode, $expires, $user['id']]);

            // PHPMailer Dosyalarını Yükle
            $excPath = 'includes/PHPMailer/Exception.php';
            $phpPath = 'includes/PHPMailer/PHPMailer.php';
            $smtPath = 'includes/PHPMailer/SMTP.php';

            if(file_exists($phpPath)) {
                require_once $excPath;
                require_once $phpPath;
                require_once $smtPath;

                // Sınıfı doğrudan çağırıyoruz (Namespace çakışmasını önlemek için)
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'rosadellacan@gmail.com'; 
                    $mail->Password   = 'smsuepcqiaodpylq'; 
                    $mail->SMTPSecure = 'ssl'; // Gmail için SSL
                    $mail->Port       = 465;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('rosadellacan@gmail.com', 'Doğal Tohum Dünyası');
                    $mail->addAddress($email, $user['full_name']);

                    $mail->isHTML(true);
                    $mail->Subject = "Sifre Sifirlama Kodu: $resetCode";
                    
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
                        <div style='background: #059669; color: white; padding: 20px; text-align: center;'>
                            <h1>Şifre Sıfırlama 🌱</h1>
                        </div>
                        <div style='padding: 20px; color: #333; text-align: center;'>
                            <p>Merhaba <strong>" . $user['full_name'] . "</strong>,</p>
                            <p>Şifreni sıfırlamak için kullanman gereken 6 haneli kod aşağıdadır:</p>
                            <div style='background: #f3f4f6; padding: 20px; font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #059669; margin: 20px 0;'>
                                $resetCode
                            </div>
                            <p>Bu kod 15 dakika boyunca geçerlidir.</p>
                        </div>
                    </div>";

                    $mail->send();
                    
                    $_SESSION['reset_email'] = $email;
                    echo "<script>window.location.href='verify-code.php';</script>";
                    exit;

                } catch (Exception $e) {
                    $message = "Mail hatası: " . $mail->ErrorInfo;
                    $msgType = "error";
                }
            } else {
                $message = "Hata: PHPMailer dosyaları 'includes/PHPMailer/' içinde bulunamadı!";
                $msgType = "error";
            }
        } else {
            $message = "Bu e-posta adresiyle kayıtlı üye bulunamadı.";
            $msgType = "error";
        }
    } else {
        $message = "Lütfen e-posta adresinizi yazın.";
        $msgType = "error";
    }
}
?>

<div class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
        <div class="text-center">
            <span class="text-5xl">🔑</span>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Şifremi Unuttum</h2>
            <p class="mt-2 text-sm text-gray-600">
                E-posta adresinizi girin, size 6 haneli bir sıfırlama kodu gönderelim.
            </p>
        </div>
        
        <?php if($message): ?>
            <div class="p-4 rounded-md text-sm font-bold text-center <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="" method="POST">
            <input name="email" type="email" required class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-nature-green focus:border-nature-green sm:text-sm" placeholder="Kayıtlı E-posta Adresiniz">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-nature-green hover:bg-nature-dark transition">
                Sıfırlama Kodu Gönder
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="font-medium text-nature-green hover:text-nature-dark">← Giriş Ekranına Dön</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>