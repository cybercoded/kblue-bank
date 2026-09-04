document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.auth-form');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.dataset.tab === 'login' ? 'loginForm' : 'openAccountForm';
            document.getElementById(target).classList.add('active');
            clearMessage();
        });
    });

    // Login
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/login.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                showMessage(data.message || 'Login failed', 'error');
            }
        } catch (err) {
            showMessage('Network error. Please try again.', 'error');
        }
    });

    // Open Account
    document.getElementById('openAccountForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/open_account.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showMessage(data.message, 'success');
                e.target.reset();
            } else {
                showMessage(data.message || 'Application failed', 'error');
            }
        } catch (err) {
            showMessage('Network error. Please try again.', 'error');
        }
    });

    function showMessage(msg, type) {
        const el = document.getElementById('message');
        el.textContent = msg;
        el.className = 'message ' + type;
    }

    function clearMessage() {
        const el = document.getElementById('message');
        el.className = 'message';
        el.textContent = '';
    }
});
