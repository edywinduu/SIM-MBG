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
                            @forelse ($allergies ?? [] as $allergy)
                                <span class="allergy-tag" style="background-color: #FEE2E2; color: #991B1B; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">{{ $allergy }}</span>
                            @empty
                                <span class="allergy-tag" style="background-color: #DCFCE7; color: #166534; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">Aman (Tidak Ada Alergi)</span>
                            @endforelse
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
                        <p>Batas akhir konfirmasi pilihan untuk {{ $targetDateFormatted ?? now()->translatedFormat('l, d F Y') }} adalah pukul <strong>{{ $cutoffTime ?? '20:00 WITA' }}</strong></p>
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

            
            @php
                $baseDate = isset($targetDate) ? $targetDate->copy() : ($schedule?->date ? \Carbon\Carbon::parse($schedule->date) : today());
            @endphp

            <nav class="day-tabs-nav" aria-label="Pilih Hari Menu">
                @for ($offset = -2; $offset <= 2; $offset++)
                    @php
                        $tabDate = $baseDate->copy()->addDays($offset);
                        $dayName = $tabDate->translatedFormat('l');
                        $dateFormatted = $tabDate->translatedFormat('j M Y');
                        $dataDay = strtolower($tabDate->format('l'));
                        $isPast = $offset < 0;
                        $isToday = $offset === 0;
                    @endphp

                    <button
                        type="button"
                        class="day-tab-btn {{ $isPast ? 'past' : ($isToday ? 'active' : '') }}"
                        data-day="{{ $dataDay }}"
                        {{ $isPast ? 'disabled aria-disabled="true" title="Hari telah berakhir"' : '' }}
                    >
                        <span class="day-name">{{ $dayName }}</span>
                        <span class="day-date">{{ $dateFormatted }}</span>
                        @if ($isPast)
                            <span class="day-state-tag past">Berakhir</span>
                        @elseif ($isToday)
                            <span class="day-state-tag open">Hari Ini (Aktif)</span>
                        @else
                            <span class="day-state-tag open">Buka</span>
                        @endif
                    </button>
                @endfor
            </nav>

            
            <div class="menu-grid-header">
                <div>
                    <h2 class="section-title-sm" style="font-size: 22px;">Daftar Pilihan Menu: {{ $targetDateFormatted ?? now()->translatedFormat('l, d F Y') }}</h2>
                    <p class="text-muted" style="font-size: 13px; margin: 0;">Tersedia {{ count($menus ?? []) }} paket standar gizi seimbang yang disiapkan oleh {{ $sppgName ?? 'SPPG Penyedia' }}</p>
                </div>
                <span class="badge badge-primary">Tersedia {{ count($menus ?? []) }} Menu</span>
            </div>

            
            <div class="menu-cards-grid" id="menuCardsGrid">
                @foreach ($menus ?? [] as $menu)
                    @php
                        $packageCode = 'Paket ' . chr(65 + $loop->index);
                        $isSelected = ($menu->id == ($selectedMenuId ?? null));
                    @endphp
                    <div class="menu-package-card {{ $isSelected ? 'selected' : '' }}" data-menu-id="{{ $menu->id }}">
                        @if ($isSelected)
                            <div class="card-selected-ribbon">✓ Terpilih</div>
                        @endif

                        <div class="package-img-holder">
                            <img src="{{ $menu->image }}" alt="{{ $menu->name }}" loading="lazy">
                        </div>

                        <div class="package-card-body">
                            <div class="package-title-row">
                                <h4 class="package-title">{{ $packageCode }}</h4>
                                <span class="package-calories">{{ $menu->calories }} kkal</span>
                            </div>

                            <p class="package-desc">{{ $menu->name }}</p>

                            <div class="package-macros-row">
                                <span class="macro-pill protein">{{ $menu->protein }}g Protein</span>
                                <span class="macro-pill fiber">{{ $menu->fiber }}g Serat</span>
                                <span class="macro-pill">{{ $menu->fat }}g Lemak</span>
                            </div>

                            <div class="plate-details-box">
                                <h5>Komposisi Piring:</h5>
                                <ul class="plate-items-list">
                                    @foreach ($menu->items as $it)
                                        <li>{{ $it }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="card-allergy-safe">
                                <span class="material-symbols-outlined">verified_user</span>
                                <p>Standar Higienis & Gizi Terverifikasi</p>
                            </div>

                            <button
                                type="button"
                                class="btn {{ $isSelected ? 'btn-secondary' : 'btn-primary' }} package-btn-action select-menu-btn"
                                data-menu-id="{{ $menu->id }}"
                                data-schedule-id="{{ $schedule?->id ?? '' }}"
                                onclick="chooseMenu({{ $menu->id }}, {{ $schedule?->id ?? 'null' }}, '{{ $packageCode }}', this)"
                                {{ $isSelected ? 'disabled' : '' }}
                            >
                                {{ $isSelected ? 'Menu Terpilih' : 'Pilih Paket Ini' }}
                            </button>
                        </div>
                    </div>
                @endforeach
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
    <script>
        async function chooseMenu(menuId, scheduleId, packageCode, btnElement) {
            if (!btnElement || btnElement.disabled) {
                return;
            }

            const originalText = btnElement.textContent;
            btnElement.disabled = true;
            btnElement.textContent = 'Menyimpan...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch("{{ route('student.menu.select') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        menu_id: menuId,
                        schedule_id: scheduleId
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Reset status terpilih pada kartu menu lainnya
                    document.querySelectorAll('.menu-package-card').forEach(card => {
                        card.classList.remove('selected');
                        const ribbon = card.querySelector('.card-selected-ribbon');
                        if (ribbon) {
                            ribbon.remove();
                        }
                        const actionBtn = card.querySelector('.select-menu-btn');
                        if (actionBtn) {
                            actionBtn.className = 'btn btn-primary package-btn-action select-menu-btn';
                            actionBtn.disabled = false;
                            actionBtn.textContent = 'Pilih Paket Ini';
                        }
                    });

                    // Set status terpilih pada kartu yang diklik
                    const currentCard = btnElement.closest('.menu-package-card');
                    if (currentCard) {
                        currentCard.classList.add('selected');
                        const ribbon = document.createElement('div');
                        ribbon.className = 'card-selected-ribbon';
                        ribbon.textContent = '✓ Terpilih';
                        currentCard.prepend(ribbon);
                    }

                    btnElement.className = 'btn btn-secondary package-btn-action select-menu-btn';
                    btnElement.disabled = true;
                    btnElement.textContent = 'Menu Terpilih';

                    const toastType = data.has_allergy_warning ? 'warning' : 'success';
                    if (typeof showStudentToast === 'function') {
                        showStudentToast(data.message, toastType);
                    }
                } else {
                    btnElement.disabled = false;
                    btnElement.textContent = originalText;
                    const errorMsg = data.message || 'Gagal memilih menu. Silakan coba lagi.';
                    if (typeof showStudentToast === 'function') {
                        showStudentToast(errorMsg, 'warning');
                    } else {
                        alert(errorMsg);
                    }
                }
            } catch (error) {
                console.error('Error saat memilih menu:', error);
                btnElement.disabled = false;
                btnElement.textContent = originalText;
                if (typeof showStudentToast === 'function') {
                    showStudentToast('Terjadi kesalahan jaringan saat memilih menu.', 'warning');
                } else {
                    alert('Terjadi kesalahan koneksi.');
                }
            }
        }
    </script>
</body>
</html>
