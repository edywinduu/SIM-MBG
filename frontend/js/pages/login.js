

document.addEventListener('DOMContentLoaded', () => {
  initLoginModule();
});

function initLoginModule() {
  const loginForm = document.querySelector('#loginForm');
  const usernameInput = document.querySelector('#username');
  const passwordInput = document.querySelector('#password');
  const togglePasswordBtn = document.querySelector('#togglePasswordBtn');
  const eyeIcon = document.querySelector('#eyeIcon');
  const showPasswordCheck = document.querySelector('#showPasswordCheck');
  const rememberMeCheck = document.querySelector('#rememberMe');
  const loginSubmitBtn = document.querySelector('#loginSubmitBtn');
  const authAlert = document.querySelector('#authAlert');
  const alertText = document.querySelector('#alertText');
  const alertIcon = document.querySelector('#alertIcon');
  const forgotPasswordBtn = document.querySelector('#forgotPasswordBtn');

  
  initRememberMe();

  
  initPasswordVisibility();

  
  if (loginForm) {
    loginForm.addEventListener('submit', handleLoginSubmit);
  }

  
  if (forgotPasswordBtn) {
    forgotPasswordBtn.addEventListener('click', (e) => {
      e.preventDefault();
      showAlert('info', 'Mode formalitas: Siapa saja dapat langsung masuk tanpa perlu mereset kata sandi.');
    });
  }

  
  function initRememberMe() {
    if (!usernameInput || !rememberMeCheck) return;

    try {
      const savedUser = localStorage.getItem('sim_mbg_remembered_user');
      if (savedUser) {
        usernameInput.value = savedUser;
        rememberMeCheck.checked = true;
      }
    } catch (err) {
      console.warn('LocalStorage tidak tersedia:', err);
    }
  }

  
  function initPasswordVisibility() {
    if (!passwordInput) return;

    function setPasswordVisibility(visible) {
      passwordInput.type = visible ? 'text' : 'password';

      if (eyeIcon) {
        eyeIcon.textContent = visible ? 'visibility_off' : 'visibility';
      }

      if (togglePasswordBtn) {
        togglePasswordBtn.setAttribute(
          'aria-label',
          visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
        );
      }

      if (showPasswordCheck && showPasswordCheck.checked !== visible) {
        showPasswordCheck.checked = visible;
      }
    }

    
    if (togglePasswordBtn) {
      togglePasswordBtn.addEventListener('click', () => {
        const isCurrentText = passwordInput.type === 'text';
        setPasswordVisibility(!isCurrentText);
      });
    }

    
    if (showPasswordCheck) {
      showPasswordCheck.addEventListener('change', () => {
        setPasswordVisibility(showPasswordCheck.checked);
      });
    }
  }

  
  function handleLoginSubmit(e) {
    e.preventDefault();

    
    const rawUsername = usernameInput ? usernameInput.value.trim() : '';
    const activeUsername = rawUsername || 'Alya MBG';

    
    if (rememberMeCheck && rememberMeCheck.checked) {
      try {
        localStorage.setItem('sim_mbg_remembered_user', activeUsername);
      } catch (err) {
        console.warn('Gagal menyimpan Remember Me:', err);
      }
    } else {
      try {
        localStorage.removeItem('sim_mbg_remembered_user');
      } catch (err) {
        console.warn('Gagal menghapus Remember Me:', err);
      }
    }

    
    const isAdmin = activeUsername.toLowerCase().includes('admin') || activeUsername.toLowerCase() === 'atmind';
    const targetRole = isAdmin ? 'admin' : 'siswa';
    const targetPath = isAdmin ? '../admin/dashboard.html' : '../siswa/dashboard.html';
    const targetLabel = isAdmin ? 'Dashboard Administrator' : 'Dashboard Siswa';

    
    const mockSession = {
      username: activeUsername,
      role: targetRole,
      nis: rawUsername.match(/^\d+$/) ? rawUsername : '12345678',
      school: 'SMA Negeri 1 Denpasar',
      isLoggedIn: true,
      loginTimestamp: new Date().toISOString()
    };

    try {
      sessionStorage.setItem('sim_mbg_auth', JSON.stringify(mockSession));
    } catch (err) {
      console.warn('SessionStorage tidak tersedia:', err);
    }

    
    if (loginSubmitBtn) {
      loginSubmitBtn.disabled = true;
      loginSubmitBtn.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Mengalihkan...</span>
      `;
    }

    
    showAlert('success', `Login berhasil! Mengalihkan ke ${targetLabel}...`);

    
    setTimeout(() => {
      window.location.href = targetPath;
    }, 450);
  }

  
  function showAlert(type, message) {
    if (!authAlert || !alertText) return;

    authAlert.className = `auth-alert ${type}`;
    alertText.textContent = message;

    if (alertIcon) {
      alertIcon.textContent = type === 'success' ? 'check_circle' : 'info';
    }

    authAlert.style.display = 'flex';
  }
}
