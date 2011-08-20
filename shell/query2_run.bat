@echo off
echo Starting proces >> C:/xampp/htdocs/PoPVisualizer/shell/query2.log
php C:/xampp/htdocs/PoPVisualizer/shell/send_query.php --host:localhost --user=codeLimited --pass= --database=DIMES_DISTANCES --port=5554 --PoPTblName=DPV_POP_32d42086f275bad8623fb4f194b829b8 --pop=PoPLocationTbl_2010_week_9_2weeks --as='3','9','12','17','18','20','25','26','27','34','52','55','57','59','73','81','103','131','137','' --EdgeTblName=DPV_EDGE_32d42086f275bad8623fb4f194b829b8 --edge=IPEdgesMedianTbl_2010_9 --popIP=PoPIPTbl_2010_week_9_2weeks --query=2 >> C:/xampp/htdocs/PoPVisualizer/shell/query2.log
echo End proces >> C:/xampp/htdocs/PoPVisualizer/shell/query2.log
EXIT
