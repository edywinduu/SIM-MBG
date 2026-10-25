/**
 * SIM-MBG — Login Module (login.js)
 * Menghubungkan antarmuka login dengan backend/api/login.php
 * Serta mendukung fallback simulasi offline/frontend testing.
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

  const STORAGE_KEY_AUTH = 'sim_mbg_auth';
  const STORAGE_KEY_REMEMBER = 'sim_mbg_remembered_user';

  // 1. Memuat username yang tersimpan jika fitur "Ingat Saya" aktif sebelumnya
  loadRememberedUser();

  // 2. Inisialisasi event toggle visibilitas kata sandi
  initPasswordVisibility();

  // 3. Inisialisasi event lupa kata sandi
  if (forgotPasswordBtn) {
    forgotPasswordBtn.addEventListener('click', handleForgotPassword);
  }

  // 4. Inisialisasi submit form login
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
   * Memuat username dari localStorage jika pernah disimpan
   */
  function loadRememberedUser() {
    try {
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
   * Menangani pengiriman form login ke backend/api/login.php
   */
  async function handleLoginSubmit(e) {
    e.preventDefault();
    hideAlert();

    const username = usernameInput ? usernameInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';

    // Validasi form frontend
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

    // Set status tombol loading
    setLoadingState(true);

    // Ambil endpoint dari atribut action form atau fallback relative path ke backend/api/login.php
    let endpoint = loginForm.getAttribute('action') || '../../../backend/api/login.php';
    // Pastikan request meminta format JSON
    if (!endpoint.includes('format=json')) {
      endpoint += (endpoint.includes('?') ? '&' : '?') + 'format=json';
    }

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          username: username,
          password: password
        })
      });

      // Periksa tipe respons
      const contentType = response.headers.get('content-type') || '';
      let data = null;

      if (contentType.includes('application/json')) {
        data = await response.json();
      } else {
        const textResponse = await response.text();
        try {
          data = JSON.parse(textResponse);
        } catch (_) {
          data = { message: textResponse };
        }
      }

      if (response.ok && data && data.status === 'success') {
        handleLoginSuccess(data, username);
      } else {
        // Tampilkan pesan error dari backend
        const errorMessage = (data && (data.message || data.error)) 
          ? (data.message || data.error) 
          : 'Username atau password salah.';
        showAlert('error', errorMessage);
        setLoadingState(false);
      }
    } catch (networkError) {
      console.warn('Gagal terhubung ke backend/api/login.php:', networkError);
      // Fallback simulasi jika backend PHP belum berjalan (misal file:// atau dev offline)
      handleFallbackOfflineLogin(username, password);
    }
  }

  /**
   * Menangani proses berhasil login dan redirect sesuai role backend
   */
  function handleLoginSuccess(responseData, enteredUsername) {
    // Normalisasi role pengguna dari backend
    const rawRole = (responseData.role || (responseData.data && responseData.data.role) || 'siswa').toLowerCase();
    const role = rawRole === 'student' ? 'siswa' : rawRole;

    const userData = responseData.data || {
      username: enteredUsername,
      role: role
    };

    // Simpan informasi sesi
    const sessionPayload = {
      id: userData.id || null,
      student_id: userData.student_id || null,
      school: userData.school || '',
      nis: userData.nis || userData.nisn || '',
      class: userData.class || '',
      email: userData.email || '',
      username: userData.username || enteredUsername,
      full_name: userData.full_name || enteredUsername,
      role: role,
      isLoggedIn: true,
      loginAt: new Date().toISOString()
    };

    try {
      sessionStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(sessionPayload));

      // Kelola preferensi "Ingat Saya"
      if (rememberMeCheckbox && rememberMeCheckbox.checked) {
        localStorage.setItem(STORAGE_KEY_REMEMBER, enteredUsername);
      } else {
        localStorage.removeItem(STORAGE_KEY_REMEMBER);
      }
    } catch (err) {
      console.warn('Gagal menyimpan sesi login:', err);
    }

    const displayName = userData.full_name || userData.username || enteredUsername;
    showAlert('success', `Login berhasil! Selamat datang, ${displayName}. Mengalihkan ke dashboard...`);

    // Tentukan URL tujuan berdasarkan role
    const redirectUrl = getDestinationByRole(role);

    setTimeout(() => {
      window.location.href = redirectUrl;
    }, 450);
  }

  /**
   * Menentukan URL dashboard berdasarkan role
   */
  function getDestinationByRole(role) {
    switch (role) {
      case 'admin':
        return '../admin/dashboard.html';
      case 'sppg':
        return '../sppg/dashboard.html';
      case 'siswa':
      default:
        return '../siswa/dashboard.html';
    }
  }

  /**
   * Fallback simulasi jika backend belum terhubung/offline
   */
  function handleFallbackOfflineLogin(username, password) {
    const lowerUser = username.toLowerCase();
    let detectedRole = 'siswa';

    if (lowerUser.includes('admin')) {
      detectedRole = 'admin';
    } else if (lowerUser.includes('sppg')) {
      detectedRole = 'sppg';
    }

    const mockData = {
      status: 'success',
      message: 'Login berhasil (Mode Simulasi Frontend).',
      role: detectedRole,
      data: {
        id: 99,
        username: username,
        full_name: username,
        role: detectedRole
      }
    };

    handleLoginSuccess(mockData, username);
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
