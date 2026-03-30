@echo off
title ShoeStore - First Time Setup
cls

echo ============================================
echo    ShoeStore E-Commerce - Setup Script
echo ============================================
echo.
echo This will setup:
echo   - Backend (Laravel API)
echo   - Frontend (Customer Store)
echo   - Admin Panel
echo.
echo IMPORTANT: Make sure XAMPP is running!
echo            - Start Apache
echo            - Start MySQL
echo.
echo Then go to http://localhost/phpmyadmin
echo and create a database named: shoes_ecom
echo.
pause

echo.
echo ============================================
echo    Step 1: Setup Backend (Laravel)
echo ============================================
cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\backend"

echo Installing Composer dependencies...
call composer install --no-interaction

echo.
echo Running migrations...
call php artisan migrate --force

echo.
echo Seeding database with sample products...
call php artisan db:seed --force

echo.
echo ============================================
echo    Step 2: Setup Frontend (Customer Store)
echo ============================================
cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\frontend"

echo Installing npm dependencies...
call npm install

echo.
echo ============================================
echo    Step 3: Setup Admin Panel
echo ============================================
cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\admin-frontend"

echo Installing npm dependencies...
call npm install

echo.
echo ============================================
echo    SETUP COMPLETE!
echo ============================================
echo.
echo To start the application:
echo   - Run "start-all.bat" to start everything
echo   - Or run individual batch files
echo.
echo URLs:
echo   Customer Store: http://localhost:5173
echo   Admin Panel:    http://localhost:5174
echo.
echo Admin Login: admin@shoestore.com / password123
echo User Login:  test@test.com / password
echo.
pause
exit
