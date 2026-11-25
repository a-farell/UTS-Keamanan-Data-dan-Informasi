# SecureInfo - Sistem Informasi Data Terenkripsi

Proyek tugas ini menyimpan judul dan konten dalam bentuk ciphertext menggunakan AES-256-GCM. RSA-2048 digunakan untuk memeriksa kunci privat saat login dan menandatangani data terenkripsi.

## Kode program dan database

Repositori GitHub: [a-farell/UTS-Keamanan-Data-dan-Informasi](https://github.com/a-farell/UTS-Keamanan-Data-dan-Informasi)

File utama: [index.php](./index.php), [admin.php](./admin.php), [crypto.php](./crypto.php), [setup.php](./setup.php), dan [database.sql](./database.sql). Untuk menyertakannya di laporan, cantumkan tautan repositori di atas dan tautan langsung ke file-file tersebut. Perubahan lokal perlu diunggah (push) ke GitHub sebelum tautan menampilkan versi terbaru.

## Instalasi lokal

1. Siapkan PHP dengan ekstensi OpenSSL dan PDO MySQL, serta MySQL/MariaDB.
2. Buat database dengan mengimpor `database.sql` melalui phpMyAdmin atau klien MySQL.
3. Sesuaikan kredensial database di `config.php`.
4. Buka `setup.php`, buat kunci, lalu simpan kunci simetris dan privat secara aman. Keduanya hanya ditampilkan sekali; jangan unggah kunci ke repositori.
5. Buka `index.php` dan masuk menggunakan kedua kunci.

## Perilaku akses

Kombinasi kunci valid membuka admin dengan ciphertext dan plaintext, serta mengizinkan penambahan/penghapusan data. Kunci yang salah tetap membuka dashboard dengan tata letak yang sama dalam mode baca-saja: setiap teks antarmuka dan isi data ditampilkan sebagai ciphertext, plaintext tidak didekripsi, dan perubahan data dinonaktifkan. Ini adalah perilaku demonstrasi, bukan pengganti autentikasi pengguna untuk aplikasi produksi.

Untuk database baru, skrip SQL membuat tabel tanpa kunci atau data contoh. Jika tabel sudah ada, `CREATE TABLE IF NOT EXISTS` akan mempertahankan isinya. Buat kunci melalui `setup.php`.
