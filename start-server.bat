@echo off
title BahariGIS - PHP Web Server
color 0b
cd /d "%~dp0"

REM Cari path PHP dari XAMPP atau System PATH
set "PHP_BIN="
if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
) else (
    where php >nul 2>&1
    if %errorlevel% equ 0 (
        set "PHP_BIN=php"
    )
)

if "%PHP_BIN%"=="" (
    echo [ERROR] PHP tidak ditemukan! Pastikan XAMPP terpasang di C:\xampp.
    echo Tekan sembarang tombol untuk keluar...
    pause >nul
    exit /b 1
)

REM Periksa apakah port 8000 sudah berjalan
netstat -ano | findstr /R /C:":8000 .*LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] PHP 8.2 Development Server [http://localhost:8000] started [Already Running]
    echo [OK] Server BahariGIS sudah aktif di: http://localhost:8000
    echo Browser dapat langsung mengakses website.
    echo Jangan tutup jendela ini jika ingin server tetap menyala.
    pause >nul
    exit /b 0
)

echo [OK] Menggunakan PHP: %PHP_BIN%
echo [INFO] Menjalankan server BahariGIS di http://localhost:8000 ...
"%PHP_BIN%" -S localhost:8000
