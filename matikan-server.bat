@echo off
title BahariGIS - Stop Web Server
color 0c
cd /d "%~dp0"

echo ==============================================================
echo    BahariGIS -- Mematikan Server Lokal (Port 8000)
echo ==============================================================
echo.

set FOUND=0
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do (
    echo Menghentikan proses PID: %%a ...
    taskkill /F /PID %%a >nul 2>&1
    set FOUND=1
)

if "%FOUND%"=="1" (
    echo [OK] Server BahariGIS di port 8000 berhasil dihentikan.
) else (
    echo [INFO] Tidak ada server BahariGIS yang sedang berjalan di port 8000.
)

echo.
echo Selesai. Tekan sembarang tombol untuk menutup...
pause >nul
