# Database ERD Analysis - Daiku Interior

## Current Database Structure

### Core Tables

1. USERS
   - id (PK)
   - nama
   - email (unique)
   - password
   - role (admin/designer/pelanggan)
   - alamat
   - no_telp
   - timestamps

2. KATALOG
   - id (PK)
   - category_id (FK -> categories.id)
   - nama_desain
   - kategori (legacy)
   - deskripsi
   - harga_estimasi
   - gambar_utama
   - galeri_gambar (JSON)
   - product_spots (JSON)
   - style_tags
   - room_size
   - inspiration_story
   - timestamps

3. CATEGORIES
   - id (PK)
   - name
   - slug
   - description
   - icon
   - image
   - parent_id (FK -> categories.id)
   - sort_order
   - is_active
   - timestamps

4. KONSULTASI
   - id (PK)
   - user_id (FK -> users.id)
   - nama
   - email
   - no_telp
   - jenis_konsultasi
   - jenis_ruangan
   - budget_range
   - timeline
   - luas_ruangan
   - gaya_preferensi
   - deskripsi_kebutuhan
   - upload_foto (JSON)
   - tanggal_konsultasi
   - waktu_konsultasi
   - status
   - catatan_admin
   - timestamps

5. RFQ (legacy B2B)
   - id (PK)
   - id_user (FK -> users.id)
   - id_katalog (FK -> katalog.id)
   - tanggal_pengajuan
   - kebutuhan_proyek
   - status_rfq
   - timestamps

6. PEMESANAN
   - id (PK)
   - id_rfq (FK -> rfq.id, nullable)
   - katalog_id (FK -> katalog.id, nullable)
   - id_user (FK -> users.id)
   - tanggal_pesan
   - status_pemesanan
   - total_harga
   - jenis_proyek
   - jenis_bangunan
   - luas_area
   - jumlah_ruangan
   - gaya_desain_preferensi
   - warna_dominan
   - deskripsi_keinginan_desain
   - upload_denah_foto (JSON)
   - timestamps

7. INVOICES
   - id (PK)
   - id_pemesanan (FK -> pemesanan.id)
   - total_tagihan
   - status_invoice
   - tanggal_jatuh_tempo
   - timestamps

8. PEMBAYARAN
   - id (PK)
   - id_invoice (FK -> invoices.id)
   - jumlah_bayar
   - tanggal_bayar
   - metode_bayar
   - bukti_pembayaran
   - timestamps

9. STATUS_TRACKING
   - id (PK)
   - id_pemesanan (FK -> pemesanan.id)
   - status
   - tanggal_update
   - catatan
   - timestamps

## Relationships

1. USER -> KONSULTASI (1:N)
2. USER -> RFQ (1:N)
3. USER -> PEMESANAN (1:N)
4. KATALOG -> RFQ (1:N)
5. CATEGORIES -> KATALOG (1:N)
6. PEMESANAN -> INVOICES (1:1)
7. INVOICES -> PEMBAYARAN (1:N)
8. PEMESANAN -> STATUS_TRACKING (1:N)
9. RFQ -> PEMESANAN (1:1, optional legacy)
10. KATALOG -> PEMESANAN (1:N, optional)

## Issues and Notes

- RFQ is still present as a legacy B2B flow and remains optional.
- Katalog now supports both category_id (new) and kategori (legacy) for compatibility.
- Pemesanan stores total_harga and optional katalog_id for direct B2C catalog orders.

## Suggested B2C Flow

USER -> KONSULTASI -> PEMESANAN -> INVOICES -> PEMBAYARAN
                      |
                      -> KATALOG (optional)

