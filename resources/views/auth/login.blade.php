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
  --blue:#1B9EC9;
  --blue-dark:#1480a8;
  --blue-deeper:#0e5f7c;
  --sand:#C97B4B;
  --sand-light:#d9956a;
  --white:#ffffff;
  --text:#0d3349;
  --text2:#2e6e8a;
  --text3:#6aafc8;
  --border:#cce9f3;
  --red:#dc2626;
  --red-bg:rgba(220,38,38,0.08);
  --radius:12px;
}

body{
  font-family:'Space Grotesk',sans-serif;
  min-height:100vh;
  display:flex;
  background:#f0f9fc;
}

/* ── LEFT PANEL ── */
.split-left{
  flex:0 0 48%;
  background:linear-gradient(160deg, var(--blue-deeper) 0%, var(--blue-dark) 45%, var(--blue) 100%);
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:60px 48px;
  position:relative;
  overflow:hidden;
}

/* decorative circles */
.split-left::before{
  content:'';
  position:absolute;
  width:400px;height:400px;
  border-radius:50%;
  border:1px solid rgba(255,255,255,0.07);
  top:-80px;right:-120px;
}
.split-left::after{
  content:'';
  position:absolute;
  width:280px;height:280px;
  border-radius:50%;
  border:1px solid rgba(255,255,255,0.06);
  bottom:-60px;left:-60px;
}

.brand-logo{
  width:400px;
  height:auto;
  object-fit:contain;
  margin-bottom:12px;
  filter:drop-shadow(0 8px 24px rgba(0,0,0,0.25));
  position:relative;z-index:1;
}

.brand-name{
  font-size:34px;
  font-weight:700;
  color:#ffffff;
  letter-spacing:-0.5px;
  margin-bottom:8px;
  position:relative;z-index:1;
}

.brand-tagline{
  font-size:13px;
  color:rgba(255,255,255,0.65);
  letter-spacing:1.2px;
  text-transform:uppercase;
  margin-bottom:36px;
  position:relative;z-index:1;
}


.powered-by{
  margin-top:44px;
  font-size:10px;
  color:rgba(255,255,255,0.35);
  letter-spacing:.8px;
  text-transform:uppercase;
  position:relative;z-index:1;
}

/* ── RIGHT PANEL ── */
.split-right{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:40px 32px;
  background:#f0f9fc;
}

.login-box{
  width:100%;
  max-width:400px;
}

.login-header{
  margin-bottom:36px;
}

.login-header-title{
  font-size:24px;
  font-weight:700;
  color:var(--text);
  margin-bottom:6px;
}

.login-header-sub{
  font-size:13px;
  color:var(--text2);
}

.login-group{
  margin-bottom:18px;
}

.login-label{
  font-size:11px;
  font-weight:600;
  color:var(--text2);
  text-transform:uppercase;
  letter-spacing:.8px;
  display:block;
  margin-bottom:7px;
}

.login-input-wrap{position:relative;}

.login-input{
  width:100%;
  background:#ffffff;
  border:1.5px solid var(--border);
  border-radius:10px;
  padding:12px 42px 12px 16px;
  color:var(--text);
  font-size:14px;
  font-family:inherit;
  outline:none;
  transition:border-color .2s,box-shadow .2s;
}

.login-input:focus{
  border-color:var(--blue);
  box-shadow:0 0 0 3px rgba(27,158,201,0.12);
}

.login-input.err-field{
  border-color:var(--red);
  box-shadow:0 0 0 3px rgba(220,38,38,0.08);
}

.login-eye{
  position:absolute;right:13px;top:50%;
  transform:translateY(-50%);
  background:none;border:none;
  color:var(--text3);cursor:pointer;font-size:16px;
  line-height:1;padding:2px;
}

.login-btn{
  width:100%;
  padding:13px;
  background:var(--blue);
  color:white;
  border:none;
  border-radius:10px;
  font-size:14px;
  font-weight:600;
  cursor:pointer;
  font-family:inherit;
  margin-top:8px;
  transition:background .15s,transform .1s,box-shadow .15s;
  box-shadow:0 4px 14px rgba(27,158,201,0.35);
}

.login-btn:hover{
  background:var(--blue-dark);
  box-shadow:0 6px 18px rgba(27,158,201,0.45);
}

.login-btn:active{
  transform:translateY(1px);
  box-shadow:0 2px 8px rgba(27,158,201,0.3);
}

.login-error{
  background:var(--red-bg);
  border:1px solid rgba(220,38,38,0.25);
  border-radius:9px;
  padding:10px 14px;
  font-size:12px;
  color:var(--red);
  margin-bottom:18px;
  display:flex;align-items:center;gap:8px;
}

.login-footer{
  margin-top:32px;
  padding-top:20px;
  border-top:1px solid var(--border);
  text-align:center;
  font-size:11px;
  color:var(--text3);
}

/* ── RESPONSIVE ── */
@media(max-width:768px){
  body{flex-direction:column;}

  .split-left{
    flex:none;
    padding:36px 24px 32px;
  }

  .brand-logo{width:260px;margin-bottom:12px;}
  .brand-name{font-size:26px;}
  .brand-tagline{margin-bottom:28px;}

  .split-right{padding:32px 20px;}
}

@media(max-width:420px){
  .split-left{padding:28px 20px;}
}
</style>
</head>
<body>

<!-- LEFT: branding -->
<div class="split-left">
  <img src="/logo-simgroup-transp.png" alt="SIMGROUP" class="brand-logo">
  <div class="brand-name">GoTiket</div>
  <div class="brand-tagline">Organize Work, Empower Teams</div>

  <div class="powered-by">Powered by SIMGROUP</div>
</div>

<!-- RIGHT: form -->
<div class="split-right">
  <div class="login-box">
    <div class="login-header">
      <div class="login-header-title">Selamat Datang 👋</div>
      <div class="login-header-sub">Masuk ke akun GoTiket Anda</div>
    </div>

    @if($errors->has('auth'))
      <div class="login-error">
        <span>⚠️</span>
        <span>{{ $errors->first('auth') }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
      @csrf
      <div class="login-group">
        <label class="login-label">Username</label>
        <input type="text" name="username"
          class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
          placeholder="Masukkan username..."
          value="{{ old('username') }}"
          autocomplete="username" required>
      </div>
      <div class="login-group">
        <label class="login-label">Password</label>
        <div class="login-input-wrap">
          <input type="password" name="password" id="login-password"
            class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
            placeholder="Masukkan password..."
            autocomplete="current-password" required>
          <button type="button" class="login-eye" onclick="togglePw()">👁</button>
        </div>
      </div>
      <button type="submit" class="login-btn">Masuk →</button>
    </form>

    <div style="text-align:center;margin-top:16px;">
      <a href="{{ route('password.forgot') }}" style="font-size:12px;color:var(--text2);text-decoration:none;">
        Lupa password?
      </a>
    </div>

    @if(session('status'))
      <div style="background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.25);border-radius:9px;padding:10px 14px;font-size:12px;color:#059669;margin-top:14px;">
        ✅ {{ session('status') }}
      </div>
    @endif

    <div class="login-footer">
      &copy; {{ date('Y') }} LOB CCCM SIMGROUP &mdash; GoTiket v1.0
    </div>
  </div>
</div>

<script>
function togglePw(){
  const inp = document.getElementById('login-password');
  const btn = inp.nextElementSibling;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}
</script>
</body>
</html>
