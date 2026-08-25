@echo off
REM Rihal Laravel scheduler runner — invoke via Windows Task Scheduler every minute.
setlocal
cd /d "C:\Users\shere\Desktop\SabaaqNext\laravel"
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
endlocal
