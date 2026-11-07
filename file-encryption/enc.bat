@echo off

set filename=%1
set username=%2

echo Generating key...
openssl rand -hex 32 > %username%key.hex

echo Encrypting file...
openssl enc -aes-256-cbc -pbkdf2 -in %filename% -out %filename%.enc -pass file:%username%key.hex

echo Creating hash...
openssl dgst -sha256 -binary %filename% > %filename%.sha256

echo Done Encryption!
pause