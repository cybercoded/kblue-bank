# K-Blue Microfinance Bank - Banking Operations System (Demo)

**Bank:** K-Blue Microfinance Bank  
**Address:** Shop 2, Olu Owode Shopping Complex, Shasha, Old Owo Road, Akure, Ondo State, Nigeria

A demo web-based banking application supporting:

- Cash Deposits & Withdrawals
- Funds Transfers (internal)
- Utility Bill Payments
- Account Opening Assistance (with admin approval)

## Technologies
- Frontend: HTML5, CSS3, JavaScript (vanilla)
- Backend: PHP 8
- Database: MySQL 8

## Features Implemented
- Secure customer authentication (password hashing)
- Account balance management with transactional integrity
- Deposit / Withdrawal simulation
- Peer-to-peer fund transfers between accounts
- Utility bill payments (IBEDC, BEDC, DStv, GOtv, Water, etc.)
- Account opening application form
- Admin panel to approve/reject applications and view customers
- Transaction history with references
- Responsive modern UI

## Demo Credentials

| Role     | Email              | Password  | Account Number |
|----------|--------------------|-----------|----------------|
| Customer | john@example.com   | password  | KB10000001     |
| Customer | jane@example.com   | password  | KB10000002     |
| Admin    | admin@kblue.com    | password  | ADMIN001       |

## How to Run

1. Ensure MySQL is running and the database is set up:
   ```bash
   mysql -u root < schema.sql
   ```

2. Start the PHP development server from the project root:
   ```bash
   php -S localhost:8080
   ```

3. Open http://localhost:8080 in your browser.

## Project Structure
```
kblue-bank/
├── index.php              # Login & Account Opening
├── dashboard.php          # Main banking dashboard
├── schema.sql             # Database schema + sample data
├── includes/
│   └── config.php         # DB config, helpers, bank constants
├── api/
│   ├── login.php
│   ├── logout.php
│   ├── open_account.php
│   ├── transaction.php    # Deposit, Withdraw, Transfer, Bills
│   ├── history.php
│   └── admin.php
└── assets/
    ├── css/style.css
    └── js/
        ├── auth.js
        └── app.js
```

## Notes for Production
This is a **demo only**. For real deployment you would need:
- Proper environment configuration & secrets management
- HTTPS
- Stronger password policies & rate limiting
- Input validation hardening & CSRF protection
- Proper admin roles table (not relying on account number)
- Audit logging
- Integration with real payment gateways / core banking APIs
- KYC verification workflows
- Email/SMS notifications
