<!-- MODAL EDIT TUGAS -->
<div class="modal-overlay" id="m-edittask">
  <div class="modal" style="width:480px;max-width:96vw">
    <div class="modal-header"><div class="modal-title">✏️ Edit Tugas</div><div class="modal-close" onclick="closeM('m-edittask')">✕</div></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Nama Tugas *</label><input type="text" class="form-input" id="edit-task-title" placeholder="Judul tugas..."></div>
      <div class="form-group"><label class="form-label">Tenggat</label><input type="date" class="form-input" id="edit-task-due"></div>
      <div class="form-group"><label class="form-label">Catatan</label><textarea class="form-input" id="edit-task-notes" placeholder="Catatan atau deskripsi tugas..." style="min-height:80px;resize:vertical"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeM('m-edittask')">Batal</button>
      <button id="btn-save-edittask" class="btn btn-primary" onclick="saveEditTask()">💾 Simpan</button>
    </div>
  </div>
</div>

