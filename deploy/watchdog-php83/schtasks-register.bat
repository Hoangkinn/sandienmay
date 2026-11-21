schtasks /Create /TN "SDM-PHP83-Watchdog" /TR "D:\laragon\www\sandienmay\deploy\watchdog-php83\watchdog.bat" /SC MINUTE /MO 10 /F
schtasks /Query /TN "SDM-PHP83-Watchdog"
