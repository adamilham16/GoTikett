# GoTiket — Sistem Tiket Internal

Aplikasi manajemen tiket (helpdesk) internal berbasis web untuk mengelola permintaan layanan IT, approval alur kerja, penugasan otomatis, dan pemantauan SLA.

**Stack:** Laravel 12 · PHP 8.2 · MySQL 8 · Nginx · Docker · Reverb · Vite

---

## Tech Stack

### Backend
| Komponen | Versi / Detail |
|---|---|
| **PHP** | 8.2 (fpm-alpine) |
| **Laravel** | 12.x |
| **MySQL** | 8.0 |
| **PhpSpreadsheet** | `phpoffice/phpspreadsheet` — export Excel `.xlsx` |
| **Laravel Reverb** | WebSocket server bawaan Laravel (broadcast real-time) |

### Frontend
| Komponen | Detail |
|---|---|
| **Blade** | Server-side rendering — tidak menggunakan React/Vue |
| **Vanilla JavaScript** | Semua interaksi UI ditulis manual (fetch, DOM, WS) |
| **Chart.js** | Grafik donut & bar di dashboard |
| **Space Grotesk** | Font utama (Google Fonts) |
| **JetBrains Mono** | Font angka & monospace |

### Build Tools
| Komponen | Versi |
|---|---|
| **Vite** | ^6.0 |
| **laravel-vite-plugin** | ^1.0 |

### Real-time / Broadcasting
| Komponen | Versi / Detail |
|---|---|
| **Laravel Reverb** | WebSocket server (port 8080) |
| **Laravel Echo** | ^2.3.1 — client-side WebSocket listener |
| **Pusher-JS** | ^8.4.3 — driver yang digunakan Laravel Echo |

### Infrastructure
| Komponen | Detail |
|---|---|
| **Docker** | Multi-stage build (builder + runtime dalam satu image Alpine) |
| **Nginx** | Reverse proxy + static file serving (dalam container yang sama dengan PHP) |
| **PHP-FPM** | FastCGI process manager |
| **Supervisor** | Mengelola proses Nginx, PHP-FPM, dan Reverb dalam satu container |
| **phpMyAdmin** | Opsional — aktif via `make up-tools` (port 8081) |

### Session & Cache
| Komponen | Driver |
|---|---|
| **Session** | `database` (tabel `sessions`) |
| **Cache** | `file` |
| **Queue** | `sync` (tidak ada antrian asinkron) |

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [UI/UX Design System](#uiux-design-system)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Peran & Izin](#peran--izin)
- [Alur Tiket](#alur-tiket)
- [Sistem SLA & Freeze](#sistem-sla--freeze)
- [Penugasan Otomatis](#penugasan-otomatis)
- [Keamanan](#keamanan)
- [Notifikasi](#notifikasi)
- [Struktur Database](#struktur-database)
- [Struktur Direktori](#struktur-direktori)
- [API Routes](#api-routes)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Docker](#docker)
- [Perintah Makefile](#perintah-makefile)
- [Akun Demo](#akun-demo)
- [Troubleshooting](#troubleshooting)

---

## Fitur Utama

> **Changelog terbaru (2026-03-31)**
> - Halaman login: hapus keterangan Uptime 99%, v1.0, dan Rating 4.6
> - Portal User: hapus card "Ringkasan" yang redundan dengan stat cards di atasnya
> - Portal IT/Manager: Vibrant Redesign — stat cards full gradient, chart headers berwarna, kanban column color-coded, glassmorphism panels, topbar rainbow accent line
> - Portal User: Vibrant Redesign — greeting header glassmorphism + orb dekoratif + gradient text, stat cards full gradient (+ purple), chart headers berwarna, ticket section header gradient, SLA panel glassmorphism

| Fitur | Keterangan |
|---|---|
| **Manajemen Tiket** | Buat, lihat, komentar, lampiran file, tutup, dan hapus tiket |
| **Approval Workflow** | Manager harus menyetujui tiket baru sebelum diproses IT |
| **Auto-Assignment** | Penugasan otomatis ke anggota IT berdasarkan kategori + klien |
| **Checklist Tugas** | IT menambahkan sub-task; tiket auto-close saat semua selesai |
| **SLA Tracking** | Progress bar waktu + tenggat, status OVERDUE, sisa hari |
| **Freeze Tiket** | IT bisa mengajukan jeda SLA; Manager approve/reject; due_date otomatis diperpanjang |
| **Manajemen User** | IT mengelola user (tambah, edit, aktif/nonaktif, reset password) |
| **Manajemen Klien** | Daftar klien yang bisa digunakan saat buat tiket |
| **Konfigurasi Aplikasi** | Nama app, subtitle, ikon, warna/gambar latar — bisa diubah tanpa deploy ulang |
| **Login Logs** | IT bisa melihat riwayat login semua user |
| **Password Reset** | Forgot password via email atau token link manual (jika tanpa email) |
| **Notifikasi In-App** | Notifikasi real-time berbasis database untuk event tiket penting |
| **Export Excel** | IT dan Manager bisa ekspor semua tiket ke file `.xlsx` |

---

## UI/UX Design System

Dashboard GoTiket menggunakan design language modern yang konsisten di seluruh portal:

### Prinsip Desain

| Prinsip | Implementasi |
|---|---|
| **Vibrant Colorful Cards** | Stat cards menggunakan full gradient (bukan tint tipis) dengan warna kontras tinggi |
| **Glassmorphism** | Panel SLA, greeting header, dan widget menggunakan `backdrop-filter:blur` dengan background semi-transparan |
| **Neumorphism Icons** | Icon container di stat cards menggunakan frosted glass (`rgba(255,255,255,0.22)` + inset shadow) di atas warna vibrant |
| **Gradient Typography** | Heading dan judul section menggunakan CSS gradient text (`-webkit-background-clip:text`) |
| **Soft Shadows** | Box shadow berwarna sesuai warna card (`rgba(warna, 0.38)`) untuk depth yang natural |
| **Playful Accents** | Orb dekoratif di greeting header, rainbow gradient line di topbar, colored kanban headers |
| **Responsive** | Breakpoint di 1024px, 900px, 768px, dan 480px |

### Palet Warna per Portal

**Portal IT/Manager:**
| Card | Warna | Gradient |
|---|---|---|
| Total Tiket | Biru | `#0284c7 → #38bdf8` |
| Menunggu Persetujuan | Amber | `#b45309 → #fbbf24` |
| Sedang Berjalan | Oranye | `#c2410c → #fb923c` |
| Selesai / Ditutup | Hijau | `#047857 → #6ee7b7` |
| Chart kiri | Ungu | `#6366f1 → #a78bfa` |
| Chart kanan | Pink→Oranye | `#ec4899 → #fb923c` |

**Portal User:**
| Card | Warna | Gradient |
|---|---|---|
| Total Tiket | Biru | `#0284c7 → #38bdf8` |
| Sedang Berjalan | Oranye | `#c2410c → #fb923c` |
| Selesai / Tayang | Hijau | `#047857 → #6ee7b7` |
| Menunggu Persetujuan | Ungu | `#7c3aed → #a78bfa` |
| Chart kiri | Ungu→Cyan | `#7c3aed → #06b6d4` |
| Chart kanan | Pink→Oranye | `#ec4899 → #f97316` |

### Struktur CSS (Partials)

Semua CSS dashboard terpusat di `resources/views/dashboard/partials/_styles.blade.php` dan diorganisir dalam blok scoped:

```
_styles.blade.php
├── :root variables (warna, radius)
├── Base styles (sidebar, topbar, layout)
├── Stat cards, kanban, table
├── USER DASHBOARD — SCOPED UI/UX ENHANCEMENTS
├── COMPACT LAYOUT OPTIMIZATION
├── STAT CARD: GRADIENT FILL (user-stats-grid)
├── CHART CONTAINERS
├── WIDE 2-COLUMN LAYOUT
├── SESI 3: TABULAR + POLISH
├── IT / MANAGER BOARD — SCOPED ENHANCEMENTS
├── IT/MANAGER: VIBRANT REDESIGN  ← scoped ke #it-dashboard
└── USER PORTAL: VIBRANT REDESIGN ← scoped ke .user-dashboard
```

---

## Arsitektur Sistem

```
Browser (Blade + Vanilla JS)
         │
         ▼
     Nginx (reverse proxy)
         │
         ▼
   PHP-FPM (Laravel 12)
         │
    ┌────┴────┐
    │         │
  MySQL 8   Storage
(database)  (lampiran)
```

Aplikasi menggunakan **arsitektur monolitik tradisional**:
- Server-side rendering dengan **Blade** untuk halaman utama
- **JSON API** (route `/app-data` dan lainnya) untuk interaksi AJAX dari JavaScript
- Tidak menggunakan framework JS (React/Vue) — semua UI ditulis dengan vanilla JS di dalam template Blade
- State frontend disimpan di variabel JS (`window.__tickets`, dll)

### Middleware Stack

```
Request → throttle (rate limiting)
        → auth.session (cek session login)
        → role:manager / role:it (cek role)
        → Controller
```

---

## Peran & Izin

Sistem memiliki tiga tipe user (`type` di tabel `users`):

| Tipe | Sebutan | Kemampuan |
|---|---|---|
| `it` | IT SIM | Semua fitur: kelola tiket, user, klien, konfigurasi, task, freeze, ekspor |
| `manager` | Manager | Approve/reject tiket dan freeze request; melihat semua tiket |
| `user` | Pemohon | Buat tiket, lihat tiket sendiri, tambah komentar & lampiran |

### Role Tambahan

Field `role` digunakan untuk penugasan otomatis:
- `ALL` — IT yang bisa menangani semua kategori (fallback terakhir)
- Nilai lain — dicocokkan dengan aturan auto-assign berdasarkan kategori

### Approver

Setiap user tipe `user` bisa memiliki `approver_id` yang mengarah ke user lain (biasanya Manager). Ini menentukan siapa yang menerima notifikasi approval.

---

## Alur Tiket

```
[User] Buat Tiket
       │
       ▼
  Status: pending_approval
  (Notifikasi dikirim ke Manager)
       │
       ├─── [Manager] Reject ──► Tiket dihapus
       │
       └─── [Manager] Approve
                   │
                   ▼
            Status: approved
            assignee_id diisi (auto-assign atau manual)
            due_date dihitung dari aturan kategori
                   │
                   ▼
         [IT] Tambah Task (checklist)
         [IT] Toggle task Done/Todo
                   │
                   ▼
         Semua Task Done?
            │         │
           Ya        Tidak
            │         │
            ▼         └──► IT lanjutkan
        Auto-close
        (closed_at diisi, komentar sistem ditambahkan)
        Notifikasi ke creator
```

### Field Penting di Tabel `tickets`

| Field | Keterangan |
|---|---|
| `ticket_id` | ID unik format `TKT-YYYYMM-NNNN` |
| `type` | Jenis: `Permintaan`, `Insiden`, `Pertanyaan`, dll |
| `approval` | Status approval: `pending_approval`, `approved` |
| `category` | Kategori masalah (digunakan untuk auto-assign) |
| `client` | Nama klien terkait |
| `assignee_id` | IT yang bertanggung jawab |
| `approved_by` | Manager yang menyetujui |
| `approved_at` | Waktu approval |
| `due_date` | Tenggat penyelesaian (bisa diperpanjang saat freeze) |
| `closed_at` | Waktu tiket ditutup (null = belum selesai) |
| `freeze_status` | `null`, `pending_approval`, atau `active` |
| `freeze_paused_seconds` | Total detik yang benar-benar dijeda (akumulasi dari freeze selesai) |

---

## Sistem SLA & Freeze

### Perhitungan SLA

SLA dihitung di `app/Models/Ticket.php` → `getSlaAttribute()`:

```
elapsed = (now - created_at) - freeze_paused_seconds
pct     = elapsed / (due_date - created_at) × 100
```

- **`freeze_paused_seconds`** — total waktu yang sudah dijeda (dari freeze yang sudah selesai/dibatalkan)
- **`due_date`** — otomatis diperpanjang di database saat freeze disetujui

Status label SLA:
| Kondisi | Label | Warna |
|---|---|---|
| Tiket closed | Closed | Hijau |
| Sedang freeze aktif | 🧊 Dijeda s/d [tanggal] | Ungu |
| Freeze pending approval | [sisa hari] hari lagi ⏸ | Kuning |
| pct ≥ 100% | OVERDUE N hari | Merah |
| pct ≥ 75% | N hari lagi | Kuning |
| pct < 75% | N hari lagi / Hari ini | Hijau |

### Alur Freeze

```
[IT] POST /tickets/{id}/freeze (duration_days, reason)
       │
       ▼
  ticket_freezes dibuat (status: pending_approval)
  ticket.freeze_status = pending_approval
  Notifikasi ke Manager
       │
       ├─── [Manager] POST /freezes/{id}/reject
       │         └──► freeze.status = rejected
       │              ticket.freeze_status = null
       │
       └─── [Manager] POST /freezes/{id}/approve
                 │
                 ▼
           freeze.status = approved
           freeze.freeze_starts_at = now()
           freeze.freeze_ends_at = now() + duration_days
           ticket.freeze_status = active
           ticket.due_date += duration_days  ← diperpanjang di DB
           Notifikasi ke creator tiket
```

### Unfreeze

```
[IT] POST /tickets/{id}/unfreeze
       │
       ▼
  Hitung waktu yang benar-benar dipakai:
    paused = now - freeze_starts_at
    requested = duration_days × 86400
    unused = max(0, requested - paused)

  ticket.freeze_paused_seconds += paused
  ticket.due_date -= unused  ← kembalikan sisa hari yang tidak dipakai
  ticket.freeze_status = null
  freeze.status = completed
  freeze.freeze_ends_at = now()
```

---

## Penugasan Otomatis

Saat tiket disetujui, sistem mencari assignee dengan urutan fallback:

```
1. AutoAssignRule dengan kategori = ticket.category DAN client = ticket.client
2. AutoAssignRule dengan kategori = ticket.category DAN client = '*' (wildcard)
3. User IT dengan role = 'ALL'
4. User IT manapun (first)
```

Aturan dikelola di menu Admin → Auto Assign dan di-cache untuk performa.

---

## Keamanan

### Autentikasi

- Session-based (bukan token/JWT)
- Session key: `user_id` disimpan di `$_SESSION` Laravel
- Middleware `auth.session` memvalidasi setiap request

### Perlindungan Brute Force

- Login throttle: **5 percobaan per 1 menit** per IP
- Forgot password throttle: **3 percobaan per 5 menit** per IP
- Setelah limit: respons 429 Too Many Requests

### Login Logging

Semua percobaan login (berhasil maupun gagal) dicatat di tabel `login_logs`:
- `user_id`, `ip_address`, `user_agent`, `status` (`success`/`failed`), `created_at`

### Password Reset

Dua mekanisme:
1. **Email** — link reset dikirim ke email user (jika `email` diisi di profil)
2. **Manual** — IT membuat link token untuk user tanpa email, lalu membagikan link secara langsung

Token berlaku **24 jam**, satu kali pakai (`used = true` setelah digunakan).

### Upload File

- Hanya tipe file yang diizinkan: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, gif, zip, rar
- Ukuran maks: **10 MB per file**
- Disimpan di `storage/app/attachments/` (tidak bisa diakses langsung dari web)
- Download melalui route `/attachments/{id}/download` yang terautentikasi

---

## Notifikasi

Notifikasi disimpan di tabel `notifications` dan ditampilkan di navbar (ikon lonceng).

Event yang memicu notifikasi:

| Event | Penerima |
|---|---|
| Tiket baru dibuat | Manager (approver user) |
| Tiket disetujui | Creator tiket |
| Freeze request masuk | Manager |
| Freeze disetujui | Creator tiket |
| Freeze ditolak | IT yang mengajukan |
| Tiket auto-close (semua task selesai) | Creator tiket |

Format notifikasi:
```json
{
  "user_id": 5,
  "type": "ticket_approved",
  "title": "Tiket Disetujui ✅",
  "message": "Tiket TKT-202603-0001 telah disetujui oleh Manager",
  "ticket_id": "TKT-202603-0001",
  "read_at": null
}
```

---

## Struktur Database

### Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `username` | varchar unique | Login username |
| `name` | varchar | Nama lengkap |
| `email` | varchar nullable | Untuk password reset via email |
| `password` | varchar | Bcrypt hash |
| `type` | enum(it,manager,user) | Tipe/peran utama |
| `role` | varchar nullable | Role spesifik (untuk auto-assign) |
| `dept` | varchar nullable | Departemen |
| `color` | varchar | Warna avatar (hex) |
| `approver_id` | FK → users.id | Manager yang approve tiket user ini |
| `is_active` | boolean | Bisa login atau tidak |

### Tabel `tickets`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `ticket_id` | varchar unique | ID tampilan: TKT-YYYYMM-NNNN |
| `title` | varchar | Judul tiket |
| `desc` | text nullable | Deskripsi masalah |
| `type` | varchar | Jenis tiket |
| `approval` | varchar | `pending_approval` / `approved` |
| `category` | varchar nullable | Kategori (untuk auto-assign) |
| `client` | varchar nullable | Nama klien |
| `priority` | varchar nullable | Prioritas |
| `creator_id` | FK → users | Pembuat tiket |
| `assignee_id` | FK → users | IT penanggung jawab |
| `approved_by` | FK → users | Manager yang approve |
| `approved_at` | datetime | Waktu approval |
| `due_date` | datetime | Tenggat (diperpanjang saat freeze) |
| `closed_at` | datetime | Waktu selesai (null = aktif) |
| `freeze_status` | varchar nullable | `pending_approval` / `active` / null |
| `freeze_paused_seconds` | int | Total detik dijeda (akumulasi) |

### Tabel `tasks`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `ticket_id` | FK → tickets | |
| `title` | varchar | Judul sub-task |
| `status` | enum(Todo,Done) | Status penyelesaian |
| `due_date` | date nullable | Tenggat task |
| `notes` | text nullable | Catatan |

### Tabel `ticket_freezes`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `ticket_id` | FK → tickets | |
| `requested_by` | FK → users | IT yang mengajukan |
| `approved_by` | FK → users | Manager yang approve |
| `duration_days` | int unsigned | Durasi freeze (hari) |
| `reason` | text | Alasan freeze |
| `status` | enum | `pending_approval`, `approved`, `rejected`, `completed` |
| `freeze_starts_at` | datetime | Waktu freeze mulai |
| `freeze_ends_at` | datetime | Waktu freeze berakhir (jadwal atau aktual) |

### Tabel `comments`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `ticket_id` | FK → tickets | |
| `user_id` | FK → users | |
| `text` | text | Isi komentar |
| `created_at` | datetime | |

### Tabel `attachments`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `ticket_id` | FK → tickets | |
| `user_id` | FK → users | |
| `original_name` | varchar | Nama file asli |
| `path` | varchar | Path di storage |
| `size` | int | Ukuran file (bytes) |

### Tabel `clients`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `nama` | varchar unique | Nama klien |

### Tabel `auto_assign_rules`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `kategori` | varchar | Kategori tiket |
| `client` | varchar | Nama klien atau `*` untuk semua |
| `assignee_id` | FK → users | IT yang ditugaskan |

### Tabel `app_configs`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `key` | varchar unique | Nama konfigurasi |
| `value` | text | Nilai |

Kunci yang didukung: `appName`, `appSubtitle`, `appIcon`, `bgType`, `bgColor`, `bgGradient`, `bgImage`

### Tabel `notifications`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | Penerima notifikasi |
| `type` | varchar | Jenis event |
| `title` | varchar | Judul notifikasi |
| `message` | text | Isi notifikasi |
| `ticket_id` | varchar nullable | ID tiket terkait |
| `read_at` | datetime | Null = belum dibaca |

### Tabel `login_logs`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users nullable | Null jika username tidak ditemukan |
| `username_attempt` | varchar | Username yang dicoba |
| `ip_address` | varchar | IP address |
| `user_agent` | text | Browser/client |
| `status` | enum(success,failed) | Hasil login |

### Tabel `password_reset_tokens`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `token` | varchar unique | Token acak 64 karakter |
| `used` | boolean | True setelah dipakai |
| `expires_at` | datetime | Kedaluwarsa 24 jam |

---

## Struktur Direktori

```
gotiket/
├── app/
│   ├── Console/
│   │   └── Commands/         ← Artisan commands (scheduled tasks)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      ← Klien, auto-assign, config, task, app-data
│   │   │   ├── AuthController.php       ← Login, logout, forgot/reset password
│   │   │   ├── NotificationController.php ← Notifikasi in-app
│   │   │   ├── TicketController.php     ← CRUD tiket, approval, freeze, komentar
│   │   │   └── UserController.php       ← Manajemen user
│   │   └── Middleware/
│   │       ├── AuthSession.php          ← Cek session login
│   │       └── RoleMiddleware.php       ← Cek role (it/manager)
│   ├── Mail/
│   │   └── PasswordResetMail.php        ← Email reset password
│   ├── Models/
│   │   ├── AppConfig.php               ← Konfigurasi aplikasi (dengan cache)
│   │   ├── Attachment.php
│   │   ├── AutoAssignRule.php          ← Aturan auto-assign (dengan cache)
│   │   ├── Client.php
│   │   ├── Comment.php
│   │   ├── LoginLog.php
│   │   ├── Notification.php            ← Notifikasi in-app
│   │   ├── PasswordResetToken.php
│   │   ├── Task.php                    ← Sub-task dalam tiket
│   │   ├── Ticket.php                  ← Model utama + SLA computation
│   │   ├── TicketFreeze.php            ← Riwayat freeze
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                         ← Registrasi middleware
├── database/
│   ├── migrations/                     ← Semua skema database
│   └── seeders/                        ← Data awal (user, klien, tiket contoh)
├── docker/
│   ├── entrypoint.sh                   ← Setup otomatis saat container start
│   ├── nginx/default.conf
│   ├── php/php.ini
│   ├── php/www.conf
│   ├── mysql/my.cnf
│   └── supervisor/supervisord.conf
├── public/                             ← Web root (index.php, aset statis)
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── forgot-password.blade.php
│       │   └── reset-password.blade.php
│       ├── dashboard/
│       │   ├── index.blade.php              ← Entry point, load partials
│       │   └── partials/
│       │       ├── _styles.blade.php        ← Semua CSS dashboard (scoped per portal)
│       │       ├── _sidebar.blade.php       ← Sidebar navigasi
│       │       ├── _main_content.blade.php  ← HTML konten (user + IT/Manager)
│       │       ├── _scripts.blade.php       ← Semua JavaScript (fetch, render, WS)
│       │       ├── _init_data.blade.php     ← Data awal dari PHP ke JS
│       │       ├── _modal_create.blade.php  ← Modal buat tiket
│       │       ├── _modal_detail.blade.php  ← Modal detail tiket
│       │       ├── _modal_approval.blade.php← Modal approve/reject
│       │       ├── _modal_admin.blade.php   ← Modal admin (klien, config, dll)
│       │       ├── _modal_task_edit.blade.php ← Modal edit task
│       │       └── _modal_freeze.blade.php  ← Modal freeze tiket
│       ├── emails/
│       │   └── password-reset.blade.php
│       └── users/
│           └── index.blade.php         ← Halaman manajemen user
├── routes/
│   ├── web.php                         ← Semua route HTTP
│   └── console.php                     ← Scheduled commands
├── storage/
│   └── app/attachments/                ← File lampiran tiket
├── Dockerfile
├── docker-compose.yml
├── Makefile
└── .env.example
```

---

## API Routes

### Publik (tanpa login)

| Method | URL | Keterangan |
|---|---|---|
| GET | `/login` | Halaman login |
| POST | `/login` | Proses login (throttle: 5/menit) |
| GET | `/forgot-password` | Halaman lupa password |
| POST | `/forgot-password` | Kirim link reset (throttle: 3/5menit) |
| GET | `/reset-password/{token}` | Halaman reset password |
| POST | `/reset-password/{token}` | Proses reset password |

### Semua User Terlogin

| Method | URL | Keterangan |
|---|---|---|
| POST | `/logout` | Logout |
| GET | `/` | Dashboard utama |
| GET | `/app-data` | Data JSON awal (tiket, user, config, dll) |
| POST | `/password/change` | Ganti password sendiri |
| GET | `/notifications` | Daftar notifikasi |
| POST | `/notifications/mark-read` | Tandai notifikasi dibaca |
| POST | `/tickets` | Buat tiket baru |
| GET | `/tickets/{id}` | Detail tiket (JSON) |
| POST | `/tickets/{id}/comment` | Tambah komentar + lampiran |
| GET | `/attachments/{id}/download` | Download lampiran |

### Manager Only

| Method | URL | Keterangan |
|---|---|---|
| POST | `/tickets/{id}/approve` | Setujui tiket |
| DELETE | `/tickets/{id}/reject` | Tolak & hapus tiket |
| POST | `/freezes/{id}/approve` | Setujui freeze request |
| POST | `/freezes/{id}/reject` | Tolak freeze request |

### IT SIM Only

| Method | URL | Keterangan |
|---|---|---|
| POST | `/tickets/{id}/tasks` | Tambah task |
| PATCH | `/tasks/{id}/toggle` | Toggle status task (Todo↔Done) |
| PATCH | `/tasks/{id}` | Edit task |
| DELETE | `/tasks/{id}` | Hapus task |
| POST | `/tickets/{id}/close` | Tutup tiket manual |
| DELETE | `/tickets/{id}` | Hapus tiket |
| POST | `/tickets/{id}/reassign` | Ganti assignee |
| POST | `/tickets/{id}/freeze` | Ajukan freeze |
| POST | `/tickets/{id}/unfreeze` | Batalkan freeze aktif |
| GET | `/tickets/export/excel` | Export semua tiket ke Excel |
| GET | `/users` | Halaman manajemen user |
| GET | `/users/data` | Data user (JSON) |
| POST | `/users` | Tambah user baru |
| PATCH | `/users/{id}` | Edit user |
| PATCH | `/users/{id}/toggle-active` | Aktif/nonaktifkan user |
| PATCH | `/users/{id}/reset-password` | Reset password user |
| DELETE | `/users/{id}` | Hapus user |
| GET | `/clients` | Daftar klien |
| POST | `/clients` | Tambah klien |
| DELETE | `/clients/{id}` | Hapus klien |
| GET | `/auto-assign` | Daftar aturan auto-assign |
| POST | `/auto-assign` | Tambah aturan auto-assign |
| DELETE | `/auto-assign/{id}` | Hapus aturan auto-assign |
| GET | `/config` | Konfigurasi aplikasi |
| POST | `/config` | Simpan konfigurasi |
| POST | `/config/reset` | Reset konfigurasi ke default |
| GET | `/security/login-logs` | Log login (200 terakhir) |
| GET | `/security/reset-requests` | Pending password reset requests |

---

## Instalasi

### Cara 1 — Docker (Direkomendasikan)

**Prasyarat:** Docker Desktop terinstall, port 8000 tersedia.

```bash
# Clone / copy project
cd gotiket

# Salin konfigurasi
cp .env.example .env

# Build & jalankan (pertama kali ~3-5 menit)
docker compose up -d

# Pantau progress setup otomatis
docker compose logs -f app
```

Buka **http://localhost:8000** — selesai.

Setup otomatis yang dijalankan `entrypoint.sh`:
1. `composer install`
2. `php artisan key:generate` (jika belum ada)
3. `php artisan migrate --seed`
4. `php artisan storage:link`
5. Set permission `storage/` dan `bootstrap/cache/`
6. Jalankan Nginx + PHP-FPM via Supervisor

---

### Cara 2 — Manual (tanpa Docker)

**Prasyarat:** PHP 8.2+, MySQL 8.0+, Composer 2.x

Extension PHP yang dibutuhkan: `pdo_mysql`, `mbstring`, `zip`, `gd`, `intl`, `fileinfo`

```bash
# Install dependency
composer install

# Konfigurasi
cp .env.example .env
# Edit .env: DB_HOST=127.0.0.1, DB_DATABASE=gotiket, dst.

# Buat database
mysql -u root -e "CREATE DATABASE gotiket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Generate key
php artisan key:generate

# Migrasi + seeder
php artisan migrate --seed

# Symlink storage
php artisan storage:link

# Jalankan
php artisan serve
```

Buka **http://localhost:8000**

---

## Konfigurasi

Salin `.env.example` ke `.env` dan sesuaikan:

```env
# Aplikasi
APP_NAME=GoTiket
APP_ENV=local          # production untuk production
APP_DEBUG=true         # false untuk production
APP_URL=http://localhost:8000
APP_PORT=8000          # Port Docker

# Database
DB_CONNECTION=mysql
DB_HOST=db             # 'db' untuk Docker, '127.0.0.1' untuk manual
DB_PORT=3306
DB_DATABASE=gotiket
DB_USERNAME=gotiket
DB_PASSWORD=secret

# Email (untuk password reset via email — opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="GoTiket"

# Port expose Docker
DB_PORT_EXPOSE=3306
PMA_PORT=8080
```

### Konfigurasi Aplikasi (Runtime)

IT SIM bisa mengubah tampilan aplikasi tanpa deploy ulang melalui menu Admin:

| Key | Keterangan |
|---|---|
| `appName` | Nama aplikasi |
| `appSubtitle` | Subtitle/tagline |
| `appIcon` | Emoji atau URL ikon |
| `bgType` | `color`, `gradient`, atau `image` |
| `bgColor` | Warna solid latar (hex) |
| `bgGradient` | CSS gradient latar |
| `bgImage` | URL gambar latar |

---

## Docker

### Struktur Container

| Container | Image | Port | Keterangan |
|---|---|---|---|
| `gotiket_app` | PHP 8.2 + Nginx (Alpine) | 8000 | Aplikasi utama |
| `gotiket_db` | MySQL 8.0 | 3306 | Database |
| `gotiket_pma` | phpMyAdmin | 8080 | Admin DB (opsional) |

phpMyAdmin hanya aktif jika dijalankan dengan `make up-tools`.

### Build Image

```bash
# Build ulang setelah perubahan Dockerfile
docker compose build app

# Build tanpa cache
docker compose build --no-cache app
```

### Deploy ke Production / VPS

```bash
# Copy project ke server
scp -r gotiket/ user@server:/var/www/

ssh user@server
cd /var/www/gotiket

# Konfigurasi production
cp .env.example .env
nano .env
# Set: APP_ENV=production, APP_DEBUG=false, password yang kuat

# Jalankan
docker compose up -d

# Cek log
docker compose logs -f app
```

---

## Perintah Makefile

```bash
make up             # Jalankan app (detached)
make up-tools       # Jalankan app + phpMyAdmin
make down           # Matikan semua container
make build          # Build ulang image
make restart        # Restart container app
make logs           # Lihat log realtime
make shell          # Masuk ke shell container app
make db-shell       # Masuk ke MySQL CLI
make migrate        # Jalankan migration baru
make seed           # Jalankan seeder
make fresh          # Reset DB + seed ulang ⚠️ HAPUS SEMUA DATA
make artisan c="route:list"   # Jalankan artisan command apapun
```

Jika `make` tidak tersedia, gunakan perintah langsung:

```bash
docker compose up -d
docker compose down
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app sh
docker compose exec db mysql -u gotiket -p gotiket
```

---

## Akun Demo

| Username | Password | Tipe | Role |
|---|---|---|---|
| adam | adam123 | it | IT SIM |
| puji | puji123 | it | IT SIM |
| rizky | rizky123 | it | IT SIM |
| saddam | saddam123 | it | IT SIM |
| icha | icha123 | user | Pemohon |
| mutia | mutia123 | user | Pemohon |
| jovi | jovi123 | manager | Manager/Approver |

Password default untuk user baru yang ditambahkan melalui UI: `[username]123`

---

## Troubleshooting

**Container tidak mau start**
```bash
docker compose logs app
docker compose logs db
```

**Port sudah dipakai**
```bash
# Edit .env, ganti port:
APP_PORT=9000
DB_PORT_EXPOSE=3307
PMA_PORT=8081
# Restart:
docker compose up -d
```

**Migration gagal (tabel sudah ada)**
```bash
# Lihat status migration
docker compose exec app php artisan migrate:status

# Jalankan ulang dari awal (⚠️ HAPUS SEMUA DATA)
make fresh
# atau:
docker compose exec app php artisan migrate:fresh --seed
```

**Permission error di storage**
```bash
docker compose exec app chown -R www-data:www-data storage/
docker compose exec app chmod -R 775 storage/ bootstrap/cache/
```

**Reset total (mulai dari nol, DATA HILANG)**
```bash
docker compose down -v   # hapus container + volume database
docker compose up -d     # buat ulang dari awal
```

**Email tidak terkirim**
- Pastikan `MAIL_*` di `.env` sudah diisi dengan benar
- Untuk Gmail: gunakan App Password (bukan password akun), aktifkan 2FA terlebih dahulu
- Cek log: `docker compose exec app php artisan tinker` → `Mail::raw('test', fn($m)=>$m->to('email@test.com')->subject('test'));`

**Cache tidak sinkron (config/auto-assign tidak update)**
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
```
