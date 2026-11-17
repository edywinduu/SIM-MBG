<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review — SIM-MBG SPPG</title>
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
    <link rel="stylesheet" href="{{ asset('css/pages/sppg.css') }}">
</head>
<body id="sppgReviewPage">
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ route('sppg.dashboard') }}" class="student-navbar-brand sppg-brand" aria-label="SIM-MBG SPPG Dashboard">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title sppg-brand-title">SIM-MBG</span>
            </a>
            <nav class="nav-links" id="mainNav">
                <ul class="student-nav-menu sppg-nav-menu">
                    <li><a href="{{ route('sppg.dashboard') }}" class="student-nav-btn sppg-nav-btn">Dashboard</a></li>
                    <li><a href="{{ route('sppg.menu') }}" class="student-nav-btn sppg-nav-btn">Menu</a></li>
                    <li><a href="{{ route('sppg.selections') }}" class="student-nav-btn sppg-nav-btn">Pilihan Siswa</a></li>
                    <li><a href="{{ route('sppg.review') }}" class="student-nav-btn sppg-nav-btn active">Review</a></li>
                </ul>
            </nav>
            <div class="nav-actions">
                @php
                    $sppgUser = Auth::user();
                    $displayName = $sppgUser?->full_name ?: ($sppgUser?->username ?? 'Petugas SPPG');
                    $initial = strtoupper(substr($displayName, 0, 1));
                @endphp
                <a href="{{ route('sppg.dashboard') }}" class="student-user-chip sppg-user-chip" title="Profil Pegawai SPPG">
                    <div class="student-user-avatar sppg-user-avatar">{{ $initial }}</div>
                    <div class="student-user-meta sppg-user-meta">
                        <span class="student-user-name sppg-user-name">{{ $displayName }}</span>
                        <span class="student-user-role sppg-user-role">{{ $sppgUser?->institution ?? 'Pegawai SPPG' }}</span>
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

    <main class="sppg-main">
        <div class="container">
            <section class="sppg-page-head">
                <div>
                    <div class="sppg-eyebrow"><span class="pill pill-green">? Layer 3 • Modul SPPG</span><span>Periode Distribusi: Oktober 2026</span></div>
                    <h1 class="sppg-title">Review & Evaluasi Kepuasan Menu MBG</h1>
                    <p class="sppg-sub">Umpan balik komprehensif dari sekolah mitra, pengawas UKS, dan audit konsumsi gizi harian.</p>
                </div>
                <div class="sppg-actions">
                    <select class="sppg-select" aria-label="Periode"><option>Oktober 2026</option><option>September 2026</option></select>
                    <button type="button" class="btn btn-primary btn-sm" data-toast="Menyiapkan laporan audit gizi..."><span class="material-symbols-outlined" style="font-size:18px;">download</span><span>Unduh Laporan Audit Gizi</span></button>
                </div>
            </section>

            <section class="sppg-kpi-grid">
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>SKOR KEPUASAN</span><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">star</span></div></div>
                    <div class="sppg-kpi-val">{{ $kpis['satisfaction_score'] }} <small>/ 5.00</small></div><div class="sppg-kpi-foot text-primary">▲ +0.18 dari siklus lalu</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>KETEPATAN DISTRIBUSI</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">schedule</span></div></div>
                    <div class="sppg-kpi-val">{{ $kpis['distribution_rate'] }}% <small>on-time</small></div><div class="sppg-kpi-foot">Rata-rata tiba: <b>09.25 WITA</b></div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>TINGKAT KONSUMSI</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">restaurant</span></div></div>
                    <div class="sppg-kpi-val">{{ $kpis['consumption_rate'] }}% <span class="pill pill-solid">Optimal</span></div><div class="sppg-kpi-foot">Sisa makanan (waste): {{ $kpis['waste_rate'] }}%</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>ULASAN DITERIMA</span><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">rate_review</span></div></div>
                    <div class="sppg-kpi-val">{{ $kpis['total_reviews'] }} <small>laporan</small></div><div class="sppg-kpi-foot"><span>{{ $kpis['active_schools'] }} Satuan Sekolah</span><span class="pill pill-green">100% Mengisi</span></div></article>
            </section>

            <div class="sppg-review-grid">
                <section>
                    <div class="sppg-panel-head wrap plain">
                        <h2>Umpan Balik Satuan Pendidikan <span class="pill pill-gray">Terbaru</span></h2>
                        <div class="sppg-chips" id="reviewFilters">
                            <button type="button" class="sppg-chip active" data-filter="all">Semua</button>
                            <button type="button" class="sppg-chip" data-filter="puas">Sangat Puas</button>
                            <button type="button" class="sppg-chip" data-filter="evaluasi">Perlu Evaluasi</button>
                        </div>
                    </div>
                    <div class="sppg-feedback-list" id="feedbackList"></div>
                </section>

                <aside class="sppg-side">
                    <section class="sppg-panel">
                        <h3 class="sppg-side-title">Sebaran Rating Kepuasan</h3>
                        <div class="sppg-rating-bars" id="ratingBars"></div>
                    </section>
                    <section class="sppg-panel">
                        <h3 class="sppg-side-title">Menu Paling Disukai Siswa</h3>
                        <ul class="sppg-fav-list" id="favList"></ul>
                    </section>
                    <section class="sppg-audit">
                        <h4><span class="material-symbols-outlined">verified_user</span>AUDIT HIGIENITAS DAPUR</h4>
                        <p>{{ $latestAudit?->result ?? 'Dapur SPPG Pusat telah tersertifikasi Laik Higiene Sanitasi dengan pemeriksaan harian suhu wadah & uji sampel mikrobiologi.' }}</p>
                        <button type="button" class="btn sppg-audit-btn" data-toast="Membuka sertifikat {{ $latestAudit?->certificate_file ?? 'laboratorium' }}...">Lihat Sertifikat Laboratorium</button>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <div class="sppg-toast" id="sppgToast" role="status" aria-live="polite"></div>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 SIM-MBG — Sistem Informasi Manajemen Makan Bergizi Gratis.</p>
                <p>Kementerian Pendidikan & Badan Gizi Nasional</p>
            </div>
        </div>
    </footer>

    <script>
        window.SPPG_FEEDBACKS = @json($feedbacks);
        window.SPPG_RATINGS = @json($ratings);
        window.SPPG_FAVORITES = @json($favorites);
    </script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/sppg.js') }}"></script>
</body>
</html>
