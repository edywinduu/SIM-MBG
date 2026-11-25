<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin — SIM-MBG</title>
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
    <link rel="stylesheet" href="{{ asset('css/pages/admin.css') }}">
</head>
<body class="admin-body" id="adminDashboardPage">
    
    <div class="ambient-decorations" aria-hidden="true">
        <div class="ambient-ellipse-secondary"></div>
        <div class="ambient-ellipse-accent"></div>
    </div>

    
    
    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ route('admin.dashboard') }}" class="admin-navbar-brand" aria-label="SIM-MBG Admin Dashboard">
                <img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG" width="52" height="48">
                <span class="admin-brand-title">SIM-MBG</span>
            </a>

            <!-- Navigasi Utama -->
            <nav class="nav-links" id="adminMainNav">
                <ul class="admin-nav-menu">
                    <li><a href="{{ route('admin.dashboard') }}" class="admin-nav-btn active">Dashboard</a></li>
                    <li><a href="{{ route('admin.students') }}" class="admin-nav-btn">Siswa</a></li>
                    <li><a href="{{ route('admin.schools') }}" class="admin-nav-btn">Sekolah</a></li>
                    <li><a href="{{ route('admin.sppg') }}" class="admin-nav-btn">SPPG</a></li>
                    <li><a href="{{ route('admin.users') }}" class="admin-nav-btn">Pengguna</a></li>
                </ul>
            </nav>

            <!-- Profil Admin Ringkas & Logout -->
            <div class="nav-actions">
                <div class="admin-user-chip">
                    <div class="admin-user-avatar">{{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username ?? 'A', 0, 1)) }}</div>
                    <div class="admin-user-meta">
                        <span class="admin-user-name admin-display-name">{{ auth()->user()->full_name ?? auth()->user()->username ?? 'Administrator' }}</span>
                        <span class="admin-user-role">{{ ucfirst(auth()->user()->role ?? 'Administrator') }}</span>
                    </div>
                </div>

                <a href="{{ route('landing') }}" class="btn btn-secondary btn-sm" title="Beranda">
                    <span class="material-symbols-outlined" style="font-size: 18px;">home</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: contents;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" id="adminLogoutBtn" title="Keluar dari Admin Panel">
                        <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                    </button>
                </form>

                
                <button type="button" class="mobile-nav-toggle" aria-controls="adminMainNav" aria-expanded="false" aria-label="Buka Menu Navigasi">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    
    <main class="admin-main-content">
        <div class="admin-container">
            
            <section class="admin-page-header">
                <div class="admin-page-title-group">
                    <h1>Dashboard Administrator</h1>
                    <div class="admin-page-subtitle">
                        <span class="material-symbols-outlined" style="color: var(--color-primary); font-size: 18px;">calendar_month</span>
                        <span>Tahun Ajaran 2026/2027 &bull; Wilayah Layanan Bali &bull; Zona Waktu WITA</span>
                    </div>
                </div>
            </section>

            
            <section class="admin-banner-notice">
                <div class="notice-content">
                    <div class="notice-icon-box">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <div class="notice-text">
                        <h4>Pembaruan Data Periodik Otomatis</h4>
                        <p>Periode Data Aktif: Semester Ganjil 2026/2027 &bull; Seluruh data MBG disinkronkan otomatis setiap hari kerja.</p>
                    </div>
                </div>
                <div class="notice-badge">
                    <span class="notice-badge-label">Status Sistem</span>
                    <span class="notice-badge-val">Sinkron Otomatis</span>
                </div>
            </section>

            
            <section class="kpi-grid" aria-label="Ringkasan Statistik MBG">
                
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Total Siswa</span>
                            <div class="kpi-value" id="kpiTotalSiswa">{{ number_format($kpi['total_students'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-up">&uarr; 12%</span>
                        <span class="kpi-footer-text">dari bulan lalu</span>
                    </div>
                </article>

                
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">domain</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Total Sekolah Terdaftar</span>
                            <div class="kpi-value" id="kpiTotalSekolah">{{ number_format($kpi['total_schools'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-up">&uarr; 3%</span>
                        <span class="kpi-footer-text">dari bulan lalu</span>
                    </div>
                </article>

                
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">restaurant</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Total SPPG</span>
                            <div class="kpi-value" id="kpiTotalSppg">{{ number_format($kpi['total_sppg'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-up">&uarr; 1</span>
                        <span class="kpi-footer-text">dari bulan lalu</span>
                    </div>
                </article>

                
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">person_check</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Total Admin &amp; Staf</span>
                            <div class="kpi-value" id="kpiTotalStaf">{{ number_format($kpi['total_staff'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-up">&uarr; 4</span>
                        <span class="kpi-footer-text">dari bulan lalu</span>
                    </div>
                </article>
            </section>

            
            <div class="dashboard-admin-grid">
                
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">
                            <span class="material-symbols-outlined" style="color: var(--color-primary);">domain</span>
                            <span>Data Ringkas Sekolah Binaan</span>
                        </h2>
                        <a href="{{ route('admin.schools') }}" class="btn btn-outline btn-sm">
                            <span>Lihat Semua</span>
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th>NAMA SEKOLAH</th>
                                    <th>KAB/KOTA</th>
                                    <th>SISWA</th>
                                    <th>SPPG MITRA</th>
                                    <th>STATUS</th>
                                    <th class="col-actions">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="dashboardSchoolsTableBody">
                                @forelse($schools as $school)
                                    @php
                                        $statusLower = strtolower($school->status ?? 'active');
                                        $isActive = in_array($statusLower, ['active', 'aktif'], true);
                                    @endphp
                                    <tr>
                                        <td class="col-no">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="cell-title">{{ $school->name }}</div>
                                            <div class="cell-sub">NPSN: {{ $school->npsn }}</div>
                                        </td>
                                        <td>{{ $school->city ?? $school->district ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ number_format($school->students_count, 0, ',', '.') }} Siswa</span>
                                        </td>
                                        <td>
                                            <div class="cell-title" style="font-size: 13px;">{{ $school->sppgUnit?->name ?? 'Belum Ditugaskan' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge-status {{ $isActive ? 'badge-status-active' : 'badge-status-process' }}">
                                                {{ $isActive ? 'Aktif' : ucfirst($school->status ?? 'Proses') }}
                                            </span>
                                        </td>
                                        <td class="col-actions">
                                            <a href="{{ route('admin.schools') }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px;">
                                                <span>Detail</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--color-muted);">
                                            Belum ada data sekolah binaan terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">
                            <span class="material-symbols-outlined" style="color: var(--color-accent);">history</span>
                            <span>Aktivitas Sistem Terbaru</span>
                        </h2>
                        <span class="badge badge-accent">Live Feed</span>
                    </div>
                    <ul class="activity-list" id="dashboardActivityList">
                        @forelse($activityLogs as $log)
                            @php
                                $statusLower = strtolower($log->status ?? '');
                                $icon = 'notifications';
                                $iconClass = 'kpi-icon-teal';

                                if (str_contains($statusLower, 'selesai') || str_contains($statusLower, 'delivered') || str_contains($statusLower, 'lulus')) {
                                    $icon = 'check_circle';
                                    $iconClass = 'kpi-icon-emerald';
                                } elseif (str_contains($statusLower, 'kirim') || str_contains($statusLower, 'transit')) {
                                    $icon = 'local_shipping';
                                    $iconClass = 'kpi-icon-teal';
                                } elseif (str_contains($statusLower, 'audit')) {
                                    $icon = 'verified';
                                    $iconClass = 'kpi-icon-emerald';
                                } elseif (str_contains($statusLower, 'pelihara') || str_contains($statusLower, 'maintenance')) {
                                    $icon = 'build';
                                    $iconClass = 'kpi-icon-accent';
                                } elseif (str_contains($statusLower, 'verifikasi') || str_contains($statusLower, 'verified')) {
                                    $icon = 'health_and_safety';
                                    $iconClass = 'kpi-icon-amber';
                                }
                            @endphp
                            <li class="activity-item">
                                <div class="activity-icon-wrapper {{ $iconClass }}">
                                    <span class="material-symbols-outlined">{{ $icon }}</span>
                                </div>
                                <div class="activity-body">
                                    <h4 class="activity-title">{{ $log->title }}</h4>
                                    @if($log->description)
                                        <p class="activity-desc">{{ $log->description }}</p>
                                    @endif
                                    <span class="activity-time">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">schedule</span>
                                        <span>
                                            @if($log->created_at)
                                                {{ $log->created_at->diffForHumans() }}
                                            @else
                                                Baru saja
                                            @endif
                                            @if($log->sppgUnit)
                                                &bull; {{ $log->sppgUnit->code }}
                                            @endif
                                        </span>
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li style="padding: 24px; text-align: center; color: var(--color-muted);">
                                Belum ada aktivitas tercatat pada sistem.
                            </li>
                        @endforelse
                    </ul>
                </section>
            </div>

            
            <section class="admin-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border-left: 4px solid var(--color-primary); padding: 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--color-heading); margin-bottom: 4px;">Informasi Pengelolaan SIM-MBG</h3>
                    <p style="font-size: 14px; color: var(--color-muted); margin: 0;">Pastikan data sekolah dan kapasitas SPPG selalu diperbarui sebelum cut-off pemilihan menu harian oleh siswa setiap pukul 20:00 WITA.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('admin.sppg') }}" class="btn btn-secondary btn-sm">
                        <span class="material-symbols-outlined" style="font-size: 18px;">soup_kitchen</span>
                        <span>Cek Unit SPPG</span>
                    </a>
                    <a href="{{ route('admin.students') }}" class="btn btn-primary btn-sm">
                        <span class="material-symbols-outlined" style="font-size: 18px;">playlist_add_check</span>
                        <span>Verifikasi Siswa</span>
                    </a>
                </div>
            </section>
        </div>
    </main>

    
<footer class="main-footer">
    <div class="admin-container">
        <div class="admin-footer-content">
            <div>
                © 2026 SIM-MBG — Sistem Informasi
                Manajemen Makan Bergizi Gratis.
            </div>

            <div>
                Kementerian Pendidikan &amp; Badan Gizi Nasional
            </div>
        </div>
    </div>
</footer>

    
    <script src="{{ asset('js/pages/admin.js') }}"></script>
</body>
</html>
