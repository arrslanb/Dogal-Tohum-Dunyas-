<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Yorumu sil
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
}

// Listeye geri dön
header("Location: comments.php");
exit;
?>