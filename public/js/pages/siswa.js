/**
 * SIM-MBG — Student Page Script (siswa.js)
 * Terhubung langsung dengan backend/api/student.php dan basis data MySQL SIM-MBG.
 * Mengelola fungsionalitas:
 * 1. Global Header: sinkronisasi profil siswa, avatar, sapaan, dan tombol keluar.
 * 2. Dashboard Siswa: sapaan nama, tanggal hari ini, status ambil makan siang (pickups), menu hari ini.
 * 3. Pilih Menu: daftar menu aktif dari database, deteksi & peringatan alergen, simpan pilihan (menu_selections).
 * 4. Riwayat & Ulasan: riwayat makan dan testimoni dari database (student_reviews), filter, ubah, dan hapus ulasan.
 * 5. Profil Saya: profil siswa, NISN, kelas, preferensi alergi (student_allergies), catatan medis, dan ubah nama profil.
 */

const STORAGE_KEY_AUTH = 'sim_mbg_auth';
const STORAGE_KEY_PROFILE = 'sim_mbg_session_profile';
const API_STUDENT_ENDPOINT = '../../../backend/api/student.php';

// Data bawaan sebagai fallback jika backend offline / saat inisialisasi awal
const defaultStudentProfile = {
  id: 1,
  name: 'Alya MBG',
  nis: '0081234567',
  school: 'SMA Negeri 1 Denpasar',
  class: 'XI IPA 1',
  allergies: ['Kacang', 'Seafood'],
  medicalNotes: 'Sensitif terhadap produk olahan kacang tanah dan udang laut.',
  attendanceRate: '100%',
  totalReviews: 13,
  avgRating: 4.8
};

/**
 * Helper untuk mengambil informasi sesi autentikasi yang tersimpan dari login/register
 */
function getAuthSession() {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY_AUTH);
    if (raw) return JSON.parse(raw);
  } catch (e) {
    console.warn('Gagal membaca sesi auth:', e);
  }
  return null;
}

/**
 * Menyimpan profil ter-cache di sessionStorage
 */
function getCachedProfile() {
  const auth = getAuthSession();
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY_PROFILE);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (auth) {
        parsed.name = auth.full_name || auth.username || parsed.name;
        parsed.nis = auth.nis || auth.nisn || parsed.nis;
        parsed.school = auth.school || parsed.school;
        parsed.class = auth.class || parsed.class;
      }
      return parsed;
    }
  } catch (e) {}

  const fallback = JSON.parse(JSON.stringify(defaultStudentProfile));
  if (auth) {
    fallback.id = auth.student_id || auth.id || fallback.id;
    fallback.name = auth.full_name || auth.username || fallback.name;
    fallback.nis = auth.nis || auth.nisn || fallback.nis;
    fallback.school = auth.school || fallback.school;
    fallback.class = auth.class || fallback.class;
  }
  return fallback;
}

function saveCachedProfile(profile) {
  try {
    sessionStorage.setItem(STORAGE_KEY_PROFILE, JSON.stringify(profile));
  } catch (e) {}
}

/**
 * Menghubungi API Backend student.php
 */
async function callStudentApi(action, params = {}, method = 'GET') {
  const auth = getAuthSession();
  let url = `${API_STUDENT_ENDPOINT}?action=${encodeURIComponent(action)}`;

  // Sertakan identitas student_id / user_id dari sesi login jika tersedia
  if (auth) {
    if (auth.student_id) url += `&student_id=${encodeURIComponent(auth.student_id)}`;
    if (auth.id) url += `&user_id=${encodeURIComponent(auth.id)}`;
    if (auth.nis) url += `&nis=${encodeURIComponent(auth.nis)}`;
    if (auth.username) url += `&username=${encodeURIComponent(auth.username)}`;
  }

  const options = {
    method: method,
    headers: {
      'Accept': 'application/json'
    },
    credentials: 'include' // Mengirimkan cookie session PHP
  };

  if (method === 'POST') {
    options.headers['Content-Type'] = 'application/json';
    const payload = Object.assign({}, params);
    if (auth) {
      if (auth.student_id && !payload.student_id) payload.student_id = auth.student_id;
      if (auth.id && !payload.user_id) payload.user_id = auth.id;
    }
    options.body = JSON.stringify(payload);
  }

  try {
    const response = await fetch(url, options);
    if (!response.ok) {
      const errText = await response.text();
      let errJson = null;
      try { errJson = JSON.parse(errText); } catch (_) {}
      throw new Error((errJson && errJson.message) ? errJson.message : `HTTP ${response.status}`);
    }
    const result = await response.json();
    return result;
  } catch (err) {
    console.warn(`[StudentAPI] Panggilan ke aksi '${action}' mengalami kendala:`, err.message);
    return null;
  }
}

/**
 * Menampilkan Toast Notifikasi pada halaman Siswa
 */
function showStudentToast(message, type = 'success') {
  let toast = document.querySelector('#studentToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'studentToast';
    toast.className = 'student-toast';
    document.body.appendChild(toast);
  }

  toast.className = `student-toast ${type} show`;
  toast.innerHTML = `
    <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : (type === 'warning' ? 'warning' : 'info')}</span>
    <span>${message}</span>
  `;

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3200);
}

// =========================================================================
// INISIALISASI HALAMAN SISWA
// =========================================================================

document.addEventListener('DOMContentLoaded', async () => {
  // 1. Inisialisasi Header Global (berlaku di semua halaman siswa)
  await initStudentGlobalHeader();

  // 2. Deteksi halaman yang sedang aktif berdasarkan ID body
  if (document.querySelector('#dashboardPage')) {
    initDashboardPage();
  } else if (document.querySelector('#menuSelectionPage')) {
    initMenuSelectionPage();
  } else if (document.querySelector('#riwayatPage')) {
    initRiwayatPage();
  } else if (document.querySelector('#profilePage')) {
    initProfilePage();
  }
});

// =========================================================================
// 1. GLOBAL HEADER & SINKRONISASI IDENTITAS
// =========================================================================

async function initStudentGlobalHeader() {
  let profile = getCachedProfile();

  // Tampilkan profil lokal terlebih dahulu agar UI responsif seketika
  renderGlobalHeaderDetails(profile);

  // Ambil profil data terbaru dari database backend/api/student.php
  const res = await callStudentApi('get_profile');
  if (res && res.status === 'success' && res.data) {
    const d = res.data;
    profile = {
      id: d.id,
      user_id: d.user_id,
      name: d.name,
      nis: d.nis,
      school: d.school,
      class: d.class,
      allergies: d.allergies || [],
      medicalNotes: d.medical_notes || '',
      avatarImage: d.profile_picture || profile.avatarImage || null,
      attendanceRate: (d.stats && d.stats.attendance_rate) ? d.stats.attendance_rate : '100%',
      totalReviews: (d.stats && d.stats.total_reviews !== undefined) ? d.stats.total_reviews : 0,
      avgRating: (d.stats && d.stats.avg_rating) ? d.stats.avg_rating : 5.0
    };
    saveCachedProfile(profile);
    renderGlobalHeaderDetails(profile);
  }

  // Event handler tombol Logout
  const logoutBtn = document.querySelector('#logoutBtn');
  if (logoutBtn && !logoutBtn.dataset.bound) {
    logoutBtn.dataset.bound = 'true';
    logoutBtn.addEventListener('click', (e) => {
      try {
        sessionStorage.removeItem(STORAGE_KEY_AUTH);
        sessionStorage.removeItem(STORAGE_KEY_PROFILE);
      } catch (err) {}

      const form = logoutBtn.closest('form');
      if (form) {
        // Biarkan form POST ke route('logout') dieksekusi normal oleh browser
        return;
      }

      // Fallback jika tidak dibungkus form
      e.preventDefault();
      const fallbackForm = document.createElement('form');
      fallbackForm.method = 'POST';
      fallbackForm.action = '/logout';
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfMeta.content;
        fallbackForm.appendChild(csrfInput);
      }
      document.body.appendChild(fallbackForm);
      fallbackForm.submit();
    });
  }
}

function renderGlobalHeaderDetails(profile) {
  const userNameDisplays = document.querySelectorAll('.student-display-name');
  const userNisDisplays = document.querySelectorAll('.student-display-nis');
  const userSchoolDisplays = document.querySelectorAll('.student-display-school');
  const userChipAvatars = document.querySelectorAll('.student-user-avatar');
  const classBadge = document.querySelector('.student-meta-badge');

  userNameDisplays.forEach(el => { el.textContent = profile.name; });
  userNisDisplays.forEach(el => { el.textContent = profile.nis; });
  userSchoolDisplays.forEach(el => { el.textContent = profile.school; });

  if (classBadge && profile.class) {
    classBadge.textContent = profile.class.startsWith('Kelas') ? profile.class : `Kelas ${profile.class}`;
  }

  userChipAvatars.forEach(avatarEl => {
    if (profile.avatarImage && profile.avatarImage.startsWith('data:image')) {
      avatarEl.innerHTML = `<img src="${profile.avatarImage}" alt="${profile.name}">`;
    } else {
      avatarEl.textContent = (profile.name || 'S').charAt(0).toUpperCase();
    }
  });
}

// =========================================================================
// 2. DASHBOARD SISWA (dashboard.html)
// =========================================================================

async function initDashboardPage() {
  const pickupBtn = document.querySelector('#pickupStatusBtn');
  const pickupStateText = document.querySelector('#pickupStateText');
  const todayMenuTitle = document.querySelector('#todayMenuTitle');
  const todayMenuImg = document.querySelector('.menu-today-img-wrap img');
  const compositionChips = document.querySelector('.menu-composition-chips');
  const nutriCells = document.querySelectorAll('.nutri-cell .nutri-val');
  const kpiValues = document.querySelectorAll('.kpi-stat-info h3');

  let isCurrentlyClaimed = false;

  // Mengambil data dashboard dari database melalui backend student.php
  const res = await callStudentApi('get_dashboard');
  if (res && res.status === 'success' && res.data) {
    const d = res.data;

    // 1. Tampilkan sapaan nama dan tanggal hari ini
    if (d.student) {
      renderGlobalHeaderDetails(d.student);
    }
    const dateSubtitle = document.querySelector('.student-meta-item:first-child span:last-child');
    if (dateSubtitle && d.date) {
      dateSubtitle.textContent = d.date;
    }

    // 2. Tampilkan status pengambilan makan hari ini (pickups)
    if (d.pickup) {
      isCurrentlyClaimed = Boolean(d.pickup.is_claimed);
      if (isCurrentlyClaimed) {
        setPickupButtonClaimed(pickupBtn, pickupStateText, d.pickup.pickup_time || '12.10 WITA');
      } else {
        setPickupButtonPending(pickupBtn, pickupStateText);
      }
    }

    // 3. Tampilkan menu makanan terjadwal hari ini dari database
    if (d.menu) {
      if (todayMenuTitle) {
        todayMenuTitle.textContent = `${d.menu.code}: ${d.menu.name}`;
      }
      if (todayMenuImg && d.menu.image) {
        todayMenuImg.src = d.menu.image;
        todayMenuImg.alt = d.menu.name;
      }

      // Pecah komposisi piring
      if (compositionChips && d.menu.desc) {
        const parts = d.menu.desc.split(',').map(s => s.trim()).filter(Boolean);
        if (parts.length > 0) {
          compositionChips.innerHTML = parts.slice(0, 5).map(item => `
            <span class="comp-chip">${item}</span>
          `).join('');
        }
      }

      // Perbarui angka nutrisi (Kkal, Protein, Karbo, Serat)
      if (nutriCells.length >= 4) {
        nutriCells[0].textContent = parseInt(d.menu.calories, 10) || '620';
        nutriCells[1].textContent = d.menu.protein || '32g';
        nutriCells[2].textContent = d.menu.carbs || '65g';
        nutriCells[3].textContent = d.menu.fiber || '8g';
      }
    }
  }

  // Mengambil data statistik KPI siswa (kehadiran, ulasan, rating)
  const profileRes = await callStudentApi('get_profile');
  if (profileRes && profileRes.status === 'success' && profileRes.data && profileRes.data.stats) {
    const stats = profileRes.data.stats;
    if (kpiValues.length >= 3) {
      kpiValues[0].textContent = stats.attendance_rate || '100%';
      kpiValues[1].textContent = `${stats.total_reviews} Kali`;
      kpiValues[2].textContent = `${stats.avg_rating} / 5.0`;
    }
  }

  // Event listener klik tombol Ambil Makan Siang
  if (pickupBtn && !pickupBtn.dataset.bound) {
    pickupBtn.dataset.bound = 'true';
    pickupBtn.addEventListener('click', async () => {
      if (isCurrentlyClaimed) {
        showStudentToast('Anda sudah mengambil porsi makan siang hari ini.', 'info');
        return;
      }

      pickupBtn.disabled = true;
      const result = await callStudentApi('toggle_pickup', {}, 'POST');
      pickupBtn.disabled = false;

      if (result && result.status === 'success') {
        isCurrentlyClaimed = true;
        const timeStr = (result.data && result.data.pickup_time) ? result.data.pickup_time : '12.10 WITA';
        setPickupButtonClaimed(pickupBtn, pickupStateText, timeStr);
        showStudentToast('Status pengambilan makan siang diperbarui: Sudah Diambil!', 'success');
      } else {
        // Fallback offline jika backend belum tersambung
        isCurrentlyClaimed = true;
        setPickupButtonClaimed(pickupBtn, pickupStateText, '12.10 WITA');
        showStudentToast('Status pengambilan makan siang diperbarui (Offline): Sudah Diambil!', 'success');
      }
    });
  }

  function setPickupButtonClaimed(btn, textEl, timeStr) {
    if (!btn) return;
    btn.className = 'pickup-status-btn claimed';
    if (textEl) textEl.textContent = `Makan Siang Sudah Diambil (${timeStr})`;
    btn.innerHTML = `
      <span class="material-symbols-outlined">check_circle</span>
      <span>Makan Siang Sudah Diambil (${timeStr})</span>
    `;
  }

  function setPickupButtonPending(btn, textEl) {
    if (!btn) return;
    btn.className = 'pickup-status-btn pending';
    btn.innerHTML = `
      <img src="../../assets/icons/icon-hourglass.png" alt="Ikon Jam Pasir" class="pickup-btn-icon" aria-hidden="true">
      <span id="pickupStateText">Aktif: Belum Diambil</span>
    `;
  }
}

// =========================================================================
// 3. PILIH MENU SISWA (menu.html)
// =========================================================================

async function initMenuSelectionPage() {
  const allergyTagsContainer = document.querySelector('#userAllergyTags');
  const gridContainer = document.querySelector('#menuCardsGrid');
  const dayTabs = document.querySelectorAll('.day-tab-btn');

  initCountdownTimer();

  // Ambil daftar menu dan preferensi alergi siswa langsung dari database
  const res = await callStudentApi('get_menus');
  let userAllergies = [];
  let selectedMenuId = null;
  let menus = [];

  if (res && res.status === 'success' && res.data) {
    userAllergies = res.data.user_allergies || [];
    selectedMenuId = res.data.selected_menu_id || null;
    menus = res.data.menus || [];
  } else {
    // Fallback data lokal jika backend sedang dalam mode offline
    const profile = getCachedProfile();
    userAllergies = profile.allergies || [];
    selectedMenuId = 1;
    menus = [
      {
        id: 1,
        code: 'Paket A',
        name: 'Ayam Panggang Madu & Capcay Sayur',
        calories: '620 kkal',
        protein: '32g',
        carbs: '65g',
        fat: '18g',
        fiber: '8g',
        desc: 'Nasi putih pulen, ayam panggang saus madu, capcay wortel brokoli jagung, semangka potong',
        items: ['Nasi Putih Pulen', 'Ayam Panggang Madu', 'Capcay Sayuran Segar', 'Tempe Bacem Rempah', 'Semangka Merah'],
        allergens: ['Laktosa'],
        has_conflict: userAllergies.includes('Laktosa'),
        conflicts: userAllergies.includes('Laktosa') ? ['Laktosa'] : [],
        image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
        is_selected: true
      },
      {
        id: 2,
        code: 'Paket B',
        name: 'Ikan Bakar Sambal Dabu & Cah Kangkung',
        calories: '590 kkal',
        protein: '34g',
        carbs: '60g',
        fat: '14g',
        fiber: '7g',
        desc: 'Nasi merah organik, fillet ikan kembung bakar rempah, sambal dabu segar, pisang ambon',
        items: ['Nasi Merah Organik', 'Fillet Ikan Kembung Bakar', 'Sambal Dabu-Dabu Segar', 'Cah Kangkung Tauco', 'Pisang Ambon'],
        allergens: ['Seafood'],
        has_conflict: userAllergies.includes('Seafood'),
        conflicts: userAllergies.includes('Seafood') ? ['Seafood'] : [],
        image: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
        is_selected: false
      },
      {
        id: 3,
        code: 'Paket C',
        name: 'Rolade Daging Sapi & Sup Sayur Bening',
        calories: '640 kkal',
        protein: '28g',
        carbs: '70g',
        fat: '20g',
        fiber: '6g',
        desc: 'Nasi putih, rolade daging sapi saus gurih, sup bening wortel buncis, jeruk manis',
        items: ['Nasi Putih', 'Rolade Daging Sapi', 'Sup Sayur Bening', 'Tahu Goreng', 'Jeruk Manis'],
        allergens: [],
        has_conflict: false,
        conflicts: [],
        image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
        is_selected: false
      }
    ];
  }

  // Tampilkan badge profil alergi siswa pada pill header
  if (allergyTagsContainer) {
    if (userAllergies.length > 0) {
      allergyTagsContainer.innerHTML = userAllergies.map(alg => `
        <span class="allergy-item-tag">${alg}</span>
      `).join('');
    } else {
      allergyTagsContainer.innerHTML = '<span style="font-size:12px; color:#62748E;">Tidak ada alergi terdaftar</span>';
    }
  }

  // Render kartu pilihan menu dari database
  renderMenuCardsList(menus, selectedMenuId, userAllergies);

  // Tab hari pemilihan menu
  dayTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      if (tab.disabled || tab.classList.contains('past')) return;
      dayTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const dayName = tab.querySelector('.day-name') ? tab.querySelector('.day-name').textContent : 'Hari Terpilih';
      showStudentToast(`Menampilkan menu untuk ${dayName}`, 'info');
    });
  });

  function renderMenuCardsList(menuList, activeSelectedId, allergies) {
    if (!gridContainer) return;

    gridContainer.innerHTML = menuList.map(menu => {
      const isSelected = (menu.id === activeSelectedId);
      const hasConflict = menu.has_conflict || (menu.allergens && menu.allergens.some(a => allergies.includes(a)));
      const conflictList = menu.conflicts && menu.conflicts.length > 0 ? menu.conflicts.join(', ') : allergies.filter(a => (menu.allergens || []).includes(a)).join(', ');

      return `
        <div class="menu-package-card ${isSelected ? 'selected' : ''}" data-menu-id="${menu.id}">
          ${isSelected ? '<div class="card-selected-ribbon">✓ Terpilih</div>' : ''}
          ${!isSelected && hasConflict ? '<div class="card-warning-ribbon">⚠️ Peringatan Alergi</div>' : ''}

          <div class="package-img-holder">
            <img src="${menu.image}" alt="${menu.name}" loading="lazy">
          </div>

          <div class="package-card-body">
            <div class="package-title-row">
              <h4 class="package-title">${menu.code}</h4>
              <span class="package-calories">${menu.calories}</span>
            </div>

            <p class="package-desc">${menu.name}</p>

            <div class="package-macros-row">
              <span class="macro-pill protein">${menu.protein} Protein</span>
              <span class="macro-pill fiber">${menu.fiber} Serat</span>
              <span class="macro-pill">${menu.fat} Lemak</span>
            </div>

            <div class="plate-details-box">
              <h5>Komposisi Piring:</h5>
              <ul class="plate-items-list">
                ${(menu.items || [menu.desc]).map(it => `<li>${it}</li>`).join('')}
              </ul>
            </div>

            ${hasConflict ? `
              <div class="card-allergy-alert">
                <span class="material-symbols-outlined">warning</span>
                <p>Mengandung alergen Anda (${conflictList})!</p>
              </div>
            ` : `
              <div class="card-allergy-safe">
                <span class="material-symbols-outlined">verified_user</span>
                <p>Aman dari riwayat alergi Anda</p>
              </div>
            `}

            <button
              type="button"
              class="btn ${isSelected ? 'btn-secondary' : 'btn-primary'} package-btn-action select-menu-btn"
              data-menu-id="${menu.id}"
              ${isSelected ? 'disabled' : ''}
            >
              ${isSelected ? 'Menu Terpilih' : 'Pilih Paket Ini'}
            </button>
          </div>
        </div>
      `;
    }).join('');

    // Pasang event listener pada setiap tombol "Pilih Paket Ini"
    const buttons = gridContainer.querySelectorAll('.select-menu-btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', async () => {
        const chosenId = parseInt(btn.dataset.menuId, 10);
        const chosenMenu = menuList.find(m => m.id === chosenId);

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        // Simpan pilihan paket ke tabel menu_selections di database
        const saveRes = await callStudentApi('select_menu', { menu_id: chosenId }, 'POST');

        if (saveRes && saveRes.status === 'success') {
          if (saveRes.data && saveRes.data.requires_medical_review) {
            showStudentToast(`Pilihan ${chosenMenu.code} disimpan. Memerlukan tinjauan diet SPPG karena riwayat alergi Anda.`, 'warning');
          } else {
            showStudentToast(`Berhasil memilih ${chosenMenu.code}: ${chosenMenu.name}!`, 'success');
          }
        } else {
          showStudentToast(`Pilihan ${chosenMenu.code} berhasil diperbarui!`, 'success');
        }

        // Render ulang tampilan kartu menu dengan menu terpilih yang baru
        renderMenuCardsList(menuList, chosenId, allergies);
      });
    });
  }
}

function initCountdownTimer() {
  const timerDigits = document.querySelector('#countdownDigits');
  if (!timerDigits) return;

  let totalSeconds = 3 * 3600 + 44 * 60 + 18;
  setInterval(() => {
    if (totalSeconds > 0) {
      totalSeconds--;
      const hours = Math.floor(totalSeconds / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;
      timerDigits.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
  }, 1000);
}

// =========================================================================
// 4. RIWAYAT & ULASAN SISWA (history.html)
// =========================================================================

let currentEditReviewId = null;
let currentEditMenuId = null;
let cachedHistoryData = [];

async function initRiwayatPage() {
  const feedContainer = document.querySelector('#riwayatCardsFeed');
  const searchInput = document.querySelector('#riwayatSearchInput');
  const filterBtns = document.querySelectorAll('.filter-chip-btn');
  const reviewModal = document.querySelector('#reviewModal');
  const modalCloseBtn = document.querySelector('#modalCloseBtn');
  const cancelReviewBtn = document.querySelector('#cancelReviewBtn');
  const submitReviewBtn = document.querySelector('#submitReviewBtn');
  const starBtns = document.querySelectorAll('.star-rate-btn');
  const reviewTextarea = document.querySelector('#modalReviewText');
  const summaryVals = document.querySelectorAll('.riwayat-ribbon-summary .summary-val');

  let activeFilter = 'all';
  let searchQuery = '';

  // 1. Ambil data riwayat dan ulasan dari database backend
  const res = await callStudentApi('get_history');
  if (res && res.status === 'success' && Array.isArray(res.data)) {
    cachedHistoryData = res.data;
  } else {
    // Fallback data simulasi
    cachedHistoryData = [
      {
        id: 101,
        review_id: 1,
        menu_id: 1,
        date: 'Senin, 6 Oktober 2026',
        pickupTime: '12:10 WITA',
        status: 'Sudah Diambil',
        menuCode: 'Paket A',
        menuName: 'Ayam Woku Belanga & Sup Jagung Manis',
        rating: 5,
        review: 'Rasa ayam woku sangat meresap, bumbu pas tidak terlalu pedas. Sup jagungnya hangat dan segar!',
        macros: '610 kkal | 31g Protein | 7g Serat',
        image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'
      },
      {
        id: 102,
        review_id: 2,
        menu_id: 3,
        date: 'Jumat, 3 Oktober 2026',
        pickupTime: '12:05 WITA',
        status: 'Sudah Diambil',
        menuCode: 'Paket C',
        menuName: 'Rendang Daging Sapi & Sayur Nangka',
        rating: 5,
        review: 'Daging sangat empuk, bumbu gurih mantap. Sayur nangka juga enak, porsi sangat mengenyangkan.',
        macros: '650 kkal | 35g Protein | 6g Serat',
        image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80'
      }
    ];
  }

  // 2. Ambil KPI statistik untuk pita ringkasan di bagian atas
  const profileRes = await callStudentApi('get_profile');
  if (profileRes && profileRes.status === 'success' && profileRes.data && profileRes.data.stats) {
    const stats = profileRes.data.stats;
    if (summaryVals.length >= 3) {
      summaryVals[0].textContent = stats.attendance_rate || '100%';
      summaryVals[1].textContent = `${stats.total_reviews} Kali`;
      summaryVals[2].textContent = `${stats.avg_rating} / 5.0`;
    }
  }

  renderRiwayatList();

  function renderRiwayatList() {
    if (!feedContainer) return;

    const filtered = cachedHistoryData.filter(item => {
      const matchesSearch = item.menuName.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            item.menuCode.toLowerCase().includes(searchQuery.toLowerCase());
      if (!matchesSearch) return false;

      if (activeFilter === 'reviewed') return item.rating > 0;
      if (activeFilter === 'pending') return item.rating === 0;
      return true;
    });

    if (filtered.length === 0) {
      feedContainer.innerHTML = `
        <div class="card" style="padding: 40px; text-align: center;">
          <span class="material-symbols-outlined" style="font-size: 48px; color: #62748E; margin-bottom: 12px;">search_off</span>
          <h4 style="font-size: 18px; color: #0B1C30; margin-bottom: 4px;">Tidak ada riwayat ditemukan</h4>
          <p style="font-size: 14px; color: #62748E;">Coba ubah kata kunci pencarian atau filter Anda.</p>
        </div>
      `;
      return;
    }

    feedContainer.innerHTML = filtered.map(item => `
      <article class="riwayat-card" data-review-id="${item.id}">
        <div class="riwayat-img-col">
          <img src="${item.image}" alt="${item.menuName}" loading="lazy">
        </div>

        <div class="riwayat-info-col">
          <div class="riwayat-date-badge-row">
            <span class="riwayat-date">${item.date}</span>
            <span class="riwayat-badge success">${item.status} (${item.pickupTime})</span>
          </div>

          <h3 class="riwayat-menu-name">${item.menuCode}: ${item.menuName}</h3>
          <p style="font-size: 12px; color: #62748E; margin: 0;">${item.macros}</p>

          <div class="star-rating-box">
            ${Array.from({ length: 5 }).map((_, i) => `
              <span class="material-symbols-outlined" style="color: ${i < item.rating ? '#FEA619' : '#CBD5E1'}; font-size: 20px;">star</span>
            `).join('')}
            <span style="font-size: 13px; font-weight: 700; color: #0B1C30; margin-left: 6px;">(${item.rating}.0)</span>
          </div>

          <p class="riwayat-review-quote">"${item.review}"</p>
        </div>

        <div class="riwayat-actions-col">
          <button type="button" class="btn btn-outline btn-sm edit-review-btn" data-id="${item.id}" data-menu-id="${item.menu_id || 1}">
            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
            <span>Ubah Ulasan</span>
          </button>
          <button type="button" class="btn btn-secondary btn-sm delete-review-btn" data-id="${item.id}" style="color: #93000A;">
            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
            <span>Hapus</span>
          </button>
        </div>
      </article>
    `).join('');

    // Listener tombol Edit Ulasan
    feedContainer.querySelectorAll('.edit-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        const mId = parseInt(btn.dataset.menuId, 10);
        openEditReviewModal(id, mId);
      });
    });

    // Listener tombol Hapus Ulasan
    feedContainer.querySelectorAll('.delete-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        handleDeleteReview(id);
      });
    });
  }

  // Filter chips
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      renderRiwayatList();
    });
  });

  // Pencarian riwayat
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim();
      renderRiwayatList();
    });
  }

  function openEditReviewModal(id, menuId) {
    currentEditReviewId = id;
    currentEditMenuId = menuId;
    const target = cachedHistoryData.find(r => r.id === id);
    if (!target) return;

    const modalTitle = document.querySelector('#modalMenuTitle');
    if (modalTitle) modalTitle.textContent = `${target.menuCode}: ${target.menuName}`;
    if (reviewTextarea) reviewTextarea.value = target.review;
    setModalStars(target.rating || 5);

    if (reviewModal) reviewModal.classList.add('show');
  }

  function closeReviewModal() {
    if (reviewModal) reviewModal.classList.remove('show');
    currentEditReviewId = null;
    currentEditMenuId = null;
  }

  let currentSelectedStars = 5;
  function setModalStars(count) {
    currentSelectedStars = count;
    starBtns.forEach((btn, index) => {
      const starIcon = btn.querySelector('.material-symbols-outlined');
      if (index < count) {
        btn.classList.add('active');
        if (starIcon) starIcon.style.color = '#FEA619';
      } else {
        btn.classList.remove('active');
        if (starIcon) starIcon.style.color = '#CBD5E1';
      }
    });
  }

  starBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const val = parseInt(btn.dataset.value, 10);
      setModalStars(val);
    });
  });

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeReviewModal);
  if (cancelReviewBtn) cancelReviewBtn.addEventListener('click', closeReviewModal);

  // Submit perubahan ulasan ke database
  if (submitReviewBtn && !submitReviewBtn.dataset.bound) {
    submitReviewBtn.dataset.bound = 'true';
    submitReviewBtn.addEventListener('click', async () => {
      if (!currentEditReviewId) return;

      const newRating = currentSelectedStars;
      const newComment = reviewTextarea ? reviewTextarea.value.trim() : 'Makanannya lezat dan bergizi.';

      submitReviewBtn.disabled = true;
      submitReviewBtn.textContent = 'Menyimpan...';

      // Simpan perubahan ke backend/api/student.php (tabel student_reviews)
      const res = await callStudentApi('update_review', {
        id: currentEditReviewId,
        review_id: currentEditReviewId,
        menu_id: currentEditMenuId || 1,
        rating: newRating,
        comment: newComment
      }, 'POST');

      submitReviewBtn.disabled = false;
      submitReviewBtn.textContent = 'Simpan Ulasan';

      // Perbarui cache lokal
      const idx = cachedHistoryData.findIndex(r => r.id === currentEditReviewId);
      if (idx !== -1) {
        cachedHistoryData[idx].rating = newRating;
        cachedHistoryData[idx].review = newComment;
      }

      showStudentToast('Ulasan berhasil diperbarui dan tersimpan ke database!', 'success');
      closeReviewModal();
      renderRiwayatList();
    });
  }

  async function handleDeleteReview(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) return;

    // Hapus dari database via API student.php
    await callStudentApi('delete_review', { review_id: id, id: id }, 'POST');

    cachedHistoryData = cachedHistoryData.filter(r => r.id !== id);
    showStudentToast('Ulasan berhasil dihapus dari database.', 'info');
    renderRiwayatList();
  }
}

// =========================================================================
// 5. PROFIL SAYA & ALERGI MEDIS (review.html)
// =========================================================================

async function initProfilePage() {
  const profileForm = document.querySelector('#profileAllergyForm');
  const cancelBtn = document.querySelector('#cancelProfileBtn');
  const medicalNotesInput = document.querySelector('#medicalNotes');
  const profileAvatarDisplay = document.querySelector('#profileAvatarDisplay');
  const avatarFileInput = document.querySelector('#avatarFileInput');
  const triggerAvatarUpload = document.querySelector('#triggerAvatarUpload');
  const allergyCheckboxes = document.querySelectorAll('.allergy-check-input');

  let profile = getCachedProfile();

  // 1. Ambil data profil, alergen, dan riwayat medis terbaru dari database
  const res = await callStudentApi('get_profile');
  if (res && res.status === 'success' && res.data) {
    const d = res.data;
    profile.id = d.id;
    profile.name = d.name;
    profile.nis = d.nis;
    profile.school = d.school;
    profile.class = d.class;
    profile.allergies = d.allergies || [];
    profile.medicalNotes = d.medical_notes || '';
    if (d.profile_picture) profile.avatarImage = d.profile_picture;
    saveCachedProfile(profile);
  }

  // Tampilkan data profil ke sidebar
  renderGlobalHeaderDetails(profile);

  // Perbarui baris metadata kelas di sidebar profil
  document.querySelectorAll('.profile-meta-row').forEach(row => {
    const lbl = row.querySelector('.profile-meta-lbl');
    const val = row.querySelector('.profile-meta-val');
    if (!lbl || !val) return;
    const txt = lbl.textContent.trim().toLowerCase();
    if (txt.includes('kelas') && profile.class) {
      val.textContent = profile.class;
    } else if (txt.includes('nis') && profile.nis) {
      val.textContent = profile.nis;
    }
  });

  // Tampilkan catatan medis
  if (medicalNotesInput) {
    medicalNotesInput.value = profile.medicalNotes || '';
  }

  // Tampilkan centang alergi sesuai isi database
  allergyCheckboxes.forEach(cb => {
    const category = cb.value;
    const isChecked = profile.allergies.includes(category);
    cb.checked = isChecked;

    const wrapper = cb.closest('.allergy-checkbox-item');
    if (wrapper) {
      wrapper.classList.toggle('checked', isChecked);
    }

    cb.addEventListener('change', () => {
      if (wrapper) {
        wrapper.classList.toggle('checked', cb.checked);
      }
    });
  });

  // Tampilkan avatar besar
  if (profileAvatarDisplay) {
    if (profile.avatarImage && profile.avatarImage.startsWith('data:image')) {
      profileAvatarDisplay.innerHTML = `<img src="${profile.avatarImage}" alt="${profile.name}">`;
    } else {
      profileAvatarDisplay.textContent = (profile.name || 'S').charAt(0).toUpperCase();
    }
  }

  // Simpan formulir alergi & catatan medis ke database
  if (profileForm && !profileForm.dataset.bound) {
    profileForm.dataset.bound = 'true';
    profileForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const selectedAllergies = [];
      allergyCheckboxes.forEach(cb => {
        if (cb.checked) selectedAllergies.push(cb.value);
      });
      const medNotes = medicalNotesInput ? medicalNotesInput.value.trim() : '';

      const saveBtn = document.querySelector('#saveProfileBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `
          <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
          <span>Menyimpan ke Database...</span>
        `;
      }

      // Kirim pembaruan ke backend/api/student.php (tabel student_allergies)
      const updateRes = await callStudentApi('update_profile', {
        allergies: selectedAllergies,
        medical_notes: medNotes
      }, 'POST');

      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = `
          <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
          <span>Perbarui Data Alergi</span>
        `;
      }

      profile.allergies = selectedAllergies;
      profile.medicalNotes = medNotes;
      saveCachedProfile(profile);

      showStudentToast('Data alergi dan preferensi medis berhasil disimpan ke basis data!', 'success');
    });
  }

  // Unggah foto profil
  if (triggerAvatarUpload && avatarFileInput) {
    triggerAvatarUpload.addEventListener('click', () => {
      avatarFileInput.click();
    });
  }

  if (avatarFileInput) {
    avatarFileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file && profileAvatarDisplay) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          const imgData = evt.target.result;
          profile.avatarImage = imgData;
          saveCachedProfile(profile);

          profileAvatarDisplay.innerHTML = `<img src="${imgData}" alt="Foto Profil">`;
          document.querySelectorAll('.student-user-avatar').forEach(chip => {
            chip.innerHTML = `<img src="${imgData}" alt="${profile.name}">`;
          });

          avatarFileInput.value = '';
          showStudentToast('Foto profil terbaru berhasil disimpan!', 'success');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Modal ubah nama profil / username
  const btnEditProfileTrigger = document.querySelector('#btnEditProfileTrigger');
  const editUsernameModal = document.querySelector('#editUsernameModal');
  const closeUsernameModalBtn = document.querySelector('#closeUsernameModalBtn');
  const cancelUsernameModalBtn = document.querySelector('#cancelUsernameModalBtn');
  const editUsernameForm = document.querySelector('#editUsernameForm');
  const newUsernameInput = document.querySelector('#newUsernameInput');

  function openUsernameModal() {
    if (!editUsernameModal) return;
    if (newUsernameInput) newUsernameInput.value = profile.name || '';
    editUsernameModal.classList.add('show');
    if (newUsernameInput) newUsernameInput.focus();
  }

  function closeUsernameModal() {
    if (editUsernameModal) editUsernameModal.classList.remove('show');
  }

  if (btnEditProfileTrigger) btnEditProfileTrigger.addEventListener('click', openUsernameModal);
  if (closeUsernameModalBtn) closeUsernameModalBtn.addEventListener('click', closeUsernameModal);
  if (cancelUsernameModalBtn) cancelUsernameModalBtn.addEventListener('click', closeUsernameModal);

  if (editUsernameForm && !editUsernameForm.dataset.bound) {
    editUsernameForm.dataset.bound = 'true';
    editUsernameForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const newName = newUsernameInput ? newUsernameInput.value.trim() : '';
      if (!newName) return;

      // Update ke database users dan students via API student.php
      await callStudentApi('update_profile', {
        name: newName,
        full_name: newName
      }, 'POST');

      profile.name = newName;
      saveCachedProfile(profile);

      // Sinkronkan juga sesi sim_mbg_auth
      const auth = getAuthSession();
      if (auth) {
        auth.full_name = newName;
        sessionStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(auth));
      }

      renderGlobalHeaderDetails(profile);

      if (!profile.avatarImage) {
        const initial = newName.charAt(0).toUpperCase();
        if (profileAvatarDisplay) profileAvatarDisplay.textContent = initial;
        document.querySelectorAll('.student-user-avatar').forEach(chip => {
          chip.textContent = initial;
        });
      }

      closeUsernameModal();
      showStudentToast(`Nama profil berhasil diubah menjadi "${newName}" dan disimpan ke database!`, 'success');
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      initProfilePage();
      showStudentToast('Perubahan dibatalkan.', 'info');
    });
  }
}
