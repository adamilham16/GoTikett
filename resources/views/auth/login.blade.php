<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GoTiket — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#e8f4f8;--surface:#ffffff;--surface2:#f0f9fc;--surface3:#e0f2f7;
  --border:#b2dce8;--border2:#80c4d8;--text:#0d3349;--text2:#2e6e8a;--text3:#6aafc8;
  --accent:#0891b2;--accent2:#06b6d4;--accent-glow:rgba(8,145,178,0.15);
  --green:#059669;--red:#dc2626;--red-bg:rgba(220,38,38,0.1);--radius:10px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Space Grotesk',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%);}
.login-card{background:rgba(255,255,255,0.95);border:1px solid var(--border);border-radius:20px;padding:36px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(8,145,178,0.2);}
.login-logo{text-align:center;margin-bottom:28px;}
.login-logo-icon{width:54px;height:54px;background:linear-gradient(135deg,var(--accent),#818cf8);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 10px;}
.login-title{font-size:20px;font-weight:700;}
.login-sub{font-size:11px;color:var(--text3);margin-top:2px;text-transform:uppercase;letter-spacing:1px;}
.login-group{margin-bottom:15px;}
.login-label{font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:5px;}
.login-input-wrap{position:relative;}
.login-input{width:100%;background:#f0f9fc;border:1px solid var(--border);border-radius:8px;padding:10px 40px 10px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s;}
.login-input:focus{border-color:var(--accent);}
.login-input.err-field{border-color:var(--red);}
.login-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;font-size:15px;}
.login-btn{width:100%;padding:11px;background:var(--accent);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:6px;transition:all .15s;}
.login-btn:hover{background:var(--accent2);}
.login-error{background:var(--red-bg);border:1px solid rgba(245,101,101,.3);border-radius:8px;padding:9px 12px;font-size:12px;color:var(--red);margin-bottom:14px;}
.login-hint{margin-top:16px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 12px;}
.login-hint-title{font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:7px;}
.login-hint-row{display:flex;justify-content:space-between;font-size:11px;color:var(--text2);padding:2px 0;}
.login-hint-pw{font-family:'JetBrains Mono',monospace;color:var(--text3);}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <div class="login-logo-icon">🗂️</div>
    <div class="login-title">GoTiket</div>
    <div class="login-sub">Organize Work, Empower Teams</div>
  </div>

  @if($errors->has('auth'))
    <div class="login-error">{{ $errors->first('auth') }}</div>
  @endif

  <form method="POST" action="{{ route('login.post') }}">
    @csrf
    <div class="login-group">
      <label class="login-label">Username</label>
      <input type="text" name="username" class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
        placeholder="Masukkan username..." value="{{ old('username') }}" autocomplete="username" required>
    </div>
    <div class="login-group">
      <label class="login-label">Password</label>
      <div class="login-input-wrap">
        <input type="password" name="password" id="login-password"
          class="login-input {{ $errors->has('auth') ? 'err-field' : '' }}"
          placeholder="Masukkan password..." autocomplete="current-password" required>
        <button type="button" class="login-eye" onclick="togglePw()">👁</button>
      </div>
    </div>
    <button type="submit" class="login-btn">🔐 Masuk</button>
  </form>

  <div class="login-hint">
    <div class="login-hint-title">Demo Accounts</div>
    @foreach([
      ['adam','adam123','IT SIM'],['puji','puji123','IT SIM'],['rizky','rizky123','IT SIM'],
      ['saddam','saddam123','IT SIM'],['icha','icha123','User'],['mutia','mutia123','User'],
      ['jovi','jovi123','Manager']
    ] as $u)
    <div class="login-hint-row">
      <span>{{ $u[2] }}: <strong>{{ $u[0] }}</strong></span>
      <span class="login-hint-pw">{{ $u[1] }}</span>
    </div>
    @endforeach
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
