@echo off
plink.exe -ssh -i "E:\Data\My Documents\Google Drive\Senjitsu\Development\github\Default\ssh private key.ppk" -P 65002 u119812537@185.232.14.54 "cd /home/u119812537/domains/rice2.pixelstail.com/public_html && bash deploy.sh"
pause
