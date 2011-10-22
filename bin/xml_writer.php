<?php
    require_once("bin/load_config.php");
    require_once("bin/idgen.php");
	require_once("bin/DBConnection.php");
	
	class xml_Writer
	{
		private $xml_dst_dir;
		private $pop_xmlString;
		private $edge_xmlString;
		private $idg;
		private $blade;
		private $schema;
		private $asList;
		
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
			$this->limit = DBMaxConnctionAttempts;
			
			$sx = simplexml_load_file("xml/query.xml");
			$res = $sx->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
			$this->asList = $res[0]->allAS;
			
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
			
			$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
			if($mysqli->connect_error) return NULL;
			return $mysqli;
		}
		
		private function create_links_table(){
			if(($this->mysqli = $this->get_connection()) == NULL)
				return false;
			$mysqli = $this->mysqli;
			
			$query = "create table if not exists `".$this->schema."`.`".$this->idg->getLinksTblName()."` (SELECT SourceAS,DestAS,Source_PoPID,Dest_PoPID,count(edgeid) as NumOfEdges FROM `".$this->schema."`.`".$this->idg->getEdgeTblName()."` group by concat(Source_PoPID,Dest_PoPID))";
			$result = $mysqli->real_query($query) or die("SQL Query Failed.");
			return true;
		}
		
		private function getPoPQuery(){return "select * from `".$this->schema."`.`".$this->idg->getPoPTblName()."` where ASN in(".$this->asList.")";}
		private function getEdgeQuery(){return "select * from `".$this->schema."`.`".$this->idg->getLinksTblName()."` where SourceAS in (".$this->asList.") AND DestAS in (".$this->asList.")";}
		
		private function sql2xml($sql,$dir)
		{
			$pageSize = fetchXMLRecordsPagingBufferSize; // Records
			
			$bufferSize = fetchXMLMemoryBufferSize; // Bytes	
			$xmlChunkSize = fetchXMLChunkSize; // in MB
			$bytesLimit = $xmlChunkSize*1048576; // in Bytes...
			
			$bytesWritten = 0;
			$currentChunk = 0;
			$firstTime = true;
			//$xml_output = '';
			
			$filepath = ($this->xml_dst_dir.'/'.$dir.'.xml');
			$filewrite = fopen($filepath, "w");
			if(!$filewrite) return false;
			
			$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
			$xml_output .= "<DATA>\n";
			
			if(($this->mysqli = $this->get_connection()) == NULL)
				return false;
			$mysqli = $this->mysqli;
			
			//$pattern = '/(^select) (.*) (from)(.*)/i';
			//$replacement = '$1 COUNT(*) $3$4';
			//$query = preg_replace($pattern, $replacement, $sql);
			
			$query = "select count(*) from (".$sql.") as temp;";

			$result = $mysqli->query($query) or die("SQL Query Failed.");
			$row = $result->fetch_row();
			$num = (int)$row[0];
			$numOfPages = ceil($num/$pageSize);
			
			for($currPage = 0; $currPage<$numOfPages; $currPage++){ 
				
				// parse 'pageSize' records from DB
				$pageOffset = $currPage*$pageSize;
				$query = $sql." limit $pageOffset,$pageSize";
				//echo $query."\n";
				$result = $mysqli->query($query) or die("SQL Query Failed.");
				$numOfRecords = $result->num_rows;
				for($x = 0 ; $x < $numOfRecords ; $x++){
				    $row = $result->fetch_assoc();
				    $xml_output .= "\t<ROW>\n"; 
					foreach($row as $key => $value){
						$xml_output .= "\t\t<".$key.">" .$value . "</".$key.">\n";
					}
				    $xml_output .= "\t</ROW>\n"; 
				}
				
				// if current chunk is bigger than 'chunkSize'
				// we continue the writing process in a new Chunk of xml 
				// xml is splitted into smaller chunks to avoid 'large' xml files
				// residing in memory during the reading phase
				if($bytesWritten>=$bytesLimit){
					
					// close the currrent chunk & flush to disk
					$xml_output .= "</DATA>"; 
					if($fwrite = fwrite($filewrite, $xml_output)){
						$bytesWritten+=$fwrite;
						fclose($filewrite);
						$bytesWritten = 0;
					} else {
						return false;
					}
					
					if($firstTime){ // create chunks dir & move file into it
						if(!$this->createDir($this->xml_dst_dir.'/'.$dir))
							return false;
						if (stristr(PHP_OS, 'WIN')) sleep(1); // arrrgh!
						rename($filepath,$this->xml_dst_dir."/$dir/".$dir.$currentChunk.'.xml');
						$firstTime=false;	
					}
					
					// continue by opening a new Chunk for writing...
					$filepath = ($this->xml_dst_dir."/$dir/".$dir.(++$currentChunk).'.xml');
					$filewrite = fopen($filepath, "w");
					if(!$filewrite) return false;
					
					$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
					$xml_output .= "<DATA>\n";
					
				}
				
				// flush generated XML to disk , if current output buffer is bigger than 'bufferSize'				
				if(strlen($xml_output)>=$bufferSize){
					if($fwrite = fwrite($filewrite, $xml_output)){
						$bytesWritten+=$fwrite;
						unset($xml_output);
						$xml_output = '';
					} else {
						return false;
					}
				}
				
			}

			$xml_output .= "</DATA>"; 
			if($fwrite = fwrite($filewrite, $xml_output)){
				$bytesWritten+=$fwrite;
				fclose($filewrite);
			} else {
				return false;
			} 
			
			/*
			if($currentChunk>0)
				if(!file_put_contents($this->xml_dst_dir."/$dir/".'numOfChunks.txt', $currentChunk))
					return false;
			 * */
			//$mysqli->close();
			return true;
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
			/*
			$filepath = ($this->xml_dst_dir.'/pop.xml');
			$filewrite = fopen($filepath, "w");
			if($this->pop_xmlString = $this->sql2xml($this->getPoPQuery())){
				fwrite($filewrite, $this->pop_xmlString);
				fclose($filewrite);
				return true;
			}
			return false;
			 * 
			 */
			
			return $this->sql2xml($this->getPoPQuery(), 'pop');
		}
		
		private function write_edge_XML()
		{
			/*
			$filepath = ($this->xml_dst_dir.'/edges.xml');
			$filewrite = fopen($filepath, "w");
			if($this->edge_xmlString = $this->sql2xml($this->getEdgeQuery())){
				fwrite($filewrite, $this->edge_xmlString);
				fclose($filewrite);
				return true;
			}
			return false;
			 * 
			 */
			
			if($this->create_links_table())
				if($this->sql2xml($this->getEdgeQuery(), 'edges'))
					return true;
				
			return false;
		}
		
		private function createDir($path)
		{
			// making a new dir to hold query results 
			if(!file_exists($path)){			 		
				if(!mkdir($path, 0777)) { 
				   return false;
				}
			}
			return true;	
		}
		
		public function writeXML()
		{
			if($this->createDir($this->xml_dst_dir)){
				if($this->write_pop_XML() && $this->write_edge_XML()){
					//$this->drop_tables();
					$this->mysqli->close();
					return true;
				}
			}
			return false;
		}
	}
?>