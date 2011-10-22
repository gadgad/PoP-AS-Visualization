<?php
require_once("bin/load_config.php");
require_once("bin/color.php");
require_once("bin/idgen.php");
require_once('bin/userData.php');
require_once('bin/colorManager.php');
require_once('bin/xml_chunk_reader.php');

// global constants
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
	private $bufferSize;
	private $tempFileName;
	private $fileWrite;
	
	private $pop_xml;
	private $edges_xml;
	private $asn_info_xml;
	
	private $COLOR_LIST;
	private $USER_COLOR_LIST;	
	private $ASN_LIST;
	private $EDGES;
	private $PLACEMARKS;
	private $LOC_2_POP_MAP;
	private $CIRCLES;
	
	private $xml_src_dir;
	private $kml_dst_dir;
	private $idg;
	private $num_of_asns;
	
	private $cm;
	
	public function __construct($queryID)
	{
		
		include_once('bin/kml_render_globals.php');
		
		$this->queryID = $queryID;
		$this->idg = new idGen($queryID);
		
		$this->xml_src_dir = $this->kml_dst_dir = 'queries/'.$this->idg->getDirName();
		$this->bufferSize = renderKMLMemoryBufferSize; // Bytes
		$this->tempFileName = $this->kml_dst_dir.'/result.kml';
		$this->kmlString = '';
		
		$this->num_of_asns = 0;
		
		$this->asn_info_xml = simplexml_load_file('xml/ASN_info.xml');
		//$this->pop_xml = simplexml_load_file($this->xml_src_dir."/pop.xml");
		//$this->edges_xml = simplexml_load_file($this->xml_src_dir."/edges.xml");
		$this->pop_xml_reader = new XMLChunkReader($this->xml_src_dir,'pop');
		$this->edges_xml_reader = new XMLChunkReader($this->xml_src_dir,'edges');
		
		$this->ASN_LIST=array();
		$this->EDGES=array();
		$this->PLACEMARKS=array();
		$this->LOC_2_POP_MAP=array();
		$this->CIRCLES=array();
		
		$this->cm = new colorManager($GLOBALS["username"],$queryID);
		$this->COLOR_LIST =& $this->cm->GLOBAL_COLOR_LIST;
		$this->USER_COLOR_LIST = $this->cm->getColorList();
		$this->parseXML();
		$this->cm->save_global_color_list();
		
		$this->round = 0;
	}

	private function dispatchAltitude(){
	    static  $altitude = INITIAL_ALTITUDE;
		$alt = $altitude; 
	    $altitude+=ALTITUDE_DELTA;
	    return $alt;
	}
	
	private function parseXML()
	{
		while($this->pop_xml = $this->pop_xml_reader->loadNext()){
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
				$asn = intval($pop->ASN);
				if(!array_key_exists($asn, $this->ASN_LIST)) $this->ASN_LIST[$asn] = array(); 
			}
			unset($this->pop_xml);
		}
		 
		while($this->edges_xml = $this->edges_xml_reader->loadNext()){
			$edges = $this->edges_xml->children();
			foreach($edges as $edge)
			{
				$srcPOP = (string)$edge->Source_PoPID;
				$dstPOP = (string)$edge->Dest_PoPID;
				$srcAS = intval($edge->SourceAS);
				$dstAS = intval($edge->DestAS);
				
				if($srcPOP!="NULL" && $dstPOP!="NULL" && $srcPOP!=$dstPOP
				&& array_key_exists($srcAS, $this->ASN_LIST)
				&& array_key_exists($dstAS, $this->ASN_LIST)
				&& array_key_exists($srcPOP, $this->PLACEMARKS) 
				&& array_key_exists($dstPOP, $this->PLACEMARKS))
				{
					if((INTRA_CON && ($srcAS==$dstAS)) || (INTER_CON && ($srcAS!=$dstAS)))
					{
						if(isset($this->PLACEMARKS[$srcPOP]["lat"]) &&
						   isset($this->PLACEMARKS[$srcPOP]["lng"]) &&
						   isset($this->PLACEMARKS[$dstPOP]["lat"]) &&
						   isset($this->PLACEMARKS[$dstPOP]["lng"])) {
								$src_lat = $this->PLACEMARKS[$srcPOP]["lat"];
								$src_lng = $this->PLACEMARKS[$srcPOP]["lng"];
								$dst_lat = $this->PLACEMARKS[$dstPOP]["lat"];
								$dst_lng = $this->PLACEMARKS[$dstPOP]["lng"];
						} else {
							continue;
						}
						
						if(($src_lat!=$dst_lat) || ($src_lng!=$dst_lng)){
						    $edge_str = $edge->Source_PoPID.$edge->Dest_PoPID;
							$this->PLACEMARKS[(string)($edge->Source_PoPID)]["connected"]=true;
							$this->PLACEMARKS[(string)($edge->Dest_PoPID)]["connected"]=true;
							$this->ASN_LIST[$srcAS]["connected"]=true;
							$this->ASN_LIST[$dstAS]["connected"]=true;
							
							$conType = ($srcAS == $dstAS)? 'intra':'inter';
							if(!isset($this->EDGES[$srcAS][$conType]))
								$this->EDGES[$srcAS][$conType] = array();
							
						    if(!array_key_exists($edge_str, $this->EDGES[$srcAS][$conType])){
						     	$this->EDGES[$srcAS][$conType][$edge_str] = array("SourceAS"=>intval($edge->SourceAS),
												     							  "DestAS"=>intval($edge->DestAS),
												     							  "SourcePoP"=>$srcPOP,
												     							  "DestPoP"=>$dstPOP,
												     							  "numOfEdges"=>intval($edge->NumOfEdges));
							}						 
					    }
					}
				}
			} // end of edges loop
			unset($this->edges_xml);
		}
		
		//sort the multi-dimensional EDGES array
		ksort($this->EDGES);
		foreach($this->EDGES as $as=>$con){
			ksort($this->EDGES[$as]);
		}
		
		if(!CONNECTED_POPS_ONLY){
			$this->num_of_asns = count($this->ASN_LIST);
		} else {
			foreach($this->ASN_LIST as $asn=>$arr){
				if(isset($arr["connected"]))
					$this->num_of_asns++;
			}
		}
		unset($this->ASN_LIST);
		$this->ASN_LIST = array();
		
		if(USE_COLOR_PICKER) $cp = new ColorPicker($this->num_of_asns);
		
		$this->pop_xml_reader->resetReader();
		while($this->pop_xml = $this->pop_xml_reader->loadNext()){
			foreach($this->pop_xml->children() as $pop)
			{
			  	$asn = intval($pop->ASN);
				$placemark =  $this->PLACEMARKS[(string)$pop->PoPID];
			  	$pop_connected = (isset($placemark["connected"])) ? true : false;
			  	if(!CONNECTED_POPS_ONLY || $pop_connected)
				{
					if(!array_key_exists($asn, $this->LOC_2_POP_MAP)) $this->LOC_2_POP_MAP[$asn] = array();
				  	$pop_str = $pop->ASN.$pop->LAT2.$pop->LNG2;
					if(!array_key_exists($pop_str, $this->LOC_2_POP_MAP[$asn])){
				  		$this->LOC_2_POP_MAP[$asn][$pop_str] = array("numOfPoPS"=>1,"asn"=>intval($pop->ASN),"lat"=>floatval($pop->LAT2),"lng"=>floatval($pop->LNG2),"pop_id_lst"=>array($pop->PoPID));
					} else {
						$this->LOC_2_POP_MAP[$asn][$pop_str]["numOfPoPS"]++;
						$this->LOC_2_POP_MAP[$asn][$pop_str]["pop_id_lst"][] = $pop->PoPID;
					}
					
					if(!array_key_exists($asn,$this->ASN_LIST)){
					  if(isset($this->USER_COLOR_LIST['asn'][$asn])){
					  	$asn_color = $this->USER_COLOR_LIST['asn'][$asn];
					  }elseif(isset($this->COLOR_LIST['asn'][$asn])){
					  	$asn_color = $this->COLOR_LIST['asn'][$asn];
					  } else {
					  	do {
					  		$asn_color = (USE_COLOR_PICKER)? $cp->getColor() : new Color();
						} while(isset($this->COLOR_LIST['color'][$asn_color->web_format()]));
						$asn_color->setTrans(255-TRANSPARENCY);
						// TODO: add implementation of k-d-tree & k-nearest alg', making sure euclidean dist' of curr color from it's nearest neighbour is bigger than treshold...
						// this will be an efficient method to enforce color diversity. 
						$this->COLOR_LIST['color'][$asn_color->web_format()] = $asn;
						$this->COLOR_LIST['asn'][$asn] = $asn_color;
					  }
					  $this->COLOR_LIST['asn'][$asn]->setTrans(255-TRANSPARENCY);
					  
					  
					  $this->ASN_LIST[$asn]= array("color"=>$asn_color, "altitude"=>$this->dispatchAltitude());
					  $asn_info = $this->asn_info_xml->xpath("/DATA/ROW[ASNumber=".$asn."]");
					  if(!empty($asn_info))
					  {
					  	$this->ASN_LIST[$asn]["Country"] = $asn_info[0]->Country;
						$this->ASN_LIST[$asn]["ISPName"] = $asn_info[0]->ISPName;
					  } else {
					  	$this->ASN_LIST[$asn]["Country"] = "unknown";
						$this->ASN_LIST[$asn]["ISPName"] = "unknown";
					  }
				  	}
			  	}
				
				if(DRAW_CIRCLES && (!CONNECTED_POPS_ONLY || $pop_connected)){
			    	//we only need LAT2,LNG2,Accuracy2
			    	//radius = Accuracy2*110000 [in meters]
			    	$this->CIRCLES[$asn][] = array("lat"=>floatval($pop->LAT2),"lng"=>floatval($pop->LNG2),"radius"=>floatval($pop->Accuracy2)*110000,"asn"=>intval($pop->ASN),"popID"=>$pop->PoPID);
				}	
		  }
		  unset($this->pop_xml);
	  }
	
	  ksort($this->LOC_2_POP_MAP);
	  ksort($this->CIRCLES);  
	}
	
	private function kmlPlaceMark($placeMark)
	{
		$kmlString = '';
		static $counter=1;
		$kmlString = '';
		
		$asn = $placeMark["asn"];
		$lng = $placeMark["lng"];
		$lat = $placeMark["lat"];
		$numOfPoPS = $placeMark["numOfPoPS"];
		
		$pop_lst_str="<P>Num Of PoPS:".$numOfPoPS."</BR>";
		for($i=0;$i<$numOfPoPS; $i++)
		{
			$pop_lst_str.="PoP ID: ".$placeMark["pop_id_lst"][$i]."</BR>\n";
		}
		$pop_lst_str.="</P>";
		$icon_href = "http://www.google.com/chart?chst=";
		$icon_href.= (ASN_EMBEDDED_IN_PLACEMARK)? "d_map_spin&chld=1|0|".$this->ASN_LIST[$asn]["color"]->web_format()."|10|_|".$asn : "d_map_xpin_letter&chld=pin||".$this->ASN_LIST[$asn]["color"]->web_format();
		$kmlString .= "<Placemark>\n<name>PlaceMark#".$counter++."</name>\n<description><![CDATA[<P>ASN#: ".$asn."</BR>ISP Name: ".$this->ASN_LIST[$asn]["ISPName"]."</BR>Country: ".$this->ASN_LIST[$asn]["Country"]."</P>".$pop_lst_str."]]></description><visibility>1</visibility>\n<Style>\n<LabelStyle>\n<scale>0</scale>\n</LabelStyle>\n<IconStyle>\n<Icon>\n<href><![CDATA[".$icon_href."]]></href>\n</Icon>\n</IconStyle>\n<LineStyle>\n<width>2</width>\n<color>".$this->ASN_LIST[$asn]["color"]->gm_format()."</color>\n</LineStyle>\n</Style>\n<Point>\n<extrude>1</extrude>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n".$lng.",".$lat.",".$this->ASN_LIST[$asn]["altitude"]."\n</coordinates>\n</Point>\n</Placemark>\n";
		
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
			
			//EDGES_COLORING_SCHEME
			$src_color = $this->ASN_LIST[$srcAS]["color"];
			$static_color = (($srcAS == $dstAS)? new Color(EDGES_INTRA_COLOR) : new Color(EDGES_INTER_COLOR));
			$linkColorArr = array('bySrcAS'=>$src_color, 'static'=>$static_color);
			$linkColor = $linkColorArr[EDGES_COLORING_SCHEME];
			if(!is_object($linkColor))
				$linkColor = $src_color;
			
	        $kmlString.="<Placemark>\n<name>Edge#".$counter++."</name>\n<description>\n<![CDATA[<A href=\"edgeDetails.php?src_pop=".$srcPOP."&dst_pop=".$dstPOP."&QID=".$this->queryID."&numOfEdges=".$numOfEdges."\" target=\"_blank\">Open in new window</A></BR><div class=\"ifrm\"><iframe name=\"ifrm\" id=\"ifrm\" src=\"edgeDetails.php?src_pop=".$srcPOP."&dst_pop=".$dstPOP."&QID=".$this->queryID."&numOfEdges=".$numOfEdges."\" frameborder=\"0\" width=\"100%\" height=\"90%\">Your browser doesn't support iframes.</iframe></div>]]>\n</description>\n<visibility>1</visibility>\n<Style>\n<LineStyle>\n<color>".($linkColor->gm_format())."</color>\n<width>".max(MIN_LINE_WIDTH,min($link["numOfEdges"],MAX_LINE_WIDTH))."</width>\n</LineStyle>\n<PolyStyle>\n<color>".($linkColor->gm_format())."</color>\n</PolyStyle>\n</Style>\n<LineString>\n<tessellate>1</tessellate>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n";
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
	private function flushToDisk($force)
	{
		// flush generated XML to disk , if current output buffer is bigger than 'bufferSize'				
		if(strlen($this->kmlString)>=$this->bufferSize || $force){
			if($fwrite = fwrite($this->fileWrite, $this->kmlString)){
				//$bytesWritten+=$fwrite;
				unset($this->kmlString);
				$this->kmlString = '';
			} else {
				return false;
			}
		}
		return true;	
	}
	
	private function generateKML()
	{
		$this->fileWrite = fopen($this->tempFileName, "w");
			
		$kml_header = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document><name>PoP Map</name>'."\n";
		$kml_footer = '</Document></kml>';
		$this->kmlString = $kml_header;
	  	if(DRAW_CIRCLES)
	  	{
	  		$this->kmlString.="<Folder><name>PoP Location Convergence Radiuses</name>";
			foreach($this->CIRCLES as $as=>$arr)
			{
				$this->kmlString.="<Folder>\n<name>ASN: ".$as."</name>\n";
				foreach($arr as $circle){
					$this->kmlString.=$this->kmlCircle($circle);
				}
				$this->kmlString.="</Folder>\n";
				$this->flushToDisk(false);	
			}
			$this->kmlString.="</Folder>\n\n";
	  	}
		
		$this->kmlString.="<Folder><name>PoP Edges</name>";
		foreach($this->EDGES as $as=>$arr1){
			$this->kmlString.="<Folder>\n<name>ASN: ".$as."</name>\n";
			foreach($arr1 as $type=>$arr2){
				$this->kmlString.="<Folder>\n<name>".$type."-connectivity</name>\n";
				foreach($arr2 as $link){
					$this->kmlString.=$this->kmlLink($link);
				}
				$this->kmlString.="</Folder>\n";
				$this->flushToDisk(false);	
			}
			$this->kmlString.="</Folder>\n";
		}
		$this->kmlString.="</Folder>\n\n";
		
		$this->kmlString.="<Folder><name>PoP Location PlaceMarks</name>";
		foreach($this->LOC_2_POP_MAP as $as=>$arr)
		{
			$this->kmlString.="<Folder>\n<name>ASN: ".$as."</name>\n";
			foreach($arr as $placeMark){
				$this->kmlString.=$this->kmlPlaceMark($placeMark);
			}
			$this->kmlString.="</Folder>\n";
			$this->flushToDisk(false);	
		}
		$this->kmlString.="</Folder>\n\n";
		
		//$this->kmlString.=(!BLACK_BACKGROUND)? '':"<Folder><name>Black Background</name>\n<GroundOverlay>\n<name>Blank Earth</name>\n<Icon>\n<href>files\untitled.bmp</href>\n<viewBoundScale>0.75</viewBoundScale>\n</Icon>\n<LatLonBox>\n<north>90</north>\n<south>-90</south>\n<east>180</east>\n<west>-180</west>\n</LatLonBox>\n</GroundOverlay>\n</Folder>\n";
	
		$this->kmlString .= $kml_footer;
		$this->flushToDisk(true);
		fclose($this->fileWrite);
	}
	
	public function writeKMZ($userSpecific)
	{
		
		$this->generateKML();
		
		// generate the .kmz file
		$zip = new ZipArchive();
		$zip_filename = ($this->kml_dst_dir.'/'.(($userSpecific)? ($GLOBALS["username"].'-'):'').'result.kmz');
		$this->filename = $zip_filename;
		
		if ($zip->open($zip_filename, ZIPARCHIVE::CREATE)!==TRUE) {
		   exit("cannot open <$zip_filename>\n");
		}
		$zip->addFile($this->tempFileName,"doc.kml");
		//if(BLACK_BACKGROUND) $zip->addFile('images/black.bmp','files/untitled.bmp');
		//echo "numfiles: " . $zip->numFiles . "\n";
		$zip->close();
		// finally, delete the original .kml file
		unlink($this->tempFileName);
		return true;
	}

	public function getFileName()
	{
		return $this->filename;
	}
} 
?>