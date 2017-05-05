@echo off
REM -----------------------------------------------------
REM Run this script only if you have problem to run
REM Apache and Mysql as service. This can occurs when
REM Microsoft add bugs in service packs that prevents
REM servers ran as a service to launch.
REM -----------------------------------------------------

echo Running Apache as user process (this process does not return so we use "start")
start c:/dolibarr\bin\apache\apache2.4.9\bin\httpd.exe -f conf\httpd.conf

echo 

echo Running Mysql as user process (this process does not return so we use "start")
start c:/dolibarr\bin\mysql\mysql5.0.45\bin\mysqld-nt.exe --defaults-file=c:/dolibarr\bin\mysql\mysql5.0.45\my.ini --console
REM start c:/dolibarr\bin\mysql\mysql5.0.45\bin\mysqld.exe --defaults-file=c:/dolibarr\bin\mysql\mysql5.0.45\my.ini --console

pause