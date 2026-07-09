# 🚀 Panduan Deploy Commain ke cPanel

Dokumen ini menjelaskan langkah-langkah deploy project Laravel 12 **Commain** ke shared hosting cPanel dengan layout aman (aplikasi di luar `public_html`).

---

## 📁 Layout di Server

```
~/
├── commain/                  # ← folder aplikasi Laravel (di LUAR public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/               # ← ini TIDAK dipakai di cPanel (lihat public_html di bawah)
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── cpanel.yaml
│
├── public_html/              # ← document root, hanya berisi stub + asset
│   ├── index.php             # ← stub front controller (dari public/index.cpanel.php)
│   ├── .htaccess             # ← dari public/.htaccess.cpanel
│   ├── favicon.ico
│   ├── robots.txt
│   └── build/                # ← hasil `npm run build` (JS + CSS bundle)
│
└── ...
```

---

## ✅ Persiapan Lokal (SEBELUM upload)

```bash
# 1. Pastikan .env production sudah siap (lihat bagian .env di bawah)
# 2. Build asset lokal (opsional — bila server tidak punya Node.js)
npm install && npm run build

# 3. Commit perubahan (opsional, bila pakai Git)
git add .
git commit -m "Deploy production"
```

---

## 🛠️ Setup Pertama Kali di Server

### 1. Buat Database MySQL
Login ke **cPanel → MySQL® Databases**:
- Buat database, misal: `<CPANEL_USER>_commain`
- Buat user, misal: `<CPANEL_USER>_commain`
- Tambahkan user ke database dengan **privilege ALL PRIVILEGES**
- Catat: nama DB, user, password

### 2. Pastikan Node.js & Composer Tersedia
Login ke **cPanel → Terminal** (atau SSH), cek:
```bash
php -v            # harus >= 8.2
composer --version
node --version    # untuk build Vite
npm --version
```

> ⚠️ **Penting:** Ubah `<CPANEL_USER>` di `cpanel.yaml` dengan username cPanel Anda (case-sensitive, sama dengan prefix database). Juga periksa path Node.js — di beberapa cPanel, binary ada di `/opt/cpanel/ea-nodejs22/bin/node` (cek dengan `which node`).

### 3. Buat Folder `commain/`
```bash
cd ~
mkdir -p commain
```

### 4. Upload Source Code
**Opsi A — Git (disarankan):**
```bash
cd ~/commain
git clone https://github.com/<user>/commain.git .
# atau jika repo sudah ada, cukup: git pull
```

**Opsi B — Upload Manual via File Manager / FTP:**
- Upload SEMUA file & folder project ke `~/commain/` **KECUALI** `public/` (lihat langkah 5)
- File yang harus ada: `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/` (otomatis dari composer install), `artisan`, `composer.json`, `package.json`, `cpanel.yaml`

### 5. Setup `public_html/`
Upload ke `~/public_html/`:
- `public/index.cpanel.php` → rename menjadi **`index.php`**
- `public/.htaccess.cpanel` → rename menjadi **`.htaccess`**
- `public/favicon.ico` → langsung
- `public/robots.txt` → langsung
- `public/build/` → langsung (folder hasil build)

> 💡 File `public/index.cpanel.php` sudah berisi `dirname(__DIR__) . '/commain'` — ini otomatis menunjuk ke `~/commain/` selama folder `commain/` ada di SEJAJAR dengan `public_html/`.

### 6. Konfigurasi `.env`
Buat/edit `~/commain/.env`:
```env
APP_NAME=Commain
APP_ENV=production
APP_KEY=                                 # ← akan di-generate otomatis oleh cpanel.yaml
APP_DEBUG=false
APP_URL=https://commain.yourdomain.com   # ← GANTI dengan domain Anda

# MySQL — GANTI sesuai langkah 1
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<CPANEL_USER>_commain
DB_USERNAME=<CPANEL_USER>_commain
DB_PASSWORD=<password_yang_anda_buat>

# Production driver (BUKAN database/file default untuk performa)
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
BROADCAST_CONNECTION=log
LOG_CHANNEL=stack
LOG_LEVEL=error
FILESYSTEM_DISK=local
```

> **Penting:** Ubah `SESSION_DRIVER`, `QUEUE_CONNECTION`, dan `CACHE_STORE` dari `database` (default di `.env.example`) ke `file` di production — lebih hemat 1 query per request dan tidak butuh cron job queue worker di shared hosting. Alternatifnya tetap `database` kalau Anda ingin tracking session/queue di MySQL.

---

## ▶️ Menjalankan Deploy

### Opsi 1 — Via cPanel GUI (Git Version Control)
1. Login cPanel → **Git Version Control**
2. Create repository pointing ke repo Git Anda, path = `~/commain/`
3. Klik **Update** untuk pull + jalankan `cpanel.yaml`

### Opsi 2 — Via Terminal
```bash
cd ~/commain
# Trigger deploy tasks manual (untuk testing pertama kali)
bash -c "$(awk '/^tasks:/,/^[a-z]+:/' cpanel.yaml | head -n -1)"
# Atau jalankan command satu per satu (lihat isi cpanel.yaml)

# Atau cara paling simpel: jalankan SEMUA command tasks
# langsung di terminal untuk verifikasi:
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
npm ci --no-audit --no-fund
npm run build
[ -f .env ] || cp .env.example .env
php artisan key:generate --ansi --force
php artisan storage:link
php artisan migrate --force --no-interaction
php artisan optimize
chmod -R 775 storage bootstrap/cache
```

### Opsi 3 — Trigger via Git Push Hook
Login cPanel → **Git Version Control** → **Manage Hooks** → tambahkan hook yang menjalankan:
```bash
cd ~/commain && /usr/local/bin/php $(awk '/id:.*"composer-install"/,/commands:/' cpanel.yaml | ...)
```
> Implementasi paling bersih: gunakan **Deploy** button di Git Version Control, yang otomatis membaca `cpanel.yaml` di root repo.

---

## 🔄 Update / Re-deploy

Untuk deployment berikutnya:
1. Push perubahan ke Git:
   ```bash
   git push origin main
   ```
2. Di cPanel → **Git Version Control** → klik **Update** (atau biarkan webhook otomatis)
3. `cpanel.yaml` akan otomatis menjalankan semua task.

---

## 🐛 Troubleshooting

| Masalah | Solusi |
|---|---|
| **500 Internal Server Error** | Cek `~/commain/storage/logs/laravel.log`. Biasanya permission `storage/` atau `.env` belum ada / `APP_KEY` kosong. |
| **Blank page / tidak ada error** | Tambahkan `APP_DEBUG=true` di `.env` (sementara, jangan lupa kembalikan ke `false`). |
| **`vendor/autoload.php` not found** | Jalankan `composer install` di `~/commain/`. |
| **Asset 404 (CSS/JS)** | Pastikan `~/public_html/build/` ada isinya (jalankan `npm run build`). |
| **Permission denied di storage/** | Jalankan `chmod -R 775 storage bootstrap/cache`. Di cPanel, group harus = username cPanel. |
| **Migration error** | Pastikan kredensial MySQL di `.env` benar dan database sudah dibuat. |
| **`npm: command not found`** | Install Node.js via cPanel → **Setup Node.js App**, lalu catat absolute path binary `node` (mis. `/home/<user>/nodevenv/commain/22/bin/node`) dan set PATH di awal cpanel.yaml. |
| **Symlink `public/storage` gagal** | Di shared hosting symlink kadang diblokir. Alternatif: hapus `public/storage` dan arahkan `FILESYSTEM_DISK=public` + jalankan `php artisan storage:link` (jika gagal, upload manual). |

---

## 🔐 Keamanan Pasca-Deploy

```bash
# Set permission ketat untuk file sensitif
chmod 644 .env
chmod 644 cpanel.yaml

# Hapus cpanel.yaml dari public_html (kalau tidak sengaja ter-upload)
# (sudah di-exclude di excludes list, tapi cek ulang via File Manager)
```

Tambahan yang direkomendasikan:
- Aktifkan **Hotlink Protection** di cPanel
- Pasang **SSL/TLS** (Let's Encrypt gratis) dan uncomment rewrite HTTPS di `.htaccess`
- Backup database terjadwal via **cPanel → Cron Jobs + mysqldump**

---

## 📝 Catatan Penting

- **Folder `public/` bawaan Laravel** (`~/commain/public/`) **TIDAK dipakai** di server. Yang dipakai hanya `~/public_html/` dengan stub dari `public/index.cpanel.php`. Anda boleh menghapus `~/commain/public/` untuk menghindari kebingungan, **TAPI** simpan `index.cpanel.php` & `.htaccess.cpanel` di repositori lokal.
- **Node.js binary path** bervariasi antar server cPanel. Jika `npm` tidak ditemukan saat task `npm-install` atau `npm-build`, tambahkan di awal `cpanel.yaml`:
  ```yaml
  - id: "set-node-path"
    commands:
      - "export PATH=/home/<CPANEL_USER>/nodevenv/commain/22/bin:$PATH"
  ```
- **File `laravel`** (122 KB) di root repo adalah executable scheduler daemon — aman dihapus dari repo dengan `git rm --cached laravel` (sudah masuk `.gitignore`).
