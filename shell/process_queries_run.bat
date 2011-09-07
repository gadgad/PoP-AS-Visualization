@echo off
FOR /F "tokens=2 delims= " %%A IN ('TASKLIST /v ^| find /I "2f3b6046384ae07cb7edfab652eec1c4"') DO SET PID=%%A
echo %PID% 1315400440 > C:/xampp/htdocs/PoPVisualizer/shell/process_queries.pid
echo Starting proces >> C:/xampp/htdocs/PoPVisualizer/shell/process_queries.log
php C:/xampp/htdocs/PoPVisualizer/shell/process_queries.php >> C:/xampp/htdocs/PoPVisualizer/shell/process_queries.log
echo End proces >> C:/xampp/htdocs/PoPVisualizer/shell/process_queries.log
EXIT
