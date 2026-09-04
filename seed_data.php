<?php
/**
 * Seed 200 realistic Nigerian records for:
 * - account_applications
 * - customers
 * - transactions
 */
require_once __DIR__ . '/includes/config.php';

$pdo = getDB();

// Nigerian first names (common)
$firstNames = [
    'Adebayo', 'Chinedu', 'Oluwaseun', 'Ngozi', 'Ifeanyi', 'Funke', 'Emeka', 'Amaka',
    'Tunde', 'Chioma', 'Bolanle', 'Ikechukwu', 'Yemi', 'Nneka', 'Segun', 'Blessing',
    'Obinna', 'Tope', 'Chukwudi', 'Aisha', 'Kunle', 'Fatima', 'Uche', 'Halima',
    'Femi', 'Zainab', 'Chisom', 'Rasheed', 'Adaeze', 'Musa', 'Omolara', 'Ibrahim',
    'Kemi', 'Sani', 'Yetunde', 'Bello', 'Opeyemi', 'Hassan', 'Titilayo', 'Yusuf',
    'Bukola', 'Abdul', 'Ijeoma', 'Suleiman', 'Damilola', 'Ganiyu', 'Chinyere', 'Rafiu',
    'Olumide', 'Hafsat', 'Ayodele', 'Maryam', 'Babatunde', 'Amina', 'Olatunji', 'Hadiza',
    'Kehinde', 'Salisu', 'Folake', 'Kabiru', 'Rachael', 'Usman', 'Shade', 'Abdullahi',
    'Temitope', 'Bashir', 'Onyinye', 'Ismail', 'Modupe', 'Garba', 'Esther', 'Aminu',
    'Grace', 'Mohammed', 'Peace', 'Aliyu', 'Joy', 'Habiba', 'Mercy', 'Farouk',
    'Victoria', 'Idris', 'Ruth', 'Sadiq', 'Faith', 'Rukayat', 'Deborah', 'Bashir',
    'Elizabeth', 'Nasir', 'Sarah', 'Lukman', 'Hannah', 'Quadri', 'Mary', 'Shola'
];

$lastNames = [
    'Okonkwo', 'Adeyemi', 'Okafor', 'Ibrahim', 'Nwosu', 'Bello', 'Eze', 'Mohammed',
    'Ogunleye', 'Abubakar', 'Okeke', 'Yusuf', 'Nwachukwu', 'Adebayo', 'Lawal', 'Obi',
    'Afolabi', 'Suleiman', 'Chukwu', 'Balogun', 'Ojo', 'Musa', 'Umeh', 'Aliyu',
    'Akinola', 'Hassan', 'Okoro', 'Garba', 'Adeniyi', 'Usman', 'Nwankwo', 'Abdullahi',
    'Olatunji', 'Sani', 'Ike', 'Bello', 'Onyeka', 'Raji', 'Chibueze', 'Danjuma',
    'Ajayi', 'Ismail', 'Okorie', 'Bashir', 'Emeka', 'Farouk', 'Ogunbiyi', 'Salisu',
    'Anyanwu', 'Nasir', 'Fashola', 'Idris', 'Onuoha', 'Kabiru', 'Adekunle', 'Sadiq',
    'Ugwu', 'Quadri', 'Akinyemi', 'Lukman', 'Nnamdi', 'Shola', 'Oladipo', 'Aminu'
];

$streets = [
    'Olu Owode Shopping Complex', 'Oba Adesida Road', 'Oyemekun Road', 'Alagbaka Estate',
    'Ijapo Estate', 'Oda Road', 'Akure-Owo Road', 'Shasha Road', 'Ilesa Road',
    'Hospital Road', 'Cathedral Road', 'Ado-Ekiti Road', 'Oke-Aro', 'Isikan',
    'Arakale Road', 'Ondo Road', 'Idanre Road', 'FUTA South Gate', 'Igbatoro Road',
    'Oke-Ijebu', 'Moferere', 'Adegbola Street', 'Oke-Aro Layout', 'Alade Street'
];

$areas = ['Akure', 'Ondo', 'Owo', 'Ikare', 'Ore', 'Okitipupa', 'Irele', 'Ile-Oluji', 'Odigbo'];

$idTypes = ['NIN', 'Voters Card', 'Drivers License', 'Passport'];

$providers = ['IBEDC', 'BEDC', 'PHCN', 'Water', 'DSTV', 'Gotv', 'Startimes', 'Internet'];

$txnTypes = ['deposit', 'withdrawal', 'transfer_out', 'transfer_in', 'bill_payment'];

function randomPhone() {
    $prefixes = ['0803', '0805', '0806', '0807', '0808', '0809', '0810', '0813', '0814', '0816', '0903', '0909', '0913'];
    return $prefixes[array_rand($prefixes)] . sprintf('%07d', mt_rand(0, 9999999));
}

function randomEmail($first, $last, $i) {
    $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com'];
    $name = strtolower($first . '.' . $last);
    $name = preg_replace('/[^a-z.]/', '', $name);
    return $name . $i . '@' . $domains[array_rand($domains)];
}

function generateRef() {
    return 'TXN' . date('Ymd') . sprintf('%06d', mt_rand(1, 999999));
}

echo "Starting seed...\n";

// ========== 1. CUSTOMERS (200 active) ==========
echo "Seeding customers...\n";
$passwordHash = password_hash('password', PASSWORD_DEFAULT);
$customerIds = []; // will hold [id => account_number]

// Keep existing 3 demo accounts
$stmt = $pdo->query("SELECT id, account_number FROM customers");
while ($row = $stmt->fetch()) {
    $customerIds[$row['id']] = $row['account_number'];
}

$pdo->beginTransaction();
try {
    for ($i = 1; $i <= 200; $i++) {
        $first = $firstNames[array_rand($firstNames)];
        $last  = $lastNames[array_rand($lastNames)];
        $full  = "$first $last";
        $email = randomEmail($first, $last, $i);
        $phone = randomPhone();
        $acc   = 'KB' . str_pad((10000100 + $i), 8, '0', STR_PAD_LEFT); // start after demo accounts
        $balance = round(mt_rand(5000, 2500000) / 100, 2); // ₦50 – ₦25,000
        // Actually make more realistic: ₦5,000 – ₦2,500,000
        $balance = mt_rand(50, 25000) * 100; // 5k to 2.5m in steps of 100
        $status = (mt_rand(1, 100) <= 92) ? 'active' : ((mt_rand(0,1)) ? 'pending' : 'suspended');

        $stmt = $pdo->prepare("INSERT INTO customers (account_number, full_name, email, phone, password_hash, balance, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $created = date('Y-m-d H:i:s', strtotime('-' . mt_rand(30, 900) . ' days'));
        $stmt->execute([$acc, $full, $email, $phone, $passwordHash, $balance, $status, $created]);
        $id = $pdo->lastInsertId();
        $customerIds[$id] = $acc;
    }
    $pdo->commit();
    echo "  → 200 customers inserted\n";
} catch (Exception $e) {
    $pdo->rollBack();
    die("Customer seed failed: " . $e->getMessage() . "\n");
}

// ========== 2. ACCOUNT APPLICATIONS (200) ==========
echo "Seeding account applications...\n";
$pdo->beginTransaction();
try {
    for ($i = 1; $i <= 200; $i++) {
        $first = $firstNames[array_rand($firstNames)];
        $last  = $lastNames[array_rand($lastNames)];
        $full  = "$first $last";
        $email = randomEmail($first, $last, 9000 + $i); // different range
        $phone = randomPhone();
        $street = $streets[array_rand($streets)];
        $area   = $areas[array_rand($areas)];
        $address = "No. " . mt_rand(1, 120) . ", $street, $area, Ondo State";
        $idType = $idTypes[array_rand($idTypes)];
        $idNum  = strtoupper(substr(md5($full . $i), 0, 11));
        $status = ['pending', 'pending', 'pending', 'approved', 'rejected'][array_rand([0,1,2,3,4])]; // more pending
        $created = date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 60) . ' days'));

        $stmt = $pdo->prepare("INSERT INTO account_applications (full_name, email, phone, address, id_type, id_number, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full, $email, $phone, $address, $idType, $idNum, $status, $created]);
    }
    $pdo->commit();
    echo "  → 200 applications inserted\n";
} catch (Exception $e) {
    $pdo->rollBack();
    die("Applications seed failed: " . $e->getMessage() . "\n");
}

// ========== 3. TRANSACTIONS (200) ==========
echo "Seeding transactions...\n";
// Prefer active customers
$activeCustomers = [];
$stmt = $pdo->query("SELECT id, account_number, balance FROM customers WHERE status = 'active'");
while ($row = $stmt->fetch()) {
    $activeCustomers[] = $row;
}

if (count($activeCustomers) < 5) {
    die("Not enough active customers for transactions\n");
}

$pdo->beginTransaction();
try {
    for ($i = 1; $i <= 200; $i++) {
        $cust = $activeCustomers[array_rand($activeCustomers)];
        $type = $txnTypes[array_rand($txnTypes)];
        $amount = 0;
        $desc = '';
        $related = null;
        $provider = null;
        $balAfter = $cust['balance']; // approximate (not updating real balances for seed)

        switch ($type) {
            case 'deposit':
                $amount = mt_rand(10, 500) * 100; // ₦1,000 – ₦50,000
                $desc = ['Cash Deposit', 'Salary Deposit', 'POS Deposit', 'Agent Deposit', 'Branch Deposit'][array_rand([0,1,2,3,4])];
                $balAfter += $amount;
                break;
            case 'withdrawal':
                $amount = mt_rand(5, 200) * 100; // ₦500 – ₦20,000
                $desc = ['Cash Withdrawal', 'ATM Withdrawal', 'Branch Withdrawal'][array_rand([0,1,2])];
                $balAfter = max(0, $balAfter - $amount);
                break;
            case 'transfer_out':
                $amount = mt_rand(10, 300) * 100;
                $other = $activeCustomers[array_rand($activeCustomers)];
                $related = $other['account_number'];
                $desc = "Transfer to " . $related;
                $balAfter = max(0, $balAfter - $amount);
                break;
            case 'transfer_in':
                $amount = mt_rand(10, 300) * 100;
                $other = $activeCustomers[array_rand($activeCustomers)];
                $related = $other['account_number'];
                $desc = "Transfer from " . $related;
                $balAfter += $amount;
                break;
            case 'bill_payment':
                $amount = mt_rand(5, 150) * 100; // ₦500 – ₦15,000
                $provider = $providers[array_rand($providers)];
                $meter = 'MET' . mt_rand(10000000, 99999999);
                $desc = "Bill payment - $provider ($meter)";
                $balAfter = max(0, $balAfter - $amount);
                break;
        }

        $ref = generateRef() . sprintf('%03d', $i);
        $created = date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 180) . ' days +' . mt_rand(0, 23) . ' hours'));

        $stmt = $pdo->prepare("INSERT INTO transactions (account_id, transaction_type, amount, balance_after, description, reference, related_account, utility_provider, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)");
        $stmt->execute([
            $cust['id'],
            $type,
            $amount,
            $balAfter,
            $desc,
            $ref,
            $related,
            $provider,
            $created
        ]);
    }
    $pdo->commit();
    echo "  → 200 transactions inserted\n";
} catch (Exception $e) {
    $pdo->rollBack();
    die("Transactions seed failed: " . $e->getMessage() . "\n");
}

// Summary
echo "\n=== SEED COMPLETE ===\n";
$counts = $pdo->query("
    SELECT 'customers' as tbl, COUNT(*) as cnt FROM customers
    UNION ALL SELECT 'account_applications', COUNT(*) FROM account_applications
    UNION ALL SELECT 'transactions', COUNT(*) FROM transactions
")->fetchAll();
foreach ($counts as $c) {
    echo str_pad($c['tbl'], 25) . ": " . $c['cnt'] . "\n";
}
echo "\nDone. All new customers use password: password\n";
?>
