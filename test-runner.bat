@echo off
setlocal enabledelayedexpansion

echo ============================================
echo  Running Refactored Architecture Tests
echo ============================================
echo.

REM 檢查是否在正確的目錄
if not exist "backend\artisan" (
    echo Error: Cannot find backend directory or Laravel artisan file!
    echo Please make sure you are running this from the project root directory.
    echo Current directory: %CD%
    pause
    exit /b 1
)

REM 進入backend目錄
cd backend
if errorlevel 1 (
    echo Error: Failed to change to backend directory!
    pause
    exit /b 1
)

REM 創建簡單的時間戳
set LOGFILE=..\test-results.txt

echo Starting test execution > %LOGFILE%
echo ============================================ >> %LOGFILE%
echo  Running Refactored Architecture Tests >> %LOGFILE%
echo ============================================ >> %LOGFILE%
echo. >> %LOGFILE%
echo Logging to: %LOGFILE%
echo.

echo 1. Checking environment...
echo ===============================
echo 1. Checking environment... >> %LOGFILE%
echo =============================== >> %LOGFILE%

echo PHP Version: >> %LOGFILE%
php -v >> %LOGFILE% 2>&1
if errorlevel 1 (
    echo Error: PHP is not installed or not in PATH! >> %LOGFILE%
    echo Error: PHP is not installed or not in PATH!
    pause
    exit /b 1
)

echo Laravel Version: >> %LOGFILE%
php artisan --version >> %LOGFILE% 2>&1
echo. >> %LOGFILE%

echo 2. Running Service Layer Tests...
echo ================================
echo 2. Running Service Layer Tests... >> %LOGFILE%
echo ================================ >> %LOGFILE%
php artisan test tests/Unit/Services/PosinServiceTest.php --verbose >> %LOGFILE% 2>&1
echo Service tests completed.
echo. >> %LOGFILE%

echo 3. Running Repository Tests...
echo ===============================
echo 3. Running Repository Tests... >> %LOGFILE%
echo =============================== >> %LOGFILE%
php artisan test tests/Unit/Repositories/PosinRepositoryTest.php --verbose >> %LOGFILE% 2>&1
echo Repository tests completed.
echo. >> %LOGFILE%

echo 4. Running Feature Tests...
echo ===========================
echo 4. Running Feature Tests... >> %LOGFILE%
echo =========================== >> %LOGFILE%
php artisan test tests/Feature/PosinControllerRefactoredTest.php --verbose >> %LOGFILE% 2>&1
echo Feature tests completed.
echo. >> %LOGFILE%

echo 5. Running Request Validation Tests...
echo =======================================
echo 5. Running Request Validation Tests... >> %LOGFILE%
echo ======================================= >> %LOGFILE%
php artisan test tests/Unit/Requests/PosinCreateRequestTest.php --verbose >> %LOGFILE% 2>&1
echo Request validation tests completed.
echo. >> %LOGFILE%

echo 6. Running All Refactored Tests...
echo ===================================
echo 6. Running All Refactored Tests... >> %LOGFILE%
echo =================================== >> %LOGFILE%
php artisan test --testsuite=Refactored --verbose >> %LOGFILE% 2>&1
echo All tests completed.
echo. >> %LOGFILE%

echo Test execution completed >> %LOGFILE%
echo ============================================ >> %LOGFILE%

echo.
echo ============================================
echo  Test Summary
echo ============================================
echo All tests completed!
echo Results saved to: test-results.txt
echo.
echo Please share the content of test-results.txt
echo for analysis.
echo.
pause