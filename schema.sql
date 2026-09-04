CREATE DATABASE IF NOT EXISTS kblue_bank;
USE kblue_bank;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active', 'pending', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer_out', 'transfer_in', 'bill_payment') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    reference VARCHAR(50) UNIQUE,
    related_account VARCHAR(20), -- for transfers
    utility_provider VARCHAR(50), -- for bills
    status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES customers(id)
);

CREATE TABLE account_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    id_type VARCHAR(50),
    id_number VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO customers (account_number, full_name, email, phone, password_hash, balance, status) VALUES
('KB10000001', 'John Doe', 'john@example.com', '08012345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 50000.00, 'active'), -- password: password
('KB10000002', 'Jane Smith', 'jane@example.com', '08087654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25000.00, 'active');

-- Admin user for demo (same password)
INSERT INTO customers (account_number, full_name, email, phone, password_hash, balance, status) VALUES
('ADMIN001', 'Bank Admin', 'admin@kblue.com', '08000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0.00, 'active');
