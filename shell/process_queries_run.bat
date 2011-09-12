@echo off
FOR /F "tokens=2 delims= " %%A IN ('TASKLIST /v ^| find /I "2b8cc1ba6002647c2f3ab4705dabb8ec"') DO SET PID=%%A
echo %PID% 1315839884 > D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/process_queries.pid
echo Starting proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/process_queries.log
php D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/process_queries.php >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/process_queries.log
echo End proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/process_queries.log
EXIT
