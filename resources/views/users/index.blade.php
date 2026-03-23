<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Manajemen Pengguna — GoTiket</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#e8f4f8;--surface:#ffffff;--surface2:#f0f9fc;--surface3:#e0f2f7;
  --border:#b2dce8;--border2:#80c4d8;--text:#0d3349;--text2:#2e6e8a;--text3:#6aafc8;
  --accent:#0891b2;--accent2:#06b6d4;--accent-glow:rgba(8,145,178,0.15);
  --green:#059669;--green-bg:rgba(5,150,105,0.1);--yellow:#d97706;--yellow-bg:rgba(217,119,6,0.1);
  --red:#dc2626;--red-bg:rgba(220,38,38,0.1);--purple:#7c3aed;--purple-bg:rgba(124,58,237,0.1);
  --orange:#ea580c;--orange-bg:rgba(234,88,12,0.1);--teal:#0d9488;--teal-bg:rgba(13,148,136,0.1);
  --radius:10px;--radius-lg:16px;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{width:100%;min-height:100vh;}
body{background:var(--bg);color:var(--text);font-family:'Space Grotesk',sans-serif;}

/* Topbar */
.topbar{height:60px;background:rgba(255,255,255,0.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:16px;padding:0 28px;position:sticky;top:0;z-index:50;box-shadow:0 2px 12px rgba(8,145,178,0.08);}
.topbar-logo{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:700;color:var(--text);}
.topbar-logo .icon{width:32px;height:32px;background:linear-gradient(135deg,var(--accent),#818cf8);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);text-decoration:none;padding:6px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);transition:all .15s;cursor:pointer;}
.back-link:hover{background:var(--surface3);color:var(--text);}
.topbar-title{flex:1;font-size:16px;font-weight:700;color:var(--text);}
.topbar-user{font-size:13px;color:var(--text2);}

/* Content */
.content{padding:28px;max-width:1200px;margin:0 auto;}

/* Card */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 2px 10px rgba(8,145,178,0.07);}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);}
.card-title{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;}

/* Table */
table{width:100%;border-collapse:collapse;}
th{padding:10px 14px;text-align:left;font-size:11.5px;font-weight:600;color:var(--text2);background:var(--surface2);border-bottom:1px solid var(--border);text-transform:uppercase;letter-spacing:0.5px;}
td{padding:11px 14px;font-size:13.5px;border-bottom:1px solid var(--surface3);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--surface2);}
.no-col{width:48px;text-align:center;font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text3);}
.actions-col{width:130px;text-align:center;}

/* Avatar */
.avatar{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0;vertical-align:middle;margin-right:7px;}

/* Badge */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600;}
.badge-it{background:var(--accent-glow);color:var(--accent);}
.badge-manager{background:var(--purple-bg);color:var(--purple);}
.badge-user{background:var(--surface2);color:var(--text2);border:1px solid var(--border);}
.badge-active{background:var(--green-bg);color:var(--green);}
.badge-inactive{background:var(--red-bg);color:var(--red);}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .15s;}
.btn-primary{background:var(--accent);color:white;}.btn-primary:hover{background:var(--accent2);}
.btn-sm{padding:4px 8px;font-size:11.5px;border-radius:6px;}
.btn-edit{background:var(--surface2);color:var(--text2);border:1px solid var(--border);}.btn-edit:hover{background:var(--surface3);}
.btn-reset{background:var(--yellow-bg);color:var(--yellow);border:1px solid rgba(217,119,6,.3);}.btn-reset:hover{background:var(--yellow);color:white;}
.btn-deactivate{background:var(--red-bg);color:var(--red);border:1px solid rgba(220,38,38,.3);}.btn-deactivate:hover{background:var(--red);color:white;}
.btn-activate{background:var(--green-bg);color:var(--green);border:1px solid rgba(5,150,105,.3);}.btn-activate:hover{background:var(--green);color:white;}
.btn:disabled{opacity:.4;cursor:not-allowed;}

/* Actions group */
.action-group{display:flex;gap:4px;justify-content:center;}

/* Legend */
.legend{display:flex;gap:16px;margin-top:14px;font-size:12px;color:var(--text3);padding:0 22px 14px;}

/* Modal */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:200;align-items:flex-start;justify-content:center;overflow-y:auto;padding:32px 16px;}
.overlay.active{display:flex;}
.modal{background:var(--surface);border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:0 20px 60px rgba(0,0,0,0.3);width:480px;max-width:100%;animation:modalIn .2s ease;margin:auto;}
@keyframes modalIn{from{opacity:0;transform:translateY(-12px) scale(0.97);}to{opacity:1;transform:none;}}
.modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);}
.modal-title{font-size:15px;font-weight:700;}
.modal-close{background:none;border:none;font-size:18px;cursor:pointer;color:var(--text3);padding:2px 6px;border-radius:5px;line-height:1;}
.modal-close:hover{background:var(--surface3);color:var(--text);}
.modal-body{padding:22px;}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;padding:14px 22px;border-top:1px solid var(--border);}

/* Form */
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:12.5px;font-weight:600;color:var(--text2);margin-bottom:5px;}
.form-control{width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;font-family:inherit;color:var(--text);background:var(--surface);transition:border-color .15s;outline:none;}
.form-control:focus{border-color:var(--accent);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.form-hint{font-size:11.5px;color:var(--text3);margin-top:4px;}

/* Toast */
#toast{position:fixed;bottom:24px;right:24px;background:var(--text);color:white;padding:11px 18px;border-radius:10px;font-size:13px;z-index:9999;opacity:0;transition:opacity .3s;pointer-events:none;max-width:320px;}
#toast.show{opacity:1;}
#toast.err{background:var(--red);}
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
  <div class="topbar-logo">
    <div class="icon">🗂️</div>
    <span>GoTiket</span>
  </div>
  <a class="back-link" href="{{ route('dashboard') }}">← Kembali ke Dashboard</a>
  <div class="topbar-title">👥 Manajemen Pengguna</div>
  <div class="topbar-user">{{ $curUser->name }} ({{ $curUser->type }})</div>
</div>

<!-- Content -->
<div class="content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">👥 Daftar Pengguna</div>
      <button class="btn btn-primary" onclick="openAdd()">+ Tambah User</button>
    </div>
    <div style="overflow-x:auto;">
      <table id="user-table">
        <thead>
          <tr>
            <th class="no-col">No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Tipe</th>
            <th>Dept / Jabatan</th>
            <th>Approver</th>
            <th>Status</th>
            <th class="actions-col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $i => $u)
          <tr id="row-{{ $u->id }}">
            <td class="no-col">{{ $i + 1 }}</td>
            <td>
              <span class="avatar" style="background:{{ $u->color ?? '#60a5fa' }}">{{ $u->initials }}</span>
              {{ $u->name }}
            </td>
            <td style="font-family:'JetBrains Mono',monospace;font-size:12.5px;">{{ $u->username }}</td>
            <td>
              @if($u->type === 'it')
                <span class="badge badge-it">🔧 IT</span>
              @elseif($u->type === 'it_manager')
                <span class="badge badge-it">⚙️ Manager IT</span>
              @elseif($u->type === 'manager')
                <span class="badge badge-manager">⭐ Manager</span>
              @else
                <span class="badge badge-user">👤 User</span>
              @endif
            </td>
            <td>
              @if($u->dept)
                <span style="font-size:12.5px;">{{ $u->dept }}</span>
                @if($u->role)
                  <span style="color:var(--text3);font-size:12px;"> / {{ $u->role }}</span>
                @endif
              @else
                <span style="color:var(--text3);">—</span>
              @endif
            </td>
            <td style="font-size:12.5px;">{{ $u->approver?->name ?? '—' }}</td>
            <td>
              @if($u->is_active)
                <span class="badge badge-active">✅ Aktif</span>
              @else
                <span class="badge badge-inactive">❌ Nonaktif</span>
              @endif
            </td>
            <td>
              <div class="action-group">
                <button class="btn btn-sm btn-edit" title="Edit"
                  onclick="openEdit({id:{{ $u->id }},name:'{{ addslashes($u->name) }}',type:'{{ $u->type }}',role:'{{ addslashes($u->role ?? '') }}',dept:'{{ addslashes($u->dept ?? '') }}',color:'{{ $u->color ?? '' }}',approver_id:{{ $u->approver_id ?? 'null' }}})">✏️</button>
                <button class="btn btn-sm btn-reset" title="Reset Password"
                  onclick="openResetPass({{ $u->id }},'{{ addslashes($u->name) }}')">🔑</button>
                @if($u->id === $curUser->id)
                  <button class="btn btn-sm btn-deactivate" disabled title="Tidak bisa menonaktifkan diri sendiri">🚫</button>
                @elseif($u->is_active)
                  <button class="btn btn-sm btn-deactivate" title="Nonaktifkan"
                    onclick="toggleActive({{ $u->id }},'{{ addslashes($u->name) }}',true)">🚫</button>
                @else
                  <button class="btn btn-sm btn-activate" title="Aktifkan"
                    onclick="toggleActive({{ $u->id }},'{{ addslashes($u->name) }}',false)">✅</button>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="legend">
      <span>✏️ Edit</span>
      <span>🔑 Reset Password</span>
      <span>🚫 Nonaktifkan</span>
      <span>✅ Aktifkan</span>
    </div>
  </div>
</div>

<!-- Modal: Tambah User -->
<div class="overlay" id="overlay-add" onclick="closeModal('add')">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title">+ Tambah Pengguna</div>
      <button class="modal-close" onclick="closeModal('add')">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input class="form-control" id="add-name" placeholder="Budi Santoso">
        </div>
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input class="form-control" id="add-username" placeholder="budi">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tipe *</label>
          <select class="form-control" id="add-type" onchange="onAddTypeChange()">
            <option value="user">👤 User</option>
            <option value="manager">⭐ Manager</option>
            <option value="it">🔧 IT</option>
            <option value="it_manager">⚙️ Manager IT</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Departemen *</label>
          <input class="form-control" id="add-dept" placeholder="IT, HRD, Finance…">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Jabatan *</label>
          <input class="form-control" id="add-role" placeholder="Staff, Supervisor…">
        </div>
        <div class="form-group" id="add-approver-group">
          <label class="form-label">Approver (Manager)</label>
          <select class="form-control" id="add-approver">
            <option value="">— Tidak Ada —</option>
            @foreach($managers as $m)
              <option value="{{ $m->id }}">{{ $m->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password <span style="font-weight:400;color:var(--text3)">(opsional)</span></label>
          <input class="form-control" id="add-pass" type="password" placeholder="Kosongkan = username+123">
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password</label>
          <input class="form-control" id="add-pass2" type="password" placeholder="Ulangi jika diisi">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-edit" onclick="closeModal('add')">Batal</button>
      <button class="btn btn-primary" onclick="submitAdd()">Tambah</button>
    </div>
  </div>
</div>

<!-- Modal: Edit User -->
<div class="overlay" id="overlay-edit" onclick="closeModal('edit')">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title">✏️ Edit Pengguna</div>
      <button class="modal-close" onclick="closeModal('edit')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input class="form-control" id="edit-name">
        </div>
        <div class="form-group">
          <label class="form-label">Tipe *</label>
          <select class="form-control" id="edit-type" onchange="onEditTypeChange()">
            <option value="user">👤 User</option>
            <option value="manager">⭐ Manager</option>
            <option value="it">🔧 IT</option>
            <option value="it_manager">⚙️ Manager IT</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Departemen *</label>
          <input class="form-control" id="edit-dept">
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan *</label>
          <input class="form-control" id="edit-role">
        </div>
      </div>
      <div class="form-group" id="edit-approver-group">
        <label class="form-label">Approver (Manager)</label>
        <select class="form-control" id="edit-approver">
          <option value="">— Tidak Ada —</option>
          @foreach($managers as $m)
            <option value="{{ $m->id }}">{{ $m->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-edit" onclick="closeModal('edit')">Batal</button>
      <button class="btn btn-primary" onclick="submitEdit()">Simpan</button>
    </div>
  </div>
</div>

<!-- Modal: Reset Password -->
<div class="overlay" id="overlay-reset" onclick="closeModal('reset')">
  <div class="modal" onclick="event.stopPropagation()" style="width:380px;">
    <div class="modal-header">
      <div class="modal-title">🔑 Reset Password</div>
      <button class="modal-close" onclick="closeModal('reset')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="reset-id">
      <p id="reset-name-label" style="font-size:13px;color:var(--text2);margin-bottom:14px;"></p>
      <div class="form-group">
        <label class="form-label">Password Baru *</label>
        <input class="form-control" id="reset-pass" type="password" placeholder="Min. 6 karakter">
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password *</label>
        <input class="form-control" id="reset-pass2" type="password" placeholder="Ulangi password">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-edit" onclick="closeModal('reset')">Batal</button>
      <button class="btn btn-primary" onclick="submitReset()">Reset</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, type = 'ok') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show' + (type === 'err' ? ' err' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.className = '', 3000);
}

function closeModal(which) {
  document.getElementById('overlay-' + which).classList.remove('active');
}

function apiJson(url, method, data) {
  return fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: data ? JSON.stringify(data) : undefined,
  }).then(r => r.json());
}

/* ── ADD ── */
function onAddTypeChange() {
  const t = document.getElementById('add-type').value;
  document.getElementById('add-approver-group').style.display = (t === 'user') ? '' : 'none';
}
function openAdd() {
  ['add-name','add-username','add-role','add-dept','add-pass','add-pass2'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('add-type').value = 'user';
  document.getElementById('add-approver').value = '';
  onAddTypeChange();
  document.getElementById('overlay-add').classList.add('active');
  setTimeout(() => document.getElementById('add-name').focus(), 50);
}
function submitAdd() {
  const name     = document.getElementById('add-name').value.trim();
  const username = document.getElementById('add-username').value.trim();
  const type     = document.getElementById('add-type').value;
  const dept     = document.getElementById('add-dept').value.trim();
  const role     = document.getElementById('add-role').value.trim();
  const approver = document.getElementById('add-approver').value;
  const pass     = document.getElementById('add-pass').value;
  const pass2    = document.getElementById('add-pass2').value;

  if (!name || !username || !dept || !role) { showToast('Isi semua field wajib.', 'err'); return; }
  if (pass && pass.length < 6)  { showToast('Password minimal 6 karakter.', 'err'); return; }
  if (pass && pass !== pass2)   { showToast('Konfirmasi password tidak cocok.', 'err'); return; }

  const payload = { name, username, type, dept, role, approver_id: approver || null };
  if (pass) payload.password = pass;

  apiJson('{{ route('users.store') }}', 'POST', payload)
    .then(r => {
      if (r.success) {
        closeModal('add');
        const msg = pass ? '✅ User ' + r.user.name + ' ditambahkan.' : '✅ User ' + r.user.name + ' ditambahkan. Password default: username+123';
        showToast(msg);
        location.reload();
      } else {
        const msg = r.errors ? Object.values(r.errors).flat().join(', ') : (r.message || 'Gagal');
        showToast(msg, 'err');
      }
    });
}

/* ── EDIT ── */
function onEditTypeChange() {
  const t = document.getElementById('edit-type').value;
  document.getElementById('edit-approver-group').style.display = (t === 'user') ? '' : 'none';
}
function openEdit(data) {
  document.getElementById('edit-id').value        = data.id;
  document.getElementById('edit-name').value      = data.name;
  document.getElementById('edit-type').value      = data.type;
  document.getElementById('edit-dept').value      = data.dept;
  document.getElementById('edit-role').value      = data.role;
  document.getElementById('edit-approver').value  = data.approver_id || '';
  onEditTypeChange();
  document.getElementById('overlay-edit').classList.add('active');
  setTimeout(() => document.getElementById('edit-name').focus(), 50);
}
function submitEdit() {
  const id       = document.getElementById('edit-id').value;
  const name     = document.getElementById('edit-name').value.trim();
  const type     = document.getElementById('edit-type').value;
  const dept     = document.getElementById('edit-dept').value.trim();
  const role     = document.getElementById('edit-role').value.trim();
  const approver = document.getElementById('edit-approver').value;

  if (!name || !dept || !role) { showToast('Isi semua field wajib.', 'err'); return; }

  apiJson(`/users/${id}`, 'PATCH', { name, type, dept, role, approver_id: approver || null })
    .then(r => {
      if (r.success) {
        closeModal('edit');
        showToast('✅ Perubahan disimpan.');
        location.reload();
      } else {
        showToast(r.message || 'Gagal', 'err');
      }
    });
}

/* ── RESET PASSWORD ── */
function openResetPass(id, name) {
  document.getElementById('reset-id').value = id;
  document.getElementById('reset-name-label').textContent = 'Reset password untuk: ' + name;
  document.getElementById('reset-pass').value  = '';
  document.getElementById('reset-pass2').value = '';
  document.getElementById('overlay-reset').classList.add('active');
  setTimeout(() => document.getElementById('reset-pass').focus(), 50);
}
function submitReset() {
  const id   = document.getElementById('reset-id').value;
  const pass = document.getElementById('reset-pass').value;
  const pass2 = document.getElementById('reset-pass2').value;

  if (pass.length < 6) { showToast('Password minimal 6 karakter.', 'err'); return; }
  if (pass !== pass2)  { showToast('Konfirmasi password tidak cocok.', 'err'); return; }

  apiJson(`/users/${id}/reset-password`, 'PATCH', { password: pass })
    .then(r => {
      if (r.success) {
        closeModal('reset');
        showToast('✅ Password berhasil direset.');
      } else {
        showToast(r.message || 'Gagal', 'err');
      }
    });
}

/* ── TOGGLE ACTIVE ── */
function toggleActive(id, name, isActive) {
  const action = isActive ? 'nonaktifkan' : 'aktifkan';
  if (!confirm(`${isActive ? 'Nonaktifkan' : 'Aktifkan'} pengguna "${name}"?`)) return;

  apiJson(`/users/${id}/toggle-active`, 'PATCH')
    .then(r => {
      if (r.success) {
        showToast('✅ User ' + name + ' telah di' + action + 'kan.');
        location.reload();
      } else {
        showToast(r.message || 'Gagal', 'err');
      }
    });
}

/* ESC to close */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    ['add','edit','reset'].forEach(w => closeModal(w));
  }
});
</script>
</body>
</html>
