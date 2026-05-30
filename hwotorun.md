# Panduan Menjalankan Project di CachyOS (Arch Linux)

Panduan ini berisi langkah-langkah untuk menjalankan *Friendship Group Web Application* menggunakan MariaDB dan *built-in web server* bawaan PHP agar lebih praktis untuk environment development.

## 1. Install Dependencies (PHP & MariaDB)

Buka terminal dan install PHP beserta MariaDB menggunakan `pacman`.

```bash
sudo pacman -Syu php mariadb
```

## 2. Konfigurasi MariaDB

Karena ini adalah instalasi baru MariaDB, kamu perlu menginisialisasi direktori database terlebih dahulu, lalu menjalankan *service*-nya.

```bash
# Inisialisasi direktori database
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql

# Aktifkan dan jalankan service MariaDB
sudo systemctl enable --now mariadb
```

*(Opsional tapi disarankan)*: Jalankan `sudo mariadb-secure-installation` jika kamu ingin mengatur password root database. Jika kamu tidak mengaturnya, kamu bisa menggunakan user `root` tanpa password (seperti settingan default di `config/database.php` project ini).

## 3. Aktifkan Ekstensi PDO MySQL di PHP

Di Arch/CachyOS, beberapa ekstensi PHP secara default dinonaktifkan. Kamu harus mengaktifkan ekstensi PDO MySQL agar PHP bisa berkomunikasi dengan MariaDB.

Gunakan text editor favoritmu (misalnya `nano`) untuk mengedit `php.ini`:
```bash
sudo nano /etc/php/php.ini
```

Cari dan hilangkan tanda titik koma (`;`) pada baris berikut:
```ini
extension=pdo_mysql
extension=mysqli
```
Simpan file tersebut.

## 4. Import Schema Database

Arahkan terminalmu ke folder project, lalu import file `database/schema.sql` ke MariaDB.

```bash
# Pindah ke direktori project
cd /home/dibow/Documents/Projects/AliansiAntah-Berantah

# Jalankan script SQL (tekan enter jika ditanya password dan kamu tidak mensetnya)
mariadb -u root -p < database/schema.sql
```
*Catatan: Perintah di atas akan secara otomatis membuat database `friendship_group_db` beserta seluruh tabelnya.*

## 5. Jalankan Local Development Server

Setelah database siap, kamu bisa menjalankan server bawaan PHP langsung dari root folder project.

```bash
# Pastikan kamu masih berada di root folder project
php -S localhost:8000
```

## 6. Buka di Browser

Project sekarang sudah berjalan! Buka browsermu dan kunjungi:
👉 **http://localhost:8000**

---

### Tips Pengujian:
- Klik **Register**, lalu gunakan Passcode berikut (yang diatur di `config/app.php`):
  - Passcode Admin: `admin-secret-code`
  - Passcode Member: `member-secret-code`
- Jika kamu menemui error terkait database, pastikan kredensial di file `config/database.php` cocok dengan settingan MariaDB kamu di CachyOS (defaultnya user `root` tanpa password).
