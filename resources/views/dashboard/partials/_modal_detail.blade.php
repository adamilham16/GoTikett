<!-- DETAIL MODAL -->
<div class="modal-overlay" id="m-detail">
  <div class="detail-modal">
    <div class="modal-header" style="flex-shrink:0">
      <div class="modal-title">Detail Tiket</div>
      <div style="display:flex;gap:7px;align-items:center" id="det-action-btns">
        <button class="btn btn-danger" id="btn-del-tix" onclick="deleteTix(curDetail)" style="font-size:12px;padding:5px 10px;display:none">🗑️ Hapus</button>
        <div class="modal-close" onclick="closeM('m-detail')">✕</div>
      </div>
    </div>
    <div id="detail-header-block" class="detail-header-block"></div>
    <div class="detail-inner">
      <div class="detail-main" id="detail-main"></div>
      <div class="detail-side" id="detail-side"></div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

