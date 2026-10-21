@echo off
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0demarrer-sicore.ps1" -Port 8001
exit /b %errorlevel%
