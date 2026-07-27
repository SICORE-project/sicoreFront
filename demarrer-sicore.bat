@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env >nul
php artisan optimize:clear
echo Mode test : admin@sicore.sn / Sicore@2026
php artisan serve --host=127.0.0.1 --port=8001
