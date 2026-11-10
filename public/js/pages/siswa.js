/**
 * SIM-MBG — Student Page Script (siswa.js)
 * Khusus menangani interaktivitas UI frontend (modal, tab, filter, preview).
 * Data utama (nama siswa, sekolah, kelas, jam distribusi, akun) sepenuhnya
 * dikelola dan dirender langsung oleh Laravel Blade & Controller backend.
 */

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

document.addEventListener('DOMContentLoaded', () => {
  // Deteksi halaman yang sedang aktif berdasarkan ID body
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
// 1. DASHBOARD SISWA
// =========================================================================

function initDashboardPage() {
  const pickupBtn = document.querySelector('#pickupStatusBtn');
  const pickupStateText = document.querySelector('#pickupStateText');
  let isCurrentlyClaimed = false;

  // Interaksi tombol status ambil makan siang (UI Toggle)
  if (pickupBtn && !pickupBtn.dataset.bound) {
    pickupBtn.dataset.bound = 'true';
    pickupBtn.addEventListener('click', () => {
      if (isCurrentlyClaimed) {
        showStudentToast('Anda sudah mengambil porsi makan siang hari ini.', 'info');
        return;
      }

      isCurrentlyClaimed = true;
      setPickupButtonClaimed(pickupBtn, pickupStateText, '12.10 WITA');
      showStudentToast('Status pengambilan makan siang diperbarui: Sudah Diambil!', 'success');
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
}

// =========================================================================
// 2. PILIH MENU SISWA
// =========================================================================

function initMenuSelectionPage() {
  const gridContainer = document.querySelector('#menuCardsGrid');
  const dayTabs = document.querySelectorAll('.day-tab-btn');

  initCountdownTimer();

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

  // Contoh data menu untuk interaksi tampilan jika kontainer menu belum diisi oleh Blade
  if (gridContainer && gridContainer.children.length === 0) {
    const menus = [
      {
        id: 1,
        code: 'Paket A',
        name: 'Ayam Panggang Madu & Capcay Sayur',
        calories: '620 kkal',
        protein: '32g',
        fiber: '8g',
        fat: '18g',
        items: ['Nasi Putih Pulen', 'Ayam Panggang Madu', 'Capcay Sayuran Segar', 'Tempe Bacem Rempah', 'Semangka Merah'],
        image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
        is_selected: true
      },
      {
        id: 2,
        code: 'Paket B',
        name: 'Ikan Bakar Sambal Dabu & Cah Kangkung',
        calories: '590 kkal',
        protein: '34g',
        fiber: '7g',
        fat: '14g',
        items: ['Nasi Merah Organik', 'Fillet Ikan Kembung Bakar', 'Sambal Dabu-Dabu Segar', 'Cah Kangkung Tauco', 'Pisang Ambon'],
        image: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
        is_selected: false
      },
      {
        id: 3,
        code: 'Paket C',
        name: 'Rolade Daging Sapi & Sup Sayur Bening',
        calories: '640 kkal',
        protein: '28g',
        fiber: '6g',
        fat: '20g',
        items: ['Nasi Putih', 'Rolade Daging Sapi', 'Sup Sayur Bening', 'Tahu Goreng', 'Jeruk Manis'],
        image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
        is_selected: false
      }
    ];

    renderMenuCardsList(menus, 1);
  }

  function renderMenuCardsList(menuList, activeSelectedId) {
    if (!gridContainer) return;

    gridContainer.innerHTML = menuList.map(menu => {
      const isSelected = (menu.id === activeSelectedId);

      return `
        <div class="menu-package-card ${isSelected ? 'selected' : ''}" data-menu-id="${menu.id}">
          ${isSelected ? '<div class="card-selected-ribbon">✓ Terpilih</div>' : ''}

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

            <div class="card-allergy-safe">
              <span class="material-symbols-outlined">verified_user</span>
              <p>Standar Higienis & Gizi Terverifikasi</p>
            </div>

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

    gridContainer.querySelectorAll('.select-menu-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const chosenId = parseInt(btn.dataset.menuId, 10);
        const chosenMenu = menuList.find(m => m.id === chosenId);
        showStudentToast(`Berhasil memilih ${chosenMenu.code}: ${chosenMenu.name}!`, 'success');
        renderMenuCardsList(menuList, chosenId);
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
// 3. RIWAYAT & ULASAN SISWA
// =========================================================================

let currentEditReviewId = null;
let cachedHistoryData = [
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
  }
];

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

    // Listener tombol Edit Ulasan
    feedContainer.querySelectorAll('.edit-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        openEditReviewModal(id);
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

  function openEditReviewModal(id) {
    currentEditReviewId = id;
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

  // Submit perubahan ulasan
  if (submitReviewBtn && !submitReviewBtn.dataset.bound) {
    submitReviewBtn.dataset.bound = 'true';
    submitReviewBtn.addEventListener('click', () => {
      if (!currentEditReviewId) return;

      const newRating = currentSelectedStars;
      const newComment = reviewTextarea ? reviewTextarea.value.trim() : 'Makanannya lezat dan bergizi.';

      const idx = cachedHistoryData.findIndex(r => r.id === currentEditReviewId);
      if (idx !== -1) {
        cachedHistoryData[idx].rating = newRating;
        cachedHistoryData[idx].review = newComment;
      }

      showStudentToast('Ulasan berhasil diperbarui!', 'success');
      closeReviewModal();
      renderRiwayatList();
    });
  }

  function handleDeleteReview(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) return;
    cachedHistoryData = cachedHistoryData.filter(r => r.id !== id);
    showStudentToast('Ulasan berhasil dihapus.', 'info');
    renderRiwayatList();
  }
}

// =========================================================================
// 4. PROFIL SAYA & ALERGI MEDIS
// =========================================================================

function initProfilePage() {
  const profileForm = document.querySelector('#profileAllergyForm');
  const profileAvatarDisplay = document.querySelector('#profileAvatarDisplay');
  const avatarFileInput = document.querySelector('#avatarFileInput');
  const triggerAvatarUpload = document.querySelector('#triggerAvatarUpload');
  const allergyCheckboxes = document.querySelectorAll('.allergy-check-input');

  // Toggle styling saat checkbox alergi dicentang
  allergyCheckboxes.forEach(cb => {
    const wrapper = cb.closest('.allergy-checkbox-item');
    if (wrapper) {
      wrapper.classList.toggle('checked', cb.checked);
    }

    cb.addEventListener('change', () => {
      if (wrapper) {
        wrapper.classList.toggle('checked', cb.checked);
      }
    });
  });

  // Simpan formulir alergi UI feedback
  if (profileForm && !profileForm.dataset.bound) {
    profileForm.dataset.bound = 'true';
    profileForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const saveBtn = document.querySelector('#saveProfileBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `
          <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
          <span>Menyimpan...</span>
        `;
        setTimeout(() => {
          saveBtn.disabled = false;
          saveBtn.innerHTML = `
            <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
            <span>Perbarui Data Alergi</span>
          `;
          showStudentToast('Data preferensi alergi berhasil diperbarui!', 'success');
        }, 600);
      }
    });
  }

  // Unggah & preview foto profil di browser
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
          profileAvatarDisplay.innerHTML = `<img src="${imgData}" alt="Foto Profil">`;
          document.querySelectorAll('.student-user-avatar').forEach(chip => {
            chip.innerHTML = `<img src="${imgData}" alt="Foto Profil">`;
          });
          avatarFileInput.value = '';
          showStudentToast('Foto profil berhasil diperbarui!', 'success');
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
    editUsernameModal.classList.add('show');
    if (newUsernameInput) newUsernameInput.focus();
  }

  function closeUsernameModal() {
    if (!editUsernameModal) editUsernameModal.classList.remove('show');
  }

  if (btnEditProfileTrigger) btnEditProfileTrigger.addEventListener('click', openUsernameModal);
  if (closeUsernameModalBtn) closeUsernameModalBtn.addEventListener('click', closeUsernameModal);
  if (cancelUsernameModalBtn) cancelUsernameModalBtn.addEventListener('click', closeUsernameModal);

  if (editUsernameForm && !editUsernameForm.dataset.bound) {
    editUsernameForm.dataset.bound = 'true';
    editUsernameForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const newName = newUsernameInput ? newUsernameInput.value.trim() : '';
      if (!newName) return;

      document.querySelectorAll('.student-display-name').forEach(el => {
        el.textContent = newName;
      });

      closeUsernameModal();
      showStudentToast(`Nama profil berhasil diubah menjadi "${newName}"!`, 'success');
    });
  }
}
