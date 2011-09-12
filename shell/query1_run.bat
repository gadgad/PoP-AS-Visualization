@echo off
FOR /F "tokens=2 delims= " %%A IN ('TASKLIST /v ^| find /I "e5d6c8bc169cee7e571aa3ce728005ac"') DO SET PID=%%A
echo %PID% 1315839275 > D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query1-0ded9632e83994b67fa234f27ad6795c.pid
echo Starting proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query1-0ded9632e83994b67fa234f27ad6795c.log
php D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/send_query.php --host=127.0.0.1 --user=codeLimited --pass= --database=DIMES_DISTANCES  --writedb=DIMES_POPS_VISUAL --port=5554 --PoPTblName=DPV_POP_0ded9632e83994b67fa234f27ad6795c --pop=PoPLocationTbl_2010_week_9_4weeks --as='25','59' --EdgeTblName=DPV_EDGE_0ded9632e83994b67fa234f27ad6795c --edge=IPEdgesMedianTbl_2010_9 --popIP=PoPIPTbl_2010_week_9_4weeks --query=1 >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query1-0ded9632e83994b67fa234f27ad6795c.log
echo End proces >> D:/PFiles/xampp/htdocs/PoP-AS-Visualization/shell/query1-0ded9632e83994b67fa234f27ad6795c.log
EXIT
