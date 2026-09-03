@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env >nul
php artisan optimize:clear
echo API attendue : http://127.0.0.1:8000/api
echo Utilisez un compte cree par les seeders du backend.
php artisan serve --host=127.0.0.1 --port=8001
