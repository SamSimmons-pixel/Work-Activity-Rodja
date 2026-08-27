# 🚀 Panduan Deployment — Work Activity Application

Dokumentasi ini menjelaskan langkah-langkah lengkap untuk melakukan *deployment*, konfigurasi server, manajemen antrean (*queue worker*), dan *real-time WebSocket server* (Laravel Reverb) untuk aplikasi **Work Activity**.

---

## 📌 Ringkasan Arsitektur & Port Server

Aplikasi ini dapat berjalan berdampingan dalam satu server VPS dengan project lain (misal: `running-text` di port 8088):

| Layanan / Komponen | Port / Socket | Keterangan |
| :--- | :--- | :--- |
| **Aplikasi Web (Nginx)** | `8099` (HTTP) | URL Akses: `http://10.112.115.18:8099` |
| **Laravel Reverb (WebSocket)** | `8081` (Internal) | Ditangani via Reverse Proxy Nginx pada path `/app` |
| **PHP-FPM** | `unix:/var/run/php/php8.4-fpm.sock` | Driver pemroses PHP (PHP 8.4) |
| **Queue Connection** | Database (`jobs` table) | Dikelola oleh daemon Supervisor `work-activity-worker` |
| **Database** | Mysql / Maria | MySQL `3306` |

---

## 🛠️ Prasyarat Server (Prerequisites)

Pastikan server (Ubuntu / Debian Linux) telah terpasang:
- **PHP** $\ge$ 8.3 (direkomendasikan 8.4) dengan ekstensi: `php8.4-fpm`, `php8.4-mysql`, `php8.4-mbstring`, `php8.4-xml`, `php8.4-curl`, `php8.4-zip`, `php8.4-bcmath`.
- **Composer** (PHP Package Manager)
- **Node.js** & **NPM** (LTS version untuk *build frontend assets*)
- **Nginx** (Web Server & Reverse Proxy)
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
# Install package PHP TANPA package development (produksi)
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

# --- Logging (produksi: error saja, bukan debug) ---
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

# --- Laravel Reverb (WebSocket) ---
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=100001
REVERB_APP_KEY=workactivityappkey
REVERB_APP_SECRET=workactivityappsecret
REVERB_HOST="10.112.115.18"
REVERB_PORT=8081
REVERB_SCHEME="http"

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

> ⚠️ **Penting**: Ganti nilai `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `DB_USERNAME`, dan `DB_PASSWORD` dengan nilai acak yang unik dan aman di lingkungan produksi.

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


## ⚙️ Konfigurasi Supervisor (Daemons)

Supervisor memastikan server WebSocket (Reverb) dan Queue Worker (pengirim notifikasi) berjalan terus di background dan otomatis aktif kembali jika server reboot/crash.

### 1. Buat Daemon Reverb (`/etc/supervisor/conf.d/work-activity-reverb.conf`)
```bash
sudo nano /etc/supervisor/conf.d/work-activity-reverb.conf
```
Isi dengan:
```ini
[program:work-activity-reverb]
command=php /var/www/Work-Activity-Rodja/artisan reverb:start --host="0.0.0.0" --port=8081
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/Work-Activity-Rodja/storage/logs/reverb.log
stopwaitsecs=3600
```

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
work-activity-reverb            RUNNING   pid 1234, uptime 0:00:10
work-activity-worker:work-activity-worker_00 RUNNING   pid 1235, uptime 0:00:10
work-activity-worker:work-activity-worker_01 RUNNING   pid 1236, uptime 0:00:10
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

    # ── Reverse Proxy WebSocket Laravel Reverb ──────────────────────────────
    location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass http://127.0.0.1:8081;
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

# Lakukan proses update (lihat bagian Deploy Ulang di bawah)
# ...

# Nonaktifkan maintenance mode SETELAH update selesai
php artisan up
```

---

## 🔄 Prosedur Update / Deploy Ulang (Continuous Deployment)

Setiap kali ada pembaruan kode yang di-*push* ke branch `deploy`, ikuti urutan berikut:

```bash
cd /var/www/Work-Activity-Rodja

# 0. Aktifkan maintenance mode
php artisan down --retry=60

# 1. Ambil kode terbaru
git pull origin deploy

# 2. Update dependensi PHP & Rebuild Assets
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Backup database SEBELUM migrasi (pencegahan data loss)
mysqldump -u [DB_USERNAME] -p [DB_DATABASE] > /var/backups/work_activity_$(date +%Y%m%d_%H%M%S).sql

# 4. Jalankan migrasi database baru
php artisan migrate --force

# 5. Refresh cache aplikasi
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart worker & websocket agar membaca kode terbaru
sudo supervisorctl restart work-activity-worker:*
sudo supervisorctl restart work-activity-reverb

# 7. Nonaktifkan maintenance mode
php artisan up
```

---

## 👥 Akun Demo / Awal Hasil Database Seeder

Setelah menjalankan `php artisan migrate --force --seed`, akun berikut siap digunakan:

| Username | Password | Role | Deskripsi & Akses |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Administrator | Akses penuh manajemen user, role, permission, divisi, & posisi. |
| **`budi`** | `password123` | Supervisor | Kepala Divisi IT. Dapat memverifikasi aktivitas bawahan & review kinerja. |
| **`ahmad`** | `password123` | Employee | IT Support (Bawahan Budi). Mencatat aktivitas harian & menerima review. |

> ⚠️ **Catatan Keamanan**: Segera ganti password akun administrator setelah login pertama kali di lingkungan produksi.

---

## 🔍 Panduan Troubleshooting

1. **Error: 413 Request Entity Too Large saat upload lampiran**
   - Pastikan di file konfigurasi Nginx terdapat `client_max_body_size 20M;`.
   - Pastikan juga di `php.ini` nilai `upload_max_filesize = 20M` dan `post_max_size = 25M`.

2. **Notifikasi real-time tidak muncul otomatis tanpa refresh**
   - Cek apakah daemon Supervisor Reverb dan Worker berjalan: `sudo supervisorctl status`.
   - Cek log Reverb: `tail -f /var/www/Work-Activity-Rodja/storage/logs/reverb.log`.
   - Cek log antrean: `tail -f /var/www/Work-Activity-Rodja/storage/logs/worker.log`.

3. **Error Permission 500 saat menulis log atau upload**
   - Jalankan ulang hak akses:
     ```bash
     sudo chown -R www-data:www-data storage bootstrap/cache database
     sudo chmod -R 775 storage bootstrap/cache database
     ```

4. **Worker atau Reverb tidak bisa di-restart**
   - Pastikan nama program supervisor sesuai: `work-activity-reverb` dan `work-activity-worker`.
   - Cek konfigurasi: `sudo supervisorctl reread && sudo supervisorctl update`.
   - Lihat log Supervisor: `sudo tail -f /var/log/supervisor/supervisord.log`.

---

## 💾 Backup & Pemulihan Database

### Backup Manual
```bash
# Backup seluruh database
mysqldump -u [DB_USERNAME] -p [DB_DATABASE] > /var/backups/work_activity_manual.sql

# Restore dari backup
mysql -u [DB_USERNAME] -p [DB_DATABASE] < /var/backups/work_activity_manual.sql
```

### Backup Otomatis (Cron Harian)
Tambahkan entri cron berikut (`sudo crontab -e`) untuk backup otomatis setiap hari pukul 02.00:
```bash
0 2 * * * mysqldump -u [DB_USERNAME] -p'[DB_PASSWORD]' [DB_DATABASE] > /var/backups/work_activity_$(date +\%Y\%m\%d).sql 2>/dev/null
# Hapus backup lebih dari 30 hari
0 3 * * * find /var/backups/ -name 'work_activity_*.sql' -mtime +30 -delete
```

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
