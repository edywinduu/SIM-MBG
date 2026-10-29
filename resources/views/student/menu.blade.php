<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Menu — SIM-MBG Siswa</title>
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
<body id="menuSelectionPage">
    
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="main-header">
        <div class="container nav-container">
            <a href="dashboard.html" class="student-navbar-brand" aria-label="SIM-MBG Dashboard">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title">SIM-MBG</span>
            </a>

            <nav class="nav-links" id="mainNav">
                <ul class="student-nav-menu">
                    <li><a href="dashboard.html" class="student-nav-btn">Dashboard</a></li>
                    <li><a href="menu.html" class="student-nav-btn active">Pilih Menu</a></li>
                    <li><a href="history.html" class="student-nav-btn">Riwayat & Ulasan</a></li>
                    <li><a href="review.html" class="student-nav-btn">Profil Saya</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <a href="review.html" class="student-user-chip" title="Lihat Profil Saya">
                    <div class="student-user-avatar">A</div>
                    <div class="student-user-meta">
                        <span class="student-user-name student-display-name">Alya MBG</span>
                        <span class="student-user-role">Siswa</span>
                    </div>
                </a>

                <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" title="Beranda">
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
            
            <div class="menu-selection-header">
                <div>
                    <h1 class="student-welcome-h1">Pilih Menu Makan Siang ??</h1>
                    <p class="text-muted" style="font-size: 14px; margin: 0;">Pilih paket menu bergizi harian Anda sebelum batas waktu pemesanan ditutup.</p>
                </div>

                <div class="allergy-profile-pill">
                    <div class="allergy-icon-circle">
                        <span class="material-symbols-outlined">health_and_safety</span>
                    </div>
                    <div class="allergy-pill-text">
                        <h4>Profil Alergi Saya</h4>
                        <div class="allergy-tags" id="userAllergyTags">
                            
                        </div>
                    </div>
                </div>
            </div>

            
            <section class="cutoff-banner">
                <div class="cutoff-left">
                    <div class="cutoff-icon-box">
                        <span class="material-symbols-outlined" style="font-size: 28px;">timer</span>
                    </div>
                    <div>
                        <h3>Batas Waktu Pemilihan Menu (Cut-Off)</h3>
                        <p>Batas akhir konfirmasi pilihan untuk Rabu, 8 Oktober 2026 adalah pukul <strong>20:00 WITA</strong></p>
                    </div>
                </div>

                <div class="countdown-timer-box">
                    <div class="countdown-label">Sisa Waktu Pemilihan</div>
                    <div class="countdown-digits" id="countdownDigits">03:44:18</div>
                </div>
            </section>

            
            <div class="day-tabs-header" style="margin-bottom: var(--space-12);">
                <h2 class="section-title-sm" style="font-size: 20px;">
                    <span class="material-symbols-outlined" style="color: var(--color-primary); font-size: 24px;">date_range</span>
                    <span>Jadwal Minggu Ini</span>
                </h2>
                <p class="text-muted" style="font-size: 13px; margin: 4px 0 0 0;">Pilih hari untuk melihat atau mengubah menu harian Anda.</p>
            </div>

            
            <nav class="day-tabs-nav" aria-label="Pilih Hari Menu">
                <button type="button" class="day-tab-btn past" data-day="senin" disabled aria-disabled="true" title="Hari telah berakhir">
                    <span class="day-name">Senin</span>
                    <span class="day-date">6 Okt 2026</span>
                    <span class="day-state-tag past">Berakhir</span>
                </button>
                <button type="button" class="day-tab-btn past" data-day="selasa" disabled aria-disabled="true" title="Hari telah berakhir">
                    <span class="day-name">Selasa</span>
                    <span class="day-date">7 Okt 2026</span>
                    <span class="day-state-tag past">Berakhir</span>
                </button>
                <button type="button" class="day-tab-btn active" data-day="rabu">
                    <span class="day-name">Rabu</span>
                    <span class="day-date">8 Okt 2026</span>
                    <span class="day-state-tag open">Hari Ini (Aktif)</span>
                </button>
                <button type="button" class="day-tab-btn" data-day="kamis">
                    <span class="day-name">Kamis</span>
                    <span class="day-date">9 Okt 2026</span>
                    <span class="day-state-tag open">Buka</span>
                </button>
                <button type="button" class="day-tab-btn" data-day="jumat">
                    <span class="day-name">Jumat</span>
                    <span class="day-date">10 Okt 2026</span>
                    <span class="day-state-tag open">Buka</span>
                </button>
            </nav>

            
            <div class="menu-grid-header">
                <div>
                    <h2 class="section-title-sm" style="font-size: 22px;">Daftar Pilihan Menu: Rabu, 8 Oktober 2026</h2>
                    <p class="text-muted" style="font-size: 13px; margin: 0;">Tersedia 3 paket standar gizi seimbang yang disiapkan oleh SPPG Denpasar Selatan</p>
                </div>
                <span class="badge badge-primary">Tersedia 3 Menu</span>
            </div>

            
            <div class="menu-cards-grid" id="menuCardsGrid">
                
            </div>

            
            <section class="weekly-balance-panel">
                <h3>Keseimbangan Gizi Pilihanmu Minggu Ini</h3>
                <p>Estimasi kecukupan gizi berdasarkan paket menu yang telah Anda pilih selama periode aktif.</p>

                <div class="balance-cards-grid">
                    <div class="balance-card">
                        <div class="balance-title">Rata-rata Energi Harian</div>
                        <div class="balance-score">625 / 650 <span style="font-size: 14px; font-weight: 600;">Kkal</span></div>
                        <div class="balance-status">? Sesuai Angka Kecukupan Gizi (AKG)</div>
                    </div>

                    <div class="balance-card">
                        <div class="balance-title">Asupan Protein Harian</div>
                        <div class="balance-score">29 / 30 <span style="font-size: 14px; font-weight: 600;">Gram</span></div>
                        <div class="balance-status">? Optimal untuk Pertumbuhan Remaja</div>
                    </div>

                    <div class="balance-card">
                        <div class="balance-title">Kesesuaian Profil Alergi</div>
                        <div class="balance-score">100% <span style="font-size: 14px; font-weight: 600;">Aman</span></div>
                        <div class="balance-status">? Terverifikasi Dapur SPPG Bebas Alergen</div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 SIM-MBG — Sistem Informasi Manajemen Makan Bergizi Gratis.</p>
                <p>Kementerian Pendidikan & Badan Gizi Nasional</p>
            </div>
        </div>
    </footer>

    
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/siswa.js') }}"></script>
</body>
</html>
