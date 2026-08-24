param(
    [int]$Port = 8001
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

$minimumPhpVersion = [Version]'8.4.1'
$phpCandidates = @(
    (Get-Command php -All -ErrorAction SilentlyContinue | ForEach-Object Source)
    (Get-ChildItem 'C:\laragon\bin\php\php-*\php.exe' -ErrorAction SilentlyContinue | ForEach-Object FullName)
) | Where-Object { $_ } | Select-Object -Unique

$phpExe = $phpCandidates | Where-Object {
    try {
        [Version](& $_ -r 'echo PHP_VERSION;' 2>$null) -ge $minimumPhpVersion
    } catch {
        $false
    }
} | Select-Object -First 1

if (-not $phpExe) {
    Write-Host "PHP $minimumPhpVersion ou superieur est requis." -ForegroundColor Red
    Write-Host "Installez/activez PHP 8.4 dans Laragon, puis ouvrez un nouveau terminal." -ForegroundColor Yellow
    exit 1
}

Write-Host "PHP utilise : $phpExe" -ForegroundColor Cyan
& $phpExe -v

$requiredExtensions = @('mbstring', 'openssl', 'curl', 'fileinfo', 'pdo', 'dom', 'xml', 'xmlwriter')
$enabledExtensions = & $phpExe -m
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
    & $phpExe artisan key:generate
}

& $phpExe artisan optimize:clear
Write-Host "SICORE Frontend : http://127.0.0.1:$Port" -ForegroundColor Green
Write-Host "Mode test : admin@sicore.sn / Sicore@2026" -ForegroundColor Yellow
& $phpExe artisan serve --host=127.0.0.1 --port=$Port
