

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

  
  const regSchool = document.querySelector('#regSchool');
  const regNis = document.querySelector('#regNis');
  const regClass = document.querySelector('#regClass');

  
  const regUsername = document.querySelector('#regUsername');
  const regEmail = document.querySelector('#regEmail');
  const regPassword = document.querySelector('#regPassword');
  const regConfirmPassword = document.querySelector('#regConfirmPassword');
  const toggleRegPasswordBtn = document.querySelector('#toggleRegPasswordBtn');
  const toggleRegConfirmBtn = document.querySelector('#toggleRegConfirmBtn');
  const regEyeIcon1 = document.querySelector('#regEyeIcon1');
  const regEyeIcon2 = document.querySelector('#regEyeIcon2');
  const showRegPasswordCheck = document.querySelector('#showRegPasswordCheck');

  
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

  
  if (regSchool && regClass) {
    regSchool.addEventListener('change', updateClassDropdown);
  }

  
  if (btnNextStep) {
    btnNextStep.addEventListener('click', handleGoToStep2);
  }

  
  if (btnPrevStep) {
    btnPrevStep.addEventListener('click', handleGoBackToStep1);
  }

  
  initPasswordToggles();

  
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegisterSubmit);
  }

  
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

    
    step1Container.style.display = 'none';
    step2Container.style.display = 'block';

    
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

    
    if (passVal && confirmVal && passVal !== confirmVal) {
      showAlert('error', 'Konfirmasi kata sandi tidak cocok. Harap periksa kembali.');
      if (regConfirmPassword) regConfirmPassword.focus();
      return;
    }

    
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

    
    if (btnRegisterSubmit) {
      btnRegisterSubmit.disabled = true;
      btnRegisterSubmit.innerHTML = `
        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
        <span>Menyimpan & Mengalihkan...</span>
      `;
    }

    showAlert('success', `Pendaftaran berhasil! Selamat datang, ${usernameVal}. Mengalihkan ke Dashboard...`);

    
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
