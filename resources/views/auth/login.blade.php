<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk SIM-MBG</title>
    <link rel="icon" href="{{ asset('assets/icons/logo.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            <a href="{{ route('landing') }}" class="student-navbar-brand logo-nav" aria-label="SIM-MBG Beranda">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title logo-brand-text">SIM-MBG</span>
            </a>
            <a href="{{ route('landing') }}" class="auth-back-link">
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
                    <h1 class="auth-title">{{ request()->is('sppg*') || request('role') === 'sppg' ? 'Masuk SPPG' : 'Masuk' }}</h1>
                </div>

                
                <div class="auth-alert" id="authAlert" role="alert" style="display: none;">
                    <span class="material-symbols-outlined auth-alert-icon" id="alertIcon">info</span>
                    <span class="auth-alert-text" id="alertText"></span>
                </div>

                
                <form action="{{ route('login') }}" id="loginForm" class="auth-form" novalidate method="post">
                    @csrf
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Nama Pengguna / NIS / Email</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon" aria-hidden="true">person</span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="{{ old('username', $rememberedUser ?? '') }}"
                                class="form-input" 
                                placeholder="Masukkan nama pengguna, NIS, atau email" 
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="password" class="form-label">Kata Sandi</label>
                        </div>
                        <div class="input-wrapper password-wrapper">
                            <span class="material-symbols-outlined input-icon" aria-hidden="true">lock</span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Masukkan kata sandi Anda" 
                                autocomplete="current-password"
                                required
                            >
                            <button 
                                type="button" 
                                class="btn-toggle-password" 
                                id="togglePasswordBtn" 
                                aria-label="Tampilkan kata sandi" 
                                title="Tampilkan atau sembunyikan kata sandi"
                            >
                                <span class="material-symbols-outlined eye-icon" id="eyeIcon">visibility</span>
                            </button>
                        </div>
                    </div>

                    
                    <div class="form-options-row">
                        
                        <label class="custom-checkbox-label" for="rememberMe">
                            <input type="checkbox" id="rememberMe" name="rememberMe" value="1" class="custom-checkbox-input" {{ old('rememberMe', !empty($rememberedUser)) ? 'checked' : '' }}>
                            <span class="custom-checkbox-box">
                                <span class="material-symbols-outlined check-icon">check</span>
                            </span>
                            <span class="checkbox-text">Ingat saya</span>
                        </label>

                        
                        <label class="custom-checkbox-label" for="showPasswordCheck">
                            <input type="checkbox" id="showPasswordCheck" class="custom-checkbox-input">
                            <a href="#" class="auth-forgot-link" id="forgotPasswordBtn">Lupa Kata Sandi?</a>
                        </label>
                    </div>

                    
                    <button type="submit" class="btn btn-primary btn-auth-submit" id="loginSubmitBtn">
                        <span class="btn-text">Masuk</span>
                        <span class="material-symbols-outlined btn-icon" aria-hidden="true">arrow_forward</span>
                    </button>
                </form>

                
                <div class="auth-card-footer">
                    @if(request()->is('sppg*') || request('role') === 'sppg')
                        <p class="auth-register-text" style="color: var(--color-muted);">Portal Khusus Petugas Sentra SPPG SIM-MBG</p>
                    @else
                        <p class="auth-register-text">Belum ada akun? <a href="{{ route('register') }}" class="auth-register-link">Daftar Sekarang!</a></p>
                    @endif
                </div>
            </div>
        </div>
    </main>

    
    <script src="{{ asset('js/pages/login.js') }}"></script>
</body>
</html>