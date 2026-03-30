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
    const warnCls=sla.cls==='sla-over'?'crit':sla.cls==='sla-warn'?'warn':'ok';
    const warnIcon=sla.cls==='sla-over'?'⚠️':sla.cls==='sla-warn'?'⏰':'✓';
    return `<div class="sla-row-item" style="cursor:pointer" onclick="openDetail('${t.id}')">
      <div class="sla-row-top">
        <span class="sla-row-title" title="${t.title}">${t.id} · ${t.title}</span>
        <span class="sla-row-label ${sla.cls||''}">${sla.label||'—'}</span>
      </div>
      <div class="sla-mini-bar"><div class="sla-mini-fill" style="width:${pct}%;background:${bar}"></div></div>
      <div class="ud-sla-meta">
        <span class="ud-sla-pct">${pct}% digunakan</span>
        <span class="ud-sla-warn ${warnCls}">${warnIcon} ${sla.label||'—'}</span>
      </div>
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
  document.getElementById('ovv-pending').textContent=pending.length;
  // Trend badge — tiket baru / selesai dalam 7 hari terakhir
  (function(){
    const weekAgo=Date.now()-7*24*60*60*1000;
    const pd=s=>{if(!s)return 0;const d=new Date(s);return isNaN(d)?0:d.getTime();};
    const setTrend=(id,val,suffix)=>{
      const el=document.getElementById(id);if(!el)return;
      if(val>0){el.textContent='+'+val+' '+suffix;el.className='ud-stat-trend';}
      else{el.textContent='— '+suffix;el.className='ud-stat-trend neutral';}
      el.style.display='';
    };
    setTrend('ovv-trend-total',mine.filter(t=>pd(t.created_at)>weekAgo).length,'7 hari ini');
    setTrend('ovv-trend-prog',active.filter(t=>pd(t.created_at)>weekAgo).length,'7 hari ini');
    setTrend('ovv-trend-done',done.filter(t=>pd(t.closed_at)>weekAgo).length,'7 hari ini');
    setTrend('ovv-trend-pending',pending.filter(t=>pd(t.created_at)>weekAgo).length,'7 hari ini');
  })();
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
  // Quick summary di kolom kanan
  const qEl=id=>document.getElementById(id);
  if(qEl('qs-active'))  qEl('qs-active').textContent=active.length;
  if(qEl('qs-pending')) qEl('qs-pending').textContent=pending.length;
  if(qEl('qs-done'))    qEl('qs-done').textContent=done.length;
  // Render SLA panel — selalu tampil di kolom kanan (tidak hide)
  const slaList=document.getElementById('ud-sla-list');
  const slaCount=document.getElementById('ud-sla-count');
  if(slaList){
    if(!active.length){
      if(slaCount) slaCount.textContent='';
      slaList.innerHTML=`<div class="ud-sla-empty"><div class="ud-sla-empty-icon">📭</div><div class="ud-sla-empty-title">Tidak ada tiket aktif</div><div class="ud-sla-empty-sub">SLA akan tampil saat ada tiket yang sedang berjalan</div></div>`;
    } else {
      if(slaCount) slaCount.textContent=active.length+' tiket';
      slaList.innerHTML=active.sort((a,b)=>(b.sla?.pct||0)-(a.sla?.pct||0)).slice(0,8).map(t=>{
        const sla=t.sla||{};
        const warnCls=sla.cls==='sla-over'?'crit':sla.cls==='sla-warn'?'warn':'ok';
        const warnIcon=sla.cls==='sla-over'?'⚠️':sla.cls==='sla-warn'?'⏰':'✓';
        return `<div class="sla-row-item" onclick="openDetail('${t.id}')">
          <div class="sla-row-top">
            <span class="sla-row-title" title="${t.title}">${t.id} · ${t.title}</span>
            <span class="sla-row-label ${sla.cls||''}">${sla.label||'—'}</span>
          </div>
          <div class="sla-mini-bar"><div class="sla-mini-fill" style="width:${sla.pct||0}%;background:${sla.bar||'var(--text3)'}"></div></div>
          <div class="ud-sla-meta">
            <span class="ud-sla-pct">${sla.pct||0}% digunakan</span>
            <span class="ud-sla-warn ${warnCls}">${warnIcon} ${sla.label||'—'}</span>
          </div>
        </div>`;
      }).join('');
    }
  }
  const el=document.getElementById('ud-ticket-list');
  if(!filtered.length){el.innerHTML=`<div class="user-empty"><div class="user-empty-illus">📭</div><div class="user-empty-title">Tidak ada tiket</div><div class="user-empty-sub">Belum ada tiket di kategori ini.</div>${udTab2==='all'?`<button class="btn btn-primary" onclick="openCreate()" style="margin:0 auto">+ Buat Tiket Pertama</button>`:''}</div>`;return;}
  // Label pendek khusus user dashboard (compact)
  const udLabel=t=>{
    if(t.approval==='pending') return 'Menunggu';
    if(t.closed_at) return 'Selesai';
    if(t.freeze_status==='active') return 'Freeze';
    if(t.freeze_status==='pending_approval') return 'Req.Freeze';
    return 'Berjalan';
  };
  el.innerHTML=filtered.map(t=>{
    const pct=t.task_total>0?Math.round((t.task_done/t.task_total)*100):0;
    const barColor=t.closed_at?'var(--green)':pct>=90?'var(--red)':pct>=75?'var(--yellow)':'linear-gradient(90deg,var(--accent),#818cf8)';
    const typeLbl=t.type==='incident'?'🚨 Insiden':t.type==='newproject'?'🆕 Proyek':' 📬 Request';
    return `<div class="user-ticket-row" onclick="openDetail('${t.id}')">
      <div class="utf-id">${t.id}</div>
      <div class="utf-body">
        <div class="utf-title">${t.title}${t.it_comment_count>0?` <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;background:var(--accent-glow);color:var(--accent);border:1px solid rgba(8,145,178,.3);border-radius:20px;padding:1px 6px;vertical-align:middle">💬 ${t.it_comment_count}</span>`:''}</div>
        <div class="utf-compact-meta">
          <span class="utf-type-badge">${typeLbl}</span>
          <div class="ud-progress-bar"><div class="ud-progress-fill" style="width:${pct}%;background:${barColor}"></div></div>
          <span class="ud-progress-pct">${pct}%</span>
          ${t.task_total>0?`<span class="utf-task-count">${t.task_done}/${t.task_total}</span>`:''}
        </div>
      </div>
      <div class="utf-status"><span class="status-chip ${stateClass(t)}"><span class="dot"></span>${udLabel(t)}</span></div>
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
  const mk=arr=>arr.map(t=>{
    const pct=t.progress||0;
    const sla=t.sla||{};
    const barColor=t.closed_at?'var(--green)':t.freeze_status==='active'?'var(--purple)':pct>=90?'var(--red)':pct>=75?'var(--yellow)':'linear-gradient(90deg,var(--accent),#818cf8)';
    const slaIcon=sla.cls==='sla-over'?'⚠️':sla.cls==='sla-warn'?'⏰':'⏱';
    const stateColor=t.freeze_status==='active'?'var(--purple)':t.freeze_status==='pending_approval'?'var(--orange)':t.closed_at?'var(--green)':'var(--text3)';
    return `<div class="ticket-card ${t.approval==='pending'?'pend':t.freeze_status==='active'?'frozen-card':''}" onclick="openDetail('${t.id}')" style="${t.freeze_status==='active'?'border-color:rgba(124,58,237,0.4);':''}">
      ${t.approval==='pending'?'<div class="pend-badge">PENDING</div>':''}
      ${t.freeze_status==='active'?'<div class="pend-badge" style="background:var(--purple);color:white">🧊 FREEZE</div>':''}
      ${t.freeze_status==='pending_approval'?'<div class="pend-badge" style="background:var(--orange);color:white">⏸ REQ. FREEZE</div>':''}
      <div class="tc-header-row">
        <span class="ticket-id">${t.id}</span>
        <span class="tag ${t.type}">${t.type==='incident'?'🚨 Insiden':t.type==='newproject'?'🆕 Proyek Baru':'📬 Permintaan'}</span>
        ${t.category?`<span class="tag" style="background:var(--surface3);color:var(--text2)">${t.category}</span>`:''}
        ${t.it_comment_count>0?`<span class="tc-comment-badge">💬 ${t.it_comment_count}</span>`:''}
      </div>
      <div class="ticket-title">${t.title}</div>
      <div class="tc-progress-row">
        <div class="tc-progress-track"><div class="tc-progress-bar" style="width:${pct}%;background:${barColor}"></div></div>
        <span class="tc-pct-label">${pct}%</span>
      </div>
      <div class="tc-info-row">
        <span class="tc-task-info">${t.task_total>0?t.task_done+'/'+t.task_total+' tugas':'Belum ada tugas'}</span>
        <span class="tc-state-lbl" style="color:${stateColor}">${stateLabel(t)}</span>
      </div>
      <div class="ticket-footer">
        <div class="ticket-assignee"><div class="mini-avatar" style="background:${t.assignee_color||ac(t.assignee)};width:18px;height:18px;font-size:8px">${t.assignee_initials||ini(t.assignee)}</div><span>${t.assignee||'Belum ditugaskan'}</span></div>
        <span class="tc-sla-chip ${sla.cls||''}">${slaIcon} ${sla.label||'—'}</span>
      </div>
      ${t.creator?`<div class="tc-creator">👤 ${t.creator}</div>`:''}
    </div>`;
  }).join('');
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
  const rule=AUTO_ASSIGN.find(r=>r.kategori===cat&&r.client===client);
  const name=rule?.assignee||'Belum ditentukan (akan di-assign manual)';
  document.getElementById('assign-preview-name').textContent=name;
  document.getElementById('assign-preview').style.display=rule?'':'none';
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
