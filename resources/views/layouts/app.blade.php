<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title id="app-title">GoTiket — Organize Work, Empower Teams</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
/* ─── paste semua CSS dari file HTML original di sini ─── */
@yield('styles')
</style>
</head>
<body>

@yield('content')

<div class="toast" id="toast"></div>

<script>
// Setup CSRF untuk semua AJAX request
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
function api(url, options = {}) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            ...options.headers,
        },
        ...options,
    }).then(r => r.json());
}

function showToast(msg, type = 'ok') {
    const t = document.getElementById('toast');
    t.className = 'toast ' + (type === 'warn' ? 'warn' : type === 'err' ? 'err' : '');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>

@yield('scripts')
</body>
</html>
