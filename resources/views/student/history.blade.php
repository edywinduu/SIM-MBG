<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat & Ulasan — SIM-MBG Siswa</title>
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
<body id="riwayatPage">
    
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
                <h1 class="student-welcome-h1">Riwayat & Ulasan Menu ??</h1>
                <p class="text-muted" style="font-size: 14px; margin: 0;">Catatan riwayat pengambilan paket makanan bergizi dan testimoni kepuasan rasa Anda.</p>
            </section>

            
            <section class="riwayat-ribbon-summary">
                <div class="summary-col">
                    <div class="summary-lbl">Tingkat Kehadiran Makan</div>
                    <div class="summary-val">{{ $attendanceRate }}</div>
                    <div class="summary-desc">Selalu mengambil paket makan siang sesuai jadwal</div>
                </div>

                <div class="summary-col">
                    <div class="summary-lbl">Total Ulasan Diserahkan</div>
                    <div class="summary-val">{{ $totalReviews }} Kali</div>
                    <div class="summary-desc">Masukan Anda membantu evaluasi gizi oleh SPPG</div>
                </div>

                <div class="summary-col">
                    <div class="summary-lbl">Rata-rata Skor Bintang</div>
                    <div class="summary-val">{{ $avgScore }}</div>
                    <div class="summary-desc">Tingkat kepuasan sangat tinggi terhadap menu yang disajikan</div>
                </div>
            </section>

            
            <div class="riwayat-toolbar">
                <div class="filter-chips-group">
                    <button type="button" class="filter-chip-btn active" data-filter="all">Semua Menu</button>
                    <button type="button" class="filter-chip-btn" data-filter="reviewed">Sudah Diulas</button>
                    <button type="button" class="filter-chip-btn" data-filter="pending">Belum Diulas</button>
                </div>

                <div class="riwayat-search-sort">
                    <div class="search-input-wrapper">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="riwayatSearchInput" class="search-field" placeholder="Cari nama menu...">
                    </div>

                    <select class="sort-select" aria-label="Urutkan Riwayat">
                        <option value="newest">Urutkan: Terkini</option>
                        <option value="oldest">Urutkan: Terlama</option>
                        <option value="rating">Rating Tertinggi</option>
                    </select>
                </div>
            </div>

            
            <section class="riwayat-cards-feed" id="riwayatCardsFeed">
                
            </section>
        </div>
    </main>

    
    <div class="student-modal-backdrop" id="reviewModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="student-modal-card">
            <div class="student-modal-header">
                <h3 id="modalTitle">Edit Ulasan Makanan</h3>
                <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup Modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="student-modal-body">
                <h4 id="modalMenuTitle" style="font-size: 16px; font-weight: 700; color: #0B1C30; margin-bottom: 8px;">Paket A</h4>
                <p style="font-size: 13px; color: #62748E; margin-bottom: 16px;">Berikan rating bintang dan ulasan rasa untuk penyedia dapur SPPG:</p>

                
                <div class="star-rating-interactive">
                    <button type="button" class="star-rate-btn" data-value="1" title="Sangat Kurang">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                    <button type="button" class="star-rate-btn" data-value="2" title="Kurang">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                    <button type="button" class="star-rate-btn" data-value="3" title="Cukup">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                    <button type="button" class="star-rate-btn" data-value="4" title="Enak">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                    <button type="button" class="star-rate-btn active" data-value="5" title="Sangat Enak">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                </div>

                <div class="form-group-medical">
                    <label for="modalReviewText" style="font-size: 13px; font-weight: 700; color: #0B1C30; display: block; margin-bottom: 6px;">Komentar / Ulasan Anda:</label>
                    <textarea id="modalReviewText" class="form-textarea" rows="4" placeholder="Tuliskan pendapat Anda tentang rasa, kebersihan, porsi makanan..."></textarea>
                </div>
            </div>

            <div class="student-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="cancelReviewBtn">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="submitReviewBtn">Simpan Ulasan</button>
            </div>
        </div>
    </div>

    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 SIM-MBG — Sistem Informasi Manajemen Makan Bergizi Gratis.</p>
                <p>Kementerian Pendidikan & Badan Gizi Nasional</p>
            </div>
        </div>
    </footer>

    
    <script>
        window.STUDENT_HISTORY_DATA = @json($historyData);
    </script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/siswa.js') }}"></script>
</body>
</html>
