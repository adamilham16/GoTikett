<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password — GoTiket</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --blue:#1B9EC9;--blue-dark:#1480a8;--blue-deeper:#0e5f7c;
  --white:#ffffff;--text:#0d3349;--text2:#2e6e8a;--text3:#6aafc8;
  --border:#cce9f3;--red:#dc2626;--red-bg:rgba(220,38,38,0.08);
  --green:#059669;--green-bg:rgba(5,150,105,0.08);--radius:12px;
}
body{font-family:'Space Grotesk',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f0f9fc;}
.box{width:100%;max-width:420px;padding:24px;}
.card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:36px 32px;box-shadow:0 4px 24px rgba(27,158,201,0.10);}
.icon-wrap{width:56px;height:56px;background:linear-gradient(135deg,var(--blue-deeper),var(--blue));border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:22px;}
h1{font-size:22px;font-weight:700;color:var(--text);margin-bottom:6px;}
.sub{font-size:13px;color:var(--text2);margin-bottom:28px;line-height:1.5;}
.form-group{margin-bottom:18px;}
label{font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:7px;}
input{width:100%;background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:12px 16px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;}
input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(27,158,201,0.12);}
.btn{width:100%;padding:13px;background:var(--blue);color:white;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:4px;transition:background .15s;}
.btn:hover{background:var(--blue-dark);}
.btn:disabled{opacity:0.6;cursor:not-allowed;}
.error-box{background:var(--red-bg);border:1px solid rgba(220,38,38,0.25);border-radius:9px;padding:10px 14px;font-size:12px;color:var(--red);margin-bottom:18px;display:flex;align-items:flex-start;gap:8px;}
.success-box{background:var(--green-bg);border:1px solid rgba(5,150,105,0.25);border-radius:9px;padding:12px 14px;font-size:13px;color:var(--green);margin-bottom:18px;line-height:1.5;}
.back-link{display:block;text-align:center;margin-top:20px;font-size:13px;color:var(--text2);text-decoration:none;}
.back-link:hover{color:var(--blue);}
.info-note{background:#f0f9fc;border:1px solid var(--border);border-radius:9px;padding:12px 14px;font-size:12px;color:var(--text2);margin-top:16px;line-height:1.6;}
.info-note strong{color:var(--blue-dark);}
</style>
</head>
<body>
<div class="box">
  <div class="card">
    <div class="icon-wrap">🔑</div>
    <h1>Lupa Password?</h1>
    <p class="sub">Masukkan username Anda. Admin IT akan menyiapkan link reset password untuk Anda.</p>

    @if(session('status'))
      <div class="success-box">
        ✅ {{ session('status') }}
      </div>
      <div class="info-note">
        <strong>Langkah selanjutnya:</strong><br>
        Hubungi admin IT Anda dan minta link reset password. Admin dapat melihat permintaan Anda di panel manajemen pengguna.
      </div>
    @else
      @if($errors->has('auth'))
        <div class="error-box"><span>⚠️</span><span>{{ $errors->first('auth') }}</span></div>
      @endif

      <form method="POST" action="{{ route('password.forgot.post') }}" onsubmit="this.querySelector('.btn').disabled=true">
        @csrf
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="Masukkan username Anda..."
            value="{{ old('username') }}" autocomplete="username" required>
        </div>
        <button type="submit" class="btn">Kirim Permintaan Reset →</button>
      </form>
    @endif

    <a href="{{ route('login') }}" class="back-link">← Kembali ke halaman login</a>
  </div>
</div>
</body>
</html>
