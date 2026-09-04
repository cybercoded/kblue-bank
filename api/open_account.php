<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$id_type = trim($_POST['id_type'] ?? '');
$id_number = trim($_POST['id_number'] ?? '');

if (!$full_name || !$email || !$phone || !$address || !$id_type || !$id_number) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

$pdo = getDB();

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM account_applications WHERE email = ? AND status = 'pending'");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending application']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO account_applications (full_name, email, phone, address, id_type, id_number) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$full_name, $email, $phone, $address, $id_type, $id_number]);

echo json_encode([
    'success' => true,
    'message' => 'Application submitted successfully! Our team will review it shortly. You will be notified once approved.'
]);
?>
