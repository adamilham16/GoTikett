<!-- APPROVAL QUEUE MODAL -->
<div class="modal-overlay" id="m-approval">
  <div class="modal" style="width:660px;max-width:96vw">
    <div class="modal-header"><div class="modal-title">⏳ Antrean Persetujuan</div><div class="modal-close" onclick="closeM('m-approval')">✕</div></div>
    <div class="modal-body" id="approval-body"></div>
  </div>
</div>

<!-- CONFIRM APPROVE MODAL -->
<div class="modal-overlay" id="m-confirm-approve">
  <div class="modal" style="width:420px;max-width:96vw">
    <div class="modal-header"><div class="modal-title">✅ Konfirmasi Persetujuan</div><div class="modal-close" onclick="closeM('m-confirm-approve')">✕</div></div>
    <div class="modal-body" style="text-align:center;padding:24px 20px">
      <div style="font-size:36px;margin-bottom:12px">⚠️</div>
      <div style="font-size:15px;font-weight:600;margin-bottom:8px">Setujui Tiket <span id="confirm-approve-id" style="color:var(--accent)"></span>?</div>
      <div style="font-size:13px;color:var(--text3)">Tiket akan disetujui dan tim IT dapat mulai merencanakan tugas.</div>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px">
      <button class="btn btn-ghost" onclick="closeM('m-confirm-approve')">Batal</button>
      <button class="btn btn-success" onclick="doApproveTix()">Ya, Setujui</button>
    </div>
  </div>
</div>

<!-- MODAL ALASAN PENOLAKAN -->
<div class="modal-overlay" id="m-reject-reason">
  <div class="modal" style="width:480px;max-width:96vw">
    <div class="modal-header">
      <div class="modal-title">❌ Alasan Penolakan</div>
      <div class="modal-close" onclick="closeM('m-reject-reason')">✕</div>
    </div>
    <div class="modal-body">
      <div style="font-size:13px;color:var(--text3);margin-bottom:14px">Tiket <strong id="reject-reason-ticket-id"></strong> akan ditolak dan dihapus dari sistem. Alasan wajib diisi agar user dapat memahami dan memperbaiki permintaannya.</div>
      <div class="form-group">
        <label class="form-label">Alasan Penolakan <span style="color:var(--red)">*</span></label>
        <textarea class="form-input" id="reject-reason-text" rows="4" style="resize:vertical;min-height:100px"
          placeholder="Tulis alasan penolakan agar user dapat memahami dan memperbaiki permintaannya..." oninput="onRejectReasonInput()"></textarea>
        <div id="reject-reason-hint" style="font-size:11px;color:var(--red);margin-top:4px;display:none">Alasan minimal 10 karakter.</div>
      </div>
    </div>
    <div class="modal-footer" style="gap:10px">
      <button class="btn btn-ghost" onclick="closeM('m-reject-reason')">Batal</button>
      <button id="btn-confirm-reject" class="btn btn-danger" onclick="doRejectTix(this)" disabled style="opacity:.5;cursor:not-allowed">🗑️ Konfirmasi Tolak</button>
    </div>
  </div>
</div>

