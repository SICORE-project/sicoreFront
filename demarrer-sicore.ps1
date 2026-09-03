param(
    [int]$Port = 8001
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

Write-Host "Verification de PHP..." -ForegroundColor Cyan
php -v

$requiredExtensions = @('mbstring', 'openssl', 'curl', 'fileinfo', 'pdo', 'dom', 'xml', 'xmlwriter')
$enabledExtensions = php -m
$missing = $requiredExtensions | Where-Object { $_ -notin $enabledExtensions }

if ($missing.Count -gt 0) {
    Write-Host "Extensions PHP manquantes : $($missing -join ', ')" -ForegroundColor Red
    Write-Host "Activez-les dans le php.ini de XAMPP, puis relancez ce script." -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

$envContent = Get-Content '.env' -Raw
if ($envContent -match "(?m)^APP_KEY=$") {
    php artisan key:generate
}

php artisan optimize:clear
Write-Host "SICORE Frontend : http://127.0.0.1:$Port" -ForegroundColor Green
Write-Host "API attendue : http://127.0.0.1:8000/api" -ForegroundColor Yellow
Write-Host "Utilisez un compte créé par les seeders du backend." -ForegroundColor Yellow
php artisan serve --host=127.0.0.1 --port=$Port
