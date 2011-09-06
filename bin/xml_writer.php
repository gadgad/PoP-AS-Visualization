<?php
    require_once("bin/load_config.php");
    require_once("bin/idgen.php");
	
	class xml_Writer
	{
		private $xml_dst_dir;
		private $pop_xmlString;
		private $edge_xmlString;
		private $idg;
		private $blade;
		private $schema;
		
		private $retries;
		private $limit;
		
		private $mysqli;
		
		public function __construct($blade,$queryID)
		{
			$this->blade = (string)$blade;
			$this->idg = new idGen($queryID);
			$this->xml_dst_dir = 'queries/'.$this->idg->getDirName();
			$this->schema = $GLOBALS["Blade_Map"][$this->blade]["write-db"];
			
			$this->retries = 0;
			$this->limit = 10;
			
			$this->mysqli = NULL;
		}
		
		private function get_connection()
		{
			if($this->mysqli != NULL)
				return $this->mysqli;
			
			$selected_blade = $this->blade;
			$blade = $GLOBALS["Blade_Map"][$selected_blade];
			$host = (string)$blade["host"];
			$port = (int)$blade["port"];
			//$hostNport = (string)$blade["host"].":".(string)$blade["port"];
			$user = (string)$blade["user"];
			$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
			$database = (string)$blade["db"];
			
			
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
				
			while($mysqli->connect_error) {
				if($mysqli->connect_errno == 2006 && $this->retries < $this->limit){
					$mysqli->close();
					$this->retries++;
					sleep(3);
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
				} else {
					//die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
					return NULL;
				}
			}
			return $mysqli;
		}
		
		
		private function getPoPQuery(){return "select * from `".$this->schema."`.`".$this->idg->getPoPTblName()."`;";}
		private function getEdgeQuery(){return "select * from `".$this->schema."`.`".$this->idg->getEdgeTblName()."` where Source_PoPID is not null and Dest_PoPID is not null;";}
		
		private function sql2xml($sql)
		{	
			if(($this->mysqli = $this->get_connection()) == NULL)
				return false;
			$mysqli = $this->mysqli;
			
			$query = $sql;
			$result = $mysqli->query($query) or die("Data not found."); 
	
			$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
			$xml_output .= "<DATA>\n"; 
			
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){ 
			    $row = $result->fetch_assoc();
			    $xml_output .= "\t<ROW>\n"; 
				foreach($row as $key => $value){
					$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
				}
			    $xml_output .= "\t</ROW>\n"; 
			} 
			$xml_output .= "</DATA>"; 
			
			//$mysqli->close();
			return $xml_output;
		}

		private function drop_tables()
		{
			if(($this->mysqli = $this->get_connection()) == NULL)
				return false;
			$mysqli = $this->mysqli;
			
			$mysqli->select_db($this->schema);
						
			$sql = 'drop table if exists '.$this->idg->getEdgeTblName();						
			$res = $mysqli->query($sql);
			$sql = 'drop table if exists '.$this->idg->getPoPTblName();						
			$res = $mysqli->query($sql);
			
		}
		
		private function write_pop_XML()
		{
			$filepath = ($this->xml_dst_dir.'/pop.xml');
			$filewrite = fopen($filepath, "w");
			if($this->pop_xmlString = $this->sql2xml($this->getPoPQuery())){
				fwrite($filewrite, $this->pop_xmlString);
				fclose($filewrite);
				return true;
			}
			return false;
		}
		
		private function write_edge_XML()
		{
			$filepath = ($this->xml_dst_dir.'/edges.xml');
			$filewrite = fopen($filepath, "w");
			if($this->edge_xmlString = $this->sql2xml($this->getEdgeQuery())){
				fwrite($filewrite, $this->edge_xmlString);
				fclose($filewrite);
				return true;
			}
			return false;
		}
		
		private function createDir()
		{
			// making a new dir to hold query results 
			$querydir = $this->xml_dst_dir;
			if(!file_exists($querydir)){			 		
				if(!mkdir($querydir, 0777)) { 
				   return false;
				}
			}
			return true;	
		}
		
		public function writeXML()
		{
			if($this->createDir()){
				if($this->write_pop_XML() && $this->write_edge_XML()){
					// TODO: uncommnet this when debuging is finished!
					//$this->drop_tables();
					$this->mysqli->close();
					return true;
				}
			}
			return false;
		}
	}
?>