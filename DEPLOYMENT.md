# 🚀 Panduan Deployment — Work Activity Application

Dokumentasi ini menjelaskan langkah-langkah lengkap untuk melakukan *deployment*, konfigurasi server, manajemen antrean (*queue worker*), dan *real-time WebSocket server* (Laravel Reverb) untuk aplikasi **Work Activity**.

---

## 📌 Ringkasan Arsitektur & Port Server

Aplikasi ini berjalan pada server Linux dengan konfigurasi port sebagai berikut:

| Layanan / Komponen | Port / Socket | Keterangan |
| :--- | :--- | :--- |
| **Aplikasi Web (Nginx)** | `8099` (HTTP) | URL Akses: `http://10.112.115.18:8099` |
| **Laravel Reverb (WebSocket)** | `8085` (TCP/WS) | Daemon WebSocket Server (`ws://10.112.115.18:8085`) |
| **PHP-FPM** | `unix:/var/run/php/php8.4-fpm.sock` | Driver pemroses PHP (PHP 8.4) |
| **Queue Connection** | Database (`jobs` table) | Dikelola oleh daemon Supervisor `work-activity-worker` |
| **Database** | MariaDB / MySQL | Port `3306` |

---

## 🛠️ Prasyarat Server (Prerequisites)

Pastikan server (Ubuntu / Debian Linux) telah terpasang:
- **PHP** $\ge$ 8.3 (direkomendasikan 8.4) dengan ekstensi: `php8.4-fpm`, `php8.4-mysql`, `php8.4-mbstring`, `php8.4-xml`, `php8.4-curl`, `php8.4-zip`, `php8.4-bcmath`.
- **Composer** (PHP Package Manager)
- **Node.js** & **NPM** (LTS version untuk *build frontend assets*)
- **Nginx** (Web Server)
- **Supervisor** (Process Control System untuk background daemons)
- **Git**

---

## 📦 Panduan Deployment Awal (Initial Setup)

### 1. Unduh Kode dari Git Repository
```bash
cd /var/www
git clone https://github.com/SamSimmons-pixel/Work-Activity-Rodja.git Work-Activity-Rodja
cd /var/www/Work-Activity-Rodja
git checkout deploy
```

---

### 2. Pasang Dependensi PHP & Build Frontend Asset
```bash
# Install package PHP produksi
composer install

# Install dependensi JS & compile asset CSS/JS produksi
npm install
npm run build
```

---

### 3. Konfigurasi Environment (`.env`)
Salin template konfigurasi:
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` di server:
```bash
nano .env
```

Pastikan nilai berikut disesuaikan dengan konfigurasi server:
```ini
APP_NAME="Work Activity"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://10.112.115.18:8099
APP_LOCALE=id

# --- Logging ---
LOG_CHANNEL=daily
LOG_LEVEL=error

# --- Database ---
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=work_activity_db
DB_USERNAME=e21a7b3299a485d1cc8f11914c243b12
DB_PASSWORD=password_db

# --- Session & Queue ---
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

# --- Keamanan ---
BCRYPT_ROUNDS=12

# --- Laravel Reverb (WebSocket Server) ---
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=100001
REVERB_APP_KEY=workactivityappkey
REVERB_APP_SECRET=workactivityappsecret

# Backend PHP berkomunikasi langsung ke Reverb daemon lokal:
REVERB_HOST="127.0.0.1"
REVERB_PORT=8085
REVERB_SCHEME="http"

# Frontend Browser berkomunikasi dari luar via IP server di port 8085:
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="10.112.115.18"
VITE_REVERB_PORT=8085
VITE_REVERB_SCHEME="http"
```

> ⚠️ **Catatan Penting**:
> 1. Variabel `VITE_*` di-compile langsung ke file JS saat `npm run build`. Setiap kali mengubah nilai `.env`, wajib jalankan `npm run build` ulang di server.
> 2. `QUEUE_CONNECTION` harus bernilai `database` (bukan nama database).

---

### 4. Setup Database, Storage Link & Permissions

```bash
# Jalankan migrasi dan seeder awal
php artisan migrate --force --seed

# Buat symbolic link folder storage publik untuk lampiran file
php artisan storage:link

# Set izin kepemilikan dan hak akses direktori
chmod -R 775 storage bootstrap/cache database
chown -R www-data:www-data storage bootstrap/cache database
```

---

### 5. Konfigurasi Firewall Server (UFW)
Pastikan port aplikasi (8099) dan WebSocket Reverb (8085) diizinkan:
```bash
sudo ufw allow 8099/tcp
sudo ufw allow 8085/tcp
sudo ufw reload
```

---

## ⚙️ Konfigurasi Supervisor (Daemons)

Supervisor memastikan server WebSocket (Reverb) dan Queue Worker (pengirim notifikasi) berjalan terus di background dan otomatis aktif kembali jika server reboot/crash.

### 1. Buat Daemon Reverb (`/etc/supervisor/conf.d/work-activity-reverb.conf`)
```bash
sudo nano /etc/supervisor/conf.d/work-activity-reverb.conf
```
Isi dengan:
```ini
[program:work-activity-reverb]
command=php /var/www/Work-Activity-Rodja/artisan reverb:start --host="0.0.0.0" --port=8085
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/Work-Activity-Rodja/storage/logs/reverb.log
stopwaitsecs=3600
```
> ⚠️ **Penting**: Gunakan `--host="0.0.0.0"` agar Reverb dapat menerima koneksi WebSocket dari browser klien luar, dan `--port=8085`.

---

### 2. Buat Daemon Queue Worker (`/etc/supervisor/conf.d/work-activity-worker.conf`)
```bash
sudo nano /etc/supervisor/conf.d/work-activity-worker.conf
```
Isi dengan:
```ini
[program:work-activity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/Work-Activity-Rodja/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/Work-Activity-Rodja/storage/logs/worker.log
stopwaitsecs=3600
```

---

### 3. Aktifkan Daemon Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start work-activity-reverb work-activity-worker:*
sudo supervisorctl status
```

**Output status yang diharapkan:**
```
work-activity-reverb                          RUNNING   pid 1234, uptime 0:00:10
work-activity-worker:work-activity-worker_00  RUNNING   pid 1235, uptime 0:00:10
work-activity-worker:work-activity-worker_01  RUNNING   pid 1236, uptime 0:00:10
```

---

## 🌐 Konfigurasi Nginx Web Server

Buat konfigurasi virtual host di `/etc/nginx/sites-available/work-activity`:
```bash
sudo nano /etc/nginx/sites-available/work-activity
```

Isi dengan konfigurasi berikut:
```nginx
server {
    listen 8099;
    server_name 10.112.115.18;
    client_max_body_size 20M;

    root /var/www/Work-Activity-Rodja/public;
    index index.php index.html;

    # ── Routing Utama Laravel ────────────────────────────────────────────────
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # ── Pemrosesan PHP FastCGI ──────────────────────────────────────────────
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
    }

    # ── Blokir File Konfigurasi Sensitif ────────────────────────────────────
    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi dan muat ulang Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/work-activity /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🛡️ Maintenance Mode (Mode Perawatan)

Aktifkan mode perawatan agar pengguna menerima halaman informatif saat proses update berlangsung:

```bash
# Aktifkan maintenance mode SEBELUM update
php artisan down --retry=60

# Lakukan proses update
# ...

# Nonaktifkan maintenance mode SETELAH update selesai
php artisan up
```

---

## 🔄 Prosedur Update / Deploy Ulang (Continuous Deployment)

Setiap kali ada pembaruan kode yang di-*push* ke branch `deploy`, jalankan urutan perintah berikut:

```bash
cd /var/www/Work-Activity-Rodja

# 1. Ambil kode terbaru
git pull origin deploy

# 2. Update dependensi & Rebuild Bundle Frontend
composer install
npm run build

# 3. Jalankan migrasi database baru
php artisan migrate

# 4. Refresh cache aplikasi
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart worker & websocket agar membaca kode terbaru
sudo supervisorctl restart work-activity-worker:*
sudo supervisorctl restart work-activity-reverb
```

---

## 👥 Akun Demo / Awal Hasil Database Seeder

Setelah menjalankan `php artisan migrate --force --seed`, akun berikut siap digunakan:

| Username | Password | Role | Deskripsi & Akses |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Administrator | Akses penuh manajemen user, role, permission, divisi, & posisi. |
| **`budi`** | `password123` | Supervisor | Kepala Divisi IT. Dapat memverifikasi aktivitas bawahan & review kinerja. |
| **`ahmad`** | `password123` | Employee | IT Support (Bawahan Budi). Mencatat aktivitas harian & menerima review. |

---

## 📊 Monitoring & Log

### Cek Status Daemon
```bash
# Status semua daemon Supervisor
sudo supervisorctl status

# Lihat log Worker secara live
tail -f /var/www/Work-Activity-Rodja/storage/logs/worker.log

# Lihat log Reverb secara live
tail -f /var/www/Work-Activity-Rodja/storage/logs/reverb.log

# Lihat log Laravel harian
tail -f /var/www/Work-Activity-Rodja/storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Audit Keamanan Dependensi (Rutin)
```bash
# Cek kerentanan package PHP
composer audit

# Cek kerentanan package JavaScript
npm audit
```
