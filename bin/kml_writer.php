<?php
require_once("bin/load_config.php");
require_once("bin/color.php");

define("PRECISION",4); //precision of floating point calculations
define("EARTH_RADIUS",6371); // earth raius in km

class Coordinate
{
    public $lat;
    public  $lng;

    public function __construct($lat,$lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }
}

///////////////////////////////////////////////////////////////////////////
class kmlWriter
{
	
	private $kmlString;
	private $filename;
	
	private $pop_xml;
	private $edges_xml;
	private $asn_info_xml;
		
	private $ASN_LIST;
	private $EDGES;
	private $PLACEMARKS;
	private $LOC_2_POP_MAP;
	private $CIRCLES;
	
	private $xml_src_dir;
	private $kml_dst_dir;
	
	
	public function __construct($xml_src_dir,$kml_dst_dir)
	{
		/*
		if(func_num_args()==2)
		{
			$this->xml_src_dir = func_get_arg(0);
			$this->kml_dst_dir = func_get_arg(1);
		} else {
			$this->xml_src_dir = 'xml';
			$this->kml_dst_dir = 'temp';
		}
		 * 
		 */
		
		$this->xml_src_dir = $xml_src_dir;
		$this->kml_dst_dir = $kml_dst_dir;
		
		$this->kmlString = '';
		$this->pop_xml = simplexml_load_file($this->xml_src_dir."\pop.xml");
		$this->edges_xml = simplexml_load_file($this->xml_src_dir."\edges.xml");
		
		$as_info_path = $GLOBALS["FileLocations"]["as-info"];
		$this->asn_info_xml = simplexml_load_file($as_info_path);
		
		$this->ASN_LIST=array();
		$this->EDGES=array();
		$this->PLACEMARKS=array();
		$this->LOC_2_POP_MAP=array();
		$this->CIRCLES=array();
		
		$this->parseXML();
	}
	
	private function dispatchAltitude(){
	    static  $altitude = INITIAL_ALTITUDE;
		$alt = $altitude; 
	    $altitude+=ALTITUDE_DELTA;
	    return $alt;
	}
	
	private function parseXML()
	{

		foreach($this->edges_xml->children() as $edge)
		{
			$srcPOP = (string)$edge->Source_PoPID;
			$dstPOP = (string)$edge->Dest_PoPID;
			if($srcPOP!="NULL" && $dstPOP!="NULL" && $srcPOP!=$dstPOP)
			{
				if((INTRA_CON && (intval($edge->SourceAS)==intval($edge->DestAS))) || (INTER_CON && (intval($edge->SourceAS)!=intval($edge->DestAS))))
				{
				    $edge_str = $edge->Source_PoPID.$edge->Dest_PoPID;
					$this->PLACEMARKS[(string)($edge->Source_PoPID)]["connected"]=true;
					$this->PLACEMARKS[(string)($edge->Dest_PoPID)]["connected"]=true;
				    if(!array_key_exists($edge_str, $this->EDGES)){
				     	$this->EDGES[$edge_str] = array("SourceAS"=>intval($edge->SourceAS),
				     							  "DestAS"=>intval($edge->DestAS),
				     							  "SourcePoP"=>$srcPOP,
				     							  "DestPoP"=>$dstPOP,
				     							  "numOfEdges"=>1,
				     							  "median_lst"=>array(floatval($edge->Median)),
				     							  "edgeID_lst"=>array($edge->edgeid),
				     							  "src_ip_lst"=>array($edge->SourceIP),
				     							  "dest_ip_lst"=>array($edge->DestIP));
				    } else {
				    	$this->EDGES[$edge_str]["numOfEdges"]++;
						$this->EDGES[$edge_str]["median_lst"][] = floatval($edge->Median);
				    	$this->EDGES[$edge_str]["edgeID_lst"][] = $edge->edgeid;
				    	$this->EDGES[$edge_str]["src_ip_lst"][] = $edge->SourceIP;
				    	$this->EDGES[$edge_str]["dest_ip_lst"][] = $edge->DestIP;
			    	}
				}
			}
		}
		
		foreach($this->pop_xml->children() as $pop)
		  {
		  	// generate map of PoP coordinates
		    if(!array_key_exists((string)$pop->PoPID, $this->PLACEMARKS) 
		    	|| !isset($this->PLACEMARKS[(string)$pop->PoPID]["lat"])
				|| !isset($this->PLACEMARKS[(string)$pop->PoPID]["lng"])){
		    	//$this->PLACEMARKS[(string)$pop->PoPID] = array("lat"=>floatval($pop->LAT2), "lng"=>floatval($pop->LNG2));
		    	$this->PLACEMARKS[(string)$pop->PoPID]["lat"] = floatval($pop->LAT2);
				$this->PLACEMARKS[(string)$pop->PoPID]["lng"] = floatval($pop->LNG2);
			}
			
		  	$pop_connected = isset($this->PLACEMARKS[(string)$pop->PoPID]["connected"]) ? true : false;
		  	if(!CONNECTED_POPS_ONLY || $pop_connected)
			{
			  	$pop_str = $pop->ASN.$pop->LAT2.$pop->LNG2;
				if(!array_key_exists($pop_str, $this->LOC_2_POP_MAP)){
			  		$this->LOC_2_POP_MAP[$pop_str] = array("numOfPoPS"=>1,"asn"=>intval($pop->ASN),"lat"=>floatval($pop->LAT2),"lng"=>floatval($pop->LNG2),"pop_id_lst"=>array($pop->PoPID));
				} else {
					$this->LOC_2_POP_MAP[$pop_str]["numOfPoPS"]++;
					$this->LOC_2_POP_MAP[$pop_str]["pop_id_lst"][] = $pop->PoPID;
				}
			}
			
			if(USE_COLOR_PICKER)
				$cp = new ColorPicker(DEFAULT_COLOR_PICKER_POOL_SIZE);
			
			$asn = intval($pop->ASN);
			if(!array_key_exists($asn,$this->ASN_LIST)){
			  $new_color = (USE_COLOR_PICKER)? $cp->getColor() : new Color();
			  $this->ASN_LIST[$asn]= array("color"=>$new_color, "altitude"=>$this->dispatchAltitude());
			  $asn_info = $this->asn_info_xml->xpath("/DATA/ROW[ASNumber=".$asn."]");
			  if(!empty($asn_info))
			  {
			  	$this->ASN_LIST[$asn]["Country"] = $asn_info[0]->Country;
				$this->ASN_LIST[$asn]["ISPName"] = $asn_info[0]->ISPName;
			  }
		  	}
			
			if(DRAW_CIRCLES && (!CONNECTED_POPS_ONLY || $pop_connected)){
		    	//we only need LAT2,LNG2,Accuracy2
		    	//radius = Accuracy2*110000 [in meters]
		    	$this->CIRCLES[] = array("lat"=>floatval($pop->LAT2),"lng"=>floatval($pop->LNG2),"radius"=>floatval($pop->Accuracy2)*110000,"asn"=>intval($pop->ASN),"popID"=>$pop->PoPID);
			}
			
		  }
	}
	
	private function kmlPlaceMark($placeMark)
	{
		$kmlString = '';
	  	static $firstTime = true;
		static $counter=1;
		static $ASN_TMP_MAP = array();
		$kmlString = '';
		
		$asn = $placeMark["asn"];
		$lng = $placeMark["lng"];
		$lat = $placeMark["lat"];
		$numOfPoPS = $placeMark["numOfPoPS"];
		
		if(!array_key_exists($asn,$ASN_TMP_MAP)){
		  if(!$firstTime){
		      $kmlString.="</Folder>";
		  }
		  $kmlString.="<Folder>\n<name>ASN: ".$asn."</name>\n";
		  $ASN_TMP_MAP[$asn]=true;
	  	}
		
		$pop_lst_str="<P>#PoPS:".$numOfPoPS."</BR>";
		for($i=0;$i<$numOfPoPS; $i++)
		{
			$pop_lst_str.="PoP ID: ".$placeMark["pop_id_lst"][$i]."</BR>\n";
		}
		$pop_lst_str.="</P>";
		$kmlString .= "<Placemark>\n<name>PlaceMark#".$counter++."</name>\n<description><![CDATA[<P>ASN#: ".$asn."</BR>ISP Name: ".$this->ASN_LIST[$asn]["ISPName"]."</BR>Country: ".$this->ASN_LIST[$asn]["Country"]."</P>".$pop_lst_str."]]></description><visibility>1</visibility>\n<Style>\n<LabelStyle>\n<scale>0</scale>\n</LabelStyle>\n<IconStyle>\n<Icon>\n<href><![CDATA[http://www.google.com/chart?chst=d_map_xpin_letter&chld=pin||".$this->ASN_LIST[$asn]["color"]->web_format()."]]></href>\n</Icon>\n</IconStyle>\n<LineStyle>\n<width>2</width>\n</LineStyle>\n</Style>\n<Point>\n<extrude>1</extrude>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n".$lng.",".$lat.",".$this->ASN_LIST[$asn]["altitude"]."\n</coordinates>\n</Point>\n</Placemark>\n";
		
		$firstTime = false;
	 	return $kmlString;
	}
	
	private function kmlCircle($circle){
	  $lat = $circle["lat"];
	  $lng = $circle["lng"];
	  $radius = $circle["radius"];
	  $asn = $circle["asn"];
	  $popID = $circle["popID"];
	  
	  $centerlat_form = $lat;
	  $centerlong_form = $lng;
	  $d= $radius;
	  $kmlString = '';
	  
	  static $ASN_TMP_MAP = array();
	  static $firstTime = true;
	  
	  if(!array_key_exists($asn,$ASN_TMP_MAP)){
		  if(!$firstTime){
		      $kmlString.="</Folder>";
		  }
		  $kmlString.="<Folder>\n<name>ASN: ".$asn."</name>\n";
		  $ASN_TMP_MAP[$asn]=true;
	  }
	
	 // convert coordinates to radians
	 $lat1 = deg2rad($centerlat_form);
	 $long1 = deg2rad($centerlong_form);
	 $d_rad = $d/6378137;
	
	 //generate kml string
	$kmlString .= "<Placemark>\n<name>pop id:".$popID."</name>\n<description><![CDATA[<P>ASN Number: ".$asn."</P><P>ISP Name: ".$this->ASN_LIST[$asn]["ISPName"]."</P><P>Country: ".$this->ASN_LIST[$asn]["Country"]."</P>]]></description>\n<visibility>1</visibility>\n<Style>\n<LineStyle>\n<color>".($this->ASN_LIST[$asn]["color"]->gm_format())."</color>\n<width>".MIN_LINE_WIDTH."</width>\n</LineStyle>\n<PolyStyle>\n<color>".($this->ASN_LIST[$asn]["color"]->gm_format())."</color>\n<fill>0</fill>\n</PolyStyle>\n</Style>\n<Polygon>\n<extrude>0</extrude>\n<tessellate>0</tessellate>\n<altitudeMode>relativeToGround</altitudeMode>\n<outerBoundaryIs>\n<LinearRing>\n<coordinates>\n";
	// loop through the array and write path linestrings
	for($i=0; $i<=360; $i++) {
	  $radial = deg2rad($i);
	  $lat_rad = asin(sin($lat1)*cos($d_rad) + cos($lat1)*sin($d_rad)*cos($radial));
	  $dlon_rad = atan2(sin($radial)*sin($d_rad)*cos($lat1),cos($d_rad)-sin($lat1)*sin($lat_rad));
	  $lon_rad = fmod(($long1+$dlon_rad + M_PI), 2*M_PI) - M_PI;
	  $kmlString.=rad2deg($lon_rad).",".rad2deg($lat_rad).",".$this->ASN_LIST[$asn]["altitude"]."\n";
	}
	$kmlString.="</coordinates>\n</LinearRing>\n</outerBoundaryIs>\n</Polygon>\n</Placemark>\n";
	 
	 $firstTime = false;
	 return $kmlString;
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	private function calcDist($lat1,$lat2,$lon1,$lon2)
	{
	    $dLat = deg2rad($lat2-$lat1);
	    $dLon = deg2rad($lon2-$lon1);
	    $lat1 = deg2rad($lat1);
	    $lat2=deg2rad($lat2);
	    // based on haversine formula:
	    $a = pow(sin($dLat/2),2)+pow(sin($dLon/2),2)*cos($lat1)*cos($lat2);
	    $c = 2*atan2(sqrt($a),sqrt(1-$a));
	    $d = EARTH_RADIUS*$c;
	    return round($d,PRECISION);
	}
	
	private function calcMidPoint($lat1,$lat2,$lon1,$lon2)
	{
	    //$dLat = deg2rad($lat2-$lat1);
	    $dLon = deg2rad($lon2-$lon1);
	    $lat1 = deg2rad($lat1);
	    $lat2=deg2rad($lat2);
	    $Bx = cos($lat2) *cos($dLon);
	    $By = cos($lat2) * sin($dLon);
	    $lat3 = atan2(sin($lat1)+sin($lat2),sqrt( (cos($lat1)+$Bx)*(cos($lat1)+$Bx) + $By*$By) );
	    $lon3 = deg2rad($lon1) +atan2($By,cos($lat1) + $Bx);
	
	    return new Coordinate(rad2deg($lat3),rad2deg($lon3));
	}
	
	private  function calcBearing($lat1,$lat2,$lon1,$lon2)
	{
	    $dLon = deg2rad($lon2-$lon1);
	    $lat1 = deg2rad($lat1);
	    $lat2=deg2rad($lat2);
	
	    $y = sin($dLon) * cos($lat2);
	    $x = cos($lat1)*sin($lat2) -sin($lat1)*cos($lat2)*cos($dLon);
	    $brng = rad2deg(atan2($y,$x));
	    return $brng;
	}
	
	private function calcDestPoint($lat1,$lon1,$dist,$brng)
	{
	    $d = $dist/EARTH_RADIUS;  // convert dist to angular distance in radians
	    $b = deg2rad($brng);
	    $lat1 = deg2rad($lat1);  $lon1 = deg2rad($lon1);
	
	    $lat2 = asin(sin($lat1)*cos($d) + cos($lat1)*sin($d)*cos($b) );
	    $lon2 = $lon1 + atan2(sin($b)*sin($d)*cos($lat1), cos($d)-sin($lat1)*sin($lat2));
	    //$lon2 = ($lon2+3*M_PI)%(2*M_PI) - M_PI;  // normalise to -180...+180
	
	    return new Coordinate(rad2deg($lat2),rad2deg($lon2));
	}
	
	private function kmlLink($link){
		static $counter=1;
	    $kmlString = "";
		
		//TODO: move this logic to caller function/code!
		if( array_key_exists($link["SourcePoP"], $this->PLACEMARKS) 
			&& array_key_exists($link["DestPoP"], $this->PLACEMARKS)
			&& array_key_exists("lat", $this->PLACEMARKS[$link["SourcePoP"]])
			&& array_key_exists("lat", $this->PLACEMARKS[$link["DestPoP"]])){
			$srcLAT = $this->PLACEMARKS[$link["SourcePoP"]]["lat"];
			$srcLNG = $this->PLACEMARKS[$link["SourcePoP"]]["lng"];
			$dstLAT = $this->PLACEMARKS[$link["DestPoP"]]["lat"];
			$dstLNG = $this->PLACEMARKS[$link["DestPoP"]]["lng"];
			
			if(($srcLAT == $dstLAT)&&($srcLNG == $dstLNG))
				return "";
		
	    	
			$srcAS = $link["SourceAS"];
			$dstAS = $link["DestAS"];
			$srcPOP = $link["SourcePoP"];
			$dstPOP = $link["DestPoP"];
			
			$numOfEdges = $link["numOfEdges"];
			$edge_lst_str = "<P>#Edges: ".$numOfEdges."</BR>";
			for($i=0;$i<min($numOfEdges,MAX_EDGES_RESULTS); $i++)
			{
				$edge_lst_str.="EdgeID: ".$link["edgeID_lst"][$i]."\nSourceIP: ".$link["src_ip_lst"][$i]."\nDestIP: ".$link["dest_ip_lst"][$i]."\nMedian: ".$link["median_lst"][$i]."</BR>\n";
			}
			$edge_lst_str.="</P>";
			if($numOfEdges>MAX_EDGES_RESULTS)
			{
				$edge_lst_str.="<P>And there are ".($numOfEdges-MAX_EDGES_RESULTS)." more...</P>\n";
			}
			
	        $kmlString.="<Placemark>\n<name>Edge#".$counter++."</name>\n<description>\n<![CDATA[\n<P>Source AS: ".$srcAS."</BR>Dest AS: ".$dstAS."</BR>Source PoP: ".$srcPOP."</BR>Dest PoP: ".$dstPOP."</P>".$edge_lst_str."\n]]>\n</description>\n<visibility>1</visibility>\n<Style>\n<LineStyle>\n<color>".($this->ASN_LIST[$srcAS]["color"]->gm_format())."</color>\n<width>".max(MIN_LINE_WIDTH,min($link["numOfEdges"],MAX_LINE_WIDTH))."</width>\n</LineStyle>\n<PolyStyle>\n<color>".($this->ASN_LIST[$srcAS]["color"]->gm_format())."</color>\n</PolyStyle>\n</Style>\n<LineString>\n<tessellate>1</tessellate>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n";
	        $kmlString.=$srcLNG.",".$srcLAT.",".$this->ASN_LIST[$srcAS]["altitude"]."\n"; //first coordinate
	        $dist = $this->calcDist($srcLAT, $dstLAT,$srcLNG, $dstLNG);
	        $brng = $this->calcBearing($srcLAT, $dstLAT,$srcLNG, $dstLNG);
	        $delta = 50; // segmentation factor in KM
	        $numOfSegments = floor($dist/$delta);
	        for($i=1;$i<$numOfSegments; $i++){
	            $c = $this->calcDestPoint($srcLAT, $srcLNG, $i*$delta, $brng);
	            //$kmlString.=$c->lng.",".$c->lat.",".($this->ASN_LIST[$srcAS]["altitude"]+($this->ASN_LIST[$dstAS]["altitude"]-$this->ASN_LIST[$srcAS]["altitude"])*$i*$delta/$dist)."\n";
	            $kmlString.=$c->lng.",".$c->lat.",".$this->ASN_LIST[$srcAS]["altitude"]."\n";
	        }
	        $kmlString.=$dstLNG.",".$dstLAT.",".$this->ASN_LIST[$dstAS]["altitude"]."\n</coordinates>\n</LineString>\n</Placemark>\n"; //last coordinate
		}
		return $kmlString;
	}
  
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function generateKML()
	{
		$kml_header = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document><name>PoP Map</name>\n';
		$kml_footer = '</Document></kml>';
		$kml_body = '';
	  	if(DRAW_CIRCLES)
	  	{
	  		$kml_body.="<Folder><name>PoP Location Convergence Radiuses</name>";
			foreach($this->CIRCLES as $circle)
			{
				$kml_body.=$this->kmlCircle($circle);
			}
			$kml_body.="</Folder></Folder>\n\n";
	  	}
	  
		$kml_body.="<Folder><name>PoP Location PlaceMarks</name>";
		foreach($this->LOC_2_POP_MAP as $placeMark)
		{
			$kml_body.=$this->kmlPlaceMark($placeMark);
		}
		$kml_body.="</Folder></Folder>\n\n";
		
		$kml_body.="<Folder><name>PoP Edges</name>";
		
		foreach($this->EDGES as $link)
		{
			$kml_body.=$this->kmlLink($link);
		}
		$kml_body.="</Folder>\n\n";
		$this->kmlString = $kml_header.$kml_body.$kml_footer;
	}
	
	public function writeKMZ()
	{
		
		$this->generateKML();
		
		//write generated KML file to disk
		// use a random 5-digit number appended to the date for the name of the kml file
		$day = date("m-d-y-");
		srand( microtime() * 1000000);
		$randomnum = rand(10000,99999);
		$file_prefix = $day.$randomnum;
		$file_ext = $file_prefix.'.kml';
		$filename = ($this->kml_dst_dir.'/'.$file_ext);
		
		// define initial write and appends
		$filewrite = fopen($filename, "w");
		//$fileappend = fopen($filename, "a");
		
		// open file and write header:
		fwrite($filewrite, $this->kmlString);
		fclose($filewrite);
		
		// generate the .kmz file
		$zip = new ZipArchive();
		$zip_file_ext = $file_prefix.'.kmz';
		$zip_filename = $this->kml_dst_dir.'/'.$zip_file_ext;
		$this->filename = $zip_filename;
		
		if ($zip->open($zip_filename, ZIPARCHIVE::CREATE)!==TRUE) {
		   exit("cannot open <$zip_filename>\n");
		}
		$zip->addFile($filename,"doc.kml");
		//echo "numfiles: " . $zip->numFiles . "\n";
		$zip->close();
		// finally, delete the original .kml file
		unlink($filename);
	}

	public function getFileName()
	{
		return $this->filename;
	}
} 
?>