<?php
session_start();
require_once '../config/db.php';

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: index.php");
        exit;
    } else {
        header("Location: ../index.php"); 
        exit;
    }
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] == 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $message = "Bu alana sadece yöneticiler girebilir!";
            }
        } else {
            $message = "E-posta veya şifre hatalı.";
        }
    } else {
        $message = "Lütfen tüm alanları doldurun.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Girişi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">

    <div class="bg-white p-10 rounded-xl shadow-2xl w-96">
        <div class="text-center mb-8">
            <span class="text-4xl">🔐</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Yönetici Girişi</h1>
        </div>

        <?php if($message): ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 text-sm rounded border border-red-200 text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6" autocomplete="off">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">E-posta Adresi</label>
                <input type="email" name="email" value="" required class="w-full px-4 py-3 rounded-lg bg-gray-100 border focus:bg-white focus:outline-none transition" placeholder="E-posta adresiniz" autocomplete="new-password">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Şifre</label>
                <input type="password" name="password" value="" required class="w-full px-4 py-3 rounded-lg bg-gray-100 border focus:bg-white focus:outline-none transition" placeholder="Şifreniz" autocomplete="new-password">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition shadow-lg">
                Giriş Yap
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="../index.php" class="text-sm text-gray-400 hover:text-gray-600">← Siteye Dön</a>
        </div>
    </div>

</body>
</html>