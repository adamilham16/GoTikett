<!-- CREATE MODAL -->
<div class="modal-overlay" id="m-create">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">🗂️ Buat Tiket Baru</div><div class="modal-close" onclick="closeM('m-create')">✕</div></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Judul Tiket *</label><input type="text" class="form-input" id="f-title" placeholder="Deskripsi singkat masalah atau fitur..."></div>
      <div class="form-group"><label class="form-label">Deskripsi</label><textarea class="form-textarea" id="f-desc" placeholder="Detail, acceptance criteria, langkah reproduksi..."></textarea></div>
      <div class="form-group"><label class="form-label">Tipe</label><select class="form-select" id="f-type"><option value="incident">🚨 Insiden</option><option value="newproject">🆕 Proyek Baru</option><option value="openrequest">📬 Permintaan</option></select></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Klien</label><select class="form-select" id="f-client" onchange="previewAssign()"></select></div>
        <div class="form-group"><label class="form-label">Kategori</label>
          <select class="form-select" id="f-cat" onchange="previewAssign()">
            <option value="Infra">🖥️ Infra</option><option value="Sistem">⚙️ Sistem</option><option value="Telko">📡 Telko</option>
          </select>
        </div>
      </div>
      <div id="assign-preview" style="padding:9px 12px;background:var(--accent-glow);border:1px solid rgba(8,145,178,.25);border-radius:8px;font-size:12px;color:var(--text2);display:none;margin-top:-6px">
        🤖 Auto assign ke: <strong id="assign-preview-name" style="color:var(--accent)"></strong>
      </div>
      <div class="form-group" style="margin-top:4px">
        <label class="form-label">📎 Lampiran Dokumen <span style="color:var(--text3);font-weight:400">(opsional, maks. 100 MB per file)</span></label>
        <div class="upload-area" id="upload-area" ondragover="event.preventDefault();this.classList.add('drag')" ondragleave="this.classList.remove('drag')" ondrop="handleDrop(event)">
          <input type="file" id="f-files" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.png,.jpg,.jpeg,.gif,.zip,.rar" onchange="handleFileSelect(this.files)">
          <div class="upload-icon">📂</div>
          <div class="upload-text">Klik atau seret & lepas file di sini</div>
          <div class="upload-sub">PDF, Word, Excel, PPT, Gambar, ZIP — maks. 100 MB per file</div>
        </div>
        <div class="file-list" id="file-list"></div>
      </div>
      <div style="background:var(--yellow-bg);border:1px solid rgba(245,197,66,.3);border-radius:8px;padding:10px 13px;font-size:12px;color:var(--yellow);margin-top:4px;">
        ⚠️ Tiket baru memerlukan persetujuan dari <strong id="approver-label">atasan</strong> sebelum masuk ke board aktif.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeM('m-create')">Batal</button>
      <button id="btn-submit-ticket" class="btn btn-primary" onclick="submitTicket()">📨 Kirim untuk Persetujuan</button>
    </div>
  </div>
</div>

