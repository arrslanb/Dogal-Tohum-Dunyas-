<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Önce resmini silelim ki klasörde çöp kalmasın
    $stmt = $pdo->prepare("SELECT image FROM blog WHERE id = ?");
    $stmt->execute([$id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($blog) {
        $path = "../uploads/" . $blog['image'];
        if (file_exists($path)) {
            unlink($path);
        }
        
        // Veritabanından sil
        $del = $pdo->prepare("DELETE FROM blog WHERE id = ?");
        $del->execute([$id]);
    }
}
header("Location: blogs.php");
exit;
?>