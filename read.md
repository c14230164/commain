# Commain — Qualitative Data Analysis

> Platform manajemen dan analisis data kualitatif yang dirancang untuk menyederhanakan alur kerja penelitian — mulai dari pendefinisian pertanyaan, input data responden, hingga visualisasi distribusi variabel.

**Commain** adalah aplikasi web berbasis **Laravel 12** yang membantu tim riset kualitatif mengelola banyak proyek, banyak responden (kertas jawaban / *paper*), dan banyak variabel jawaban secara terstruktur. Aplikasi ini menyediakan workflow dua langkah (*Input Questions* → *Input Castor*) lengkap dengan autocomplete, ringkasan real-time, import/export proyek dalam format JSON, serta fitur penggabungan (*merge*) antar proyek.

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Direktori](#struktur-direktori)
- [Skema Database](#skema-database)
- [Alur Aplikasi](#alur-aplikasi)
- [Daftar Route](#daftar-route)
- [Instalasi & Setup](#instalasi--setup)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Testing](#testing)
- [Konvensi & Catatan](#konvensi--catatan)

---

## Tentang Proyek

Riset kualitatif dengan banyak responden (misalnya coding kuesioner terbuka) sering kali menghadapi tantangan: variabel jawaban tidak terstruktur, data sulit digabung antar enumerator, dan proses review memakan waktu. **Commain** menjawab itu dengan:

- Setiap **Project** memiliki kumpulan **Question** tetap (jumlahnya ditentukan saat membuat proyek).
- Setiap **Question** memiliki banyak **Variable** (label/kategori jawaban) yang terbentuk otomatis dari input pengguna (*firstOrCreate*).
- Setiap **Respondent** (koresponden = satu kertas jawaban) menghubungkan dirinya ke banyak Variable lintas Question.
- Review页面 menampilkan **distribusi variabel + persentase** untuk setiap soal, lengkap dengan diagram pie (Chart.js).

Nama folder **`Commain/`** yang kosong di root adalah *placeholder* dokumentasi/asset tambahan (saat ini belum berisi file).

---

## Fitur Utama

| Fitur | Deskripsi |
|---|---|
| **Manajemen Proyek** | Buat proyek baru dengan nama opsional (auto-number) dan jumlah soal (1–99). |
| **Setup Pertanyaan** | Wizard 2 langkah: input soal → input data responden. |
| **Input Castor (Responden)** | Form multi-variabel per soal dengan **autocomplete** dari variabel yang sudah pernah dipakai. |
| **Validasi Paper ID** | Deteksi duplikat `paper_id` per proyek; pengguna dapat memilih membuka data lama. |
| **Rename Variabel** | Mengganti nama variabel, dengan logika **merge otomatis** jika nama baru sudah ada di soal yang sama. |
| **Edit / Hapus Responden** | Muat ulang data responden sebelumnya, edit, atau hapus dengan konfirmasi. |
| **Review & Statistik** | Tabel distribusi variabel + persentase per soal, lengkap dengan **chart pie** per soal. |
| **Import / Export JSON** | Backup penuh satu proyek ke JSON (soal + variabel + responden + relasi) dan restore. |
| **Merge Proyek** | Gabungkan ≥ 2 proyek dengan validasi: jumlah soal sama & tidak ada duplikat `paper_id`. |
| **Summary Real-time** | Sidebar kanan di halaman setup menampilkan agregasi variabel per soal (live update via AJAX). |

---

## Tech Stack

### Backend
- **PHP 8.2+**
- **Laravel 12.x** (framework utama)
- **Eloquent ORM** + **SQLite** (default; konfigurable ke MySQL/Postgres via `.env`)
- **Pail** (log viewer CLI)
- **Pint** (code style fixer)

### Frontend
- **Vite 6** (bundler)
- **Tailwind CSS 4** (via `@tailwindcss/vite`)
- **Alpine.js 3** (interaktivitas ringan, dimuat via CDN)
- **Axios** (HTTP client untuk endpoint AJAX)
- **Chart.js** (visualisasi pie chart di halaman review, dimuat via CDN)

### Testing
- **PHPUnit 11** (Unit + Feature)
- **Mockery**

### Dev Tooling
- `composer.json` script `dev` menjalankan `php artisan serve` + `queue:listen` + `pail` + `vite` secara paralel via `concurrently`.

---

## Struktur Direktori

```
commain/
├── app/
│   ├── Http/Controllers/
│   │   ├── Controller.php
│   │   ├── ProjectController.php
│   │   └── ProjectExportImportController.php
│   └── Models/
│       ├── Project.php
│       ├── Question.php
│       ├── Respondent.php
│       ├── Variable.php
│       └── User.php
├── bootstrap/app.php
├── config/                 # app, auth, cache, database, filesystems, logging, mail, queue, services, session
├── database/
│   ├── factories/
│   ├── migrations/         # 4 migrasi custom (projects, questions, respondents, variables, pivot)
│   └── seeders/
├── public/                 # entry point + build assets
├── resources/
│   ├── css/app.css
│   ├── js/app.js, bootstrap.js
│   └── views/
│       ├── layouts/app.blade.php
│       ├── home.blade.php
│       └── projects/
│           ├── index.blade.php
│           ├── setup.blade.php
│           └── review.blade.php
├── routes/
│   ├── console.php
│   └── web.php
├── storage/
├── tests/
│   ├── Feature/ExampleTest.php
│   └── Unit/ExampleTest.php
├── Commain/                # folder placeholder (kosong)
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
└── .env.example
```

---

## Skema Database

Empat tabel utama + satu tabel pivot. Semua foreign key memakai `onDelete('cascade')` kecuali `users` (standar Laravel).

```
projects
├── id
├── name            (string)
├── question_count  (unsignedTinyInteger, 1–99)
└── timestamps

questions
├── id
├── project_id      (FK → projects)
├── order           (unsignedInteger, 1..question_count)
├── text            (text)
└── timestamps

variables
├── id
├── question_id     (FK → questions)
├── name            (string)
└── timestamps

respondents
├── id
├── project_id      (FK → projects)
├── paper_id        (string)
├── castor_name     (string, nullable)
└── timestamps
└── UNIQUE (project_id, paper_id)

respondent_variable  (pivot)
├── respondent_id   (FK → respondents)
└── variable_id     (FK → variables)
└── PRIMARY KEY (respondent_id, variable_id)
```

**Relasi Eloquent:**

- `Project hasMany Question` / `Question belongsTo Project`
- `Project hasMany Respondent` / `Respondent belongsTo Project`
- `Question hasMany Variable` / `Variable belongsTo Question`
- `Respondent belongsToMany Variable` (via `respondent_variable`)

---

## Alur Aplikasi

1. **Halaman Home** (`/`) — Landing + tombol *New Project* (membuka modal pilih nama & jumlah soal).
2. **Daftar Proyek** (`/projects`) — Tabel proyek; mendukung **Edit / Review / Export / Delete**, **Import JSON**, dan **Merge** (centang ≥ 2 proyek).
3. **Setup Proyek** (`/projects/{id}/setup`) — Dua langkah:
   - **Step 1: Input Questions** — Form textarea sejumlah `question_count`. Simpan via AJAX → `POST /projects/{id}/questions`.
   - **Step 2: Input Castor** — Form input `paper_id` + `castor_name`, lalu untuk setiap soal tambahkan variabel jawaban. Sidebar kanan menampilkan **summary real-time**.
4. **Review** (`/projects/{id}/review`) — Statistik agregat per soal: tabel count + persentase, dan pie chart (Chart.js).
5. **Export** (`GET /projects/{id}/export`) — Unduh JSON berisi project, questions, variables, respondents, dan relasi pivot.
6. **Import** (`POST /projects/import`) — Unggah JSON; akan dibuat proyek baru (suffix `- Imported` jika nama bentrok).
7. **Merge** (`POST /projects/merge`) — Validasi jumlah soal + duplikat `paper_id`, lalu buat proyek baru hasil konsolidasi dengan cache variabel per soal (case-insensitive).

---

## Daftar Route

Semua route didefinisikan di `routes/web.php` di bawah prefix `projects` (kecuali `/`).

| Method | URI | Name | Handler |
|---|---|---|---|
| GET | `/` | `home` | `ProjectController@home` |
| GET | `/projects` | `projects.index` | `ProjectController@index` |
| POST | `/projects` | `projects.store` | `ProjectController@store` |
| POST | `/projects/import` | `projects.import` | `ProjectExportImportController@import` |
| POST | `/projects/merge` | `projects.merge` | `ProjectExportImportController@merge` |
| GET | `/projects/{project}/export` | `projects.export` | `ProjectExportImportController@export` |
| GET | `/projects/{project}/setup` | `projects.setup` | `ProjectController@setup` |
| GET | `/projects/{project}/review` | `projects.review` | `ProjectController@review` |
| POST | `/projects/{project}/questions` | `projects.questions.store` | `ProjectController@storeQuestions` |
| POST | `/projects/{project}/respondent` | `projects.respondent.store` | `ProjectController@storeRespondent` |
| GET | `/projects/{project}/summary` | `projects.summary` | `ProjectController@getSummary` |
| GET | `/projects/{project}/respondents` | `projects.respondents.list` | `ProjectController@getRespondents` |
| GET | `/projects/{project}/respondent/{respondent}` | `projects.respondent.full` | `ProjectController@getRespondentFull` |
| DELETE | `/projects/{project}/respondent/{respondent}` | `projects.respondent.delete` | `ProjectController@deleteRespondent` |
| GET | `/projects/questions/{question}/suggestions` | `projects.questions.suggestions` | `ProjectController@getSuggestions` |
| GET | `/projects/variables/{variable}/respondents` | `projects.variables.respondents` | `ProjectController@getVariableRespondents` |
| POST | `/projects/variables/{variable}/rename` | `projects.variables.rename` | `ProjectController@renameVariable` |
| DELETE | `/projects/{project}/delete` | `projects.destroy` | `ProjectController@destroy` |

> **Catatan:** Rute `projects.destroy` dideklarasikan dengan method `DELETE` di belakang, namun URI masih di bawah prefix `projects` — sehingga URL efektifnya adalah `DELETE /projects/{project}/delete` (lihat definisi di `routes/web.php`).

---

## Instalasi & Setup

### Prasyarat
- PHP ≥ 8.2
- Composer
- Node.js & npm
- SQLite (default) atau MySQL/PostgreSQL

### Langkah

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin env & generate app key
cp .env.example .env
php artisan key:generate

# 3. (Opsional) Buat database SQLite
touch database/database.sqlite

# 4. Jalankan migrasi
php artisan migrate

# 5. Install dependensi frontend & build asset
npm install
npm run build    # production
# atau
npm run dev      # development dengan HMR
```

> Skrip `post-create-project-cmd` di `composer.json` akan otomatis membuat `.env`, file SQLite, dan menjalankan migrasi **graceful** saat `composer create-project` pertama kali.

---

## Menjalankan Aplikasi

### Mode Development (server + queue + log + vite sekaligus)

```bash
composer run dev
```

Skrip ini menjalankan 4 proses paralel:
- `php artisan serve` — web server
- `php artisan queue:listen --tries=1` — worker antrian
- `php artisan pail --timeout=0` — log streamer
- `npm run dev` — Vite dev server dengan HMR

### Mode Biasa

```bash
php artisan serve    # http://localhost:8000
npm run dev          # terminal terpisah untuk HMR
```

---

## Testing

```bash
php artisan test
# atau
vendor/bin/phpunit
```

Konfigurasi `phpunit.xml` mengatur:
- `APP_ENV=testing`
- `CACHE_STORE=array`
- `MAIL_MAILER=array`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=array`
- `BCRYPT_ROUNDS=4` (lebih cepat untuk test)

> DB untuk testing masih default (sqlite file). Untuk in-memory, uncomment baris `DB_CONNECTION=sqlite` & `DB_DATABASE=:memory:` di `phpunit.xml`. Saat ini hanya ada `ExampleTest` boilerplate sebagai titik awal.

---

## Konvensi & Catatan

- **Session/Queue/Cache** default menggunakan driver `database` — pastikan tabel terkait (sessions, jobs, cache) sudah ada dari migrasi `0001_01_01_*`.
- **Vite HMR host** di `vite.config.js` di-set ke `10.92.180.174` (IP laptop saat development via hotspot HP). Sesuaikan dengan IP perangkat Anda bila perlu.
- **Autocomplete variabel** hanya menampilkan variabel yang pernah dibuat di soal yang sama (`Variable::where('question_id', $q->id)->distinct()->pluck('name')`).
- **Merge proyek** menggunakan *case-insensitive* cache untuk variabel (`strtolower($varName)`) sehingga ` "Pendidikan"`, `"PENDIDIKAN"`, dan `"pendidikan"` akan dianggap variabel yang sama.
- **Rename variabel** akan otomatis melakukan **merge** jika nama baru sudah ada di soal yang sama (respondent dipindahkan, variabel lama dihapus).
- **Folder `Commain/`** di root adalah placeholder (saat ini kosong) — dapat digunakan untuk dokumentasi tambahan atau aset di masa depan.
- **File `laravel`** (≈ 122 KB) di root adalah executable Laravel scheduler daemon yang dibuat otomatis oleh `php artisan schedule:work` atau sejenisnya; aman dihapus bila tidak digunakan.

---

## Lisensi

Proyek ini berdiri di atas Laravel framework yang berlisensi **MIT**. Lihat [LICENSE](https://opensource.org/licenses/MIT) untuk detail.

---

© Commain Systems — dibangun untuk peneliti kualitatif.
