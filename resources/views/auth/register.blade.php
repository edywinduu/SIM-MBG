<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Siswa — SIM-MBG</title>
    <link rel="icon" href="{{ asset('assets/icons/logo.svg') }}">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
</head>
<body class="auth-body">
    
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="auth-header">
        <div class="container auth-nav-container">
            <a href="../../index.html" class="student-navbar-brand logo-nav" aria-label="SIM-MBG Beranda">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title logo-brand-text">SIM-MBG</span>
            </a>
            <a href="../../index.html" class="auth-back-link">
                <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                
                <div class="auth-card-header">
                    <div class="auth-logo-badge">
                        <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="56" height="52">
                    </div>
                    <h1 class="auth-title" id="registerTitle">Daftar Akun</h1>
                    <p class="auth-subtitle" id="registerSubtitle">Langkah 1 dari 2: Verifikasi Identitas Sekolah Siswa</p>
                </div>

                
                <div class="auth-stepper-pills" aria-label="Tahapan Registrasi">
                    <div class="stepper-pill active" id="stepPill1">
                        <span class="pill-number">1</span>
                        <span class="pill-text">Data Sekolah</span>
                    </div>
                    <div class="stepper-divider"></div>
                    <div class="stepper-pill" id="stepPill2">
                        <span class="pill-number">2</span>
                        <span class="pill-text">Data Akun</span>
                    </div>
                </div>

                
                <div class="auth-alert" id="registerAlert" role="alert" style="display: none;">
                    <span class="material-symbols-outlined auth-alert-icon" id="regAlertIcon">info</span>
                    <span class="auth-alert-text" id="regAlertText"></span>
                </div>

                
                <form action="../../../backend/api/register.php" id="multiStepRegisterForm" class="auth-form" novalidate method="post">
                    
                    
                    <div class="auth-step-container" id="step1Container">
                        
                        <div class="form-group">
                            <label for="regSchool" class="form-label">Asal Sekolah</label>
                            <div class="select-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">school</span>
                                <select id="regSchool" name="school" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Asal Sekolah --</option>
                                    <option value="SD Negeri 1 Denpasar" data-level="SD">SD Negeri 1 Denpasar (Jenjang SD)</option>
                                    <option value="SMP Negeri 1 Denpasar" data-level="SMP">SMP Negeri 1 Denpasar (Jenjang SMP)</option>
                                    <option value="SMA Negeri 1 Denpasar" data-level="SMA">SMA Negeri 1 Denpasar (Jenjang SMA)</option>
                                </select>
                                <span class="material-symbols-outlined select-arrow-icon" aria-hidden="true">expand_more</span>
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label for="regNis" class="form-label">Nomor Induk Siswa (NIS)</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">badge</span>
                                <input 
                                    type="text" 
                                    id="regNis" 
                                    name="nis" 
                                    class="form-input" 
                                    placeholder="Contoh: 12345678" 
                                    inputmode="numeric"
                                    required
                                >
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label for="regClass" class="form-label">Kelas</label>
                            <div class="select-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">class</span>
                                <select id="regClass" name="class" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kelas Terlebih Dahulu --</option>
                                    <option value="Kelas 1">Kelas 1</option>
                                    <option value="Kelas 2">Kelas 2</option>
                                    <option value="Kelas 3">Kelas 3</option>
                                    <option value="Kelas 4">Kelas 4</option>
                                    <option value="Kelas 5">Kelas 5</option>
                                    <option value="Kelas 6">Kelas 6</option>
                                    <option value="Kelas 7 (VII)">Kelas 7 (VII)</option>
                                    <option value="Kelas 8 (VIII)">Kelas 8 (VIII)</option>
                                    <option value="Kelas 9 (IX)">Kelas 9 (IX)</option>
                                    <option value="Kelas 10 (X)">Kelas 10 (X)</option>
                                    <option value="Kelas 11 (XI IPA 1)" selected>Kelas 11 (XI IPA 1)</option>
                                    <option value="Kelas 11 (XI IPS 1)">Kelas 11 (XI IPS 1)</option>
                                    <option value="Kelas 12 (XII)">Kelas 12 (XII)</option>
                                </select>
                                <span class="material-symbols-outlined select-arrow-icon" aria-hidden="true">expand_more</span>
                            </div>
                        </div>

                        
                        <button type="button" class="btn btn-primary btn-auth-submit" id="btnNextStep" style="margin-top: 12px;">
                            <span>Lanjut ke Data Akun</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>

                    
                    <div class="auth-step-container" id="step2Container" style="display: none;">
                        
                        <div class="form-group">
                            <label for="regUsername" class="form-label">Nama Pengguna (Username)</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">person</span>
                                <input 
                                    type="text" 
                                    id="regUsername" 
                                    name="username" 
                                    class="form-input" 
                                    placeholder="Contoh: alya_mbg" 
                                    autocomplete="username"
                                    required
                                >
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label for="regEmail" class="form-label">Alamat Email</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">mail</span>
                                <input 
                                    type="email" 
                                    id="regEmail" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="Contoh: alya@sekolah.id" 
                                    autocomplete="email"
                                    required
                                >
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label for="regPassword" class="form-label">Kata Sandi</label>
                            <div class="input-wrapper password-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">lock</span>
                                <input 
                                    type="password" 
                                    id="regPassword" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Minimal 8 karakter" 
                                    autocomplete="new-password"
                                    required
                                >
                                <button 
                                    type="button" 
                                    class="btn-toggle-password" 
                                    id="toggleRegPasswordBtn" 
                                    aria-label="Tampilkan kata sandi" 
                                    title="Tampilkan/Sembunyikan kata sandi"
                                >
                                    <span class="material-symbols-outlined eye-icon" id="regEyeIcon1">visibility</span>
                                </button>
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label for="regConfirmPassword" class="form-label">Konfirmasi Kata Sandi</label>
                            <div class="input-wrapper password-wrapper">
                                <span class="material-symbols-outlined input-icon" aria-hidden="true">lock_reset</span>
                                <input 
                                    type="password" 
                                    id="regConfirmPassword" 
                                    name="confirmPassword" 
                                    class="form-input" 
                                    placeholder="Ulangi kata sandi yang sama" 
                                    autocomplete="new-password"
                                    required
                                >
                                <button 
                                    type="button" 
                                    class="btn-toggle-password" 
                                    id="toggleRegConfirmBtn" 
                                    aria-label="Tampilkan konfirmasi sandi" 
                                    title="Tampilkan/Sembunyikan konfirmasi sandi"
                                >
                                    <span class="material-symbols-outlined eye-icon" id="regEyeIcon2">visibility</span>
                                </button>
                            </div>
                        </div>

                        
                        <div class="form-options-row" style="margin-bottom: 16px;">
                            <label class="custom-checkbox-label" for="showRegPasswordCheck">
                                <input type="checkbox" id="showRegPasswordCheck" class="custom-checkbox-input">
                                <span class="custom-checkbox-box">
                                    <span class="material-symbols-outlined check-icon">check</span>
                                </span>
                                <span class="checkbox-text">Tampilkan sandi</span>
                            </label>
                        </div>

                        
                        <div class="auth-btn-row">
                            <button type="button" class="btn btn-secondary btn-back-step" id="btnPrevStep">
                                <span class="material-symbols-outlined">arrow_back</span>
                                <span>Kembali</span>
                            </button>
                            <button type="submit" class="btn btn-primary btn-auth-submit" id="btnRegisterSubmit">
                                <span class="btn-text">Daftar & Masuk</span>
                                <span class="material-symbols-outlined">check_circle</span>
                            </button>
                        </div>
                    </div>
                </form>

                
                <div class="auth-card-footer">
                    <p class="auth-register-text">Sudah punya akun? <a href="login.html" class="auth-register-link">Masuk Sekarang!</a></p>
                </div>
            </div>
        </div>
    </main>

    
    <script src="{{ asset('js/pages/register.js') }}"></script>
</body>
</html>
