<!-- MODAL REQUEST FREEZE -->
<div class="modal-overlay" id="m-freeze">
  <div class="modal" style="width:480px;max-width:96vw">
    <div class="modal-header">
      <div class="modal-title">⏸ Request Pending / Freeze</div>
      <div class="modal-close" onclick="closeM('m-freeze')">✕</div>
    </div>
    <div class="modal-body">
      <div style="background:var(--purple-bg);border:1px solid rgba(124,58,237,0.25);border-radius:10px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--purple);line-height:1.5">
        <strong>ℹ️ Tentang Freeze:</strong> Request ini akan dikirim ke Manager untuk disetujui. Setelah disetujui, SLA tiket akan dihentikan sementara selama durasi yang ditentukan.
      </div>
      <div class="form-group">
        <label class="form-label">Durasi Freeze (hari) <span style="color:var(--red)">*</span></label>
        <input type="number" class="form-input" id="freeze-duration" min="1" max="365" placeholder="Contoh: 7">
      </div>
      <div class="form-group">
        <label class="form-label">Alasan Freeze <span style="color:var(--red)">*</span></label>
        <textarea class="form-input" id="freeze-reason" placeholder="Jelaskan alasan tiket perlu di-freeze..." style="min-height:90px;resize:vertical"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeM('m-freeze')">Batal</button>
      <button id="btn-submit-freeze" class="btn btn-primary" onclick="submitFreeze(this)" style="background:var(--purple);border-color:var(--purple)">⏸ Kirim Request</button>
    </div>
  </div>
</div>

