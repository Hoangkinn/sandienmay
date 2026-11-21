@echo off
setlocal
cd /d "D:\laragon\www\sandienmay\deploy\watchdog-php83"
set LOG=watchdog.log

rem Luu y: KHONG redirect WinSCP.com sang NUL device — WinSCP.com se loi; dung file wlog.txt
"C:\Program Files (x86)\WinSCP\WinSCP.com" /script="D:\laragon\www\sandienmay\deploy\watchdog-php83\check.txt" > wlog.txt 2>&1
if not exist htaccess-now goto :logfail

findstr /c:"ea-php83" htaccess-now > findstr-out.txt
if %errorlevel%==0 goto :ok

rem Mat dong php83 -> khoi phuc
"C:\Program Files (x86)\WinSCP\WinSCP.com" /script="D:\laragon\www\sandienmay\deploy\watchdog-php83\fix.txt" > wlog.txt 2>&1
echo %date% %time% BI FLIP -^> da khoi phuc ea-php83 >> %LOG%
goto :end

:ok
del /q htaccess-now
goto :end

:logfail
echo %date% %time% LOI: khong tai duoc .htaccess (FTP fail) >> %LOG%

:end
endlocal
