<main class="main" id="main-content">
  <div class="topbar">
    <h1 id="page-title">Papan Tiket</h1>
    <div class="live-dot"><span class="live-pulse"></span>Terhubung</div>
    <!-- Bell Notifikasi -->
    <div class="notif-wrap" id="notif-wrap">
      <button class="notif-btn" onclick="toggleNotifDropdown()" title="Notifikasi">
        🔔<span class="notif-badge" id="notif-badge" style="display:none">0</span>
      </button>
      <div class="notif-dropdown" id="notif-dropdown">
        <div class="notif-hdr">
          <span style="font-size:13px;font-weight:700">Notifikasi</span>
          <button class="notif-mark-btn" onclick="markAllNotifRead()">Tandai semua dibaca</button>
        </div>
        <div id="notif-list"><div class="notif-empty">Memuat…</div></div>
      </div>
    </div>
    <div class="search-box"><span style="color:var(--text3)">🔍</span><input type="text" placeholder="Cari tiket atau penanggung jawab..." oninput="searchTix(this.value)"></div>
    @if($user->type !== 'user')
    <div class="view-toggle">
      <button class="view-btn active" id="btn-board" onclick="switchView('board',this)" title="Tampilan Papan">⊞</button>
      <button class="view-btn" id="btn-list" onclick="switchView('list',this)" title="Tampilan Daftar">☰</button>
    </div>
    <a href="{{ route('tickets.export') }}" class="btn btn-ghost" title="Ekspor data tiket ke Excel">📊 Ekspor Excel</a>
    @endif
  </div>

  <div class="content">
    <!-- USER DASHBOARD -->
    <div id="user-dashboard" class="user-dashboard" style="{{ $user->type !== 'user' ? 'display:none' : '' }}">
      <div class="user-dash-header">
        <div class="user-dash-greeting" id="ud-greeting">Selamat datang, {{ $user->name }}!</div>
        <div class="user-dash-sub" id="ud-sub">Berikut ringkasan tiket kamu</div>
      </div>
      <div class="user-stats-grid" style="margin-bottom:10px">
        <div class="stat-card blue"><div class="ud-card-top"><div class="stat-icon blue">📋</div><div class="stat-label">Total Tiket</div></div><div class="stat-value" id="ovv-total">0</div><div class="stat-sub">Semua tiket kamu</div><div class="ud-stat-trend" id="ovv-trend-total" style="display:none"></div></div>
        <div class="stat-card orange"><div class="ud-card-top"><div class="stat-icon orange">💻</div><div class="stat-label">Sedang Berjalan</div></div><div class="stat-value" id="ovv-prog">0</div><div class="stat-sub">Dalam pengerjaan</div><div class="ud-stat-trend" id="ovv-trend-prog" style="display:none"></div></div>
        <div class="stat-card green"><div class="ud-card-top"><div class="stat-icon green">✅</div><div class="stat-label">Selesai / Tayang</div></div><div class="stat-value" id="ovv-done">0</div><div class="stat-sub">Sudah production</div><div class="ud-stat-trend" id="ovv-trend-done" style="display:none"></div></div>
        <div class="stat-card yellow"><div class="ud-card-top"><div class="stat-icon yellow">⏳</div><div class="stat-label">Menunggu Persetujuan</div></div><div class="stat-value" id="ovv-pending">0</div><div class="stat-sub">Menunggu acc atasan</div><div class="ud-stat-trend" id="ovv-trend-pending" style="display:none"></div></div>
      </div>
      <!-- USER CHARTS ROW -->
      <div class="charts-row" id="user-charts-row">
        <div class="chart-card">
          <div class="chart-card-hdr"><span class="chart-card-title">Status Tiket Saya</span></div>
          <div class="chart-canvas-wrap"><canvas id="chart-user-donut"></canvas></div>
        </div>
        <div class="chart-card">
          <div class="chart-card-hdr"><span class="chart-card-title">Tiket per Bulan</span></div>
          <div class="chart-canvas-wrap"><canvas id="chart-user-bar"></canvas></div>
        </div>
      </div>

      <div id="ud-banners" style="margin-bottom:8px"></div>
      <div class="ud-main-grid">

        <!-- ── Kolom Kiri (70%): Daftar Tiket ── -->
        <div class="ud-left-col">
          <div class="user-ticket-section">
            <div class="user-ticket-header">
              <span class="user-ticket-title">📄 Tiket Saya</span>
              <span id="ud-ticket-count" style="font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace"></span>
            </div>
            <div class="ud-filter-row">
              <div class="ud-tabs" id="ud-tabs">
                <button class="ud-tab active" onclick="udSetTab('all',this)">Semua<span class="ud-tab-count" id="udt-all">0</span></button>
                <button class="ud-tab" onclick="udSetTab('active',this)">Aktif<span class="ud-tab-count" id="udt-active">0</span></button>
                <button class="ud-tab" onclick="udSetTab('golive',this)">Tayang<span class="ud-tab-count" id="udt-golive">0</span></button>
                <button class="ud-tab" onclick="udSetTab('closed',this)">Ditutup<span class="ud-tab-count" id="udt-closed">0</span></button>
              </div>
              <div class="ud-type-filters">
                <button class="ud-type-btn active" onclick="udSetType('all',this)">Semua Tipe</button>
                <button class="ud-type-btn" onclick="udSetType('incident',this)">Insiden</button>
                <button class="ud-type-btn" onclick="udSetType('newproject',this)">Proyek Baru</button>
                <button class="ud-type-btn" onclick="udSetType('openrequest',this)">Open Request</button>
              </div>
            </div>
            <div id="ud-ticket-list" class="user-ticket-list"></div>
          </div>
        </div>

        <!-- ── Kolom Kanan (30%): Insight + SLA ── -->
        <div class="ud-right-col">

          <!-- Quick Stats Card -->
          <div class="ud-quick-card">
            <div class="ud-quick-header">Ringkasan</div>
            <div class="ud-quick-body">
              <div class="ud-qs-row">
                <span class="ud-qs-label">Sedang berjalan</span>
                <span class="ud-qs-num" id="qs-active" style="color:#0891b2">0</span>
              </div>
              <div class="ud-qs-row">
                <span class="ud-qs-label">Menunggu persetujuan</span>
                <span class="ud-qs-num" id="qs-pending" style="color:#d97706">0</span>
              </div>
              <div class="ud-qs-row">
                <span class="ud-qs-label">Selesai / ditutup</span>
                <span class="ud-qs-num" id="qs-done" style="color:#059669">0</span>
              </div>
            </div>
          </div>

          <!-- SLA Panel: selalu tampil di kolom kanan -->
          <div id="ud-stage-panel" style="background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07)">
            <div style="padding:10px 14px;border-bottom:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:12px;font-weight:700">📊 Progres SLA Aktif</span>
              <span id="ud-sla-count" style="font-size:10px;color:var(--text3);font-family:'JetBrains Mono',monospace"></span>
            </div>
            <div id="ud-sla-list" style="padding:8px 10px;display:flex;flex-direction:column;gap:6px"></div>
          </div>

        </div>
      </div>
    </div>

    <!-- IT/MANAGER DASHBOARD -->
    <div id="it-dashboard" style="{{ $user->type === 'user' ? 'display:none' : '' }}">
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card blue"><div class="stat-icon blue">📋</div><div class="stat-value" id="s-total">0</div><div class="stat-label">Total Tiket</div><div class="stat-sub" id="s-total-sub">—</div></div>
        <div class="stat-card yellow"><div class="stat-icon yellow">⏳</div><div class="stat-value" id="s-pend">0</div><div class="stat-label">Menunggu Persetujuan</div><div class="stat-sub">Menunggu persetujuan</div></div>
        <div class="stat-card orange"><div class="stat-icon orange">💻</div><div class="stat-value" id="s-active">0</div><div class="stat-label">Sedang Berjalan</div><div class="stat-sub">Disetujui, belum selesai</div></div>
        <div class="stat-card green"><div class="stat-icon green">🔒</div><div class="stat-value" id="s-closed">0</div><div class="stat-label">Selesai / Ditutup</div><div class="stat-sub">Sudah ditutup</div></div>
      </div>
      <!-- IT/MANAGER CHARTS ROW -->
      <div class="charts-row" id="it-charts-row">
        <div class="chart-card">
          <div class="chart-card-hdr"><span class="chart-card-title" id="it-chart-left-title">Statistik</span></div>
          <div class="chart-canvas-wrap"><canvas id="chart-it-left"></canvas></div>
        </div>
        <div class="chart-card">
          <div class="chart-card-hdr"><span class="chart-card-title" id="it-chart-right-title">Tren</span></div>
          <div class="chart-canvas-wrap"><canvas id="chart-it-right"></canvas></div>
        </div>
      </div>

      <!-- PANEL: Aktivitas + SLA Progress -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;margin-bottom:4px">
        <!-- Aktivitas Terbaru -->
        <div style="background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07)">
          <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface2)">
            <span style="font-size:13px;font-weight:700">🕐 Aktivitas Terbaru</span>
            <span id="activity-count" style="font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace"></span>
          </div>
          <div id="activity-list" style="max-height:210px;overflow-y:auto;padding:4px 0"></div>
        </div>
        <!-- SLA Progress per Tiket -->
        <div style="background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07)">
          <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface2)">
            <span style="font-size:13px;font-weight:700">📊 Status SLA Aktif</span>
            <span id="sla-summary-count" style="font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace"></span>
          </div>
          <div id="sla-list" style="max-height:210px;overflow-y:auto;padding:8px 12px;display:flex;flex-direction:column;gap:8px"></div>
        </div>
      </div>
    </div>

    <!-- BOARD VIEW -->
    <div id="board-view" style="{{ $user->type === 'user' ? 'display:none' : '' }}">
      <div class="board-header">
        <span class="board-title">Detail Report Tiket</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <div class="filter-group" id="fg-type">
            <button class="filter-btn active" onclick="setTF('all',this)">Semua</button>
            <button class="filter-btn" onclick="setTF('incident',this)">🚨 Insiden</button>
            <button class="filter-btn" onclick="setTF('newproject',this)">🆕 Proyek Baru</button>
            <button class="filter-btn" onclick="setTF('openrequest',this)">📬 Permintaan</button>
          </div>
        </div>
      </div>
      <div class="kanban" style="grid-template-columns:repeat(3,1fr)">
        <div class="kanban-col"><div class="col-header"><div class="col-dot" style="background:#94a3b8"></div><div class="col-title">Antrean</div><div class="col-count" id="cnt-todo">0</div></div><div class="col-body" id="col-todo"></div></div>
        <div class="kanban-col"><div class="col-header"><div class="col-dot" style="background:#60a5fa"></div><div class="col-title">Sedang Berjalan</div><div class="col-count" id="cnt-onprogress">0</div></div><div class="col-body" id="col-onprogress"></div></div>
        <div class="kanban-col"><div class="col-header"><div class="col-dot" style="background:#34d399"></div><div class="col-title">Selesai</div><div class="col-count" id="cnt-done">0</div></div><div class="col-body" id="col-done"></div></div>
      </div>
    </div>

    <div id="list-view" style="display:none">
      <div class="board-header"><span class="board-title">📄 Semua Tiket</span></div>
      <div id="tbl-load-banner" style="display:none;background:var(--yellow-bg);border:1px solid rgba(217,119,6,.25);border-radius:8px;padding:8px 14px;font-size:12px;color:var(--yellow);margin-bottom:10px"></div>
      <div class="table-wrap">
        <table><thead><tr><th>ID</th><th>Judul</th><th>Status</th><th>Penanggung Jawab</th><th>Dibuat</th><th>Tenggat / SLA</th><th>Durasi</th><th>Ditutup</th><th></th></tr></thead>
        <tbody id="tbl-body"></tbody></table>
      </div>
      <div id="tbl-pagination"></div>
    </div>
  </div>
</main>
