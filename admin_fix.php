<?php
require_once 'config/db.php';

$password = "123456"; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$username = "admin";

try {
    // Önce eski admin varsa silelim ki çakışma olmasın
    $pdo->exec("DELETE FROM users WHERE username = '$username'");

    // Yeni admini taze taze ekleyelim
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'admin')");
    $stmt->execute(['username' => $username, 'password' => $hashed_password]);

    echo "<h1 style='color:green; font-family:sans-serif; text-align:center; margin-top:50px;'>
            ✅ Başarılı!<br>
            Kullanıcı Adı: admin<br>
            Şifre: 123456<br><br>
            <a href='admin/login.php'>Giriş Yapmak İçin Tıkla</a>
          </h1>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>