@echo off
echo ========================================
echo    Creating MySQL Database
echo ========================================
echo.
echo This will create the 'shoes_ecom' database in MySQL (XAMPP).
echo.

cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\backend"

echo Creating database...
php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS shoes_ecom');"

echo.
echo Database 'shoes_ecom' created successfully!
echo.
pause
