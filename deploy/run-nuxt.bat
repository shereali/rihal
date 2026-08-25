@echo off
REM Rihal Nuxt 4 production server — run as a supervised process (NSSM or a logged-in session).
REM For a proper Windows service, wrap with NSSM:
REM   nssm install rihal-nuxt "C:\Users\shere\Desktop\SabaaqNext\deploy\run-nuxt.bat"
setlocal
cd /d "C:\Users\shere\Desktop\SabaaqNext\nuxt"
set NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
node .output/server/index.mjs
endlocal
