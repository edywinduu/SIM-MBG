/**
 * SIM-MBG — Student Page Script (siswa.js)
 * Khusus menangani interaktivitas UI frontend (modal, tab, filter, preview).
 * Data utama (nama siswa, sekolah, kelas, jam distribusi, akun, menu, riwayat)
 * sepenuhnya dikelola dan dirender langsung oleh Laravel Blade & Controller backend.
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

  // Interaksi tombol status ambil makan siang terhubung ke backend
  if (pickupBtn && !pickupBtn.dataset.bound) {
    pickupBtn.dataset.bound = 'true';
    pickupBtn.addEventListener('click', async () => {
      if (pickupBtn.disabled || pickupBtn.classList.contains('claimed')) {
        showStudentToast('Anda sudah mengambil porsi makan siang hari ini.', 'info');
        return;
      }

      pickupBtn.disabled = true;

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/student/pickup/claim', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.status === 'success' || data.status === 'info') {
          setPickupButtonClaimed(pickupBtn, pickupStateText, data.pickup_time || '');
          showStudentToast(data.message || 'Makan siang sudah diambil!', data.status);
        } else {
          pickupBtn.disabled = false;
          showStudentToast(data.message || 'Gagal memperbarui status pengambilan.', 'error');
        }
      } catch (err) {
        console.error(err);
        pickupBtn.disabled = false;
        showStudentToast('Terjadi gangguan jaringan saat memproses status pengambilan.', 'error');
      }
    });
  }

  function setPickupButtonClaimed(btn, textEl, timeStr) {
    if (!btn) return;
    btn.className = 'pickup-status-btn claimed';
    btn.disabled = true;
    const timeLabel = timeStr ? ` (${timeStr})` : '';
    if (textEl) textEl.textContent = `Makan Siang Sudah Diambil${timeLabel}`;
    btn.innerHTML = `
      <span class="material-symbols-outlined">check_circle</span>
      <span>Makan Siang Sudah Diambil${timeLabel}</span>
    `;
  }
}

// =========================================================================
// 2. PILIH MENU SISWA
// =========================================================================

function initMenuSelectionPage() {
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
}

/**
 * Menghitung sisa waktu menuju batas akhir pemesanan menu (Cut-Off pukul 20:00 WITA)
 */
function initCountdownTimer() {
  const timerDigits = document.querySelector('#countdownDigits');
  if (!timerDigits) return;

  function updateTimer() {
    const now = new Date();
    const cutoff = new Date();
    cutoff.setHours(20, 0, 0, 0);

    const diffMs = cutoff - now;
    if (diffMs <= 0) {
      timerDigits.textContent = '00:00:00';
      const label = document.querySelector('.countdown-label');
      if (label) label.textContent = 'Waktu Pemilihan Ditutup';
      return;
    }

    const totalSeconds = Math.floor(diffMs / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    timerDigits.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }

  updateTimer();
  setInterval(updateTimer, 1000);
}

// =========================================================================
// 3. RIWAYAT & ULASAN SISWA
// =========================================================================

let currentEditReviewId = null;
let currentEditMenuId = null;
let cachedHistoryData = (typeof window !== 'undefined' && Array.isArray(window.STUDENT_HISTORY_DATA))
  ? window.STUDENT_HISTORY_DATA
  : [];

function initRiwayatPage() {
  const feedContainer = document.querySelector('#riwayatCardsFeed');
  const searchInput = document.querySelector('#riwayatSearchInput');
  const filterBtns = document.querySelectorAll('.filter-chip-btn');
  const sortSelect = document.querySelector('.sort-select');
  const reviewModal = document.querySelector('#reviewModal');
  const modalCloseBtn = document.querySelector('#modalCloseBtn');
  const cancelReviewBtn = document.querySelector('#cancelReviewBtn');
  const submitReviewBtn = document.querySelector('#submitReviewBtn');
  const starBtns = document.querySelectorAll('.star-rate-btn');
  const reviewTextarea = document.querySelector('#modalReviewText');

  if (typeof window !== 'undefined' && Array.isArray(window.STUDENT_HISTORY_DATA)) {
    cachedHistoryData = window.STUDENT_HISTORY_DATA;
  } else {
    cachedHistoryData = [];
  }

  let activeFilter = 'all';
  let searchQuery = '';
  let currentSort = 'newest';

  renderRiwayatList();

  function renderRiwayatList() {
    if (!feedContainer) return;

    let filtered = cachedHistoryData.filter(item => {
      const matchesSearch = item.menuName.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            item.menuCode.toLowerCase().includes(searchQuery.toLowerCase());
      if (!matchesSearch) return false;

      if (activeFilter === 'reviewed') return item.rating > 0;
      if (activeFilter === 'pending') return item.rating === 0;
      return true;
    });

    if (currentSort === 'rating') {
      filtered = [...filtered].sort((a, b) => b.rating - a.rating);
    } else if (currentSort === 'oldest') {
      filtered = [...filtered].reverse();
    }

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
      <article class="riwayat-card" data-review-id="${item.id || ''}">
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
          <button type="button" class="btn btn-outline btn-sm edit-review-btn" data-id="${item.id || ''}" data-menu-id="${item.menu_id || ''}">
            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
            <span>${item.rating > 0 ? 'Ubah Ulasan' : 'Tulis Ulasan'}</span>
          </button>
          ${item.id ? `
          <button type="button" class="btn btn-secondary btn-sm delete-review-btn" data-id="${item.id}" style="color: #93000A;">
            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
            <span>Hapus</span>
          </button>` : ''}
        </div>
      </article>
    `).join('');

    // Listener tombol Edit Ulasan
    feedContainer.querySelectorAll('.edit-review-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id ? parseInt(btn.dataset.id, 10) : null;
        const menuId = btn.dataset.menuId ? parseInt(btn.dataset.menuId, 10) : null;
        openEditReviewModal(id, menuId);
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

  // Pengurutan riwayat
  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      currentSort = e.target.value;
      renderRiwayatList();
    });
  }

  function openEditReviewModal(id, menuId) {
    currentEditReviewId = id || null;
    currentEditMenuId = menuId || null;
    const target = cachedHistoryData.find(r => (id && r.id === id) || (menuId && r.menu_id === menuId));
    if (!target) return;

    if (!currentEditMenuId && target.menu_id) {
      currentEditMenuId = target.menu_id;
    }

    const modalTitle = document.querySelector('#modalMenuTitle');
    if (modalTitle) modalTitle.textContent = `${target.menuCode}: ${target.menuName}`;
    if (reviewTextarea) reviewTextarea.value = target.rating > 0 ? target.review : '';
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

  // Submit perubahan ulasan
  if (submitReviewBtn && !submitReviewBtn.dataset.bound) {
    submitReviewBtn.dataset.bound = 'true';
    submitReviewBtn.addEventListener('click', async () => {
      const newRating = currentSelectedStars;
      const newComment = reviewTextarea ? reviewTextarea.value.trim() : '';

      submitReviewBtn.disabled = true;

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/student/review/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            review_id: currentEditReviewId,
            menu_id: currentEditMenuId,
            rating: newRating,
            comment: newComment
          })
        });

        const data = await res.json();
        if (data.status === 'success') {
          const idx = cachedHistoryData.findIndex(r =>
            (currentEditReviewId && r.id === currentEditReviewId) ||
            (currentEditMenuId && r.menu_id === currentEditMenuId)
          );
          if (idx !== -1) {
            if (data.review_id) cachedHistoryData[idx].id = data.review_id;
            cachedHistoryData[idx].rating = newRating;
            cachedHistoryData[idx].review = newComment;
          }

          showStudentToast(data.message || 'Ulasan berhasil disimpan!', 'success');
          closeReviewModal();
          renderRiwayatList();
        } else {
          showStudentToast(data.message || 'Gagal menyimpan ulasan.', 'error');
        }
      } catch (err) {
        console.error(err);
        showStudentToast('Terjadi kesalahan koneksi saat menyimpan ulasan.', 'error');
      } finally {
        submitReviewBtn.disabled = false;
      }
    });
  }

  async function handleDeleteReview(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) return;

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const res = await fetch(`/student/review/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        }
      });
      const data = await res.json();
      if (data.status === 'success') {
        cachedHistoryData = cachedHistoryData.filter(r => r.id !== id);
        showStudentToast(data.message || 'Ulasan berhasil dihapus.', 'info');
        renderRiwayatList();
      } else {
        showStudentToast(data.message || 'Gagal menghapus ulasan.', 'error');
      }
    } catch (err) {
      console.error(err);
      showStudentToast('Terjadi kesalahan koneksi saat menghapus ulasan.', 'error');
    }
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

  // Simpan formulir alergi ke database
  if (profileForm && !profileForm.dataset.bound) {
    profileForm.dataset.bound = 'true';
    profileForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const saveBtn = document.querySelector('#saveProfileBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `
          <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 20px;">sync</span>
          <span>Menyimpan...</span>
        `;
      }

      const selectedAllergens = Array.from(allergyCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
      const medicalNotes = document.querySelector('#medicalNotes')?.value.trim() || '';

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/student/profile/allergies', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            allergens: selectedAllergens,
            medical_notes: medicalNotes
          })
        });

        const data = await res.json();
        if (data.status === 'success') {
          showStudentToast(data.message || 'Data preferensi alergi berhasil diperbarui!', 'success');
        } else {
          showStudentToast(data.message || 'Gagal memperbarui data alergi.', 'error');
        }
      } catch (err) {
        console.error(err);
        showStudentToast('Terjadi kesalahan jaringan saat memperbarui data alergi.', 'error');
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = `
            <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
            <span>Perbarui Data Alergi</span>
          `;
        }
      }
    });
  }

  // Tombol batal perubahan profil
  const cancelProfileBtn = document.querySelector('#cancelProfileBtn');
  if (cancelProfileBtn && !cancelProfileBtn.dataset.bound) {
    cancelProfileBtn.dataset.bound = 'true';
    cancelProfileBtn.addEventListener('click', () => {
      window.location.reload();
    });
  }

  // Unggah berkas foto profil ke server
  if (triggerAvatarUpload && avatarFileInput) {
    triggerAvatarUpload.addEventListener('click', () => {
      avatarFileInput.click();
    });
  }

  if (avatarFileInput && !avatarFileInput.dataset.bound) {
    avatarFileInput.dataset.bound = 'true';
    avatarFileInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('avatar', file);

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/student/profile/avatar', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: formData
        });

        const data = await res.json();
        if (data.status === 'success') {
          const imgUrl = data.avatar_url;
          if (profileAvatarDisplay) {
            profileAvatarDisplay.innerHTML = `<img src="${imgUrl}" alt="Foto Profil">`;
          }
          document.querySelectorAll('.student-user-avatar').forEach(chip => {
            chip.innerHTML = `<img src="${imgUrl}" alt="Foto Profil">`;
          });
          avatarFileInput.value = '';
          showStudentToast(data.message || 'Foto profil berhasil diperbarui!', 'success');
        } else {
          showStudentToast(data.message || 'Gagal mengunggah foto profil.', 'error');
        }
      } catch (err) {
        console.error(err);
        showStudentToast('Terjadi kesalahan saat mengunggah foto profil.', 'error');
      }
    });
  }

  // Modal ubah nama profil
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
    if (editUsernameModal) {
      editUsernameModal.classList.remove('show');
    }
  }

  if (btnEditProfileTrigger) btnEditProfileTrigger.addEventListener('click', openUsernameModal);
  if (closeUsernameModalBtn) closeUsernameModalBtn.addEventListener('click', closeUsernameModal);
  if (cancelUsernameModalBtn) cancelUsernameModalBtn.addEventListener('click', closeUsernameModal);

  // Tutup modal jika klik di area luar modal (backdrop)
  if (editUsernameModal) {
    editUsernameModal.addEventListener('click', (e) => {
      if (e.target === editUsernameModal) {
        closeUsernameModal();
      }
    });
  }

  // Tutup modal jika menekan tombol Escape di keyboard
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && editUsernameModal?.classList.contains('show')) {
      closeUsernameModal();
    }
  });

  if (editUsernameForm && !editUsernameForm.dataset.bound) {
    editUsernameForm.dataset.bound = 'true';
    editUsernameForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const newName = newUsernameInput ? newUsernameInput.value.trim() : '';
      if (!newName) return;

      const saveBtn = document.querySelector('#saveUsernameBtn');
      if (saveBtn) saveBtn.disabled = true;

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/student/profile/name', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ name: newName })
        });

        const data = await res.json();
        if (data.status === 'success') {
          const finalName = data.name || newName;
          document.querySelectorAll('.student-display-name').forEach(el => {
            el.textContent = finalName;
          });
          closeUsernameModal();
          showStudentToast(data.message || `Nama profil berhasil diubah menjadi "${finalName}"!`, 'success');
        } else {
          showStudentToast(data.message || 'Gagal mengubah nama profil.', 'error');
        }
      } catch (err) {
        console.error(err);
        showStudentToast('Terjadi kesalahan jaringan saat memperbarui nama profil.', 'error');
      } finally {
        if (saveBtn) saveBtn.disabled = false;
      }
    });
  }
}
