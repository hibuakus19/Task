<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TaskFlow — Manajemen Tugas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ── Reset & Variabel ───────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:         #0d0f14;
  --surface:    #161920;
  --surface2:   #1e2230;
  --border:     #2a2f40;
  --text:       #e8eaf0;
  --muted:      #6b7280;
  --accent:     #6ee7b7;
  --accent2:    #34d399;
  --danger:     #f87171;
  --warn:       #fbbf24;
  --info:       #60a5fa;
  --radius:     12px;
  --radius-sm:  8px;
  --font-head:  'Syne', sans-serif;
  --font-body:  'DM Sans', sans-serif;
  --shadow:     0 4px 24px rgba(0,0,0,.4);
  --tr:         .18s ease;
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 15px;
  min-height: 100vh;
  /* grain texture */
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E");
}

/* ── Layout ─────────────────────────────────────── */
.app { max-width: 1100px; margin: 0 auto; padding: 0 20px 60px; }

/* ── Header ─────────────────────────────────────── */
header {
  padding: 40px 0 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.logo {
  font-family: var(--font-head);
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -1px;
  background: linear-gradient(135deg, var(--accent), #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.logo span { -webkit-text-fill-color: var(--muted); font-weight: 400; }

/* ── Stats ──────────────────────────────────────── */
.stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 28px;
}
.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  text-align: center;
  transition: transform var(--tr), border-color var(--tr);
}
.stat-card:hover { transform: translateY(-2px); border-color: var(--accent); }
.stat-num {
  font-family: var(--font-head);
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 6px;
}
.stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; }
.c-total  { color: var(--text); }
.c-selesai{ color: var(--accent); }
.c-proses { color: var(--info); }
.c-urgent { color: var(--danger); }

/* ── Toolbar ─────────────────────────────────────── */
.toolbar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: center;
}
.search-wrap {
  flex: 1;
  min-width: 220px;
  position: relative;
}
.search-wrap svg {
  position: absolute;
  left: 14px; top: 50%;
  transform: translateY(-50%);
  color: var(--muted);
  pointer-events: none;
}
input[type="search"],
select {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 14px;
  padding: 10px 14px;
  outline: none;
  transition: border-color var(--tr), box-shadow var(--tr);
}
input[type="search"] {
  width: 100%;
  padding-left: 42px;
}
input:focus, select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(110,231,183,.12);
}
select { cursor: pointer; }

/* ── Buttons ─────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  border-radius: var(--radius-sm);
  font-family: var(--font-head);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all var(--tr);
  white-space: nowrap;
}
.btn-primary {
  background: var(--accent);
  color: #0d0f14;
}
.btn-primary:hover { background: var(--accent2); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(110,231,183,.3); }
.btn-ghost {
  background: transparent;
  color: var(--muted);
  border: 1px solid var(--border);
}
.btn-ghost:hover { color: var(--text); border-color: var(--muted); }
.btn-danger { background: rgba(248,113,113,.15); color: var(--danger); border: 1px solid rgba(248,113,113,.25); }
.btn-danger:hover { background: rgba(248,113,113,.25); }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* ── Task Grid ───────────────────────────────────── */
#task-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.task-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: transform var(--tr), border-color var(--tr), box-shadow var(--tr);
  animation: slideUp .25s ease both;
  position: relative;
  overflow: hidden;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.task-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.task-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
}
.task-card[data-prioritas="tinggi"]::before { background: var(--danger); }
.task-card[data-prioritas="sedang"]::before { background: var(--warn); }
.task-card[data-prioritas="rendah"]::before { background: var(--info); }

.task-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.task-title {
  font-family: var(--font-head);
  font-size: 16px;
  font-weight: 700;
  line-height: 1.3;
  flex: 1;
}
.task-title.done { text-decoration: line-through; color: var(--muted); }

.task-desc { font-size: 13px; color: var(--muted); line-height: 1.6; }

.task-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 99px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .5px;
}
.badge-selesai { background: rgba(110,231,183,.15); color: var(--accent); }
.badge-proses  { background: rgba(96,165,250,.15);  color: var(--info); }
.badge-belum   { background: rgba(107,114,128,.15); color: var(--muted); }
.badge-tinggi  { background: rgba(248,113,113,.15); color: var(--danger); }
.badge-sedang  { background: rgba(251,191,36,.15);  color: var(--warn); }
.badge-rendah  { background: rgba(96,165,250,.1);   color: #93c5fd; }

.task-deadline { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 4px; }
.task-deadline.overdue { color: var(--danger); }

.task-actions { display: flex; gap: 8px; margin-top: auto; }

/* ── Empty State ────────────────────────────────── */
#empty {
  text-align: center;
  padding: 80px 20px;
  display: none;
}
#empty svg { opacity: .2; margin-bottom: 20px; }
#empty p { color: var(--muted); font-size: 15px; }

/* ── Modal ──────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 32px;
  width: 100%;
  max-width: 480px;
  box-shadow: var(--shadow);
  transform: translateY(20px);
  transition: transform .25s;
}
.modal-overlay.open .modal { transform: translateY(0); }
.modal h2 {
  font-family: var(--font-head);
  font-size: 1.4rem;
  font-weight: 800;
  margin-bottom: 24px;
}

.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.form-group label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; font-weight: 600; }
.form-group input,
.form-group textarea,
.form-group select {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 14px;
  padding: 10px 14px;
  outline: none;
  transition: border-color var(--tr), box-shadow var(--tr);
  width: 100%;
}
.form-group textarea { resize: vertical; min-height: 80px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

/* ── Toast ──────────────────────────────────────── */
#toast {
  position: fixed;
  bottom: 28px; right: 28px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 14px 20px;
  font-size: 14px;
  box-shadow: var(--shadow);
  transform: translateY(80px);
  opacity: 0;
  transition: all .3s ease;
  z-index: 999;
  max-width: 280px;
}
#toast.show { transform: translateY(0); opacity: 1; }
#toast.success { border-left: 3px solid var(--accent); }
#toast.error   { border-left: 3px solid var(--danger); }

/* ── Loading ────────────────────────────────────── */
.spinner {
  width: 36px; height: 36px;
  border: 3px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin .7s linear infinite;
  margin: 60px auto;
  display: none;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 640px) {
  .stats { grid-template-columns: 1fr 1fr; }
  .logo { font-size: 1.5rem; }
  #task-grid { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="app">

  <!-- Header -->
  <header>
    <div class="logo">TaskFlow <span>/ Manajemen Tugas</span></div>
    <button class="btn btn-primary" onclick="openModal()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tugas Baru
    </button>
  </header>

  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-num c-total" id="s-total">–</div>
      <div class="stat-label">Total Tugas</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-selesai" id="s-selesai">–</div>
      <div class="stat-label">Selesai</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-proses" id="s-proses">–</div>
      <div class="stat-label">Dalam Proses</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-urgent" id="s-urgent">–</div>
      <div class="stat-label">Urgent</div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="search" id="q" placeholder="Cari tugas…" oninput="debounceLoad()">
    </div>
    <select id="f-status" onchange="loadTasks()">
      <option value="">Semua Status</option>
      <option value="belum">Belum</option>
      <option value="proses">Dalam Proses</option>
      <option value="selesai">Selesai</option>
    </select>
    <select id="f-prioritas" onchange="loadTasks()">
      <option value="">Semua Prioritas</option>
      <option value="tinggi">Tinggi</option>
      <option value="sedang">Sedang</option>
      <option value="rendah">Rendah</option>
    </select>
    <select id="f-sort" onchange="loadTasks()">
      <option value="dibuat_pada">Terbaru</option>
      <option value="deadline">Deadline</option>
      <option value="prioritas">Prioritas</option>
      <option value="judul">Judul A–Z</option>
    </select>
  </div>

  <!-- Task Grid -->
  <div class="spinner" id="spinner"></div>
  <div id="task-grid"></div>
  <div id="empty">
    <svg width="72" height="72" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <rect x="3" y="3" width="18" height="18" rx="3"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="12" y2="13"/>
    </svg>
    <p>Belum ada tugas ditemukan.<br>Klik <strong>Tugas Baru</strong> untuk memulai.</p>
  </div>
</div>

<!-- Modal Form -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModal(event)">
  <div class="modal">
    <h2 id="modal-title">Tambah Tugas</h2>
    <div class="form-group">
      <label>Judul Tugas *</label>
      <input type="text" id="f-judul" placeholder="Nama tugas…">
    </div>
    <div class="form-group">
      <label>Deskripsi</label>
      <textarea id="f-deskripsi" placeholder="Detail tugas (opsional)…"></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Prioritas</label>
        <select id="f-prio">
          <option value="rendah">Rendah</option>
          <option value="sedang" selected>Sedang</option>
          <option value="tinggi">Tinggi</option>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select id="f-stat">
          <option value="belum" selected>Belum</option>
          <option value="proses">Dalam Proses</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Deadline</label>
      <input type="date" id="f-deadline">
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModalDirect()">Batal</button>
      <button class="btn btn-primary" onclick="saveTask()">Simpan</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast"></div>

<script>
// ── State ────────────────────────────────────────
let editId = null;
let debounceTimer = null;

// ── Init ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadTasks);

// ── Load Tasks ───────────────────────────────────
async function loadTasks() {
  const q   = document.getElementById('q').value;
  const st  = document.getElementById('f-status').value;
  const pr  = document.getElementById('f-prioritas').value;
  const so  = document.getElementById('f-sort').value;

  showSpinner(true);
  const params = new URLSearchParams({ q, status: st, prioritas: pr, sort: so });
  try {
    const res  = await fetch(`api.php?${params}`);
    const data = await res.json();
    renderStats(data.stats);
    renderTasks(data.tasks);
  } catch (e) {
    toast('Gagal memuat data.', 'error');
  }
  showSpinner(false);
}

function debounceLoad() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadTasks, 350);
}

// ── Render Stats ─────────────────────────────────
function renderStats(s) {
  if (!s) return;
  document.getElementById('s-total').textContent   = s.total   ?? 0;
  document.getElementById('s-selesai').textContent = s.selesai ?? 0;
  document.getElementById('s-proses').textContent  = s.proses  ?? 0;
  document.getElementById('s-urgent').textContent  = s.urgent  ?? 0;
}

// ── Render Tasks ─────────────────────────────────
function renderTasks(tasks) {
  const grid  = document.getElementById('task-grid');
  const empty = document.getElementById('empty');
  grid.innerHTML = '';

  if (!tasks || tasks.length === 0) {
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';

  tasks.forEach((t, i) => {
    const card = document.createElement('div');
    card.className = 'task-card';
    card.dataset.prioritas = t.prioritas;
    card.style.animationDelay = (i * 0.05) + 's';

    const isOverdue = t.deadline && new Date(t.deadline) < new Date() && t.status !== 'selesai';
    const deadlineStr = t.deadline
      ? `<span class="task-deadline ${isOverdue ? 'overdue' : ''}">
           <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
             <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
           </svg>
           ${formatDate(t.deadline)}${isOverdue ? ' ⚠' : ''}
         </span>`
      : '';

    card.innerHTML = `
      <div class="task-header">
        <div class="task-title ${t.status==='selesai'?'done':''}">${esc(t.judul)}</div>
      </div>
      ${t.deskripsi ? `<div class="task-desc">${esc(t.deskripsi)}</div>` : ''}
      <div class="task-meta">
        <span class="badge badge-${t.status}">${labelStatus(t.status)}</span>
        <span class="badge badge-${t.prioritas}">${labelPrioritas(t.prioritas)}</span>
        ${deadlineStr}
      </div>
      <div class="task-actions">
        <button class="btn btn-ghost btn-sm" onclick="editTask(${t.id})">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Edit
        </button>
        ${t.status !== 'selesai' ? `
        <button class="btn btn-ghost btn-sm" onclick="quickDone(${t.id})">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Selesai
        </button>` : ''}
        <button class="btn btn-danger btn-sm" onclick="deleteTask(${t.id})">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
            <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
          </svg>
          Hapus
        </button>
      </div>
    `;
    grid.appendChild(card);
  });
}

// ── Modal ─────────────────────────────────────────
function openModal(data = null) {
  editId = data ? data.id : null;
  document.getElementById('modal-title').textContent = data ? 'Edit Tugas' : 'Tugas Baru';
  document.getElementById('f-judul').value     = data?.judul     ?? '';
  document.getElementById('f-deskripsi').value = data?.deskripsi ?? '';
  document.getElementById('f-prio').value      = data?.prioritas ?? 'sedang';
  document.getElementById('f-stat').value      = data?.status    ?? 'belum';
  document.getElementById('f-deadline').value  = data?.deadline  ?? '';
  document.getElementById('modal-overlay').classList.add('open');
  setTimeout(() => document.getElementById('f-judul').focus(), 200);
}

function closeModal(e) {
  if (e.target === document.getElementById('modal-overlay')) closeModalDirect();
}
function closeModalDirect() {
  document.getElementById('modal-overlay').classList.remove('open');
}

async function editTask(id) {
  try {
    const res  = await fetch(`api.php?id=${id}`);
    const task = await res.json();
    if (task.error) throw new Error(task.error);
    openModal(task);
  } catch (e) {
    toast('Gagal memuat tugas.', 'error');
  }
}

// ── Save Task ─────────────────────────────────────
async function saveTask() {
  const judul = document.getElementById('f-judul').value.trim();
  if (!judul) { toast('Judul wajib diisi!', 'error'); return; }

  const payload = {
    judul,
    deskripsi: document.getElementById('f-deskripsi').value.trim(),
    prioritas:  document.getElementById('f-prio').value,
    status:     document.getElementById('f-stat').value,
    deadline:   document.getElementById('f-deadline').value || null,
  };

  const method = editId ? 'PUT' : 'POST';
  const url    = editId ? `api.php?id=${editId}` : 'api.php';

  try {
    const res  = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.error) throw new Error(data.error);
    closeModalDirect();
    toast(editId ? 'Tugas diperbarui!' : 'Tugas ditambahkan!', 'success');
    loadTasks();
  } catch (e) {
    toast('Gagal menyimpan: ' + e.message, 'error');
  }
}

// ── Quick Done ────────────────────────────────────
async function quickDone(id) {
  try {
    const res  = await fetch(`api.php?id=${id}`);
    const task = await res.json();
    task.status = 'selesai';
    await fetch(`api.php?id=${id}`, { method: 'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(task) });
    toast('Tugas ditandai selesai ✓', 'success');
    loadTasks();
  } catch (e) {
    toast('Gagal memperbarui.', 'error');
  }
}

// ── Delete Task ───────────────────────────────────
async function deleteTask(id) {
  if (!confirm('Hapus tugas ini?')) return;
  try {
    await fetch(`api.php?id=${id}`, { method: 'DELETE' });
    toast('Tugas dihapus.', 'success');
    loadTasks();
  } catch (e) {
    toast('Gagal menghapus.', 'error');
  }
}

// ── Helpers ───────────────────────────────────────
function showSpinner(v) {
  document.getElementById('spinner').style.display = v ? 'block' : 'none';
}

function toast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className   = 'show ' + type;
  clearTimeout(el._t);
  el._t = setTimeout(() => el.className = '', 3000);
}

function esc(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(d) {
  if (!d) return '';
  const [y,m,day] = d.split('-');
  return `${day}/${m}/${y}`;
}

function labelStatus(s) {
  return { belum:'● Belum', proses:'▶ Proses', selesai:'✓ Selesai' }[s] ?? s;
}
function labelPrioritas(p) {
  return { tinggi:'↑ Tinggi', sedang:'→ Sedang', rendah:'↓ Rendah' }[p] ?? p;
}

// Enter untuk submit
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModalDirect();
  if (e.key === 'Enter' && e.ctrlKey && document.getElementById('modal-overlay').classList.contains('open')) saveTask();
});
</script>
</body>
</html>
