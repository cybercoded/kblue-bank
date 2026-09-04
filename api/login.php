<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

if ($user['status'] === 'pending') {
    echo json_encode(['success' => false, 'message' => 'Account is pending approval']);
    exit;
}

if ($user['status'] === 'suspended') {
    echo json_encode(['success' => false, 'message' => 'Account is suspended']);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['account_number'] = $user['account_number'];
$_SESSION['full_name'] = $user['full_name'];

echo json_encode(['success' => true, 'message' => 'Login successful']);
?>
