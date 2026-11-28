 <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIM-MBG — Sistem Informasi Manajemen Makan Bergizi Gratis</title>
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
    <link rel="stylesheet" href="{{ asset('css/pages/landing.css') }}">
</head>
<body>
    
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="student-navbar-brand logo-nav" aria-label="SIM-MBG Beranda">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title logo-brand-text">SIM-MBG</span>
            </a>

            <nav class="nav-links" id="mainNav">
                <ul class="student-nav-menu">
                    <li><a href="#beranda" class="student-nav-btn nav-link active">Beranda</a></li>
                    <li><a href="#menu-pilihan" class="student-nav-btn nav-link">Pilihan Menu</a></li>
                    <li><a href="#fitur" class="student-nav-btn nav-link">Keunggulan</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                @auth
                    @php
                        $authUser = Auth::user();
                        $roleLabel = match(strtolower($authUser->role ?? '')) {
                            'admin' => 'Administrator',
                            'sppg' => 'Petugas SPPG',
                            'student', 'siswa' => 'Siswa',
                            default => ucfirst($authUser->role ?? 'Pengguna'),
                        };
                        $displayName = $authUser->full_name ?: $authUser->username;
                        $initial = strtoupper(substr($displayName, 0, 1));
                    @endphp
                    <a href="{{ route('dashboard') }}" class="student-user-chip" title="Buka Dashboard">
                        <div class="student-user-avatar">
                            @if(!empty($authUser->avatar) && file_exists(public_path($authUser->avatar)))
                                <img src="{{ asset($authUser->avatar) }}" alt="{{ $displayName }}">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=297373&color=ffffff&bold=true" alt="{{ $displayName }}" onerror="this.onerror=null; this.parentElement.innerText='{{ $initial }}';">
                            @endif
                        </div>
                        <div class="student-user-meta">
                            <span class="student-user-name">{{ $displayName }}</span>
                            <span class="student-user-role">{{ $roleLabel }}</span>
                        </div>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="display: contents;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" id="logoutBtn" title="Keluar">
                            <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="student-user-chip" title="Masuk ke Akun SIM-MBG">
                        <div class="student-user-avatar">
                            <span class="material-symbols-outlined" style="font-size: 20px;">account_circle</span>
                        </div>
                        <div class="student-user-meta">
                            <span class="student-user-name">Masuk</span>
                            <span class="student-user-role">Portal MBG</span>
                        </div>
                    </a>
                @endauth
                <button type="button" class="mobile-nav-toggle" aria-controls="mainNav" aria-expanded="false" aria-label="Buka Menu Navigasi">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <main id="beranda">
        <section class="landing-hero">
            <div class="container hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        <span>Sistem Manajemen Gizi</span>
                    </div>

                    <h1 class="hero-title">SIM-MBG</h1>

                    <p class="hero-tagline">Siap memilih menu hari ini? ??</p>

                    <p class="hero-desc">
                        Sistem Informasi Manajemen Makan Bergizi Gratis terintegrasi untuk memastikan setiap siswa menerima asupan yang diinginkan!
                    </p>

                    <div class="hero-cta-group">
                        <a href="https://wa.me/" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            <img src="{{ asset('assets/icons/whatsapp.svg') }}" alt="WhatsApp Icon" width="20" height="20">
                            <span>Hubungi Kami</span>
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <span>Buka Dashboard</span>
                                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <span>Mulai Sekarang</span>
                                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
                            </a>
                        @endauth
                    </div>
                </div>

                
                <div class="hero-carousel" id="alur">
                    <h2 class="carousel-section-title">Alur Pemesanan</h2>

                    
                    <div class="timeline-container">
                        <div class="timeline-track" aria-hidden="true">
                            <div class="timeline-progress" id="timelineProgress"></div>
                        </div>

                        <div class="timeline-steps" role="tablist" aria-label="Alur Pemesanan">
                            
                            <button type="button" class="step-item active-done" data-step="1" role="tab" id="stepTab1" aria-controls="carouselCard1" aria-selected="false" title="Langkah 1: Daftar Akun">
                                <div class="step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <line x1="19" y1="8" x2="19" y2="14"></line>
                                        <line x1="22" y1="11" x2="16" y2="11"></line>
                                    </svg>
                                </div>
                                <span class="step-label">Daftar</span>
                            </button>

                            
                            <button type="button" class="step-item active" data-step="2" role="tab" id="stepTab2" aria-controls="carouselCard2" aria-selected="true" title="Langkah 2: Pilih Menu">
                                <div class="step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                        <line x1="6" y1="1" x2="6" y2="4"></line>
                                        <line x1="10" y1="1" x2="10" y2="4"></line>
                                        <line x1="14" y1="1" x2="14" y2="4"></line>
                                    </svg>
                                </div>
                                <span class="step-label">Pilih</span>
                            </button>

                            
                            <button type="button" class="step-item" data-step="3" role="tab" id="stepTab3" aria-controls="carouselCard3" aria-selected="false" title="Langkah 3: Konfirmasi & Ambil">
                                <div class="step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <span class="step-label">Konfirmasi</span>
                            </button>
                        </div>
                    </div>

                    
                    <div class="carousel-stage-container" aria-roledescription="carousel" aria-label="Alur Pemesanan Interaktif">
                        
                        <button type="button" class="carousel-nav-btn prev-btn" id="prevBtn" aria-label="Langkah Sebelumnya">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>

                        
                        <div class="carousel-viewport" id="carouselViewport">
                            
                            <div class="carousel-card card-step-1 card-left" id="carouselCard1" data-step="1" role="tabpanel" aria-labelledby="stepTab1" aria-hidden="true" tabindex="0">
                                <div class="step-badge-pill">
                                    <span class="badge-dot" aria-hidden="true"></span>
                                    <span>Langkah 01: Daftar Akun</span>
                                </div>
                                <img src="{{ asset('assets/images/card-daftar-akun.svg') }}" alt="Langkah 01: Ilustrasi antarmuka pendaftaran akun siswa" class="card-hero-img" width="520" height="320" />
                            </div>

                            
                            <div class="carousel-card card-step-2 active card-center" id="carouselCard2" data-step="2" role="tabpanel" aria-labelledby="stepTab2" aria-hidden="false" tabindex="0">
                                <div class="step-badge-pill">
                                    <span class="badge-dot" aria-hidden="true"></span>
                                    <span>Langkah 02: Pilih Menu Makanan</span>
                                </div>
                                <img src="{{ asset('assets/images/card-pilih-menu.svg') }}" alt="Langkah 02: Ilustrasi antarmuka pemilihan menu makanan bergizi" class="card-hero-img" width="520" height="320" />
                            </div>

                            
                            <div class="carousel-card card-step-3 card-right" id="carouselCard3" data-step="3" role="tabpanel" aria-labelledby="stepTab3" aria-hidden="true" tabindex="0">
                                <div class="step-badge-pill">
                                    <span class="badge-dot" aria-hidden="true"></span>
                                    <span>Langkah 03: Konfirmasi Pesanan</span>
                                </div>
                                <img src="{{ asset('assets/images/card-konfirmasi.svg') }}" alt="Langkah 03: Ilustrasi kartu ambil makanan dan konfirmasi pesanan" class="card-hero-img" width="520" height="320" />
                            </div>
                        </div>

                        
                        <button type="button" class="carousel-nav-btn next-btn" id="nextBtn" aria-label="Langkah Selanjutnya">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>

                    
                    <p class="carousel-bottom-caption" id="carouselCaption" aria-live="polite">Tekan Menu Makanan</p>
                </div>
            </div>
        </section>

        
        <section class="marquee-section" id="menu-pilihan">
            <div class="marquee-header">
                <span class="marquee-subtitle">Variasi Menu Sehat Harian Penerima MBG</span>
            </div>
            <div class="marquee-container" aria-label="Daftar Variasi Menu Makanan">
                <div class="marquee-content">
                    @if(isset($menus) && $menus->count() > 0)
                        @foreach([1, 2] as $repeat)
                            @foreach($menus as $menuItem)
                                <div class="menu-chip"
                                     data-menu-name="{{ $menuItem->name }}"
                                     data-menu-desc="{{ $menuItem->description }}"
                                     data-menu-calories="{{ $menuItem->calories }}"
                                     role="button"
                                     tabindex="0"
                                     aria-label="{{ $menuItem->name }}: {{ $menuItem->description }}">
                                    <span class="menu-chip-name">{{ $menuItem->name }}</span>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>
        </section>

        
        <section class="features-section" id="fitur">
            <div class="container">
                <div class="section-title-center">
                    <span class="badge badge-primary">Standar Kualitas MBG</span>
                    <h2>Kombinasi Gizi Seimbang & Teknologi Akurat</h2>
                    <p>Mendukung terlaksananya program Makan Bergizi Gratis pemerintah dengan transparansi menu dan pemantauan distribusi yang presisi.</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <span class="material-symbols-outlined">nutrition</span>
                        </div>
                        <h3>Nutrisi Terstandar Kemenkes</h3>
                        <p>Kandungan energi harian (600�650 kkal) dengan proporsi makronutrisi seimbang antara protein hewani/nabati, serat sayuran, dan buah segar.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <span class="material-symbols-outlined">health_and_safety</span>
                        </div>
                        <h3>Peringatan Alergi Siswa</h3>
                        <p>Sistem secara otomatis mendeteksi profil pantangan makanan siswa (kacang, seafood, telur, laktosa) dan memandu pemilihan paket alternatif yang aman.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon-box">
                            <span class="material-symbols-outlined">sync_alt</span>
                        </div>
                        <h3>Integrasi Real-Time SPPG</h3>
                        <p>Dapur SPPG menerima rekap porsi dan label kemasan secara instan, meminimalkan sisa makanan (zero food waste) dan keterlambatan distribusi.</p>
                    </div>
                </div>
            </div>
        </section>

        
        <section class="cta-banner-section">
            <div class="container">
                <div class="cta-banner">
                    <div class="cta-text-content">
                        <h2>Wujudkan Generasi Sehat & Berprestasi</h2>
                        <p>Dukung pemenuhan nutrisi anak bangsa setiap hari sekolah. Masuk ke akun Anda sekarang untuk memilih menu bergizi esok hari.</p>
                    </div>
                    <div class="cta-actions">
                        <a href="{{ route('register') }}" class="btn btn-primary">Daftar Akun Siswa</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">Masuk Portal</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-nav">
                        <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="40" height="36">
                        <span class="logo-brand-text" style="font-size: 20px;">SIM-MBG</span>
                    </div>
                    <p>Sistem Informasi Manajemen Makan Bergizi Gratis. Memastikan hak setiap siswa atas nutrisi harian berkualitas.</p>
                </div>

                <div class="footer-col">
                    <h4>Navigasi</h4>
                    <ul>
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#alur">Alur Pemesanan</a></li>
                        <li><a href="#menu-pilihan">Pilihan Menu</a></li>
                        <li><a href="#fitur">Keunggulan</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Bantuan & Kontak</h4>
                    <ul>
                        <li><a href="https://wa.me/" target="_blank" rel="noopener noreferrer">Layanan WhatsApp SPPG</a></li>
                        <li><a href="mailto:info@sim-mbg.go.id">info@sim-mbg.go.id</a></li>
                        <li><span style="color: var(--color-muted); font-size: 14px;">Waktu Operasional: 07:00 - 16:00 WITA</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 SIM-MBG. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/landing.js') }}"></script>
</body>
</html>