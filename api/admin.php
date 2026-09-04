<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'applications') {
        $stmt = $pdo->query("SELECT * FROM account_applications WHERE status = 'pending' ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'applications' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'customers') {
        $stmt = $pdo->query("SELECT id, account_number, full_name, email, phone, balance, status, created_at FROM customers WHERE account_number != 'ADMIN001' ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'customers' => $stmt->fetchAll()]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $id = intval($input['id'] ?? 0);

    if ($action === 'approve' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM account_applications WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $app = $stmt->fetch();
        if (!$app) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            exit;
        }

        // Check email not taken
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$app['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }

        $accNum = generateAccountNumber();
        // Default password: password (same as demo)
        $hash = password_hash('password', PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (account_number, full_name, email, phone, password_hash, balance, status) VALUES (?, ?, ?, ?, ?, 0.00, 'active')");
            $stmt->execute([$accNum, $app['full_name'], $app['email'], $app['phone'], $hash]);

            $stmt = $pdo->prepare("UPDATE account_applications SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => "Account approved! Account number: $accNum (default password: password)"
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create account']);
        }
        exit;
    }

    if ($action === 'reject' && $id) {
        $stmt = $pdo->prepare("UPDATE account_applications SET status = 'rejected' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Application rejected']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
