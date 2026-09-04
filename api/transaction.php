<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$type = $_POST['type'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$description = trim($_POST['description'] ?? '');
$userId = $_SESSION['user_id'];

if ($amount < 100) {
    echo json_encode(['success' => false, 'message' => 'Minimum amount is ₦100']);
    exit;
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    // Lock user row
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        throw new Exception('Account not active');
    }

    $ref = generateReference();
    $newBalance = $user['balance'];

    switch ($type) {
        case 'deposit':
            $newBalance = $user['balance'] + $amount;
            $desc = $description ?: 'Cash Deposit';
            $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference) VALUES (?, 'deposit', ?, ?, ?, ?)");
            $stmt->execute([$userId, $amount, $newBalance, $desc, $ref]);
            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $userId]);
            $message = "Deposit of ₦" . number_format($amount, 2) . " successful";
            break;

        case 'withdrawal':
            if ($user['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            $newBalance = $user['balance'] - $amount;
            $desc = $description ?: 'Cash Withdrawal';
            $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference) VALUES (?, 'withdrawal', ?, ?, ?, ?)");
            $stmt->execute([$userId, $amount, $newBalance, $desc, $ref]);
            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $userId]);
            $message = "Withdrawal of ₦" . number_format($amount, 2) . " successful";
            break;

        case 'transfer':
            $recipientAcc = trim($_POST['recipient'] ?? '');
            if (!$recipientAcc) {
                throw new Exception('Recipient account required');
            }
            if ($recipientAcc === $user['account_number']) {
                throw new Exception('Cannot transfer to yourself');
            }
            if ($user['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }

            $stmt = $pdo->prepare("SELECT * FROM customers WHERE account_number = ? AND status = 'active' FOR UPDATE");
            $stmt->execute([$recipientAcc]);
            $recipient = $stmt->fetch();
            if (!$recipient) {
                throw new Exception('Recipient account not found or inactive');
            }

            $newBalance = $user['balance'] - $amount;
            $recipientNewBal = $recipient['balance'] + $amount;
            $desc = $description ?: ('Transfer to ' . $recipientAcc);
            $refIn = generateReference();

            // Out
            $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference, related_account) VALUES (?, 'transfer_out', ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $amount, $newBalance, $desc, $ref, $recipientAcc]);

            // In
            $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference, related_account) VALUES (?, 'transfer_in', ?, ?, ?, ?, ?)");
            $stmt->execute([$recipient['id'], $amount, $recipientNewBal, 'Transfer from ' . $user['account_number'], $refIn, $user['account_number']]);

            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $userId]);
            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$recipientNewBal, $recipient['id']]);

            $message = "Transfer of ₦" . number_format($amount, 2) . " to " . $recipientAcc . " successful";
            break;

        case 'bill':
            $provider = trim($_POST['provider'] ?? '');
            $meter = trim($_POST['meter'] ?? '');
            if (!$provider || !$meter) {
                throw new Exception('Provider and meter/account number required');
            }
            if ($user['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            $newBalance = $user['balance'] - $amount;
            $desc = "Bill payment - $provider ($meter)";
            $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference, utility_provider) VALUES (?, 'bill_payment', ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $amount, $newBalance, $desc, $ref, $provider]);
            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $userId]);
            $message = "Bill payment of ₦" . number_format($amount, 2) . " to $provider successful";
            break;

        default:
            throw new Exception('Invalid transaction type');
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => $message,
        'new_balance' => $newBalance,
        'reference' => $ref
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
