// Auth Modal
function showAuth(type) {
  document.getElementById('authOverlay').classList.add('active');
  if (type === 'login') {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
  } else {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
  }
}

function hideAuth(e) {
  if (!e || e.target === document.getElementById('authOverlay')) {
    document.getElementById('authOverlay').classList.remove('active');
  }
}

async function doLogin() {
  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;
  const errEl = document.getElementById('loginError');

  errEl.style.display = 'none';

  if (!email || !password) {
    showError(errEl, 'Please fill in all fields');
    return;
  }

  const btn = document.querySelector('#loginForm .btn-submit');
  btn.disabled = true; btn.textContent = 'Signing in...';

  try {
    const fd = new FormData();
    fd.append('action', 'login');
    fd.append('email', email);
    fd.append('password', password);

    const res = await fetch('php/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.error) {
      showError(errEl, data.error);
    } else {
      window.location.href = 'dashboard.php';
    }
  } catch (err) {
    showError(errEl, 'Connection error. Please try again.');
  } finally {
    btn.disabled = false; btn.textContent = 'Sign In';
  }
}

async function doRegister() {
  const name = document.getElementById('regName').value;
  const email = document.getElementById('regEmail').value;
  const password = document.getElementById('regPassword').value;
  const errEl = document.getElementById('registerError');

  errEl.style.display = 'none';

  if (!name || !email || !password) {
    showError(errEl, 'Please fill in all fields');
    return;
  }

  const btn = document.querySelector('#registerForm .btn-submit');
  btn.disabled = true; btn.textContent = 'Creating account...';

  try {
    const fd = new FormData();
    fd.append('action', 'register');
    fd.append('name', name);
    fd.append('email', email);
    fd.append('password', password);

    const res = await fetch('php/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.error) {
      showError(errEl, data.error);
    } else {
      window.location.href = 'dashboard.php';
    }
  } catch (err) {
    showError(errEl, 'Connection error. Please try again.');
  } finally {
    btn.disabled = false; btn.textContent = 'Create Free Account';
  }
}

function showError(el, msg) {
  el.textContent = msg;
  el.style.display = 'block';
}

// Enter key support
document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    if (document.getElementById('loginForm').style.display !== 'none') {
      doLogin();
    } else if (document.getElementById('registerForm').style.display !== 'none') {
      doRegister();
    }
  }
});
