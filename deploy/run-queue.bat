@echo off
REM Rihal queue worker — run as a supervised process (NSSM or a logged-in session).
REM For a proper Windows service, wrap with NSSM:
REM   nssm install rihal-queue "C:\Users\shere\Desktop\SabaaqNext\deploy\run-queue.bat"
setlocal
cd /d "C:\Users\shere\Desktop\SabaaqNext\laravel"
:loop
php artisan queue:work --queue=notifications,default --tries=3 --max-time=3600 --sleep=3
timeout /t 5 >nul
goto loop
endlocal
