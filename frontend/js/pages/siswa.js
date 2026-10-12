/**
 * ==========================================================================
 * SISWA JS - SIM-MBG (Member 2: Siswa)
 * State Management, Mock Data, Menu Selection, History, Reviews, and Profile
 * Compliant with AGENTS.md (Vanilla JS, camelCase, no external libraries)
 * ==========================================================================
 */

// Key Storage Constants
const STORAGE_KEY_AUTH = 'sim_mbg_auth';
const STORAGE_KEY_SELECTED_MENU = 'sim_mbg_student_selected_menu';
const STORAGE_KEY_MEAL_PICKUP = 'sim_mbg_student_meal_pickup';
const STORAGE_KEY_REVIEWS = 'sim_mbg_student_reviews';
const STORAGE_KEY_PROFILE = 'sim_mbg_student_profile';

// Mock Data Initializer
const defaultStudentProfile = {
  id: 1,
  name: 'Alya MBG',
  nis: '12345678',
  school: 'SMA Negeri 1 Denpasar',
  class: 'XI IPA 1',
  allergies: ['Kacang', 'Seafood'],
  medicalNotes: 'Sensitif terhadap produk olahan kacang tanah dan udang laut. Memerlukan menu alternatif tinggi protein nabati/telur.',
  attendanceRate: '100%',
  totalReviews: 13,
  avgRating: 4.8
};

const defaultWeeklyMenus = {
  'rabu': [
    {
      id: 'menu-a',
      code: 'Paket A',
      name: 'Ayam Panggang Madu & Capcay Sayur',
      calories: '620 kkal',
      protein: '32g',
      carbs: '65g',
      fat: '18g',
      fiber: '8g',
      desc: 'Nasi putih pulen, ayam panggang saus madu, capcay wortel brokoli jagung muda, tempe bacem, dan semangka potong.',
      items: ['Nasi Putih Pulen', 'Ayam Panggang Madu', 'Capcay Sayuran Segar', 'Tempe Bacem Rempah', 'Semangka Merah'],
      allergens: [],
      image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
      ecoFriendly: true
    },
    {
      id: 'menu-b',
      code: 'Paket B',
      name: 'Ikan Bakar Sambal Dabu & Cah Kangkung',
      calories: '590 kkal',
      protein: '34g',
      carbs: '60g',
      fat: '14g',
      fiber: '7g',
      desc: 'Nasi merah organik, fillet ikan kembung bakar rempah, sambal dabu-dabu segar, cah kangkung tauco, dan pisang ambon.',
      items: ['Nasi Merah Organik', 'Fillet Ikan Kembung Bakar', 'Sambal Dabu-Dabu Segar', 'Cah Kangkung Tauco', 'Pisang Ambon'],
      allergens: ['Seafood'],
      image: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
      ecoFriendly: true
    },
    {
      id: 'menu-c',
      code: 'Paket C',
      name: 'Rolade Daging Sapi & Sup Sayur Bening',
      calories: '640 kkal',
      protein: '28g',
      carbs: '70g',
      fat: '20g',
      fiber: '6g',
      desc: 'Nasi putih, rolade daging sapi saus gurih, sup bening wortel buncis makaroni, tahu goreng sumedang, dan jeruk manis.',
      items: ['Nasi Putih', 'Rolade Sapi Saus Gurih', 'Sup Sayur Bening Makaroni', 'Tahu Goreng Gurih', 'Jeruk Manis'],
      allergens: [],
      image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
      ecoFriendly: true
    }
  ]
};

const defaultHistoryReviews = [
  {
    id: 101,
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
    date: 'Jumat, 3 Oktober 2026',
    pickupTime: '12:05 WITA',
    status: 'Sudah Diambil',
    menuCode: 'Paket C',
    menuName: 'Rendang Daging Sapi & Sayur Nangka',
    rating: 5,
    review: 'Daging sangat empuk, bumbu gurih mantap. Sayur nangka juga enak, porsi sangat mengenyangkan.',
    macros: '650 kkal | 35g Protein | 6g Serat',
    image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 103,
    date: 'Kamis, 2 Oktober 2026',
    pickupTime: '12:20 WITA',
    status: 'Sudah Diambil',
    menuCode: 'Paket B',
    menuName: 'Telur Balado & Orek Tempe Manis',
    rating: 4,
    review: 'Pilihan yang enak untuk hari Kamis, tempe oreknya garing renyah. Buah semangka manis segar.',
    macros: '580 kkal | 24g Protein | 8g Serat',
    image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80'
  }
];

// Session-based state (Persisten antar halaman selama tab dibuka, otomatis reset saat tab ditutup)
function getStoredProfile() {
  try {
    const raw = sessionStorage.getItem('sim_mbg_session_profile');
    if (raw) return JSON.parse(raw);
  } catch (e) {}
  return JSON.parse(JSON.stringify(defaultStudentProfile));
}

function saveStoredProfile(profile) {
  try {
    sessionStorage.setItem('sim_mbg_session_profile', JSON.stringify(profile));
  } catch (e) {}
}

let memorySelectedMenuId = 'menu-a';
let memoryHistoryReviews = JSON.parse(JSON.stringify(defaultHistoryReviews));
let memoryIsClaimed = false;

function getSelectedMenuId() {
  return memorySelectedMenuId;
}

function setSelectedMenuId(menuId) {
  memorySelectedMenuId = menuId;
}

function getStoredReviews() {
  return memoryHistoryReviews;
}

function saveStoredReviews(reviews) {
  memoryHistoryReviews = reviews;
}

// Global Toast Notification Helper
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

// DOM Ready Handler
document.addEventListener('DOMContentLoaded', () => {
  initStudentGlobalHeader();
  
  // Page Specific Inits
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

/**
 * 1. Global Header Sync (User chip, logout simulation)
 */
function initStudentGlobalHeader() {
  const profile = getStoredProfile();
  const userNameDisplay = document.querySelectorAll('.student-display-name');
  const userNisDisplay = document.querySelectorAll('.student-display-nis');
  const userSchoolDisplay = document.querySelectorAll('.student-display-school');
  const userChipAvatars = document.querySelectorAll('.student-user-avatar');

  userNameDisplay.forEach(el => el.textContent = profile.name);
  userNisDisplay.forEach(el => el.textContent = profile.nis);
  userSchoolDisplay.forEach(el => el.textContent = profile.school);

  // Sync gambar / avatar inisial pada chip pojok kanan atas di semua halaman
  userChipAvatars.forEach(avatarEl => {
    if (profile.avatarImage) {
      avatarEl.innerHTML = `<img src="${profile.avatarImage}" alt="${profile.name}">`;
    } else {
      avatarEl.textContent = (profile.name || 'A').charAt(0).toUpperCase();
    }
  });

  // Logout button trigger
  const logoutBtn = document.querySelector('#logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      try {
        sessionStorage.removeItem(STORAGE_KEY_AUTH);
      } catch (err) {}
      showStudentToast('Berhasil keluar. Mengalihkan ke halaman masuk...', 'info');
      setTimeout(() => {
        window.location.href = '../auth/login.html';
      }, 600);
    });
  }
}

/**
 * 2. Dashboard Logic
 */
function initDashboardPage() {
  const pickupBtn = document.querySelector('#pickupStatusBtn');
  const pickupStateText = document.querySelector('#pickupStateText');
  const selectedMenuId = getSelectedMenuId();
  const activePackage = defaultWeeklyMenus['rabu'].find(m => m.id === selectedMenuId) || defaultWeeklyMenus['rabu'][0];

  // Update menu preview on dashboard
  const todayMenuTitle = document.querySelector('#todayMenuTitle');
  if (todayMenuTitle) {
    todayMenuTitle.textContent = `${activePackage.code}: ${activePackage.name}`;
  }

  // Pickup button toggle (In-Memory)
  if (pickupBtn) {
    if (memoryIsClaimed) {
      setButtonClaimed(pickupBtn, pickupStateText);
    }

    pickupBtn.addEventListener('click', () => {
      if (!memoryIsClaimed) {
        memoryIsClaimed = true;
        setButtonClaimed(pickupBtn, pickupStateText);
        showStudentToast('Status pengambilan makan siang diperbarui: Sudah Diambil!', 'success');
      }
    });
  }

  function setButtonClaimed(btn, textEl) {
    btn.className = 'pickup-status-btn claimed';
    if (textEl) textEl.textContent = 'Makan Siang Sudah Diambil (12.10 WITA)';
    btn.innerHTML = `
      <span class="material-symbols-outlined">check_circle</span>
      <span>Makan Siang Sudah Diambil (12.10 WITA)</span>
    `;
  }
}

/**
 * 3. Menu Selection Logic
 */
function initMenuSelectionPage() {
  const profile = getStoredProfile();
  let currentSelectedMenuId = getSelectedMenuId();

  // Render Allergy Tags in Header
  const allergyTagsContainer = document.querySelector('#userAllergyTags');
  if (allergyTagsContainer) {
    allergyTagsContainer.innerHTML = profile.allergies.map(alg => `
      <span class="allergy-item-tag">${alg}</span>
    `).join('') || '<span style="font-size:12px; color:#62748E;">Tidak ada alergi terdaftar</span>';
  }

  // Render 3 Menu Cards
  renderMenuCards(currentSelectedMenuId, profile.allergies);

  // Day Tab Navigation Switcher
  const dayTabs = document.querySelectorAll('.day-tab-btn');
  dayTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Abaikan jika hari telah berakhir atau disabled
      if (tab.disabled || tab.classList.contains('past')) return;

      dayTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const day = tab.dataset.day;
      if (day !== 'rabu') {
        showStudentToast(`Menampilkan menu untuk ${tab.querySelector('.day-name').textContent}`, 'info');
      }
    });
  });

  // Countdown Cut-off Simulation
  initCountdownTimer();
}

function renderMenuCards(selectedId, userAllergies) {
  const gridContainer = document.querySelector('#menuCardsGrid');
  if (!gridContainer) return;

  const menus = defaultWeeklyMenus['rabu'];
  gridContainer.innerHTML = menus.map(menu => {
    const isSelected = menu.id === selectedId;
    const hasAllergyConflict = menu.allergens.some(alg => userAllergies.includes(alg));

    return `
      <div class="menu-package-card ${isSelected ? 'selected' : ''}" data-menu-id="${menu.id}">
        ${isSelected ? '<div class="card-selected-ribbon">✓ Terpilih</div>' : ''}
        ${!isSelected && hasAllergyConflict ? '<div class="card-warning-ribbon">⚠️ Peringatan Alergi</div>' : ''}
        
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
              ${menu.items.map(it => `<li>${it}</li>`).join('')}
            </ul>
          </div>

          ${hasAllergyConflict ? `
            <div class="card-allergy-alert">
              <span class="material-symbols-outlined">warning</span>
              <p>Mengandung alergen Anda (${menu.allergens.join(', ')})!</p>
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

  // Add click listeners to select buttons
  const buttons = gridContainer.querySelectorAll('.select-menu-btn');
  buttons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const chosenId = btn.dataset.menuId;
      setSelectedMenuId(chosenId);
      const chosenMenu = menus.find(m => m.id === chosenId);
      showStudentToast(`Berhasil memilih ${chosenMenu.code}: ${chosenMenu.name}!`, 'success');
      renderMenuCards(chosenId, userAllergies);
    });
  });
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

/**
 * 4. Riwayat & Ulasan Logic
 */
let currentEditReviewId = null;

function initRiwayatPage() {
  const feedContainer = document.querySelector('#riwayatCardsFeed');
  const searchInput = document.querySelector('#riwayatSearchInput');
  const filterBtns = document.querySelectorAll('.filter-chip-btn');
  const reviewModal = document.querySelector('#reviewModal');
  const modalCloseBtn = document.querySelector('#modalCloseBtn');
  const cancelReviewBtn = document.querySelector('#cancelReviewBtn');
  const submitReviewBtn = document.querySelector('#submitReviewBtn');
  const starBtns = document.querySelectorAll('.star-rate-btn');
  const reviewTextarea = document.querySelector('#modalReviewText');

  let activeFilter = 'all';
  let searchQuery = '';

  function renderList() {
    if (!feedContainer) return;
    const reviews = getStoredReviews();

    const filtered = reviews.filter(item => {
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
          <button type="button" class="btn btn-outline btn-sm edit-review-btn" data-id="${item.id}">
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

    // Attach actions
    feedContainer.querySelectorAll('.edit-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        openEditReviewModal(id);
      });
    });

    feedContainer.querySelectorAll('.delete-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        deleteReview(id);
      });
    });
  }

  // Filter handlers
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      renderList();
    });
  });

  // Search input
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim();
      renderList();
    });
  }

  // Modal interactions
  function openEditReviewModal(id) {
    currentEditReviewId = id;
    const reviews = getStoredReviews();
    const target = reviews.find(r => r.id === id);
    if (!target) return;

    document.querySelector('#modalMenuTitle').textContent = `${target.menuCode}: ${target.menuName}`;
    reviewTextarea.value = target.review;
    setModalStars(target.rating);

    reviewModal.classList.add('show');
  }

  function closeReviewModal() {
    reviewModal.classList.remove('show');
    currentEditReviewId = null;
  }

  let currentSelectedStars = 5;
  function setModalStars(count) {
    currentSelectedStars = count;
    starBtns.forEach((btn, index) => {
      if (index < count) {
        btn.classList.add('active');
        btn.querySelector('.material-symbols-outlined').style.color = '#FEA619';
      } else {
        btn.classList.remove('active');
        btn.querySelector('.material-symbols-outlined').style.color = '#CBD5E1';
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

  if (submitReviewBtn) {
    submitReviewBtn.addEventListener('click', () => {
      if (!currentEditReviewId) return;
      const reviews = getStoredReviews();
      const idx = reviews.findIndex(r => r.id === currentEditReviewId);
      if (idx !== -1) {
        reviews[idx].rating = currentSelectedStars;
        reviews[idx].review = reviewTextarea.value.trim() || 'Makanannya lezat dan bergizi.';
        saveStoredReviews(reviews);
        showStudentToast('Ulasan berhasil diperbarui!', 'success');
        closeReviewModal();
        renderList();
      }
    });
  }

  function deleteReview(id) {
    if (confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) {
      let reviews = getStoredReviews();
      reviews = reviews.filter(r => r.id !== id);
      saveStoredReviews(reviews);
      showStudentToast('Ulasan berhasil dihapus', 'info');
      renderList();
    }
  }

  renderList();
}

/**
 * 5. Profil Saya Logic (Frame 107:6395)
 */
function initProfilePage() {
  const profileForm = document.querySelector('#profileAllergyForm');
  const cancelBtn = document.querySelector('#cancelProfileBtn');
  const medicalNotesInput = document.querySelector('#medicalNotes');
  const profile = getStoredProfile();

  // Populate data
  if (medicalNotesInput) {
    medicalNotesInput.value = profile.medicalNotes || '';
  }

  // Allergy Checkboxes setup
  const allergyCheckboxes = document.querySelectorAll('.allergy-check-input');
  allergyCheckboxes.forEach(cb => {
    const category = cb.value;
    if (profile.allergies.includes(category)) {
      cb.checked = true;
      cb.closest('.allergy-checkbox-item').classList.add('checked');
    }

    cb.addEventListener('change', () => {
      const wrapper = cb.closest('.allergy-checkbox-item');
      if (cb.checked) {
        wrapper.classList.add('checked');
      } else {
        wrapper.classList.remove('checked');
      }
    });
  });

  // Handle Form Submission
  if (profileForm) {
    profileForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const selectedAllergies = [];
      allergyCheckboxes.forEach(cb => {
        if (cb.checked) selectedAllergies.push(cb.value);
      });

      const currentProfile = getStoredProfile();
      currentProfile.allergies = selectedAllergies;
      currentProfile.medicalNotes = medicalNotesInput ? medicalNotesInput.value.trim() : '';

      saveStoredProfile(currentProfile);
      showStudentToast('Data alergi dan preferensi medis berhasil disimpan!', 'success');
    });
  }

  // Inisialisasi tampilan avatar besar di halaman profil saat dibuka
  if (profileAvatarDisplay) {
    if (profile.avatarImage) {
      profileAvatarDisplay.innerHTML = `<img src="${profile.avatarImage}" alt="${profile.name}">`;
    } else {
      profileAvatarDisplay.textContent = (profile.name || 'A').charAt(0).toUpperCase();
    }
  }

  // 1. Upload Foto Profil (Hanya dari Tombol Kamera)
  const avatarFileInput = document.querySelector('#avatarFileInput');
  const triggerAvatarUpload = document.querySelector('#triggerAvatarUpload');

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
          
          // Ambil referensi profile terbaru dari session
          const currentProfile = getStoredProfile();
          currentProfile.avatarImage = imgData;
          saveStoredProfile(currentProfile);

          // Update foto di card profil
          profileAvatarDisplay.innerHTML = `<img src="${imgData}" alt="Foto Profil">`;

          // Update foto di user chip pojok kanan atas navbar
          document.querySelectorAll('.student-user-avatar').forEach(chip => {
            chip.innerHTML = `<img src="${imgData}" alt="${currentProfile.name}">`;
          });

          // Reset nilai input agar memilih file yang sama atau file baru berturut-turut selalu memicu event change
          avatarFileInput.value = '';

          showStudentToast('Foto profil terbaru berhasil disimpan!', 'success');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // 2. Ubah Profil / Username (Modal Interaktif)
  const btnEditProfileTrigger = document.querySelector('#btnEditProfileTrigger');
  const editUsernameModal = document.querySelector('#editUsernameModal');
  const closeUsernameModalBtn = document.querySelector('#closeUsernameModalBtn');
  const cancelUsernameModalBtn = document.querySelector('#cancelUsernameModalBtn');
  const editUsernameForm = document.querySelector('#editUsernameForm');
  const newUsernameInput = document.querySelector('#newUsernameInput');

  function openUsernameModal() {
    if (!editUsernameModal) return;
    if (newUsernameInput) {
      newUsernameInput.value = profile.name || 'Alya MBG';
    }
    editUsernameModal.classList.add('show');
    if (newUsernameInput) newUsernameInput.focus();
  }

  function closeUsernameModal() {
    if (editUsernameModal) {
      editUsernameModal.classList.remove('show');
    }
  }

  if (btnEditProfileTrigger) btnEditProfileTrigger.addEventListener('click', openUsernameModal);
  if (closeUsernameModalBtn) closeUsernameModalBtn.addEventListener('click', closeUsernameModal);
  if (cancelUsernameModalBtn) cancelUsernameModalBtn.addEventListener('click', closeUsernameModal);

  if (editUsernameForm) {
    editUsernameForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const newName = newUsernameInput ? newUsernameInput.value.trim() : '';
      if (newName) {
        profile.name = newName;
        saveStoredProfile(profile);

        // Update semua elemen teks nama siswa di halaman & chip pojok kanan secara instan
        document.querySelectorAll('.student-display-name').forEach(el => {
          el.textContent = newName;
        });

        // Update inisial avatar jika belum memakai foto
        if (!profile.avatarImage) {
          const initial = newName.charAt(0).toUpperCase();
          if (profileAvatarDisplay) profileAvatarDisplay.textContent = initial;
          document.querySelectorAll('.student-user-avatar').forEach(chip => {
            chip.textContent = initial;
          });
        }

        closeUsernameModal();
        showStudentToast(`Nama profil berhasil diubah menjadi "${newName}"!`, 'success');
      }
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      initProfilePage();
      showStudentToast('Perubahan dibatalkan.', 'info');
    });
  }
}
