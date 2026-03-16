# ═══════════════════════════════════════════════════
#  GoTiket — Docker Shortcuts
#  Penggunaan: make <perintah>
# ═══════════════════════════════════════════════════

.PHONY: help up down build restart logs shell db-shell migrate seed fresh

# Default: tampilkan daftar perintah
help:
	@echo ""
	@echo "  GoTiket Docker Commands"
	@echo "  ─────────────────────────────────────────"
	@echo "  make up          Jalankan semua container"
	@echo "  make up-tools    Jalankan + phpMyAdmin"
	@echo "  make down        Matikan semua container"
	@echo "  make build       Build ulang image"
	@echo "  make restart     Restart container app"
	@echo "  make logs        Lihat log container app"
	@echo "  make shell       Masuk ke shell container app"
	@echo "  make db-shell    Masuk ke MySQL CLI"
	@echo "  make migrate     Jalankan migration"
	@echo "  make seed        Jalankan seeder"
	@echo "  make fresh       Drop semua tabel & migrate+seed ulang"
	@echo "  make artisan c=  Jalankan artisan, contoh: make artisan c='route:list'"
	@echo ""

# Jalankan (tanpa phpMyAdmin)
up:
	@echo ">>> Menjalankan GoTiket..."
	@cp -n .env.example .env 2>/dev/null || true
	docker compose up -d
	@echo ">>> Selesai! Buka http://localhost:$${APP_PORT:-8000}"

# Jalankan dengan phpMyAdmin
up-tools:
	@cp -n .env.example .env 2>/dev/null || true
	docker compose --profile tools up -d
	@echo ">>> App: http://localhost:$${APP_PORT:-8000}"
	@echo ">>> phpMyAdmin: http://localhost:$${PMA_PORT:-8080}"

# Matikan
down:
	docker compose down

# Build ulang
build:
	docker compose build --no-cache
	docker compose up -d

# Restart hanya container app
restart:
	docker compose restart app

# Log realtime
logs:
	docker compose logs -f app

# Shell ke container app
shell:
	docker compose exec app sh

# MySQL CLI
db-shell:
	docker compose exec db mysql -u$${DB_USERNAME:-gotiket_user} -p$${DB_PASSWORD:-gotiket_pass} $${DB_DATABASE:-gotiket}

# Artisan
artisan:
	docker compose exec app php artisan $(c)

# Migration
migrate:
	docker compose exec app php artisan migrate

# Seeder
seed:
	docker compose exec app php artisan db:seed

# Fresh (hapus semua data dan mulai ulang)
fresh:
	@echo "⚠️  Ini akan menghapus SEMUA data. Lanjutkan? [y/N]" && read ans && [ $${ans:-N} = y ]
	docker compose exec app php artisan migrate:fresh --seed
	@echo ">>> Database direset dan di-seed ulang."
