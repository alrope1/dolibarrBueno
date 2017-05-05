@echo off
REM --------------------------------------------------------
REM This script install Apache and Mysql DoliWamp services
REM --------------------------------------------------------

cd "c:/dolibarr"

echo ---- Execute uninstall_services.bat >> doliwamp.log 2>>&1
NET STOP doliwampapache
.\bin\apache\apache2.4.9\bin\httpd.exe -k uninstall -n doliwampapache

NET STOP doliwampmysqld 
REM Mysql 5.0-
.\bin\mysql\mysql5.0.45\bin\mysqld-nt.exe --remove doliwampmysqld
REM Mysql 5.1+
REM .\bin\mysql\mysql5.0.45\bin\mysqld.exe --remove doliwampmysqld

REM wampmanager.exe -quit -id={doliwampserver}
echo ---- End script >> doliwamp.log 2>>&1

REM pause
