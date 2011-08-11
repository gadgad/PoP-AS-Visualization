<?php
    require_once("bin/load_config.php");
	
	class xmlWriter
	{
		private $xml_dst_dir;
		
		function __construct($xml_dst_dir)
		{
			$this->xml_dst_dir = $xml_dst_dir;
		}
		
		function sql2xml($sql)
		{
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
			if ($mysqli->connect_error) {
	 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
			}
			
			$query = $sql;
			$result = $mysqli->query($query) or ret_res("Data not found."); 
	
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
			
			$mysqli->close();
			return $xml_output;
		}
		
		function write_pop_XML()
		{
			$filepath = ($this->xml_dst_dir.'/pop.xml');
			$filewrite = fopen($filepath, "w");
			fwrite($filewrite, $this->pop_xmlString);
			fclose($filewrite);
		}
	}
?>