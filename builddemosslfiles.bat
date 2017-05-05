@echo off
REM Launch Dolibarr demo SSL key generation
REM ---------------------------------------

REM Build private key
c:/dolibarr\bin\apache\apache2.4.9\bin\openssl genrsa -out myserver.key 512

REM Set permissions on file
REM chmod 400 myserver.key

REM Create CSR file
c:/dolibarr\bin\apache\apache2.4.9\bin\openssl req -config openssl.conf -new -key myserver.key -out myserver.csr

REM  Create empty dir and files
echo 01 > tmp\serial
touch tmp\index.txt
touch tmp\index.txt.attr

REM Certify request
c:/dolibarr\bin\apache\apache2.4.9\bin\openssl ca -config openssl.conf -out myserver.crt -infiles myserver.csr

REM Check everything is OK
c:/dolibarr\bin\apache\apache2.4.9\bin\openssl verify -CAfile ca_demo_dolibarr.crt myserver.crt
