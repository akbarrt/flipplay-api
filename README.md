# FlipPlay API — Modul 4 Pemrograman Web

REST API FlipPlay (Laravel 12 + MySQL). Melanjutkan rancangan entitas Modul 3
(Media dan Pengguna/koleksi media) menjadi API dengan database sungguhan.

> ZIP ini berisi **file inti** (model, migration, controller, dst.). File ini
> harus ditimpa ke project Laravel 12 yang baru dibuat. Langkahnya ada di bawah.

---

## 1. Cara Setup (urut, jangan dilewati)

Prasyarat: PHP >= 8.2, Composer, MySQL aktif (Laragon/XAMPP).

```bash
# 1. Buat project Laravel baru (di folder lain, BUKAN di folder codelab / Modul 3)
composer create-project laravel/laravel flipplay-api
cd flipplay-api

# 2. Aktifkan routing API (WAJIB di Laravel 11/12, kalau tidak routes/api.php tidak terbaca)
php artisan install:api
```

3. **Salin isi ZIP ini ke dalam project** (timpa jika ditanya):
   `app/`, `database/`, `routes/api.php`, dan `postman/`.
   - `routes/api.php` dan `app/Models/User.php` akan menimpa file bawaan.
   - Migration `users` bawaan Laravel TIDAK dihapus, karena dipakai sebagai tabel Pengguna.
   - `database/seeders/DatabaseSeeder.php` bawaan akan tertimpa.

4. Buat database kosong (misal `flipplay_api`) lewat HeidiSQL/phpMyAdmin, lalu edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flipplay_api
DB_USERNAME=root
DB_PASSWORD=
```
Gunakan database **berbeda** dari database codelab.

5. Agar error (422/404) selalu berupa JSON, buka `bootstrap/app.php` dan isi blok
   `withExceptions`:
```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*') || $request->expectsJson());
})
```

6. Jalankan:
```bash
php artisan migrate:fresh --seed
php artisan route:list      # pastikan semua endpoint /api/... muncul
php artisan serve           # API aktif di http://127.0.0.1:8000
```

7. Import `postman/FlipPlay-API.postman_collection.json` ke Postman, lalu uji.

> Selalu kirim header `Accept: application/json` di Postman (sudah ada di collection).

## 2. Push ke GitHub

```bash
git init
git add .
git commit -m "FlipPlay API Modul 4"
git branch -M main
git remote add origin https://github.com/<username>/<nama-repo>.git
git push -u origin main
```
`.env` otomatis di-ignore oleh Laravel (jangan di-push). Tidak perlu hosting;
demo dijalankan lokal dengan `php artisan serve` dan diuji di Postman.

---

## 3. Struktur Data

**media**: `id, title (unique), type (film|series|music|game), genre, release_year, description, timestamps`
**users** (Pengguna, bawaan Laravel): `id, name, email, password, ...`
**media_user** (pivot): `id, media_id, user_id, timestamps` + `unique(media_id, user_id)`

Relasi: `Media belongsToMany User` dan `User belongsToMany Media` (many-to-many).

## 4. Daftar Endpoint

| Method | Endpoint | Fungsi | Status |
|---|---|---|---|
| GET | `/api/media` | Daftar media (paginate 10). Filter: `?search=`, `?type=`, `?genre=` | 200 |
| GET | `/api/media/{id}` | Detail media + pengguna yang mengoleksi | 200 / 404 |
| POST | `/api/media` | Tambah media | 201 / 422 |
| PATCH | `/api/media/{id}` | Ubah sebagian data | 200 / 404 / 422 |
| DELETE | `/api/media/{id}` | Hapus media | 200 / 404 |
| GET | `/api/users` | Daftar pengguna | 200 |
| GET | `/api/users/{id}` | Detail pengguna + koleksinya | 200 / 404 |
| GET | `/api/users/{id}/collection` | Koleksi pengguna | 200 |
| POST | `/api/users/{id}/collection` | Tambah media ke koleksi (`{"media_id": 1}`) | 201 / 409 / 422 |
| DELETE | `/api/users/{id}/collection/{media_id}` | Hapus media dari koleksi | 200 / 404 |

Contoh body POST `/api/media`:
```json
{ "title": "Inception", "type": "film", "genre": "Sci-Fi", "release_year": 2010 }
```
Contoh error 422:
```json
{ "message": "Judul media wajib diisi",
  "errors": { "title": ["Judul media wajib diisi"] } }
```

## 5. Pemetaan ke Rubrik Penilaian (Demo 80%)

| Aspek rubrik | Poin | Lokasi di kode |
|---|---|---|
| CRUD lengkap + status code tepat | 20 | `MediaController.php`, `routes/api.php` (`apiResource`). 201 di `store`, 404 otomatis via Route Model Binding |
| Eloquent Relationship (migration, relasi, eager loading) | 20 | Migration `media` & `media_user`; `belongsToMany` di `Media.php` & `User.php`; `load('users')`, `withCount()` di controller |
| Form Request Validation & 422 | 15 | `StoreMediaRequest.php` (required POST / sometimes PATCH, unique judul, `Rule::in`), `AddToCollectionRequest.php` |
| API Resource + `whenLoaded` | 10 | `MediaResource.php`, `UserResource.php` (`whenLoaded`, `whenCounted`, `whenPivotLoaded`) |
| Custom action (attach/detach) | 10 | `UserController::addToCollection()` (attach + 409 duplikat), `removeFromCollection()` (detach) |
| Pengujian happy & failure path | 5 | `postman/FlipPlay-API.postman_collection.json` (4 folder, 23 request) |

Ketentuan lain: repo GitHub, minimal 2 entitas relasi Eloquent, semua endpoint
terlihat di `php artisan route:list`. Semuanya sudah terpenuhi.

## 6. Hal yang Perlu Dipahami (untuk sesi tanya jawab, 10%)

1. **Kenapa `->parameters(['media' => 'media'])` di route?**
   Laravel meng-singularkan "media" menjadi "medium", sehingga parameter route jadi
   `{medium}` dan tidak cocok dengan `Media $media` di controller (hasilnya 404/error binding).
2. **Kenapa `protected $table = 'media'`?** Agar nama tabel eksplisit dan tidak bergantung pada tebakan Laravel.
3. **Kenapa pivot bernama `media_user`?** Konvensi: singular, urut alfabet.
4. **409 vs 422?** 422 = format input salah (validasi). 409 = input valid tapi bertentangan
   dengan data yang ada (media sudah ada di koleksi).
5. **Dua lapisan anti-duplikat:** cek `exists()` di controller (memberi respons 409 yang rapi)
   dan `unique(['media_id','user_id'])` di database (pengaman terakhir).
6. **`sometimes` vs `required`:** PATCH hanya mengirim field yang diubah, jadi tidak boleh `required`.
7. **`ignore($this->route('media'))`:** saat PATCH, judul milik media itu sendiri tidak dianggap duplikat.
8. **`whenLoaded`:** relasi hanya ditampilkan di JSON jika sudah di-eager-load, sehingga tidak ada query tambahan (N+1).
9. **`cascadeOnDelete`:** menghapus media/pengguna otomatis menghapus barisnya di pivot.
10. **Kenapa tidak pakai `DB::transaction` + `lockForUpdate`?** Itu untuk pola "baca kondisi, validasi, ubah"
    pada data yang sama (seperti peminjaman buku). Di sini duplikat sudah dijaga oleh unique index.

## 7. Troubleshooting

| Masalah | Solusi |
|---|---|
| `route:list` tidak menampilkan `/api/...` | Jalankan `php artisan install:api` |
| Error koneksi DB / `.env` tidak terbaca | Cek MySQL aktif, database sudah dibuat, lalu `php artisan config:clear` |
| 422/404 muncul sebagai halaman HTML / redirect | Tambahkan header `Accept: application/json`, dan lakukan langkah 5 |
| Data dobel atau berantakan | `php artisan migrate:fresh --seed` |
| "Nothing to migrate" setelah edit migration | Gunakan `migrate:fresh` (data boleh hilang) |
| Request Postman "Update/Destroy" 404 | Request itu memakai ID 16 (hasil Store). Jalankan Store dulu atau ganti ID |
| Request "Hapus media tidak ada di koleksi" malah sukses | Ganti ID media yang memang tidak ada di koleksi user tersebut (lihat `GET /users/1/collection`) |

## 8. Urutan Demo yang Disarankan

1. `php artisan route:list` (tunjukkan semua endpoint).
2. CRUD Media: store (201), index + search, show, update, destroy.
3. Failure: judul kosong (422), judul duplikat (422), ID tidak ada (404).
4. Tambah media ke koleksi (201), lihat `GET /users/1` (muncul di koleksi).
5. Tambah media yang sama lagi (409), lalu hapus dari koleksi (200).