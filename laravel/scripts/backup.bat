@echo off
REM Rihal production MySQL backup + uploaded files archive.
REM Usage: scripts\backup.bat
REM Detects mysqldump automatically; override with MYSQL_BIN if needed.

setlocal EnableDelayedExpansion
REM Use the known-good MySQL 8.4.3 client. Override via MYSQL_BIN if your install differs.
set MYSQL_BIN=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set TS=%%i
set BACKUP_DIR=%~dp0..\storage\backups
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=*" %%a in ('powershell -NoProfile -Command "(Get-Content .env | Where-Object { $_ -match '^DB_DATABASE=' }) -replace 'DB_DATABASE=' "') do set DB_DATABASE=%%a
for /f "tokens=*" %%a in ('powershell -NoProfile -Command "(Get-Content .env | Where-Object { $_ -match '^DB_USERNAME=' }) -replace 'DB_USERNAME=' "') do set DB_USERNAME=%%a
for /f "tokens=*" %%a in ('powershell -NoProfile -Command "(Get-Content .env | Where-Object { $_ -match '^DB_PASSWORD=' }) -replace 'DB_PASSWORD=' "') do set DB_PASSWORD=%%a
for /f "tokens=*" %%a in ('powershell -NoProfile -Command "(Get-Content .env | Where-Object { $_ -match '^DB_HOST=' }) -replace 'DB_HOST=' "') do set DB_HOST=%%a

echo Backing up database %DB_DATABASE% ...
echo Using mysqldump: %MYSQL_BIN%\mysqldump.exe
if "%DB_PASSWORD%"=="" (
  "%MYSQL_BIN%\mysqldump.exe" -h %DB_HOST% -u %DB_USERNAME% --single-transaction --routines --triggers %DB_DATABASE% > "%BACKUP_DIR%\rihal-db-%TS%.sql"
) else (
  set "MYSQL_PWD=%DB_PASSWORD%"
  "%MYSQL_BIN%\mysqldump.exe" -h %DB_HOST% -u %DB_USERNAME% --single-transaction --routines --triggers %DB_DATABASE% > "%BACKUP_DIR%\rihal-db-%TS%.sql"
)
if errorlevel 1 (
  echo [ERROR] Database backup failed.
  exit /b 1
)

echo Archiving uploaded files ...
if exist "storage\app\public" (
  powershell -NoProfile -Command "Compress-Archive -Path storage\app\public\* -DestinationPath '%BACKUP_DIR%\rihal-files-%TS%.zip' -Force"
)

echo Backup complete: %BACKUP_DIR%
endlocal
