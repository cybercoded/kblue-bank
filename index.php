<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo BANK_NAME; ?> - Banking Operations System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="bank-logo">
                <div class="logo-icon">KB</div>
                <h1><?php echo BANK_NAME; ?></h1>
                <p class="tagline">Secure • Reliable • Accessible</p>
            </div>

            <div class="auth-tabs">
                <button class="tab-btn active" data-tab="login">Login</button>
                <button class="tab-btn" data-tab="open-account">Open Account</button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form active">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
                <p class="demo-hint">Demo credentials available on request<br><span style="font-size:0.7rem;opacity:0.85">john@example.com · admin@kblue.com</span></p>
            </form>

            <!-- Account Opening Form -->
            <form id="openAccountForm" class="auth-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="app_email">Email Address</label>
                    <input type="email" id="app_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="08012345678">
                </div>
                <div class="form-group">
                    <label for="address">Residential Address</label>
                    <textarea id="address" name="address" rows="2" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_type">ID Type</label>
                        <select id="id_type" name="id_type" required>
                            <option value="">Select...</option>
                            <option value="NIN">National ID (NIN)</option>
                            <option value="Voters Card">Voter's Card</option>
                            <option value="Drivers License">Driver's License</option>
                            <option value="Passport">International Passport</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_number">ID Number</label>
                        <input type="text" id="id_number" name="id_number" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </form>

            <div id="message" class="message"></div>
        </div>

        <div class="bank-info">
            <p><strong><?php echo BANK_NAME; ?></strong></p>
            <p><?php echo BANK_ADDRESS; ?></p>
            <p class="footer-note">Demo Banking Operations System • Cash Deposits & Withdrawals • Funds Transfers • Utility Bill Payments</p>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>
