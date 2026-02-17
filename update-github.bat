@echo off
title Auto Push Github
color 0A

echo ==============================
echo    AUTO UPDATE GITHUB
echo ==============================

cd /d %~dp0

echo.
echo Menambahkan perubahan...
git add .

echo.
set /p msg=Masukkan pesan commit: 

echo.
echo Commit...
git commit -m "%msg%"

echo.
echo Upload ke Github...
git push

echo.
echo ==============================
echo   SELESAI 🚀
echo ==============================
pause
