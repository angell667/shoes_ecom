@echo off
title ShoeStore - Starting All Servers
cls

echo ============================================
echo    ShoeStore E-Commerce Platform
echo ============================================
echo.
echo This will start:
echo   - Backend (Laravel API)   : http://localhost:8000
echo   - Frontend (Vue.js Store) : http://localhost:5173
echo   - Admin Panel             : http://localhost:5173/admin
echo.
echo IMPORTANT: Make sure XAMPP MySQL is running!
echo.
pause

echo.
echo Starting Backend (Laravel API)...
start "ShoeStore Backend" cmd /k "cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\backend" && php artisan serve --host=127.0.0.1 --port=8000"

timeout /t 2 /nobreak >nul

echo Starting Frontend (Customer Store + Admin Panel)...
start "ShoeStore Frontend" cmd /k "cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\frontend" && npm run dev"

echo.
echo ============================================
echo    All servers are starting!
echo ============================================
echo.
echo Customer Store: http://localhost:5173
echo Admin Panel:    http://localhost:5173/admin
echo Backend API:    http://localhost:8000
echo.
echo Admin Login: admin@shoestore.com / password123
echo.
pause
exit
