# Voice To Text (VTT)

Aplikasi sederhana untuk mengubah suara menjadi teks (Speech-to-Text) dan menyimpannya sebagai catatan. Dibangun menggunakan PHP Native dan Vanilla JavaScript (Web Speech API).

## Fitur
- 🎤 **Rekam Suara ke Teks:** Menggunakan Web Speech API yang akurat (mendukung Bahasa Indonesia).
- 📝 **Simpan Catatan:** Menyimpan hasil transkripsi ke dalam file JSON (tanpa database SQL).
- 📱 **Mobile Friendly:** Tampilan responsif dan optimal untuk HP.
- 🌓 **Dark Mode:** Mendukung mode gelap dan terang.
- 🔒 **Sistem Login:** Login sederhana untuk keamanan catatan.

## Instalasi

1.  **Clone atau Download** repository ini.
2.  **Upload** ke server hosting atau `htdocs` (XAMPP) kamu.
3.  **Pastikan Permission Folder (PENTING!):**
    Agar aplikasi bisa menyimpan catatan, kamu harus mengubah permission folder `data` dan isinya.
    - Folder `data/` -> Permission **777** (Read/Write/Execute untuk semua).
    - File `data/users.json` & `data/notes.json` -> Permission **666** atau **777**.
    - *Jika di Hosting cPanel:* Klik kanan folder `data` -> Change Permissions -> Centang semua kotak Write.

## Akun Default
- **Username:** `admin`
- **Password:** `admin123`
*(Silakan ubah password setelah login atau edit file `data/users.json`)*

## Troubleshooting & Tips (Kendala Umum)

Jika kamu mengalami masalah saat deploy, cek solusi berikut (berdasarkan pengalaman pengembangan):

### 1. Gagal Simpan Catatan (`ERR_CONNECTION_CLOSED` atau Gagal Write)
**Penyebab:** Server PHP tidak memiliki izin untuk menulis ke file JSON.
**Solusi:** Ubah permission folder `data` menjadi **777**. Di banyak shared hosting, PHP berjalan sebagai user yang berbeda, jadi butuh akses "World Writeable".

### 2. Teks Dobel di HP (Contoh: "cek cek cek 123")
**Penyebab:** Bug pada Google Chrome Android saat menggunakan mode `continuous`.
**Solusi:** Aplikasi ini sudah dilengkapi fitur **Auto-Restart**. Script akan otomatis mematikan dan menyalakan ulang perekaman setiap kali kamu berhenti bicara sejenak. Ini mencegah teks menumpuk/dobel.

### 3. Microphone Tidak Jalan
**Penyebab:** Browser memblokir akses microphone jika website tidak menggunakan HTTPS.
**Solusi:**
- Pastikan website kamu diakses via **HTTPS** (bukan HTTP).
- Izinkan akses microphone saat browser meminta izin.

### 4. Perubahan Tidak Muncul (Cache)
**Penyebab:** Browser HP sering menyimpan cache file JavaScript lama secara agresif.
**Solusi:** Aplikasi ini sudah menggunakan *cache busting* (versi otomatis). Namun jika masih bandel, coba "Clear Cache" di browser HP kamu.

---
Dibuat dengan ❤️ untuk kemudahan mencatat.
