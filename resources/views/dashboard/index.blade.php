<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $config['appName'] ?? 'GoTiket' }} — {{ $config['appSubtitle'] ?? 'Atur Kerja, Dukung Tim' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@include('dashboard.partials._styles')
</head>
<body>
<div class="bg-layer" id="bg-layer"></div>

@include('dashboard.partials._sidebar')

@include('dashboard.partials._main_content')

@include('dashboard.partials._modal_create')
@include('dashboard.partials._modal_approval')
@include('dashboard.partials._modal_task_edit')
@include('dashboard.partials._modal_freeze')
@include('dashboard.partials._modal_detail')
@include('dashboard.partials._modal_admin')

<div class="toast" id="toast"></div>

@include('dashboard.partials._init_data')
@vite('resources/js/app.js')
</body>
</html>
