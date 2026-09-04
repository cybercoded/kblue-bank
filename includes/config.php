<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kblue_bank');

// Bank info
define('BANK_NAME', 'K-Blue Microfinance Bank');
define('BANK_ADDRESS', 'Shop 2, Olu Owode Shopping Complex, Shasha, Old Owo Road, Akure, Ondo State, Nigeria');
define('BANK_SHORT', 'K-Blue MFB');

// Session
session_start();

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }
    return $pdo;
}

function generateAccountNumber() {
    return 'KB' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
}

function generateReference() {
    return 'TXN' . date('YmdHis') . mt_rand(1000, 9999);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['account_number']) && $_SESSION['account_number'] === 'ADMIN001';
}
?>
