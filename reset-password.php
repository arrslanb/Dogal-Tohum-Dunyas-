<?php
require_once 'includes/header.php';
if(!isset($_SESSION['code_verified'])) { header("Location: forgot-password.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];
    $email = $_SESSION['reset_email'];

    if($pass === $pass_confirm) {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ?");
        $update->execute([$hashed, $email]);
        
        unset($_SESSION['reset_email'], $_SESSION['code_verified']);
        echo "<script>alert('Şifreniz güncellendi!'); window.location.href='login.php';</script>";
    } else { $error = "Şifreler uyuşmuyor!"; }
}
?>
<div class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <form method="POST" class="bg-white p-8 rounded-xl shadow-lg max-w-sm w-full space-y-4">
        <h2 class="text-2xl font-bold text-center">Yeni Şifre Oluştur</h2>
        <input type="password" name="password" placeholder="Yeni Şifre" required class="w-full border p-3 rounded-lg outline-none">
        <input type="password" name="password_confirm" placeholder="Şifre Tekrar" required class="w-full border p-3 rounded-lg outline-none">
        <button type="submit" class="w-full bg-nature-green text-white py-3 rounded-lg font-bold">Şifreyi Güncelle</button>
    </form>
</div>