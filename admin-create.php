<?php
require_once 'config/db.php';

try {
    // 1. Önce eski admin varsa silelim (Çakışma olmasın)
    $email = 'admin@hotmail.com';
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$email]);

    // 2. Yeni Admini Oluştur
    $password = password_hash("1234", PASSWORD_DEFAULT); // Şifre:?
    
    $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['Süper Yönetici', $email, $password, 'admin']);

    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
            <h1 style='color: green;'>✅ Admin Hesabı Oluşturuldu!</h1>
            <p>Aşağıdaki bilgilerle giriş yapabilirsin:</p>
            <div style='background: #f0f0f0; padding: 20px; display: inline-block; border-radius: 10px; text-align: left;'>
                <strong>E-posta:</strong> admin@dogaltohum.com<br>
                <strong>Şifre:</strong> 123
            </div>
            <br><br>
            <a href='admin/login.php' style='background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Giriş Sayfasına Git →</a>
          </div>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>