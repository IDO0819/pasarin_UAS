/**
 * auth.js - Menangani submit form login & register secara AJAX
 * ke REST API api/auth.php.
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    if (registerForm) registerForm.addEventListener('submit', handleRegister);
});

function showFormError(message) {
    const box = document.getElementById('form-alert');
    if (box) {
        box.textContent = message;
        box.className = 'alert alert-error';
        box.style.display = 'block';
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;

    try {
        const result = await apiRequest('auth.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'login',
                email: form.email.value,
                password: form.password.value,
            }),
        });

        if (result.success) {
            window.location.href = result.role === 'admin'
                ? `${BASE_URL}/admin/index.php`
                : `${BASE_URL}/index.php`;
        } else {
            showFormError(result.message);
            btn.disabled = false;
        }
    } catch (err) {
        showFormError('Terjadi kesalahan koneksi');
        btn.disabled = false;
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');

    if (form.password.value.length < 8) {
        showFormError('Password minimal 8 karakter');
        return;
    }
    if (form.password.value !== form.password_confirm.value) {
        showFormError('Konfirmasi password tidak sama');
        return;
    }

    btn.disabled = true;

    try {
        const result = await apiRequest('auth.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'register',
                username: form.username.value,
                email: form.email.value,
                password: form.password.value,
            }),
        });

        if (result.success) {
            showToast('Registrasi berhasil! Silakan login.');
            setTimeout(() => { window.location.href = `${BASE_URL}/login.php`; }, 1200);
        } else {
            showFormError(result.message);
            btn.disabled = false;
        }
    } catch (err) {
        showFormError('Terjadi kesalahan koneksi');
        btn.disabled = false;
    }
}
