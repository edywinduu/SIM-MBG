<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Siswa — SIM-MBG</title>
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
    <link rel="stylesheet" href="{{ asset('css/pages/siswa.css') }}">
</head>
<body id="dashboardPage">
    
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ route('student.dashboard') }}" class="student-navbar-brand" aria-label="SIM-MBG Dashboard">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title">SIM-MBG</span>
            </a>

            
            <nav class="nav-links" id="mainNav">
                <ul class="student-nav-menu">
                    <li><a href="{{ route('student.dashboard') }}" class="student-nav-btn {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="{{ route('student.menu') }}" class="student-nav-btn {{ request()->routeIs('student.menu') ? 'active' : '' }}">Pilih Menu</a></li>
                    <li><a href="{{ route('student.history') }}" class="student-nav-btn {{ request()->routeIs('student.history') ? 'active' : '' }}">Riwayat & Ulasan</a></li>
                    <li><a href="{{ route('student.profile') }}" class="student-nav-btn {{ request()->routeIs('student.profile') ? 'active' : '' }}">Profil Saya</a></li>
                </ul>
            </nav>

            
            <div class="nav-actions">
                @php
                    $authUser = Auth::user();
                    $displayName = $authUser?->full_name ?: ($authUser?->username ?? 'Siswa');
                    $initial = strtoupper(substr($displayName, 0, 1));
                @endphp
                <a href="{{ route('student.profile') }}" class="student-user-chip" title="Lihat Profil Saya">
                    <div class="student-user-avatar">{{ $initial }}</div>
                    <div class="student-user-meta">
                        <span class="student-user-name student-display-name">{{ $displayName }}</span>
                        <span class="student-user-role">Siswa</span>
                    </div>
                </a>

                <a href="{{ route('landing') }}" class="btn btn-secondary btn-sm" title="Beranda">
                    <span class="material-symbols-outlined" style="font-size: 18px;">home</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: contents;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" id="logoutBtn" title="Keluar">
                        <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                    </button>
                </form>

                
                <button type="button" class="mobile-nav-toggle" aria-controls="mainNav" aria-expanded="false" aria-label="Buka Menu Navigasi">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    
    <main class="student-main-content">
        <div class="container">
            
            <section class="student-header-banner">
                <h1 class="student-welcome-h1">Selamat Pagi, <span class="student-display-name">{{ auth()->user()->full_name }}</span></h1>
                <div class="student-meta-subtitle">
                    <div class="student-meta-item">
                        <span class="material-symbols-outlined" style="color: var(--color-primary); font-size: 18px;">calendar_today</span>
                        <span>{{ now()-> translatedFormat('l, d F Y')}}</span>
                    </div>
                    <div class="student-meta-item">
                        <span class="material-symbols-outlined" style="color: var(--color-primary); font-size: 18px;">school</span>
                        <span class="student-display-school">{{ auth()->user()->institution }}</span>
                    </div>
                    <span class="student-meta-badge">{{ $student?->class_name ?? 'Error' }}</span>
                </div>
            </section>
            <div class="dashboard-grid">
                
                <div class="dashboard-left-col">
                    
                    <article class="distribusi-card">
                        <div class="card-top-title">
                            <h2 class="section-title-sm">
                                <span class="material-symbols-outlined" style="color: var(--color-primary);">lunch_dining</span>
                                <span>Distribusi Makan Siang</span>
                            </h2>
                            <span class="badge badge-primary">Sesuai Jadwal</span>
                        </div>

                        <div class="time-badge-box">
                            <div class="time-info-left">
                                <div class="time-icon-circle">
                                    <span class="material-symbols-outlined">schedule</span>
                                </div>
                                <div>
                                    <div class="time-val">{{ $distributionTime ?? '11:30 - 12:30 WITA' }}</div>
                                    <div class="time-loc">Lokasi: Kantin Sekolah / Ruang Makan Kelas</div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="pickup-status-btn pending" id="pickupStatusBtn">
                            <img src="{{ asset('assets/icons/icon-hourglass.png') }}" alt="Ikon Jam Pasir" class="pickup-btn-icon" aria-hidden="true">
                            <span id="pickupStateText">Aktif: Belum Diambil </span>
                        </button>
                    </article>

                    
                    <article class="notice-card">
                        <div class="tomorrow-action-box">
                            <div class="tomorrow-meta">
                                <h4>Pemilihan menu {{ now()->addDay()->translatedFormat('l, d F Y') }}</h4>
                                <p>Batas waktu pemilihan ditutup malam ini pukul {{ $cutoffTime ?? '20:00 WITA' }}</p>
                            </div>
                            <a href="{{ route('student.menu') }}" class="btn btn-primary btn-sm">
                                <span>Pilih Menu</span>
                                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            </a>
                        </div>
                    </article>
                </div>

                <div class="dashboard-right-col">
                    <article class="menu-today-card">
                        <div class="menu-today-img-wrap">
                            <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80" alt="Menu Hari Ini" loading="lazy">
                            <span class="menu-img-badge">
                                <span class="material-symbols-outlined" style="font-size: 16px;">restaurant</span>
                                <span>MENU HARI INI</span>
                            </span>
                        </div>

                        <div class="menu-today-body">
                            <h3 class="menu-today-title" id="todayMenuTitle">{{ $selectedMenu?->name ?? 'Belum Memilih Menu' }}</h3>
                            
                            <div class="menu-composition-chips">
                                <span class="comp-chip">Nasi Pulen</span>
                                <span class="comp-chip">Ayam Panggang</span>
                                <span class="comp-chip">Capcay Segar</span>
                                <span class="comp-chip">Tempe Bacem</span>
                                <span class="comp-chip">Semangka</span>
                            </div>

                            
                            <div class="macro-bar-container">
                                <div class="macro-bar-labels">
                                    <span>Protein 35%</span>
                                    <span>Karbohidrat 45%</span>
                                    <span>Lemak Sehat 20%</span>
                                </div>
                                <div class="macro-bar-track">
                                    <div class="macro-seg-protein"></div>
                                    <div class="macro-seg-carb"></div>
                                    <div class="macro-seg-fat"></div>
                                </div>
                            </div>

                            
                            <div class="nutrition-grid-4">
                                <div class="nutri-cell">
                                    <div class="nutri-val">620</div>
                                    <div class="nutri-lbl">Kkal</div>
                                </div>
                                <div class="nutri-cell">
                                    <div class="nutri-val">32g</div>
                                    <div class="nutri-lbl">Protein</div>
                                </div>
                                <div class="nutri-cell">
                                    <div class="nutri-val">65g</div>
                                    <div class="nutri-lbl">Karbo</div>
                                </div>
                                <div class="nutri-cell">
                                    <div class="nutri-val">8g</div>
                                    <div class="nutri-lbl">Serat</div>
                                </div>
                            </div>

                            <div class="menu-eco-badge">
                                <span class="material-symbols-outlined" style="font-size: 18px;">compost</span>
                                <span>Kemasan Ramah Lingkungan & Higienis</span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            
            {{-- Section Statistik Analitik Siswa --}}
            <section class="dashboard-kpi-row">
                {{-- Kartu 1: Tingkat Kehadiran MBG --}}
                <div class="kpi-stat-card enhanced-kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-icon-wrap attendance">
                            <span class="material-symbols-outlined">event_available</span>
                        </div>
                        <span class="kpi-top-badge badge-success">
                            <span class="kpi-badge-dot"></span>
                            <span>{{ $attendanceRate >= 100 ? 'Sempurna' : 'Baik' }}</span>
                        </span>
                    </div>

                    <div class="kpi-main-metric">
                        <div class="kpi-stat-info">
                            <h3>{{ $attendanceRate }}%</h3>
                            <p>Tingkat Kehadiran MBG</p>
                        </div>
                    </div>

                    <div class="kpi-progress-wrap">
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill attendance-fill" style="width: {{ $attendanceRate }}%;"></div>
                        </div>
                    </div>

                    <div class="kpi-meta-details">
                        <div class="kpi-detail-item">
                            <span class="detail-label">Total Diambil:</span>
                            <span class="detail-value">{{ $totalPickups }} Hari</span>
                        </div>
                        <div class="kpi-detail-item">
                            <span class="detail-label">Terlewat:</span>
                            <span class="detail-value">{{ $missedPickups }} Hari</span>
                        </div>
                    </div>
                </div>

                {{-- Kartu 2: Ulasan Menu Diserahkan --}}
                <div class="kpi-stat-card enhanced-kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-icon-wrap review">
                            <span class="material-symbols-outlined">rate_review</span>
                        </div>
                        <span class="kpi-top-badge badge-info">
                            <span class="kpi-badge-dot info"></span>
                            <span>Aktif</span>
                        </span>
                    </div>

                    <div class="kpi-main-metric">
                        <div class="kpi-stat-info">
                            <h3>{{ $reviewCount }} Kali</h3>
                            <p>Ulasan Menu Diserahkan</p>
                        </div>
                    </div>

                    <div class="kpi-progress-wrap">
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill review-fill" style="width: {{ $reviewPercentage }}%;"></div>
                        </div>
                    </div>

                    <div class="kpi-meta-details">
                        <div class="kpi-detail-item">
                            <span class="detail-label">Sudah Diulas:</span>
                            <span class="detail-value">{{ $reviewCount }} Menu</span>
                        </div>
                        <div class="kpi-detail-item">
                            <span class="detail-label">Belum Diulas:</span>
                            <span class="detail-value">{{ $pendingReviewCount }} Menu</span>
                        </div>
                    </div>
                </div>

                {{-- Kartu 3: Rata-rata Kepuasan Rasa --}}
                <div class="kpi-stat-card enhanced-kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-icon-wrap rating">
                            <span class="material-symbols-outlined">star</span>
                        </div>
                        <span class="kpi-top-badge badge-warning">
                            <span class="material-symbols-outlined" style="font-size: 14px; color: #F59E0B;">hotel_class</span>
                            <span>Bintang {{ $avgRating }}</span>
                        </span>
                    </div>

                    <div class="kpi-main-metric">
                        <div class="kpi-stat-info">
                            <h3>{{ $avgRating }} <span class="kpi-sub-scale">/ 5.0</span></h3>
                            <p>Rata-rata Kepuasan Rasa</p>
                        </div>
                    </div>

                    <div class="kpi-progress-wrap">
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill rating-fill" style="width: {{ $ratingPercentage }}%;"></div>
                        </div>
                    </div>

                    <div class="kpi-meta-details">
                        <div class="kpi-detail-item">
                            <span class="detail-label">Penilaian Rasa:</span>
                            <span class="detail-value">Sangat Enak</span>
                        </div>
                        <div class="kpi-detail-item">
                            <span class="detail-label">Kesesuaian Porsi:</span>
                            <span class="detail-value">Pas & Cukup</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 SIM-MBG � Sistem Informasi Manajemen Makan Bergizi Gratis.</p>
                <p>Kementerian Pendidikan & Badan Gizi Nasional</p>
            </div>
        </div>
    </footer>

    
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/siswa.js') }}"></script>
</body>
</html>