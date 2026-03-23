<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $config['appName'] ?? 'GoTiket' }} — {{ $config['appSubtitle'] ?? 'Atur Kerja, Dukung Tim' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
</style>
</head>
<body>
<div class="bg-layer" id="bg-layer"></div>

<aside class="sidebar" id="sidebar">
  <div class="logo">
    <div class="logo-icon" id="logo-icon">🗂️</div>
    <div><div class="logo-text" id="logo-name">GoTiket</div><div class="logo-sub" id="logo-sub">Atur Kerja, Dukung Tim</div></div>
  </div>
  <nav class="nav">
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
    <div class="view-toggle">
      <button class="view-btn active" id="btn-board" onclick="switchView('board',this)" title="Tampilan Papan">⊞</button>
      <button class="view-btn" id="btn-list" onclick="switchView('list',this)" title="Tampilan Daftar">☰</button>
    </div>
    @if($user->type !== 'user')
    <a href="{{ route('tickets.export') }}" class="btn btn-ghost" title="Ekspor data tiket ke Excel">📊 Ekspor Excel</a>
    @endif
    @if($user->type === 'user')
    <button class="btn btn-primary" onclick="openCreate()">+ Buat Tiket</button>
    @endif
  </div>

  <div class="content">
    <!-- USER DASHBOARD -->
    <div id="user-dashboard" class="user-dashboard" style="{{ $user->type !== 'user' ? 'display:none' : '' }}">
      <div class="user-dash-header">
        <div class="user-dash-greeting" id="ud-greeting">Selamat datang, {{ $user->name }}!</div>
        <div class="user-dash-sub" id="ud-sub">Berikut ringkasan tiket kamu</div>
      </div>
      <div class="user-stats-grid" style="margin-bottom:14px">
        <div class="stat-card blue"><div class="stat-icon blue">📋</div><div class="stat-value" id="ovv-total">0</div><div class="stat-label">Total Tiket</div><div class="stat-sub">Semua tiket kamu</div></div>
        <div class="stat-card orange"><div class="stat-icon orange">💻</div><div class="stat-value" id="ovv-prog">0</div><div class="stat-label">Sedang Berjalan</div><div class="stat-sub">Dalam pengerjaan</div></div>
        <div class="stat-card green"><div class="stat-icon green">✅</div><div class="stat-value" id="ovv-done">0</div><div class="stat-label">Selesai / Tayang</div><div class="stat-sub">Sudah production</div></div>
        <div class="stat-card teal"><div class="stat-icon" style="background:var(--teal-bg)">🔒</div><div class="stat-value" id="ovv-closed">0</div><div class="stat-label">Ditutup</div><div class="stat-sub">Tiket ditutup</div></div>
      </div>
      <div id="ud-banners" style="margin-bottom:10px"></div>
      <div class="user-ticket-section">
        <div class="user-ticket-header">
          <span class="user-ticket-title">📄 Tiket Saya</span>
          <span id="ud-ticket-count" style="font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace"></span>
        </div>
        <div style="padding:10px 16px;border-bottom:1px solid var(--border);background:rgba(248,252,255,.6);display:flex;flex-direction:column;gap:8px">
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
      <!-- Riwayat Tahap tiket user -->
      <div id="ud-stage-panel" style="background:rgba(255,255,255,0.90);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 10px rgba(8,145,178,0.07);margin-top:14px;display:none">
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);background:var(--surface2)">
          <span style="font-size:13px;font-weight:700">📊 Progres SLA Tiket Saya</span>
        </div>
        <div id="ud-sla-list" style="padding:8px 12px;display:flex;flex-direction:column;gap:8px"></div>
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
<script>
/* ═══ DATA AWAL DARI SERVER ═══ */
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const CUR_USER = @json($_curUser);
const INIT_TICKETS = @json($_tickets);
const INIT_CLIENTS = @json($_clients);
const INIT_IT_TEAM = @json($itTeam);
const INIT_CONFIG  = @json($config);
const INIT_AA      = @json($_aa);

/* ═══ STATE GLOBAL ═══ */
let tickets = [...INIT_TICKETS];
let CLIENTS = [...INIT_CLIENTS];
let IT_TEAM = [...INIT_IT_TEAM];
let AUTO_ASSIGN = [...INIT_AA];
let APP_CONFIG  = Object.assign({appName:'GoTiket',appSubtitle:'Atur Kerja, Dukung Tim',appIcon:'🗂️',bgType:'gradient',bgColor:'#e0f2f7',bgGradient:'linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%)',bgImage:''}, INIT_CONFIG);
const curUser = CUR_USER;

let sfilt=null, tfilt='all', sq='', curDetail=null, udTab='all', _editTaskId=null;
let _listPage=1, _listPerPage=25;
let uploadedFiles=[];

/* ═══ API HELPER ═══ */
function api(url, options={}) {
  const isForm = options.body instanceof FormData;
  return fetch(url, {
    headers: isForm ? {'X-CSRF-TOKEN':CSRF,'Accept':'application/json',...(options.headers||{})} : {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json',...(options.headers||{})},
    ...options,
  }).then(r=>r.json());
}

function apiJson(url, method='GET', data=null) {
  return api(url, {method, body: data ? JSON.stringify(data) : undefined});
}

/* ═══ LOADING HELPER ═══ */
function setLoading(btn, isLoading) {
  if(!btn) return;
  if(isLoading) {
    btn.disabled = true;
    btn._origHtml = btn.innerHTML;
    btn.innerHTML = '⏳ Memproses...';
  } else {
    btn.disabled = false;
    btn.innerHTML = btn._origHtml || btn.innerHTML;
  }
}

/* ═══ CONSTANTS ═══ */
const AV=['#4f8ef7','#3dd68c','#f5c542','#f56565','#a78bfa','#fb923c','#f472b6','#34d399'];

/* ═══ HELPERS ═══ */
function ini(n){if(!n)return'??';return n.split(' ').map(x=>x[0]).join('').substring(0,2).toUpperCase();}
function ac(n){if(!n)return AV[0];let h=0;for(let c of n)h+=c.charCodeAt(0);return AV[h%AV.length];}
function stateLabel(t){
  if(t.approval==='pending') return 'Menunggu Persetujuan';
  if(t.closed_at) return 'Selesai';
  if(t.freeze_status==='active') return '🧊 Pending/Freeze';
  if(t.freeze_status==='pending_approval') return '⏸ Req. Freeze';
  return 'Berjalan';
}
function stateClass(t){
  if(t.approval==='pending') return 'sc-pending';
  if(t.closed_at) return 'sc-closed';
  if(t.freeze_status==='active') return 'sc-frozen';
  if(t.freeze_status==='pending_approval') return 'sc-req-freeze';
  return 'sc-active';
}
function isAdmin(){return curUser.type==='it'||curUser.type==='it_manager';}
function fmts(d){if(!d)return'—';return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short'});}
function todayStr(){return new Date().toISOString().split('T')[0];}

/* ═══ ACTIVITY LOG ═══ */
function renderActivityLog(){
  const el=document.getElementById('activity-list');
  if(!el) return;
  const sorted=[...tickets].sort((a,b)=>new Date(b.created_at)-new Date(a.created_at)).slice(0,8);
  if(!sorted.length){el.innerHTML='<div style="text-align:center;padding:20px;color:var(--text3);font-size:12px">Belum ada aktivitas</div>';return;}
  document.getElementById('activity-count').textContent=tickets.length+' tiket';
  const typeIcon={incident:'🚨',newproject:'🆕',openrequest:'📬'};
  el.innerHTML=sorted.map(t=>{
    const bg=t.approval==='pending'?'var(--yellow-bg)':t.closed_at?'var(--green-bg)':'var(--accent-glow)';
    const ic=t.approval==='pending'?'⏳':typeIcon[t.type]||'📋';
    const when=fmts(t.created_at);
    return `<div class="activity-item" onclick="openDetail('${t.id}')" style="cursor:pointer">
      <div class="activity-dot" style="background:${bg}">${ic}</div>
      <div class="activity-body">
        <div class="activity-title">${t.id} · ${t.title}</div>
        <div class="activity-meta">${stateLabel(t)} · ${t.assignee||'—'} · ${when}</div>
      </div>
    </div>`;
  }).join('');
}

/* ═══ SLA PANEL ═══ */
function renderSLAPanel(){
  const el=document.getElementById('sla-list');
  if(!el) return;
  // Active tickets (approved, not closed)
  const active=tickets.filter(t=>t.approval==='approved'&&!t.closed_at);
  document.getElementById('sla-summary-count').textContent=active.length+' aktif';
  if(!active.length){el.innerHTML='<div style="text-align:center;padding:20px;color:var(--text3);font-size:12px">Tidak ada tiket aktif</div>';return;}
  // Sort by SLA pct descending (most urgent first)
  const sorted=[...active].sort((a,b)=>(b.sla?.pct||0)-(a.sla?.pct||0));
  el.innerHTML=sorted.slice(0,6).map(t=>{
    const sla=t.sla||{};
    const bar=sla.bar||'var(--text3)';
    const pct=sla.pct||0;
    return `<div class="sla-row-item" style="cursor:pointer" onclick="openDetail('${t.id}')">
      <div class="sla-row-top">
        <span class="sla-row-title" title="${t.title}">${t.id} · ${t.title}</span>
        <span class="sla-row-label ${sla.cls||''}">${sla.label||'—'}</span>
      </div>
      <div class="sla-mini-bar"><div class="sla-mini-fill" style="width:${pct}%;background:${bar}"></div></div>
    </div>`;
  }).join('');
}

/* ═══ RENDER ALL ═══ */
let _currentView = 'board'; // track view state
function renderAll(){
  const isUser=curUser.type==='user';
  document.getElementById('user-dashboard').style.display=isUser?'':'none';
  document.getElementById('it-dashboard').style.display=isUser?'none':'';
  if(isUser){
    document.getElementById('board-view').style.display='none';
    document.getElementById('list-view').style.display='none';
    renderUserDashboard();
  } else {
    const showList=_currentView==='list';
    document.getElementById('board-view').style.display=showList?'none':'';
    document.getElementById('list-view').style.display=showList?'':'none';
    renderStats();
    renderBoard();
    renderTable();
    renderBadges();
    renderActivityLog();
    renderSLAPanel();
  }
}

let udTab2='all', udTypeFilt='all';
function udSetTab(tab,el){udTab2=tab;document.querySelectorAll('.ud-tab').forEach(b=>b.classList.remove('active'));el.classList.add('active');renderUserDashboard();}
function udSetType(type,el){udTypeFilt=type;document.querySelectorAll('.ud-type-btn').forEach(b=>b.classList.remove('active'));el.classList.add('active');renderUserDashboard();}

function renderUserDashboard(){
  const mine=tickets.filter(t=>t.creator_id===curUser.id);
  const active=mine.filter(t=>t.approval==='approved'&&!t.closed_at);
  const done=mine.filter(t=>!!t.closed_at);
  const pending=mine.filter(t=>t.approval==='pending');
  document.getElementById('ovv-total').textContent=mine.length;
  document.getElementById('ovv-prog').textContent=active.length;
  document.getElementById('ovv-done').textContent=done.length;
  document.getElementById('ovv-closed').textContent=done.length;
  // Banner pending + banner penolakan
  const bannerEl=document.getElementById('ud-banners');
  if(bannerEl){
    const pendingBanner=pending.length?`<div class="ud-banner" onclick="openApprovalQueue()" style="background:var(--yellow-bg);border-color:rgba(245,197,66,.3)">
      <div class="ud-banner-icon">⏳</div>
      <div class="ud-banner-text"><div class="ud-banner-title" style="color:var(--yellow)">${pending.length} tiket menunggu persetujuan</div>
      <div class="ud-banner-sub">Klik untuk melihat detail antrean persetujuan</div></div>
      <div class="ud-banner-arrow" style="color:var(--yellow)">→</div>
    </div>`:'';
    bannerEl.innerHTML=pendingBanner;
    fetch('/tickets/rejection-notice').then(r=>r.json()).then(res=>{
      if(res.has_notice&&res.data){
        const d=res.data;
        const rejBanner=document.createElement('div');
        rejBanner.className='ud-banner';
        rejBanner.style.cssText='background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);margin-bottom:8px;align-items:flex-start';
        rejBanner.innerHTML=`<div class="ud-banner-icon" style="color:#ef4444">❌</div>
          <div class="ud-banner-text" style="flex:1">
            <div class="ud-banner-title" style="color:#ef4444">Tiket ${d.ticket_id} ditolak oleh ${d.rejected_by}</div>
            <div class="ud-banner-sub" style="font-weight:600;margin-top:3px">${d.title}</div>
            <div class="ud-banner-sub" style="margin-top:4px">Alasan: ${d.reason}</div>
          </div>
          <button onclick="dismissRejectionNotice(this)" style="border:none;background:none;cursor:pointer;font-size:16px;color:var(--text3);padding:2px 6px;flex-shrink:0" title="Tutup">✕</button>`;
        bannerEl.insertBefore(rejBanner, bannerEl.firstChild);
      }
    }).catch(()=>{});
  }
  document.getElementById('udt-all').textContent=mine.length;
  document.getElementById('udt-active').textContent=active.length;
  document.getElementById('udt-golive').textContent=done.length;
  document.getElementById('udt-closed').textContent=done.length;
  let filtered=mine;
  if(udTab2==='active') filtered=active;
  else if(udTab2==='golive'||udTab2==='closed') filtered=done;
  if(udTypeFilt!=='all') filtered=filtered.filter(t=>t.type===udTypeFilt);
  document.getElementById('ud-ticket-count').textContent=filtered.length+' tiket';
  // Render user SLA panel
  const slaPanel=document.getElementById('ud-stage-panel');
  const slaList=document.getElementById('ud-sla-list');
  if(slaPanel&&slaList){
    const active=mine.filter(t=>t.approval==='approved'&&!t.closed_at);
    slaPanel.style.display=active.length?'':'none';
    slaList.innerHTML=active.sort((a,b)=>(b.sla?.pct||0)-(a.sla?.pct||0)).slice(0,5).map(t=>{
      const sla=t.sla||{};
      return `<div class="sla-row-item" style="cursor:pointer" onclick="openDetail('${t.id}')">
        <div class="sla-row-top">
          <span class="sla-row-title" title="${t.title}">${t.id} · ${t.title}</span>
          <span class="sla-row-label ${sla.cls||''}">${sla.label||'—'}</span>
        </div>
        <div class="sla-mini-bar"><div class="sla-mini-fill" style="width:${sla.pct||0}%;background:${sla.bar||'var(--text3)'}"></div></div>
      </div>`;
    }).join('');
  }
  const el=document.getElementById('ud-ticket-list');
  if(!filtered.length){el.innerHTML=`<div class="user-empty"><div class="user-empty-illus">📭</div><div class="user-empty-title">Tidak ada tiket</div><div class="user-empty-sub">Belum ada tiket di kategori ini.</div>${udTab2==='all'?`<button class="btn btn-primary" onclick="openCreate()" style="margin:0 auto">+ Buat Tiket Pertama</button>`:''}</div>`;return;}
  el.innerHTML=filtered.map(t=>{
    const pct=t.task_total>0?Math.round((t.task_done/t.task_total)*100):0;
    return `<div class="user-ticket-row" onclick="openDetail('${t.id}')">
      <div class="utf-id">${t.id}</div>
      <div class="utf-body">
        <div class="utf-title">${t.title}${t.it_comment_count>0?` <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;background:var(--accent-glow);color:var(--accent);border:1px solid rgba(8,145,178,.3);border-radius:20px;padding:1px 6px;vertical-align:middle">💬 ${t.it_comment_count}</span>`:''}</div>
        <div class="stage-minibar" style="gap:0">
          <div class="progress-bar" style="height:3px;flex:1;margin:0"><div class="progress-fill" style="width:${pct}%;background:${t.closed_at?'var(--green)':'linear-gradient(90deg,var(--accent),#818cf8)'}"></div></div>
        </div>
      </div>
      <div class="utf-status"><span class="status-chip ${stateClass(t)}"><span class="dot"></span>${stateLabel(t)}</span></div>
      <div class="utf-sla ${(t.sla||{}).cls||''}">${(t.sla||{}).due||'—'}</div>
    </div>`;
  }).join('');
}

function renderStats(){
  const pending=tickets.filter(t=>t.approval==='pending');
  const active=tickets.filter(t=>t.approval==='approved'&&!t.closed_at);
  const closed=tickets.filter(t=>!!t.closed_at);
  document.getElementById('s-total').textContent=tickets.length;
  document.getElementById('s-pend').textContent=pending.length;
  document.getElementById('s-active').textContent=active.length;
  document.getElementById('s-closed').textContent=closed.length;
  const sub=document.getElementById('s-total-sub');
  if(sub) sub.textContent=closed.length+' ditutup · '+active.length+' aktif';
}

function renderBadges(){
  const pendTickets=tickets.filter(t=>t.approval==='pending').length;
  const pendFreeze=tickets.filter(t=>t.freeze_status==='pending_approval').length;
  const totalPend=pendTickets+(curUser.type==='manager'?pendFreeze:0);
  document.getElementById('total-badge').textContent=tickets.length;
  const pb=document.getElementById('pending-badge');
  pb.textContent=totalPend;
  pb.style.display=totalPend>0?'':'none';
  const nb=document.getElementById('nav-approval');
  if(nb) nb.style.display=(curUser.type!=='user')?'':'none';
}

function renderBoard(){
  const filt=t=>(tfilt==='all'||t.type===tfilt)&&(!sq||t.id.toLowerCase().includes(sq.toLowerCase())||t.title.toLowerCase().includes(sq.toLowerCase())||(t.assignee||'').toLowerCase().includes(sq.toLowerCase()));
  const todo=tickets.filter(t=>t.approval==='pending'&&filt(t));
  const onprog=tickets.filter(t=>t.approval==='approved'&&!t.closed_at&&filt(t));
  const done=tickets.filter(t=>!!t.closed_at&&filt(t));
  const mk=arr=>arr.map(t=>`
    <div class="ticket-card ${t.approval==='pending'?'pend':t.freeze_status==='active'?'frozen-card':''}" onclick="openDetail('${t.id}')" style="${t.freeze_status==='active'?'border-color:rgba(124,58,237,0.4);':''}">
      ${t.approval==='pending'?'<div class="pend-badge">PENDING</div>':''}
      ${t.freeze_status==='active'?'<div class="pend-badge" style="background:var(--purple);color:white">🧊 FREEZE</div>':''}
      ${t.freeze_status==='pending_approval'?'<div class="pend-badge" style="background:var(--orange);color:white">⏸ REQ. FREEZE</div>':''}
      <div class="ticket-id">${t.id}</div>
      <div class="ticket-title">${t.title}</div>
      <div class="ticket-meta">
        <span class="tag ${t.type}">${t.type==='incident'?'🚨 Insiden':t.type==='newproject'?'🆕 Proyek Baru':'📬 Permintaan'}</span>
        <span class="tag" style="background:var(--surface3);color:var(--text2)">${t.category||''}</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:7px;margin-bottom:3px">
        <span style="font-size:10px;font-weight:600;color:${t.freeze_status==='active'?'var(--purple)':t.freeze_status==='pending_approval'?'var(--orange)':'var(--text3)'}">${stateLabel(t)}</span>
        <span style="font-size:10px;font-weight:700;font-family:'JetBrains Mono',monospace;color:${t.progress===100?'var(--green)':t.progress>0?'var(--accent)':'var(--text3)'}">${t.task_total>0?t.task_done+'/'+t.task_total+' tugas':''}</span>
      </div>
      <div class="progress-bar"><div class="progress-fill" style="width:${t.progress}%;background:${t.closed_at?'var(--green)':t.freeze_status==='active'?'var(--purple)':'linear-gradient(90deg,var(--accent),#818cf8)'}"></div></div>
      <div class="ticket-footer">
        <div class="ticket-assignee"><div class="mini-avatar" style="background:${t.assignee_color||ac(t.assignee)};width:18px;height:18px;font-size:8px">${t.assignee_initials||ini(t.assignee)}</div>${t.assignee||'—'}</div>
        <span class="${(t.sla||{}).cls||''}" style="font-size:10px;font-family:'JetBrains Mono',monospace">${(t.sla||{}).label||'—'}</span>
      </div>
    </div>`).join('');
  document.getElementById('col-todo').innerHTML=mk(todo);
  document.getElementById('col-onprogress').innerHTML=mk(onprog);
  document.getElementById('col-done').innerHTML=mk(done);
  document.getElementById('cnt-todo').textContent=todo.length;
  document.getElementById('cnt-onprogress').textContent=onprog.length;
  document.getElementById('cnt-done').textContent=done.length;
}

function renderTable(){
  const sqL=sq.toLowerCase();
  const list=tickets.filter(t=>!sq||
    t.id.toLowerCase().includes(sqL)||
    t.title.toLowerCase().includes(sqL)||
    (t.assignee||'').toLowerCase().includes(sqL)
  );
  const total=list.length;
  const totalPages=Math.max(1,Math.ceil(total/_listPerPage));
  if(_listPage>totalPages) _listPage=totalPages;
  const start=(_listPage-1)*_listPerPage;
  const page=list.slice(start, start+_listPerPage);

  const tbody=document.getElementById('tbl-body');
  if(!total){
    tbody.innerHTML=`<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text3)">Tidak ada tiket</td></tr>`;
    document.getElementById('tbl-pagination').innerHTML='';
    return;
  }
  tbody.innerHTML=page.map(t=>`
    <tr>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3)">${t.id}</td>
      <td style="font-weight:600;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${t.title}</td>
      <td><span class="status-chip ${stateClass(t)}"><span class="dot"></span>${stateLabel(t)}</span></td>
      <td><div style="display:flex;align-items:center;gap:5px"><div class="mini-avatar" style="background:${t.assignee_color||ac(t.assignee)}">${t.assignee_initials||ini(t.assignee)}</div><span style="font-size:12px">${t.assignee||'—'}</span></div></td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3)">${fmts(t.created_at)}</td>
      <td><span class="${(t.sla||{}).cls||''}" style="font-family:'JetBrains Mono',monospace;font-size:11px">${(t.sla||{}).due||t.due_date||'—'}</span></td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px">${t.lead_time||'—'}</td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text3)">${t.closed_at?fmts(t.closed_at):'—'}</td>
      <td><button class="btn btn-ghost" style="font-size:11px;padding:4px 10px" onclick="openDetail('${t.id}')">Lihat</button></td>
    </tr>`).join('');

  renderPagination(total, totalPages);
}

function renderPagination(total, totalPages){
  const pg=document.getElementById('tbl-pagination');
  if(!pg) return;
  if(totalPages<=1 && total<=_listPerPage){pg.innerHTML='';return;}

  const start=(_listPage-1)*_listPerPage+1;
  const end=Math.min(_listPage*_listPerPage, total);

  // Halaman yang ditampilkan: selalu tampil first, last, dan window sekitar current
  const pages=[];
  for(let i=1;i<=totalPages;i++){
    if(i===1||i===totalPages||Math.abs(i-_listPage)<=2) pages.push(i);
  }
  const pgBtns=[];
  let prev=-1;
  for(const p of pages){
    if(prev!==-1 && p-prev>1) pgBtns.push('<span class="pg-ellipsis">…</span>');
    pgBtns.push(`<button class="pg-btn${p===_listPage?' active':''}" onclick="setListPage(${p})">${p}</button>`);
    prev=p;
  }

  pg.innerHTML=`<div class="pagination">
    <div class="pagination-info">Menampilkan ${start}–${end} dari <strong>${total}</strong> tiket</div>
    <div class="pagination-controls">
      <button class="pg-btn" onclick="setListPage(${_listPage-1})" ${_listPage<=1?'disabled':''}>‹</button>
      ${pgBtns.join('')}
      <button class="pg-btn" onclick="setListPage(${_listPage+1})" ${_listPage>=totalPages?'disabled':''}>›</button>
    </div>
    <div class="pg-perpage">Baris/halaman:
      <select onchange="setListPerPage(+this.value)">
        ${[10,25,50,100].map(n=>`<option value="${n}"${n===_listPerPage?' selected':''}>${n}</option>`).join('')}
      </select>
    </div>
  </div>`;
}

function setListPage(p){
  const total=tickets.filter(t=>!sq||t.id.toLowerCase().includes(sq.toLowerCase())||t.title.toLowerCase().includes(sq.toLowerCase())||(t.assignee||'').toLowerCase().includes(sq.toLowerCase())).length;
  const totalPages=Math.max(1,Math.ceil(total/_listPerPage));
  _listPage=Math.max(1,Math.min(p,totalPages));
  renderTable();
  // Scroll ke atas tabel
  document.getElementById('list-view')?.scrollIntoView({behavior:'smooth',block:'start'});
}

function setListPerPage(n){
  _listPerPage=n;
  _listPage=1;
  renderTable();
}

/* ═══ OPEN CREATE ═══ */
function openCreate(){
  document.getElementById('f-title').value='';
  document.getElementById('f-desc').value='';
  document.getElementById('f-type').value='openrequest';
  resetUpload();
  const fc=document.getElementById('f-client');
  fc.innerHTML=CLIENTS.map(c=>`<option value="${c.nama}">${c.nama}</option>`).join('');
  const approver=curUser.approver||'atasan';
  document.getElementById('approver-label').textContent=approver;
  previewAssign();
  document.getElementById('m-create').classList.add('active');
}

function previewAssign(){
  const cat=document.getElementById('f-cat').value;
  const client=document.getElementById('f-client').value;
  const rule=AUTO_ASSIGN.find(r=>r.kategori===cat&&r.client===client)||AUTO_ASSIGN.find(r=>r.kategori===cat);
  const name=rule?.assignee||'Puji Rahmat';
  document.getElementById('assign-preview-name').textContent=name;
  document.getElementById('assign-preview').style.display='';
}

/* ═══ SUBMIT TICKET ═══ */
function submitTicket(){
  const title=document.getElementById('f-title').value.trim();
  if(!title){document.getElementById('f-title').style.borderColor='var(--red)';return;}
  document.getElementById('f-title').style.borderColor='';
  const btn=document.getElementById('btn-submit-ticket');
  const form=new FormData();
  form.append('title',title);
  form.append('desc',document.getElementById('f-desc').value.trim());
  form.append('type',document.getElementById('f-type').value);
  form.append('category',document.getElementById('f-cat').value);
  form.append('client',document.getElementById('f-client').value);
  uploadedFiles.forEach(f=>{if(f.file) form.append('attachments[]',f.file);});
  setLoading(btn,true);
  api('{{ route('tickets.store') }}',{method:'POST',body:form}).then(r=>{
    setLoading(btn,false);
    if(r.success){
      closeM('m-create');
      reloadTickets();
      showToast('📨 Tiket dikirim untuk persetujuan atasan');
    } else showToast(r.message||'Gagal membuat tiket','err');
  }).catch(()=>setLoading(btn,false));
}

/* ═══ RELOAD TICKETS ═══ */
function reloadTickets(){
  fetch('{{ route('app.data') }}',{headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}})
    .then(r=>r.json()).then(data=>{
      tickets=[...data.tickets];
      CLIENTS=[...data.clients];
      IT_TEAM=[...data.itTeam];
      AUTO_ASSIGN=[...data.autoAssignRules];
      // Tampilkan banner jika data melebihi batas muat (500)
      const banner=document.getElementById('tbl-load-banner');
      if(banner){
        const total=data.tickets_total||0;
        const loaded=tickets.length;
        banner.style.display=(total>loaded)?'':'none';
        if(total>loaded) banner.textContent=`⚠️ Menampilkan ${loaded} dari ${total} tiket. Gunakan filter/pencarian untuk mempersempit hasil.`;
      }
      _listPage=1;
      renderAll();
    });
}

/* ═══ OPEN DETAIL ═══ */
function openDetail(id){
  curDetail=id;
  fetch(`/tickets/${id}`,{headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}})
    .then(r=>r.json()).then(t=>renderDetail(t));
  document.getElementById('m-detail').classList.add('active');
}

function renderDetail(t){
  const bd=document.getElementById('btn-del-tix');
  if(bd) bd.style.display=t.can_delete?'':'none';

  // Hitung tugas overdue untuk banner peringatan
  const todayMs = new Date(); todayMs.setHours(0,0,0,0);
  const overdueTasks=(t.tasks||[]).filter(tk=>{
    if(!tk.due_date||tk.due_date==='—'||tk.status==='Done') return false;
    return new Date(tk.due_date) < todayMs;
  });
  const overdueDaysMax = overdueTasks.length
    ? Math.max(...overdueTasks.map(tk=>Math.floor((todayMs-new Date(tk.due_date))/86400000)))
    : 0;

  document.getElementById('detail-header-block').innerHTML=`
    <div class="detail-id">${t.id} · Creator: ${t.creator?.name||'—'} · ${t.created_at}</div>
    <div class="detail-title">${t.title}</div>
    <div class="detail-tags">
      <span class="status-chip ${stateClass(t)}"><span class="dot"></span>${stateLabel(t)}</span>
      <span class="tag ${t.type}">${t.type==='incident'?'🚨 Insiden':t.type==='newproject'?'🆕 Proyek Baru':'📬 Permintaan'}</span>
      <span class="tag" style="background:var(--surface3);color:var(--text2)">${t.category||''}</span>
    </div>
    ${overdueTasks.length?`<div style="background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:12px 14px;margin-top:10px;display:flex;gap:10px;align-items:flex-start">
      <div style="font-size:18px;flex-shrink:0">⚠️</div>
      <div>
        <div style="font-size:13px;font-weight:700;color:var(--red);margin-bottom:3px">${overdueTasks.length} Tugas Melewati Tenggat</div>
        <div style="font-size:11.5px;color:var(--text2);line-height:1.5">${overdueTasks.map(tk=>`<span style="font-weight:600">${tk.title}</span> (terlambat ${Math.floor((todayMs-new Date(tk.due_date))/86400000)} hari)`).join(' · ')}
        ${overdueDaysMax>=3?`<br><span style="color:var(--red);font-weight:600">⚡ Pertimbangkan eskalasi ke atasan — sudah melewati tenggat ${overdueDaysMax} hari.</span>`:''}
        </div>
      </div>
    </div>`:''}
    ${t.freeze_status==='active'?`<div class="freeze-banner"><div class="freeze-banner-icon">🧊</div><div class="freeze-banner-body"><div class="freeze-banner-title">Tiket Sedang Dalam Status Freeze</div><div class="freeze-banner-meta">SLA dihentikan sementara · Berakhir: <strong>${t.freeze_ends_at||'—'}</strong><br>Alasan: ${t.freeze_reason||'—'}<br>Diminta oleh: ${t.freeze_requester||'—'}</div></div></div>`:''}
    ${t.freeze_status==='pending_approval'?`<div class="freeze-banner" style="background:rgba(234,88,12,0.07);border-color:rgba(234,88,12,0.3)"><div class="freeze-banner-icon">⏸</div><div class="freeze-banner-body"><div class="freeze-banner-title" style="color:var(--orange)">Request Freeze Menunggu Persetujuan Manager</div><div class="freeze-banner-meta">Durasi: <strong>${t.freeze_duration||'—'} hari</strong> · Alasan: ${t.freeze_reason||'—'}<br>Diminta oleh: ${t.freeze_requester||'—'}</div></div></div>`:''}
    `;

  const sla=t.sla||{};
  document.getElementById('detail-main').innerHTML=`
    <div class="ds"><div class="dst">📋 Deskripsi</div><div class="desc-text">${t.desc||'<span style="color:var(--text3);font-style:italic">Tidak ada deskripsi</span>'}</div></div>
    <div class="ds"><div class="dst">⏱️ SLA & Progres</div>
      <div class="sla-box">
        <div class="sla-row">
          <div class="sla-cell"><div class="lbl">Status SLA</div><div class="val ${sla.cls||''}">${sla.label||'—'}</div></div>
          <div class="sla-cell"><div class="lbl">Durasi</div><div class="val">${t.lead_time||'—'}</div></div>
          <div class="sla-cell"><div class="lbl">Tenggat</div><div class="val">${sla.due||t.due_date||'—'}</div></div>
        </div>
        <div style="margin-top:8px;font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Progres Tugas</div>
        <div class="sla-bar"><div class="sla-fill" style="width:${t.progress}%;background:${t.progress===100?'var(--green)':'linear-gradient(90deg,var(--accent),#818cf8)'}"></div></div>
        <div class="sla-info"><span>0%</span><span>${t.progress}%</span><span>100%</span></div>
      </div>
    </div>
    <div class="ds">
      <div class="dst">✅ Tugas / Daftar Periksa</div>
      ${isAdmin()?`<div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:12px;display:flex;flex-direction:column;gap:8px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <input type="text" class="form-input" id="new-task-title" placeholder="Judul tugas... *" style="font-size:12px">
          <input type="date" class="form-input" id="new-task-due" style="font-size:12px">
        </div>
        <textarea class="form-input" id="new-task-notes" placeholder="Catatan... (opsional)" style="font-size:12px;min-height:46px;resize:vertical"></textarea>
        <div><button class="btn btn-primary" onclick="addTask('${t.id}',this)" style="font-size:12px;padding:7px 12px">➕ Tambah Tugas</button></div>
      </div>`:''}
      <div id="task-list-${t.id}">${renderTaskList(t.tasks||[],t.id)}</div>
    </div>
    <div class="ds">
      <div class="dst">💬 Komentar & Diskusi</div>
      <div class="chat-area" id="chat-area">
        ${(t.comments||[]).map(c=>`
          <div class="chat-msg">
            <div class="mini-avatar" style="background:${c.color||ac(c.user)};width:26px;height:26px;font-size:10px;flex-shrink:0">${c.initials||ini(c.user)}</div>
            <div class="chat-bubble ${c.own?'own':''}"><div class="chat-user">${c.user}</div>${c.text}<div class="chat-time">${c.time}</div></div>
          </div>`).join('')}
      </div>
      <div class="chat-input-area">
        <textarea class="chat-input" id="chat-input" placeholder="Ketik komentar... (Enter untuk kirim, Shift+Enter baris baru)" rows="1"
          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendComment('${t.id}');}"></textarea>
        <button class="btn btn-primary" onclick="sendComment('${t.id}',this)" style="padding:7px 12px;font-size:12px">📤</button>
      </div>
    </div>`;

  document.getElementById('detail-side').innerHTML=`
    <div class="meta-item"><div class="meta-label">Penanggung Jawab</div>
      <div style="display:flex;align-items:center;gap:7px;margin-bottom:6px">
        <div class="mini-avatar" style="background:${t.assignee?.color||ac(t.assignee?.name)};width:24px;height:24px">${t.assignee?.initials||ini(t.assignee?.name)}</div>
        <span class="meta-value">${t.assignee?.name||'—'}</span>
      </div>
      ${curUser.type==='it_manager'?`<select class="form-select" id="reassign-sel" style="font-size:11px;padding:5px 8px">
        ${IT_TEAM.map(u=>`<option value="${u.id}" ${u.id===t.assignee?.id?'selected':''}>${u.name}</option>`).join('')}
      </select>
      <button class="btn btn-ghost" onclick="reassign('${t.id}',this)" style="font-size:11px;padding:4px 10px;margin-top:5px;width:100%">🔄 Ganti Penugasan</button>`:''}
    </div>
    <div class="meta-item"><div class="meta-label">Pembuat</div>
      <div style="display:flex;align-items:center;gap:7px">
        <div class="mini-avatar" style="background:${t.creator?.color||ac(t.creator?.name)};width:24px;height:24px">${t.creator?.initials||ini(t.creator?.name)}</div>
        <span class="meta-value">${t.creator?.name||'—'}</span>
      </div>
    </div>
    ${t.approved_by?`<div class="meta-item"><div class="meta-label">Disetujui Oleh</div><div class="meta-value" style="color:var(--green)">✅ ${t.approved_by}<div style="font-size:11px;color:var(--text3);font-family:'JetBrains Mono',monospace">${t.approved_at||''}</div></div></div>`:''}
    <div class="meta-item"><div class="meta-label">Klien</div><div class="meta-value">${t.client||'—'}</div></div>
    <div class="meta-item"><div class="meta-label">Kategori</div><div class="meta-value">${t.category||'—'}</div></div>
    <div class="meta-item"><div class="meta-label">Progres</div>
      <div style="display:flex;align-items:center;gap:7px">
        <div class="progress-bar" style="flex:1;height:5px"><div class="progress-fill" style="width:${t.progress}%"></div></div>
        <span style="font-size:11px;font-family:'JetBrains Mono',monospace;color:var(--text3)">${t.progress}%</span>
      </div>
    </div>
    ${t.attachments?.length?`<div class="meta-item"><div class="meta-label">📎 Lampiran</div>
      ${t.attachments.map(a=>`<div class="file-item" style="margin-top:5px">
        <span class="file-icon">${a.icon}</span>
        <div class="file-info"><div class="file-name" title="${a.name}">${a.name}</div><div class="file-size">${a.size}</div></div>
        <a href="${a.url}" class="btn btn-ghost" style="font-size:10px;padding:3px 8px" download>⬇️</a>
      </div>`).join('')}
    </div>`:''}
    ${isAdmin()&&!t.closed_at&&t.approval==='approved'?`
      <div style="margin-top:8px;border-top:1px solid var(--border);padding-top:8px;display:flex;flex-direction:column;gap:6px">
        <button class="btn btn-ghost" onclick="closeTix('${t.id}')" style="width:100%;font-size:12px">🔒 Tutup Tiket</button>
        ${!t.freeze_status?`<button class="btn btn-ghost" onclick="openFreezeModal('${t.id}')" style="width:100%;font-size:12px;color:var(--purple);border-color:rgba(124,58,237,0.3)">⏸ Pending / Freeze</button>`:''}
        ${t.freeze_status==='active'?`<button class="btn btn-ghost" onclick="unfreezeTix('${t.id}',this)" style="width:100%;font-size:12px;color:var(--green);border-color:rgba(5,150,105,0.3)">▶ Aktifkan Kembali</button>`:''}
        ${t.freeze_status==='pending_approval'?`<div style="font-size:11px;color:var(--orange);text-align:center;padding:4px">⏸ Menunggu persetujuan freeze...</div>`:''}
      </div>
    `:`${isAdmin()?`<button class="btn btn-ghost" onclick="closeTix('${t.id}')" style="width:100%;font-size:12px;margin-top:6px">🔒 Tutup Tiket</button>`:''}`}`;

  setTimeout(()=>{const ca=document.getElementById('chat-area');if(ca)ca.scrollTop=ca.scrollHeight;},50);
}

function renderTaskList(tasks, ticketId){
  if(!tasks.length) return `<div class="task-empty">Belum ada tugas</div>`;
  const todayMs=new Date(); todayMs.setHours(0,0,0,0);
  return `<table class="task-table"><thead><tr><th>Nama Tugas</th><th>Tenggat</th><th>Catatan</th><th>Status</th><th></th></tr></thead><tbody>
    ${tasks.map(tk=>{
      const taskDue=tk.due_date&&tk.due_date!=='—'?new Date(tk.due_date):null;
      const overdue=taskDue&&taskDue<todayMs&&tk.status!=='Done';
      const overdueDays=overdue?Math.floor((todayMs-taskDue)/86400000):0;
      const notes=tk.notes||'—';
      return `<tr style="${overdue?'background:rgba(220,38,38,0.04)':''}">
        <td style="font-weight:600">${tk.title}${overdue?`<span style="margin-left:6px;font-size:9px;font-weight:700;background:var(--red);color:#fff;padding:1px 6px;border-radius:20px;vertical-align:middle">OVERDUE</span>`:''}${overdue&&overdueDays>=3?`<span style="margin-left:4px;font-size:9px;font-weight:700;background:var(--orange-bg);color:var(--orange);border:1px solid rgba(234,88,12,0.3);padding:1px 6px;border-radius:20px;vertical-align:middle">⚡ Eskalasi</span>`:''}</td>
        <td><span class="task-due${overdue?' overdue':''}">${tk.due_date||'—'}${overdue?` <span style="font-size:9px;font-weight:600">(+${overdueDays}h)</span>`:''}</span></td>
        <td class="task-notes" style="max-width:140px;white-space:normal">${notes}</td>
        <td><span class="${tk.status==='Done'?'task-status-done':'task-status-todo'}" style="${isAdmin()?'cursor:pointer':''}" ${isAdmin()?`onclick="toggleTask(${tk.id},'${ticketId}')"`:''}>${tk.status==='Done'?'Selesai':'Belum'}</span></td>
        <td style="white-space:nowrap">${isAdmin()?`<button onclick="openEditTask(${tk.id},'${tk.title}','${tk.due_date||''}','${(tk.notes||'').replace(/'/g,'&#39;')}')" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:12px" title="Edit">✏️</button><button onclick="deleteTask(${tk.id},'${ticketId}')" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:12px" title="Hapus">✕</button>`:''}</td>
      </tr>`;
    }).join('')}
  </tbody></table>`;
}

/* ═══ TASK ACTIONS ═══ */
function addTask(ticketId,btn){
  const title=document.getElementById('new-task-title').value.trim();
  if(!title) return;
  const due_date=document.getElementById('new-task-due')?.value||null;
  const notes=document.getElementById('new-task-notes')?.value.trim()||null;
  setLoading(btn,true);
  apiJson(`/tickets/${ticketId}/tasks`,'POST',{title,due_date,notes}).then(r=>{
    setLoading(btn,false);
    if(r.success){
      document.getElementById('new-task-title').value='';
      if(document.getElementById('new-task-due')) document.getElementById('new-task-due').value='';
      if(document.getElementById('new-task-notes')) document.getElementById('new-task-notes').value='';
      openDetail(ticketId);
    }
  }).catch(()=>setLoading(btn,false));
}
function toggleTask(taskId,ticketId){
  apiJson(`/tasks/${taskId}/toggle`,'PATCH').then(()=>{reloadTickets();openDetail(ticketId);});
}
function deleteTask(taskId,ticketId){
  if(!confirm('Hapus tugas ini?')) return;
  apiJson(`/tasks/${taskId}`,'DELETE').then(()=>openDetail(ticketId));
}
function openEditTask(id,title,dueDate,notes){
  _editTaskId=id;
  document.getElementById('edit-task-title').value=title;
  document.getElementById('edit-task-due').value=dueDate;
  document.getElementById('edit-task-notes').value=notes.replace(/&#39;/g,"'");
  document.getElementById('m-edittask').classList.add('active');
}
function saveEditTask(){
  if(!_editTaskId) return;
  const data={
    title:document.getElementById('edit-task-title').value.trim(),
    due_date:document.getElementById('edit-task-due').value||null,
    notes:document.getElementById('edit-task-notes').value.trim()||null,
  };
  if(!data.title){showToast('Nama tugas wajib diisi','warn');return;}
  const btn=document.getElementById('btn-save-edittask');
  setLoading(btn,true);
  apiJson(`/tasks/${_editTaskId}`,'PATCH',data).then(r=>{
    setLoading(btn,false);
    if(r.success){closeM('m-edittask');_editTaskId=null;openDetail(curDetail);showToast('✅ Tugas diperbarui');}
    else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}

/* ═══ DELETE TICKET ═══ */
function deleteTix(id){
  if(!confirm('Hapus tiket '+id+'? Tindakan ini tidak dapat dibatalkan.')) return;
  apiJson(`/tickets/${id}`,'DELETE').then(r=>{
    if(r.success){closeM('m-detail');reloadTickets();showToast('🗑️ Tiket '+id+' dihapus','warn');}
  });
}

/* ═══ CLOSE TICKET ═══ */
function closeTix(id){
  if(!confirm('Tutup tiket '+id+'? Status akan berubah menjadi Selesai dan tidak dapat dibuka kembali.')) return;
  apiJson(`/tickets/${id}/close`,'POST').then(r=>{
    if(r.success){reloadTickets();openDetail(id);showToast('🔒 Tiket '+id+' berhasil ditutup');}
  });
}

/* ═══ REASSIGN ═══ */
function reassign(id,btn){
  const sel=document.getElementById('reassign-sel');
  if(!sel) return;
  setLoading(btn,true);
  apiJson(`/tickets/${id}/reassign`,'POST',{assignee_id:sel.value}).then(r=>{
    setLoading(btn,false);
    if(r.success){reloadTickets();openDetail(id);showToast('🔄 Penugasan diperbarui');}
  }).catch(()=>setLoading(btn,false));
}

/* ═══ COMMENT ═══ */
function sendComment(ticketId,btn){
  const inp=document.getElementById('chat-input');
  const text=inp.value.trim();
  if(!text) return;
  setLoading(btn,true);
  apiJson(`/tickets/${ticketId}/comment`,'POST',{text}).then(r=>{
    setLoading(btn,false);
    if(r.success){
      inp.value='';
      const ca=document.getElementById('chat-area');
      const c=r.comment;
      const div=document.createElement('div');
      div.className='chat-msg';div.style.animation='fu .2s ease';
      div.innerHTML=`<div class="mini-avatar" style="background:${c.color||ac(c.user)};width:26px;height:26px;font-size:10px;flex-shrink:0">${c.initials||ini(c.user)}</div>
        <div class="chat-bubble own"><div class="chat-user">${c.user}</div>${c.text}<div class="chat-time">${c.time}</div></div>`;
      ca.appendChild(div);
      ca.scrollTop=ca.scrollHeight;
    }
  }).catch(()=>setLoading(btn,false));
}

/* ═══ APPROVAL QUEUE ═══ */
function openApprovalQueue(){
  const pend=tickets.filter(t=>t.approval==='pending');
  const pendFreeze=tickets.filter(t=>t.freeze_status==='pending_approval');
  const isM=curUser.type==='manager';
  let html='';

  // Section: Antrean tiket baru
  if(!pend.length){
    html+='<div style="text-align:center;padding:24px;color:var(--text3);font-size:13px">Tidak ada tiket pending ✅</div>';
  } else {
    html+=pend.map(t=>`<div class="aq-item">
      <div class="aq-hdr"><span class="aq-id">${t.id}</span></div>
      <div class="aq-title">${t.title}</div>
      <div class="aq-meta">🤖 Auto-assign: <strong>${t.assignee||'—'}</strong> · 📂 ${t.category||''} · 🗓 ${fmts(t.created_at)}<br>👤 Diajukan oleh: <strong>${t.creator||'—'}</strong></div>
      ${isM?`<div class="aq-actions">
        <button class="btn btn-success" onclick="approveTix('${t.id}')">✅ Setujui</button>
        <button class="btn btn-danger" onclick="rejectTix('${t.id}')">❌ Tolak</button>
        <button class="btn btn-ghost" onclick="closeM('m-approval');openDetail('${t.id}')">Lihat</button>
      </div>`:`<div style="margin-top:8px;font-size:12px;color:var(--yellow)">ℹ️ Hanya Kepala Departemen yang dapat menyetujui tiket.</div>`}
    </div>`).join('');
  }

  // Section: Antrean request freeze
  if(pendFreeze.length){
    html+=`<div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
      <div style="font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">⏸ Request Pending/Freeze (${pendFreeze.length})</div>
      ${pendFreeze.map(t=>`<div class="aq-item" style="border-color:rgba(124,58,237,0.25);background:rgba(124,58,237,0.04)">
        <div class="aq-hdr"><span class="aq-id" style="color:var(--purple)">${t.id}</span><span style="font-size:10px;color:var(--purple);font-weight:600;margin-left:8px">🧊 Freeze ${t.freeze_duration||'?'} hari</span></div>
        <div class="aq-title">${t.title}</div>
        <div class="aq-meta">📋 Diminta oleh: <strong>${t.freeze_requester||'—'}</strong> · 📂 ${t.category||''}<br>💬 Alasan: ${t.freeze_reason||'—'}</div>
        ${isM?`<div class="aq-actions">
          <button class="btn btn-success" onclick="approveFreezeReq(${t.freeze_id},this)">✅ Setujui Freeze</button>
          <button class="btn btn-danger" onclick="rejectFreezeReq(${t.freeze_id},this)">❌ Tolak</button>
          <button class="btn btn-ghost" onclick="closeM('m-approval');openDetail('${t.id}')">Lihat</button>
        </div>`:`<div style="margin-top:8px;font-size:12px;color:var(--purple)">ℹ️ Hanya Manager yang dapat menyetujui freeze.</div>`}
      </div>`).join('')}
    </div>`;
  }

  document.getElementById('approval-body').innerHTML=html;
  document.getElementById('m-approval').classList.add('active');
}

function approveTix(id){
  document.getElementById('confirm-approve-id').textContent=id;
  _pendingApproveId=id;
  document.getElementById('m-confirm-approve').classList.add('active');
}
let _pendingApproveId=null;
function doApproveTix(){
  const id=_pendingApproveId; if(!id)return;
  _pendingApproveId=null; closeM('m-confirm-approve');
  apiJson(`/tickets/${id}/approve`,'POST').then(r=>{
    if(r.success){
      reloadTickets();closeM('m-approval');
      showToast('✅ Tiket '+id+' disetujui — IT dapat mulai merencanakan tugas');
    } else showToast(r.message||'Gagal','err');
  });
}

let _rejectTicketId=null;
function rejectTix(id){
  _rejectTicketId=id;
  document.getElementById('reject-reason-ticket-id').textContent=id;
  document.getElementById('reject-reason-text').value='';
  document.getElementById('reject-reason-hint').style.display='none';
  const btn=document.getElementById('btn-confirm-reject');
  btn.disabled=true; btn.style.opacity='.5'; btn.style.cursor='not-allowed';
  closeM('m-approval');
  document.getElementById('m-reject-reason').classList.add('active');
}
function onRejectReasonInput(){
  const val=document.getElementById('reject-reason-text').value.trim();
  const hint=document.getElementById('reject-reason-hint');
  const btn=document.getElementById('btn-confirm-reject');
  const ok=val.length>=10;
  hint.style.display=ok?'none':'block';
  btn.disabled=!ok; btn.style.opacity=ok?'1':'.5'; btn.style.cursor=ok?'pointer':'not-allowed';
}
function doRejectTix(btn){
  const reason=document.getElementById('reject-reason-text').value.trim();
  if(!reason||reason.length<10){showToast('Alasan minimal 10 karakter','warn');return;}
  if(!_rejectTicketId) return;
  setLoading(btn,true);
  apiJson(`/tickets/${_rejectTicketId}/reject`,'DELETE',{reason}).then(r=>{
    setLoading(btn,false);
    if(r.success){
      closeM('m-reject-reason');
      reloadTickets();
      showToast('🗑️ Tiket '+_rejectTicketId+' ditolak','warn');
      _rejectTicketId=null;
    } else showToast(r.message||'Gagal menolak tiket','err');
  }).catch(()=>setLoading(btn,false));
}
function dismissRejectionNotice(btn){
  btn.closest('.ud-banner').remove();
  fetch('/tickets/rejection-notice/dismiss',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}});
}

/* ═══ FREEZE ═══ */
let _freezeTicketId=null;
function openFreezeModal(ticketId){
  _freezeTicketId=ticketId;
  document.getElementById('freeze-duration').value='';
  document.getElementById('freeze-reason').value='';
  document.getElementById('m-freeze').classList.add('active');
}
function submitFreeze(btn){
  const duration=document.getElementById('freeze-duration').value;
  const reason=document.getElementById('freeze-reason').value.trim();
  if(!duration||parseInt(duration)<1){showToast('Durasi freeze wajib diisi (minimal 1 hari)','warn');return;}
  if(!reason){showToast('Alasan freeze wajib diisi','warn');return;}
  if(!_freezeTicketId) return;
  setLoading(btn,true);
  apiJson(`/tickets/${_freezeTicketId}/freeze`,'POST',{duration_days:parseInt(duration),reason}).then(r=>{
    setLoading(btn,false);
    if(r.success){
      closeM('m-freeze');
      _freezeTicketId=null;
      reloadTickets();
      openDetail(curDetail);
      showToast('⏸ Request freeze dikirim ke Manager untuk persetujuan');
    } else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}
function unfreezeTix(id,btn){
  if(!confirm('Aktifkan kembali tiket '+id+'? SLA akan dilanjutkan dari titik terakhir.')) return;
  setLoading(btn,true);
  apiJson(`/tickets/${id}/unfreeze`,'POST').then(r=>{
    setLoading(btn,false);
    if(r.success){reloadTickets();openDetail(id);showToast('▶ Tiket '+id+' diaktifkan kembali. SLA dilanjutkan.');}
    else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}
function approveFreezeReq(freezeId,btn){
  if(!confirm('Setujui request freeze ini? SLA tiket akan dihentikan sementara.')) return;
  setLoading(btn,true);
  apiJson(`/freezes/${freezeId}/approve`,'POST').then(r=>{
    setLoading(btn,false);
    if(r.success){reloadTickets();openApprovalQueue();showToast('✅ Freeze disetujui. SLA tiket dihentikan sementara.');}
    else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}
function rejectFreezeReq(freezeId,btn){
  if(!confirm('Tolak request freeze ini?')) return;
  setLoading(btn,true);
  apiJson(`/freezes/${freezeId}/reject`,'POST').then(r=>{
    setLoading(btn,false);
    if(r.success){reloadTickets();openApprovalQueue();showToast('❌ Request freeze ditolak','warn');}
    else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}

/* ═══ UPLOAD ═══ */
function handleDrop(e){e.preventDefault();document.getElementById('upload-area').classList.remove('drag');handleFileSelect(e.dataTransfer.files);}
function handleFileSelect(files){
  const MAX=100*1024*1024;
  Array.from(files).forEach(file=>{
    if(file.size>MAX){showToast(`❌ "${file.name}" melebihi 100 MB`,'err');return;}
    if(uploadedFiles.find(f=>f.name===file.name&&f.size===file.size)){showToast(`⚠️ "${file.name}" sudah ada`,'warn');return;}
    uploadedFiles.push({name:file.name,size:file.size,file});
    renderFileList();
  });
  document.getElementById('f-files').value='';
}
function removeFile(idx){uploadedFiles.splice(idx,1);renderFileList();}
function formatSize(b){if(b>=1073741824)return(b/1073741824).toFixed(1)+' GB';if(b>=1048576)return(b/1048576).toFixed(1)+' MB';if(b>=1024)return(b/1024).toFixed(1)+' KB';return b+' B';}
function getFileIcon(n){const m={pdf:'📄',doc:'📝',docx:'📝',xls:'🗂️',xlsx:'🗂️',ppt:'📑',pptx:'📑',txt:'📃',csv:'🗂️',png:'🖼️',jpg:'🖼️',jpeg:'🖼️',gif:'🖼️',zip:'🗜️',rar:'🗜️'};const e=n.split('.').pop().toLowerCase();return m[e]||'📎';}
function renderFileList(){
  const el=document.getElementById('file-list');
  if(!el) return;
  el.innerHTML=uploadedFiles.map((f,i)=>`<div class="file-item"><span class="file-icon">${getFileIcon(f.name)}</span><div class="file-info"><div class="file-name">${f.name}</div><div class="file-size">${formatSize(f.size)}</div></div><button class="file-remove" onclick="removeFile(${i})">✕</button></div>`).join('');
}
function resetUpload(){uploadedFiles=[];const fl=document.getElementById('file-list');if(fl)fl.innerHTML='';const fi=document.getElementById('f-files');if(fi)fi.value='';}

/* ═══ AUTO ASSIGN MANAGEMENT ═══ */
function openAutoAssign(){
  const sel=document.getElementById('aa-client');
  sel.innerHTML=CLIENTS.map(c=>`<option value="${c.nama}">${c.nama}</option>`).join('');
  const usel=document.getElementById('aa-user');
  usel.innerHTML=IT_TEAM.map(u=>`<option value="${u.id}">${u.name}</option>`).join('');
  renderAATable();
  renderClientTable();
  document.getElementById('m-autoassign').classList.add('active');
}
function switchAATab(tab){
  document.getElementById('aa-tab-rules').style.display=tab==='rules'?'':'none';
  document.getElementById('aa-tab-clients').style.display=tab==='clients'?'':'none';
  document.getElementById('tab-rules').classList.toggle('active',tab==='rules');
  document.getElementById('tab-clients').classList.toggle('active',tab==='clients');
}
function renderAATable(){
  const el=document.getElementById('aa-table');
  if(!AUTO_ASSIGN.length){el.innerHTML='<div style="text-align:center;padding:20px;color:var(--text3);font-size:12px">Belum ada aturan</div>';return;}
  el.innerHTML=`<table class="task-table"><thead><tr><th>Kategori</th><th>Klien</th><th>Penanggung Jawab</th><th></th></tr></thead><tbody>
    ${AUTO_ASSIGN.map(r=>`<tr><td>${r.kategori}</td><td>${r.client}</td><td>${r.assignee}</td><td><button onclick="deleteAA(${r.id})" style="background:none;border:none;color:var(--red);cursor:pointer">✕</button></td></tr>`).join('')}
  </tbody></table>`;
}
function saveAutoAssign(){
  const data={kategori:document.getElementById('aa-kat').value,client:document.getElementById('aa-client').value,assignee_id:document.getElementById('aa-user').value};
  apiJson('{{ route('autoassign.store') }}','POST',data).then(r=>{if(r.success){reloadTickets();openAutoAssign();showToast('✅ Aturan berhasil disimpan');}});
}
function deleteAA(id){
  apiJson(`/auto-assign/${id}`,'DELETE').then(r=>{if(r.success){reloadTickets();openAutoAssign();}});
}
function renderClientTable(){
  const el=document.getElementById('client-table');
  el.innerHTML=`<table class="task-table"><thead><tr><th>Nama Client</th><th></th></tr></thead><tbody>
    ${CLIENTS.map(c=>`<tr><td>${c.nama}</td><td><button onclick="deleteClient(${c.id},'${c.nama}')" style="background:none;border:none;color:var(--red);cursor:pointer">✕</button></td></tr>`).join('')}
  </tbody></table>`;
}
function addClient(){
  const nama=document.getElementById('new-client-name').value.trim();
  if(!nama) return;
  apiJson('{{ route('clients.store') }}','POST',{nama}).then(r=>{if(r.success){document.getElementById('new-client-name').value='';reloadTickets();openAutoAssign();showToast('✅ Klien berhasil ditambahkan');}});
}
function deleteClient(id, nama){
  if(!confirm('Hapus client "'+nama+'"?')) return;
  apiJson(`/clients/${id}`,'DELETE').then(()=>{reloadTickets();openAutoAssign();showToast('🗑️ Klien berhasil dihapus');});
}

/* ═══ USER MANAGEMENT ═══ */
function openUserManagement(){
  fetch('{{ route('users.data') }}',{headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}})
    .then(r=>r.json()).then(users=>{
      renderUserTable(users);
      // Populate approver dropdown dengan daftar manager
      const managers=users.filter(u=>u.type==='manager');
      const nappr=document.getElementById('nu-approver');
      nappr.innerHTML='<option value="">— Pilih Approver —</option>'+managers.map(u=>`<option value="${u.id}">${u.name}</option>`).join('');
    });
  const ndept=document.getElementById('nu-dept');
  ndept.innerHTML=CLIENTS.map(c=>`<option value="${c.nama}">${c.nama}</option>`).join('')+'<option value="IT">IT</option>';
  onNuTypeChange();
  document.getElementById('m-users').classList.add('active');
}
function renderUserTable(users){
  const el=document.getElementById('user-table');
  el.innerHTML=`<table class="task-table"><thead><tr><th>Nama</th><th>Username</th><th>Tipe</th><th>Departemen</th><th></th></tr></thead><tbody>
    ${users.map(u=>`<tr>
      <td><div style="display:flex;align-items:center;gap:7px"><div class="mini-avatar" style="background:${u.color};width:22px;height:22px;font-size:9px">${u.initials}</div>${u.name}</div></td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px">${u.username}</td>
      <td>${u.type==='it'?'🔧 IT SIM':u.type==='it_manager'?'⚙️ Manager IT':u.type==='manager'?'⭐ Manajer':'👤 Pengguna'}</td>
      <td>${u.dept}</td>
      <td>${u.id!==curUser.id?`<button onclick="deleteUser(${u.id})" class="btn btn-danger" style="font-size:10px;padding:3px 8px">Hapus</button>`:''}</td>
    </tr>`).join('')}
  </tbody></table>`;
}
function onNuTypeChange(){
  const t=document.getElementById('nu-type').value;
  document.getElementById('nu-approver-wrap').style.display=t==='user'?'':'none';
}
function addUser(){
  const type=document.getElementById('nu-type').value;
  const data={
    name:document.getElementById('nu-name').value.trim(),
    username:document.getElementById('nu-username').value.trim(),
    type:type,
    role:document.getElementById('nu-role').value.trim()||'Staff',
    dept:document.getElementById('nu-dept').value,
    approver_id:type==='user'?document.getElementById('nu-approver').value||null:null
  };
  if(!data.name||!data.username){showToast('Nama dan username wajib diisi','warn');return;}
  const btn=document.getElementById('btn-add-user');
  setLoading(btn,true);
  apiJson('{{ route('users.store') }}','POST',data).then(r=>{
    setLoading(btn,false);
    if(r.success){
      // reset form
      ['nu-name','nu-username','nu-role'].forEach(id=>document.getElementById(id).value='');
      document.getElementById('nu-type').value='user';
      onNuTypeChange();
      openUserManagement();
      showToast('✅ User '+r.user.name+' ditambahkan. Kata sandi default: username+123');
    } else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}
function deleteUser(id){
  if(!confirm('Hapus pengguna ini?')) return;
  apiJson(`/users/${id}`,'DELETE').then(r=>{if(r.success)openUserManagement();});
}

/* ═══ APP SETTINGS ═══ */
function openAppSettings(){
  document.getElementById('cfg-name').value=APP_CONFIG.appName||'GoTiket';
  document.getElementById('cfg-subtitle').value=APP_CONFIG.appSubtitle||'Atur Kerja, Dukung Tim';
  document.getElementById('cfg-icon').value=APP_CONFIG.appIcon||'🗂️';
  document.getElementById('cfg-bg-gradient').value=APP_CONFIG.bgGradient||'';
  document.getElementById('cfg-bg-image').value=APP_CONFIG.bgImage||'';
  const emojis=['🗂️','🎯','🚀','💡','⚡','🔧','📊','🏆','🌟','💎','🔐','📱','🖥️','🌈','🎨'];
  document.getElementById('emoji-grid').innerHTML=emojis.map(e=>`<span onclick="selectEmoji('${e}')" style="font-size:22px;cursor:pointer;padding:4px;border-radius:6px;transition:background .15s" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='none'">${e}</span>`).join('');
  const bgPresets=['#0a0c10','#0f172a','#1e293b','#e0f2f7','#f8fafc','#fefce8'];
  document.getElementById('bg-presets').innerHTML=bgPresets.map(c=>`<div onclick="setBgColor('${c}')" style="width:28px;height:28px;border-radius:6px;background:${c};cursor:pointer;border:2px solid var(--border)"></div>`).join('');
  const gradients=['linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%)','linear-gradient(135deg,#0c4a6e,#0e7490,#0d9488)','linear-gradient(135deg,#1e1b4b,#312e81,#1e40af)','linear-gradient(135deg,#0f172a,#1e293b,#334155)','linear-gradient(135deg,#fdf4ff,#fce7f3,#ffe4e6)','linear-gradient(135deg,#f0fdf4,#dcfce7,#d1fae5)'];
  document.getElementById('gradient-presets').innerHTML=gradients.map(g=>`<div onclick="setBgGradient('${g}')" style="width:48px;height:32px;border-radius:6px;background:${g};cursor:pointer;border:2px solid var(--border)"></div>`).join('');
  switchBgTab(APP_CONFIG.bgType||'gradient');
  updateBgPreview();
  document.getElementById('m-appsettings').classList.add('active');
}
function selectEmoji(e){document.getElementById('cfg-icon').value=e;document.getElementById('emoji-picker').style.display='none';}
function toggleEmojiPicker(){const ep=document.getElementById('emoji-picker');ep.style.display=ep.style.display==='none'?'':'none';}
function switchBgTab(tab){
  APP_CONFIG.bgType=tab;
  ['solid','gradient','image'].forEach(t=>{document.getElementById('bg-panel-'+t).style.display=t===tab?'':'none';document.getElementById('bg-tab-'+t).classList.toggle('active',t===tab);});
  updateBgPreview();
}
function setBgColor(c){document.getElementById('cfg-bg-color').value=c;document.getElementById('cfg-bg-color-hex').value=c;APP_CONFIG.bgColor=c;updateBgPreview();}
function setBgGradient(g){document.getElementById('cfg-bg-gradient').value=g;APP_CONFIG.bgGradient=g;updateBgPreview();}
function syncColorFromHex(v){if(/^#[0-9a-fA-F]{6}$/.test(v)){document.getElementById('cfg-bg-color').value=v;APP_CONFIG.bgColor=v;updateBgPreview();}}
function updateBgPreview(){
  const el=document.getElementById('bg-preview');if(!el)return;
  const tab=APP_CONFIG.bgType;
  if(tab==='solid') el.style.background=document.getElementById('cfg-bg-color').value||APP_CONFIG.bgColor;
  else if(tab==='gradient') el.style.background=document.getElementById('cfg-bg-gradient').value||APP_CONFIG.bgGradient;
  else{const img=document.getElementById('cfg-bg-image').value;el.style.background=img?`url(${img}) center/cover`:'#ccc';}
}
function saveAppSettings(){
  APP_CONFIG.appName=document.getElementById('cfg-name').value.trim()||'GoTiket';
  APP_CONFIG.appSubtitle=document.getElementById('cfg-subtitle').value.trim()||'Atur Kerja, Dukung Tim';
  APP_CONFIG.appIcon=document.getElementById('cfg-icon').value||'🗂️';
  const tab=APP_CONFIG.bgType;
  if(tab==='solid') APP_CONFIG.bgColor=document.getElementById('cfg-bg-color').value;
  else if(tab==='gradient') APP_CONFIG.bgGradient=document.getElementById('cfg-bg-gradient').value||APP_CONFIG.bgGradient;
  else APP_CONFIG.bgImage=document.getElementById('cfg-bg-image').value.trim();
  apiJson('{{ route('config.save') }}','POST',APP_CONFIG).then(r=>{
    if(r.success){applyAppConfig();closeM('m-appsettings');showToast('✅ Pengaturan disimpan!');}
  });
}
function resetAppSettings(){
  if(!confirm('Reset semua pengaturan ke default? Tampilan aplikasi akan kembali ke awal dan tindakan ini tidak dapat dibatalkan.')) return;
  apiJson('{{ route('config.reset') }}','POST').then(r=>{
    if(r.success){APP_CONFIG={appName:'GoTiket',appSubtitle:'Atur Kerja, Dukung Tim',appIcon:'🗂️',bgType:'gradient',bgColor:'#e0f2f7',bgGradient:'linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%)',bgImage:''};applyAppConfig();closeM('m-appsettings');showToast('↺ Reset default');}
  });
}
function applyAppConfig(){
  const cfg=APP_CONFIG;
  document.getElementById('logo-icon').textContent=cfg.appIcon||'🗂️';
  document.getElementById('logo-name').textContent=cfg.appName||'GoTiket';
  document.getElementById('logo-sub').textContent=cfg.appSubtitle||'';
  document.title=(cfg.appName||'GoTiket')+' — '+(cfg.appSubtitle||'');
  const bg=document.getElementById('bg-layer');if(!bg)return;
  const t=cfg.bgType||'gradient';
  if(t==='solid') bg.style.cssText=`position:fixed;inset:0;z-index:-1;background:${cfg.bgColor||'#e8f4f8'}`;
  else if(t==='gradient') bg.style.cssText=`position:fixed;inset:0;z-index:-1;background:${cfg.bgGradient||'linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%)'}`;
  else if(cfg.bgImage) bg.style.cssText=`position:fixed;inset:0;z-index:-1;background:url(${cfg.bgImage}) center/cover no-repeat`;
}

/* ═══ PASSWORD ═══ */
function openChangePassword(){document.getElementById('pw-old').value='';document.getElementById('pw-new').value='';document.getElementById('pw-confirm').value='';document.getElementById('m-password').classList.add('active');}
function togglePw(id,el){const inp=document.getElementById(id);inp.type=inp.type==='password'?'text':'password';el.textContent=inp.type==='password'?'👁':'🙈';}
function checkPwStrength(v){const bar=document.getElementById('pw-strength-bar');const lbl=document.getElementById('pw-strength-label');if(!v){bar.style.width='0%';lbl.textContent='';return;}let s=0;if(v.length>=6)s++;if(v.length>=10)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;const cols=['#ef4444','#f97316','#eab308','#22c55e','#10b981'];const lbls=['Sangat Lemah','Lemah','Cukup','Kuat','Sangat Kuat'];bar.style.width=(s*20)+'%';bar.style.background=cols[s-1]||'#ef4444';lbl.textContent=lbls[s-1]||'';}
function savePassword(){
  const old=document.getElementById('pw-old').value;
  const nw=document.getElementById('pw-new').value;
  const cf=document.getElementById('pw-confirm').value;
  if(!old||!nw||!cf){showToast('Semua field wajib diisi','warn');return;}
  if(nw!==cf){showToast('Password baru tidak cocok','err');return;}
  if(nw.length<6){showToast('Password minimal 6 karakter','warn');return;}
  const btn=document.getElementById('btn-save-password');
  setLoading(btn,true);
  apiJson('{{ route('password.change') }}','POST',{old_password:old,new_password:nw,new_password_confirmation:cf}).then(r=>{
    setLoading(btn,false);
    if(r.success){closeM('m-password');showToast('✅ Password berhasil diubah');}
    else showToast(r.message||'Gagal','err');
  }).catch(()=>setLoading(btn,false));
}

/* ═══ VIEW / FILTER ═══ */
function switchView(v,el){
  _currentView=v;
  const isB=v==='board';
  document.getElementById('board-view').style.display=isB?'':'none';
  document.getElementById('list-view').style.display=isB?'none':'';
  document.getElementById('btn-board').classList.toggle('active',isB);
  document.getElementById('btn-list').classList.toggle('active',!isB);
  document.getElementById('page-title').textContent=isB?'Papan Tiket':'Semua Tiket';
  if(el){document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));el.classList.add('active');}
  if(isB) renderBoard(); else renderTable();
}
function setTF(f,btn){tfilt=f;document.querySelectorAll('#board-view .filter-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');renderBoard();}
function searchTix(q){sq=q;_listPage=1;renderBoard();renderTable();}

/* ═══ SIDEBAR TOGGLE ═══ */
function toggleSidebar(){
  const sb=document.getElementById('sidebar');
  const main=document.getElementById('main-content');
  const btn=document.getElementById('sidebar-toggle');
  const collapsed=sb.classList.toggle('collapsed');
  main.classList.toggle('sb-collapsed',collapsed);
  btn.classList.toggle('sb-collapsed',collapsed);
  btn.textContent=collapsed?'▶':'☰';
  localStorage.setItem('sb_collapsed',collapsed?'1':'0');
}
(function(){
  if(localStorage.getItem('sb_collapsed')==='1'){
    const sb=document.getElementById('sidebar');
    const main=document.getElementById('main-content');
    const btn=document.getElementById('sidebar-toggle');
    if(sb){sb.classList.add('collapsed');}
    if(main){main.classList.add('sb-collapsed');}
    if(btn){btn.classList.add('sb-collapsed');btn.textContent='▶';}
  }
})();

/* ═══ MODAL ═══ */
function closeM(id){document.getElementById(id).classList.remove('active');}
document.querySelectorAll('.modal-overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('active');}));

/* ═══ TOAST ═══ */
function showToast(msg,type='ok'){
  const t=document.getElementById('toast');
  t.className='toast '+(type==='warn'?'warn':type==='err'?'err':'');
  t.textContent=msg;t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}

/* ═══ EXPORT EXCEL (via server) ═══ */
// Export sudah handled via GET route /tickets/export/excel

/* ═══ NOTIFIKASI IN-APP ═══ */
let _notifCount = -1; // -1 = initial load, skip toast pertama kali

function toggleNotifDropdown() {
  const dd = document.getElementById('notif-dropdown');
  const isOpen = dd.classList.toggle('open');
  if (isOpen) {
    apiJson('/notifications/mark-read', 'POST').then(() => {
      _notifCount = 0;
      updateNotifBadge(0);
    });
  }
}

document.addEventListener('click', e => {
  const wrap = document.getElementById('notif-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('notif-dropdown')?.classList.remove('open');
  }
});

function updateNotifBadge(count) {
  const badge = document.getElementById('notif-badge');
  if (!badge) return;
  if (count > 0) { badge.textContent = count > 99 ? '99+' : count; badge.style.display = 'flex'; }
  else { badge.style.display = 'none'; }
}

function renderNotifList(items) {
  const el = document.getElementById('notif-list');
  if (!el) return;
  if (!items || !items.length) {
    el.innerHTML = '<div class="notif-empty">🔔 Tidak ada notifikasi</div>';
    return;
  }
  const typeIcon = {ticket_approved:'✅',ticket_rejected:'❌',ticket_closed:'🔒',ticket_assigned:'📋',comment_added:'💬'};
  el.innerHTML = items.map(n => `
    <div class="notif-item ${n.read ? '' : 'unread'}" onclick="onNotifClick('${n.ticket_id||''}')">
      <div class="notif-item-title">${typeIcon[n.type]||'🔔'} ${n.title}</div>
      <div class="notif-item-msg">${n.message}</div>
      <div class="notif-item-time">${n.time}</div>
    </div>`).join('');
}

function onNotifClick(ticketId) {
  document.getElementById('notif-dropdown')?.classList.remove('open');
  if (ticketId) openDetail(ticketId);
}

function markAllNotifRead() {
  apiJson('/notifications/mark-read', 'POST').then(() => {
    _notifCount = 0;
    updateNotifBadge(0);
    pollNotifications();
  });
}

function pollNotifications() {
  fetch('/notifications', {headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}})
    .then(r => r.json())
    .then(data => {
      const count = data.unread_count || 0;
      if (_notifCount >= 0 && count > _notifCount) {
        showToast(`🔔 ${count - _notifCount} notifikasi baru`);
      }
      _notifCount = count;
      updateNotifBadge(count);
      renderNotifList(data.notifications || []);
    })
    .catch(() => {});
}

// Poll pertama 1 detik setelah init, lalu setiap 30 detik
setTimeout(() => { pollNotifications(); setInterval(pollNotifications, 30000); }, 1000);

/* ═══ INIT ═══ */
applyAppConfig();
renderAll();

// Update admin section visibility
document.getElementById('nav-admin-section').style.display=curUser.type==='it'?'':'none';
document.getElementById('nav-approval').style.display=curUser.type!=='user'?'':'none';

// Color picker listener
document.addEventListener('DOMContentLoaded',()=>{
  const cp=document.getElementById('cfg-bg-color');
  if(cp) cp.addEventListener('input',()=>{document.getElementById('cfg-bg-color-hex').value=cp.value;APP_CONFIG.bgColor=cp.value;updateBgPreview();});
  const gi=document.getElementById('cfg-bg-gradient');
  if(gi) gi.addEventListener('input',updateBgPreview);
  const img=document.getElementById('cfg-bg-image');
  if(img) img.addEventListener('input',updateBgPreview);
});
</script>
</body>
</html>
