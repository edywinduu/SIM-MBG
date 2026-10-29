<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilihan Pengguna — SIM-MBG SPPG</title>
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
<body id="sppgPilihanPage">
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    <header class="main-header">
        <div class="container nav-container">
            <a href="dashboard.html" class="student-navbar-brand sppg-brand" aria-label="SIM-MBG SPPG Dashboard">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="student-brand-title sppg-brand-title">SIM-MBG</span>
            </a>
            <nav class="nav-links" id="mainNav">
                <ul class="student-nav-menu sppg-nav-menu">
                    <li><a href="dashboard.html" class="student-nav-btn sppg-nav-btn">Dashboard</a></li>
                    <li><a href="menu.html" class="student-nav-btn sppg-nav-btn">Menu</a></li>
                    <li><a href="selections.blade.php" class="student-nav-btn sppg-nav-btn active">Pilihan Siswa</a></li>
                    <li><a href="review.html" class="student-nav-btn sppg-nav-btn">Review</a></li>
                </ul>
            </nav>
            <div class="nav-actions">
                <a href="dashboard.html" class="student-user-chip sppg-user-chip" title="Profil Pegawai SPPG">
                    <div class="student-user-avatar sppg-user-avatar">A</div>
                    <div class="student-user-meta sppg-user-meta">
                        <span class="student-user-name sppg-user-name">Pgw Alya</span>
                        <span class="student-user-role sppg-user-role">Pegawai SPPG</span>
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

    <main class="sppg-main">
        <div class="container">
            <section class="sppg-page-head">
                <div>
                    <nav class="sppg-crumb" aria-label="Breadcrumb"><span>SPPG Nasional</span><span>›</span><span>Modul Distribusi</span><span>›</span><b>Pilihan Siswa</b></nav>
                    <h1 class="sppg-title">Preferensi & Pilihan Menu Siswa Penerima MBG</h1>
                    <p class="sppg-sub">Monitoring pilihan menu siswa, pencatatan diet khusus, riwayat alergi, dan penyesuaian porsi sekolah secara berkala.</p>
                </div>
                <div class="sppg-actions">
                    <button type="button" class="btn btn-secondary btn-sm" data-toast="Menyiapkan label kemasan..."><span class="material-symbols-outlined" style="font-size:18px;">print</span><span>Cetak Label Kemasan</span></button>
                    <button type="button" class="btn btn-primary btn-sm" id="addPrefBtn"><span class="material-symbols-outlined" style="font-size:18px;">add_circle</span><span>Input Preferensi Siswa</span></button>
                </div>
            </section>

            <section class="sppg-kpi-grid">
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>TOTAL TERDATA</span><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">groups</span></div></div>
                    <div class="sppg-kpi-val">12.482 <small>Siswa</small></div><div class="sppg-kpi-foot text-primary">? 100% Terverifikasi SPPG</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>PAKET REGULER</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">restaurant</span></div></div>
                    <div class="sppg-kpi-val val-primary">11.230 <span class="pill pill-green">90%</span></div><div class="sppg-kpi-foot">? Bebas alergen umum</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>DIET & ALERGI KHUSUS</span><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">warning</span></div></div>
                    <div class="sppg-kpi-val val-accent">1.252 <span class="pill pill-orange">10%</span></div><div class="sppg-kpi-foot val-accent">Monitoring Lab Ketat</div></article>
                <article class="sppg-kpi"><div class="sppg-kpi-top"><span>SEKOLAH TERHUBUNG</span><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">apartment</span></div></div>
                    <div class="sppg-kpi-val">68 <small>Unit SPPG</small></div><div class="sppg-kpi-foot">? 100% Dapur Unit Siap</div></article>
            </section>

            <section class="sppg-panel sppg-filterbar">
                <div class="sppg-search grow"><span class="material-symbols-outlined">search</span><input type="text" id="studentSearch" placeholder="Cari nama siswa, NISN, atau ID porsi..."></div>
                <select class="sppg-select" id="fSchool" aria-label="Filter sekolah"><option value="">Semua Sekolah</option></select>
                <select class="sppg-select" id="fClass" aria-label="Filter kelas"><option value="">Semua Kelas</option></select>
                <select class="sppg-select" id="fDiet" aria-label="Kategori diet">
                    <option value="">Kategori Diet</option><option value="reguler">Paket Reguler</option><option value="khusus">Diet / Alergi Khusus</option><option value="evaluasi">Dalam Evaluasi</option>
                </select>
                <select class="sppg-select" id="fStatus" aria-label="Filter status">
                    <option value="">Semua Status</option><option value="aktif">Aktif</option><option value="review">Review Medis</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" id="resetFilterBtn" title="Reset filter"><span class="material-symbols-outlined" style="font-size:18px;">filter_alt_off</span></button>
            </section>

            <section class="sppg-panel">
                <div class="sppg-panel-head wrap">
                    <h2><span class="dot"></span>Daftar Pilihan Menu Siswa <span class="pill pill-blue" id="recordCount">0 Records</span></h2>
                    <div class="sppg-actions">
                        <button type="button" class="btn btn-secondary btn-sm" id="exportStudentBtn"><span class="material-symbols-outlined" style="font-size:18px;">download</span><span>Ekspor CSV</span></button>
                    </div>
                </div>
                <div class="sppg-table-wrap">
                    <table class="sppg-table">
                        <thead><tr><th>No</th><th>NISN & Nama Siswa</th><th>Sekolah</th><th>Kelas</th><th>Pilihan Menu Aktif</th><th>Catatan Alergi / Diet</th><th class="ctr">Status Konsumsi</th><th class="ctr">Aksi</th></tr></thead>
                        <tbody id="studentTableBody"></tbody>
                    </table>
                </div>
                <div class="sppg-table-foot">
                    <span class="text-muted" id="studentInfo"></span>
                    <div class="sppg-pagination" id="studentPagination"></div>
                </div>
            </section>

            <section class="sppg-info-trio">
                <article class="sppg-info"><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">local_shipping</span></div><div><h4>Distribusi Porsi Pagi</h4><p>Dapur Unit SPPG Denpasar Pusat telah menyalurkan 4.120 porsi tepat waktu pukul 09.30 WITA.</p></div></article>
                <article class="sppg-info"><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">verified</span></div><div><h4>Protokol Wadah Bertanda</h4><p>1.252 porsi diet khusus menggunakan segel peach antimikroba dengan label nama dan NISN tertera.</p></div></article>
                <article class="sppg-info"><div class="sppg-ico ico-green"><span class="material-symbols-outlined">sync_alt</span></div><div><h4>Sinkronisasi Data UKS</h4><p>Data terhubung langsung dengan catatan rekam medis sekolah dan persetujuan wali murid.</p></div></article>
            </section>
        </div>
    </main>

    <div class="sppg-modal" id="studentModal" role="dialog" aria-modal="true" aria-labelledby="studentModalTitle">
        <div class="sppg-modal-card">
            <div class="sppg-modal-head"><h3 id="studentModalTitle">Ubah Preferensi Siswa</h3><button type="button" class="modal-close" data-close aria-label="Tutup"><span class="material-symbols-outlined">close</span></button></div>
            <form id="studentForm" novalidate>
                <div class="sppg-modal-body">
                    <label class="sppg-field">Nama Siswa<input type="text" id="sfName" class="sppg-input" required></label>
                    <div class="sppg-field-row">
                        <label class="sppg-field">NISN<input type="text" id="sfNisn" class="sppg-input" required></label>
                        <label class="sppg-field">Kelas<input type="text" id="sfClass" class="sppg-input" placeholder="VII-A" required></label>
                    </div>
                    <label class="sppg-field">Sekolah<input type="text" id="sfSchool" class="sppg-input" required></label>
                    <div class="sppg-field-row">
                        <label class="sppg-field">Pilihan Menu<select id="sfType" class="sppg-input"><option value="reguler">Paket Reguler A</option><option value="reguler-b">Paket Reguler B</option><option value="khusus">Diet / Alergi Khusus</option><option value="evaluasi">Dalam Evaluasi Menu</option></select></label>
                        <label class="sppg-field">Status<select id="sfStatus" class="sppg-input"><option value="aktif">Aktif</option><option value="review">Review Medis</option></select></label>
                    </div>
                    <label class="sppg-field">Catatan Alergi / Diet<textarea id="sfNote" class="sppg-input" rows="2" placeholder="Kosongkan jika tidak ada pantangan"></textarea></label>
                </div>
                <div class="sppg-modal-foot"><button type="button" class="btn btn-secondary btn-sm" data-close>Batal</button><button type="submit" class="btn btn-primary btn-sm">Simpan</button></div>
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

    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/sppg.js') }}"></script>
</body>
</html>
