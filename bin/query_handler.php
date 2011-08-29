<?php
    require_once("bin/load_config.php");	
	
    class queryHandler
	{
		public $queryID;
		public $username;
		
		private	$blade;
		private $host;
		private $port;
		private $hostNport;
		private $user;
		private $pass;
		private $database;		
		
		public function __construct($queryID){
			$this->queryID = $queryID;
			$this->username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
			
			$queries = simplexml_load_file("xml\query.xml");
			$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/blade');
			$selected_blade = (string)$res[0];
			
			$this->$blade = $Blade_Map[$selected_blade];
			$this->$host = (string)$blade["host"];
			$this->$port = (int)$blade["port"];
			$this->$user = (string)$blade["user"];
			$this->$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
			$this->$database = (string)$blade["db"];	
			
		}
		
		public function getStatus(){
			
			// 0 - error , 1 - running , 2 - complete
			$edge_state = 0;
			$pop_state = 0;
			
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
					
			while($mysqli->connect_error) {
					sleep(3);
					//$mysqli->ping();
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
	 		    	//ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
			}
			
			$result = $mysqli->query('SHOW FULL PROCESSLIST;');
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
			    if($row['State']!=NULL){
			    	//echo $row['Id']." ".$row['Info']."\n";
			    	$tbl = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
					if($idg->getPoPTblName()==$tbl){
						$pop_state = 1;
					} else if($idg->getEdgeTblName()==$tbl){
						$edge_state = 1;
					}
			    }
			}
			if($pop_state==0){
				$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getPoPTblName()."'");
				$num = $result->num_rows;
				if($num>0) $pop_state = 2;	
			}
			if($edge_state==0){
				$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getEdgeTblName()."'");
				$num = $result->num_rows;
				if($num>0) $edge_state = 2;	
			}
			$mysqli->close();
			
		}
	}
?>