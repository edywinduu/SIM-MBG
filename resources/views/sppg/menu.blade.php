<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu — SIM-MBG SPPG</title>
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
<body id="sppgMenuPage">
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
                    <li><a href="{{ route('sppg.menu') }}" class="student-nav-btn sppg-nav-btn active">Menu</a></li>
                    <li><a href="{{ route('sppg.selections') }}" class="student-nav-btn sppg-nav-btn">Pilihan Siswa</a></li>
                    <li><a href="{{ route('sppg.review') }}" class="student-nav-btn sppg-nav-btn">Review</a></li>
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
                    <div class="sppg-eyebrow"><span class="pill pill-green">● SPPG Wilayah Denpasar & Badung</span><span>Siklus Periode Oktober 2026</span></div>
                    <h1 class="sppg-title">Manajemen Siklus Menu Makan Bergizi Gratis (SPPG)</h1>
                    <p class="sppg-sub">Kelola daftar paket menu bergizi, kalori harian, alergen, dan jadwal distribusi untuk sekolah penerima.</p>
                </div>
                <div class="sppg-actions">
                    <button type="button" class="btn btn-secondary btn-sm" id="exportMenuBtn"><span class="material-symbols-outlined" style="font-size:18px;">download</span><span>Export Rekap (.CSV)</span></button>
                    <button type="button" class="btn btn-primary btn-sm" id="addMenuBtn"><span class="material-symbols-outlined" style="font-size:18px;">add_circle</span><span>Tambah Menu Baru</span></button>
                </div>
            </section>

            <section class="sppg-kpi-grid">
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>TOTAL MENU AKTIF</span><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">restaurant</span></div></div>
                    <div class="sppg-kpi-val" id="kpiTotalMenus">{{ $kpis['total_active_menus'] ?? 5 }} <small>Paket Menu</small></div><div class="sppg-kpi-foot text-primary">✓ 100% terjadwal 4 siklus</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>RATA-RATA KALORI</span><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">local_fire_department</span></div></div>
                    <div class="sppg-kpi-val" id="kpiAvgCalories">{{ $kpis['avg_calories'] ?? 615 }} <small>kkal / porsi</small></div><div class="sppg-kpi-foot text-primary">✓ Target Kemenkes: 580 - 650 kkal</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>MENU ALERGEN KHUSUS</span><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">shield</span></div></div>
                    <div class="sppg-kpi-val val-accent" id="kpiSpecialMenus">{{ $kpis['special_menus'] ?? 2 }} <small>Variasi</small></div><div class="sppg-kpi-foot val-accent">Melayani {{ $kpis['sensitive_students'] ?? 3 }} siswa sensitif</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>KEPATUHAN STANDAR GIZI</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">nutrition</span></div></div>
                    <div class="sppg-kpi-val">98.5% <small>Audit Ahli</small></div><div class="sppg-kpi-foot text-primary">☆ Akreditasi A Dinas Kesehatan</div></article>
            </section>

            <section class="sppg-panel sppg-toolbar">
                <div class="sppg-tabs" id="menuTabs">
                    <button type="button" class="sppg-tab active" data-filter="all">Semua Menu <span class="count" id="tabCountAll">{{ $counts['all'] ?? 5 }}</span></button>
                    <button type="button" class="sppg-tab" data-filter="reguler">Menu Reguler (SD/SMP) <span class="count" id="tabCountReguler">{{ $counts['reguler'] ?? 2 }}</span></button>
                    <button type="button" class="sppg-tab" data-filter="khusus">Menu Alergi Khusus <span class="count" id="tabCountKhusus">{{ $counts['khusus'] ?? 2 }}</span></button>
                    <button type="button" class="sppg-tab" data-filter="cadangan">Menu Cadangan Darurat <span class="count" id="tabCountCadangan">{{ $counts['cadangan'] ?? 1 }}</span></button>
                </div>
                <div class="sppg-actions">
                    <div class="sppg-search"><span class="material-symbols-outlined">search</span><input type="text" id="menuSearch" placeholder="Cari nama menu / bahan..."></div>
                    <div class="sppg-view-toggle">
                        <button type="button" class="active" data-view="grid" aria-label="Tampilan grid"><span class="material-symbols-outlined">grid_view</span></button>
                        <button type="button" data-view="list" aria-label="Tampilan list"><span class="material-symbols-outlined">view_list</span></button>
                    </div>
                </div>
            </section>

            <section class="sppg-menu-grid" id="menuGrid"></section>

            <section class="sppg-panel sppg-verify">
                <div class="sppg-ico ico-green lg"><span class="material-symbols-outlined">verified_user</span></div>
                <div class="sppg-verify-body">
                    <h3>Verifikasi Ahli Gizi SPPG Terpenuhi</h3>
                    <p>Semua paket menu di atas telah diaudit memenuhi Angka Kecukupan Gizi (AKG) 2025: kecukupan protein hewani = 22g dan mikronutrien esensial bagi usia 7–15 tahun.</p>
                </div>
                <div class="sppg-actions">
                    <button type="button" class="btn btn-secondary btn-sm" data-toast="Membuka riwayat audit gizi..."><span class="material-symbols-outlined" style="font-size:18px;">history</span><span>Riwayat Audit Gizi</span></button>
                    <button type="button" class="btn btn-primary btn-sm" data-toast="Menyiapkan lembar kontrol dapur..."><span class="material-symbols-outlined" style="font-size:18px;">print</span><span>Cetak Lembar Kontrol Dapur</span></button>
                </div>
            </section>

            <section class="sppg-panel sppg-table-foot solo">
                <span class="text-muted" id="menuInfo"></span>
                <div class="sppg-pagination"><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn">3</button><button class="page-btn">4</button></div>
            </section>
        </div>
    </main>

    <div class="sppg-modal" id="menuModal" role="dialog" aria-modal="true" aria-labelledby="menuModalTitle">
        <div class="sppg-modal-card">
            <div class="sppg-modal-head"><h3 id="menuModalTitle">Tambah Menu Baru</h3><button type="button" class="modal-close" data-close aria-label="Tutup"><span class="material-symbols-outlined">close</span></button></div>
            <form id="menuForm" novalidate>
                <div class="sppg-modal-body">
                    <label class="sppg-field">Nama Paket Menu<input type="text" id="mfName" class="sppg-input" required placeholder="Contoh: Paket Ayam Bakar & Sayur Asem"></label>
                    <label class="sppg-field">Komposisi Singkat<textarea id="mfDesc" class="sppg-input" rows="2" placeholder="Nasi, lauk, sayur, buah..."></textarea></label>
                    <div class="sppg-field-row">
                        <label class="sppg-field">Kalori (kkal)<input type="number" id="mfKcal" class="sppg-input" min="300" max="900" value="620" required></label>
                        <label class="sppg-field">Kategori<select id="mfCat" class="sppg-input"><option value="reguler">Reguler SD/SMP</option><option value="khusus">Alergi Khusus</option><option value="cadangan">Cadangan Darurat</option></select></label>
                    </div>
                    <div class="sppg-field-row three">
                        <label class="sppg-field">Karbo (g)<input type="number" id="mfCarb" class="sppg-input" value="72"></label>
                        <label class="sppg-field">Protein (g)<input type="number" id="mfProt" class="sppg-input" value="26"></label>
                        <label class="sppg-field">Lemak (g)<input type="number" id="mfFat" class="sppg-input" value="15"></label>
                    </div>
                </div>
                <div class="sppg-modal-foot"><button type="button" class="btn btn-secondary btn-sm" data-close>Batal</button><button type="submit" class="btn btn-primary btn-sm">Simpan Menu</button></div>
            </form>
        </div>
    </div>
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
        window.SPPG_MENUS = @json($menus ?? []);
    </script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/sppg.js') }}"></script>
</body>
</html>
