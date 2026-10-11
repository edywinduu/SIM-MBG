/**
 * ==========================================================================
 * LOGIN JS - SIM-MBG
 * Handles login formality flow, password visibility toggles,
 * remember me persistence, and redirection to Student Dashboard.
 * Vanilla JS adhering to AGENTS.md guidelines (no libraries)
 * ==========================================================================
 */

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

  // 1. Inisialisasi Fitur Remember Me
  initRememberMe();

  // 2. Inisialisasi Tampilan Display Password (Sinkronisasi Tombol Mata & Checkbox)
  initPasswordVisibility();

  // 3. Inisialisasi Pengiriman Form Login Formalitas
  if (loginForm) {
    loginForm.addEventListener('submit', handleLoginSubmit);
  }

  // 4. Inisialisasi Bantuan Lupa Kata Sandi
  if (forgotPasswordBtn) {
    forgotPasswordBtn.addEventListener('click', (e) => {
      e.preventDefault();
      showAlert('info', 'Mode formalitas: Siapa saja dapat langsung masuk tanpa perlu mereset kata sandi.');
    });
  }

  /**
   * Menangani simpanan Remember Me dari LocalStorage
   */
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

  /**
   * Mengatur interaksi Display Password dua arah (Eye button & Checkbox)
   */
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

    // Toggle dari tombol icon mata di dalam input
    if (togglePasswordBtn) {
      togglePasswordBtn.addEventListener('click', () => {
        const isCurrentText = passwordInput.type === 'text';
        setPasswordVisibility(!isCurrentText);
      });
    }

    // Toggle dari checkbox "Tampilkan sandi"
    if (showPasswordCheck) {
      showPasswordCheck.addEventListener('change', () => {
        setPasswordVisibility(showPasswordCheck.checked);
      });
    }
  }

  /**
   * Menangani submit form login sebagai formalitas ke Dashboard Siswa
   */
  function handleLoginSubmit(e) {
    e.preventDefault();

    // Ambil data nama pengguna atau gunakan default ramah siswa
    const rawUsername = usernameInput ? usernameInput.value.trim() : '';
    const activeUsername = rawUsername || 'Alya MBG';

    // Kelola penyimpanan Remember Me
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

    // Simpan sesi autentikasi mock untuk simulasi Dashboard
    const mockSession = {
      username: activeUsername,
      role: 'siswa',
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

    // Tampilkan status loading pada tombol
    if (loginSubmitBtn) {
      loginSubmitBtn.disabled = true;
      loginSubmitBtn.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Mengalihkan...</span>
      `;
    }

    // Tampilkan notifikasi feedback sukses
    showAlert('success', `Login berhasil! Mengalihkan ke Dashboard Siswa...`);

    // Redirect ke Dashboard Siswa
    setTimeout(() => {
      window.location.href = '../siswa/dashboard.html';
    }, 450);
  }

  /**
   * Menampilkan pesan alert
   */
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
