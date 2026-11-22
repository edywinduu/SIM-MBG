/**
 * SIM-MBG — Login Module (login.js)
 * Terintegrasi penuh dengan backend Laravel menggunakan Fetch API.
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
  const rememberMeCheckbox = document.querySelector('#rememberMe');
  const showPasswordCheck = document.querySelector('#showPasswordCheck');
  const forgotPasswordBtn = document.querySelector('#forgotPasswordBtn');
  const loginSubmitBtn = document.querySelector('#loginSubmitBtn');
  const authAlert = document.querySelector('#authAlert');
  const alertIcon = document.querySelector('#alertIcon');
  const alertText = document.querySelector('#alertText');

  const STORAGE_KEY_REMEMBER = 'sim_mbg_remembered_user';

  // 1. Muat username yang tersimpan jika fitur "Ingat Saya" aktif sebelumnya
  loadRememberedUser();

  // Bersihkan storage lokal seketika jika pengguna mencopot centang "Ingat Saya"
  if (rememberMeCheckbox) {
    rememberMeCheckbox.addEventListener('change', () => {
      if (!rememberMeCheckbox.checked) {
        localStorage.removeItem(STORAGE_KEY_REMEMBER);
      }
    });
  }

  // 2. Inisialisasi event toggle visibilitas kata sandi
  initPasswordVisibility();

  // 3. Inisialisasi event lupa kata sandi
  if (forgotPasswordBtn) {
    forgotPasswordBtn.addEventListener('click', handleForgotPassword);
  }

  // 4. Inisialisasi submit form login via Fetch API
  if (loginForm) {
    loginForm.addEventListener('submit', handleLoginSubmit);
  }

  /**
   * Menampilkan atau menyembunyikan input kata sandi
   */
  function initPasswordVisibility() {
    function setPasswordVisible(visible) {
      if (!passwordInput) return;
      passwordInput.type = visible ? 'text' : 'password';
      if (eyeIcon) {
        eyeIcon.textContent = visible ? 'visibility_off' : 'visibility';
      }
      if (showPasswordCheck) {
        showPasswordCheck.checked = visible;
      }
    }

    if (togglePasswordBtn && passwordInput) {
      togglePasswordBtn.addEventListener('click', () => {
        const isCurrentlyText = passwordInput.type === 'text';
        setPasswordVisible(!isCurrentlyText);
      });
    }

    if (showPasswordCheck && passwordInput) {
      showPasswordCheck.addEventListener('change', () => {
        setPasswordVisible(showPasswordCheck.checked);
      });
    }
  }

  /**
   * Memuat username dari server cookie / localStorage jika pernah disimpan
   */
  function loadRememberedUser() {
    try {
      if (usernameInput && usernameInput.value.trim()) {
        if (rememberMeCheckbox) {
          rememberMeCheckbox.checked = true;
        }
        localStorage.setItem(STORAGE_KEY_REMEMBER, usernameInput.value.trim());
        return;
      }

      const savedUser = localStorage.getItem(STORAGE_KEY_REMEMBER);
      if (savedUser && usernameInput) {
        usernameInput.value = savedUser;
        if (rememberMeCheckbox) {
          rememberMeCheckbox.checked = true;
        }
      }
    } catch (err) {
      console.warn('Gagal membaca rememberMe dari localStorage:', err);
    }
  }

  /**
   * Menangani klik Lupa Kata Sandi
   */
  function handleForgotPassword(e) {
    e.preventDefault();
    showAlert(
      'error',
      'Untuk mereset kata sandi, silakan hubungi Administrator SIM-MBG atau pihak SPPG di sekolah Anda.'
    );
  }

  /**
   * Menangani pengiriman form login ke Laravel via Fetch API (AJAX)
   */
  async function handleLoginSubmit(e) {
    e.preventDefault();
    hideAlert();

    const username = usernameInput ? usernameInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';
    const remember = rememberMeCheckbox ? rememberMeCheckbox.checked : false;

    // Validasi awal frontend
    if (!username) {
      showAlert('error', 'Nama Pengguna, NIS, atau Email wajib diisi.');
      if (usernameInput) usernameInput.focus();
      return;
    }

    if (!password) {
      showAlert('error', 'Kata sandi wajib diisi.');
      if (passwordInput) passwordInput.focus();
      return;
    }

    // Aktifkan status loading pada tombol submit
    setLoadingState(true);

    // Ambil URL target langsung dari atribut action pada tag form
    const endpoint = loginForm.getAttribute('action') || loginForm.action || '/login';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
        },
        body: JSON.stringify({
          username: username,
          password: password,
          remember: remember,
          rememberMe: remember
        })
      });

      const data = await response.json();

      if (response.ok && data.status === 'success') {
        // Kelola penyimpanan teks username untuk fitur "Ingat Saya"
        if (remember) {
          localStorage.setItem(STORAGE_KEY_REMEMBER, username);
        } else {
          localStorage.removeItem(STORAGE_KEY_REMEMBER);
        }

        showAlert('success', data.message || 'Login berhasil! Mengalihkan...');

        // Redirect sesuai respons dari controller Laravel
        const redirectUrl = data.redirect_url || data.redirect || '/dashboard';
        setTimeout(() => {
          window.location.href = redirectUrl;
        }, 500);
      } else {
        // Ekstraksi pesan error dari respons Laravel (validasi 422 atau kredensial salah 401/403)
        let errorMessage = data.message || 'Username atau password salah.';
        if (data.errors) {
          const firstErrorKey = Object.keys(data.errors)[0];
          if (firstErrorKey && data.errors[firstErrorKey].length > 0) {
            errorMessage = data.errors[firstErrorKey][0];
          }
        }
        showAlert('error', errorMessage);
        setLoadingState(false);
      }
    } catch (networkError) {
      console.error('Fetch error:', networkError);
      showAlert('error', 'Gagal terhubung ke server. Periksa koneksi Anda.');
      setLoadingState(false);
    }
  }

  /**
   * Mengatur indikator loading pada tombol submit
   */
  function setLoadingState(isLoading) {
    if (!loginSubmitBtn) return;

    if (isLoading) {
      loginSubmitBtn.disabled = true;
      loginSubmitBtn.dataset.originalHtml = loginSubmitBtn.innerHTML;
      loginSubmitBtn.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Memverifikasi...</span>
      `;
    } else {
      loginSubmitBtn.disabled = false;
      if (loginSubmitBtn.dataset.originalHtml) {
        loginSubmitBtn.innerHTML = loginSubmitBtn.dataset.originalHtml;
      } else {
        loginSubmitBtn.innerHTML = `
          <span class="btn-text">Masuk</span>
          <span class="material-symbols-outlined btn-icon" aria-hidden="true">arrow_forward</span>
        `;
      }
    }
  }

  /**
   * Menampilkan pesan notifikasi alert
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

  /**
   * Menyembunyikan pesan notifikasi alert
   */
  function hideAlert() {
    if (authAlert) {
      authAlert.style.display = 'none';
    }
  }
}
