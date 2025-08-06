@echo off
setlocal enabledelayedexpansion

set "folder=database\migrations"
set "datePrefix=2025_08_06"
set /a counter=1

REM Sort files alphabetically — same order Laravel uses
for /f "delims=" %%F in ('dir /b /on "%folder%\*.php"') do (
    set "filename=%%~nxF"
    set "nameOnly=%%~nF"
    set "suffix=!nameOnly!"

    REM Strip timestamp prefix (first 4 underscore-separated parts)
    for /l %%i in (1,1,4) do (
        for /f "tokens=1* delims=_" %%a in ("!suffix!") do (
            set "suffix=%%b"
        )
    )

    set "padded=000000!counter!"
    set "padded=!padded:~-6!"

    set "newname=%datePrefix%_!padded!_!suffix!.php"

    echo Renaming: !filename! → !newname!
    ren "%folder%\!filename!" "!newname!"

    set /a counter+=1
)

echo Done.
pause
