# Deploy Daiku ke Render

Konfigurasi ini menjalankan Laravel melalui Docker pada Render Free, serta PostgreSQL dan bucket privat Supabase Free. Struktur ERD bisnis tidak diubah.

## Sebelum membuat layanan

1. Pastikan seluruh perubahan proyek sudah di-push ke GitHub repository `lingga-dwi/proyekakhir`.
2. Buat project gratis di [Supabase](https://supabase.com/dashboard), lalu catat **Database URL** dari Connect dan detail S3 dari **Storage > Configuration > S3**.
3. Di Supabase Storage, aktifkan S3 protocol, buat access key khusus server, lalu buat bucket bernama `payment-proofs` dengan akses **Private**. Jangan membuat bucket ini Public.
4. Buat `APP_KEY` lokal dengan perintah berikut, lalu simpan hasilnya untuk dimasukkan ke Render:

   ```powershell
   php artisan key:generate --show
   ```

5. Tentukan nomor WhatsApp Daiku dalam format internasional tanpa tanda `+`, misalnya `628xxxxxxxxxx`.

## Buat layanan di Render

1. Masuk ke [Render Dashboard](https://dashboard.render.com/), pilih **New > Blueprint**.
2. Hubungkan repository GitHub `lingga-dwi/proyekakhir`, lalu pilih file `render.yaml`.
3. Saat Render meminta nilai rahasia, isi:
   - `APP_KEY`: hasil langkah sebelumnya.
   - `APP_URL`: URL layanan yang dipilih, misalnya `https://daiku-interior.onrender.com`.
   - `DAIKU_WHATSAPP_NUMBER`: nomor WhatsApp Daiku.
   - `DB_URL`: Database URL Supabase (gunakan koneksi PostgreSQL dengan `sslmode=require`).
   - `PAYMENT_EVIDENCE_KEY`, `PAYMENT_EVIDENCE_SECRET`, `PAYMENT_EVIDENCE_REGION`, `PAYMENT_EVIDENCE_ENDPOINT`: detail S3 dari Supabase Storage. Endpoint berbentuk `https://<project-ref>.storage.supabase.co/storage/v1/s3`.
4. Tinjau rencana layanan. Web service memakai paket **Free** dan tidak memasang Persistent Disk.
5. Klik **Apply**. Render menjalankan migrasi pada PostgreSQL Supabase lalu men-deploy website.

## Setelah deploy pertama

1. Buka URL Render dan cek halaman beranda, login, katalog, konsultasi, serta dashboard admin.
2. Jika URL akhir berbeda dari nilai `APP_URL`, ubah variabel itu melalui **Environment** pada service, lalu lakukan redeploy.
3. Buat satu akun admin melalui registrasi awal, lalu ubah rolenya langsung pada database hanya bila belum ada admin. Jangan pernah membagikan kredensial database atau `APP_KEY`.
4. Uji unggah bukti pembayaran dan redeploy sekali. File harus tetap tersedia karena tersimpan di bucket privat Supabase, bukan di filesystem Render.

## Memindahkan data dari XAMPP (opsional)

Untuk demonstrasi PA, lebih aman memulai dengan database kosong dan membuat data uji baru di Supabase. Jika data lokal benar-benar perlu dipertahankan, buat backup MySQL terlebih dahulu dan lakukan migrasi MySQL-ke-PostgreSQL secara terpisah; jangan menjalankan import SQL MySQL langsung ke PostgreSQL karena sintaksnya berbeda.

## Catatan operasional

- Render menjalankan migrasi setiap deploy dengan `php artisan migrate --force`.
- Data bukti pembayaran ada di bucket privat Supabase, sedangkan katalog bawaan tetap berasal dari repository.
- Perubahan `.env` lokal tidak ikut terunggah karena file tersebut sengaja diabaikan Git. Semua rahasia produksi dimasukkan di Render Environment.
- Layanan gratis cocok untuk demo/PA, bukan operasional komersial: Render dapat tidur setelah 15 menit tanpa trafik dan Supabase dapat menjeda project setelah sekitar 7 hari aktivitas rendah.
