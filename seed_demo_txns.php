<?php
/**
 * Seed realistic transactions for the main demo accounts
 * so Overview + Transactions pages show data when logged in as john/jane.
 */
require_once __DIR__ . '/includes/config.php';

$pdo = getDB();

$demoAccounts = ['KB10000001', 'KB10000002']; // John & Jane

$providers = ['IBEDC', 'BEDC', 'DSTV', 'Gotv', 'Water', 'Startimes', 'Internet'];
$depositNarrations = ['Cash Deposit', 'Salary Credit', 'Agent Deposit', 'POS Deposit', 'Branch Deposit', 'Mobile Deposit'];
$withdrawNarrations = ['Cash Withdrawal', 'ATM Withdrawal', 'Branch Withdrawal', 'POS Cash Out'];

function ref() {
    return 'TXN' . date('Ymd') . sprintf('%06d', mt_rand(100000, 999999));
}

foreach ($demoAccounts as $accNum) {
    $stmt = $pdo->prepare("SELECT id, account_number, balance FROM customers WHERE account_number = ?");
    $stmt->execute([$accNum]);
    $user = $stmt->fetch();
    if (!$user) {
        echo "Account $accNum not found, skipping\n";
        continue;
    }

    // Clear existing (optional) — keep any that exist
    // $pdo->prepare("DELETE FROM transactions WHERE account_id = ?")->execute([$user['id']]);

    $balance = (float)$user['balance'];
    $count = 0;

    $pdo->beginTransaction();
    try {
        // Create ~25 transactions per demo user spanning last 90 days
        for ($i = 0; $i < 25; $i++) {
            $daysAgo = mt_rand(1, 90);
            $hours = mt_rand(8, 18);
            $created = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days +{$hours} hours"));

            $roll = mt_rand(1, 100);

            if ($roll <= 30) {
                // Deposit
                $amount = mt_rand(20, 800) * 100; // 2k – 80k
                $balance += $amount;
                $type = 'deposit';
                $desc = $depositNarrations[array_rand($depositNarrations)];
                $related = null;
                $provider = null;
            } elseif ($roll <= 55) {
                // Withdrawal
                $amount = mt_rand(10, 300) * 100;
                if ($amount > $balance) $amount = max(500, (int)($balance * 0.3));
                $balance = max(0, $balance - $amount);
                $type = 'withdrawal';
                $desc = $withdrawNarrations[array_rand($withdrawNarrations)];
                $related = null;
                $provider = null;
            } elseif ($roll <= 75) {
                // Transfer out
                $amount = mt_rand(15, 250) * 100;
                if ($amount > $balance) $amount = max(500, (int)($balance * 0.2));
                $balance = max(0, $balance - $amount);
                $type = 'transfer_out';
                $related = 'KB' . str_pad(mt_rand(10000101, 10000300), 8, '0', STR_PAD_LEFT);
                $desc = "Transfer to $related";
                $provider = null;
            } elseif ($roll <= 88) {
                // Transfer in
                $amount = mt_rand(15, 400) * 100;
                $balance += $amount;
                $type = 'transfer_in';
                $related = 'KB' . str_pad(mt_rand(10000101, 10000300), 8, '0', STR_PAD_LEFT);
                $desc = "Transfer from $related";
                $provider = null;
            } else {
                // Bill
                $amount = mt_rand(8, 120) * 100;
                if ($amount > $balance) $amount = max(500, (int)($balance * 0.15));
                $balance = max(0, $balance - $amount);
                $type = 'bill_payment';
                $provider = $providers[array_rand($providers)];
                $meter = 'MET' . mt_rand(10000000, 99999999);
                $desc = "Bill payment - $provider ($meter)";
                $related = null;
            }

            $stmt = $pdo->prepare("
                INSERT INTO transactions 
                (account_id, transaction_type, amount, balance_after, description, reference, related_account, utility_provider, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)
            ");
            $stmt->execute([
                $user['id'],
                $type,
                $amount,
                round($balance, 2),
                $desc,
                ref(),
                $related,
                $provider,
                $created
            ]);
            $count++;
        }

        // Sync customer balance to final computed value
        $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?")->execute([round($balance, 2), $user['id']]);

        $pdo->commit();
        echo "✓ {$user['full_name']} ({$accNum}): $count transactions added. Balance now ₦" . number_format($balance, 2) . "\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "✗ Failed for $accNum: " . $e->getMessage() . "\n";
    }
}

echo "\nDone. Log in as john@example.com or jane@example.com to see transactions.\n";
?>
