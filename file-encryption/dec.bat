@echo off

set filename=%1
set username=%2

echo Decrypting file...
openssl enc -d -aes-256-cbc -pbkdf2 -in %filename%.enc -out %filename%.dec -pass file:%username%key.hex

echo Comparing original and decrypted files...
FC.exe /B %filename% %filename%.dec

echo Creating hash for decrypted file...
openssl dgst -sha256 -binary %filename%.dec > %filename%.dec.sha256

echo Comparing hashes...
FC.exe /B %filename%.sha256 %filename%.dec.sha256

echo Done Decryption!
pause