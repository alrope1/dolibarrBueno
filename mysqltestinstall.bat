@echo off
REM ------------------------------------------------------------
REM Launch a test to connect to mysql
REM ------------------------------------------------------------
REM To change password, run following SQL command: 
REM GRANT ALL ON *.* TO login@localhost IDENTIFIED BY "newpassword"

echo -----------------------------------------------------------
echo This programm wil test a login on Mysql installed
echo by DoliWamp.
echo Version: 5.0.45
echo Port: 3306
echo -----------------------------------------------------------
echo If login is successull, type "quit" and enter twice to exit.
echo ------------------------------------------------------------


SET SAVES=
SET /P SAVES=Enter password to test: 

echo Try to connect to mysql with this password
.\bin\mysql\mysql5.0.45\bin\mysql -P 3306 -u root -p%SAVES%

pause
