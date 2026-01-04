<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['cart'])) {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // Telefonu formdan alıyoruz
    $address = $_POST['address']; // Adres artık saf adres
    $total_price = $_POST['total_price'];

    try {
        $pdo->beginTransaction();

        // GÜNCELLEME BURADA: customer_phone alanını da ekledik
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $total_price]);
        
        $order_id = $pdo->lastInsertId();

        $ids = implode(',', array_keys($_SESSION['cart']));
        $stmtProducts = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
        
        while ($product = $stmtProducts->fetch(PDO::FETCH_ASSOC)) {
            $qty = $_SESSION['cart'][$product['id']];
            $price = $product['price'];
            
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmtItem->execute([$order_id, $product['id'], $qty, $price]);
        }

        $pdo->commit();
        unset($_SESSION['cart']);

        header("Location: success.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Hata: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>