<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

echo json_encode(['success' => true, 'transactions' => $transactions]);
?>
