<!-- AUTO ASSIGN MODAL -->
<div class="modal-overlay" id="m-autoassign">
  <div class="modal" style="width:720px;max-width:95vw">
    <div class="modal-header"><div class="modal-title">🤖 Penugasan Otomatis</div><div class="modal-close" onclick="closeM('m-autoassign')">✕</div></div>
    <div class="modal-body">
      <div style="display:flex;gap:4px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:3px;margin-bottom:16px;width:fit-content">
        <button class="filter-btn active" id="tab-rules" onclick="switchAATab('rules')">📋 Aturan Penugasan</button>
        <button class="filter-btn" id="tab-clients" onclick="switchAATab('clients')">🏢 Kelola Klien</button>
      </div>
      <div id="aa-tab-rules">
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
          <div style="font-size:12px;font-weight:700;margin-bottom:10px">➕ Buat Aturan Baru</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;align-items:end">
            <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Kategori</label>
              <select class="form-select" id="aa-kat" style="font-size:12px"><option value="Infra">🖥️ Infra</option><option value="Sistem">⚙️ Sistem</option><option value="Telko">📡 Telko</option></select></div>
            <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Klien</label><select class="form-select" id="aa-client" style="font-size:12px"></select></div>
            <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Tugaskan ke IT</label><select class="form-select" id="aa-user" style="font-size:12px"></select></div>
            <button class="btn btn-primary" onclick="saveAutoAssign()" style="padding:7px 12px;font-size:12px;white-space:nowrap">💾 Simpan</button>
          </div>
        </div>
        <div id="aa-table"></div>
      </div>
      <div id="aa-tab-clients" style="display:none">
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
          <div style="font-size:12px;font-weight:700;margin-bottom:10px">➕ Tambah Klien Baru</div>
          <div style="display:flex;gap:8px;align-items:center">
            <input type="text" class="form-input" id="new-client-name" placeholder="Nama client..." style="flex:1;font-size:12px">
            <button class="btn btn-primary" onclick="addClient()" style="white-space:nowrap;font-size:12px">➕ Tambah Tugas</button>
          </div>
        </div>
        <div id="client-table"></div>
      </div>
    </div>
  </div>
</div>

<!-- USER MANAGEMENT MODAL -->
<div class="modal-overlay" id="m-users">
  <div class="modal" style="width:720px;max-width:95vw">
    <div class="modal-header"><div class="modal-title">👥 Kelola Pengguna</div><div class="modal-close" onclick="closeM('m-users')">✕</div></div>
    <div class="modal-body">
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
        <div style="font-size:12px;font-weight:700;margin-bottom:12px">➕ Tambah Pengguna Baru</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Nama Lengkap *</label><input type="text" class="form-input" id="nu-name" placeholder="Contoh: Budi Santoso" style="font-size:12px"></div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Username *</label><input type="text" class="form-input" id="nu-username" placeholder="Contoh: budi" style="font-size:12px;font-family:'JetBrains Mono',monospace"></div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Jabatan / Peran</label><input type="text" class="form-input" id="nu-role" placeholder="Contoh: Staff, Supervisor" style="font-size:12px"></div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Tipe Pengguna</label>
            <select class="form-select" id="nu-type" style="font-size:12px" onchange="onNuTypeChange()">
              <option value="user">👤 Pengguna (Pemohon)</option><option value="manager">⭐ Kepala Dept (Penyetuju)</option><option value="it">🔧 IT SIM (Admin)</option><option value="it_manager">⚙️ Manager IT</option>
            </select>
          </div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Departemen</label><select class="form-select" id="nu-dept" style="font-size:12px"></select></div>
          <div id="nu-approver-wrap"><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:4px">Atasan / Penyetuju</label><select class="form-select" id="nu-approver" style="font-size:12px"></select></div>
          <div style="display:flex;align-items:flex-end"><button id="btn-add-user" class="btn btn-primary" onclick="addUser()" style="width:100%;font-size:12px">➕ Tambah Pengguna</button></div>
        </div>
      </div>
      <div style="font-size:12px;font-weight:700;margin-bottom:8px">👥 Daftar Pengguna</div>
      <div id="user-table"></div>
    </div>
  </div>
</div>

<!-- APP SETTINGS MODAL -->
<div class="modal-overlay" id="m-appsettings">
  <div class="modal" style="width:560px;max-width:95vw">
    <div class="modal-header"><div class="modal-title">🎨 Pengaturan Aplikasi</div><div class="modal-close" onclick="closeM('m-appsettings')">✕</div></div>
    <div class="modal-body" style="display:flex;flex-direction:column;gap:20px">
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:16px">
        <div style="font-size:12px;font-weight:700;margin-bottom:12px">🏷️ Identitas Aplikasi</div>
        <div style="display:grid;grid-template-columns:80px 1fr 1fr;gap:10px;align-items:end">
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Ikon</label>
            <input type="text" class="form-input" id="cfg-icon" value="🗂️" style="font-size:22px;text-align:center;padding:6px 8px;cursor:pointer" maxlength="2" onclick="toggleEmojiPicker()" readonly></div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Nama Aplikasi</label><input type="text" class="form-input" id="cfg-name" placeholder="GoTiket" style="font-size:13px"></div>
          <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Subjudul</label><input type="text" class="form-input" id="cfg-subtitle" placeholder="Atur Kerja, Dukung Tim" style="font-size:13px"></div>
        </div>
        <div id="emoji-picker" style="display:none;margin-top:10px;background:var(--surface3);border:1px solid var(--border);border-radius:8px;padding:10px">
          <div style="font-size:10px;color:var(--text3);font-weight:600;text-transform:uppercase;margin-bottom:8px">Pilih Ikon</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px" id="emoji-grid"></div>
        </div>
      </div>
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:16px">
        <div style="font-size:12px;font-weight:700;margin-bottom:12px">🖼️ Latar Belakang</div>
        <div style="display:flex;gap:6px;margin-bottom:14px">
          <button class="filter-btn active" id="bg-tab-solid" onclick="switchBgTab('solid')">🎨 Warna Solid</button>
          <button class="filter-btn" id="bg-tab-gradient" onclick="switchBgTab('gradient')">🌈 Gradien</button>
          <button class="filter-btn" id="bg-tab-image" onclick="switchBgTab('image')">🖼️ Gambar / URL</button>
        </div>
        <div id="bg-panel-solid">
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="color" id="cfg-bg-color" value="#0a0c10" style="width:40px;height:36px;border:none;background:none;cursor:pointer;border-radius:6px">
            <input type="text" id="cfg-bg-color-hex" class="form-input" value="#0a0c10" style="font-size:12px;font-family:'JetBrains Mono',monospace;width:110px" oninput="syncColorFromHex(this.value)">
            <div style="display:flex;gap:5px;flex-wrap:wrap" id="bg-presets"></div>
          </div>
        </div>
        <div id="bg-panel-gradient" style="display:none">
          <div style="display:flex;flex-wrap:wrap;gap:8px" id="gradient-presets"></div>
          <div style="margin-top:10px"><input type="text" class="form-input" id="cfg-bg-gradient" placeholder="linear-gradient(135deg, #0a0c10, #1a1d24)" style="font-size:12px;font-family:'JetBrains Mono',monospace"></div>
        </div>
        <div id="bg-panel-image" style="display:none">
          <input type="text" class="form-input" id="cfg-bg-image" placeholder="https://..." style="font-size:12px">
        </div>
        <div style="margin-top:14px">
          <div id="bg-preview" style="height:70px;border-radius:8px;border:1px solid var(--border);background:#0a0c10;transition:all .3s;display:flex;align-items:center;justify-content:center">
            <span style="font-size:11px;color:rgba(255,255,255,.3)">Pratinjau Latar</span>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn btn-primary" onclick="saveAppSettings()" style="flex:1">💾 Simpan Pengaturan</button>
        <button class="btn btn-ghost" onclick="resetAppSettings()">↺ Reset Default</button>
        <button class="btn btn-ghost" onclick="closeM('m-appsettings')">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- CHANGE PASSWORD MODAL -->
<div class="modal-overlay" id="m-password">
  <div class="modal" style="width:420px;max-width:95vw">
    <div class="modal-header"><div class="modal-title">🔑 Ganti Password</div><div class="modal-close" onclick="closeM('m-password')">✕</div></div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:10px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:20px">
        <div class="avatar" style="width:38px;height:38px;font-size:14px;background:{{ $user->color }}">{{ $user->initials }}</div>
        <div><div style="font-size:13px;font-weight:700">{{ $user->name }}</div><div style="font-size:11px;color:var(--text3)">{{ $user->role }} · {{ $user->dept }}</div></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px">
        <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Password Lama *</label>
          <div style="position:relative"><input type="password" class="form-input" id="pw-old" placeholder="Masukkan password lama" style="padding-right:40px"><span onclick="togglePw('pw-old',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text3)">👁</span></div></div>
        <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Password Baru *</label>
          <div style="position:relative"><input type="password" class="form-input" id="pw-new" placeholder="Minimal 6 karakter" style="padding-right:40px" oninput="checkPwStrength(this.value)"><span onclick="togglePw('pw-new',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text3)">👁</span></div>
          <div style="margin-top:6px"><div style="height:3px;background:var(--border);border-radius:10px;overflow:hidden"><div id="pw-strength-bar" style="height:100%;width:0%;border-radius:10px;transition:all .3s"></div></div><div id="pw-strength-label" style="font-size:10px;color:var(--text3);margin-top:3px"></div></div></div>
        <div><label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;display:block;margin-bottom:5px">Konfirmasi Password Baru *</label>
          <div style="position:relative"><input type="password" class="form-input" id="pw-confirm" placeholder="Ulangi password baru" style="padding-right:40px"><span onclick="togglePw('pw-confirm',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text3)">👁</span></div></div>
      </div>
      <div style="display:flex;gap:8px;margin-top:20px">
        <button id="btn-save-password" class="btn btn-primary" onclick="savePassword()" style="flex:1">💾 Simpan Password</button>
        <button class="btn btn-ghost" onclick="closeM('m-password')">Batal</button>
      </div>
    </div>
  </div>
</div>

@php
$_curUser = ['id'=>$user->id,'name'=>$user->name,'type'=>$user->type,'role'=>$user->role,'dept'=>$user->dept,'color'=>$user->color,'initials'=>$user->initials,'approver'=>$user->approver?->name];
$_tickets = $tickets->map(function($t){ $f=$t->currentFreeze; return ['id'=>$t->ticket_id,'title'=>$t->title,'type'=>$t->type,'approval'=>$t->approval,'category'=>$t->category,'client'=>$t->client,'assignee'=>$t->assignee?->name,'assignee_color'=>$t->assignee?->color,'assignee_initials'=>$t->assignee?->initials,'creator'=>$t->creator?->name,'creator_id'=>$t->creator_id,'created_at'=>$t->created_at->toISOString(),'due_date'=>$t->due_date?->format('d M Y'),'closed_at'=>$t->closed_at?->toISOString(),'lead_time'=>$t->lead_time,'progress'=>$t->progress,'sla'=>$t->sla,'task_total'=>$t->tasks->count(),'task_done'=>$t->tasks->where('status','Done')->count(),'it_comment_count'=>$t->it_comment_count??0,'freeze_status'=>$t->freeze_status,'freeze_id'=>$f?->id,'freeze_duration'=>$f?->duration_days,'freeze_reason'=>$f?->reason,'freeze_requester'=>$f?->requester?->name,'freeze_ends_at'=>$f?->freeze_ends_at?->format('d M Y')]; })->values();
$_clients = $clients->map(fn($c)=>['id'=>$c->id,'nama'=>$c->nama])->values();
$_aa = $autoAssignRules->map(fn($r)=>['id'=>$r->id,'kategori'=>$r->kategori,'client'=>$r->client,'assignee'=>$r->assignee?->name])->values();
@endphp
