document.addEventListener('DOMContentLoaded', () => {
    // Navigation
    const navItems = document.querySelectorAll('.nav-item[data-section]');
    const sections = document.querySelectorAll('.content-section');
    const pageTitle = document.getElementById('page-title');

    const titles = {
        overview: 'Account Overview',
        deposit: 'Cash Deposit',
        withdraw: 'Cash Withdrawal',
        transfer: 'Funds Transfer',
        bills: 'Utility Bill Payments',
        history: 'Transaction History',
        admin: 'Admin Panel'
    };

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const section = item.dataset.section;
            navItems.forEach(n => n.classList.remove('active'));
            item.classList.add('active');
            sections.forEach(s => s.classList.remove('active'));
            document.getElementById('section-' + section).classList.add('active');
            pageTitle.textContent = titles[section] || 'Dashboard';

            if (section === 'history') loadFullHistory();
            if (section === 'admin') {
                loadApplications();
                loadCustomers();
            }
        });
    });

    // Deposit
    document.getElementById('depositForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('type', 'deposit');
        await processTransaction('api/transaction.php', formData, e.target);
    });

    // Withdraw
    document.getElementById('withdrawForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('type', 'withdrawal');
        await processTransaction('api/transaction.php', formData, e.target);
    });

    // Transfer
    document.getElementById('transferForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('type', 'transfer');
        await processTransaction('api/transaction.php', formData, e.target);
    });

    // Bills
    document.getElementById('billForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('type', 'bill');
        await processTransaction('api/transaction.php', formData, e.target);
    });

    async function processTransaction(url, formData, form) {
        try {
            const res = await fetch(url, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                form.reset();
                if (data.new_balance !== undefined) {
                    updateBalance(data.new_balance);
                }
                // Refresh recent if on overview
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message || 'Transaction failed', 'error');
            }
        } catch (err) {
            showToast('Network error', 'error');
        }
    }

    function updateBalance(bal) {
        document.getElementById('current-balance').textContent = '₦' + Number(bal).toLocaleString('en-NG', { minimumFractionDigits: 2 });
        currentUser.balance = bal;
    }

    function showToast(msg, type) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = 'toast ' + type + ' show';
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    async function loadFullHistory() {
        try {
            const res = await fetch('api/history.php');
            const data = await res.json();
            const tbody = document.getElementById('full-history-body');
            if (!data.success || !data.transactions.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="empty">No transactions found</td></tr>';
                return;
            }
            tbody.innerHTML = data.transactions.map(txn => {
                const isCredit = ['deposit', 'transfer_in'].includes(txn.transaction_type);
                return `<tr>
                    <td>${formatDate(txn.created_at)}</td>
                    <td><small>${txn.reference || '-'}</small></td>
                    <td><span class="badge badge-${txn.transaction_type}">${txn.transaction_type.replace('_', ' ')}</span></td>
                    <td>${escapeHtml(txn.description || '-')}</td>
                    <td class="${isCredit ? 'credit' : 'debit'}">${isCredit ? '+' : '-'}₦${Number(txn.amount).toLocaleString('en-NG', {minimumFractionDigits:2})}</td>
                    <td>₦${Number(txn.balance_after).toLocaleString('en-NG', {minimumFractionDigits:2})}</td>
                    <td>${txn.status}</td>
                </tr>`;
            }).join('');
        } catch (err) {
            console.error(err);
        }
    }

    async function loadApplications() {
        const el = document.getElementById('applications-list');
        try {
            const res = await fetch('api/admin.php?action=applications');
            const data = await res.json();
            if (!data.success || !data.applications.length) {
                el.innerHTML = '<p class="empty">No pending applications</p>';
                return;
            }
            el.innerHTML = `<table class="admin-table">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>ID</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>${data.applications.map(app => `
                    <tr>
                        <td>${escapeHtml(app.full_name)}</td>
                        <td>${escapeHtml(app.email)}</td>
                        <td>${escapeHtml(app.phone)}</td>
                        <td>${escapeHtml(app.id_type)}: ${escapeHtml(app.id_number)}</td>
                        <td>${formatDate(app.created_at)}</td>
                        <td class="admin-actions">
                            <button class="btn btn-success btn-sm" onclick="approveApp(${app.id})">Approve</button>
                            <button class="btn btn-danger btn-sm" onclick="rejectApp(${app.id})">Reject</button>
                        </td>
                    </tr>
                `).join('')}</tbody>
            </table>`;
        } catch (err) {
            el.innerHTML = '<p>Error loading applications</p>';
        }
    }

    async function loadCustomers() {
        const el = document.getElementById('customers-list');
        try {
            const res = await fetch('api/admin.php?action=customers');
            const data = await res.json();
            if (!data.success) {
                el.innerHTML = '<p>Error</p>';
                return;
            }
            el.innerHTML = `<table class="admin-table">
                <thead><tr><th>Account</th><th>Name</th><th>Email</th><th>Balance</th><th>Status</th></tr></thead>
                <tbody>${data.customers.map(c => `
                    <tr>
                        <td>${escapeHtml(c.account_number)}</td>
                        <td>${escapeHtml(c.full_name)}</td>
                        <td>${escapeHtml(c.email)}</td>
                        <td>₦${Number(c.balance).toLocaleString('en-NG', {minimumFractionDigits:2})}</td>
                        <td class="status-${c.status}">${c.status}</td>
                    </tr>
                `).join('')}</tbody>
            </table>`;
        } catch (err) {
            el.innerHTML = '<p>Error loading customers</p>';
        }
    }

    window.approveApp = async (id) => {
        if (!confirm('Approve this application and create account?')) return;
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'approve', id})
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) loadApplications();
    };

    window.rejectApp = async (id) => {
        if (!confirm('Reject this application?')) return;
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'reject', id})
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) loadApplications();
    };

    function formatDate(str) {
        const d = new Date(str);
        return d.toLocaleDateString('en-NG', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
