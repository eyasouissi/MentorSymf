@echo off
REM Clear screen and show header
cls
echo ====================================
echo  Wordlist Updater - Debug Mode      
echo ====================================

REM 1. Navigate to project
cd /d "C:\Users\bhjis\mentor"

REM 2. Verify PHP works
echo Checking PHP version...
php -v
if errorlevel 1 (
    echo ERROR: PHP not found! Check your PHP installation.
    pause
    exit /b 1
)

REM 3. Run command with logging
echo Starting update...
php bin/console app:update-wordlist -vvv > update.log 2>&1

REM 4. Show results
echo Update completed. Showing log:
type update.log

REM 5. Keep window open
echo If you see errors, take a screenshot and share it
pause