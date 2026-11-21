@echo off
rem Nap 25 file da build_len demo.sandienmay.com (khong touch .env)
rem CHAY SAU KHI da chay deploy/sql-demo-2026-10-29.sql trong phpMyAdmin
"C:\Program Files (x86)\WinSCP\WinSCP.com" /script=D:\laragon\www\sandienmay\deploy\winscp-upload.txt
echo.
echo Neu co loi "batch abort", dung o day. File cu tren host da duoc backup ve:
echo   D:\laragon\www\sandienmay\deploy\backup-host-2026-10-29
pause
