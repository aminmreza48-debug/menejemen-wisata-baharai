@echo off
title BahariGIS - Web Server Launcher
color 0b

REM Pastikan working directory selalu di folder file .bat ini berada
cd /d "%~dp0"

echo ==============================================================
echo    BahariGIS -- Sistem Informasi Geografis Wisata Bahari
echo    Manajemen Web GIS (Semester 5)
echo ==============================================================
echo.

REM Deteksi lokasi PHP (Cek XAMPP atau PATH sistem)
set PHP_BIN=
if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
) else (
    where php >nul 2>&1
    if %errorlevel% equ 0 (
        set "PHP_BIN=php"
    )
)

if "%PHP_BIN%"=="" (
    echo [ERROR] PHP tidak ditemukan!
    echo Silakan pastikan XAMPP terpasang di C:\xampp atau tambahkan PHP ke System PATH.
    echo.
    pause
    exit /b 1
)

set PORT=8000

REM Cek apakah port 8000 sudah aktif
netstat -ano | findstr /R /C:":8000 .*LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Server BahariGIS SUDAH AKTIF di: http://localhost:%PORT%
    echo [INFO] Membuka browser langsung ke aplikasi...
    start "" "http://localhost:%PORT%"
    echo.
    echo Server tetap berjalan normal. Tekan sembarang tombol untuk menutup jendela ini...
    pause >nul
    exit /b 0
)

echo [OK] Menggunakan PHP: %PHP_BIN%
echo [INFO] Menjalankan server lokal di: http://localhost:%PORT%
echo.
echo ==============================================================
echo   Petunjuk Penggunaan:
echo   1. Website: http://localhost:%PORT%
echo   2. Login Admin: http://localhost:%PORT%/admin/login.php
echo      Email: admin@baharigis.id
echo      Password: admin123
echo   3. Biarkan jendela ini tetap terbuka selama menggunakan aplikasi.
echo ==============================================================
echo.

REM Buka browser otomatis setelah 1 detik
start "" "http://localhost:%PORT%"

REM Jalankan PHP Built-in Server
"%PHP_BIN%" -S localhost:%PORT%
pause
