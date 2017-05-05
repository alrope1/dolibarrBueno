@echo off
REM --------------------------------------------------------
REM This script install Apache and Mysql DoliWamp services
REM --------------------------------------------------------

echo ---- Execute install_services.bat >> doliwamp.log 2>>&1

REM NET STOP doliwampapache
REM NET STOP doliwampmysqld 

cd "c:/dolibarr"

REM Apache x.x
.\bin\apache\apache2.4.9\bin\httpd.exe -k install -n doliwampapache
REM reg add HKLM\SYSTEM\CurrentControlSet\Services\doliwampapache /V Start /t REG_DWORD /d 3 /f

REM Mysql 5.0-
REM .\bin\mysql\mysql5.0.45\bin\mysqld-nt.exe --install-manual doliwampmysqld
.\bin\mysql\mysql5.0.45\bin\mysqld-nt.exe --install doliwampmysqld
REM Mysql 5.1+
REM .\bin\mysql\mysql5.0.45\bin\mysqld.exe --install doliwampmysqld

echo ---- End script >> doliwamp.log 2>>&1

REM pause
