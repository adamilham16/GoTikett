#!/bin/bash
# =============================================================================
# deploy.sh — GoTiket Production Deployment Script
# Usage: ./deploy.sh
# =============================================================================

set -e

# Warna output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Timestamp
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
PROJECT_DIR="/var/www/gotiket"

# Fungsi print
info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; }
step()    { echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; echo -e "${CYAN}  STEP: $1${NC}"; echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

# Trap error — tampilkan pesan gagal jika script berhenti karena error
trap 'error "Deploy GAGAL pada step terakhir. Cek log di atas."; exit 1' ERR

# =============================================================================
echo -e "\n${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        GoTiket — Production Deployment           ║${NC}"
echo -e "${GREEN}║        ${TIMESTAMP}              ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}\n"

# Pastikan berada di direktori project
cd "$PROJECT_DIR"

# =============================================================================
step "1/6  Git Pull — Ambil update terbaru"
# =============================================================================
info "Pulling dari origin/main..."
git pull origin main
success "Git pull selesai."

# =============================================================================
step "2/6  Docker Build — Rebuild image (no-cache)"
# =============================================================================
info "Membangun ulang Docker image..."
docker compose build --no-cache
success "Docker build selesai."

# =============================================================================
step "3/6  Docker Up — Menjalankan container"
# =============================================================================
info "Menjalankan semua container..."
docker compose up -d
success "Container berjalan."

# Tunggu sebentar agar container siap
info "Menunggu container siap (5 detik)..."
sleep 5

# =============================================================================
step "4/6  Artisan Migrate — Menjalankan migrasi database"
# =============================================================================
info "Menjalankan migrasi database..."
docker compose exec -T app php artisan migrate --force
success "Migrasi selesai."

# =============================================================================
step "5/6  Clear Cache — Membersihkan cache aplikasi"
# =============================================================================
info "Membersihkan cache..."
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan view:clear
success "Semua cache dibersihkan."

# =============================================================================
step "6/6  Status Container"
# =============================================================================
info "Status semua container GoTiket:"
echo ""
docker compose ps
echo ""

# =============================================================================
# Verifikasi semua container running
# =============================================================================
UNHEALTHY=$(docker compose ps --format json 2>/dev/null | grep -c '"State":"exited"' || true)

if [ "$UNHEALTHY" -gt 0 ]; then
    warning "Ada $UNHEALTHY container yang tidak berjalan. Cek dengan: docker compose logs"
fi

# =============================================================================
# Pesan akhir
# =============================================================================
echo -e "\n${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║       DEPLOY BERHASIL!                          ║${NC}"
echo -e "${GREEN}║       GoTiket v1.0 sudah live di production     ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}\n"
