<aside class="sidebar" id="sidebar">
  <div class="logo">
    <div class="logo-icon" id="logo-icon">🗂️</div>
    <div><div class="logo-text" id="logo-name">GoTiket</div><div class="logo-sub" id="logo-sub">Atur Kerja, Dukung Tim</div></div>
  </div>
  <nav class="nav">
    @if($user->type === 'user')
    <div style="padding:0 0 12px">
      <button class="sb-create-btn" onclick="openCreate()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Tiket Baru
      </button>
    </div>
    @endif
    <div class="nav-section">
      <div class="nav-label">Ikhtisar</div>
      @if($user->type !== 'user')
      <div class="nav-item active" id="nav-board" onclick="switchView('board',this)"><span class="nav-icon">📋</span> Papan Tiket</div>
      <div class="nav-item" onclick="switchView('list',this)"><span class="nav-icon">📄</span> Semua Tiket <span class="badge" id="total-badge">0</span></div>
      @endif
      <div class="nav-item" id="nav-approval" onclick="openApprovalQueue()"><span class="nav-icon">⏳</span> Antrean Persetujuan <span class="badge yellow" id="pending-badge">0</span></div>
    </div>
    <div class="nav-section" id="nav-admin-section">
      <div class="nav-label">Administrasi</div>
      <div class="nav-item" onclick="openAutoAssign()"><span class="nav-icon">🤖</span> Atur Penugasan Otomatis</div>
      <div class="nav-item" onclick="window.location.href='{{ route('users.page') }}'"><span class="nav-icon">👥</span> Kelola User</div>
      <div class="nav-item" onclick="openAppSettings()"><span class="nav-icon">🎨</span> Pengaturan Aplikasi</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Departemen</div>
      <div id="dept-info" style="padding:6px 10px 4px;font-size:11px;color:var(--text3);line-height:1.6"></div>
    </div>
  </nav>
  <div class="sidebar-footer">
    <div class="nav-item" onclick="openChangePassword()" style="margin-bottom:6px;border:1px solid var(--border);border-radius:8px">
      <span class="nav-icon">🔑</span> Ganti Password
    </div>
    <div style="font-size:10px;color:var(--text3);padding:0 8px 7px;text-transform:uppercase;letter-spacing:.8px;font-weight:600;">Sesi Aktif</div>
    <div class="user-card" style="cursor:default">
      <div class="avatar" id="sb-av" style="background:{{ $user->color }}">{{ $user->initials }}</div>
      <div style="flex:1;min-width:0">
        <div class="user-name" id="sb-name">{{ $user->name }}</div>
        <div class="user-role" id="sb-role">{{ $user->role }} · {{ $user->dept }}</div>
      </div>
    </div>
    <div id="sb-type-badge" style="font-size:10px;text-align:center;padding:5px 0 0;font-weight:600;color:{{ $user->color }}">
      @if($user->type === 'it') 🔧 IT SIM (Admin)
      @elseif($user->type === 'it_manager') ⚙️ Manager IT
      @elseif($user->type === 'manager') ⭐ Kepala Dept (Penyetuju)
      @else 👤 Pengguna (Pemohon)
      @endif
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
      @csrf
      <button type="submit" class="nav-item" style="width:100%;color:var(--red);border:1px solid rgba(245,101,101,.25);border-radius:8px;justify-content:center;cursor:pointer;background:none;">
        <span>🚪</span> Logout
      </button>
    </form>
  </div>
</aside>
<button class="sidebar-toggle" id="sidebar-toggle" onclick="toggleSidebar()" title="Sembunyikan/tampilkan sidebar">☰</button>

