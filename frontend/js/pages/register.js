/**
 * ==========================================================================
 * REGISTER JS - SIM-MBG
 * Handles 2-step registration workflow:
 * Step 1: School (SD/SMP/SMA), NIS, Class dropdowns
 * Step 2: Username, Email, Password, Confirm Password
 * Final: Direct redirection to Student Dashboard with active login session.
 * Vanilla JS adhering to AGENTS.md guidelines (no libraries)
 * ==========================================================================
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

  // Input Step 1
  const regSchool = document.querySelector('#regSchool');
  const regNis = document.querySelector('#regNis');
  const regClass = document.querySelector('#regClass');

  // Input Step 2
  const regUsername = document.querySelector('#regUsername');
  const regEmail = document.querySelector('#regEmail');
  const regPassword = document.querySelector('#regPassword');
  const regConfirmPassword = document.querySelector('#regConfirmPassword');
  const toggleRegPasswordBtn = document.querySelector('#toggleRegPasswordBtn');
  const toggleRegConfirmBtn = document.querySelector('#toggleRegConfirmBtn');
  const regEyeIcon1 = document.querySelector('#regEyeIcon1');
  const regEyeIcon2 = document.querySelector('#regEyeIcon2');
  const showRegPasswordCheck = document.querySelector('#showRegPasswordCheck');

  // Data Pilihan Kelas Berdasarkan Jenjang Sekolah
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

  // 1. Inisialisasi Perubahan Dropdown Sekolah & Kelas
  if (regSchool && regClass) {
    regSchool.addEventListener('change', updateClassDropdown);
  }

  // 2. Inisialisasi Navigasi Langkah 1 ke Langkah 2
  if (btnNextStep) {
    btnNextStep.addEventListener('click', handleGoToStep2);
  }

  // 3. Inisialisasi Navigasi Kembali dari Langkah 2 ke Langkah 1
  if (btnPrevStep) {
    btnPrevStep.addEventListener('click', handleGoBackToStep1);
  }

  // 4. Inisialisasi Tampilan Display Password
  initPasswordToggles();

  // 5. Inisialisasi Submit Form Registrasi Selesai
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegisterSubmit);
  }

  /**
   * Mengupdate pilihan dropdown Kelas sesuai sekolah yang dipilih (SD / SMP / SMA)
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

    // Otomatis pilih opsi pertama yang relevan untuk kenyamanan
    if (classes.length > 0) {
      regClass.selectedIndex = level === 'SMA' ? 3 : 1; // Default XI IPA 1 untuk SMA
    }
  }

  /**
   * Validasi Tahap 1 dan beralih ke Tahap 2
   */
  function handleGoToStep2() {
    hideAlert();

    const schoolVal = regSchool ? regSchool.value.trim() : '';
    const nisVal = regNis ? regNis.value.trim() : '';
    const classVal = regClass ? regClass.value.trim() : '';

    // Validasi kelengkapan tahap 1
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

    // Beralih tampilan ke Tahap 2
    step1Container.style.display = 'none';
    step2Container.style.display = 'block';

    // Update Stepper Progress Pills
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
   * Kembali ke Tahap 1 dari Tahap 2
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
   * Mengatur fitur display password untuk kata sandi & konfirmasi
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
   * Submit registrasi dan direct ke Dashboard Siswa dengan status login aktif
   */
  function handleRegisterSubmit(e) {
    e.preventDefault();
    hideAlert();

    const schoolVal = regSchool ? regSchool.value.trim() : 'SMA Negeri 1 Denpasar';
    const nisVal = regNis ? regNis.value.trim() : '12345678';
    const classVal = regClass ? regClass.value.trim() : 'Kelas 11 (XI IPA 1)';
    const usernameVal = regUsername && regUsername.value.trim() ? regUsername.value.trim() : 'Alya MBG';
    const emailVal = regEmail && regEmail.value.trim() ? regEmail.value.trim() : 'alya@sekolah.id';
    const passVal = regPassword ? regPassword.value : '';
    const confirmVal = regConfirmPassword ? regConfirmPassword.value : '';

    // Cek kecocokan password jika diisi
    if (passVal && confirmVal && passVal !== confirmVal) {
      showAlert('error', 'Konfirmasi kata sandi tidak cocok. Harap periksa kembali.');
      if (regConfirmPassword) regConfirmPassword.focus();
      return;
    }

    // Buat data sesi siswa terlogin
    const mockAuthSession = {
      username: usernameVal,
      school: schoolVal,
      nis: nisVal,
      class: classVal,
      email: emailVal,
      role: 'siswa',
      isLoggedIn: true,
      registeredAt: new Date().toISOString()
    };

    try {
      sessionStorage.setItem('sim_mbg_auth', JSON.stringify(mockAuthSession));
      localStorage.setItem('sim_mbg_remembered_user', usernameVal);
    } catch (err) {
      console.warn('Gagal menyimpan sesi registrasi:', err);
    }

    // Tampilkan loading feedback pada tombol
    if (btnRegisterSubmit) {
      btnRegisterSubmit.disabled = true;
      btnRegisterSubmit.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Menyimpan & Mengalihkan...</span>
      `;
    }

    showAlert('success', `Pendaftaran berhasil! Selamat datang, ${usernameVal}. Mengalihkan ke Dashboard...`);

    // Redirect ke Dashboard Siswa sebagai siswa terlogin
    setTimeout(() => {
      window.location.href = '../siswa/dashboard.html';
    }, 450);
  }

  function showAlert(type, message) {
    if (!registerAlert || !regAlertText) return;

    registerAlert.className = `auth-alert ${type}`;
    regAlertText.textContent = message;

    if (regAlertIcon) {
      regAlertIcon.textContent = type === 'success' ? 'check_circle' : 'info';
    }

    registerAlert.style.display = 'flex';
  }

  function hideAlert() {
    if (registerAlert) {
      registerAlert.style.display = 'none';
    }
  }
}
