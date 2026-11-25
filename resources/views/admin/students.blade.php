<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Siswa — SIM-MBG Admin</title>
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
    <link rel="stylesheet" href="{{ asset('css/pages/admin.css') }}?v={{ time() }}">
    <style>
        .admin-modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 43, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: var(--space-20);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .admin-modal-overlay.open {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            display: flex !important;
        }
        .admin-modal-overlay.open .admin-modal {
            transform: translateY(0) scale(1) !important;
        }
    </style>
</head>
<body class="admin-body" id="adminStudentsPage">
    
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
                    <li><a href="{{ route('admin.dashboard') }}" class="admin-nav-btn">Dashboard</a></li>
                    <li><a href="{{ route('admin.students') }}" class="admin-nav-btn active">Siswa</a></li>
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
                    <h1>Manajemen Data Siswa</h1>
                    <div class="admin-page-subtitle">
                        Kelola data siswa penerima MBG, pantau jenis menu, serta verifikasi catatan alergi medis.
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="btn btn-primary" id="btnAddStudent" onclick="openStudentModal()">
                        <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
                        <span>Tambah Siswa</span>
                    </button>
                </div>
            </section>

            @if(session('success'))
                <div style="background-color: #ECFDF5; border-left: 4px solid #10B981; color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-sm);">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #10B981;">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div style="background-color: #FEF2F2; border-left: 4px solid #EF4444; color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-sm);">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #EF4444;">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            
            <section class="kpi-grid-3" aria-label="Rekapitulasi Siswa">
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Total Siswa Terdaftar</span>
                            <div class="kpi-value" id="recapTotalSiswa">{{ number_format($recap['total'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-footer-text">Seluruh sekolah binaan</span>
                    </div>
                </article>

                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">restaurant</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Penerima Menu Reguler</span>
                            <div class="kpi-value" id="recapMenuReguler">{{ number_format($recap['regular'], 0, ',', '.') }} ({{ $recap['regular_percent'] }}%)</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-up">Bebas Alergen</span>
                    </div>
                </article>

                <article class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-squircle">
                            <span class="material-symbols-outlined">health_and_safety</span>
                        </div>
                        <div class="kpi-meta">
                            <span class="kpi-label">Penerima Menu Khusus (Diet/Alergi)</span>
                            <div class="kpi-value" id="recapMenuKhusus">{{ number_format($recap['special'], 0, ',', '.') }} ({{ $recap['special_percent'] }}%)</div>
                        </div>
                    </div>
                    <div class="kpi-divider"></div>
                    <div class="kpi-footer">
                        <span class="kpi-badge kpi-badge-info">Perlu Perhatian Khusus</span>
                    </div>
                </article>
            </section>

            
            <section class="admin-card">
                
                <div class="admin-toolbar">
                    <div class="toolbar-filters">
                        
                        <div class="search-box">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input
                                type="text"
                                class="search-input"
                                id="studentSearchInput"
                                value="{{ $search ?? '' }}"
                                placeholder="Cari nama siswa, NISN, atau kelas..."
                            >
                        </div>

                        
                        <select class="filter-select" id="studentSchoolFilter" aria-label="Filter berdasarkan Sekolah">
                            <option value="">Semua Sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ (string)($schoolFilter ?? '') === (string)$school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>

                        
                        <select class="filter-select" id="studentMenuFilter" aria-label="Filter berdasarkan Jenis Menu">
                            <option value="">Semua Jenis Menu</option>
                            <option value="Reguler" {{ ($menuFilter ?? '') === 'Reguler' ? 'selected' : '' }}>Menu Reguler</option>
                            <option value="Menu Khusus" {{ ($menuFilter ?? '') === 'Menu Khusus' ? 'selected' : '' }}>Menu Khusus (Alergi)</option>
                        </select>
                    </div>
                </div>

                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="col-no">NO</th>
                                <th>NISN</th>
                                <th>NAMA SISWA</th>
                                <th>SEKOLAH</th>
                                <th>KELAS</th>
                                <th>JENIS MENU</th>
                                <th>CATATAN ALERGI</th>
                                <th>STATUS</th>
                                <th class="col-actions">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            @forelse($students as $st)
                                @php
                                    $hasAllergy = $st->allergies->isNotEmpty();
                                    $allergySummary = $hasAllergy ? $st->allergies->pluck('name')->join(', ') : '';
                                    $statusLower = strtolower($st->recipient_status ?? 'active');
                                    $isActive = in_array($statusLower, ['active', 'aktif'], true);
                                @endphp
                                <tr data-student-id="{{ $st->id }}">
                                    <td class="col-no">{{ $students->firstItem() + $loop->index }}</td>
                                    <td style="font-family: monospace; font-weight: 600;">{{ $st->nisn }}</td>
                                    <td>
                                        <div class="cell-title">{{ $st->name }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-title" style="font-size: 13px;">{{ $st->school?->name ?? 'Sekolah Tidak Ditemukan' }}</div>
                                    </td>
                                    <td><strong>{{ $st->class_name }}</strong></td>
                                    <td>
                                        @if($hasAllergy)
                                            <span class="badge badge-accent">Menu Khusus</span>
                                        @else
                                            <span class="badge badge-primary">Reguler</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasAllergy)
                                            <span class="badge-allergy">
                                                <span class="material-symbols-outlined" style="font-size: 13px;">warning</span>
                                                {{ $allergySummary }}
                                            </span>
                                        @else
                                            <span class="badge-allergy-safe">Tidak ada alergi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $isActive ? 'badge-status-active' : 'badge-status-inactive' }}">
                                            {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="col-actions">
                                        <div class="btn-action-group">
                                            <button type="button" class="btn-icon-action" data-edit-id="{{ $st->id }}" title="Edit Siswa">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </button>
                                            <button type="button" class="btn-icon-action delete" data-delete-id="{{ $st->id }}" title="Hapus Siswa">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--color-muted);">
                                        <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px;">search_off</span>
                                        Tidak ada data siswa yang cocok dengan filter atau pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                <div class="admin-pagination">
                    <div class="pagination-info" id="studentPaginationInfo">
                        Menampilkan {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} siswa
                    </div>
                    <div class="pagination-controls" id="studentPaginationControls" data-current-page="{{ $students->currentPage() }}" data-last-page="{{ $students->lastPage() }}">
                        <button type="button" class="btn-page" {{ $students->onFirstPage() ? 'disabled' : '' }} id="btnPrevPage">
                            <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span>
                        </button>
                        @for($p = 1; $p <= $students->lastPage(); $p++)
                            <button type="button" class="btn-page {{ $p === $students->currentPage() ? 'active' : '' }}" data-page="{{ $p }}">{{ $p }}</button>
                        @endfor
                        <button type="button" class="btn-page" {{ !$students->hasMorePages() ? 'disabled' : '' }} id="btnNextPage">
                            <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    
    <div class="admin-modal-overlay" id="modalStudent" role="dialog" aria-modal="true" aria-labelledby="modalStudentTitle">
        <div class="admin-modal">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title" id="modalStudentTitle">Tambah Siswa Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('modalStudent')" aria-label="Tutup Modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="studentForm" method="POST" action="{{ route('admin.students.store') }}">
                @csrf
                <input type="hidden" id="studentFormId" name="student_id">
                <div class="admin-modal-body">
                    <div class="form-grid-2">
                        <div class="admin-form-group">
                            <label for="studentFormNisn" class="admin-form-label">NISN <span class="required">*</span></label>
                            <input type="text" id="studentFormNisn" name="nisn" class="admin-form-input" placeholder="Contoh: 0081234567" required maxlength="12">
                            <span class="admin-form-help">Nomor Induk Siswa Nasional (10-12 digit)</span>
                        </div>
                        <div class="admin-form-group">
                            <label for="studentFormName" class="admin-form-label">Nama Lengkap Siswa <span class="required">*</span></label>
                            <input type="text" id="studentFormName" name="name" class="admin-form-input" placeholder="Masukkan nama siswa" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="admin-form-group">
                            <label for="studentFormSchool" class="admin-form-label">Sekolah <span class="required">*</span></label>
                            <select id="studentFormSchool" name="school_id" class="admin-form-select" required>
                                <option value="" disabled selected>Pilih Sekolah</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="studentFormClass" class="admin-form-label">Kelas <span class="required">*</span></label>
                            <input type="text" id="studentFormClass" name="class_name" class="admin-form-input" placeholder="Contoh: XI IPA 1" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="admin-form-group">
                            <label for="studentFormMenuType" class="admin-form-label">Jenis Paket Menu <span class="required">*</span></label>
                            <select id="studentFormMenuType" name="menu_type" class="admin-form-select" required>
                                <option value="Reguler">Reguler</option>
                                <option value="Menu Khusus">Menu Khusus (Diet/Alergen)</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="studentFormStatus" class="admin-form-label">Status Keaktifan <span class="required">*</span></label>
                            <select id="studentFormStatus" name="recipient_status" class="admin-form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="studentFormAllergies" class="admin-form-label">Catatan Alergi / Pantangan Medis</label>
                        <input type="text" id="studentFormAllergies" name="allergies" class="admin-form-input" placeholder="Contoh: Kacang, Seafood, Telur (kosongkan jika tidak ada)">
                        <span class="admin-form-help">Catatan ini akan otomatis ditampilkan pada peringatan menu harian siswa.</span>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalStudent')">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        <span>Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="admin-modal-overlay" id="modalDeleteStudent" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="admin-modal" style="max-width: 440px;">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title" id="modalDeleteTitle">Konfirmasi Hapus Siswa</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('modalDeleteStudent')" aria-label="Tutup">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="admin-modal-body" style="text-align: center; padding: 24px;">
                <span class="material-symbols-outlined" style="color: var(--color-danger); font-size: 48px; margin-bottom: 12px;">warning</span>
                <p style="font-size: 15px; font-weight: 700; color: var(--color-heading); margin-bottom: 6px;">Apakah Anda yakin ingin menghapus siswa ini?</p>
                <p style="font-size: 13px; color: var(--color-muted); margin-bottom: 0;" id="deleteStudentName">-</p>
                <p style="font-size: 12px; color: var(--color-danger); margin-top: 10px;">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="admin-modal-footer" style="justify-content: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalDeleteStudent')">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" style="background-color: var(--color-danger); border-color: var(--color-danger);" id="btnConfirmDeleteStudent">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                    <span>Ya, Hapus</span>
                </button>
            </div>
        </div>
    </div>

    
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

    <script>
        function openStudentModal() {
            var modal = document.getElementById('modalStudent');
            if (modal) {
                var title = document.getElementById('modalStudentTitle');
                if (title) title.textContent = 'Tambah Siswa Baru';
                var form = document.getElementById('studentForm');
                if (form) form.reset();
                var idInput = document.getElementById('studentFormId');
                if (idInput) idInput.value = '';
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(id) {
            var modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        window.openStudentModal = openStudentModal;
        window.closeModal = closeModal;
    </script>
    <script src="{{ asset('js/pages/admin.js') }}?v={{ time() }}"></script>
</body>
</html>
