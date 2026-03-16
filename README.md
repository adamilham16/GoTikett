# GoTiket — Laravel 12 + MySQL

Aplikasi ticketing internal berbasis web.
Stack: **Laravel 12 · MySQL 8 · PHP 8.2 · Nginx · Docker**

---

## Cara Jalankan

Ada dua cara: **Docker** (direkomendasikan) atau **Manual** (tanpa Docker).

---

## Cara 1 — Docker (Direkomendasikan)

### Prasyarat
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) sudah terinstall
- Port 8000 tidak dipakai aplikasi lain

### Langkah

```bash
# 1. Masuk ke folder project
cd gotiket

# 2. Salin file konfigurasi
cp .env.example .env

# 3. Build image dan jalankan semua container
#    (Proses pertama ~3-5 menit karena download image)
docker compose up -d

# 4. Lihat progress setup (migration, seeder, dll)
docker compose logs -f app
```

Buka browser: **http://localhost:8000** — selesai.

Tidak perlu install PHP, MySQL, atau Composer secara manual.

---

### Perintah Sehari-hari (via Makefile)

```bash
make up             # Jalankan app
make up-tools       # Jalankan app + phpMyAdmin (http://localhost:8080)
make down           # Matikan semua container
make logs           # Lihat log realtime
make shell          # Masuk ke shell container
make migrate        # Jalankan migration
make fresh          # Reset DB + seed ulang (HAPUS SEMUA DATA)
make artisan c="route:list"   # Jalankan artisan command apapun
```

Jika tidak ada `make`, gunakan langsung:

```bash
docker compose up -d
docker compose down
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app sh
```

---

### Struktur Container

| Container | Isi | Port |
|---|---|---|
| `gotiket_app` | PHP 8.2 + Nginx (Alpine) | 8000 |
| `gotiket_db` | MySQL 8.0 | 3306 |
| `gotiket_pma` | phpMyAdmin (opsional) | 8080 |

phpMyAdmin hanya aktif jika dijalankan dengan: `make up-tools`

---

### Ganti Port (jika port 8000 sudah dipakai)

Edit file `.env`:

```env
APP_PORT=9000       # ganti port app
DB_PORT_EXPOSE=3307 # ganti port MySQL yang diexpose
PMA_PORT=8081       # ganti port phpMyAdmin
```

Lalu restart: `docker compose up -d`

---

### Deploy ke VPS / Production

```bash
# 1. Copy project ke server
scp -r gotiket/ user@server:/var/www/

# 2. SSH ke server
ssh user@server
cd /var/www/gotiket

# 3. Set environment production
cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, isi password yang kuat

# 4. Jalankan
docker compose up -d
```

---

## Cara 2 — Tanpa Docker (Manual)

Cocok jika sudah punya XAMPP, Laragon, atau server dengan PHP dan MySQL.

### Prasyarat
- PHP 8.2+ dengan extension: `pdo_mysql`, `mbstring`, `zip`, `gd`, `intl`
- MySQL 8.0+
- Composer 2.x

### Langkah

```bash
# 1. Install dependency PHP
composer install

# 2. Salin konfigurasi
cp .env.example .env

# 3. Edit .env: ganti DB_HOST ke 127.0.0.1 dan isi kredensial MySQL lokal
#    DB_HOST=127.0.0.1
#    DB_DATABASE=gotiket
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Buat database
mysql -u root -e "CREATE DATABASE gotiket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Generate app key
php artisan key:generate

# 6. Migration + seeder
php artisan migrate --seed

# 7. Storage symlink
php artisan storage:link

# 8. Jalankan
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## Akun Demo

| Username | Password   | Role              |
|----------|------------|-------------------|
| adam     | adam123    | IT SIM (Admin)    |
| puji     | puji123    | IT SIM (Admin)    |
| rizky    | rizky123   | IT SIM (Admin)    |
| saddam   | saddam123  | IT SIM (Admin)    |
| icha     | icha123    | User (Requester)  |
| mutia    | mutia123   | User (Requester)  |
| jovi     | jovi123    | Manager/Approver  |

Password default untuk user baru yang ditambahkan: `[username]123`

---

## Struktur File Docker

```
docker/
  nginx/default.conf        ← Konfigurasi Nginx
  php/php.ini               ← Konfigurasi PHP (upload, memory, timezone)
  php/www.conf              ← PHP-FPM pool
  mysql/my.cnf              ← Konfigurasi MySQL (charset, buffer)
  supervisor/supervisord.conf ← Nginx + PHP-FPM dalam 1 container
  entrypoint.sh             ← Setup otomatis saat container start

Dockerfile                  ← Image PHP 8.2 + Nginx Alpine
docker-compose.yml          ← Orkestrasi container
.env.example                ← Template konfigurasi
.dockerignore               ← Exclude file saat build
Makefile                    ← Shortcut perintah
```

---

## Troubleshooting

**Container tidak mau start**
```bash
docker compose logs app
docker compose logs db
```

**Port sudah dipakai**
```bash
# Ganti port di .env
APP_PORT=9000
```

**Reset semua (mulai dari nol — DATA HILANG)**
```bash
docker compose down -v
docker compose up -d
```

**Permission error di storage**
```bash
docker compose exec app chown -R www-data:www-data storage/
```
