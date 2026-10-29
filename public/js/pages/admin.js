

const STORAGE_KEY_ADMIN_STORE = 'sim_mbg_admin_store';
const STORAGE_KEY_AUTH = 'sim_mbg_auth';

const defaultAdminSeed = {
  sppg: [
    {
      id: 1,
      code: 'SPPG-DPS-01',
      name: 'SPPG Denpasar Barat 01',
      regency: 'Kota Denpasar',
      address: 'Jl. Gunung Agung No. 45, Pemecutan, Denpasar Barat',
      capacity: 3500,
      status: 'Aktif',
      contactPerson: 'I Wayan Sudarma',
      phone: '081234567891'
    },
    {
      id: 2,
      code: 'SPPG-BDG-02',
      name: 'SPPG Badung Selatan 02',
      regency: 'Kab. Badung',
      address: 'Jl. Bypass Ngurah Rai No. 12, Tuban, Kuta',
      capacity: 2800,
      status: 'Aktif',
      contactPerson: 'Ni Made Rai',
      phone: '081234567892'
    },
    {
      id: 3,
      code: 'SPPG-DPS-03',
      name: 'SPPG Denpasar Selatan 03',
      regency: 'Kota Denpasar',
      address: 'Jl. Tukad Yeh Aya No. 88, Panjer, Denpasar Selatan',
      capacity: 3200,
      status: 'Aktif',
      contactPerson: 'I Ketut Gede',
      phone: '081234567893'
    },
    {
      id: 4,
      code: 'SPPG-BDG-04',
      name: 'SPPG Badung Utara 04',
      regency: 'Kab. Badung',
      address: 'Jl. Raya Mengwi No. 102, Mengwi, Badung',
      capacity: 2200,
      status: 'Pemeliharaan',
      contactPerson: 'I Nyoman Suweta',
      phone: '081234567894'
    }
  ],
  schools: [
    {
      id: 1,
      npsn: '50103120',
      name: 'SMA Negeri 1 Denpasar',
      regency: 'Kota Denpasar',
      sppgId: 1,
      status: 'Aktif',
      address: 'Jl. Kamboja No. 4, Dangin Puri Kangin, Denpasar'
    },
    {
      id: 2,
      npsn: '50101850',
      name: 'SMP Negeri 2 Kuta',
      regency: 'Kab. Badung',
      sppgId: 2,
      status: 'Aktif',
      address: 'Jl. Kediri No. 7, Tuban, Kuta'
    },
    {
      id: 3,
      npsn: '50103122',
      name: 'SMA Negeri 3 Denpasar',
      regency: 'Kota Denpasar',
      sppgId: 3,
      status: 'Aktif',
      address: 'Jl. Nusa Indah No. 20, Sumerta, Denpasar Timur'
    },
    {
      id: 4,
      npsn: '50103115',
      name: 'SMP Negeri 1 Denpasar',
      regency: 'Kota Denpasar',
      sppgId: 1,
      status: 'Aktif',
      address: 'Jl. Surapati No. 2, Dangin Puri, Denpasar'
    },
    {
      id: 5,
      npsn: '50103441',
      name: 'SD Negeri 1 Renon',
      regency: 'Kota Denpasar',
      sppgId: 3,
      status: 'Proses',
      address: 'Jl. Tukad Balian No. 15, Renon, Denpasar Selatan'
    },
    {
      id: 6,
      npsn: '50101905',
      name: 'SMA Negeri 1 Kuta Utara',
      regency: 'Kab. Badung',
      sppgId: 4,
      status: 'Proses',
      address: 'Jl. Raya Dalung No. 40, Kuta Utara, Badung'
    }
  ],
  students: [
    {
      id: 1,
      nisn: '0081234567',
      name: 'Alya MBG',
      schoolId: 1,
      class: 'XI IPA 1',
      menuType: 'Menu Khusus',
      allergies: 'Kacang, Seafood',
      status: 'Aktif'
    },
    {
      id: 2,
      nisn: '0081234568',
      name: 'Satria Pratama',
      schoolId: 1,
      class: 'XI IPA 2',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 3,
      nisn: '0081234569',
      name: 'Ni Putu Maharani',
      schoolId: 2,
      class: 'VIII B',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 4,
      nisn: '0081234570',
      name: 'I Made Bagus Anggara',
      schoolId: 1,
      class: 'XI IPA 1',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 5,
      nisn: '0081234571',
      name: 'Kadek Dwi Lestari',
      schoolId: 3,
      class: 'X-3',
      menuType: 'Menu Khusus',
      allergies: 'Telur',
      status: 'Aktif'
    },
    {
      id: 6,
      nisn: '0081234572',
      name: 'Komang Arya Wijaya',
      schoolId: 2,
      class: 'IX A',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 7,
      nisn: '0081234573',
      name: 'Ketut Ayu Savitri',
      schoolId: 4,
      class: 'VII C',
      menuType: 'Menu Khusus',
      allergies: 'Laktosa',
      status: 'Aktif'
    },
    {
      id: 8,
      nisn: '0081234574',
      name: 'Wayan Yoga Mahendra',
      schoolId: 1,
      class: 'XII IPS 1',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 9,
      nisn: '0081234575',
      name: 'Gede Raditya Putra',
      schoolId: 3,
      class: 'XI MIPA 3',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 10,
      nisn: '0081234576',
      name: 'Ni Luh Gede Shanti',
      schoolId: 5,
      class: 'V A',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 11,
      nisn: '0081234577',
      name: 'Putu Indra Pratama',
      schoolId: 6,
      class: 'X-1',
      menuType: 'Menu Khusus',
      allergies: 'Gluten',
      status: 'Aktif'
    },
    {
      id: 12,
      nisn: '0081234578',
      name: 'Made Dian Saraswati',
      schoolId: 4,
      class: 'VIII D',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Aktif'
    },
    {
      id: 13,
      nisn: '0081234579',
      name: 'Nyoman Krisna Bayu',
      schoolId: 2,
      class: 'VII A',
      menuType: 'Reguler',
      allergies: '-',
      status: 'Nonaktif'
    }
  ],
  users: [
    {
      id: 1,
      name: 'Atmind Administrator',
      username: 'admin',
      email: 'admin@mbg.go.id',
      role: 'Admin',
      institution: 'Dinas Pendidikan & Gizi Provinsi',
      status: 'Aktif'
    },
    {
      id: 2,
      name: 'Pgw Alya Sudarma',
      username: 'petugas_sppg1',
      email: 'sppg.dps01@mbg.go.id',
      role: 'Petugas SPPG',
      institution: 'SPPG Denpasar Barat 01',
      status: 'Aktif'
    },
    {
      id: 3,
      name: 'Ni Made Rai Admin SPPG',
      username: 'petugas_sppg2',
      email: 'sppg.bdg02@mbg.go.id',
      role: 'Petugas SPPG',
      institution: 'SPPG Badung Selatan 02',
      status: 'Aktif'
    },
    {
      id: 4,
      name: 'Alya MBG',
      username: 'alya_siswa',
      email: 'alya@siswa.mbg.go.id',
      role: 'Siswa',
      institution: 'SMA Negeri 1 Denpasar',
      status: 'Aktif'
    },
    {
      id: 5,
      name: 'Satria Pratama',
      username: 'satria_siswa',
      email: 'satria@siswa.mbg.go.id',
      role: 'Siswa',
      institution: 'SMA Negeri 1 Denpasar',
      status: 'Aktif'
    },
    {
      id: 6,
      name: 'I Gusti Ngurah Operator',
      username: 'staf_admin',
      email: 'ngurah@mbg.go.id',
      role: 'Admin',
      institution: 'Dinas Kesehatan Prov. Bali',
      status: 'Aktif'
    },
    {
      id: 7,
      name: 'Nyoman Budi Arsana',
      username: 'budi_arsana',
      email: 'budi@mbg.go.id',
      role: 'Petugas SPPG',
      institution: 'SPPG Badung Utara 04',
      status: 'Nonaktif'
    }
  ],
  activities: [
    {
      id: 1,
      time: '10 menit yang lalu',
      title: 'Pembaruan Alergi Siswa',
      desc: 'Siswa Alya MBG (SMA Negeri 1 Denpasar) memperbarui catatan alergi Kacang & Seafood.',
      icon: 'health_and_safety',
      iconClass: 'kpi-icon-amber'
    },
    {
      id: 2,
      time: '35 menit yang lalu',
      title: 'Verifikasi Sekolah Mitra Baru',
      desc: 'SMP Negeri 2 Kuta telah berhasil diverifikasi dan terhubung ke SPPG Badung Selatan 02.',
      icon: 'verified',
      iconClass: 'kpi-icon-emerald'
    },
    {
      id: 3,
      time: '1 jam yang lalu',
      title: 'Sinkronisasi Kuota Porsi MBG',
      desc: 'SPPG Denpasar Barat 01 memperbarui estimasi kapasitas harian menjadi 3.500 porsi.',
      icon: 'restaurant',
      iconClass: 'kpi-icon-teal'
    },
    {
      id: 4,
      time: '2 jam yang lalu',
      title: 'Pendaftaran Siswa Baru',
      desc: 'Pendaftaran 35 siswa baru dari SD Negeri 1 Renon berhasil diproses ke dalam sistem.',
      icon: 'person_add',
      iconClass: 'kpi-icon-teal'
    },
    {
      id: 5,
      time: '4 jam yang lalu',
      title: 'Pemeliharaan Unit SPPG',
      desc: 'SPPG Badung Utara 04 dijadwalkan inspeksi higienitas dapur hingga 15 Oktober 2026.',
      icon: 'build',
      iconClass: 'kpi-icon-accent'
    }
  ]
};

const AdminStore = {
  get() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY_ADMIN_STORE);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (e) {
      console.warn('Gagal membaca admin store dari localStorage:', e);
    }
    
    this.save(defaultAdminSeed);
    return defaultAdminSeed;
  },

  save(data) {
    try {
      localStorage.setItem(STORAGE_KEY_ADMIN_STORE, JSON.stringify(data));
    } catch (e) {
      console.error('Gagal menyimpan admin store ke localStorage:', e);
    }
  },

  
  getSchool(id) {
    const data = this.get();
    return data.schools.find((s) => s.id === Number(id));
  },

  getSppg(id) {
    const data = this.get();
    return data.sppg.find((sp) => sp.id === Number(id));
  },

  countStudentsInSchool(schoolId) {
    const data = this.get();
    return data.students.filter((st) => st.schoolId === Number(schoolId)).length;
  },

  countSchoolsInSppg(sppgId) {
    const data = this.get();
    return data.schools.filter((sc) => sc.sppgId === Number(sppgId)).length;
  },

  logActivity(title, desc, icon = 'info', iconClass = 'kpi-icon-teal') {
    const data = this.get();
    const newAct = {
      id: Date.now(),
      time: 'Baru saja',
      title,
      desc,
      icon,
      iconClass
    };
    data.activities.unshift(newAct);
    if (data.activities.length > 8) data.activities.pop();
    this.save(data);
  }
};

function showToast(message, type = 'success') {
  let container = document.querySelector('.admin-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'admin-toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `admin-toast ${type}`;

  const iconName =
    type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';

  toast.innerHTML = `
    <span class="material-symbols-outlined" style="color: #FFFFFF; font-size: 24px;">${iconName}</span>
    <span class="admin-toast-text">${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
}

document.addEventListener('click', (e) => {
  if (e.target.classList.contains('admin-modal-overlay')) {
    e.target.classList.remove('open');
    document.body.style.overflow = '';
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const openModals = document.querySelectorAll('.admin-modal-overlay.open');
    openModals.forEach((m) => m.classList.remove('open'));
    document.body.style.overflow = '';
  }
});

function initAdminAuthSync() {
  const adminNameDisplays = document.querySelectorAll('.admin-display-name');
  const logoutBtn = document.getElementById('adminLogoutBtn');

  
  let currentAuth = null;
  try {
    currentAuth = JSON.parse(sessionStorage.getItem(STORAGE_KEY_AUTH));
  } catch (err) {}

  if (currentAuth && currentAuth.username) {
    adminNameDisplays.forEach((el) => {
      el.textContent = currentAuth.username.toUpperCase() === 'ADMIN' ? 'Atmind Admin' : currentAuth.username;
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      try {
        sessionStorage.removeItem(STORAGE_KEY_AUTH);
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

  
  const mobileToggle = document.querySelector('.mobile-nav-toggle');
  const navMenu = document.querySelector('.admin-nav-menu');
  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = navMenu.style.display === 'flex';
      navMenu.style.display = isOpen ? 'none' : 'flex';
      if (!isOpen) {
        navMenu.style.flexDirection = 'column';
        navMenu.style.position = 'absolute';
        navMenu.style.top = '80px';
        navMenu.style.left = '20px';
        navMenu.style.right = '20px';
        navMenu.style.background = '#FFFFFF';
        navMenu.style.padding = '16px';
        navMenu.style.borderRadius = '12px';
        navMenu.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        navMenu.style.zIndex = '100';
      }
    });
  }
}

function initDashboardPage() {
  const store = AdminStore.get();

  
  const kpiSiswa = document.getElementById('kpiTotalSiswa');
  const kpiSekolah = document.getElementById('kpiTotalSekolah');
  const kpiSppg = document.getElementById('kpiTotalSppg');
  const kpiStaf = document.getElementById('kpiTotalStaf');

  if (kpiSiswa) kpiSiswa.textContent = Number(12482).toLocaleString('id-ID'); 
  if (kpiSekolah) kpiSekolah.textContent = store.schools.length;
  if (kpiSppg) kpiSppg.textContent = store.sppg.length;
  if (kpiStaf) kpiStaf.textContent = store.users.filter((u) => u.role === 'Admin').length + 20;

  
  const activityList = document.getElementById('dashboardActivityList');
  if (activityList) {
    activityList.innerHTML = store.activities
      .map(
        (act) => `
        <li class="activity-item">
          <div class="activity-icon-wrapper ${act.iconClass || 'kpi-icon-teal'}">
            <span class="material-symbols-outlined">${act.icon || 'notifications'}</span>
          </div>
          <div class="activity-body">
            <h4 class="activity-title">${act.title}</h4>
            <p class="activity-desc">${act.desc}</p>
            <span class="activity-time">
              <span class="material-symbols-outlined" style="font-size: 13px;">schedule</span>
              <span>${act.time}</span>
            </span>
          </div>
        </li>
      `
      )
      .join('');
  }

  
  const tableBody = document.getElementById('dashboardSchoolsTableBody');
  if (tableBody) {
    tableBody.innerHTML = store.schools
      .slice(0, 5)
      .map((sch, idx) => {
        const sppg = AdminStore.getSppg(sch.sppgId);
        const sppgName = sppg ? sppg.name : 'Belum Ditugaskan';
        const studentCount = AdminStore.countStudentsInSchool(sch.id);
        const statusClass =
          sch.status === 'Aktif' ? 'badge-status-active' : 'badge-status-process';

        return `
        <tr>
          <td class="col-no">${idx + 1}</td>
          <td>
            <div class="cell-title">${sch.name}</div>
            <div class="cell-sub">NPSN: ${sch.npsn}</div>
          </td>
          <td>${sch.regency}</td>
          <td>
            <span class="badge badge-primary">${studentCount > 0 ? studentCount * 120 : 850} Siswa</span>
          </td>
          <td>
            <div class="cell-title" style="font-size: 13px;">${sppgName}</div>
          </td>
          <td>
            <span class="badge-status ${statusClass}">${sch.status}</span>
          </td>
          <td class="col-actions">
            <a href="schools.html" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px;">
              <span>Detail</span>
            </a>
          </td>
        </tr>
      `;
      })
      .join('');
  }
}

function initStudentsPage() {
  let state = {
    search: '',
    schoolFilter: '',
    menuFilter: '',
    currentPage: 1,
    pageSize: 8,
    activeEditId: null,
    deleteTargetId: null
  };

  const searchInput = document.getElementById('studentSearchInput');
  const schoolFilter = document.getElementById('studentSchoolFilter');
  const menuFilter = document.getElementById('studentMenuFilter');
  const tableBody = document.getElementById('studentsTableBody');
  const paginationControls = document.getElementById('studentPaginationControls');
  const paginationInfo = document.getElementById('studentPaginationInfo');
  const addBtn = document.getElementById('btnAddStudent');
  const modalForm = document.getElementById('studentForm');
  const deleteConfirmBtn = document.getElementById('btnConfirmDeleteStudent');

  
  function populateSchoolSelects() {
    const store = AdminStore.get();
    const options = store.schools
      .map((s) => `<option value="${s.id}">${s.name}</option>`)
      .join('');

    if (schoolFilter) {
      schoolFilter.innerHTML = `<option value="">Semua Sekolah</option>` + options;
    }

    const formSchoolSelect = document.getElementById('studentFormSchool');
    if (formSchoolSelect) {
      formSchoolSelect.innerHTML = `<option value="" disabled selected>Pilih Sekolah</option>` + options;
    }
  }

  
  function updateRecapKPI() {
    const store = AdminStore.get();
    const total = store.students.length;
    const reguler = store.students.filter((s) => s.menuType === 'Reguler').length;
    const khusus = store.students.filter((s) => s.menuType === 'Menu Khusus').length;

    const elTotal = document.getElementById('recapTotalSiswa');
    const elReguler = document.getElementById('recapMenuReguler');
    const elKhusus = document.getElementById('recapMenuKhusus');

    if (elTotal) elTotal.textContent = total;
    if (elReguler) elReguler.textContent = `${reguler} (${Math.round((reguler / (total || 1)) * 100)}%)`;
    if (elKhusus) elKhusus.textContent = `${khusus} (${Math.round((khusus / (total || 1)) * 100)}%)`;
  }

  
  function render() {
    const store = AdminStore.get();
    let filtered = store.students.filter((s) => {
      const matchSearch =
        s.name.toLowerCase().includes(state.search.toLowerCase()) ||
        s.nisn.includes(state.search) ||
        s.class.toLowerCase().includes(state.search.toLowerCase());
      const matchSchool = state.schoolFilter ? s.schoolId === Number(state.schoolFilter) : true;
      const matchMenu = state.menuFilter ? s.menuType === state.menuFilter : true;
      return matchSearch && matchSchool && matchMenu;
    });

    const totalItems = filtered.length;
    const totalPages = Math.ceil(totalItems / state.pageSize) || 1;
    if (state.currentPage > totalPages) state.currentPage = totalPages;

    const startIndex = (state.currentPage - 1) * state.pageSize;
    const pagedItems = filtered.slice(startIndex, startIndex + state.pageSize);

    if (tableBody) {
      if (pagedItems.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="9" style="text-align: center; padding: 40px; color: var(--color-muted);">
              <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px;">search_off</span>
              Tidak ada data siswa yang cocok dengan filter atau pencarian.
            </td>
          </tr>
        `;
      } else {
        tableBody.innerHTML = pagedItems
          .map((st, i) => {
            const school = AdminStore.getSchool(st.schoolId);
            const schoolName = school ? school.name : 'Sekolah Tidak Ditemukan';
            const statusClass =
              st.status === 'Aktif' ? 'badge-status-active' : 'badge-status-inactive';
            const menuBadge =
              st.menuType === 'Reguler'
                ? `<span class="badge badge-primary">Reguler</span>`
                : `<span class="badge badge-accent">Menu Khusus</span>`;
            const allergyDisplay =
              st.allergies && st.allergies !== '-'
                ? `<span class="badge-allergy"><span class="material-symbols-outlined" style="font-size: 13px;">warning</span>${st.allergies}</span>`
                : `<span class="badge-allergy-safe">Tidak ada alergi</span>`;

            return `
            <tr>
              <td class="col-no">${startIndex + i + 1}</td>
              <td style="font-family: monospace; font-weight: 600;">${st.nisn}</td>
              <td>
                <div class="cell-title">${st.name}</div>
              </td>
              <td>
                <div class="cell-title" style="font-size: 13px;">${schoolName}</div>
              </td>
              <td><strong>${st.class}</strong></td>
              <td>${menuBadge}</td>
              <td>${allergyDisplay}</td>
              <td><span class="badge-status ${statusClass}">${st.status}</span></td>
              <td class="col-actions">
                <div class="btn-action-group">
                  <button type="button" class="btn-icon-action" data-edit-id="${st.id}" title="Edit Siswa">
                    <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                  </button>
                  <button type="button" class="btn-icon-action delete" data-delete-id="${st.id}" title="Hapus Siswa">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          `;
          })
          .join('');
      }
    }

    
    if (paginationInfo) {
      const from = totalItems === 0 ? 0 : startIndex + 1;
      const to = Math.min(startIndex + state.pageSize, totalItems);
      paginationInfo.textContent = `Menampilkan ${from}–${to} dari ${totalItems} siswa`;
    }

    if (paginationControls) {
      let btns = `
        <button type="button" class="btn-page" ${state.currentPage === 1 ? 'disabled' : ''} id="btnPrevPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span>
        </button>
      `;
      for (let p = 1; p <= totalPages; p++) {
        btns += `
          <button type="button" class="btn-page ${p === state.currentPage ? 'active' : ''}" data-page="${p}">${p}</button>
        `;
      }
      btns += `
        <button type="button" class="btn-page" ${state.currentPage === totalPages ? 'disabled' : ''} id="btnNextPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
        </button>
      `;
      paginationControls.innerHTML = btns;
    }
  }

  
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.search = e.target.value.trim();
      state.currentPage = 1;
      render();
    });
  }

  if (schoolFilter) {
    schoolFilter.addEventListener('change', (e) => {
      state.schoolFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (menuFilter) {
    menuFilter.addEventListener('change', (e) => {
      state.menuFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (paginationControls) {
    paginationControls.addEventListener('click', (e) => {
      const pageBtn = e.target.closest('[data-page]');
      if (pageBtn) {
        state.currentPage = Number(pageBtn.dataset.page);
        render();
        return;
      }
      if (e.target.closest('#btnPrevPage') && state.currentPage > 1) {
        state.currentPage--;
        render();
        return;
      }
      if (e.target.closest('#btnNextPage')) {
        const store = AdminStore.get();
        const totalItems = store.students.length;
        if (state.currentPage * state.pageSize < totalItems) {
          state.currentPage++;
          render();
        }
      }
    });
  }

  
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      state.activeEditId = null;
      document.getElementById('modalStudentTitle').textContent = 'Tambah Siswa Baru';
      modalForm.reset();
      document.getElementById('studentFormId').value = '';
      openModal('modalStudent');
    });
  }

  
  if (tableBody) {
    tableBody.addEventListener('click', (e) => {
      const editBtn = e.target.closest('[data-edit-id]');
      if (editBtn) {
        const id = Number(editBtn.dataset.editId);
        state.activeEditId = id;
        const store = AdminStore.get();
        const student = store.students.find((s) => s.id === id);
        if (student) {
          document.getElementById('modalStudentTitle').textContent = 'Ubah Data Siswa';
          document.getElementById('studentFormId').value = student.id;
          document.getElementById('studentFormNisn').value = student.nisn;
          document.getElementById('studentFormName').value = student.name;
          document.getElementById('studentFormSchool').value = student.schoolId;
          document.getElementById('studentFormClass').value = student.class;
          document.getElementById('studentFormMenuType').value = student.menuType;
          document.getElementById('studentFormAllergies').value =
            student.allergies === '-' ? '' : student.allergies;
          document.getElementById('studentFormStatus').value = student.status;
          openModal('modalStudent');
        }
        return;
      }

      const deleteBtn = e.target.closest('[data-delete-id]');
      if (deleteBtn) {
        const id = Number(deleteBtn.dataset.deleteId);
        state.deleteTargetId = id;
        const store = AdminStore.get();
        const student = store.students.find((s) => s.id === id);
        if (student) {
          document.getElementById('deleteStudentName').textContent = `${student.name} (NISN: ${student.nisn})`;
          openModal('modalDeleteStudent');
        }
      }
    });
  }

  
  if (modalForm) {
    modalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const nisn = document.getElementById('studentFormNisn').value.trim();
      const name = document.getElementById('studentFormName').value.trim();
      const schoolId = Number(document.getElementById('studentFormSchool').value);
      const studentClass = document.getElementById('studentFormClass').value.trim();
      const menuType = document.getElementById('studentFormMenuType').value;
      const allergies = document.getElementById('studentFormAllergies').value.trim() || '-';
      const status = document.getElementById('studentFormStatus').value;

      if (!nisn || !name || !schoolId || !studentClass) {
        showToast('Mohon lengkapi seluruh field wajib!', 'error');
        return;
      }

      const store = AdminStore.get();
      if (state.activeEditId) {
        
        const idx = store.students.findIndex((s) => s.id === state.activeEditId);
        if (idx !== -1) {
          store.students[idx] = {
            ...store.students[idx],
            nisn,
            name,
            schoolId,
            class: studentClass,
            menuType,
            allergies,
            status
          };
          AdminStore.save(store);
          AdminStore.logActivity(
            'Data Siswa Diperbarui',
            `Data siswa ${name} (${studentClass}) berhasil diperbarui oleh admin.`,
            'edit'
          );
          showToast(`Data siswa "${name}" berhasil diperbarui!`, 'success');
        }
      } else {
        
        const newStudent = {
          id: Date.now(),
          nisn,
          name,
          schoolId,
          class: studentClass,
          menuType,
          allergies,
          status
        };
        store.students.unshift(newStudent);
        AdminStore.save(store);
        AdminStore.logActivity(
          'Siswa Baru Ditambahkan',
          `Siswa baru ${name} (${studentClass}) berhasil didaftarkan.`,
          'person_add'
        );
        showToast(`Siswa "${name}" berhasil ditambahkan!`, 'success');
      }

      closeModal('modalStudent');
      updateRecapKPI();
      render();
    });
  }

  
  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
      if (!state.deleteTargetId) return;
      const store = AdminStore.get();
      const target = store.students.find((s) => s.id === state.deleteTargetId);
      store.students = store.students.filter((s) => s.id !== state.deleteTargetId);
      AdminStore.save(store);

      if (target) {
        AdminStore.logActivity(
          'Data Siswa Dihapus',
          `Siswa ${target.name} dihapus dari sistem oleh admin.`,
          'delete',
          'kpi-icon-accent'
        );
      }

      showToast('Data siswa berhasil dihapus!', 'info');
      closeModal('modalDeleteStudent');
      state.deleteTargetId = null;
      updateRecapKPI();
      render();
    });
  }

  populateSchoolSelects();
  updateRecapKPI();
  render();
}

function initSchoolsPage() {
  let state = {
    search: '',
    regencyFilter: '',
    statusFilter: '',
    currentPage: 1,
    pageSize: 6,
    activeEditId: null,
    deleteTargetId: null
  };

  const searchInput = document.getElementById('schoolSearchInput');
  const regencyFilter = document.getElementById('schoolRegencyFilter');
  const statusFilter = document.getElementById('schoolStatusFilter');
  const tableBody = document.getElementById('schoolsTableBody');
  const paginationControls = document.getElementById('schoolPaginationControls');
  const paginationInfo = document.getElementById('schoolPaginationInfo');
  const addBtn = document.getElementById('btnAddSchool');
  const modalForm = document.getElementById('schoolForm');
  const deleteConfirmBtn = document.getElementById('btnConfirmDeleteSchool');

  function populateSppgSelect() {
    const store = AdminStore.get();
    const sppgSelect = document.getElementById('schoolFormSppg');
    if (sppgSelect) {
      sppgSelect.innerHTML =
        `<option value="" disabled selected>Pilih Sentra SPPG Mitra</option>` +
        store.sppg.map((sp) => `<option value="${sp.id}">${sp.name} (${sp.regency})</option>`).join('');
    }
  }

  function updateKPI() {
    const store = AdminStore.get();
    const total = store.schools.length;
    const active = store.schools.filter((s) => s.status === 'Aktif').length;

    const elTotal = document.getElementById('kpiTotalSekolahPage');
    const elActive = document.getElementById('kpiSekolahAktif');
    if (elTotal) elTotal.textContent = total;
    if (elActive) elActive.textContent = `${active} Terlayani`;
  }

  function render() {
    const store = AdminStore.get();
    let filtered = store.schools.filter((s) => {
      const matchSearch =
        s.name.toLowerCase().includes(state.search.toLowerCase()) ||
        s.npsn.includes(state.search) ||
        s.address.toLowerCase().includes(state.search.toLowerCase());
      const matchReg = state.regencyFilter ? s.regency === state.regencyFilter : true;
      const matchStat = state.statusFilter ? s.status === state.statusFilter : true;
      return matchSearch && matchReg && matchStat;
    });

    const totalItems = filtered.length;
    const totalPages = Math.ceil(totalItems / state.pageSize) || 1;
    if (state.currentPage > totalPages) state.currentPage = totalPages;

    const startIndex = (state.currentPage - 1) * state.pageSize;
    const pagedItems = filtered.slice(startIndex, startIndex + state.pageSize);

    if (tableBody) {
      if (pagedItems.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 40px; color: var(--color-muted);">
              <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px;">domain_disabled</span>
              Tidak ada data sekolah yang cocok.
            </td>
          </tr>
        `;
      } else {
        tableBody.innerHTML = pagedItems
          .map((sch, i) => {
            const sppg = AdminStore.getSppg(sch.sppgId);
            const sppgName = sppg ? sppg.name : 'Belum Ditugaskan';
            const studentCount = AdminStore.countStudentsInSchool(sch.id);
            const statusClass =
              sch.status === 'Aktif' ? 'badge-status-active' : 'badge-status-process';

            return `
            <tr>
              <td class="col-no">${startIndex + i + 1}</td>
              <td>
                <div class="cell-title">${sch.name}</div>
                <div class="cell-sub">NPSN: ${sch.npsn} &bull; ${sch.address}</div>
              </td>
              <td><strong>${sch.regency}</strong></td>
              <td>
                <span class="badge badge-primary">${studentCount > 0 ? studentCount * 120 : 850} Siswa</span>
              </td>
              <td>
                <div class="cell-title" style="font-size: 13px;">${sppgName}</div>
              </td>
              <td><span class="badge-status ${statusClass}">${sch.status}</span></td>
              <td class="col-actions">
                <div class="btn-action-group">
                  <button type="button" class="btn-icon-action" data-edit-id="${sch.id}" title="Edit Sekolah">
                    <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                  </button>
                  <button type="button" class="btn-icon-action delete" data-delete-id="${sch.id}" title="Hapus Sekolah">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          `;
          })
          .join('');
      }
    }

    if (paginationInfo) {
      const from = totalItems === 0 ? 0 : startIndex + 1;
      const to = Math.min(startIndex + state.pageSize, totalItems);
      paginationInfo.textContent = `Menampilkan ${from}–${to} dari ${totalItems} sekolah`;
    }

    if (paginationControls) {
      let btns = `
        <button type="button" class="btn-page" ${state.currentPage === 1 ? 'disabled' : ''} id="btnPrevPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span>
        </button>
      `;
      for (let p = 1; p <= totalPages; p++) {
        btns += `
          <button type="button" class="btn-page ${p === state.currentPage ? 'active' : ''}" data-page="${p}">${p}</button>
        `;
      }
      btns += `
        <button type="button" class="btn-page" ${state.currentPage === totalPages ? 'disabled' : ''} id="btnNextPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
        </button>
      `;
      paginationControls.innerHTML = btns;
    }
  }

  
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.search = e.target.value.trim();
      state.currentPage = 1;
      render();
    });
  }

  if (regencyFilter) {
    regencyFilter.addEventListener('change', (e) => {
      state.regencyFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', (e) => {
      state.statusFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (paginationControls) {
    paginationControls.addEventListener('click', (e) => {
      const pageBtn = e.target.closest('[data-page]');
      if (pageBtn) {
        state.currentPage = Number(pageBtn.dataset.page);
        render();
        return;
      }
      if (e.target.closest('#btnPrevPage') && state.currentPage > 1) {
        state.currentPage--;
        render();
        return;
      }
      if (e.target.closest('#btnNextPage')) {
        const store = AdminStore.get();
        if (state.currentPage * state.pageSize < store.schools.length) {
          state.currentPage++;
          render();
        }
      }
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', () => {
      state.activeEditId = null;
      document.getElementById('modalSchoolTitle').textContent = 'Tambah Sekolah Mitra';
      modalForm.reset();
      document.getElementById('schoolFormId').value = '';
      openModal('modalSchool');
    });
  }

  if (tableBody) {
    tableBody.addEventListener('click', (e) => {
      const editBtn = e.target.closest('[data-edit-id]');
      if (editBtn) {
        const id = Number(editBtn.dataset.editId);
        state.activeEditId = id;
        const store = AdminStore.get();
        const school = store.schools.find((s) => s.id === id);
        if (school) {
          document.getElementById('modalSchoolTitle').textContent = 'Ubah Data Sekolah';
          document.getElementById('schoolFormId').value = school.id;
          document.getElementById('schoolFormNpsn').value = school.npsn;
          document.getElementById('schoolFormName').value = school.name;
          document.getElementById('schoolFormRegency').value = school.regency;
          document.getElementById('schoolFormSppg').value = school.sppgId;
          document.getElementById('schoolFormAddress').value = school.address;
          document.getElementById('schoolFormStatus').value = school.status;
          openModal('modalSchool');
        }
        return;
      }

      const deleteBtn = e.target.closest('[data-delete-id]');
      if (deleteBtn) {
        const id = Number(deleteBtn.dataset.deleteId);
        state.deleteTargetId = id;
        const store = AdminStore.get();
        const school = store.schools.find((s) => s.id === id);
        if (school) {
          document.getElementById('deleteSchoolName').textContent = `${school.name} (NPSN: ${school.npsn})`;
          openModal('modalDeleteSchool');
        }
      }
    });
  }

  if (modalForm) {
    modalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const npsn = document.getElementById('schoolFormNpsn').value.trim();
      const name = document.getElementById('schoolFormName').value.trim();
      const regency = document.getElementById('schoolFormRegency').value;
      const sppgId = Number(document.getElementById('schoolFormSppg').value);
      const address = document.getElementById('schoolFormAddress').value.trim();
      const status = document.getElementById('schoolFormStatus').value;

      if (!npsn || !name || !regency || !sppgId) {
        showToast('Mohon lengkapi seluruh field wajib!', 'error');
        return;
      }

      const store = AdminStore.get();
      if (state.activeEditId) {
        const idx = store.schools.findIndex((s) => s.id === state.activeEditId);
        if (idx !== -1) {
          store.schools[idx] = {
            ...store.schools[idx],
            npsn,
            name,
            regency,
            sppgId,
            address,
            status
          };
          AdminStore.save(store);
          AdminStore.logActivity('Data Sekolah Diperbarui', `Sekolah ${name} diperbarui oleh admin.`, 'edit');
          showToast(`Sekolah "${name}" berhasil diperbarui!`, 'success');
        }
      } else {
        const newSchool = {
          id: Date.now(),
          npsn,
          name,
          regency,
          sppgId,
          address,
          status
        };
        store.schools.unshift(newSchool);
        AdminStore.save(store);
        AdminStore.logActivity('Sekolah Baru Ditambahkan', `Sekolah ${name} berhasil didaftarkan ke sistem.`, 'domain_add');
        showToast(`Sekolah "${name}" berhasil didaftarkan!`, 'success');
      }

      closeModal('modalSchool');
      updateKPI();
      render();
    });
  }

  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
      if (!state.deleteTargetId) return;
      const store = AdminStore.get();
      const target = store.schools.find((s) => s.id === state.deleteTargetId);
      store.schools = store.schools.filter((s) => s.id !== state.deleteTargetId);
      AdminStore.save(store);

      if (target) {
        AdminStore.logActivity('Sekolah Dihapus', `Sekolah ${target.name} dihapus dari data sistem.`, 'delete');
      }

      showToast('Data sekolah berhasil dihapus!', 'info');
      closeModal('modalDeleteSchool');
      state.deleteTargetId = null;
      updateKPI();
      render();
    });
  }

  populateSppgSelect();
  updateKPI();
  render();
}

function initSppgPage() {
  let state = {
    search: '',
    regencyFilter: '',
    statusFilter: '',
    currentPage: 1,
    pageSize: 6,
    activeEditId: null,
    deleteTargetId: null
  };

  const searchInput = document.getElementById('sppgSearchInput');
  const regencyFilter = document.getElementById('sppgRegencyFilter');
  const statusFilter = document.getElementById('sppgStatusFilter');
  const tableBody = document.getElementById('sppgTableBody');
  const paginationControls = document.getElementById('sppgPaginationControls');
  const paginationInfo = document.getElementById('sppgPaginationInfo');
  const addBtn = document.getElementById('btnAddSppg');
  const modalForm = document.getElementById('sppgForm');
  const deleteConfirmBtn = document.getElementById('btnConfirmDeleteSppg');

  function updateKPI() {
    const store = AdminStore.get();
    const total = store.sppg.length;
    const totalCapacity = store.sppg.reduce((acc, curr) => acc + (curr.capacity || 0), 0);

    const elTotal = document.getElementById('kpiTotalSppgPage');
    const elCapacity = document.getElementById('kpiKapasitasPorsi');

    if (elTotal) elTotal.textContent = total;
    if (elCapacity) elCapacity.textContent = `${totalCapacity.toLocaleString('id-ID')} Porsi/Hari`;
  }

  function render() {
    const store = AdminStore.get();
    let filtered = store.sppg.filter((sp) => {
      const matchSearch =
        sp.name.toLowerCase().includes(state.search.toLowerCase()) ||
        sp.code.toLowerCase().includes(state.search.toLowerCase()) ||
        sp.address.toLowerCase().includes(state.search.toLowerCase());
      const matchReg = state.regencyFilter ? sp.regency === state.regencyFilter : true;
      const matchStat = state.statusFilter ? sp.status === state.statusFilter : true;
      return matchSearch && matchReg && matchStat;
    });

    const totalItems = filtered.length;
    const totalPages = Math.ceil(totalItems / state.pageSize) || 1;
    if (state.currentPage > totalPages) state.currentPage = totalPages;

    const startIndex = (state.currentPage - 1) * state.pageSize;
    const pagedItems = filtered.slice(startIndex, startIndex + state.pageSize);

    if (tableBody) {
      if (pagedItems.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 40px; color: var(--color-muted);">
              <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px;">kitchen</span>
              Tidak ada data SPPG yang cocok.
            </td>
          </tr>
        `;
      } else {
        tableBody.innerHTML = pagedItems
          .map((sp, i) => {
            const schoolCount = AdminStore.countSchoolsInSppg(sp.id);
            const statusClass =
              sp.status === 'Aktif' ? 'badge-status-active' : 'badge-status-process';

            return `
            <tr>
              <td class="col-no">${startIndex + i + 1}</td>
              <td>
                <div class="cell-title">${sp.name}</div>
                <div class="cell-sub">Kode: ${sp.code} &bull; PJ: ${sp.contactPerson || '-'} (${sp.phone || '-'})</div>
              </td>
              <td><strong>${sp.regency}</strong></td>
              <td style="max-width: 200px; font-size: 13px;">${sp.address}</td>
              <td>
                <strong>${Number(sp.capacity).toLocaleString('id-ID')}</strong> <span class="cell-sub">porsi/hari</span>
              </td>
              <td>
                <span class="badge badge-primary">${schoolCount} Sekolah</span>
              </td>
              <td><span class="badge-status ${statusClass}">${sp.status}</span></td>
              <td class="col-actions">
                <div class="btn-action-group">
                  <button type="button" class="btn-icon-action" data-edit-id="${sp.id}" title="Edit SPPG">
                    <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                  </button>
                  <button type="button" class="btn-icon-action delete" data-delete-id="${sp.id}" title="Hapus SPPG">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          `;
          })
          .join('');
      }
    }

    if (paginationInfo) {
      const from = totalItems === 0 ? 0 : startIndex + 1;
      const to = Math.min(startIndex + state.pageSize, totalItems);
      paginationInfo.textContent = `Menampilkan ${from}–${to} dari ${totalItems} unit SPPG`;
    }

    if (paginationControls) {
      let btns = `
        <button type="button" class="btn-page" ${state.currentPage === 1 ? 'disabled' : ''} id="btnPrevPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span>
        </button>
      `;
      for (let p = 1; p <= totalPages; p++) {
        btns += `
          <button type="button" class="btn-page ${p === state.currentPage ? 'active' : ''}" data-page="${p}">${p}</button>
        `;
      }
      btns += `
        <button type="button" class="btn-page" ${state.currentPage === totalPages ? 'disabled' : ''} id="btnNextPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
        </button>
      `;
      paginationControls.innerHTML = btns;
    }
  }

  
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.search = e.target.value.trim();
      state.currentPage = 1;
      render();
    });
  }

  if (regencyFilter) {
    regencyFilter.addEventListener('change', (e) => {
      state.regencyFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', (e) => {
      state.statusFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (paginationControls) {
    paginationControls.addEventListener('click', (e) => {
      const pageBtn = e.target.closest('[data-page]');
      if (pageBtn) {
        state.currentPage = Number(pageBtn.dataset.page);
        render();
        return;
      }
      if (e.target.closest('#btnPrevPage') && state.currentPage > 1) {
        state.currentPage--;
        render();
        return;
      }
      if (e.target.closest('#btnNextPage')) {
        const store = AdminStore.get();
        if (state.currentPage * state.pageSize < store.sppg.length) {
          state.currentPage++;
          render();
        }
      }
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', () => {
      state.activeEditId = null;
      document.getElementById('modalSppgTitle').textContent = 'Tambah Unit SPPG';
      modalForm.reset();
      document.getElementById('sppgFormId').value = '';
      openModal('modalSppg');
    });
  }

  if (tableBody) {
    tableBody.addEventListener('click', (e) => {
      const editBtn = e.target.closest('[data-edit-id]');
      if (editBtn) {
        const id = Number(editBtn.dataset.editId);
        state.activeEditId = id;
        const store = AdminStore.get();
        const sp = store.sppg.find((item) => item.id === id);
        if (sp) {
          document.getElementById('modalSppgTitle').textContent = 'Ubah Unit SPPG';
          document.getElementById('sppgFormId').value = sp.id;
          document.getElementById('sppgFormCode').value = sp.code;
          document.getElementById('sppgFormName').value = sp.name;
          document.getElementById('sppgFormRegency').value = sp.regency;
          document.getElementById('sppgFormCapacity').value = sp.capacity;
          document.getElementById('sppgFormContact').value = sp.contactPerson || '';
          document.getElementById('sppgFormPhone').value = sp.phone || '';
          document.getElementById('sppgFormAddress').value = sp.address;
          document.getElementById('sppgFormStatus').value = sp.status;
          openModal('modalSppg');
        }
        return;
      }

      const deleteBtn = e.target.closest('[data-delete-id]');
      if (deleteBtn) {
        const id = Number(deleteBtn.dataset.deleteId);
        state.deleteTargetId = id;
        const store = AdminStore.get();
        const sp = store.sppg.find((item) => item.id === id);
        if (sp) {
          document.getElementById('deleteSppgName').textContent = `${sp.name} (${sp.code})`;
          openModal('modalDeleteSppg');
        }
      }
    });
  }

  if (modalForm) {
    modalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const code = document.getElementById('sppgFormCode').value.trim();
      const name = document.getElementById('sppgFormName').value.trim();
      const regency = document.getElementById('sppgFormRegency').value;
      const capacity = Number(document.getElementById('sppgFormCapacity').value);
      const contactPerson = document.getElementById('sppgFormContact').value.trim();
      const phone = document.getElementById('sppgFormPhone').value.trim();
      const address = document.getElementById('sppgFormAddress').value.trim();
      const status = document.getElementById('sppgFormStatus').value;

      if (!code || !name || !regency || !capacity) {
        showToast('Mohon lengkapi seluruh field wajib!', 'error');
        return;
      }

      const store = AdminStore.get();
      if (state.activeEditId) {
        const idx = store.sppg.findIndex((s) => s.id === state.activeEditId);
        if (idx !== -1) {
          store.sppg[idx] = {
            ...store.sppg[idx],
            code,
            name,
            regency,
            capacity,
            contactPerson,
            phone,
            address,
            status
          };
          AdminStore.save(store);
          AdminStore.logActivity('Data SPPG Diperbarui', `Unit SPPG ${name} diperbarui oleh admin.`, 'edit');
          showToast(`Unit SPPG "${name}" berhasil diperbarui!`, 'success');
        }
      } else {
        const newSppg = {
          id: Date.now(),
          code,
          name,
          regency,
          capacity,
          contactPerson,
          phone,
          address,
          status
        };
        store.sppg.unshift(newSppg);
        AdminStore.save(store);
        AdminStore.logActivity('Unit SPPG Baru Ditambahkan', `Unit SPPG ${name} (${code}) didaftarkan.`, 'restaurant');
        showToast(`Unit SPPG "${name}" berhasil didaftarkan!`, 'success');
      }

      closeModal('modalSppg');
      updateKPI();
      render();
    });
  }

  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
      if (!state.deleteTargetId) return;
      const store = AdminStore.get();
      const target = store.sppg.find((s) => s.id === state.deleteTargetId);
      store.sppg = store.sppg.filter((s) => s.id !== state.deleteTargetId);
      AdminStore.save(store);

      if (target) {
        AdminStore.logActivity('Unit SPPG Dihapus', `Unit SPPG ${target.name} dihapus dari sistem.`, 'delete');
      }

      showToast('Unit SPPG berhasil dihapus!', 'info');
      closeModal('modalDeleteSppg');
      state.deleteTargetId = null;
      updateKPI();
      render();
    });
  }

  updateKPI();
  render();
}

function initUsersPage() {
  let state = {
    search: '',
    roleFilter: '',
    statusFilter: '',
    currentPage: 1,
    pageSize: 6,
    activeEditId: null,
    deleteTargetId: null
  };

  const searchInput = document.getElementById('userSearchInput');
  const roleFilter = document.getElementById('userRoleFilter');
  const statusFilter = document.getElementById('userStatusFilter');
  const tableBody = document.getElementById('usersTableBody');
  const paginationControls = document.getElementById('userPaginationControls');
  const paginationInfo = document.getElementById('userPaginationInfo');
  const addBtn = document.getElementById('btnAddUser');
  const modalForm = document.getElementById('userForm');
  const deleteConfirmBtn = document.getElementById('btnConfirmDeleteUser');

  function updateKPI() {
    const store = AdminStore.get();
    const total = store.users.length;
    const adminCount = store.users.filter((u) => u.role === 'Admin').length;
    const sppgCount = store.users.filter((u) => u.role === 'Petugas SPPG').length;
    const siswaCount = store.users.filter((u) => u.role === 'Siswa').length;

    const elTotal = document.getElementById('kpiTotalUsersPage');
    const elAdmin = document.getElementById('kpiUserAdmin');
    const elSppg = document.getElementById('kpiUserSppg');
    const elSiswa = document.getElementById('kpiUserSiswa');

    if (elTotal) elTotal.textContent = total;
    if (elAdmin) elAdmin.textContent = adminCount;
    if (elSppg) elSppg.textContent = sppgCount;
    if (elSiswa) elSiswa.textContent = siswaCount;
  }

  function render() {
    const store = AdminStore.get();
    let filtered = store.users.filter((u) => {
      const matchSearch =
        u.name.toLowerCase().includes(state.search.toLowerCase()) ||
        u.username.toLowerCase().includes(state.search.toLowerCase()) ||
        u.email.toLowerCase().includes(state.search.toLowerCase()) ||
        u.institution.toLowerCase().includes(state.search.toLowerCase());
      const matchRole = state.roleFilter ? u.role === state.roleFilter : true;
      const matchStat = state.statusFilter ? u.status === state.statusFilter : true;
      return matchSearch && matchRole && matchStat;
    });

    const totalItems = filtered.length;
    const totalPages = Math.ceil(totalItems / state.pageSize) || 1;
    if (state.currentPage > totalPages) state.currentPage = totalPages;

    const startIndex = (state.currentPage - 1) * state.pageSize;
    const pagedItems = filtered.slice(startIndex, startIndex + state.pageSize);

    if (tableBody) {
      if (pagedItems.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 40px; color: var(--color-muted);">
              <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px;">person_off</span>
              Tidak ada data pengguna yang cocok.
            </td>
          </tr>
        `;
      } else {
        tableBody.innerHTML = pagedItems
          .map((u, i) => {
            const statusClass =
              u.status === 'Aktif' ? 'badge-status-active' : 'badge-status-inactive';
            const roleBadgeClass =
              u.role === 'Admin'
                ? 'badge-role-admin'
                : u.role === 'Petugas SPPG'
                ? 'badge-role-sppg'
                : 'badge-role-siswa';

            return `
            <tr>
              <td class="col-no">${startIndex + i + 1}</td>
              <td>
                <div class="cell-title">${u.name}</div>
                <div class="cell-sub">${u.email}</div>
              </td>
              <td><span style="font-family: monospace; font-weight: 700;">@${u.username}</span></td>
              <td><span class="badge ${roleBadgeClass}">${u.role}</span></td>
              <td>${u.institution}</td>
              <td><span class="badge-status ${statusClass}">${u.status}</span></td>
              <td class="col-actions">
                <div class="btn-action-group">
                  <button type="button" class="btn-icon-action" data-edit-id="${u.id}" title="Edit Pengguna">
                    <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                  </button>
                  <button type="button" class="btn-icon-action delete" data-delete-id="${u.id}" title="Hapus Pengguna">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          `;
          })
          .join('');
      }
    }

    if (paginationInfo) {
      const from = totalItems === 0 ? 0 : startIndex + 1;
      const to = Math.min(startIndex + state.pageSize, totalItems);
      paginationInfo.textContent = `Menampilkan ${from}–${to} dari ${totalItems} pengguna`;
    }

    if (paginationControls) {
      let btns = `
        <button type="button" class="btn-page" ${state.currentPage === 1 ? 'disabled' : ''} id="btnPrevPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span>
        </button>
      `;
      for (let p = 1; p <= totalPages; p++) {
        btns += `
          <button type="button" class="btn-page ${p === state.currentPage ? 'active' : ''}" data-page="${p}">${p}</button>
        `;
      }
      btns += `
        <button type="button" class="btn-page" ${state.currentPage === totalPages ? 'disabled' : ''} id="btnNextPage">
          <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
        </button>
      `;
      paginationControls.innerHTML = btns;
    }
  }

  
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.search = e.target.value.trim();
      state.currentPage = 1;
      render();
    });
  }

  if (roleFilter) {
    roleFilter.addEventListener('change', (e) => {
      state.roleFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', (e) => {
      state.statusFilter = e.target.value;
      state.currentPage = 1;
      render();
    });
  }

  if (paginationControls) {
    paginationControls.addEventListener('click', (e) => {
      const pageBtn = e.target.closest('[data-page]');
      if (pageBtn) {
        state.currentPage = Number(pageBtn.dataset.page);
        render();
        return;
      }
      if (e.target.closest('#btnPrevPage') && state.currentPage > 1) {
        state.currentPage--;
        render();
        return;
      }
      if (e.target.closest('#btnNextPage')) {
        const store = AdminStore.get();
        if (state.currentPage * state.pageSize < store.users.length) {
          state.currentPage++;
          render();
        }
      }
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', () => {
      state.activeEditId = null;
      document.getElementById('modalUserTitle').textContent = 'Tambah Pengguna Baru';
      modalForm.reset();
      document.getElementById('userFormId').value = '';
      openModal('modalUser');
    });
  }

  if (tableBody) {
    tableBody.addEventListener('click', (e) => {
      const editBtn = e.target.closest('[data-edit-id]');
      if (editBtn) {
        const id = Number(editBtn.dataset.editId);
        state.activeEditId = id;
        const store = AdminStore.get();
        const user = store.users.find((u) => u.id === id);
        if (user) {
          document.getElementById('modalUserTitle').textContent = 'Ubah Data Pengguna';
          document.getElementById('userFormId').value = user.id;
          document.getElementById('userFormName').value = user.name;
          document.getElementById('userFormUsername').value = user.username;
          document.getElementById('userFormEmail').value = user.email;
          document.getElementById('userFormRole').value = user.role;
          document.getElementById('userFormInstitution').value = user.institution;
          document.getElementById('userFormStatus').value = user.status;
          openModal('modalUser');
        }
        return;
      }

      const deleteBtn = e.target.closest('[data-delete-id]');
      if (deleteBtn) {
        const id = Number(deleteBtn.dataset.deleteId);
        state.deleteTargetId = id;
        const store = AdminStore.get();
        const user = store.users.find((u) => u.id === id);
        if (user) {
          document.getElementById('deleteUserName').textContent = `${user.name} (@${user.username})`;
          openModal('modalDeleteUser');
        }
      }
    });
  }

  if (modalForm) {
    modalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('userFormName').value.trim();
      const username = document.getElementById('userFormUsername').value.trim();
      const email = document.getElementById('userFormEmail').value.trim();
      const role = document.getElementById('userFormRole').value;
      const institution = document.getElementById('userFormInstitution').value.trim();
      const status = document.getElementById('userFormStatus').value;

      if (!name || !username || !email || !role || !institution) {
        showToast('Mohon lengkapi seluruh field wajib!', 'error');
        return;
      }

      const store = AdminStore.get();
      if (state.activeEditId) {
        const idx = store.users.findIndex((u) => u.id === state.activeEditId);
        if (idx !== -1) {
          store.users[idx] = {
            ...store.users[idx],
            name,
            username,
            email,
            role,
            institution,
            status
          };
          AdminStore.save(store);
          AdminStore.logActivity('Data Pengguna Diperbarui', `Akun @${username} diperbarui oleh admin.`, 'edit');
          showToast(`Pengguna "${name}" berhasil diperbarui!`, 'success');
        }
      } else {
        const newUser = {
          id: Date.now(),
          name,
          username,
          email,
          role,
          institution,
          status
        };
        store.users.unshift(newUser);
        AdminStore.save(store);
        AdminStore.logActivity('Pengguna Baru Ditambahkan', `Akun baru @${username} (${role}) didaftarkan.`, 'group_add');
        showToast(`Pengguna "${name}" berhasil didaftarkan!`, 'success');
      }

      closeModal('modalUser');
      updateKPI();
      render();
    });
  }

  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
      if (!state.deleteTargetId) return;
      const store = AdminStore.get();
      const target = store.users.find((u) => u.id === state.deleteTargetId);
      store.users = store.users.filter((u) => u.id !== state.deleteTargetId);
      AdminStore.save(store);

      if (target) {
        AdminStore.logActivity('Pengguna Dihapus', `Akun @${target.username} dihapus oleh admin.`, 'delete');
      }

      showToast('Data pengguna berhasil dihapus!', 'info');
      closeModal('modalDeleteUser');
      state.deleteTargetId = null;
      updateKPI();
      render();
    });
  }

  updateKPI();
  render();
}

document.addEventListener('DOMContentLoaded', () => {
  initAdminAuthSync();

  if (document.getElementById('adminDashboardPage')) {
    initDashboardPage();
  } else if (document.getElementById('adminStudentsPage')) {
    initStudentsPage();
  } else if (document.getElementById('adminSchoolsPage')) {
    initSchoolsPage();
  } else if (document.getElementById('adminSppgPage')) {
    initSppgPage();
  } else if (document.getElementById('adminUsersPage')) {
    initUsersPage();
  }
});
