/**
 * SIM-MBG — Register Module (register.js)
 * Menangani alur pendaftaran berjenjang (multi-step) siswa
 * dan mengirimkan data akun ke backend/api/register.php
 */

document.addEventListener('DOMContentLoaded', () => {
  initRegisterModule();
});

function initRegisterModule() {
  const registerForm = document.querySelector('#multiStepRegisterForm');
  const step1Container = document.querySelector('#step1Container');
  const step2Container = document.querySelector('#step2Container');
  const btnNextStep = document.querySelector('#btnNextStep');
  const btnPrevStep = document.querySelector('#btnPrevStep');
  const btnRegisterSubmit = document.querySelector('#btnRegisterSubmit');
  const stepPill1 = document.querySelector('#stepPill1');
  const stepPill2 = document.querySelector('#stepPill2');
  const registerTitle = document.querySelector('#registerTitle');
  const registerSubtitle = document.querySelector('#registerSubtitle');
  const registerAlert = document.querySelector('#registerAlert');
  const regAlertText = document.querySelector('#regAlertText');
  const regAlertIcon = document.querySelector('#regAlertIcon');

  // Input Data Sekolah (Langkah 1)
  const regSchool = document.querySelector('#regSchool');
  const regNis = document.querySelector('#regNis');
  const regClass = document.querySelector('#regClass');

  // Input Data Akun (Langkah 2)
  const regUsername = document.querySelector('#regUsername');
  const regEmail = document.querySelector('#regEmail');
  const regPassword = document.querySelector('#regPassword');
  const regConfirmPassword = document.querySelector('#regConfirmPassword');
  const toggleRegPasswordBtn = document.querySelector('#toggleRegPasswordBtn');
  const toggleRegConfirmBtn = document.querySelector('#toggleRegConfirmBtn');
  const regEyeIcon1 = document.querySelector('#regEyeIcon1');
  const regEyeIcon2 = document.querySelector('#regEyeIcon2');
  const showRegPasswordCheck = document.querySelector('#showRegPasswordCheck');

  const STORAGE_KEY_AUTH = 'sim_mbg_auth';
  const STORAGE_KEY_REMEMBER = 'sim_mbg_remembered_user';

  // Opsi kelas dinamis berdasarkan jenjang sekolah
  const classOptionsByLevel = {
    'SD': [
      'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'
    ],
    'SMP': [
      'Kelas 7 (VII A)', 'Kelas 7 (VII B)', 'Kelas 8 (VIII A)', 'Kelas 8 (VIII B)', 'Kelas 9 (IX A)', 'Kelas 9 (IX B)'
    ],
    'SMA': [
      'Kelas 10 (X MIPA 1)', 'Kelas 10 (X IPS 1)', 'Kelas 11 (XI IPA 1)', 'Kelas 11 (XI IPS 1)', 'Kelas 12 (XII IPA 1)', 'Kelas 12 (XII IPS 1)'
    ]
  };

  // Event listener perubahan pilihan sekolah
  if (regSchool && regClass) {
    regSchool.addEventListener('change', updateClassDropdown);
  }

  // Navigasi langkah stepper
  if (btnNextStep) {
    btnNextStep.addEventListener('click', handleGoToStep2);
  }

  if (btnPrevStep) {
    btnPrevStep.addEventListener('click', handleGoBackToStep1);
  }

  // Inisialisasi toggle visibilitas kata sandi
  initPasswordToggles();

  // Inisialisasi pengiriman formulir pendaftaran
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegisterSubmit);
  }

  /**
   * Menyesuaikan opsi dropdown kelas sesuai jenjang sekolah yang dipilih
   */
  function updateClassDropdown() {
    const selectedOption = regSchool.options[regSchool.selectedIndex];
    const level = selectedOption ? selectedOption.getAttribute('data-level') : '';

    if (!level || !classOptionsByLevel[level]) return;

    const classes = classOptionsByLevel[level];
    regClass.innerHTML = `<option value="" disabled selected>-- Pilih Kelas untuk ${level} --</option>`;

    classes.forEach((cls) => {
      const opt = document.createElement('option');
      opt.value = cls;
      opt.textContent = cls;
      regClass.appendChild(opt);
    });

    if (classes.length > 0) {
      regClass.selectedIndex = level === 'SMA' ? 3 : 1;
    }
  }

  /**
   * Validasi Langkah 1 dan transisi ke Langkah 2
   */
  function handleGoToStep2() {
    hideAlert();

    const schoolVal = regSchool ? regSchool.value.trim() : '';
    const nisVal = regNis ? regNis.value.trim() : '';
    const classVal = regClass ? regClass.value.trim() : '';

    if (!schoolVal) {
      showAlert('error', 'Silakan pilih Asal Sekolah (SD, SMP, atau SMA) terlebih dahulu.');
      regSchool.focus();
      return;
    }

    if (!nisVal) {
      showAlert('error', 'Silakan isi Nomor Induk Siswa (NIS).');
      regNis.focus();
      return;
    }

    if (!classVal) {
      showAlert('error', 'Silakan pilih Kelas dari dropdown.');
      regClass.focus();
      return;
    }

    // Pindah tampilan kontainer
    step1Container.style.display = 'none';
    step2Container.style.display = 'block';

    // Perbarui stepper pill
    stepPill1.classList.remove('active');
    stepPill1.classList.add('done');
    stepPill2.classList.add('active');

    if (registerSubtitle) {
      registerSubtitle.textContent = 'Langkah 2 dari 2: Lengkapi Akun Pengguna & Kata Sandi';
    }

    if (regUsername) {
      regUsername.focus();
    }
  }

  /**
   * Kembali ke Langkah 1 dari Langkah 2
   */
  function handleGoBackToStep1() {
    hideAlert();

    step2Container.style.display = 'none';
    step1Container.style.display = 'block';

    stepPill1.classList.add('active');
    stepPill1.classList.remove('done');
    stepPill2.classList.remove('active');

    if (registerSubtitle) {
      registerSubtitle.textContent = 'Langkah 1 dari 2: Verifikasi Identitas Sekolah Siswa';
    }
  }

  /**
   * Menangani toggle visibilitas kata sandi dan konfirmasi
   */
  function initPasswordToggles() {
    function setVisibility(inputEl, iconEl, isVisible) {
      if (!inputEl) return;
      inputEl.type = isVisible ? 'text' : 'password';
      if (iconEl) {
        iconEl.textContent = isVisible ? 'visibility_off' : 'visibility';
      }
    }

    if (toggleRegPasswordBtn && regPassword) {
      toggleRegPasswordBtn.addEventListener('click', () => {
        const isCurrentlyText = regPassword.type === 'text';
        setVisibility(regPassword, regEyeIcon1, !isCurrentlyText);
      });
    }

    if (toggleRegConfirmBtn && regConfirmPassword) {
      toggleRegConfirmBtn.addEventListener('click', () => {
        const isCurrentlyText = regConfirmPassword.type === 'text';
        setVisibility(regConfirmPassword, regEyeIcon2, !isCurrentlyText);
      });
    }

    if (showRegPasswordCheck) {
      showRegPasswordCheck.addEventListener('change', () => {
        const checked = showRegPasswordCheck.checked;
        setVisibility(regPassword, regEyeIcon1, checked);
        setVisibility(regConfirmPassword, regEyeIcon2, checked);
      });
    }
  }

  /**
   * Mengirimkan data registrasi ke backend/api/register.php via method POST
   */
  async function handleRegisterSubmit(e) {
    e.preventDefault();
    hideAlert();

    const schoolVal = regSchool ? regSchool.value.trim() : '';
    const nisVal = regNis ? regNis.value.trim() : '';
    const classVal = regClass ? regClass.value.trim() : '';
    const usernameVal = regUsername ? regUsername.value.trim() : '';
    const emailVal = regEmail ? regEmail.value.trim() : '';
    const passVal = regPassword ? regPassword.value : '';
    const confirmVal = regConfirmPassword ? regConfirmPassword.value : '';

    // Validasi field Langkah 1
    if (!schoolVal) {
      showAlert('error', 'Silakan pilih Asal Sekolah terlebih dahulu.');
      handleGoBackToStep1();
      if (regSchool) regSchool.focus();
      return;
    }

    if (!nisVal) {
      showAlert('error', 'Nomor Induk Siswa (NIS) wajib diisi.');
      handleGoBackToStep1();
      if (regNis) regNis.focus();
      return;
    }

    if (!classVal) {
      showAlert('error', 'Kelas wajib dipilih.');
      handleGoBackToStep1();
      if (regClass) regClass.focus();
      return;
    }

    // Validasi field Langkah 2
    if (!usernameVal) {
      showAlert('error', 'Nama Pengguna (username) wajib diisi.');
      if (regUsername) regUsername.focus();
      return;
    }

    if (!emailVal) {
      showAlert('error', 'Alamat email wajib diisi.');
      if (regEmail) regEmail.focus();
      return;
    }

    if (!passVal) {
      showAlert('error', 'Kata sandi wajib diisi.');
      if (regPassword) regPassword.focus();
      return;
    }

    if (passVal.length < 8) {
      showAlert('error', 'Kata sandi minimal 8 karakter.');
      if (regPassword) regPassword.focus();
      return;
    }

    if (passVal !== confirmVal) {
      showAlert('error', 'Konfirmasi kata sandi tidak cocok. Harap periksa kembali.');
      if (regConfirmPassword) regConfirmPassword.focus();
      return;
    }

    // Set status tombol loading
    setLoadingState(true);

    // Ambil endpoint dari form action atau fallback ke backend/api/register.php
    let endpoint = registerForm.getAttribute('action') || '../../../backend/api/register.php';
    if (!endpoint.includes('format=json')) {
      endpoint += (endpoint.includes('?') ? '&' : '?') + 'format=json';
    }

    const payload = {
      school: schoolVal,
      nis: nisVal,
      class: classVal,
      username: usernameVal,
      email: emailVal,
      password: passVal,
      confirmPassword: confirmVal
    };

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

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
        handleRegisterSuccess(data, usernameVal);
      } else {
        const errorMessage = (data && (data.message || data.error))
          ? (data.message || data.error)
          : 'Pendaftaran gagal. Silakan periksa kembali data Anda.';
        showAlert('error', errorMessage);
        setLoadingState(false);
      }
    } catch (networkError) {
      console.warn('Gagal terhubung ke backend/api/register.php:', networkError);
      // Fallback simulasi jika backend belum aktif
      handleFallbackOfflineRegister(payload);
    }
  }

  /**
   * Menangani respon berhasil registrasi dan redirect ke dashboard
   */
  function handleRegisterSuccess(responseData, usernameVal) {
    const userData = responseData.data || {
      username: usernameVal,
      role: 'siswa'
    };

    const sessionPayload = {
      id: userData.id || null,
      student_id: userData.student_id || null,
      username: userData.username || usernameVal,
      full_name: userData.full_name || usernameVal,
      school: userData.school || (regSchool ? regSchool.value : ''),
      nis: userData.nis || (regNis ? regNis.value : ''),
      class: userData.class || (regClass ? regClass.value : ''),
      email: userData.email || (regEmail ? regEmail.value : ''),
      role: 'siswa',
      isLoggedIn: true,
      registeredAt: new Date().toISOString()
    };

    try {
      sessionStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(sessionPayload));
      localStorage.setItem(STORAGE_KEY_REMEMBER, usernameVal);
    } catch (err) {
      console.warn('Gagal menyimpan sesi registrasi:', err);
    }

    const displayName = userData.full_name || userData.username || usernameVal;
    showAlert('success', `Pendaftaran berhasil! Selamat datang, ${displayName}. Mengalihkan ke Dashboard...`);

    setTimeout(() => {
      window.location.href = '../siswa/dashboard.html';
    }, 500);
  }

  /**
   * Fallback simulasi jika backend belum terhubung/offline
   */
  function handleFallbackOfflineRegister(payload) {
    const mockData = {
      status: 'success',
      message: 'Pendaftaran berhasil (Mode Simulasi Frontend).',
      role: 'siswa',
      data: {
        id: 99,
        student_id: 99,
        username: payload.username,
        full_name: payload.username,
        school: payload.school,
        nis: payload.nis,
        class: payload.class,
        email: payload.email,
        role: 'siswa'
      }
    };
    handleRegisterSuccess(mockData, payload.username);
  }

  /**
   * Mengatur indikator loading pada tombol submit registrasi
   */
  function setLoadingState(isLoading) {
    if (!btnRegisterSubmit) return;

    if (isLoading) {
      btnRegisterSubmit.disabled = true;
      btnRegisterSubmit.dataset.originalHtml = btnRegisterSubmit.innerHTML;
      btnRegisterSubmit.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Menyimpan & Mendaftarkan...</span>
      `;
    } else {
      btnRegisterSubmit.disabled = false;
      if (btnRegisterSubmit.dataset.originalHtml) {
        btnRegisterSubmit.innerHTML = btnRegisterSubmit.dataset.originalHtml;
      } else {
        btnRegisterSubmit.innerHTML = `
          <span class="btn-text">Daftar & Masuk</span>
          <span class="material-symbols-outlined">check_circle</span>
        `;
      }
    }
  }

  /**
   * Menampilkan pesan notifikasi alert
   */
  function showAlert(type, message) {
    if (!registerAlert || !regAlertText) return;

    registerAlert.className = `auth-alert ${type}`;
    regAlertText.textContent = message;

    if (regAlertIcon) {
      regAlertIcon.textContent = type === 'success' ? 'check_circle' : 'info';
    }

    registerAlert.style.display = 'flex';
  }

  /**
   * Menyembunyikan notifikasi alert
   */
  function hideAlert() {
    if (registerAlert) {
      registerAlert.style.display = 'none';
    }
  }
}
