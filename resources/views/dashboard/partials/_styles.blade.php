<style>
{{-- CSS identik dengan versi HTML original --}}
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
html,body{width:100%;height:100%;}
body{background:transparent;color:var(--text);font-family:'Space Grotesk',sans-serif;min-height:100vh;width:100vw;display:flex;overflow-x:hidden;position:relative;}
.sidebar{width:240px;min-height:100vh;background:linear-gradient(180deg,#0c4a6e 0%,#0e7490 60%,#0d9488 100%);border-right:1px solid rgba(255,255,255,0.15);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;box-shadow:4px 0 20px rgba(8,145,178,0.2);transition:width .25s ease,transform .25s ease;}
.sidebar.collapsed{width:0;overflow:hidden;transform:translateX(-240px);}
.sidebar-toggle{position:fixed;top:14px;left:252px;z-index:200;width:30px;height:32px;background:linear-gradient(135deg,#0c4a6e,#0e7490);border:1px solid rgba(255,255,255,0.25);border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;transition:left .25s ease,background .15s;box-shadow:2px 2px 8px rgba(0,0,0,.2);}
.sidebar-toggle:hover{background:linear-gradient(135deg,#0e7490,#0d9488);}
.sidebar.collapsed~.sidebar-toggle,.sidebar-toggle.sb-collapsed{left:12px;}
.logo{padding:20px;border-bottom:1px solid rgba(255,255,255,0.15);display:flex;align-items:center;gap:10px;}
.logo-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),#818cf8);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;}
.logo-text{font-size:15px;font-weight:700;color:#ffffff;}.logo-sub{font-size:10px;color:rgba(255,255,255,0.6);font-weight:500;letter-spacing:1px;text-transform:uppercase;}
.nav{padding:12px;flex:1;overflow-y:auto;}
.nav-section{margin-bottom:18px;}
.nav-label{font-size:10px;font-weight:600;color:rgba(255,255,255,0.5);letter-spacing:1.2px;text-transform:uppercase;padding:0 8px;margin-bottom:5px;}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:rgba(255,255,255,0.8);transition:all .15s;margin-bottom:2px;position:relative;}
.nav-item:hover{background:rgba(255,255,255,0.15);color:#ffffff;}
.nav-item.active{background:rgba(255,255,255,0.2);color:#ffffff;font-weight:700;}
.nav-item.active::before{content:'';position:absolute;left:-12px;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#ffffff;border-radius:0 3px 3px 0;}
.nav-icon{font-size:14px;width:18px;text-align:center;}
.badge{margin-left:auto;background:var(--accent);color:white;font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;font-family:'JetBrains Mono',monospace;}
.badge.red{background:var(--red);}.badge.yellow{background:var(--yellow);color:#111;}
.sidebar-footer{padding:12px;border-top:1px solid rgba(255,255,255,0.15);}
.sb-create-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11px 14px;background:linear-gradient(135deg,#6366F1,#7C3AED);color:#fff;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;font-family:inherit;box-shadow:0 4px 14px rgba(99,102,241,0.35);transition:opacity .15s,transform .12s;}
.sb-create-btn:hover{opacity:.88;transform:translateY(-1px);}
.user-card{display:flex;align-items:center;gap:9px;padding:8px;border-radius:8px;cursor:pointer;transition:background .15s;}
.user-card:hover{background:rgba(255,255,255,0.15);}
.avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0;}
.user-name{font-size:13px;font-weight:600;color:#ffffff;}.user-role{font-size:11px;color:rgba(255,255,255,0.6);}
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;min-width:0;background:transparent;transition:margin-left .3s ease;}
.main.sb-collapsed{margin-left:0;}
.topbar{height:60px;background:rgba(255,255,255,0.92);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;padding:0 24px;position:sticky;top:0;z-index:50;width:100%;box-shadow:0 2px 12px rgba(8,145,178,0.08);}
.topbar h1{font-size:17px;font-weight:700;flex:1;}
.live-dot{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--text3);}
.live-pulse{width:7px;height:7px;border-radius:50%;background:var(--green);animation:lp 1.5s infinite;}
@keyframes lp{0%,100%{box-shadow:0 0 0 0 rgba(61,214,140,.5);}50%{box-shadow:0 0 0 5px rgba(61,214,140,0);}}
.search-box{display:flex;align-items:center;gap:8px;background:#f0f9fc;border:1px solid var(--border);border-radius:8px;padding:7px 12px;width:280px;min-width:200px;transition:border-color .2s;}
.search-box:focus-within{border-color:var(--accent);}
.search-box input{background:none;border:none;outline:none;color:var(--text);font-size:13px;font-family:inherit;width:100%;}
.search-box input::placeholder{color:var(--text3);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .15s;}
.btn-primary{background:var(--accent);color:white;}.btn-primary:hover{background:var(--accent2);transform:translateY(-1px);}
.btn-ghost{background:rgba(224,242,247,0.8);color:var(--text2);border:1px solid var(--border);}.btn-ghost:hover{background:var(--surface3);color:var(--text);}
.btn-success{background:var(--green-bg);color:var(--green);border:1px solid rgba(61,214,140,.4);}.btn-success:hover{background:var(--green);color:#111;}
.btn-danger{background:var(--red-bg);color:var(--red);border:1px solid rgba(245,101,101,.4);}.btn-danger:hover{background:var(--red);color:white;}
.view-toggle{display:flex;gap:4px;}
.view-btn{padding:6px 8px;border-radius:6px;background:none;border:1px solid var(--border);color:var(--text3);cursor:pointer;font-size:14px;transition:all .15s;}
.view-btn.active{background:var(--surface3);color:var(--text);border-color:var(--border2);}
.content{padding:24px;flex:1;overflow-x:auto;min-width:0;background:transparent;}
.stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px;}
.stat-card{background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;position:relative;overflow:hidden;transition:all .2s;box-shadow:0 2px 10px rgba(8,145,178,0.07);}
.stat-card:hover{border-color:var(--border2);transform:translateY(-2px);}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.stat-card.blue::before{background:linear-gradient(90deg,var(--accent),transparent);}
.stat-card.green::before{background:linear-gradient(90deg,var(--green),transparent);}
.stat-card.yellow::before{background:linear-gradient(90deg,var(--yellow),transparent);}
.stat-card.red::before{background:linear-gradient(90deg,var(--red),transparent);}
.stat-card.orange::before{background:linear-gradient(90deg,var(--orange),transparent);}
.stat-card.teal::before{background:linear-gradient(90deg,var(--teal),transparent);}
.stat-icon.teal{background:var(--teal-bg);}
.stat-icon{font-size:17px;width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;}
.stat-icon.blue{background:var(--accent-glow);}.stat-icon.green{background:var(--green-bg);}.stat-icon.yellow{background:var(--yellow-bg);}.stat-icon.red{background:var(--red-bg);}.stat-icon.orange{background:var(--orange-bg);}
.stat-value{font-size:24px;font-weight:700;font-family:'JetBrains Mono',monospace;margin-bottom:3px;}
.stat-label{font-size:11px;color:var(--text2);font-weight:500;}.stat-sub{font-size:11px;margin-top:5px;color:var(--text3);}
.stage-tooltip{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:#1e2130;border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:11.5px;color:var(--text);line-height:1.5;width:220px;z-index:999;pointer-events:none;opacity:0;transition:opacity .15s;box-shadow:0 8px 24px rgba(0,0,0,.5);}
.stage-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:#1e2130;}
.col-header:hover .stage-tooltip{opacity:1;}
.col-header{position:relative;}
.tl-desc{font-size:11px;color:var(--text3);line-height:1.5;margin-top:3px;max-width:180px;}
.upload-area{border:2px dashed var(--border2);border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:all .2s;background:var(--surface2);position:relative;}
.upload-area:hover,.upload-area.drag{border-color:var(--accent);background:var(--accent-glow);}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.upload-icon{font-size:28px;margin-bottom:6px;}
.upload-text{font-size:13px;font-weight:600;color:var(--text2);}
.upload-sub{font-size:11px;color:var(--text3);margin-top:3px;}
.file-list{display:flex;flex-direction:column;gap:6px;margin-top:10px;}
.file-item{display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:7px 10px;}
.file-icon{font-size:16px;flex-shrink:0;}
.file-info{flex:1;min-width:0;}
.file-name{font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.file-size{font-size:10px;color:var(--text3);margin-top:1px;}
.file-remove{background:none;border:none;color:var(--red);cursor:pointer;font-size:14px;padding:2px 4px;border-radius:4px;flex-shrink:0;}
.file-remove:hover{background:var(--red-bg);}
/* ── Notification Bell ── */
.notif-wrap{position:relative;}
.notif-btn{background:none;border:1px solid var(--border);border-radius:8px;padding:6px 10px;cursor:pointer;font-size:15px;position:relative;color:var(--text2);transition:all .15s;line-height:1;}
.notif-btn:hover{background:var(--surface2);}
.notif-badge{position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0 3px;font-family:'JetBrains Mono',monospace;pointer-events:none;}
.notif-dropdown{position:absolute;top:calc(100% + 8px);right:0;width:340px;max-height:420px;overflow-y:auto;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 8px 30px rgba(8,145,178,.18);z-index:300;display:none;}
.notif-dropdown.open{display:block;}
.notif-hdr{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-bottom:1px solid var(--border);position:sticky;top:0;background:#fff;z-index:1;}
.notif-mark-btn{background:none;border:none;font-size:11px;color:var(--accent);cursor:pointer;font-family:inherit;font-weight:600;}
.notif-mark-btn:hover{color:var(--accent2);}
.notif-item{padding:10px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;}
.notif-item:last-child{border-bottom:none;}
.notif-item:hover{background:var(--surface2);}
.notif-item.unread{background:rgba(8,145,178,.05);border-left:3px solid var(--accent);}
.notif-item-title{font-size:12.5px;font-weight:600;color:var(--text);}
.notif-item-msg{font-size:11.5px;color:var(--text2);margin-top:2px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.notif-item-time{font-size:10px;color:var(--text3);margin-top:3px;}
.notif-empty{padding:28px 14px;text-align:center;font-size:12px;color:var(--text3);}
/* ── Pagination ── */
.pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 4px;flex-wrap:wrap;gap:8px;}
.pagination-info{font-size:11px;color:var(--text3);}
.pagination-controls{display:flex;align-items:center;gap:3px;}
.pg-btn{min-width:30px;height:28px;padding:0 7px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text2);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .12s;}
.pg-btn:hover:not(:disabled){background:var(--surface2);border-color:var(--border2);color:var(--text);}
.pg-btn.active{background:var(--accent);color:#fff;border-color:var(--accent);}
.pg-btn:disabled{opacity:.35;cursor:default;}
.pg-ellipsis{font-size:12px;color:var(--text3);padding:0 3px;}
.pg-perpage{font-size:11px;color:var(--text2);display:flex;align-items:center;gap:5px;}
.pg-perpage select{font-size:11px;border:1px solid var(--border);border-radius:5px;padding:3px 5px;font-family:inherit;background:var(--surface);color:var(--text);cursor:pointer;}
.board-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.board-title{font-size:15px;font-weight:700;}
.filter-group{display:flex;gap:4px;background:rgba(255,255,255,0.9);border:1px solid var(--border);border-radius:8px;padding:3px;}
.filter-btn{padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;border:none;font-family:inherit;color:var(--text2);background:none;transition:all .15s;}
.filter-btn.active{background:var(--surface3);color:var(--text);}
.kanban{display:grid;grid-template-columns:repeat(8,minmax(205px,1fr));gap:13px;overflow-x:auto;padding-bottom:10px;}
.kanban-col{background:rgba(255,255,255,0.82);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;min-width:205px;box-shadow:0 2px 10px rgba(8,145,178,0.07);}
.col-header{padding:11px 13px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:7px;background:rgba(240,249,252,0.6);}
.col-dot{width:7px;height:7px;border-radius:50%;}
.col-title{font-size:12px;font-weight:700;flex:1;}
.col-count{font-size:11px;font-weight:700;font-family:'JetBrains Mono',monospace;color:var(--text3);background:var(--surface2);padding:2px 6px;border-radius:20px;}
.col-body{padding:9px;display:flex;flex-direction:column;gap:8px;min-height:170px;background:rgba(248,252,255,0.5);}
.ticket-card{background:#ffffff;border:1px solid var(--border);border-radius:var(--radius);padding:11px;cursor:pointer;transition:all .15s;position:relative;box-shadow:0 1px 4px rgba(8,145,178,0.06);}
.ticket-card:hover{border-color:var(--accent2);transform:translateY(-2px);box-shadow:0 6px 18px rgba(8,145,178,0.18);}
.ticket-card.pend{border-color:rgba(245,197,66,.35);opacity:.75;}
.ticket-id{font-size:10px;font-family:'JetBrains Mono',monospace;color:var(--text3);margin-bottom:4px;}
.ticket-title{font-size:12px;font-weight:600;line-height:1.4;margin-bottom:7px;}
.ticket-meta{display:flex;gap:4px;flex-wrap:wrap;}
.tag{font-size:10px;font-weight:600;padding:2px 6px;border-radius:20px;text-transform:uppercase;letter-spacing:.4px;}
.tag.critical{background:var(--red-bg);color:var(--red);}.tag.high{background:var(--orange-bg);color:var(--orange);}.tag.medium{background:var(--yellow-bg);color:var(--yellow);}.tag.low{background:var(--green-bg);color:var(--green);}
.tag.incident{background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);}.tag.newproject{background:var(--accent-glow);color:var(--accent2);border:1px solid rgba(79,142,247,.3);}.tag.openrequest{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25);}
.ticket-footer{display:flex;align-items:center;justify-content:space-between;margin-top:7px;padding-top:7px;border-top:1px solid var(--border);}
.ticket-assignee{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text3);}
.mini-avatar{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:white;flex-shrink:0;}
.sla-ok{color:var(--green);}.sla-warn{color:var(--yellow);}.sla-over{color:var(--red);}
.progress-bar{height:3px;background:var(--border);border-radius:10px;overflow:hidden;margin-top:7px;}
.progress-fill{height:100%;border-radius:10px;background:linear-gradient(90deg,var(--accent),#818cf8);transition:width .5s;}
.pend-badge{position:absolute;top:7px;right:7px;font-size:9px;font-weight:700;background:var(--yellow-bg);color:var(--yellow);padding:1px 5px;border-radius:20px;}
.table-wrap{background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07);}
table{width:100%;border-collapse:collapse;}
thead{background:var(--surface2);}
th{padding:10px 13px;text-align:left;font-size:10.5px;font-weight:700;color:var(--text3);letter-spacing:.7px;text-transform:uppercase;border-bottom:1px solid var(--border);}
td{padding:11px 13px;font-size:12px;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--surface2);}
.status-chip{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:20px;}
.status-chip .dot{width:5px;height:5px;border-radius:50%;}
.sc-pending{background:var(--yellow-bg);color:var(--yellow);}.sc-pending .dot{background:var(--yellow);}
.sc-active{background:var(--accent-glow);color:var(--accent);}.sc-active .dot{background:var(--accent);}
.sc-closed{background:var(--green-bg);color:var(--green);}.sc-closed .dot{background:var(--green);}
.sc-frozen{background:var(--purple-bg);color:var(--purple);}.sc-frozen .dot{background:var(--purple);}
.sc-req-freeze{background:rgba(234,88,12,0.1);color:var(--orange);}.sc-req-freeze .dot{background:var(--orange);}
.sla-freeze{color:var(--purple)!important;}
.freeze-chip{display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 7px;border-radius:12px;background:var(--purple-bg);color:var(--purple);margin-left:4px;}
.freeze-banner{background:linear-gradient(135deg,rgba(124,58,237,0.08),rgba(124,58,237,0.14));border:1px solid rgba(124,58,237,0.3);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;gap:10px;align-items:flex-start;}
.freeze-banner-icon{font-size:20px;flex-shrink:0;}
.freeze-banner-body{flex:1;min-width:0;}
.freeze-banner-title{font-size:13px;font-weight:700;color:var(--purple);margin-bottom:3px;}
.freeze-banner-meta{font-size:11px;color:var(--text2);line-height:1.5;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.78);backdrop-filter:blur(5px);z-index:1000;display:none;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:#ffffff;border:1px solid var(--border2);border-radius:var(--radius-lg);width:570px;max-height:90vh;overflow-y:auto;animation:mi .2s ease;}
.detail-modal{background:var(--surface);border:1px solid var(--border2);border-radius:var(--radius-lg);width:860px;max-height:93vh;overflow:hidden;display:flex;flex-direction:column;animation:mi .2s ease;}
@keyframes mi{from{opacity:0;transform:scale(.96) translateY(-10px);}to{opacity:1;transform:scale(1) translateY(0);}}
.modal-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.modal-title{font-size:15px;font-weight:700;}
.modal-close{width:26px;height:26px;border-radius:6px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;transition:all .15s;}
.modal-close:hover{background:var(--red-bg);color:var(--red);}
.modal-body{padding:20px;}
.form-group{margin-bottom:14px;}
.form-label{font-size:11px;font-weight:600;color:var(--text2);margin-bottom:5px;display:block;text-transform:uppercase;letter-spacing:.5px;}
.form-input,.form-select,.form-textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .2s;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--accent);}
.form-textarea{resize:vertical;min-height:75px;line-height:1.6;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.modal-footer{padding:13px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:7px;}
.detail-inner{display:flex;flex:1;overflow:hidden;}
.detail-main{flex:1;overflow-y:auto;padding:18px 20px;}
.detail-side{width:250px;border-left:1px solid var(--border);overflow-y:auto;padding:18px;}
.detail-header-block{padding:18px 20px;border-bottom:1px solid var(--border);flex-shrink:0;}
.detail-id{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3);margin-bottom:6px;}
.detail-title{font-size:18px;font-weight:700;margin-bottom:9px;line-height:1.3;}
.detail-tags{display:flex;gap:5px;flex-wrap:wrap;align-items:center;}
.ds{margin-bottom:20px;}
.dst{font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:9px;}
.desc-text{font-size:13px;line-height:1.7;color:var(--text2);}
.meta-item{margin-bottom:13px;}
.meta-label{font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;}
.meta-value{font-size:13px;font-weight:500;}
.sla-box{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:13px;margin-bottom:14px;}
.sla-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:10px;}
.sla-cell .lbl{font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:.6px;margin-bottom:3px;}
.sla-cell .val{font-size:13px;font-weight:600;}
.sla-bar{height:4px;background:var(--border);border-radius:10px;overflow:hidden;}
.sla-fill{height:100%;border-radius:10px;transition:width .5s;}
.sla-info{display:flex;justify-content:space-between;margin-top:3px;font-size:10px;}
.approval-box{background:var(--yellow-bg);border:1px solid rgba(245,197,66,.3);border-radius:10px;padding:13px;margin-bottom:14px;}
.approval-box-title{font-size:13px;font-weight:700;color:var(--yellow);margin-bottom:5px;}
.approval-box-text{font-size:12px;color:var(--text2);line-height:1.5;}
.chat-area{display:flex;flex-direction:column;gap:9px;max-height:280px;overflow-y:auto;padding:2px 0;scroll-behavior:smooth;}
.chat-msg{display:flex;gap:8px;animation:fu .2s ease;}
@keyframes fu{from{opacity:0;transform:translateY(5px);}to{opacity:1;transform:translateY(0);}}
.chat-bubble{flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:9px 11px;font-size:12px;line-height:1.6;}
.chat-bubble.own{background:var(--accent-glow);border-color:rgba(8,145,178,.25);}
.chat-user{font-size:10px;font-weight:700;color:var(--text2);margin-bottom:3px;}
.chat-time{font-size:10px;color:var(--text3);margin-top:3px;font-family:'JetBrains Mono',monospace;}
.chat-input-area{display:flex;gap:8px;margin-top:10px;}
.chat-input{flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px;font-family:inherit;outline:none;resize:none;min-height:38px;max-height:100px;transition:border-color .2s;}
.chat-input:focus{border-color:var(--accent);}
.task-due{font-size:10px;font-family:'JetBrains Mono',monospace;color:var(--text3);}
.task-due.overdue{color:var(--red);font-weight:700;}
.task-notes{font-size:11px;color:var(--text2);line-height:1.4;}
.aq-item{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;}
.aq-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;}
.aq-id{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3);}
.aq-title{font-size:13px;font-weight:700;margin-bottom:5px;}
.aq-meta{font-size:12px;color:var(--text2);line-height:1.6;margin-bottom:10px;}
.aq-actions{display:flex;gap:6px;}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:#1e293b;color:#e2e8f0;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transition:all .25s;pointer-events:none;white-space:nowrap;}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.toast.warn{background:#92400e;}.toast.err{background:#7f1d1d;}
.bg-layer{position:fixed;inset:0;z-index:-1;background:linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%);}
.user-dashboard{max-width:860px;margin:0 auto;}
.user-dash-header{margin-bottom:20px;}
.user-dash-greeting{font-size:20px;font-weight:700;margin-bottom:4px;}
.user-dash-sub{font-size:13px;color:var(--text2);}
.user-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;}
.ud-banner{display:flex;align-items:center;gap:12px;background:var(--green-bg);border:1px solid rgba(5,150,105,.25);border-radius:12px;padding:13px 16px;cursor:pointer;transition:all .15s;margin-bottom:10px;}
.ud-banner:hover{background:rgba(5,150,105,.18);transform:translateY(-1px);}
.ud-banner-icon{font-size:22px;flex-shrink:0;}
.ud-banner-text{flex:1;}
.ud-banner-title{font-size:13px;font-weight:700;color:var(--green);margin-bottom:2px;}
.ud-banner-sub{font-size:12px;color:var(--text2);}
.ud-banner-arrow{font-size:16px;color:var(--green);flex-shrink:0;}
.ud-tabs{display:flex;gap:4px;background:rgba(255,255,255,0.9);border:1px solid var(--border);border-radius:8px;padding:3px;margin-bottom:12px;width:fit-content;}
.ud-tab{padding:5px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;border:none;font-family:inherit;color:var(--text2);background:none;transition:all .15s;}
.ud-tab.active{background:var(--surface3);color:var(--text);}
.ud-tab-count{font-size:10px;font-family:'JetBrains Mono',monospace;margin-left:4px;opacity:.7;}
.user-ticket-section{background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07);}
.user-ticket-header{padding:13px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface2);}
.user-ticket-title{font-size:13px;font-weight:700;}
.user-ticket-list{display:flex;flex-direction:column;}
.user-ticket-row{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;}
.user-ticket-row:last-child{border-bottom:none;}
.user-ticket-row:hover{background:var(--surface2);}
.utf-id{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3);flex-shrink:0;width:68px;}
.utf-body{flex:1;min-width:0;}
.utf-title{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:5px;}
.utf-status{flex-shrink:0;}
.utf-date{font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace;flex-shrink:0;width:80px;text-align:right;}
.utf-sla{flex-shrink:0;text-align:right;width:96px;font-size:10.5px;font-family:'JetBrains Mono',monospace;font-weight:600;}
.stage-minibar{display:flex;gap:2px;align-items:center;}
.stage-pip{height:4px;flex:1;border-radius:2px;background:var(--border);}
.stage-pip.done{background:var(--green);}
.stage-pip.active{background:var(--accent);}
.stage-pip.overdue{background:var(--red);}
.ud-type-filters{display:flex;gap:4px;flex-wrap:wrap;}
.ud-type-btn{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;border:1px solid var(--border);font-family:inherit;color:var(--text2);background:var(--surface2);transition:all .15s;}
.ud-type-btn:hover{border-color:var(--accent);color:var(--accent);}
.ud-type-btn.active{background:var(--accent);color:#fff;border-color:var(--accent);}
.user-empty{text-align:center;padding:48px 24px;color:var(--text3);}
.user-empty-illus{font-size:52px;margin-bottom:14px;line-height:1;}
.user-empty-title{font-size:15px;font-weight:700;color:var(--text2);margin-bottom:6px;}
.user-empty-sub{font-size:13px;margin-bottom:18px;line-height:1.6;}
.task-form{display:grid;grid-template-columns:1fr 160px auto;gap:8px;align-items:end;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:12px;}
.task-table{width:100%;border-collapse:collapse;font-size:12px;}
.task-table thead th{padding:7px 10px;font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.6px;background:var(--surface2);border-bottom:1px solid var(--border);}
.task-table tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
.task-table tbody tr:last-child{border-bottom:none;}
.task-table tbody tr:hover td{background:var(--surface2);}
.task-table td{padding:9px 10px;vertical-align:middle;}
.task-status-done{background:var(--green-bg);color:var(--green);border:1px solid rgba(5,150,105,.25);font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;}
.task-status-todo{background:var(--surface2);color:var(--text3);border:1px solid var(--border);font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;}
.task-empty{text-align:center;padding:20px;color:var(--text3);font-size:12px;}
.activity-item{display:flex;align-items:flex-start;gap:10px;padding:9px 16px;border-bottom:1px solid var(--border);transition:background .12s;}
.activity-item:last-child{border-bottom:none;}
.activity-item:hover{background:var(--surface2);}
.activity-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:1px;}
.activity-body{flex:1;min-width:0;}
.activity-title{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.activity-meta{font-size:10.5px;color:var(--text3);margin-top:2px;font-family:'JetBrains Mono',monospace;}
.sla-row-item{display:flex;flex-direction:column;gap:4px;}
.sla-row-top{display:flex;align-items:center;justify-content:space-between;}
.sla-row-title{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;}
.sla-row-label{font-size:10.5px;font-weight:700;font-family:'JetBrains Mono',monospace;}
.sla-mini-bar{height:4px;background:var(--border);border-radius:10px;overflow:hidden;}
.sla-mini-fill{height:100%;border-radius:10px;transition:width .5s;}

/* ══════════════════════════════════════════════════════════════
   USER DASHBOARD — SCOPED UI/UX ENHANCEMENTS
   Semua selector di bawah hanya berlaku di dalam
   .user-stats-grid / .user-ticket-row / #ud-sla-list
   Tidak ada dampak ke halaman atau widget lain.
══════════════════════════════════════════════════════════════ */

/* 1. SUMMARY CARDS — transition base (warna & hover di-handle blok Vibrant Redesign) */
.user-stats-grid .stat-card{transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;}
/* icon & label sejajar dalam satu baris */
.ud-card-top{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.ud-card-top .stat-icon{margin-bottom:0;flex-shrink:0;}
.ud-card-top .stat-label{font-size:12px;color:var(--text2);font-weight:600;}
/* trend badge opsional */
.ud-stat-trend{display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;color:var(--green);margin-top:6px;background:var(--green-bg);padding:2px 8px;border-radius:20px;width:fit-content;}
.ud-stat-trend.neutral{color:var(--text3);background:var(--surface3);}

/* 2. STATUS BADGE — pill penuh + ikon */
.user-ticket-row .status-chip{padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;gap:4px;}
.user-ticket-row .status-chip .dot{display:none;}
.user-ticket-row .sc-closed::before   {content:'✓ ';}
.user-ticket-row .sc-active::before   {content:'⏳ ';}
.user-ticket-row .sc-pending::before  {content:'⌛ ';}
.user-ticket-row .sc-frozen::before   {content:'🧊 ';}
.user-ticket-row .sc-req-freeze::before{content:'⏸ ';}

/* 3. DAFTAR TIKET — card ringan, hover naik */
.user-ticket-list{padding:6px;gap:0;display:flex;flex-direction:column;}
.user-ticket-row{
  border-radius:10px!important;border:1px solid var(--border)!important;
  margin:3px 0!important;padding:12px 14px!important;
  transition:transform .18s ease,box-shadow .18s ease,background .15s,border-color .15s!important;
}
.user-ticket-row:last-child{border-bottom:1px solid var(--border)!important;margin-bottom:3px!important;}
.user-ticket-row:hover{
  background:rgba(240,249,252,.9)!important;
  box-shadow:0 6px 20px rgba(8,145,178,.13);
  transform:translateY(-2px);
  border-color:var(--border2)!important;
}
/* judul tebal, underline saat hover */
.utf-title{font-weight:700!important;font-size:13.5px;cursor:pointer;transition:color .15s;}
.user-ticket-row:hover .utf-title{color:var(--accent);text-decoration:underline;text-underline-offset:2px;}
/* ID badge kecil */
.utf-id{font-size:10px!important;color:var(--text3)!important;background:var(--surface3);padding:2px 7px!important;border-radius:6px;width:auto!important;flex-shrink:0;}
/* baris info tambahan di bawah judul */
.utf-info-row{display:flex;align-items:center;gap:6px;margin-top:3px;}
.utf-type-badge{font-size:10px;font-weight:600;padding:1px 7px;border-radius:10px;color:var(--text2);background:var(--surface3);}

/* 4. PROGRESS BAR — lebih tebal + persentase */
.ud-progress-wrap{display:flex;align-items:center;gap:8px;margin-top:6px;}
.ud-progress-bar{flex:1;height:6px;background:var(--border);border-radius:4px;overflow:hidden;}
.ud-progress-fill{height:100%;border-radius:4px;transition:width .5s ease;}
.ud-progress-pct{font-size:10px;font-weight:700;font-family:'JetBrains Mono',monospace;color:var(--text3);flex-shrink:0;min-width:28px;text-align:right;}

/* 5. FILTER TAB — tab aktif lebih tegas */
.ud-tab{transition:all .2s ease;color:var(--text3);border-radius:6px;}
.ud-tab.active{background:var(--accent)!important;color:#fff!important;border-radius:6px;box-shadow:0 2px 8px rgba(8,145,178,.25);}
.ud-tab.active .ud-tab-count{opacity:1;background:rgba(255,255,255,.22);padding:0 5px;border-radius:10px;}
.ud-tab:hover:not(.active){background:var(--surface3);color:var(--text);}
.ud-type-btn{transition:all .18s ease;}
.ud-type-btn:hover:not(.active){border-color:var(--accent);color:var(--accent);background:var(--accent-glow);}

/* 6. SLA PANEL — scoped ke #ud-sla-list saja */
#ud-sla-list .sla-row-item{
  background:var(--surface2);border:1px solid var(--border);border-radius:10px;
  padding:10px 12px;cursor:pointer;
  transition:transform .18s ease,box-shadow .18s ease,border-color .15s;
}
#ud-sla-list .sla-row-item:hover{border-color:var(--border2);box-shadow:0 4px 12px rgba(8,145,178,.1);transform:translateY(-1px);}
#ud-sla-list .sla-mini-bar{height:6px!important;border-radius:4px;margin-top:6px;}
.ud-sla-meta{display:flex;align-items:center;justify-content:space-between;margin-top:4px;font-size:10px;font-family:'JetBrains Mono',monospace;}
.ud-sla-pct{color:var(--text3);}
.ud-sla-warn{display:inline-flex;align-items:center;gap:3px;font-weight:700;}
.ud-sla-warn.ok  {color:var(--green);}
.ud-sla-warn.warn{color:var(--yellow);}
.ud-sla-warn.crit{color:var(--red);}

/* 7. MICRO INTERACTIONS */
.user-dashboard .btn{transition:all .2s ease;}
.user-dashboard .btn-primary:hover{filter:brightness(.9);transform:translateY(-1px);}
.user-dashboard .ud-banner{transition:transform .2s ease,box-shadow .2s ease;}
.user-dashboard .ud-banner:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(8,145,178,.12);}

/* ══ COMPACT LAYOUT OPTIMIZATION ══════════════════════════════
   Semua scoped di bawah .user-dashboard atau selector spesifik.
   Tidak ada dampak ke halaman lain.
════════════════════════════════════════════════════════════════ */

/* Header: kurangi spacing */
.user-dashboard .user-dash-header{margin-bottom:12px;}
.user-dashboard .user-dash-greeting{font-size:17px;}
.user-dashboard .user-dash-sub{font-size:12px;margin-top:2px;}

/* Stats grid: padding & gap lebih kecil */
.user-stats-grid{gap:10px;}
.user-stats-grid .stat-card{padding:12px 14px;}
.user-stats-grid .stat-value{font-size:22px;margin-bottom:2px;}
.ud-card-top{margin-bottom:8px;}
.user-stats-grid .stat-sub{margin-top:3px;font-size:10.5px;}

/* Filter row: tab + type filter dalam SATU baris horizontal */
.ud-filter-row{
  display:flex;align-items:center;justify-content:space-between;
  gap:8px;flex-wrap:wrap;
  padding:8px 14px;
  border-bottom:1px solid var(--border);
  background:rgba(248,252,255,.6);
}
.ud-filter-row .ud-tabs{margin:0;flex-shrink:0;}
.ud-filter-row .ud-type-filters{flex-wrap:nowrap;gap:3px;}
.ud-filter-row .ud-type-btn{padding:2px 8px;font-size:10.5px;}

/* Ticket section header: compact */
.user-dashboard .user-ticket-header{padding:10px 14px;}

/* Ticket list: card lebih compact */
.user-ticket-list{padding:4px 6px;}
.user-ticket-row{padding:9px 12px!important;margin:2px 0!important;}
.utf-title{font-size:13px!important;}

/* Meta compact: type badge + progress bar + % dalam SATU baris */
.utf-compact-meta{display:flex;align-items:center;gap:6px;margin-top:4px;}
.utf-compact-meta .utf-type-badge{flex-shrink:0;}
.utf-compact-meta .ud-progress-bar{flex:1;height:4px;}
.utf-compact-meta .ud-progress-fill{height:4px;}
.utf-compact-meta .ud-progress-pct{font-size:9.5px;min-width:24px;}
.utf-task-count{font-size:9.5px;color:var(--text3);flex-shrink:0;font-family:'JetBrains Mono',monospace;}

/* Status badge: label pendek, tidak melebar */
.user-ticket-row .utf-status{flex-shrink:0;max-width:130px;}
.user-ticket-row .status-chip{white-space:nowrap;}

/* SLA panel: compact */
#ud-sla-list{padding:6px 10px!important;gap:6px!important;}
#ud-sla-list .sla-row-item{padding:8px 10px;}

/* Banner: compact — scoped ke .user-dashboard */
.user-dashboard #ud-banners{margin-bottom:8px;}
.user-dashboard .ud-banner{padding:10px 14px!important;}

/* ══ STAT CARD: GRADIENT FILL (Sesi 2) ══════════════════════
   Mengganti 2px top-strip dengan tinted gradient background.
   Scoped ke .user-stats-grid (IT-dashboard ditangani di blok Vibrant Redesign).
════════════════════════════════════════════════════════════ */
.stat-card::before{display:none;}/* sembunyikan strip 2px lama */

.user-stats-grid .stat-card.blue {
  background:linear-gradient(145deg,rgba(8,145,178,0.12) 0%,rgba(255,255,255,0.97) 55%);
  border-color:rgba(8,145,178,0.22);
}
.user-stats-grid .stat-card.green {
  background:linear-gradient(145deg,rgba(5,150,105,0.12) 0%,rgba(255,255,255,0.97) 55%);
  border-color:rgba(5,150,105,0.22);
}
.user-stats-grid .stat-card.orange {
  background:linear-gradient(145deg,rgba(234,88,12,0.12) 0%,rgba(255,255,255,0.97) 55%);
  border-color:rgba(234,88,12,0.22);
}
.user-stats-grid .stat-card.teal {
  background:linear-gradient(145deg,rgba(13,148,136,0.12) 0%,rgba(255,255,255,0.97) 55%);
  border-color:rgba(13,148,136,0.22);
}

/* Stat value warna: di-handle di blok Vibrant Redesign (putih di atas gradient) */

/* Trend badge: arrow ↑↓ */
.ud-stat-trend::before       { content:'↑ '; font-size:11px; }
.ud-stat-trend.neutral::before{ content:'— '; }

/* ══ CHART CONTAINERS ════════════════════════════════════════ */
.charts-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;}
.chart-card{background:rgba(255,255,255,0.93);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07);}
.chart-card-hdr{padding:11px 16px;border-bottom:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between;}
.chart-card-title{font-size:13px;font-weight:700;color:var(--text);}
.chart-canvas-wrap{padding:14px 16px;height:210px;position:relative;}
.chart-canvas-wrap canvas{max-height:100%;}

/* ══ WIDE 2-COLUMN LAYOUT ════════════════════════════════════
   Ekspansi container + layout kiri(tiket) / kanan(SLA+insight)
   Semua scoped dalam .user-dashboard — zero global impact.
════════════════════════════════════════════════════════════ */
.user-dashboard{max-width:1400px!important;}

/* Grid utama: 2 kolom */
.ud-main-grid{
  display:grid;
  grid-template-columns:1fr 290px;
  gap:14px;
  align-items:start;
}
.ud-left-col{min-width:0;}

/* Kolom kanan: sticky saat scroll */
.ud-right-col{
  display:flex;flex-direction:column;gap:12px;
  position:sticky;top:72px;
}

/* Quick-info card di kolom kanan */
.ud-quick-card{
  background:rgba(255,255,255,.92);
  backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  overflow:hidden;
  box-shadow:0 2px 10px rgba(8,145,178,.07);
}
.ud-quick-header{
  padding:10px 14px;border-bottom:1px solid var(--border);
  background:var(--surface2);
  font-size:12px;font-weight:700;color:var(--text2);
  text-transform:uppercase;letter-spacing:.6px;
}
.ud-quick-body{padding:12px 14px;display:flex;flex-direction:column;gap:10px;}
.ud-qs-row{display:flex;align-items:center;justify-content:space-between;}
.ud-qs-label{font-size:12px;color:var(--text2);}
.ud-qs-num{font-size:15px;font-weight:700;font-family:'JetBrains Mono',monospace;}

/* SLA panel di kolom kanan: scroll-able jika banyak item */
.ud-right-col #ud-stage-panel{flex:1;}
.ud-right-col #ud-sla-list{max-height:calc(100vh - 340px);overflow-y:auto;}

/* SLA empty state */
.ud-sla-empty{
  text-align:center;padding:24px 12px;color:var(--text3);
}
.ud-sla-empty-icon{font-size:28px;margin-bottom:8px;}
.ud-sla-empty-title{font-size:12px;font-weight:600;color:var(--text2);}
.ud-sla-empty-sub{font-size:11px;margin-top:3px;line-height:1.5;}

/* Responsive: di layar kecil kembali ke 1 kolom */
@media(max-width:900px){
  .ud-main-grid{grid-template-columns:1fr;}
  .ud-right-col{position:static;}
  .user-dashboard{max-width:100%!important;}
}

/* ══ SESI 3: TABULAR + POLISH ════════════════════════════════

   1. TABLE VIEW — improved header, row hover accent, ID badge
════════════════════════════════════════════════════════════ */
.table-wrap{box-shadow:0 2px 16px rgba(8,145,178,0.09);}
.table-wrap table{border-collapse:separate;border-spacing:0;}
.table-wrap thead{background:linear-gradient(135deg,#f0f9fc,#e8f4f8);}
.table-wrap th{border-bottom:2px solid var(--border2)!important;white-space:nowrap;}
/* Row hover: subtle indigo tint + left accent */
.table-wrap tbody tr{transition:background .12s;}
.table-wrap tbody tr:hover td{background:rgba(99,102,241,0.04);}
.table-wrap tbody tr:hover td:first-child{box-shadow:inset 3px 0 0 #6366F1;}
/* Table cursor pointer */
.table-wrap tbody tr{cursor:pointer;}
/* ID badge */
.tbl-id{display:inline-block;font-family:'JetBrains Mono',monospace;font-size:10.5px;font-weight:700;background:var(--surface3);color:var(--text3);padding:2px 7px;border-radius:6px;white-space:nowrap;}
/* Title+type cell */
.tbl-title-cell{max-width:240px;}
.tbl-title-cell .tbl-name{font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;}
/* Action button in table */
.tbl-detail-btn{padding:5px 12px;font-size:11px;font-weight:700;border-radius:7px;border:none;cursor:pointer;background:linear-gradient(135deg,#6366F1,#818CF8);color:#fff;font-family:inherit;transition:opacity .15s,transform .12s;white-space:nowrap;}
.tbl-detail-btn:hover{opacity:.88;transform:translateY(-1px);}

/* ── 2. STATUS CHIPS: vibrant gradient fill (tabel) ── */
#list-view .status-chip{padding:4px 11px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.2px;}
#list-view .status-chip .dot{display:none;}
#list-view .sc-active    {background:linear-gradient(135deg,#6366F1,#818CF8);color:#fff;}
#list-view .sc-pending   {background:linear-gradient(135deg,#F59E0B,#FBBF24);color:#fff;}
#list-view .sc-closed    {background:linear-gradient(135deg,#10B981,#34D399);color:#fff;}
#list-view .sc-frozen    {background:linear-gradient(135deg,#7C3AED,#A78BFA);color:#fff;}
#list-view .sc-req-freeze{background:linear-gradient(135deg,#F97316,#FB923C);color:#fff;}
/* Detail modal chips juga lebih vibrant */
.detail-tags .sc-active    {background:linear-gradient(135deg,#6366F1,#818CF8);color:#fff;border:none;}
.detail-tags .sc-pending   {background:linear-gradient(135deg,#F59E0B,#FBBF24);color:#fff;border:none;}
.detail-tags .sc-closed    {background:linear-gradient(135deg,#10B981,#34D399);color:#fff;border:none;}
.detail-tags .sc-frozen    {background:linear-gradient(135deg,#7C3AED,#A78BFA);color:#fff;border:none;}
.detail-tags .sc-req-freeze{background:linear-gradient(135deg,#F97316,#FB923C);color:#fff;border:none;}
.detail-tags .status-chip .dot{display:none;}

/* ── 3. SLA PANEL REDESIGN: colored left-border ── */
/* IT dashboard */
#sla-list .sla-row-item{border-left:4px solid var(--border2);border-radius:10px;transition:transform .18s ease,box-shadow .18s ease;}
#sla-list .sla-row-item:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(8,145,178,.12);}
#sla-list .sla-row-item.sla-ok  {border-left-color:#10B981;}
#sla-list .sla-row-item.sla-warn{border-left-color:#F59E0B;}
#sla-list .sla-row-item.sla-over{border-left-color:#EF4444;}
/* User dashboard */
#ud-sla-list .sla-row-item{border-left:4px solid var(--border2);}
#ud-sla-list .sla-row-item.sla-ok  {border-left-color:#10B981;}
#ud-sla-list .sla-row-item.sla-warn{border-left-color:#F59E0B;}
#ud-sla-list .sla-row-item.sla-over{border-left-color:#EF4444;}

/* ── 4. RESPONSIVE ── */
@media(max-width:1024px){
  .ud-main-grid{grid-template-columns:1fr 250px;}
}
@media(max-width:768px){
  .content{padding:14px 12px;}
  .topbar{flex-wrap:wrap;height:auto;padding:8px 14px;gap:8px;}
  .topbar h1{font-size:15px;flex:none;width:100%;}
  .search-box{width:100%;min-width:unset;}
  .stats-grid,.user-stats-grid{grid-template-columns:repeat(2,1fr);gap:10px;}
  .charts-row{grid-template-columns:1fr;}
  .ud-main-grid{grid-template-columns:1fr;}
  .ud-right-col{position:static;}
  .table-wrap{overflow-x:auto;border-radius:var(--radius);}
  .kanban{overflow-x:auto;}
}
@media(max-width:480px){
  .content{padding:10px 8px;}
  .stats-grid,.user-stats-grid{grid-template-columns:repeat(2,1fr);gap:8px;}
  .stat-card{padding:11px 12px;}
  .stat-value{font-size:20px!important;}
  .topbar .live-dot,.topbar .view-toggle{display:none;}
  .notif-dropdown{width:290px;right:-10px;}
}

/* ══ IT / MANAGER BOARD — SCOPED ENHANCEMENTS ══════════════════
   Scoped ke #it-dashboard dan #board-view .ticket-card.
   Zero impact ke user-dashboard atau halaman lain.
══════════════════════════════════════════════════════════════ */

/* Stat cards IT: transition base (warna & hover di-handle blok Vibrant Redesign) */
#it-dashboard .stat-card{transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;}

/* Ticket card: hover lebih kuat */
#board-view .ticket-card{transition:transform .18s ease,box-shadow .18s ease,border-color .15s!important;}
#board-view .ticket-card:hover{transform:translateY(-3px)!important;box-shadow:0 10px 28px rgba(8,145,178,.22)!important;border-color:var(--border2)!important;}

/* Header row: ID + tag + comment badge sejajar */
.tc-header-row{display:flex;align-items:center;gap:4px;margin-bottom:5px;flex-wrap:wrap;}
.tc-header-row .ticket-id{margin-bottom:0;flex-shrink:0;}

/* Progress bar lebih tebal + persentase */
.tc-progress-row{display:flex;align-items:center;gap:6px;margin-top:6px;}
.tc-progress-track{flex:1;height:5px;background:var(--border);border-radius:3px;overflow:hidden;}
.tc-progress-bar{height:100%;border-radius:3px;transition:width .5s ease;}
.tc-pct-label{font-size:9.5px;font-weight:700;font-family:'JetBrains Mono',monospace;color:var(--text3);flex-shrink:0;min-width:26px;text-align:right;}

/* Baris task info + status */
.tc-info-row{display:flex;align-items:center;justify-content:space-between;margin-top:3px;margin-bottom:5px;}
.tc-task-info{font-size:10px;color:var(--text3);font-weight:500;}
.tc-state-lbl{font-size:10px;font-weight:700;}

/* SLA chip dengan ikon */
.tc-sla-chip{display:inline-flex;align-items:center;gap:2px;font-size:10px;font-weight:700;font-family:'JetBrains Mono',monospace;}

/* Comment badge */
.tc-comment-badge{display:inline-flex;align-items:center;gap:2px;font-size:9.5px;font-weight:700;background:var(--accent-glow);color:var(--accent);border:1px solid rgba(8,145,178,.25);border-radius:10px;padding:1px 5px;flex-shrink:0;}

/* Creator/requester baris bawah card */
.tc-creator{font-size:10px;color:var(--text3);margin-top:5px;padding-top:5px;border-top:1px solid var(--border);display:flex;align-items:center;gap:4px;}

/* ══════════════════════════════════════════════════════════════
   IT/MANAGER: VIBRANT REDESIGN
   Scoped ke #it-dashboard, #it-charts-row, #board-view.
   Zero impact ke user-dashboard atau halaman lain.
══════════════════════════════════════════════════════════════ */

/* Tambah variable pink */
:root{--pink:#ec4899;--pink-bg:rgba(236,72,153,0.1);}

/* ── 1. STAT CARDS: full vibrant gradient + strong shadow ── */
#it-dashboard .stat-card.blue{
  background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%)!important;
  border:1px solid #0369a1!important;
  border-left:none!important;
  box-shadow:0 8px 28px rgba(2,132,199,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
#it-dashboard .stat-card.yellow{
  background:linear-gradient(135deg,#b45309 0%,#fbbf24 100%)!important;
  border:1px solid #92400e!important;
  border-left:none!important;
  box-shadow:0 8px 28px rgba(217,119,6,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
#it-dashboard .stat-card.orange{
  background:linear-gradient(135deg,#c2410c 0%,#fb923c 100%)!important;
  border:1px solid #9a3412!important;
  border-left:none!important;
  box-shadow:0 8px 28px rgba(234,88,12,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
#it-dashboard .stat-card.green{
  background:linear-gradient(135deg,#047857 0%,#6ee7b7 100%)!important;
  border:1px solid #065f46!important;
  border-left:none!important;
  box-shadow:0 8px 28px rgba(5,150,105,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}

/* Teks putih di atas card vibrant */
#it-dashboard .stat-card .stat-label{color:rgba(255,255,255,0.85)!important;}
#it-dashboard .stat-card .stat-sub{color:rgba(255,255,255,0.7)!important;}
#it-dashboard .stat-card.blue .stat-value,
#it-dashboard .stat-card.yellow .stat-value,
#it-dashboard .stat-card.orange .stat-value,
#it-dashboard .stat-card.green .stat-value{color:#ffffff!important;}

/* Neumorphism-style icon: frosted glass di atas warna vibrant */
#it-dashboard .stat-card .stat-icon{
  background:rgba(255,255,255,0.22)!important;
  box-shadow:inset 0 1px 3px rgba(0,0,0,.14),0 2px 8px rgba(0,0,0,.10);
  width:40px;height:40px;font-size:19px;
  border:1px solid rgba(255,255,255,0.3);
}

/* Hover: lift + brightness */
#it-dashboard .stat-card:hover{
  transform:translateY(-6px) scale(1.02)!important;
  filter:brightness(1.07);
}

/* ── 2. CHART CARDS IT: gradient header warna ── */
#it-charts-row .chart-card{
  box-shadow:0 6px 24px rgba(8,145,178,0.14)!important;
}
#it-charts-row .chart-card:first-child .chart-card-hdr{
  background:linear-gradient(135deg,#6366f1 0%,#a78bfa 100%);
  border-bottom:none;
}
#it-charts-row .chart-card:last-child .chart-card-hdr{
  background:linear-gradient(135deg,#ec4899 0%,#fb923c 100%);
  border-bottom:none;
}
#it-charts-row .chart-card-hdr .chart-card-title{color:#ffffff!important;}

/* ── 3. PANEL AKTIVITAS + SLA IT: glassmorphism lebih dalam ── */
#it-dashboard div:has(>#activity-list),
#it-dashboard div:has(>#sla-list){
  background:rgba(255,255,255,0.75)!important;
  backdrop-filter:blur(18px)!important;
  -webkit-backdrop-filter:blur(18px)!important;
  box-shadow:0 6px 28px rgba(8,145,178,0.15)!important;
  border:1px solid rgba(8,145,178,0.2)!important;
}
#it-dashboard div:has(>#activity-list)>div:first-child{
  background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(240,249,252,0.9))!important;
  border-bottom:1px solid rgba(99,102,241,0.15)!important;
}
#it-dashboard div:has(>#sla-list)>div:first-child{
  background:linear-gradient(135deg,rgba(236,72,153,0.12),rgba(240,249,252,0.9))!important;
  border-bottom:1px solid rgba(236,72,153,0.15)!important;
}

/* ── 4. KANBAN COLUMNS: color-coded header + shadow ── */
#board-view .kanban-col{
  box-shadow:0 4px 18px rgba(8,145,178,0.12)!important;
  border:1px solid rgba(8,145,178,0.14)!important;
}
#board-view .kanban-col:nth-child(1) .col-header{
  background:linear-gradient(135deg,rgba(148,163,184,0.25),rgba(240,249,252,0.7));
  border-bottom:2px solid #94a3b8;
}
#board-view .kanban-col:nth-child(2) .col-header{
  background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(240,249,252,0.7));
  border-bottom:2px solid #6366f1;
}
#board-view .kanban-col:nth-child(3) .col-header{
  background:linear-gradient(135deg,rgba(52,211,153,0.2),rgba(240,249,252,0.7));
  border-bottom:2px solid #34d399;
}

/* ── 5. TOPBAR: rainbow accent bottom line ── */
.topbar{
  border-bottom:2px solid transparent!important;
  border-image:linear-gradient(90deg,#6366f1,#ec4899,#f59e0b,#0ea5e9) 1!important;
  box-shadow:0 2px 18px rgba(8,145,178,0.13)!important;
}

/* ══════════════════════════════════════════════════════════════
   USER PORTAL: VIBRANT REDESIGN
   Scoped ke .user-dashboard, .user-stats-grid, #user-charts-row,
   #ud-stage-panel. Zero impact ke IT/Manager atau halaman lain.
══════════════════════════════════════════════════════════════ */

/* ── 1. GREETING HEADER: glassmorphism + gradient text ── */
.user-dashboard .user-dash-header{
  background:linear-gradient(135deg,rgba(99,102,241,0.11) 0%,rgba(236,72,153,0.07) 55%,rgba(14,165,233,0.05) 100%);
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border:1px solid rgba(99,102,241,0.18);
  border-radius:var(--radius-lg);
  padding:18px 24px;
  margin-bottom:14px;
  box-shadow:0 4px 22px rgba(99,102,241,0.12);
  position:relative;overflow:hidden;
}
/* dekorasi orb kanan atas */
.user-dashboard .user-dash-header::before{
  content:'';position:absolute;top:-24px;right:-24px;
  width:110px;height:110px;
  background:radial-gradient(circle,rgba(236,72,153,0.22),transparent 70%);
  border-radius:50%;pointer-events:none;
}
/* dekorasi orb kiri bawah */
.user-dashboard .user-dash-header::after{
  content:'';position:absolute;bottom:-18px;left:30px;
  width:80px;height:80px;
  background:radial-gradient(circle,rgba(99,102,241,0.18),transparent 70%);
  border-radius:50%;pointer-events:none;
}
.user-dashboard .user-dash-greeting{
  font-size:20px;font-weight:800;
  background:linear-gradient(135deg,#6366f1,#ec4899);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;
  position:relative;z-index:1;
}
.user-dashboard .user-dash-sub{
  font-size:12px;color:var(--text2);margin-top:3px;
  position:relative;z-index:1;
}

/* ── 2. STAT CARDS user: full vibrant gradient ── */
.user-stats-grid .stat-card.blue{
  background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%)!important;
  border:1px solid #0369a1!important;border-left:none!important;
  box-shadow:0 8px 28px rgba(2,132,199,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
.user-stats-grid .stat-card.orange{
  background:linear-gradient(135deg,#c2410c 0%,#fb923c 100%)!important;
  border:1px solid #9a3412!important;border-left:none!important;
  box-shadow:0 8px 28px rgba(234,88,12,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
.user-stats-grid .stat-card.green{
  background:linear-gradient(135deg,#047857 0%,#6ee7b7 100%)!important;
  border:1px solid #065f46!important;border-left:none!important;
  box-shadow:0 8px 28px rgba(5,150,105,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}
/* yellow class → purple gradient (tambah purple ke user portal) */
.user-stats-grid .stat-card.yellow{
  background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 100%)!important;
  border:1px solid #6d28d9!important;border-left:none!important;
  box-shadow:0 8px 28px rgba(124,58,237,0.38),0 2px 6px rgba(0,0,0,.06)!important;
}

/* Teks putih di atas card vibrant */
.user-stats-grid .stat-card.blue .stat-label,
.user-stats-grid .stat-card.orange .stat-label,
.user-stats-grid .stat-card.green .stat-label,
.user-stats-grid .stat-card.yellow .stat-label{color:rgba(255,255,255,0.85)!important;}
.user-stats-grid .stat-card.blue .stat-sub,
.user-stats-grid .stat-card.orange .stat-sub,
.user-stats-grid .stat-card.green .stat-sub,
.user-stats-grid .stat-card.yellow .stat-sub{color:rgba(255,255,255,0.7)!important;}
.user-stats-grid .stat-card.blue .stat-value,
.user-stats-grid .stat-card.orange .stat-value,
.user-stats-grid .stat-card.green .stat-value,
.user-stats-grid .stat-card.yellow .stat-value{color:#ffffff!important;}

/* Neumorphism-style icon: frosted glass di atas warna vibrant */
.user-stats-grid .stat-card .stat-icon{
  background:rgba(255,255,255,0.22)!important;
  box-shadow:inset 0 1px 3px rgba(0,0,0,.14),0 2px 8px rgba(0,0,0,.10);
  width:40px;height:40px;font-size:19px;
  border:1px solid rgba(255,255,255,0.3);
}

/* Trend badge: tetap terbaca di atas warna vibrant */
.user-stats-grid .stat-card .ud-stat-trend{
  background:rgba(255,255,255,0.2)!important;
  color:rgba(255,255,255,0.92)!important;
}

/* Hover: lift + brightness */
.user-stats-grid .stat-card:hover{
  transform:translateY(-6px) scale(1.02)!important;
  filter:brightness(1.07);
}

/* ── 3. CHART CARDS user: gradient header berbeda per chart ── */
#user-charts-row .chart-card{
  box-shadow:0 6px 24px rgba(8,145,178,0.14)!important;
}
#user-charts-row .chart-card:first-child .chart-card-hdr{
  background:linear-gradient(135deg,#7c3aed 0%,#06b6d4 100%);
  border-bottom:none;
}
#user-charts-row .chart-card:last-child .chart-card-hdr{
  background:linear-gradient(135deg,#ec4899 0%,#f97316 100%);
  border-bottom:none;
}
#user-charts-row .chart-card-hdr .chart-card-title{color:#ffffff!important;}

/* ── 4. TICKET SECTION HEADER: gradient accent ── */
.user-dashboard .user-ticket-header{
  background:linear-gradient(135deg,rgba(14,165,233,0.09),rgba(240,249,252,0.97));
  border-bottom:2px solid rgba(14,165,233,0.2)!important;
}
.user-dashboard .user-ticket-title{
  background:linear-gradient(135deg,#0ea5e9,#6366f1);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;
  font-weight:800;
}

/* ── 5. SLA PANEL kolom kanan: glassmorphism + header warna ── */
#ud-stage-panel{
  background:rgba(255,255,255,0.72)!important;
  backdrop-filter:blur(18px)!important;-webkit-backdrop-filter:blur(18px)!important;
  box-shadow:0 6px 28px rgba(124,58,237,0.16)!important;
  border:1px solid rgba(124,58,237,0.18)!important;
}
#ud-stage-panel>div:first-child{
  background:linear-gradient(135deg,rgba(124,58,237,0.14),rgba(240,249,252,0.92))!important;
  border-bottom:1px solid rgba(124,58,237,0.16)!important;
}
</style>
