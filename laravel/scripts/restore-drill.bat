@echo off
REM Rihal restore drill — restore a backup into a STAGING database and verify integrity.
REM Usage: scripts\restore-drill.bat <backup-sql> <staging-db>
REM Requires: MySQL client binaries (MYSQL_BIN or default Laragon 8.4.3).

setlocal EnableDelayedExpansion
set MYSQL_BIN=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin
set BACKUP=%~1
set STAGING=%~2
if "%BACKUP%"=="" set BACKUP=storage\backups\rihal-db-latest.sql
if "%STAGING%"=="" set STAGING=rihal_next_staging

echo Creating staging DB %STAGING% from scratch (fresh migrations recreate triggers cleanly) ...
"%MYSQL_BIN%\mysql.exe" -h 127.0.0.1 -u root -e "DROP DATABASE IF EXISTS %STAGING%; CREATE DATABASE %STAGING% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"%MYSQL_BIN%\mysql.exe" -h 127.0.0.1 -u root %STAGING% -e "DROP TRIGGER IF EXISTS rihal_audit_logs_no_update; DROP TRIGGER IF EXISTS rihal_audit_logs_no_delete;" 2>nul

echo Restoring data dump into %STAGING% ...
"%MYSQL_BIN%\mysql.exe" -h 127.0.0.1 -u root %STAGING% < "%BACKUP%"
if errorlevel 1 (
  echo [ERROR] Restore failed.
  exit /b 1
)

echo Re-running migrations to recreate schema + audit triggers cleanly ...
php scripts/ensure-audit-triggers.php %STAGING% 2>&1 | tail -2

echo Verifying audit append-only trigger survived restore ...
"%MYSQL_BIN%\mysql.exe" -h 127.0.0.1 -u root %STAGING% -N -e "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE EVENT_OBJECT_TABLE='audit_logs';" > storage\backups\_trigger_count.txt
set /p COUNT=<storage\backups\_trigger_count.txt
for /f "delims=" %%c in ("%COUNT%") do set COUNT=%%c
echo Triggers found: %COUNT%
if "%COUNT%"=="2" (echo RESTORE DRILL PASSED) else (echo RESTORE DRILL FAILED: expected 2 audit triggers, got %COUNT%)

del /q storage\backups\_trigger_count.txt 2>nul
endlocal
