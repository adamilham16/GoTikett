<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password GoTiket</title>
</head>
<body style="margin:0;padding:0;background:#f0f9fc;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9fc;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#0e5f7c,#1B9EC9);padding:32px 40px;text-align:center;">
            <div style="font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">GoTiket</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.65);letter-spacing:1.2px;text-transform:uppercase;margin-top:4px;">Organize Work, Empower Teams</div>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 40px 32px;">
            <p style="margin:0 0 8px;font-size:15px;color:#0d3349;font-weight:600;">Halo, {{ $userName }}!</p>
            <p style="margin:0 0 24px;font-size:14px;color:#2e6e8a;line-height:1.6;">
              Kami menerima permintaan reset password untuk akun GoTiket Anda.
              Klik tombol di bawah untuk membuat password baru.
            </p>

            <!-- CTA Button -->
            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
              <tr>
                <td style="background:#1B9EC9;border-radius:10px;text-align:center;">
                  <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 36px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.3px;">
                    Reset Password Saya →
                  </a>
                </td>
              </tr>
            </table>

            <!-- Warning box -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#fff8ed;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                  <p style="margin:0;font-size:12px;color:#92400e;line-height:1.5;">
                    ⚠️ <strong>Link berlaku hingga {{ $expiresAt }} WIB.</strong>
                    Jika Anda tidak meminta reset password, abaikan email ini —
                    password Anda tidak akan berubah.
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:24px 0 0;font-size:12px;color:#6aafc8;line-height:1.5;">
              Jika tombol tidak berfungsi, salin dan tempel URL berikut ke browser:<br>
              <a href="{{ $resetUrl }}" style="color:#1B9EC9;word-break:break-all;">{{ $resetUrl }}</a>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f0f9fc;padding:20px 40px;border-top:1px solid #cce9f3;text-align:center;">
            <p style="margin:0;font-size:11px;color:#6aafc8;">
              &copy; {{ date('Y') }} LOB CCCM SIMGROUP &mdash; GoTiket v1.0<br>
              Email ini dikirim otomatis, mohon jangan membalas.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
