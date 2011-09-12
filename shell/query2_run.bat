@echo off
FOR /F "tokens=2 delims= " %%A IN ('TASKLIST /v ^| find /I "d5be53493c7795dd512f2638dbd083b0"') DO SET PID=%%A
echo %PID% 1315839278 > D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query2-0ded9632e83994b67fa234f27ad6795c.pid
echo Starting proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query2-0ded9632e83994b67fa234f27ad6795c.log
php D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/send_query.php --host=127.0.0.1 --user=codeLimited --pass= --database=DIMES_DISTANCES  --writedb=DIMES_POPS_VISUAL --port=5554 --PoPTblName=DPV_POP_0ded9632e83994b67fa234f27ad6795c --pop=PoPLocationTbl_2010_week_9_4weeks --as='25','59' --EdgeTblName=DPV_EDGE_0ded9632e83994b67fa234f27ad6795c --edge=IPEdgesMedianTbl_2010_9 --popIP=PoPIPTbl_2010_week_9_4weeks --query=2 >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query2-0ded9632e83994b67fa234f27ad6795c.log
echo End proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query2-0ded9632e83994b67fa234f27ad6795c.log
EXIT
