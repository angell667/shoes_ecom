@echo off
title ShoeStore Backend
cls

echo ============================================
echo    Starting ShoeStore Backend (Laravel)
echo ============================================
echo.
echo Server: http://localhost:8000
echo API:    http://localhost:8000/api
echo.
echo Press Ctrl+C to stop
echo.

cd /d "%~dp0backend"
php artisan serve --host=127.0.0.1 --port=8000

pause
exit
