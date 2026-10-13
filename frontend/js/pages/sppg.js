/**
 * ==========================================================================
 * SPPG JS - SIM-MBG
 * Panel SPPG: Beranda, Menu, Pilihan Pengguna, Review
 * Vanilla JS (mock data, tanpa library) sesuai AGENTS.md
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  initCommon();
  const id = document.body.id;
  if (id === 'sppgDashboardPage') initDashboard();
  if (id === 'sppgMenuPage') initMenuPage();
  if (id === 'sppgPilihanPage') initPilihanPage();
  if (id === 'sppgReviewPage') initReviewPage();
});

/* ------------------------------------------------------------------ */
/* HELPERS                                                             */
/* ------------------------------------------------------------------ */
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

function esc(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function fmt(n) {
  return Number(n).toLocaleString('id-ID');
}

let toastTimer;
function showToast(msg) {
  const el = $('#sppgToast');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
}

function openModal(modal) { if (modal) modal.classList.add('open'); }
function closeModal(modal) { if (modal) modal.classList.remove('open'); }

function bindModal(modal) {
  if (!modal) return;
  $$('[data-close]', modal).forEach((b) => b.addEventListener('click', () => closeModal(modal)));
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(modal); });
}

function downloadCSV(filename, rows) {
  const csv = rows.map((r) => r.map((c) => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

/** Render pagination dengan ellipsis. onChange(page) dipanggil saat klik. */
function renderPagination(container, page, totalPages, onChange) {
  if (!container) return;
  const pages = [];
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || Math.abs(i - page) <= 1) pages.push(i);
    else if (pages[pages.length - 1] !== '…') pages.push('…');
  }
  container.innerHTML =
    `<button type="button" class="page-btn" data-p="${page - 1}" ${page === 1 ? 'disabled' : ''} aria-label="Sebelumnya"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></button>` +
    pages.map((p) => p === '…'
      ? '<span class="text-muted">…</span>'
      : `<button type="button" class="page-btn ${p === page ? 'active' : ''}" data-p="${p}">${p}</button>`).join('') +
    `<button type="button" class="page-btn" data-p="${page + 1}" ${page === totalPages ? 'disabled' : ''} aria-label="Berikutnya"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></button>`;
  $$('button[data-p]:not(:disabled)', container).forEach((b) =>
    b.addEventListener('click', () => onChange(parseInt(b.dataset.p, 10))));
}

/* ------------------------------------------------------------------ */
/* COMMON: logout, tanggal, data-toast                                 */
/* ------------------------------------------------------------------ */
function initCommon() {
  const logoutBtn = $('#logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      try { sessionStorage.removeItem('sim_mbg_auth'); } catch (err) { console.warn(err); }
      window.location.href = '../auth/login.html';
    });
  }

  $$('[data-toast]').forEach((el) => el.addEventListener('click', (e) => {
    e.preventDefault();
    showToast(el.dataset.toast);
  }));

  const dateEl = $('#todayDate');
  if (dateEl) {
    dateEl.textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  }
}

/* ------------------------------------------------------------------ */
/* BERANDA                                                             */
/* ------------------------------------------------------------------ */
const ACTIVITIES = [
  { icon: 'task_alt', tone: 'ico-green', title: 'Penerimaan batch selesai • SMP Negeri 1 Denpasar', desc: '240 porsi diserahkan kepada Petugas UKS (Sdr. I Made Darmawan)', time: 'Hari ini, 09:12 WIB', pill: ['pill-green', 'Terkirim'] },
  { icon: 'directions_run', tone: 'ico-blue', title: 'Armada kurir menuju lokasi • SMP Negeri 2 Gianyar', desc: '180 porsi dalam box termal pengangkut SPPG Armada B-04', time: 'Hari ini, 08:45 WIB', pill: ['pill-blue', 'Diproses'] },
  { icon: 'rate_review', tone: 'ico-orange', title: 'Catatan rasa & suhu diterima • SD Negeri 3 Sanur', desc: 'Umpan balik dari komite gizi sekolah: Sup tersaji hangat, kemasan higienis utuh', time: 'Hari ini, 08:20 WIB', pill: ['pill-orange', 'Evaluasi Diterima'] },
  { icon: 'shield', tone: 'ico-green', title: 'Inspeksi higienitas dapur pusat disetujui', desc: 'Verifikasi harian oleh Tim Pengawas Nutrisi SPPG Wilayah I', time: 'Hari ini, 06:15 WIB', pill: ['pill-green', 'Terkirim'] }
];

const SCHOOLS = [
  { name: 'SMP Negeri 1 Denpasar', npsn: '50103120', pic: 'I Nyoman Sukra', area: 'Kota Denpasar / Denpasar Barat', qty: 542, eta: '09:00 - 09:30 WIB', status: 'terkirim' },
  { name: 'SMP Negeri 2 Denpasar', npsn: '50103121', pic: 'Ni Ketut Suartini', area: 'Kota Denpasar / Denpasar Timur', qty: 765, eta: '09:15 - 09:45 WIB', status: 'dikirim' },
  { name: 'SMP Negeri 1 Gianyar', npsn: '50102044', pic: 'Wayan Sudarta', area: 'Kabupaten Gianyar / Kota', qty: 480, eta: '09:30 - 10:00 WIB', status: 'dikirim' },
  { name: 'SMP Negeri 1 Klungkung', npsn: '50101980', pic: 'Luh Putu Aryani', area: 'Kabupaten Klungkung / Semarapura', qty: 415, eta: '10:00 - 10:30 WIB', status: 'menunggu' },
  { name: 'SMP Negeri 1 Kuta', npsn: '50101567', pic: 'Gede Wira Pratama', area: 'Kabupaten Badung / Kuta Selatan', qty: 738, eta: '09:00 - 09:30 WIB', status: 'terkirim' },
  { name: 'SMP Negeri 3 Denpasar', npsn: '50103122', pic: 'Made Sutrisna', area: 'Kota Denpasar / Denpasar Selatan', qty: 390, eta: '09:30 - 10:00 WIB', status: 'terkirim' },
  { name: 'SD Negeri 3 Sanur', npsn: '50100871', pic: 'Ni Luh Ariani', area: 'Kota Denpasar / Denpasar Selatan', qty: 210, eta: '09:45 - 10:15 WIB', status: 'dikirim' },
  { name: 'SMP Negeri 2 Mengwi', npsn: '50102310', pic: 'Komang Sudiarta', area: 'Kabupaten Badung / Mengwi', qty: 330, eta: '10:15 - 10:45 WIB', status: 'menunggu' }
];

const SCHOOL_STATUS = {
  terkirim: ['pill-green', 'Terkirim'],
  dikirim: ['pill-blue', 'Sedang Dikirim'],
  menunggu: ['pill-gray', 'Menunggu']
};

function initDashboard() {
  const list = $('#activityList');
  if (list) {
    list.innerHTML = ACTIVITIES.map((a) => `
      <li>
        <div class="sppg-ico ${a.tone}"><span class="material-symbols-outlined">${a.icon}</span></div>
        <div class="act-body"><h4>${esc(a.title)}</h4><p>${esc(a.desc)}</p><small>${esc(a.time)}</small></div>
        <span class="pill ${a.pill[0]}">${esc(a.pill[1])}</span>
      </li>`).join('');
  }

  const PER_PAGE = 5;
  let page = 1;
  let query = '';
  const body = $('#schoolTableBody');
  const info = $('#schoolInfo');
  const pag = $('#schoolPagination');

  function render() {
    const q = query.toLowerCase();
    const rows = SCHOOLS.filter((s) => !q || s.name.toLowerCase().includes(q) || s.area.toLowerCase().includes(q));
    const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    page = Math.min(page, totalPages);
    const start = (page - 1) * PER_PAGE;
    const slice = rows.slice(start, start + PER_PAGE);

    body.innerHTML = slice.length ? slice.map((s, i) => {
      const st = SCHOOL_STATUS[s.status];
      return `
      <tr>
        <td>${start + i + 1}</td>
        <td><div class="cell-title">${esc(s.name)}</div><div class="cell-sub">NPSN: ${esc(s.npsn)} • PIC: ${esc(s.pic)}</div></td>
        <td>${esc(s.area)}</td>
        <td class="num">${fmt(s.qty)}</td>
        <td>${esc(s.eta)}</td>
        <td class="ctr"><span class="pill ${st[0]}">● ${st[1]}</span></td>
        <td class="ctr"><a href="#" class="sppg-link sm" data-detail="${esc(s.name)}">Lihat Detail</a></td>
      </tr>`;
    }).join('') : '<tr><td colspan="7" class="sppg-empty">Sekolah tidak ditemukan.</td></tr>';

    info.innerHTML = rows.length
      ? `Menampilkan <b>${start + 1}</b> - <b>${start + slice.length}</b> dari <b>${rows.length}</b> sekolah penerima`
      : 'Tidak ada data';
    renderPagination(pag, page, totalPages, (p) => { page = p; render(); });

    $$('[data-detail]', body).forEach((a) => a.addEventListener('click', (e) => {
      e.preventDefault();
      showToast(`Detail distribusi ${a.dataset.detail}`);
    }));
  }

  $('#schoolSearch').addEventListener('input', (e) => { query = e.target.value.trim(); page = 1; render(); });

  $('#createReportBtn').addEventListener('click', () => {
    downloadCSV('laporan-distribusi.csv', [
      ['No', 'Sekolah', 'NPSN', 'PIC', 'Wilayah', 'Jumlah Porsi', 'Estimasi Tiba', 'Status'],
      ...SCHOOLS.map((s, i) => [i + 1, s.name, s.npsn, s.pic, s.area, s.qty, s.eta, SCHOOL_STATUS[s.status][1]])
    ]);
    showToast('Laporan distribusi diunduh');
  });

  $('#syncBtn').addEventListener('click', (e) => {
    const btn = e.currentTarget;
    const icon = $('.material-symbols-outlined', btn);
    icon.style.animation = 'spin 1s linear infinite';
    btn.disabled = true;
    setTimeout(() => {
      icon.style.animation = '';
      btn.disabled = false;
      showToast('Data berhasil disinkronkan');
    }, 1200);
  });

  render();
}

/* ------------------------------------------------------------------ */
/* MENU                                                                */
/* ------------------------------------------------------------------ */
const CAT_LABEL = { reguler: 'Reguler SD/SMP', khusus: 'Alergi Khusus', cadangan: 'Cadangan Operasional' };

const MENUS = [
  { id: 1, day: 'Senin • Minggu ke-1', cat: 'reguler', name: 'Paket Ayam Panggang Madu & Capcay Bakso', desc: 'Nasi Merah Organik, Ayam Panggang Madu 80g, Capcay Sayur Segar, Semangka Merah, Kotak Susu UHT', kcal: 630, carb: 75, prot: 26, fat: 16, tags: [['ok', 'Bebas Kacang'], ['warn', 'Mengandung Susu Sapi']], active: true, img: '' },
  { id: 2, day: 'Selasa • Minggu ke-1', cat: 'khusus', catLabel: 'Diet Alergi Seafood', name: 'Paket Semur Daging Sapi & Tumis Buncis Tempe', desc: 'Nasi Putih Pulen, Semur Daging Gandik 75g + Tahu Sutra, Tumis Buncis Tempe, Potongan Pepaya, Susu Kedelai', kcal: 620, carb: 78, prot: 28, fat: 14, tags: [['ok', 'Bebas Ikan & Udang'], ['ok', 'Bebas Laktosa Sapi']], active: true, img: '' },
  { id: 3, day: 'Rabu • Minggu ke-1', cat: 'reguler', name: 'Paket Ikan Fillet Kunyit & Sayur Bening Bayam', desc: 'Nasi Putih Pulen, Fillet Ikan Kakap Bumbu Kunyit 85g, Sayur Bening Bayam Jagung Manis, Pisang Cavendish', kcal: 595, carb: 72, prot: 27, fat: 12, tags: [['warn', 'Mengandung Ikan Laut'], ['ok', 'Bebas Telur']], active: true, img: '' },
  { id: 4, day: 'Kamis • Minggu ke-1', cat: 'khusus', catLabel: 'Bebas Gluten / Celiac', name: 'Paket Sate Ayam Lilit & Sayur Daun Kelor Jagung', desc: 'Nasi Jagung Organik, Sate Ayam Lilit Tanpa Terigu 80g, Sup Sayur Daun Kelor & Labu Kuning, Melon Hijau', kcal: 605, carb: 70, prot: 25, fat: 15, tags: [['ok', 'Bebas Gandum / Terigu'], ['ok', 'Bebas Kacang Tanah']], active: true, img: '' },
  { id: 5, day: 'Jumat • Minggu ke-1', cat: 'reguler', name: 'Paket Rolade Daging Telur & Tumis Brokoli', desc: 'Nasi Merah, Rolade Sapi Isi Telur Rebus 85g, Tumis Brokoli Wortel Saus Tiram Ringan, Pir Hijau Segar', kcal: 635, carb: 74, prot: 29, fat: 17, tags: [['warn', 'Mengandung Telur Ayam'], ['ok', 'Bebas Kacang']], active: true, img: '' },
  { id: 6, day: 'Cadangan Operasional', cat: 'cadangan', catLabel: 'Ready-to-Deploy', name: 'Paket Siaga Cadangan: Ayam Opor Suwir & Tahu Tempe', desc: 'Nasi Pulen, Ayam Opor Suwir Higienis 80g, Bacem Tahu Tempe Kedelai Lokal, Apel Manis Fuji, Susu UHT', kcal: 610, carb: 76, prot: 24, fat: 15, tags: [['ok', 'Hipoalergenik Ringan'], ['warn', 'Mengandung Kedelai']], active: true, img: '' }
];

function initMenuPage() {
  const grid = $('#menuGrid');
  const info = $('#menuInfo');
  const modal = $('#menuModal');
  const form = $('#menuForm');
  let filter = 'all';
  let query = '';
  let editingId = null;
  bindModal(modal);

  function render() {
    const q = query.toLowerCase();
    const rows = MENUS.filter((m) =>
      (filter === 'all' || m.cat === filter) &&
      (!q || m.name.toLowerCase().includes(q) || m.desc.toLowerCase().includes(q)));

    grid.innerHTML = rows.length ? rows.map((m) => `
      <article class="sppg-menu-card ${m.active ? '' : 'off'}" data-id="${m.id}">
        <div class="sppg-menu-img">
          ${m.img ? `<img src="${esc(m.img)}" alt="${esc(m.name)}" loading="lazy">` : '<span class="material-symbols-outlined ph">lunch_dining</span>'}
          <div class="menu-tags">
            <span class="menu-tag">${esc(m.day)}</span>
            <span class="menu-tag cat cat-${m.cat}">${esc(m.catLabel || CAT_LABEL[m.cat])}</span>
          </div>
        </div>
        <div class="sppg-menu-body">
          <h3>${esc(m.name)}</h3>
          <p class="sppg-menu-desc">${esc(m.desc)}</p>
          <div class="macro-box">
            <div class="macro-top"><b>🔥 ${m.kcal} kkal</b><span>Target: 600–650 kkal</span></div>
            <div class="macro-cells">
              <div>Karbo<b>${m.carb}g</b></div><div>Protein<b>${m.prot}g</b></div><div>Lemak<b>${m.fat}g</b></div>
            </div>
          </div>
          <div class="allergen-row">${m.tags.map(([t, l]) => `<span class="pill ${t === 'ok' ? 'pill-green' : 'pill-orange'}">${t === 'ok' ? '✓' : '⚠'} ${esc(l)}</span>`).join('')}</div>
          <div class="menu-card-foot">
            <label class="toggle"><input type="checkbox" data-toggle ${m.active ? 'checked' : ''}><span class="toggle-track"></span><span>${m.cat === 'cadangan' ? 'Siaga' : 'Aktif'}</span></label>
            <div class="row-actions">
              <button type="button" class="btn-mini" data-gizi><span class="material-symbols-outlined">bar_chart</span>Standar Gizi</button>
              <button type="button" class="btn-mini" data-edit><span class="material-symbols-outlined">edit</span>Edit</button>
            </div>
          </div>
        </div>
      </article>`).join('') : '<p class="sppg-panel sppg-empty">Menu tidak ditemukan.</p>';

    info.innerHTML = `Menampilkan <b>${rows.length ? 1 : 0}</b> - <b>${rows.length}</b> dari <b>24</b> Menu Aktif`;

    $$('.sppg-menu-card', grid).forEach((card) => {
      const m = MENUS.find((x) => x.id === Number(card.dataset.id));
      $('[data-toggle]', card).addEventListener('change', (e) => {
        m.active = e.target.checked;
        card.classList.toggle('off', !m.active);
        showToast(`${m.name} ${m.active ? 'diaktifkan' : 'dinonaktifkan'}`);
      });
      $('[data-gizi]', card).addEventListener('click', () =>
        showToast(`${m.kcal} kkal • Protein ${m.prot}g • Karbo ${m.carb}g • Lemak ${m.fat}g`));
      $('[data-edit]', card).addEventListener('click', () => openForm(m));
    });
  }

  function openForm(m) {
    editingId = m ? m.id : null;
    $('#menuModalTitle').textContent = m ? 'Edit Menu' : 'Tambah Menu Baru';
    $('#mfName').value = m ? m.name : '';
    $('#mfDesc').value = m ? m.desc : '';
    $('#mfKcal').value = m ? m.kcal : 620;
    $('#mfCat').value = m ? m.cat : 'reguler';
    $('#mfCarb').value = m ? m.carb : 72;
    $('#mfProt').value = m ? m.prot : 26;
    $('#mfFat').value = m ? m.fat : 15;
    openModal(modal);
    $('#mfName').focus();
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = $('#mfName').value.trim();
    const kcal = parseInt($('#mfKcal').value, 10);
    if (!name || !kcal) { showToast('Nama menu dan kalori wajib diisi'); return; }
    const data = {
      name, desc: $('#mfDesc').value.trim() || '-', kcal, cat: $('#mfCat').value,
      carb: parseInt($('#mfCarb').value, 10) || 0, prot: parseInt($('#mfProt').value, 10) || 0, fat: parseInt($('#mfFat').value, 10) || 0
    };
    if (editingId) {
      Object.assign(MENUS.find((x) => x.id === editingId), data);
    } else {
      MENUS.push({ id: Date.now(), day: 'Belum dijadwalkan', tags: [['ok', 'Menunggu verifikasi']], active: true, img: '', ...data });
    }
    closeModal(modal);
    showToast(editingId ? 'Menu diperbarui' : 'Menu baru ditambahkan');
    render();
  });

  $('#addMenuBtn').addEventListener('click', () => openForm(null));
  $('#menuSearch').addEventListener('input', (e) => { query = e.target.value.trim(); render(); });

  $$('#menuTabs .sppg-tab').forEach((tab) => tab.addEventListener('click', () => {
    $$('#menuTabs .sppg-tab').forEach((t) => t.classList.remove('active'));
    tab.classList.add('active');
    filter = tab.dataset.filter;
    render();
  }));

  $$('.sppg-view-toggle button').forEach((btn) => btn.addEventListener('click', () => {
    $$('.sppg-view-toggle button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
    grid.classList.toggle('list', btn.dataset.view === 'list');
  }));

  $('#exportMenuBtn').addEventListener('click', () => {
    downloadCSV('rekap-menu-sppg.csv', [
      ['Menu', 'Jadwal', 'Kategori', 'Kkal', 'Karbo (g)', 'Protein (g)', 'Lemak (g)', 'Status'],
      ...MENUS.map((m) => [m.name, m.day, m.catLabel || CAT_LABEL[m.cat], m.kcal, m.carb, m.prot, m.fat, m.active ? 'Aktif' : 'Nonaktif'])
    ]);
    showToast('Rekap menu diunduh');
  });

  render();
}

/* ------------------------------------------------------------------ */
/* PILIHAN PENGGUNA                                                    */
/* ------------------------------------------------------------------ */
const TYPE_META = {
  reguler: { cls: 'pill-green', icon: 'shield', label: 'Paket Reguler A', cat: 'reguler' },
  'reguler-b': { cls: 'pill-green', icon: 'shield', label: 'Paket Reguler B', cat: 'reguler' },
  khusus: { cls: 'pill-orange', icon: 'restaurant', label: 'Diet / Alergi Khusus', cat: 'khusus' },
  evaluasi: { cls: 'pill-blue', icon: 'assignment', label: 'Dalam Evaluasi Menu', cat: 'evaluasi' }
};

const STUDENTS = [
  { nama: 'Made Arya Putra', nisn: '0087612451', sekolah: 'SMP Negeri 1 Denpasar', kelas: 'VII-A', type: 'reguler', note: '', status: 'aktif' },
  { nama: 'Ni Putu Gita Dewi', nisn: '0087612452', sekolah: 'SMP Negeri 2 Denpasar', kelas: 'VII-B', type: 'khusus', menuLabel: 'Khusus Non-Laktosa', note: 'Alergi Susu Sapi & Olahan', status: 'aktif' },
  { nama: 'Komang Aditya', nisn: '0087612453', sekolah: 'SMP Negeri 1 Denpasar', kelas: 'VIII-C', type: 'khusus', menuLabel: 'Bebas Telur / Ovov', note: 'Alergi Telur Ayam (Gatal)', status: 'aktif' },
  { nama: 'Ni Luh Sasmita', nisn: '0087612454', sekolah: 'SMP Negeri 3 Denpasar', kelas: 'VIII-C', type: 'reguler-b', note: '', status: 'aktif' },
  { nama: 'I Gusti Surya Mahendra', nisn: '0087612455', sekolah: 'SMP Negeri 1 Kuta', kelas: 'IX-A', type: 'reguler', note: '', status: 'aktif' },
  { nama: 'Anak Agung Istri Dewi', nisn: '0087612456', sekolah: 'SMP Negeri 10 Denpasar', kelas: 'VII-D', type: 'khusus', menuLabel: 'Bebas Kacang (Peanut-Free)', note: 'Alergi Kacang Tanah Akut', status: 'aktif' },
  { nama: 'Kadek Rama Danendra', nisn: '0087612457', sekolah: 'SMP Negeri 2 Denpasar', kelas: 'VIII-E', type: 'evaluasi', note: 'Suspek Alergi Seafood', status: 'review' },
  { nama: 'Desak Putu Wahyuni', nisn: '0087612458', sekolah: 'SMP Negeri 1 Denpasar', kelas: 'VII-B', type: 'reguler', note: '', status: 'aktif' },
  { nama: 'Gede Pramana Wicaksana', nisn: '0087612459', sekolah: 'SMP Negeri 10 Denpasar', kelas: 'IX-C', type: 'khusus', menuLabel: 'Non-Seafood (Alergi Ikan)', note: 'Ikan Laut & Udang', status: 'aktif' },
  { nama: 'Luh Putu Anindya', nisn: '0087612460', sekolah: 'SMP Negeri 3 Denpasar', kelas: 'VIII-A', type: 'reguler-b', note: '', status: 'aktif' }
];

function initials(name) {
  const p = name.trim().split(/\s+/);
  return ((p[0] || '')[0] + ((p[1] || '')[0] || '')).toUpperCase();
}

function initPilihanPage() {
  const PER_PAGE = 10;
  let page = 1;
  let editing = null;
  const body = $('#studentTableBody');
  const modal = $('#studentModal');
  const form = $('#studentForm');
  const f = { q: $('#studentSearch'), school: $('#fSchool'), cls: $('#fClass'), diet: $('#fDiet'), status: $('#fStatus') };
  bindModal(modal);

  function fillSelect(sel, values) {
    values.forEach((v) => {
      const o = document.createElement('option');
      o.value = v; o.textContent = v;
      sel.appendChild(o);
    });
  }

  function populateFilters() {
    $$('option:not(:first-child)', f.school).forEach((o) => o.remove());
    $$('option:not(:first-child)', f.cls).forEach((o) => o.remove());
    const cur = { s: f.school.value, c: f.cls.value };
    fillSelect(f.school, [...new Set(STUDENTS.map((s) => s.sekolah))].sort());
    fillSelect(f.cls, [...new Set(STUDENTS.map((s) => s.kelas))].sort());
    f.school.value = cur.s; f.cls.value = cur.c;
  }

  function filtered() {
    const q = f.q.value.trim().toLowerCase();
    return STUDENTS.filter((s) =>
      (!q || s.nama.toLowerCase().includes(q) || s.nisn.includes(q)) &&
      (!f.school.value || s.sekolah === f.school.value) &&
      (!f.cls.value || s.kelas === f.cls.value) &&
      (!f.diet.value || TYPE_META[s.type].cat === f.diet.value) &&
      (!f.status.value || s.status === f.status.value));
  }

  function render() {
    const rows = filtered();
    const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    page = Math.min(page, totalPages);
    const start = (page - 1) * PER_PAGE;
    const slice = rows.slice(start, start + PER_PAGE);

    body.innerHTML = slice.length ? slice.map((s, i) => {
      const t = TYPE_META[s.type];
      const wilayah = /Kuta/.test(s.sekolah) ? 'Kab. Badung' : 'Kota Denpasar';
      return `
      <tr data-nisn="${esc(s.nisn)}">
        <td>${start + i + 1}</td>
        <td><div class="cell-student"><span class="student-avatar">${esc(initials(s.nama))}</span><div><div class="cell-title" style="font-size:14px;">${esc(s.nama)}</div><div class="cell-sub">NISN: ${esc(s.nisn)}</div></div></div></td>
        <td><div class="cell-title" style="font-size:14px;">${esc(s.sekolah)}</div><div class="cell-sub">${wilayah}</div></td>
        <td><b>${esc(s.kelas)}</b></td>
        <td><span class="pill ${t.cls}"><span class="material-symbols-outlined" style="font-size:14px;">${t.icon}</span>${esc(s.menuLabel || t.label)}</span></td>
        <td>${s.note ? `<span class="pill ${s.status === 'review' ? 'pill-gray' : 'pill-red'}">${esc(s.note)}</span>` : '<span class="cell-empty">Tidak ada catatan</span>'}</td>
        <td class="ctr">${s.status === 'review' ? '<span class="pill pill-blue">Review Medis</span>' : '<span class="pill pill-green">● Aktif</span>'}</td>
        <td class="ctr"><div class="row-actions">
          ${s.status === 'review' ? '<a href="#" class="sppg-link" data-verify>Verifikasi</a>' : '<a href="#" class="sppg-link" data-edit>Ubah</a>'}
          <button type="button" class="icon-btn" data-history title="Riwayat"><span class="material-symbols-outlined">history</span></button>
        </div></td>
      </tr>`;
    }).join('') : '<tr><td colspan="8" class="sppg-empty">Data siswa tidak ditemukan.</td></tr>';

    $('#recordCount').textContent = `${fmt(rows.length)} Records`;
    $('#studentInfo').innerHTML = rows.length
      ? `Menampilkan <b>${start + 1}</b>–<b>${start + slice.length}</b> dari <b>${fmt(rows.length)}</b> Siswa`
      : 'Tidak ada data';
    renderPagination($('#studentPagination'), page, totalPages, (p) => { page = p; render(); });

    $$('tr[data-nisn]', body).forEach((tr) => {
      const s = STUDENTS.find((x) => x.nisn === tr.dataset.nisn);
      const ed = $('[data-edit]', tr);
      const ver = $('[data-verify]', tr);
      if (ed) ed.addEventListener('click', (e) => { e.preventDefault(); openForm(s); });
      if (ver) ver.addEventListener('click', (e) => {
        e.preventDefault();
        s.status = 'aktif';
        showToast(`${s.nama} terverifikasi`);
        render();
      });
      $('[data-history]', tr).addEventListener('click', () => showToast(`Riwayat preferensi ${s.nama}`));
    });
  }

  function openForm(s) {
    editing = s;
    $('#studentModalTitle').textContent = s ? 'Ubah Preferensi Siswa' : 'Input Preferensi Siswa';
    $('#sfName').value = s ? s.nama : '';
    $('#sfNisn').value = s ? s.nisn : '';
    $('#sfNisn').readOnly = !!s;
    $('#sfClass').value = s ? s.kelas : '';
    $('#sfSchool').value = s ? s.sekolah : '';
    $('#sfType').value = s ? s.type : 'reguler';
    $('#sfStatus').value = s ? s.status : 'aktif';
    $('#sfNote').value = s ? s.note : '';
    openModal(modal);
    $('#sfName').focus();
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = {
      nama: $('#sfName').value.trim(), nisn: $('#sfNisn').value.trim(), kelas: $('#sfClass').value.trim(),
      sekolah: $('#sfSchool').value.trim(), type: $('#sfType').value, status: $('#sfStatus').value, note: $('#sfNote').value.trim()
    };
    if (!data.nama || !data.nisn || !data.kelas || !data.sekolah) { showToast('Lengkapi semua kolom wajib'); return; }
    if (editing) {
      Object.assign(editing, data);
      delete editing.menuLabel;
    } else {
      if (STUDENTS.some((s) => s.nisn === data.nisn)) { showToast('NISN sudah terdaftar'); return; }
      STUDENTS.unshift(data);
    }
    closeModal(modal);
    showToast(editing ? 'Preferensi diperbarui' : 'Preferensi siswa ditambahkan');
    populateFilters();
    page = 1;
    render();
  });

  $('#addPrefBtn').addEventListener('click', () => openForm(null));
  Object.values(f).forEach((el) => el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', () => { page = 1; render(); }));

  $('#resetFilterBtn').addEventListener('click', () => {
    f.q.value = ''; f.school.value = ''; f.cls.value = ''; f.diet.value = ''; f.status.value = '';
    page = 1; render();
  });

  $('#exportStudentBtn').addEventListener('click', () => {
    downloadCSV('pilihan-menu-siswa.csv', [
      ['NISN', 'Nama', 'Sekolah', 'Kelas', 'Pilihan Menu', 'Catatan', 'Status'],
      ...filtered().map((s) => [s.nisn, s.nama, s.sekolah, s.kelas, s.menuLabel || TYPE_META[s.type].label, s.note, s.status])
    ]);
    showToast('Data siswa diunduh');
  });

  populateFilters();
  render();
}

/* ------------------------------------------------------------------ */
/* REVIEW                                                              */
/* ------------------------------------------------------------------ */
const FEEDBACKS = [
  { code: 'D1', school: 'SMP Negeri 1 Denpasar', who: 'I Nyoman Sukra • Koordinator UKS & Gizi • Hari ini, 11:30 WITA', score: 5.0, text: '"Anak-anak sangat antusias dengan menu Ayam Panggang Madu dan Sayur Capcay. Kemasan tiba dalam boks termal higienis, suhu sup masih hangat, dan tidak ada wadah penyok. 542 porsi tuntas dibagikan tepat waktu."', tags: [['Suhu Wadah: Hangat (58°C)', 'green'], ['Sisa Porsi: < 1%', 'green']], kind: 'puas' },
  { code: 'G2', school: 'SMP Negeri 2 Gianyar', who: 'Ni Ketut Suartini • Pengawas Distribusi Sekolah • Hari ini, 10:45 WITA', score: 4.8, text: '"Kualitas nasi pulen dan tekstur daging sangat lembut untuk siswa. Catatan khusus: paket siswa alergi telur (Komang Aditya) terlabeli dengan segel peach sangat jelas sehingga tidak tertukar."', tags: [['Label Alergen: Akurat 100%', 'green'], ['Porsi Diterima: 765', 'gray']], kind: 'puas' },
  { code: 'K1', school: 'SMP Negeri 1 Klungkung', who: 'Luh Putu Aryani • Komite Sekolah & Tim UKS • Kemarin, 12:15 WITA', score: 3.8, text: '"Rasa sayuran sop jagung manis disukai anak-anak. Namun, armada tiba pada 09.55 WITA (sedikit mendekati jam istirahat pertama). Mohon estimasi waktu pengantaran untuk jalur Klungkung dimajukan 15 menit."', tags: [['Catatan: Jadwal Armada', 'orange'], ['Koreksi Logistik', 'gray']], kind: 'evaluasi', warn: true }
];

const RATINGS = [[5, 82], [4, 14], [3, 4], [2, 0], [1, 0]];
const FAVORITES = [
  ['Ayam Panggang Madu & Capcay', 'Paket Reguler SD/SMP', '99.1%'],
  ['Semur Gandik Sapi & Buncis', 'Paket Reguler Tinggi Protein', '97.4%'],
  ['Ikan Fillet Bumbu Kuning', 'Paket Omega-3 & Sayur Bayam', '95.0%']
];

function initReviewPage() {
  const list = $('#feedbackList');
  let filter = 'all';

  function render() {
    const rows = FEEDBACKS.filter((f) => filter === 'all' || f.kind === filter);
    list.innerHTML = rows.length ? rows.map((f, i) => `
      <article class="fb-card ${f.warn ? 'warn' : ''}" data-i="${FEEDBACKS.indexOf(f)}">
        <div class="fb-head">
          <div class="fb-avatar">${esc(f.code)}</div>
          <div class="fb-who"><h4>${esc(f.school)}</h4><p>${esc(f.who)}</p></div>
          <span class="fb-score"><span class="material-symbols-outlined">star</span>${f.score.toFixed(1)}</span>
        </div>
        <div class="fb-quote">${esc(f.text)}</div>
        <div class="fb-foot">
          <div class="fb-tags">${f.tags.map(([l, c]) => `<span class="pill ${c === 'green' ? 'pill-green' : c === 'orange' ? 'pill-orange' : 'pill-gray'}">${esc(l)}</span>`).join('')}</div>
          ${f.warn
            ? `<button type="button" class="fb-respond ${f.responded ? 'done' : ''}" data-respond>${f.responded ? '✓ Tanggapan Terkirim' : 'Tanggapi Tim Dapur <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>'}</button>`
            : '<span class="fb-verified"><span class="material-symbols-outlined">check_circle</span>Terverifikasi SPPG</span>'}
        </div>
      </article>`).join('') : '<p class="sppg-panel sppg-empty">Tidak ada umpan balik.</p>';

    $$('[data-respond]', list).forEach((btn) => btn.addEventListener('click', () => {
      const f = FEEDBACKS[Number(btn.closest('.fb-card').dataset.i)];
      if (f.responded) return;
      f.responded = true;
      showToast(`Tanggapan untuk ${f.school} diteruskan ke Tim Dapur`);
      render();
    }));
  }

  $$('#reviewFilters .sppg-chip').forEach((chip) => chip.addEventListener('click', () => {
    $$('#reviewFilters .sppg-chip').forEach((c) => c.classList.remove('active'));
    chip.classList.add('active');
    filter = chip.dataset.filter;
    render();
  }));

  $('#ratingBars').innerHTML = RATINGS.map(([star, pct]) => `
    <div class="rate-row">
      <span>${star} Bintang</span>
      <div class="rate-track"><div class="rate-fill ${star === 3 ? 'low' : star === 4 ? 'mid' : ''}" style="width:${pct}%"></div></div>
      <b>${pct}%</b>
    </div>`).join('');

  $('#favList').innerHTML = FAVORITES.map(([name, sub, pct]) => `
    <li><div><b>${esc(name)}</b><small>${esc(sub)}</small></div><span class="pill pill-green">${pct} Suka</span></li>`).join('');

  render();
}
