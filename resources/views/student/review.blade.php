<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Saya & Alergi — SIM-MBG Siswa</title>
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
<body id="profilePage">
    
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
                    <li><a href="menu.html" class="student-nav-btn">Pilih Menu</a></li>
                    <li><a href="history.html" class="student-nav-btn">Riwayat & Ulasan</a></li>
                    <li><a href="review.html" class="student-nav-btn active">Profil Saya</a></li>
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
            <div class="profile-container-grid">
                
                <aside class="profile-card-sidebar">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-big-avatar" id="profileAvatarDisplay">A</div>
                        <button type="button" class="btn-change-avatar" id="triggerAvatarUpload" title="Ubah Foto Profil">
                            <span class="material-symbols-outlined">photo_camera</span>
                        </button>
                    </div>

                    <input type="file" id="avatarFileInput" accept="image/*" style="display: none;" aria-label="Unggah Foto Profil">
                    <button type="button" class="btn-upload-text" id="btnEditProfileTrigger" title="Ubah Nama Profil / Username">
                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                        <span>Ubah Profil</span>
                    </button>

                    <h2 class="profile-user-title student-display-name">Alya MBG</h2>
                    <p class="profile-school-subtitle student-display-school">SMA Negeri 1 Denpasar</p>

                    <div class="profile-sidebar-meta-list">
                        <div class="profile-meta-row">
                            <span class="profile-meta-lbl">NISN / NIS</span>
                            <span class="profile-meta-val student-display-nis">12345678</span>
                        </div>
                        <div class="profile-meta-row">
                            <span class="profile-meta-lbl">Kelas</span>
                            <span class="profile-meta-val">XI IPA 1</span>
                        </div>
                        <div class="profile-meta-row">
                            <span class="profile-meta-lbl">Tingkat Sekolah</span>
                            <span class="profile-meta-val">SMA / MA</span>
                        </div>
                        <div class="profile-meta-row">
                            <span class="profile-meta-lbl">Unit Dapur SPPG</span>
                            <span class="profile-meta-val">SPPG Denpasar Selatan</span>
                        </div>
                        <div class="profile-meta-row">
                            <span class="profile-meta-lbl">Status Penerima</span>
                            <span class="profile-meta-val" style="color: var(--color-primary);">? Terdaftar Aktif</span>
                        </div>
                    </div>
                </aside>

                
                <section class="profile-content-card">
                    <div class="profile-section-heading">
                        <h2>Alergi & Pantangan Makanan Siswa</h2>
                        <p>Data alergen ini digunakan secara otomatis oleh sistem saat Anda memilih menu dan diteruskan langsung ke dapur SPPG untuk pencegahan kontaminasi silang.</p>
                    </div>

                    <form id="profileAllergyForm" novalidate>
                        
                        <div class="allergy-checkbox-grid">
                            
                            <label class="allergy-checkbox-item">
                                <input type="checkbox" value="Kacang" class="allergy-check-input">
                                <div class="allergy-item-info">
                                    <h4>Alergi Kacang (Peanuts / Tree Nuts)</h4>
                                    <p>Kacang tanah, mede, almond, atau selai kacang</p>
                                </div>
                            </label>

                            
                            <label class="allergy-checkbox-item">
                                <input type="checkbox" value="Seafood" class="allergy-check-input">
                                <div class="allergy-item-info">
                                    <h4>Alergi Makanan Laut (Seafood)</h4>
                                    <p>Udang, kepiting, cumi-cumi, atau kerang laut</p>
                                </div>
                            </label>

                            
                            <label class="allergy-checkbox-item">
                                <input type="checkbox" value="Telur" class="allergy-check-input">
                                <div class="allergy-item-info">
                                    <h4>Alergi Telur</h4>
                                    <p>Telur ayam, telur bebek, atau produk olahan telur</p>
                                </div>
                            </label>

                            
                            <label class="allergy-checkbox-item">
                                <input type="checkbox" value="Laktosa" class="allergy-check-input">
                                <div class="allergy-item-info">
                                    <h4>Intoleransi Laktosa (Susu Sapi)</h4>
                                    <p>Susu segar, mentega, keju olahan hewani</p>
                                </div>
                            </label>

                            
                            <label class="allergy-checkbox-item" style="grid-column: span 2;">
                                <input type="checkbox" value="Vegetarian" class="allergy-check-input">
                                <div class="allergy-item-info">
                                    <h4>Diet Khusus Vegetarian / Bebas Daging</h4>
                                    <p>Hanya mengonsumsi sayuran, jamur, tempe, tahu, dan protein nabati</p>
                                </div>
                            </label>
                        </div>

                        
                        <div class="form-group-medical">
                            <label for="medicalNotes">Catatan Tambahan Medis / Riwayat Khusus:</label>
                            <textarea id="medicalNotes" class="form-textarea" rows="3" placeholder="Tuliskan catatan medis jika ada alergi obat atau riwayat maag akut..."></textarea>
                        </div>

                        
                        <div class="profile-form-actions">
                            <button type="button" class="btn btn-secondary" id="cancelProfileBtn">Batalkan Perubahan</button>
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                                <span>Perbarui Data Alergi</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    
    <div class="student-modal-backdrop" id="editUsernameModal" role="dialog" aria-modal="true" aria-labelledby="modalUsernameTitle">
        <div class="student-modal-card">
            <div class="student-modal-header">
                <h3 id="modalUsernameTitle">Ubah Nama Profil Siswa</h3>
                <button type="button" class="modal-close-btn" id="closeUsernameModalBtn" aria-label="Tutup Modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="editUsernameForm">
                <div class="student-modal-body">
                    <div class="form-group" style="margin-bottom: var(--space-16);">
                        <label for="newUsernameInput" style="display: block; font-size: 14px; font-weight: 700; color: #0B1C30; margin-bottom: 6px;">Nama Lengkap / Username:</label>
                        <input type="text" id="newUsernameInput" class="form-textarea" style="min-height: 44px; height: 44px; padding: 8px 12px;" required placeholder="Masukkan nama profil baru...">
                    </div>
                    <p style="font-size: 12px; color: #62748E; margin: 0;">Nama ini akan tampil di seluruh sapaan dashboard, ulasan makanan, dan navbar siswa.</p>
                </div>

                <div class="student-modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelUsernameModalBtn">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveUsernameBtn">Simpan Nama</button>
                </div>
            </form>
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

    
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pages/siswa.js') }}"></script>
</body>
</html>
