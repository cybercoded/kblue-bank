<?php
require_once 'includes/config.php';
requireLogin();

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Get recent transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo BANK_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon small">KB</div>
                <span><?php echo BANK_SHORT; ?></span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active" data-section="overview">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
                    Overview
                </a>
                <a href="#" class="nav-item" data-section="deposit">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    Deposit
                </a>
                <a href="#" class="nav-item" data-section="withdraw">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    Withdraw
                </a>
                <a href="#" class="nav-item" data-section="transfer">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                    Transfer
                </a>
                <a href="#" class="nav-item" data-section="bills">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Pay Bills
                </a>
                <a href="#" class="nav-item" data-section="history">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Transactions
                </a>
                <?php if (isAdmin()): ?>
                <a href="#" class="nav-item" data-section="admin">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin
                </a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                    <small><?php echo htmlspecialchars($user['account_number']); ?></small>
                </div>
                <a href="api/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h2 id="page-title">Account Overview</h2>
                <div class="balance-display">
                    <span>Available Balance</span>
                    <strong id="current-balance">₦<?php echo number_format($user['balance'], 2); ?></strong>
                </div>
            </header>

            <!-- Overview Section -->
            <section id="section-overview" class="content-section active">
                <div class="cards-grid">
                    <div class="stat-card">
                        <h3>Account Number</h3>
                        <p class="stat-value"><?php echo htmlspecialchars($user['account_number']); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Account Status</h3>
                        <p class="stat-value status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Member Since</h3>
                        <p class="stat-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <div class="card">
                    <h3>Recent Transactions</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="recent-txns">
                                <?php if (empty($transactions)): ?>
                                <tr><td colspan="5" class="empty">No transactions yet</td></tr>
                                <?php else: ?>
                                <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($txn['created_at'])); ?></td>
                                    <td><span class="badge badge-<?php echo $txn['transaction_type']; ?>"><?php echo str_replace('_', ' ', ucfirst($txn['transaction_type'])); ?></span></td>
                                    <td><?php echo htmlspecialchars($txn['description'] ?? '-'); ?></td>
                                    <td class="<?php echo in_array($txn['transaction_type'], ['deposit','transfer_in']) ? 'credit' : 'debit'; ?>">
                                        <?php echo in_array($txn['transaction_type'], ['deposit','transfer_in']) ? '+' : '-'; ?>₦<?php echo number_format($txn['amount'], 2); ?>
                                    </td>
                                    <td>₦<?php echo number_format($txn['balance_after'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Deposit Section -->
            <section id="section-deposit" class="content-section">
                <div class="card form-card">
                    <h3>Cash Deposit</h3>
                    <p class="section-desc">Credit your account with cash or agent deposit. Funds are available immediately.</p>
                    <form id="depositForm">
                        <div class="form-group">
                            <label for="deposit_amount">Amount (₦)</label>
                            <input type="number" id="deposit_amount" name="amount" min="100" step="0.01" required placeholder="e.g. 5000">
                        </div>
                        <div class="form-group">
                            <label for="deposit_desc">Description / Narration</label>
                            <input type="text" id="deposit_desc" name="description" placeholder="Cash deposit">
                        </div>
                        <button type="submit" class="btn btn-primary">Process Deposit</button>
                    </form>
                </div>
            </section>

            <!-- Withdraw Section -->
            <section id="section-withdraw" class="content-section">
                <div class="card form-card">
                    <h3>Cash Withdrawal</h3>
                    <p class="section-desc">Debit cash from your available balance. Subject to account limits.</p>
                    <form id="withdrawForm">
                        <div class="form-group">
                            <label for="withdraw_amount">Amount (₦)</label>
                            <input type="number" id="withdraw_amount" name="amount" min="100" step="0.01" required placeholder="e.g. 2000">
                        </div>
                        <div class="form-group">
                            <label for="withdraw_desc">Description / Narration</label>
                            <input type="text" id="withdraw_desc" name="description" placeholder="Cash withdrawal">
                        </div>
                        <button type="submit" class="btn btn-primary">Process Withdrawal</button>
                    </form>
                </div>
            </section>

            <!-- Transfer Section -->
            <section id="section-transfer" class="content-section">
                <div class="card form-card">
                    <h3>Funds Transfer</h3>
                    <p class="section-desc">Send funds instantly to any active K-Blue account number.</p>
                    <form id="transferForm">
                        <div class="form-group">
                            <label for="recipient_account">Recipient Account Number</label>
                            <input type="text" id="recipient_account" name="recipient" required placeholder="KB10000002">
                        </div>
                        <div class="form-group">
                            <label for="transfer_amount">Amount (₦)</label>
                            <input type="number" id="transfer_amount" name="amount" min="100" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="transfer_desc">Narration</label>
                            <input type="text" id="transfer_desc" name="description" placeholder="Transfer to...">
                        </div>
                        <button type="submit" class="btn btn-primary">Send Transfer</button>
                    </form>
                </div>
            </section>

            <!-- Bills Section -->
            <section id="section-bills" class="content-section">
                <div class="card form-card">
                    <h3>Utility Bill Payments</h3>
                    <p class="section-desc">Settle electricity, water, cable TV and internet bills from your account.</p>
                    <form id="billForm">
                        <div class="form-group">
                            <label for="utility_provider">Utility Provider</label>
                            <select id="utility_provider" name="provider" required>
                                <option value="">Select provider...</option>
                                <option value="IBEDC">IBEDC (Electricity - Ibadan Disco)</option>
                                <option value="BEDC">BEDC (Electricity - Benin Disco)</option>
                                <option value="PHCN">PHCN / NEDC</option>
                                <option value="Water">State Water Corporation</option>
                                <option value="DSTV">DStv / MultiChoice</option>
                                <option value="Gotv">GOtv</option>
                                <option value="Startimes">StarTimes</option>
                                <option value="Internet">Internet Service Provider</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="meter_number">Meter / Account / Smartcard Number</label>
                            <input type="text" id="meter_number" name="meter" required placeholder="Enter meter or account number">
                        </div>
                        <div class="form-group">
                            <label for="bill_amount">Amount (₦)</label>
                            <input type="number" id="bill_amount" name="amount" min="100" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Pay Bill</button>
                    </form>
                </div>
            </section>

            <!-- History Section -->
            <section id="section-history" class="content-section">
                <div class="card">
                    <h3>Full Transaction History</h3>
                    <div class="table-responsive">
                        <table class="data-table" id="full-history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="full-history-body">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <?php if (isAdmin()): ?>
            <!-- Admin Section -->
            <section id="section-admin" class="content-section">
                <div class="card">
                    <h3>Pending Account Applications</h3>
                    <div id="applications-list">Loading...</div>
                </div>
                <div class="card" style="margin-top:1.5rem;">
                    <h3>All Customers</h3>
                    <div id="customers-list">Loading...</div>
                </div>
            </section>
            <?php endif; ?>

            <div id="toast" class="toast"></div>
        </main>
    </div>

    <script>
        const currentUser = {
            id: <?php echo $user['id']; ?>,
            balance: <?php echo $user['balance']; ?>,
            account: '<?php echo $user['account_number']; ?>',
            isAdmin: <?php echo isAdmin() ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
