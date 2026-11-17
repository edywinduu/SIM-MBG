<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beranda — SIM-MBG SPPG</title>
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
<body id="sppgDashboardPage">
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
                    <li><a href="{{ route('sppg.dashboard') }}" class="student-nav-btn sppg-nav-btn active">Dashboard</a></li>
                    <li><a href="{{ route('sppg.menu') }}" class="student-nav-btn sppg-nav-btn">Menu</a></li>
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
                    <h1 class="sppg-title">Selamat Pagi, Admin SPPG! ??</h1>
                    <p class="sppg-sub">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <span id="todayDate">–</span> • Satuan Pelayanan Gizi Wilayah Denpasar & Badung
                    </p>
                </div>
                <div class="sppg-actions">
                    <select class="sppg-select" id="periodSelect" aria-label="Pilih periode">
                        <option>Oktober 2026</option>
                        <option>September 2026</option>
                        <option>Agustus 2026</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" id="syncBtn">
                        <span class="material-symbols-outlined" style="font-size:18px;">sync</span><span>Sinkron</span>
                    </button>
                </div>
            </section>

            <section class="sppg-panel sppg-announce">
                <div class="sppg-ico ico-orange"><span class="material-symbols-outlined">notifications_active</span></div>
                <div class="sppg-announce-body">
                    <div><span class="pill pill-orange">PENGUMUMAN SPPG</span> <small class="text-muted">07:30 WIB</small></div>
                    <h3>Jadwal Distribusi Menu MBG</h3>
                    <p>Periode Oktober 2026 sedang berjalan aktif sesuai standar AKG Nasional.</p>
                </div>
                <a href="#" class="sppg-link" data-toast="Membuka Pedoman Siklus...">Lihat Pedoman Siklus <span class="material-symbols-outlined">chevron_right</span></a>
            </section>

            <section class="sppg-kpi-grid">
                <article class="sppg-kpi">
                    <div class="sppg-kpi-top"><span>Total Porsi Hari Ini</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">lunch_dining</span></div></div>
                    <div class="sppg-kpi-val">3.450 <small>Porsi</small></div>
                    <div class="sppg-kpi-foot"><span class="pill pill-green">? +5.2%</span><span>vs target kemarin</span></div>
                </article>
                <article class="sppg-kpi">
                    <div class="sppg-kpi-top"><span>Sekolah Penerima Aktif</span><div class="sppg-ico ico-blue"><span class="material-symbols-outlined">school</span></div></div>
                    <div class="sppg-kpi-val">18 <small>Lembaga</small></div>
                    <div class="sppg-kpi-foot"><span class="pill pill-blue">100% Tercakup</span><span>SD & SMP Negeri</span></div>
                </article>
                <article class="sppg-kpi">
                    <div class="sppg-kpi-top"><span>Menu Terverifikasi</span><div class="sppg-ico ico-green"><span class="material-symbols-outlined">verified</span></div></div>
                    <div class="sppg-kpi-val">12 <small>Siklus Menu</small></div>
                    <div class="sppg-kpi-foot"><span class="pill pill-green">Standar Halal & Higienis</span></div>
                </article>
                <article class="sppg-kpi">
                    <div class="sppg-kpi-top"><span>Evaluasi & Catatan</span><div class="sppg-ico ico-orange"><span class="material-symbols-outlined">report</span></div></div>
                    <div class="sppg-kpi-val val-accent">4 <small>Catatan Baru</small></div>
                    <div class="sppg-kpi-foot"><span class="pill pill-orange">Perlu Tindak Lanjut</span><span>Umpan balik sekolah</span></div>
                </article>
            </section>

            <div class="sppg-two-col">
                <section class="sppg-panel">
                    <div class="sppg-panel-head">
                        <h2><span class="sppg-ico ico-blue sm"><span class="material-symbols-outlined">local_shipping</span></span>Aktivitas Distribusi & Verifikasi</h2>
                        <a href="#" class="sppg-link sm" data-toast="Menampilkan semua aktivitas...">Lihat Semua</a>
                    </div>
                    <ul class="sppg-activity" id="activityList"></ul>
                </section>

                <section class="sppg-panel">
                    <div class="sppg-panel-head">
                        <h2><span class="sppg-ico ico-blue sm"><span class="material-symbols-outlined">restaurant</span></span>Menu Hari Ini</h2>
                        @if ($todayMenu && $todaySchedule)
                            <span class="pill pill-green">{{ $todaySchedule->cycle ?? 'Siklus Aktif' }}</span>
                        @endif
                    </div>
                    @if ($todayMenu)
                        <div class="sppg-today-img">
                            <img src="{{ $todayMenu->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $todayMenu->name }}" loading="lazy">
                            <div class="sppg-today-cap">
                                <small>{{ match ($todayMenu->category) { 'special' => 'PAKET MENU SPESIAL', 'backup' => 'PAKET MENU CADANGAN', default => 'PAKET MENU STANDAR' } }}</small>
                                <strong>{{ $todayMenu->name }}</strong>
                            </div>
                        </div>
                        @php
                            $componentLabels = ['Karbohidrat', 'Lauk Utama', 'Sayuran', 'Buah & Pelengkap'];
                        @endphp
                        <ul class="sppg-comp-list">
                            @forelse ($todayMenu->items ?? [] as $index => $item)
                                <li>
                                    <b>{{ $componentLabels[$index] ?? 'Pelengkap' }}</b>
                                    <span>{{ $item }}</span>
                                </li>
                            @empty
                                <li>
                                    <b>Komposisi</b>
                                    <span>{{ $todayMenu->description ?? 'Rincian menu belum tersedia.' }}</span>
                                </li>
                            @endforelse
                        </ul>
                        <div class="sppg-macro-3">
                            <div><small>Energi</small><strong>{{ $todayMenu->calories ?? 0 }} <em>kkal</em></strong></div>
                            <div><small>Protein</small><strong>{{ (float) ($todayMenu->protein ?? 0) }} <em>gram</em></strong></div>
                            <div><small>Serat Pangan</small><strong>{{ (float) ($todayMenu->fiber ?? 0) }} <em>gram</em></strong></div>
                        </div>
                    @else
                        <div class="sppg-empty">
                            <p style="margin: 0; font-weight: 600; font-size: var(--font-size-base, 1rem);">Belum Memilih Menu</p>
                        </div>
                    @endif
                </section>
            </div>

            <section class="sppg-panel">
                <div class="sppg-panel-head wrap">
                    <div>
                        <h2><span class="sppg-ico ico-blue sm"><span class="material-symbols-outlined">table_chart</span></span>Data Ringkasan Distribusi Sekolah</h2>
                        <p class="text-muted sppg-note">Pemantauan distribusi riil paket makan bergizi ke seluruh sekolah mitra SPPG</p>
                    </div>
                    <div class="sppg-actions">
                        <div class="sppg-search"><span class="material-symbols-outlined">search</span><input type="text" id="schoolSearch" placeholder="Cari nama sekolah / wilayah..."></div>
                        <button type="button" class="btn btn-primary btn-sm" id="createReportBtn"><span class="material-symbols-outlined" style="font-size:18px;">add</span><span>Buat Laporan Distribusi</span></button>
                    </div>
                </div>
                <div class="sppg-table-wrap">
                    <table class="sppg-table">
                        <thead><tr><th>No</th><th>Nama Sekolah</th><th>Wilayah / Kecamatan</th><th class="num">Jumlah Porsi</th><th>Estimasi Tiba</th><th class="ctr">Status</th><th class="ctr">Aksi</th></tr></thead>
                        <tbody id="schoolTableBody"></tbody>
                    </table>
                </div>
                <div class="sppg-table-foot">
                    <span class="text-muted" id="schoolInfo"></span>
                    <div class="sppg-pagination" id="schoolPagination"></div>
                </div>
            </section>

            <section class="sppg-help-bar">
                <span class="material-symbols-outlined">help</span>
                <span>Membutuhkan penyesuaian porsi khusus alergi atau siswa berkebutuhan nutrisi klinis? Buka modul Manajemen Siswa SPPG.</span>
                <a href="{{ route('sppg.selections') }}" class="sppg-link sm">Buka Manajemen Siswa <span class="material-symbols-outlined">open_in_new</span></a>
            </section>
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

    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/sppg.js') }}"></script>
</body>
</html>
