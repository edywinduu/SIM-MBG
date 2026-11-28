/**
 * SIM-MBG — SPPG Page Script (sppg.js)
 * Terintegrasi penuh dengan Laravel Blade & Controller SPPG:
 * - Dashboard: Aktivitas distribusi, tabel distribusi sekolah, sinkronisasi & update status pengiriman
 * - Menu: Daftar siklus menu dari database, tambah menu, ubah menu, dan toggle status aktif/siaga
 * - Pilihan Siswa: Rekapitulasi pilihan menu & alergi siswa, input/ubah preferensi, dan verifikasi medis
 * - Review & Evaluasi: Umpan balik sekolah, sebaran rating, menu favorit, dan tanggapan tim dapur
 */

document.addEventListener('DOMContentLoaded', () => {
  initCommon();
  const id = document.body.id;
  if (id === 'sppgDashboardPage') initDashboard();
  if (id === 'sppgMenuPage') initMenuPage();
  if (id === 'sppgPilihanPage') initPilihanPage();
  if (id === 'sppgReviewPage') initReviewPage();
});

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

function esc(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function fmt(n) {
  return Number(n || 0).toLocaleString('id-ID');
}

function getCsrfToken() {
  return $('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

let toastTimer;
function showToast(msg) {
  const el = $('#sppgToast');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2400);
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

function initCommon() {
  const logoutBtn = $('#logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      try { sessionStorage.removeItem('sim_mbg_auth'); } catch (err) { console.warn(err); }

      const form = logoutBtn.closest('form');
      if (form) {
        return;
      }

      e.preventDefault();
      const fallbackForm = document.createElement('form');
      fallbackForm.method = 'POST';
      fallbackForm.action = '/logout';
      const csrf = getCsrfToken();
      if (csrf) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        fallbackForm.appendChild(csrfInput);
      }
      document.body.appendChild(fallbackForm);
      fallbackForm.submit();
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

// =========================================================================
// 1. DASHBOARD SPPG
// =========================================================================

const DEFAULT_ACTIVITIES = [
  { icon: 'task_alt', tone: 'ico-green', title: 'Penerimaan batch selesai • SMP Negeri 1 Denpasar', desc: '240 porsi diserahkan kepada Petugas UKS (Sdr. I Made Darmawan)', time: 'Hari ini, 09:12 WITA', pill: ['pill-green', 'Terkirim'] },
  { icon: 'directions_run', tone: 'ico-blue', title: 'Armada kurir menuju lokasi • SMP Negeri 2 Gianyar', desc: '180 porsi dalam box termal pengangkut SPPG Armada B-04', time: 'Hari ini, 08:45 WITA', pill: ['pill-blue', 'Diproses'] },
  { icon: 'rate_review', tone: 'ico-orange', title: 'Catatan rasa & suhu diterima • SD Negeri 3 Sanur', desc: 'Umpan balik dari komite gizi sekolah: Sup tersaji hangat, kemasan higienis utuh', time: 'Hari ini, 08:20 WITA', pill: ['pill-orange', 'Evaluasi Diterima'] },
  { icon: 'shield', tone: 'ico-green', title: 'Inspeksi higienitas dapur pusat disetujui', desc: 'Verifikasi harian oleh Tim Pengawas Nutrisi SPPG Wilayah I', time: 'Hari ini, 06:15 WITA', pill: ['pill-green', 'Terkirim'] }
];

const DEFAULT_SCHOOLS = [
  { id: 1, name: 'SMP Negeri 1 Denpasar', npsn: '50103120', pic: 'I Nyoman Sukra', area: 'Kota Denpasar / Denpasar Barat', qty: 542, eta: '09:00 - 09:30 WITA', status: 'terkirim' },
  { id: 2, name: 'SMP Negeri 2 Denpasar', npsn: '50103121', pic: 'Ni Ketut Suartini', area: 'Kota Denpasar / Denpasar Timur', qty: 765, eta: '09:15 - 09:45 WITA', status: 'dikirim' },
  { id: 3, name: 'SMP Negeri 1 Gianyar', npsn: '50102044', pic: 'Wayan Sudarta', area: 'Kabupaten Gianyar / Kota', qty: 480, eta: '09:30 - 10:00 WITA', status: 'dikirim' },
  { id: 4, name: 'SMP Negeri 1 Klungkung', npsn: '50101980', pic: 'Luh Putu Aryani', area: 'Kabupaten Klungkung / Semarapura', qty: 415, eta: '10:00 - 10:30 WITA', status: 'menunggu' },
  { id: 5, name: 'SMP Negeri 1 Kuta', npsn: '50101567', pic: 'Gede Wira Pratama', area: 'Kabupaten Badung / Kuta Selatan', qty: 738, eta: '09:00 - 09:30 WITA', status: 'terkirim' }
];

const SCHOOL_STATUS = {
  terkirim: ['pill-green', 'Terkirim'],
  dikirim: ['pill-blue', 'Sedang Dikirim'],
  menunggu: ['pill-gray', 'Menunggu']
};

function initDashboard() {
  let activities = (typeof window !== 'undefined' && Array.isArray(window.SPPG_ACTIVITIES) && window.SPPG_ACTIVITIES.length)
    ? window.SPPG_ACTIVITIES
    : DEFAULT_ACTIVITIES;

  let schools = (typeof window !== 'undefined' && Array.isArray(window.SPPG_SCHOOLS) && window.SPPG_SCHOOLS.length)
    ? window.SPPG_SCHOOLS
    : DEFAULT_SCHOOLS;

  const list = $('#activityList');
  function renderActivities() {
    if (!list) return;
    list.innerHTML = activities.map((a) => `
      <li>
        <div class="sppg-ico ${esc(a.tone || 'ico-green')}"><span class="material-symbols-outlined">${esc(a.icon || 'task_alt')}</span></div>
        <div class="act-body"><h4>${esc(a.title)}</h4><p>${esc(a.desc)}</p><small>${esc(a.time)}</small></div>
        <span class="pill ${esc((a.pill && a.pill[0]) || 'pill-green')}">${esc((a.pill && a.pill[1]) || 'Terkirim')}</span>
      </li>`).join('');
  }

  renderActivities();

  const PER_PAGE = 5;
  let page = 1;
  let query = '';
  const body = $('#schoolTableBody');
  const info = $('#schoolInfo');
  const pag = $('#schoolPagination');

  function render() {
    if (!body) return;
    const q = query.toLowerCase();
    const rows = schools.filter((s) => !q || s.name.toLowerCase().includes(q) || s.area.toLowerCase().includes(q));
    const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    page = Math.min(page, totalPages);
    const start = (page - 1) * PER_PAGE;
    const slice = rows.slice(start, start + PER_PAGE);

    body.innerHTML = slice.length ? slice.map((s, i) => {
      const st = SCHOOL_STATUS[s.status] || SCHOOL_STATUS.menunggu;
      const actionLabel = s.status === 'terkirim' ? 'Lihat Detail' : 'Update Status';
      return `
      <tr>
        <td>${start + i + 1}</td>
        <td><div class="cell-title">${esc(s.name)}</div><div class="cell-sub">NPSN: ${esc(s.npsn)} • PIC: ${esc(s.pic)}</div></td>
        <td>${esc(s.area)}</td>
        <td class="num">${fmt(s.qty)}</td>
        <td>${esc(s.eta)}</td>
        <td class="ctr"><span class="pill ${st[0]}">● ${st[1]}</span></td>
        <td class="ctr"><a href="#" class="sppg-link sm" data-dist-id="${s.id || ''}" data-detail="${esc(s.name)}">${actionLabel}</a></td>
      </tr>`;
    }).join('') : '<tr><td colspan="7" class="sppg-empty">Sekolah tidak ditemukan.</td></tr>';

    if (info) {
      info.innerHTML = rows.length
        ? `Menampilkan <b>${start + 1}</b> - <b>${start + slice.length}</b> dari <b>${rows.length}</b> sekolah penerima`
        : 'Tidak ada data';
    }
    renderPagination(pag, page, totalPages, (p) => { page = p; render(); });

    $$('[data-detail]', body).forEach((a) => a.addEventListener('click', async (e) => {
      e.preventDefault();
      const distId = Number(a.dataset.distId);
      const targetSchool = schools.find((s) => (distId && s.id === distId) || s.name === a.dataset.detail);
      if (!targetSchool) return;

      if (targetSchool.status === 'terkirim') {
        showToast(`Distribusi ${targetSchool.name} (${fmt(targetSchool.qty)} porsi) telah diterima.`);
        return;
      }

      if (distId) {
        try {
          const res = await fetch(`/sppg/distributions/${distId}/status`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': getCsrfToken(),
              'Accept': 'application/json'
            }
          });
          const json = await res.json();
          if (res.ok && json.status === 'success') {
            targetSchool.status = json.distribution_status || 'terkirim';
            if (Array.isArray(json.activities) && json.activities.length) {
              activities = json.activities;
              renderActivities();
            }
            render();
            showToast(json.message || `Status distribusi ${targetSchool.name} diperbarui.`);
            return;
          }
        } catch (err) {
          console.error(err);
        }
      }

      targetSchool.status = targetSchool.status === 'menunggu' ? 'dikirim' : 'terkirim';
      render();
      showToast(`Status distribusi ${targetSchool.name} diperbarui.`);
    }));
  }

  $('#schoolSearch')?.addEventListener('input', (e) => { query = e.target.value.trim(); page = 1; render(); });

  $('#createReportBtn')?.addEventListener('click', () => {
    downloadCSV('laporan-distribusi.csv', [
      ['No', 'Sekolah', 'NPSN', 'PIC', 'Wilayah', 'Jumlah Porsi', 'Estimasi Tiba', 'Status'],
      ...schools.map((s, i) => [i + 1, s.name, s.npsn, s.pic, s.area, s.qty, s.eta, (SCHOOL_STATUS[s.status] || SCHOOL_STATUS.menunggu)[1]])
    ]);
    showToast('Laporan distribusi diunduh');
  });

  $('#syncBtn')?.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    const icon = $('.material-symbols-outlined', btn);
    if (icon) icon.style.animation = 'spin 1s linear infinite';
    btn.disabled = true;

    try {
      const res = await fetch('/sppg/dashboard/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json'
        }
      });
      const json = await res.json();
      if (res.ok && json.status === 'success') {
        if (Array.isArray(json.schools) && json.schools.length) {
          schools = json.schools;
          render();
        }
        if (Array.isArray(json.activities) && json.activities.length) {
          activities = json.activities;
          renderActivities();
        }
        if (json.kpis) {
          const elPortions = $('#kpiTotalPortions');
          const elSchools = $('#kpiActiveSchools');
          const elMenus = $('#kpiVerifiedMenus');
          const elEvals = $('#kpiPendingEvals');
          if (elPortions) elPortions.innerHTML = `${fmt(json.kpis.total_portions)} <small>Porsi</small>`;
          if (elSchools) elSchools.innerHTML = `${fmt(json.kpis.active_schools)} <small>Lembaga</small>`;
          if (elMenus) elMenus.innerHTML = `${fmt(json.kpis.verified_menus)} <small>Siklus Menu</small>`;
          if (elEvals) elEvals.innerHTML = `${fmt(json.kpis.pending_evaluations)} <small>Catatan Baru</small>`;
        }
        showToast(json.message || 'Data berhasil disinkronkan');
        return;
      }
    } catch (err) {
      console.error(err);
    } finally {
      if (icon) icon.style.animation = '';
      btn.disabled = false;
    }

    showToast('Data berhasil disinkronkan');
  });

  render();
}

// =========================================================================
// 2. MANAJEMEN SIKLUS MENU SPPG
// =========================================================================

const CAT_LABEL = { reguler: 'Reguler SD/SMP', khusus: 'Alergi Khusus', cadangan: 'Cadangan Operasional' };

const DEFAULT_MENUS = [
  {
    id: 1,
    day: 'Senin • Minggu ke-1',
    cat: 'reguler',
    name: 'Paket Ayam Panggang Madu & Capcay Bakso',
    desc: 'Nasi Merah Organik, Ayam Panggang Madu 80g, Capcay Sayur Segar, Semangka Merah, Kotak Susu UHT',
    kcal: 630,
    carb: 75,
    prot: 26,
    fat: 16,
    tags: [['ok', 'Bebas Kacang'], ['warn', 'Mengandung Susu Sapi']],
    active: true,
    img: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'
  }
];

function initMenuPage() {
  let menus = (typeof window !== 'undefined' && Array.isArray(window.SPPG_MENUS) && window.SPPG_MENUS.length)
    ? window.SPPG_MENUS
    : DEFAULT_MENUS;

  const grid = $('#menuGrid');
  const info = $('#menuInfo');
  const modal = $('#menuModal');
  const form = $('#menuForm');
  let filter = 'all';
  let query = '';
  let editingId = null;
  bindModal(modal);

  function updateCounters() {
    const activeMenus = menus.filter((m) => m.active);
    const totalActive = activeMenus.length;
    const avgCal = totalActive
      ? Math.round(activeMenus.reduce((sum, m) => sum + Number(m.kcal || 0), 0) / totalActive)
      : 615;
    const specialCount = menus.filter((m) => m.cat === 'khusus').length;
    const regularCount = menus.filter((m) => m.cat === 'reguler').length;
    const backupCount = menus.filter((m) => m.cat === 'cadangan').length;

    const elTotal = $('#kpiTotalMenus');
    const elAvg = $('#kpiAvgCalories');
    const elSpec = $('#kpiSpecialMenus');
    if (elTotal) elTotal.innerHTML = `${totalActive} <small>Paket Menu</small>`;
    if (elAvg) elAvg.innerHTML = `${avgCal} <small>kkal / porsi</small>`;
    if (elSpec) elSpec.innerHTML = `${specialCount} <small>Variasi</small>`;

    const cAll = $('#tabCountAll');
    const cReg = $('#tabCountReguler');
    const cKhu = $('#tabCountKhusus');
    const cCad = $('#tabCountCadangan');
    if (cAll) cAll.textContent = menus.length;
    if (cReg) cReg.textContent = regularCount;
    if (cKhu) cKhu.textContent = specialCount;
    if (cCad) cCad.textContent = backupCount;
  }

  function render() {
    if (!grid) return;
    const q = query.toLowerCase();
    const rows = menus.filter((m) =>
      (filter === 'all' || m.cat === filter) &&
      (!q || m.name.toLowerCase().includes(q) || (m.desc || '').toLowerCase().includes(q)));

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
          <div class="allergen-row">${(m.tags || []).map(([t, l]) => `<span class="pill ${t === 'ok' ? 'pill-green' : 'pill-orange'}">${t === 'ok' ? '✓' : '⚠'} ${esc(l)}</span>`).join('')}</div>
          <div class="menu-card-foot">
            <label class="toggle"><input type="checkbox" data-toggle ${m.active ? 'checked' : ''}><span class="toggle-track"></span><span>${m.cat === 'cadangan' ? 'Siaga' : 'Aktif'}</span></label>
            <div class="row-actions">
              <button type="button" class="btn-mini" data-gizi><span class="material-symbols-outlined">bar_chart</span>Standar Gizi</button>
              <button type="button" class="btn-mini" data-edit><span class="material-symbols-outlined">edit</span>Edit</button>
            </div>
          </div>
        </div>
      </article>`).join('') : '<p class="sppg-panel sppg-empty">Menu tidak ditemukan.</p>';

    const activeCount = menus.filter((m) => m.active).length;
    if (info) {
      info.innerHTML = `Menampilkan <b>${rows.length ? 1 : 0}</b> - <b>${rows.length}</b> dari <b>${activeCount}</b> Menu Aktif`;
    }

    $$('.sppg-menu-card', grid).forEach((card) => {
      const m = menus.find((x) => x.id === Number(card.dataset.id));
      if (!m) return;

      $('[data-toggle]', card)?.addEventListener('change', async (e) => {
        const checked = e.target.checked;
        m.active = checked;
        card.classList.toggle('off', !m.active);
        updateCounters();

        try {
          const res = await fetch(`/sppg/menu/${m.id}/toggle`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': getCsrfToken(),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ active: checked })
          });
          const json = await res.json();
          if (res.ok && json.status === 'success') {
            showToast(json.message || `${m.name} ${m.active ? 'diaktifkan' : 'dinonaktifkan'}`);
            return;
          }
        } catch (err) {
          console.error(err);
        }

        showToast(`${m.name} ${m.active ? 'diaktifkan' : 'dinonaktifkan'}`);
      });

      $('[data-gizi]', card)?.addEventListener('click', () =>
        showToast(`${m.kcal} kkal • Protein ${m.prot}g • Karbo ${m.carb}g • Lemak ${m.fat}g`));

      $('[data-edit]', card)?.addEventListener('click', () => openForm(m));
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

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = $('#mfName').value.trim();
    const kcal = parseInt($('#mfKcal').value, 10);
    if (!name || !kcal) {
      showToast('Nama menu dan kalori wajib diisi');
      return;
    }

    const payload = {
      name,
      desc: $('#mfDesc').value.trim() || '-',
      kcal,
      cat: $('#mfCat').value,
      carb: parseInt($('#mfCarb').value, 10) || 0,
      prot: parseInt($('#mfProt').value, 10) || 0,
      fat: parseInt($('#mfFat').value, 10) || 0
    };

    const submitBtn = $('button[type="submit"]', form);
    if (submitBtn) submitBtn.disabled = true;

    try {
      const url = editingId ? `/sppg/menu/${editingId}` : '/sppg/menu';
      const method = editingId ? 'PUT' : 'POST';
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (res.ok && json.status === 'success' && json.menu) {
        if (editingId) {
          const target = menus.find((x) => x.id === editingId);
          if (target) Object.assign(target, json.menu);
        } else {
          menus.push(json.menu);
        }
        closeModal(modal);
        updateCounters();
        render();
        showToast(json.message || (editingId ? 'Menu diperbarui' : 'Menu baru ditambahkan'));
        return;
      }
    } catch (err) {
      console.error(err);
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }

    if (editingId) {
      Object.assign(menus.find((x) => x.id === editingId) || {}, payload);
    } else {
      menus.push({ id: Date.now(), day: 'Belum dijadwalkan', tags: [['ok', 'Menunggu verifikasi']], active: true, img: '', ...payload });
    }
    closeModal(modal);
    updateCounters();
    render();
    showToast(editingId ? 'Menu diperbarui' : 'Menu baru ditambahkan');
  });

  $('#addMenuBtn')?.addEventListener('click', () => openForm(null));
  $('#menuSearch')?.addEventListener('input', (e) => { query = e.target.value.trim(); render(); });

  $$('#menuTabs .sppg-tab').forEach((tab) => tab.addEventListener('click', () => {
    $$('#menuTabs .sppg-tab').forEach((t) => t.classList.remove('active'));
    tab.classList.add('active');
    filter = tab.dataset.filter;
    render();
  }));

  $$('.sppg-view-toggle button').forEach((btn) => btn.addEventListener('click', () => {
    $$('.sppg-view-toggle button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
    grid?.classList.toggle('list', btn.dataset.view === 'list');
  }));

  $('#exportMenuBtn')?.addEventListener('click', () => {
    downloadCSV('rekap-menu-sppg.csv', [
      ['Menu', 'Jadwal', 'Kategori', 'Kkal', 'Karbo (g)', 'Protein (g)', 'Lemak (g)', 'Status'],
      ...menus.map((m) => [m.name, m.day, m.catLabel || CAT_LABEL[m.cat], m.kcal, m.carb, m.prot, m.fat, m.active ? 'Aktif' : 'Nonaktif'])
    ]);
    showToast('Rekap menu diunduh');
  });

  updateCounters();
  render();
}

// =========================================================================
// 3. PILIHAN & PREFERENSI MENU SISWA
// =========================================================================

const TYPE_META = {
  reguler: { cls: 'pill-green', icon: 'shield', label: 'Paket Reguler A', cat: 'reguler' },
  'reguler-b': { cls: 'pill-green', icon: 'shield', label: 'Paket Reguler B', cat: 'reguler' },
  khusus: { cls: 'pill-orange', icon: 'restaurant', label: 'Diet / Alergi Khusus', cat: 'khusus' },
  evaluasi: { cls: 'pill-blue', icon: 'assignment', label: 'Dalam Evaluasi Menu', cat: 'evaluasi' }
};

const DEFAULT_STUDENTS = [
  { nama: 'Made Arya Putra', nisn: '0087612451', sekolah: 'SMP Negeri 1 Denpasar', kelas: 'VII-A', type: 'reguler', note: '', status: 'aktif' },
  { nama: 'Ni Putu Gita Dewi', nisn: '0087612452', sekolah: 'SMP Negeri 2 Denpasar', kelas: 'VII-B', type: 'khusus', menuLabel: 'Khusus Non-Laktosa', note: 'Alergi Susu Sapi & Olahan', status: 'aktif' },
  { nama: 'Komang Aditya', nisn: '0087612453', sekolah: 'SMP Negeri 1 Denpasar', kelas: 'VIII-C', type: 'khusus', menuLabel: 'Bebas Telur / Ovov', note: 'Alergi Telur Ayam (Gatal)', status: 'aktif' }
];

function initials(name) {
  const p = String(name || '').trim().split(/\s+/);
  return ((p[0] || '')[0] + ((p[1] || '')[0] || '')).toUpperCase() || 'S';
}

function initPilihanPage() {
  let students = (typeof window !== 'undefined' && Array.isArray(window.SPPG_STUDENTS) && window.SPPG_STUDENTS.length)
    ? window.SPPG_STUDENTS
    : DEFAULT_STUDENTS;

  const PER_PAGE = 10;
  let page = 1;
  let editing = null;
  const body = $('#studentTableBody');
  const modal = $('#studentModal');
  const form = $('#studentForm');
  const f = { q: $('#studentSearch'), school: $('#fSchool'), cls: $('#fClass'), diet: $('#fDiet'), status: $('#fStatus') };
  bindModal(modal);

  function fillSelect(sel, values) {
    if (!sel) return;
    values.forEach((v) => {
      const o = document.createElement('option');
      o.value = v;
      o.textContent = v;
      sel.appendChild(o);
    });
  }

  function populateFilters() {
    if (!f.school || !f.cls) return;
    $$('option:not(:first-child)', f.school).forEach((o) => o.remove());
    $$('option:not(:first-child)', f.cls).forEach((o) => o.remove());
    const cur = { s: f.school.value, c: f.cls.value };
    fillSelect(f.school, [...new Set(students.map((s) => s.sekolah))].sort());
    fillSelect(f.cls, [...new Set(students.map((s) => s.kelas))].sort());
    f.school.value = cur.s;
    f.cls.value = cur.c;
  }

  function updateStudentKpis() {
    const total = students.length;
    const special = students.filter((s) => s.type === 'khusus' || s.type === 'evaluasi' || (s.note && s.note !== '')).length;
    const regular = Math.max(0, total - special);
    const regPct = total > 0 ? Math.round((regular / total) * 100) : 0;
    const specPct = total > 0 ? Math.max(0, 100 - regPct) : 0;

    const elTot = $('#kpiTotalStudents');
    const elReg = $('#kpiRegularStudents');
    const elSpec = $('#kpiSpecialStudents');
    if (elTot) elTot.innerHTML = `${fmt(total)} <small>Siswa</small>`;
    if (elReg) elReg.innerHTML = `${fmt(regular)} <span class="pill pill-green">${regPct}%</span>`;
    if (elSpec) elSpec.innerHTML = `${fmt(special)} <span class="pill pill-orange">${specPct}%</span>`;
  }

  function filtered() {
    const q = (f.q?.value || '').trim().toLowerCase();
    return students.filter((s) => {
      const meta = TYPE_META[s.type] || TYPE_META.reguler;
      return (!q || s.nama.toLowerCase().includes(q) || s.nisn.includes(q)) &&
        (!f.school?.value || s.sekolah === f.school.value) &&
        (!f.cls?.value || s.kelas === f.cls.value) &&
        (!f.diet?.value || meta.cat === f.diet.value) &&
        (!f.status?.value || s.status === f.status.value);
    });
  }

  function render() {
    if (!body) return;
    const rows = filtered();
    const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    page = Math.min(page, totalPages);
    const start = (page - 1) * PER_PAGE;
    const slice = rows.slice(start, start + PER_PAGE);

    body.innerHTML = slice.length ? slice.map((s, i) => {
      const t = TYPE_META[s.type] || TYPE_META.reguler;
      const wilayah = s.wilayah || (/Kuta/.test(s.sekolah) ? 'Kab. Badung' : 'Kota Denpasar');
      return `
      <tr data-nisn="${esc(s.nisn)}">
        <td>${start + i + 1}</td>
        <td><div class="cell-student"><span class="student-avatar">${esc(initials(s.nama))}</span><div><div class="cell-title" style="font-size:14px;">${esc(s.nama)}</div><div class="cell-sub">NISN: ${esc(s.nisn)}</div></div></div></td>
        <td><div class="cell-title" style="font-size:14px;">${esc(s.sekolah)}</div><div class="cell-sub">${esc(wilayah)}</div></td>
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

    if ($('#recordCount')) $('#recordCount').textContent = `${fmt(rows.length)} Records`;
    if ($('#studentInfo')) {
      $('#studentInfo').innerHTML = rows.length
        ? `Menampilkan <b>${start + 1}</b>–<b>${start + slice.length}</b> dari <b>${fmt(rows.length)}</b> Siswa`
        : 'Tidak ada data';
    }
    renderPagination($('#studentPagination'), page, totalPages, (p) => { page = p; render(); });

    $$('tr[data-nisn]', body).forEach((tr) => {
      const s = students.find((x) => x.nisn === tr.dataset.nisn);
      if (!s) return;
      const ed = $('[data-edit]', tr);
      const ver = $('[data-verify]', tr);
      if (ed) ed.addEventListener('click', (e) => { e.preventDefault(); openForm(s); });
      if (ver) ver.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
          const res = await fetch(`/sppg/selections/${encodeURIComponent(s.nisn)}/verify`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': getCsrfToken(),
              'Accept': 'application/json'
            }
          });
          const json = await res.json();
          if (res.ok && json.status === 'success') {
            if (json.student) Object.assign(s, json.student);
            else s.status = 'aktif';
            updateStudentKpis();
            render();
            showToast(json.message || `${s.nama} terverifikasi`);
            return;
          }
        } catch (err) {
          console.error(err);
        }

        s.status = 'aktif';
        updateStudentKpis();
        showToast(`${s.nama} terverifikasi`);
        render();
      });
      $('[data-history]', tr)?.addEventListener('click', () => showToast(`Riwayat preferensi ${s.nama} (${s.menuLabel || 'Paket Reguler'})`));
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

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
      nama: $('#sfName').value.trim(),
      nisn: $('#sfNisn').value.trim(),
      kelas: $('#sfClass').value.trim(),
      sekolah: $('#sfSchool').value.trim(),
      type: $('#sfType').value,
      status: $('#sfStatus').value,
      note: $('#sfNote').value.trim()
    };
    if (!data.nama || !data.nisn || !data.kelas || !data.sekolah) {
      showToast('Lengkapi semua kolom wajib');
      return;
    }

    const submitBtn = $('button[type="submit"]', form);
    if (submitBtn) submitBtn.disabled = true;

    try {
      const res = await fetch('/sppg/selections/save', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (res.ok && json.status === 'success' && json.student) {
        const existingIdx = students.findIndex((s) => s.nisn === json.student.nisn);
        if (existingIdx !== -1) {
          students[existingIdx] = json.student;
        } else {
          students.unshift(json.student);
        }
        closeModal(modal);
        populateFilters();
        updateStudentKpis();
        page = 1;
        render();
        showToast(json.message || (editing ? 'Preferensi diperbarui' : 'Preferensi siswa ditambahkan'));
        return;
      }
    } catch (err) {
      console.error(err);
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }

    if (editing) {
      Object.assign(editing, data);
      delete editing.menuLabel;
    } else {
      if (students.some((s) => s.nisn === data.nisn)) {
        showToast('NISN sudah terdaftar');
        return;
      }
      students.unshift(data);
    }
    closeModal(modal);
    showToast(editing ? 'Preferensi diperbarui' : 'Preferensi siswa ditambahkan');
    populateFilters();
    updateStudentKpis();
    page = 1;
    render();
  });

  $('#addPrefBtn')?.addEventListener('click', () => openForm(null));
  Object.values(f).forEach((el) => el?.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', () => { page = 1; render(); }));

  $('#resetFilterBtn')?.addEventListener('click', () => {
    if (f.q) f.q.value = '';
    if (f.school) f.school.value = '';
    if (f.cls) f.cls.value = '';
    if (f.diet) f.diet.value = '';
    if (f.status) f.status.value = '';
    page = 1;
    render();
  });

  $('#exportStudentBtn')?.addEventListener('click', () => {
    downloadCSV('pilihan-menu-siswa.csv', [
      ['NISN', 'Nama', 'Sekolah', 'Kelas', 'Pilihan Menu', 'Catatan', 'Status'],
      ...filtered().map((s) => [s.nisn, s.nama, s.sekolah, s.kelas, s.menuLabel || (TYPE_META[s.type] || TYPE_META.reguler).label, s.note, s.status])
    ]);
    showToast('Data siswa diunduh');
  });

  populateFilters();
  updateStudentKpis();
  render();
}

// =========================================================================
// 4. REVIEW & EVALUASI KEPUASAN MENU MBG
// =========================================================================

const DEFAULT_FEEDBACKS = [
  { code: 'D1', school: 'SMP Negeri 1 Denpasar', who: 'I Nyoman Sukra • Koordinator UKS & Gizi • Hari ini, 11:30 WITA', score: 5.0, text: '"Anak-anak sangat antusias dengan menu Ayam Panggang Madu dan Sayur Capcay. Kemasan tiba dalam boks termal higienis, suhu sup masih hangat, dan tidak ada wadah penyok. 542 porsi tuntas dibagikan tepat waktu."', tags: [['Suhu Wadah: Hangat (58°C)', 'green'], ['Sisa Porsi: < 1%', 'green']], kind: 'puas' },
  { code: 'G2', school: 'SMP Negeri 2 Gianyar', who: 'Ni Ketut Suartini • Pengawas Distribusi Sekolah • Hari ini, 10:45 WITA', score: 4.8, text: '"Kualitas nasi pulen dan tekstur daging sangat lembut untuk siswa. Catatan khusus: paket siswa alergi telur (Komang Aditya) terlabeli dengan segel peach sangat jelas sehingga tidak tertukar."', tags: [['Label Alergen: Akurat 100%', 'green'], ['Porsi Diterima: 765', 'gray']], kind: 'puas' },
  { code: 'K1', school: 'SMP Negeri 1 Klungkung', who: 'Luh Putu Aryani • Komite Sekolah & Tim UKS • Kemarin, 12:15 WITA', score: 3.8, text: '"Rasa sayuran sop jagung manis disukai anak-anak. Namun, armada tiba pada 09.55 WITA (sedikit mendekati jam istirahat pertama). Mohon estimasi waktu pengantaran untuk jalur Klungkung dimajukan 15 menit."', tags: [['Catatan: Jadwal Armada', 'orange'], ['Koreksi Logistik', 'gray']], kind: 'evaluasi', warn: true }
];

const DEFAULT_RATINGS = [[5, 82], [4, 14], [3, 4], [2, 0], [1, 0]];
const DEFAULT_FAVORITES = [
  ['Ayam Panggang Madu & Capcay', 'Paket Reguler SD/SMP', '99.1%'],
  ['Semur Gandik Sapi & Buncis', 'Paket Reguler Tinggi Protein', '97.4%'],
  ['Ikan Fillet Bumbu Kuning', 'Paket Omega-3 & Sayur Bayam', '95.0%']
];

function initReviewPage() {
  const list = $('#feedbackList');
  if (!list) return;

  const feedbacks = (typeof window !== 'undefined' && Array.isArray(window.SPPG_FEEDBACKS))
    ? window.SPPG_FEEDBACKS
    : DEFAULT_FEEDBACKS;

  const ratings = (typeof window !== 'undefined' && Array.isArray(window.SPPG_RATINGS))
    ? window.SPPG_RATINGS
    : DEFAULT_RATINGS;

  const favorites = (typeof window !== 'undefined' && Array.isArray(window.SPPG_FAVORITES))
    ? window.SPPG_FAVORITES
    : DEFAULT_FAVORITES;

  let filter = 'all';

  function render() {
    const rows = feedbacks.filter((f) => filter === 'all' || f.kind === filter);
    list.innerHTML = rows.length ? rows.map((f) => `
      <article class="fb-card ${f.warn ? 'warn' : ''}" data-i="${feedbacks.indexOf(f)}">
        <div class="fb-head">
          <div class="fb-avatar">${esc(f.code)}</div>
          <div class="fb-who"><h4>${esc(f.school)}</h4><p>${esc(f.who)}</p></div>
          <span class="fb-score"><span class="material-symbols-outlined">star</span>${Number(f.score).toFixed(1)}</span>
        </div>
        <div class="fb-quote">${esc(f.text)}</div>
        <div class="fb-foot">
          <div class="fb-tags">${(f.tags || []).map(([l, c]) => `<span class="pill ${c === 'green' ? 'pill-green' : c === 'orange' ? 'pill-orange' : 'pill-gray'}">${esc(l)}</span>`).join('')}</div>
          ${f.warn
            ? `<button type="button" class="fb-respond ${f.responded ? 'done' : ''}" data-respond>${f.responded ? '✓ Tanggapan Terkirim' : 'Tanggapi Tim Dapur <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>'}</button>`
            : '<span class="fb-verified"><span class="material-symbols-outlined">check_circle</span>Terverifikasi SPPG</span>'}
        </div>
      </article>`).join('') : '<p class="sppg-panel sppg-empty">Tidak ada umpan balik.</p>';

    $$('[data-respond]', list).forEach((btn) => btn.addEventListener('click', async () => {
      const card = btn.closest('.fb-card');
      const idx = Number(card?.dataset.i);
      const f = feedbacks[idx];
      if (!f || f.responded) return;

      btn.disabled = true;
      if (f.id) {
        try {
          const res = await fetch(`/sppg/review/${f.id}/respond`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': getCsrfToken(),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ response: 'Tanggapan diteruskan ke Tim Dapur' })
          });
          const json = await res.json();
          if (json.status === 'success') {
            f.responded = true;
            showToast(json.message || `Tanggapan untuk ${f.school} diteruskan ke Tim Dapur`);
            render();
            return;
          }
        } catch (err) {
          console.error(err);
        }
      }

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

  const ratingBarsEl = $('#ratingBars');
  if (ratingBarsEl) {
    ratingBarsEl.innerHTML = ratings.map(([star, pct]) => `
      <div class="rate-row">
        <span>${star} Bintang</span>
        <div class="rate-track"><div class="rate-fill ${star === 3 ? 'low' : star === 4 ? 'mid' : ''}" style="width:${pct}%"></div></div>
        <b>${pct}%</b>
      </div>`).join('');
  }

  const favListEl = $('#favList');
  if (favListEl) {
    favListEl.innerHTML = favorites.map(([name, sub, pct]) => `
      <li><div><b>${esc(name)}</b><small>${esc(sub)}</small></div><span class="pill pill-green">${pct} Suka</span></li>`).join('');
  }

  render();
}
