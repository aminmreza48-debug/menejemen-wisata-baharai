@echo off
title Push BahariGIS ke GitHub
color 0b
cd /d "%~dp0"

echo ==============================================================
echo    BahariGIS -- Push ke GitHub Repositori
echo    Target: https://github.com/aminmreza48-debug/menejemen-wisata-baharai.git
echo ==============================================================
echo.

set "PATH=%LOCALAPPDATA%\Programs\Git\cmd;%PATH%"

echo Menjalankan: git push -u origin main ...
echo.
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo ==============================================================
    echo [SUKSES] Berhasil melakukan push ke GitHub!
    echo Repositori: https://github.com/aminmreza48-debug/menejemen-wisata-baharai
    echo ==============================================================
) else (
    echo.
    echo ==============================================================
    echo [PETUNJUK AUTENTIKASI GITHUB]:
    echo 1. Di prompt Password, gunakan GitHub Personal Access Token (PAT)
    echo    (karena GitHub tidak lagi menerima password akun biasa).
    echo 2. Atau lebih mudah: Buka VS Code, klik tab Source Control
    echo    (ikon cabang di sisi kiri / Ctrl+Shift+G), lalu klik tombol
    echo    "Publish Branch" atau "Sync Changes" untuk login otomatis!
    echo ==============================================================
)

echo.
pause
