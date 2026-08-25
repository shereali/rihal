@echo off
REM Rihal production MySQL backup + uploaded files archive.
REM Usage: scripts\backup.bat
REM Requirements: mysqldump on PATH, .env configured with DB_*

setlocal
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%-%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_DIR=%~dp0..\storage\backups
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=*" %%a in ('powershell -command "(Get-Content .env | Where-Object { $_ -match '^DB_DATABASE=' }) -replace 'DB_DATABASE=' "') do set DB_DATABASE=%%a
for /f "tokens=*" %%a in ('powershell -command "(Get-Content .env | Where-Object { $_ -match '^DB_USERNAME=' }) -replace 'DB_USERNAME=' "') do set DB_USERNAME=%%a
for /f "tokens=*" %%a in ('powershell -command "(Get-Content .env | Where-Object { $_ -match '^DB_PASSWORD=' }) -replace 'DB_PASSWORD=' "') do set DB_PASSWORD=%%a
for /f "tokens=*" %%a in ('powershell -command "(Get-Content .env | Where-Object { $_ -match '^DB_HOST=' }) -replace 'DB_HOST=' "') do set DB_HOST=%%a

echo Backing up database %DB_DATABASE% ...
mysqldump -h %DB_HOST% -u %DB_USERNAME% -p%DB_PASSWORD% --single-transaction --routines --triggers %DB_DATABASE% > "%BACKUP_DIR%\rihal-db-%TIMESTAMP%.sql"
if errorlevel 1 (
  echo [ERROR] Database backup failed.
  exit /b 1
)

echo Archiving uploaded files ...
if exist "storage\app\public" (
  powershell -command "Compress-Archive -Path storage\app\public\* -DestinationPath '%BACKUP_DIR%\rihal-files-%TIMESTAMP%.zip' -Force"
)

echo Backup complete: %BACKUP_DIR%
endlocal
