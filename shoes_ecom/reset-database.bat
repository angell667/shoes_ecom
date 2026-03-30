@echo off
title ShoeStore - Reset Database
cls

echo ============================================
echo    ShoeStore - Reset Database
echo ============================================
echo.
echo WARNING: This will delete all data in the database!
echo.
pause

cd /d "C:\Users\angellX\Desktop\New folder (4)\shoes_ecom\backend"

echo.
echo Dropping all tables...
call php artisan db:wipe --force

echo.
echo Running migrations...
call php artisan migrate --force

echo.
echo Seeding with sample data...
call php artisan db:seed --force

echo.
echo Database reset complete!
echo.
echo Test Accounts:
echo   Admin: admin@shoestore.com / password123
echo   User:  test@test.com / password
echo.
pause
exit
