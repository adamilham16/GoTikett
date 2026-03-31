<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GoTiket — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}

:root{
  --indigo:#6366F1;
  --violet:#7C3AED;
  --pink:#EC4899;
  --orange:#F97316;
  --green:#10B981;
  --red:#EF4444;
  --text:#1E1B4B;
  --text2:#4338CA;
  --text3:#818CF8;
  --border:rgba(99,102,241,0.2);
  --radius:14px;
}

body{
  font-family:'Space Grotesk',sans-serif;
  min-height:100vh;
  display:flex;
  overflow:hidden;
}

/* ══════════════════════════════════════
   LEFT PANEL
══════════════════════════════════════ */
.split-left{
  flex:0 0 48%;
  position:relative;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:60px 52px;
  overflow:hidden;
  background:#1E1B4B;
}

/* Animated gradient mesh */
.split-left::before{
  content:'';
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 20%, rgba(99,102,241,0.6) 0%, transparent 60%),
    radial-gradient(ellipse 60% 70% at 80% 80%, rgba(124,58,237,0.5) 0%, transparent 55%),
    radial-gradient(ellipse 50% 50% at 50% 50%, rgba(236,72,153,0.25) 0%, transparent 60%);
  animation:meshMove 8s ease-in-out infinite alternate;
}
@keyframes meshMove{
  0%  {opacity:.8; transform:scale(1)    rotate(0deg);}
  100%{opacity:1;  transform:scale(1.05) rotate(2deg);}
}

/* Floating orbs */
.orb{
  position:absolute;
  border-radius:50%;
  filter:blur(60px);
  animation:floatOrb linear infinite;
  pointer-events:none;
}
.orb-1{width:300px;height:300px;background:rgba(99,102,241,0.35);top:-80px;right:-60px;animation-duration:12s;}
.orb-2{width:200px;height:200px;background:rgba(236,72,153,0.3);bottom:-40px;left:-40px;animation-duration:9s;animation-delay:-3s;}
.orb-3{width:150px;height:150px;background:rgba(124,58,237,0.4);top:40%;left:10%;animation-duration:14s;animation-delay:-6s;}

@keyframes floatOrb{
  0%  {transform:translateY(0px)   translateX(0px);}
  33% {transform:translateY(-30px) translateX(15px);}
  66% {transform:translateY(15px)  translateX(-20px);}
  100%{transform:translateY(0px)   translateX(0px);}
}

/* Grid pattern overlay */
.left-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size:40px 40px;
}

.left-content{
  position:relative;z-index:2;
  display:flex;flex-direction:column;
  align-items:center;
  width:100%;max-width:380px;
}

.brand-logo{
  width:340px;height:auto;
  object-fit:contain;
  margin-bottom:10px;
  filter:drop-shadow(0 8px 32px rgba(99,102,241,0.5)) brightness(1.1);
}

.brand-name{
  font-size:36px;font-weight:700;
  color:#fff;
  letter-spacing:-1px;
  margin-bottom:6px;
  background:linear-gradient(135deg,#fff 30%,#C7D2FE);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.brand-tagline{
  font-size:12px;color:rgba(255,255,255,0.55);
  letter-spacing:2px;text-transform:uppercase;
  margin-bottom:44px;
}

/* Feature highlights */
.features{
  width:100%;
  display:flex;flex-direction:column;gap:14px;
  margin-bottom:44px;
}

.feat-item{
  display:flex;align-items:center;gap:14px;
  background:rgba(255,255,255,0.07);
  border:1px solid rgba(255,255,255,0.1);
  border-radius:12px;
  padding:12px 16px;
  backdrop-filter:blur(10px);
  transition:background .2s;
}
.feat-item:hover{background:rgba(255,255,255,0.12);}

.feat-icon{
  width:38px;height:38px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;font-size:18px;
}
.feat-icon.purple{background:rgba(124,58,237,0.4);}
.feat-icon.pink  {background:rgba(236,72,153,0.35);}
.feat-icon.orange{background:rgba(249,115,22,0.4);}

.feat-text{flex:1;}
.feat-title{font-size:13px;font-weight:600;color:#fff;margin-bottom:2px;}
.feat-desc {font-size:11px;color:rgba(255,255,255,0.5);line-height:1.4;}

/* Stats row */
.left-stats{
  width:100%;
  display:grid;grid-template-columns:repeat(3,1fr);gap:10px;
}

.lstat{
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.1);
  border-radius:10px;
  padding:10px 12px;
  text-align:center;
}
.lstat-num{
  font-size:20px;font-weight:700;
  font-family:'JetBrains Mono',monospace;
  background:linear-gradient(135deg,#A5B4FC,#E879F9);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.lstat-label{font-size:10px;color:rgba(255,255,255,0.45);margin-top:3px;letter-spacing:.5px;}

.powered-by{
  position:absolute;bottom:20px;
  font-size:10px;color:rgba(255,255,255,0.25);
  letter-spacing:1px;text-transform:uppercase;
  z-index:2;
}

/* ══════════════════════════════════════
   RIGHT PANEL
══════════════════════════════════════ */
.split-right{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:40px 32px;
  background:linear-gradient(135deg,#EEF2FF 0%,#F5F3FF 50%,#FDF4FF 100%);
  position:relative;
  overflow:hidden;
}

/* Soft background blobs */
.split-right::before{
  content:'';
  position:absolute;
  width:500px;height:500px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(99,102,241,0.08) 0%,transparent 70%);
  top:-100px;right:-100px;
  pointer-events:none;
}
.split-right::after{
  content:'';
  position:absolute;
  width:400px;height:400px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(236,72,153,0.06) 0%,transparent 70%);
  bottom:-80px;left:-80px;
  pointer-events:none;
}

.login-box{
  position:relative;z-index:1;
  width:100%;max-width:420px;
  background:rgba(255,255,255,0.85);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid rgba(99,102,241,0.15);
  border-radius:24px;
  padding:40px 36px;
  box-shadow:
    0 4px 6px rgba(99,102,241,0.04),
    0 10px 40px rgba(99,102,241,0.12),
    0 0 0 1px rgba(255,255,255,0.8) inset;
}

.login-header{margin-bottom:32px;}

.login-avatar{
  width:52px;height:52px;border-radius:16px;
  background:linear-gradient(135deg,var(--indigo),var(--violet));
  display:flex;align-items:center;justify-content:center;
  margin-bottom:16px;
  box-shadow:0 8px 20px rgba(99,102,241,0.35);
}

.login-header-title{
  font-size:26px;font-weight:700;
  color:var(--text);
  letter-spacing:-0.5px;
  margin-bottom:6px;
}

.login-header-sub{
  font-size:13px;color:#6B7280;line-height:1.5;
}

/* Form groups */
.login-group{margin-bottom:18px;}

.login-label{
  font-size:11.5px;font-weight:600;
  color:#4B5563;
  text-transform:uppercase;letter-spacing:.8px;
  display:block;margin-bottom:8px;
}

.login-input-wrap{position:relative;}

.input-icon{
  position:absolute;left:14px;top:50%;
  transform:translateY(-50%);
  color:#9CA3AF;
  display:flex;align-items:center;
  pointer-events:none;
}

.login-input{
  width:100%;
  background:#F9FAFB;
  border:1.5px solid #E5E7EB;
  border-radius:12px;
  padding:13px 44px 13px 44px;
  color:var(--text);
  font-size:14px;
  font-family:inherit;
  outline:none;
  transition:all .2s;
}

.login-input:focus{
  border-color:var(--indigo);
  background:#fff;
  box-shadow:0 0 0 4px rgba(99,102,241,0.1);
}

.login-input.err-field{
  border-color:var(--red);
  background:#FFF5F5;
  box-shadow:0 0 0 4px rgba(239,68,68,0.08);
}

.login-eye{
  position:absolute;right:14px;top:50%;
  transform:translateY(-50%);
  background:none;border:none;
  color:#9CA3AF;cursor:pointer;
  display:flex;align-items:center;
  padding:4px;border-radius:6px;
  transition:color .15s,background .15s;
}
.login-eye:hover{color:var(--indigo);background:rgba(99,102,241,0.08);}

/* Button */
.login-btn{
  width:100%;
  padding:14px;
  background:linear-gradient(135deg,var(--indigo) 0%,var(--violet) 100%);
  color:white;
  border:none;
  border-radius:12px;
  font-size:14px;font-weight:700;
  cursor:pointer;
  font-family:inherit;
  margin-top:6px;
  transition:all .2s;
  box-shadow:0 4px 15px rgba(99,102,241,0.4);
  position:relative;overflow:hidden;
  letter-spacing:.3px;
}

.login-btn::before{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(135deg,var(--violet) 0%,var(--pink) 100%);
  opacity:0;transition:opacity .3s;
}
.login-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 25px rgba(99,102,241,0.5);
}
.login-btn:hover::before{opacity:1;}
.login-btn:active{transform:translateY(0);}
.login-btn-text{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:8px;}

/* Loading state */
.login-btn.loading .login-btn-text{opacity:0;}
.login-btn.loading::after{
  content:'';
  position:absolute;top:50%;left:50%;
  width:20px;height:20px;
  margin:-10px 0 0 -10px;
  border:2px solid rgba(255,255,255,0.3);
  border-top-color:#fff;
  border-radius:50%;
  animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}

/* Error */
.login-error{
  background:rgba(239,68,68,0.06);
  border:1px solid rgba(239,68,68,0.2);
  border-radius:10px;
  padding:11px 14px;
  font-size:12.5px;color:var(--red);
  margin-bottom:20px;
  display:flex;align-items:center;gap:9px;
  animation:slideIn .2s ease;
}
@keyframes slideIn{from{opacity:0;transform:translateY(-6px);}to{opacity:1;transform:none;}}

/* Forgot password */
.forgot-wrap{
  text-align:right;margin-top:-8px;margin-bottom:20px;
}
.forgot-link{
  font-size:12px;color:var(--indigo);
  text-decoration:none;font-weight:500;
  transition:color .15s;
}
.forgot-link:hover{color:var(--violet);text-decoration:underline;}

/* Divider */
.login-divider{
  height:1px;background:linear-gradient(90deg,transparent,#E5E7EB,transparent);
  margin:24px 0 16px;
}

/* Footer */
.login-footer{
  text-align:center;
  font-size:11px;color:#9CA3AF;
}

/* Success */
.login-success{
  background:rgba(16,185,129,0.07);
  border:1px solid rgba(16,185,129,0.25);
  border-radius:10px;padding:11px 14px;
  font-size:12.5px;color:var(--green);
  margin-top:14px;
  display:flex;align-items:center;gap:9px;
  animation:slideIn .2s ease;
}

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media(max-width:900px){
  .split-left{flex:0 0 42%;}
}

@media(max-width:768px){
  body{flex-direction:column;overflow:auto;}

  .split-left{
    flex:none;
    padding:40px 28px 36px;
    min-height:auto;
  }
  .brand-logo{width:240px;}
  .brand-name{font-size:28px;}
  .brand-tagline{margin-bottom:28px;font-size:11px;}
  .features{gap:10px;margin-bottom:28px;}
  .feat-item{padding:10px 14px;}
  .left-stats{gap:8px;}
  .lstat-num{font-size:17px;}

  .split-right{padding:32px 20px;min-height:auto;}
  .login-box{padding:32px 24px;border-radius:20px;}
}

@media(max-width:420px){
  .split-left{padding:28px 18px;}
  .features{display:none;}
  .login-box{padding:28px 20px;}
}
</style>
</head>
<body>

<!-- LEFT: Branding -->
<div class="split-left">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div class="left-grid"></div>

  <div class="left-content">
    <img src="/logo-simgroup-transp.png" alt="SIMGROUP" class="brand-logo">
    <div class="brand-name">GoTiket</div>
    <div class="brand-tagline">Organize Work · Empower Teams</div>

    <div class="features">
      <div class="feat-item">
        <div class="feat-icon purple">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div class="feat-text">
          <div class="feat-title">Manajemen Tiket Terpusat</div>
          <div class="feat-desc">Buat, pantau & selesaikan permintaan IT dalam satu tempat</div>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon pink">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="feat-text">
          <div class="feat-title">SLA Monitoring Real-time</div>
          <div class="feat-desc">Pantau tenggat waktu dan progres penyelesaian tiket</div>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon orange">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
        </div>
        <div class="feat-text">
          <div class="feat-title">Laporan & Analitik</div>
          <div class="feat-desc">Ekspor data dan pantau performa tim IT Anda</div>
        </div>
      </div>
    </div>

  </div>

  <div class="powered-by">Powered by SIMGROUP · LOB CCCM</div>
</div>

<!-- RIGHT: Form -->
<div class="split-right">
  <div class="login-box">
    <div class="login-header">
      <div class="login-avatar">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div class="login-header-title">Selamat Datang 👋</div>
      <div class="login-header-sub">Masuk ke akun GoTiket Anda untuk melanjutkan</div>
    </div>

    @if($errors->has('auth'))
      <div class="login-error">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ $errors->first('auth') }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" id="login-form">
      @csrf
      <div class="login-group">
        <label class="login-label">Username</label>
        <div class="login-input-wrap">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input type="text" name="username"
            class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
            placeholder="Masukkan username..."
            value="{{ old('username') }}"
            autocomplete="username" required>
        </div>
      </div>

      <div class="login-group">
        <label class="login-label">Password</label>
        <div class="login-input-wrap">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" name="password" id="login-password"
            class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
            placeholder="Masukkan password..."
            autocomplete="current-password" required>
          <button type="button" class="login-eye" id="eye-btn" onclick="togglePw()" title="Tampilkan/sembunyikan password">
            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <div class="forgot-wrap">
        <a href="{{ route('password.forgot') }}" class="forgot-link">Lupa password?</a>
      </div>

      <button type="submit" class="login-btn" id="login-btn">
        <span class="login-btn-text">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Masuk ke GoTiket
        </span>
      </button>
    </form>

    @if(session('status'))
      <div class="login-success">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
      </div>
    @endif

    <div class="login-divider"></div>
    <div class="login-footer">
      &copy; {{ date('Y') }} LOB CCCM SIMGROUP &mdash; GoTiket v1.0
    </div>
  </div>
</div>

<script>
function togglePw(){
  const inp = document.getElementById('login-password');
  const icon = document.getElementById('eye-icon');
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  icon.innerHTML = show
    ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}

document.getElementById('login-form').addEventListener('submit', function(){
  const btn = document.getElementById('login-btn');
  btn.classList.add('loading');
  btn.disabled = true;
});
</script>
</body>
</html>
