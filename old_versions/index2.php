<?php
$pop_xml = simplexml_load_file("xml\pop.xml");
//$EDGES_xml = simplexml_load_file("xml\edges_lat_lng.xml");
$EDGES_xml = simplexml_load_file("xml\edges.xml");
$asn_info_xml = simplexml_load_file("xml\ASN_info.xml");

$ASN_LIST=array();
$EDGES=array();
$POP_2_LOC_MAP=array();
$LOC_2_POP_MAP=array();

define('MIN_LINE_WIDTH',3);
define('MAX_LINE_WIDTH',5);
define('INITIAL_ALTITUDE',10);
define('ALTITUDE_DELTA',5000);
define('MAX_EDGES_RESULTS',10);
define('DRAW_CIRCLES',true);
define('INTER_CON',true);
define('INTRA_CON',true);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Color
{
	public $trans;
	public $red;
	public $green;
	public $blue;
	
	function __construct()
	{
		$this->randColor();
	}
	
	function randColor()
	{
		$this->red = rand(0,255);
		$this->green = rand(0,255);
		$this->blue = rand(0,255);
		$this->trans = 150;
	}
	
	public function gm_format()
	{
		return dechex($this->trans).dechex($this->blue).dechex($this->green).dechex($this->red);
	}
	
	public function web_format()
	{
		return dechex($this->red).dechex($this->green).dechex($this->blue);
	}
	
}

//////////////////////////////////////////////////////////////////////////////////////////////////////

function dispatchAltitude(){
    static  $altitude = INITIAL_ALTITUDE; 
    $altitude+=ALTITUDE_DELTA;
    return $altitude;
}

function kmlPlaceMark($placeMark)
{
	$kmlString = '';
  	static $firstTime = true;
	static $counter=1;
	static $ASN_TMP_MAP = array();
  	global $ASN_LIST;
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
	$kmlString .= "<Placemark>\n<name>PlaceMark#".$counter++."</name>\n<description><![CDATA[<P>ASN#: ".$asn."</BR>ISP Name: ".$ASN_LIST[$asn]["ISPName"]."</BR>Country: ".$ASN_LIST[$asn]["Country"]."</P>".$pop_lst_str."]]></description><visibility>1</visibility>\n<Style>\n<LabelStyle>\n<scale>0</scale>\n</LabelStyle>\n<IconStyle>\n<Icon>\n<href><![CDATA[http://www.google.com/chart?chst=d_map_xpin_letter&chld=pin||".$ASN_LIST[$asn]["color"]->web_format()."]]></href>\n</Icon>\n</IconStyle>\n<LineStyle>\n<width>2</width>\n</LineStyle>\n</Style>\n<Point>\n<extrude>1</extrude>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n".$lng.",".$lat.",".$ASN_LIST[$asn]["altitude"]."\n</coordinates>\n</Point>\n</Placemark>\n";
	
	$firstTime = false;
 	return $kmlString;
}

function kmlCircle($lat,$lng,$radius,$asn,$popID){
  $centerlat_form = $lat;
  $centerlong_form = $lng;
  $d= $radius;
  $kmlString = '';
  
  static $ASN_TMP_MAP = array();
  static $firstTime = true;
  global $ASN_LIST;
  
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
$kmlString .= "<Placemark>\n<name>pop id:".$popID."</name>\n<description><![CDATA[<P>ASN Number: ".$asn."</P><P>ISP Name: ".$ASN_LIST[$asn]["ISPName"]."</P><P>Country: ".$ASN_LIST[$asn]["Country"]."</P>]]></description>\n<visibility>1</visibility>\n<Style>\n<LineStyle>\n<color>".($ASN_LIST[$asn]["color"]->gm_format())."</color>\n<width>".MIN_LINE_WIDTH."</width>\n</LineStyle>\n<PolyStyle>\n<color>".($ASN_LIST[$asn]["color"]->gm_format())."</color>\n<fill>0</fill>\n</PolyStyle>\n</Style>\n<Polygon>\n<extrude>0</extrude>\n<tessellate>0</tessellate>\n<altitudeMode>relativeToGround</altitudeMode>\n<outerBoundaryIs>\n<LinearRing>\n<coordinates>\n";
// loop through the array and write path linestrings
for($i=0; $i<=360; $i++) {
  $radial = deg2rad($i);
  $lat_rad = asin(sin($lat1)*cos($d_rad) + cos($lat1)*sin($d_rad)*cos($radial));
  $dlon_rad = atan2(sin($radial)*sin($d_rad)*cos($lat1),cos($d_rad)-sin($lat1)*sin($lat_rad));
  $lon_rad = fmod(($long1+$dlon_rad + M_PI), 2*M_PI) - M_PI;
  $kmlString.=rad2deg($lon_rad).",".rad2deg($lat_rad).",".$ASN_LIST[$asn]["altitude"]."\n";
}
$kmlString.="</coordinates>\n</LinearRing>\n</outerBoundaryIs>\n</Polygon>\n</Placemark>\n";
 
 $firstTime = false;
 return $kmlString;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$precision = 4; //precision of floating point calculations
$R = 6371; // earth raius in km

class Coordinate
{
    public $lat;
    public  $lng;

    function _construct($lat,$lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }
}

function calcDist($lat1,$lat2,$lon1,$lon2)
{
    global $precision;
    global $R;

    $dLat = deg2rad($lat2-$lat1);
    $dLon = deg2rad($lon2-$lon1);
    $lat1 = deg2rad($lat1);
    $lat2=deg2rad($lat2);
    // based on haversine formula:
    $a = pow(sin($dLat/2),2)+pow(sin($dLon/2),2)*cos($lat1)*cos($lat2);
    $c = 2*atan2(sqrt($a),sqrt(1-$a));
    $d = $R*$c;
    return round($d,$precision);
}

function calcMidPoint($lat1,$lat2,$lon1,$lon2)
{
    //$dLat = deg2rad($lat2-$lat1);
    $dLon = deg2rad($lon2-$lon1);
    $lat1 = deg2rad($lat1);
    $lat2=deg2rad($lat2);
    $Bx = cos($lat2) *cos($dLon);
    $By = cos($lat2) * sin($dLon);
    $lat3 = atan2(sin($lat1)+sin($lat2),sqrt( (cos($lat1)+$Bx)*(cos($lat1)+$Bx) + $By*$By) );
    $lon3 = deg2rad($lon1) +atan2($By,cos($lat1) + $Bx);

    $c = new Coordinate();
    $c->lat = rad2deg($lat3);
    $c->lng= rad2deg($lon3);
    return $c;
}

function calcBearing($lat1,$lat2,$lon1,$lon2)
{
    $dLon = deg2rad($lon2-$lon1);
    $lat1 = deg2rad($lat1);
    $lat2=deg2rad($lat2);

    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1)*sin($lat2) -sin($lat1)*cos($lat2)*cos($dLon);
    $brng = rad2deg(atan2($y,$x));
    return $brng;
}

function calcDestPoint($lat1,$lon1,$dist,$brng)
{
    global $R;
    $d = $dist/$R;  // convert dist to angular distance in radians
    $b = deg2rad($brng);
    $lat1 = deg2rad($lat1);  $lon1 = deg2rad($lon1);

    $lat2 = asin(sin($lat1)*cos($d) + cos($lat1)*sin($d)*cos($b) );
    $lon2 = $lon1 + atan2(sin($b)*sin($d)*cos($lat1), cos($d)-sin($lat1)*sin($lat2));
    //$lon2 = ($lon2+3*M_PI)%(2*M_PI) - M_PI;  // normalise to -180...+180

    $c = new Coordinate();
    $c->lat = rad2deg($lat2);
    $c->lng= rad2deg($lon2);
    return $c;
}

function kmlLink($link){
     global $ASN_LIST;
     global $EDGES;
	 global $POP_2_LOC_MAP;
	 static $counter=1;
    $kmlString = "";
	
	if($link["SourcePoP"]!="NULL" && $link["DestPoP"]!="NULL" && array_key_exists($link["SourcePoP"], $POP_2_LOC_MAP) && array_key_exists($link["DestPoP"], $POP_2_LOC_MAP)){
		$srcLAT = ($link["Source_LAT"]!=0)?$link["Source_LAT"]:$POP_2_LOC_MAP[$link["SourcePoP"]]["lat"];
		$srcLNG = ($link["Source_LNG"]!=0)?$link["Source_LNG"]:$POP_2_LOC_MAP[$link["SourcePoP"]]["lng"];
		$dstLAT = ($link["Dest_LAT"]!=0)?$link["Dest_LAT"]:$POP_2_LOC_MAP[$link["DestPoP"]]["lat"];
		$dstLNG = ($link["Dest_LNG"]!=0)?$link["Dest_LNG"]:$POP_2_LOC_MAP[$link["DestPoP"]]["lng"];
		
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
		
        $kmlString.="<Placemark>\n<name>Edge#".$counter++."</name>\n<description>\n<![CDATA[\n<P>Source AS: ".$srcAS."</BR>Dest AS: ".$dstAS."</BR>Source PoP: ".$srcPOP."</BR>Dest PoP: ".$dstPOP."</P>".$edge_lst_str."\n]]>\n</description>\n<visibility>1</visibility>\n<Style>\n<LineStyle>\n<color>".($ASN_LIST[$srcAS]["color"]->gm_format())."</color>\n<width>".max(MIN_LINE_WIDTH,min($link["numOfEdges"],MAX_LINE_WIDTH))."</width>\n</LineStyle>\n<PolyStyle>\n<color>".($ASN_LIST[$srcAS]["color"]->gm_format())."</color>\n</PolyStyle>\n</Style>\n<LineString>\n<tessellate>1</tessellate>\n<altitudeMode>relativeToGround</altitudeMode>\n<coordinates>\n";
        $kmlString.=$srcLNG.",".$srcLAT.",".$ASN_LIST[$srcAS]["altitude"]."\n"; //first coordinate
        $dist = calcDist($srcLAT, $dstLAT,$srcLNG, $dstLNG);
        $brng = calcBearing($srcLAT, $dstLAT,$srcLNG, $dstLNG);
        $delta = 50; // segmentation factor in KM
        $numOfSegments = floor($dist/$delta);
        for($i=1;$i<$numOfSegments; $i++){
            $c = calcDestPoint($srcLAT, $srcLNG, $i*$delta, $brng);
            //$kmlString.=$c->lng.",".$c->lat.",".($ASN_LIST[$srcAS]["altitude"]+($ASN_LIST[$dstAS]["altitude"]-$ASN_LIST[$srcAS]["altitude"])*$i*$delta/$dist)."\n";
            $kmlString.=$c->lng.",".$c->lat.",".$ASN_LIST[$srcAS]["altitude"]."\n";
        }
        $kmlString.=$dstLNG.",".$dstLAT.",".$ASN_LIST[$dstAS]["altitude"]."\n</coordinates>\n</LineString>\n</Placemark>\n"; //last coordinate
    }
    return $kmlString;
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$kml_header = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document><name>PoP Map</name>'."\n";
$kml_footer='</Document></kml>';
$kml_body = '';

if(DRAW_CIRCLES){
	$kml_body.="<Folder><name>PoP Location Convergence Radiuses</name>";
}

foreach($pop_xml->children() as $pop)
  {
  	$pop_str = $pop->ASN.$pop->LAT2.$pop->LNG2;
	if(!array_key_exists($pop_str, $LOC_2_POP_MAP)){
  		$LOC_2_POP_MAP[$pop_str] = array("numOfPoPS"=>1,"asn"=>intval($pop->ASN),"lat"=>floatval($pop->LAT2),"lng"=>floatval($pop->LNG2),"pop_id_lst"=>array($pop->PoPID));
	} else {
		$LOC_2_POP_MAP[$pop_str]["numOfPoPS"]++;
		$LOC_2_POP_MAP[$pop_str]["pop_id_lst"][] = $pop->PoPID;
	}
	
	$asn = intval($pop->ASN);
	if(!array_key_exists($asn,$ASN_LIST)){
	  $ASN_LIST[$asn]= array("color"=>  new Color(),"altitude"=>  dispatchAltitude());;
	  $asn_info = $asn_info_xml->xpath("/DATA/ROW[ASNumber=".$asn."]");
	  if(!empty($asn_info))
	  {
	  	$ASN_LIST[$asn]["Country"] = $asn_info[0]->Country;
		$ASN_LIST[$asn]["ISPName"] = $asn_info[0]->ISPName;
	  }
  	}
	
	if(DRAW_CIRCLES){
    	//we only need LAT2,LNG2,Accuracy2
    	//radius = Accuracy2*110000 [in meters]
    	$kml_body.=kmlCircle(floatval($pop->LAT2),floatval($pop->LNG2), floatval($pop->Accuracy2)*110000,intval($pop->ASN),$pop->PoPID);
	}
	
	// generate map of PoP coordinates
    if(!array_key_exists((string)$pop->PoPID, $POP_2_LOC_MAP)){
    	$POP_2_LOC_MAP[(string)$pop->PoPID] = array("lat"=>floatval($pop->LAT2), "lng"=>floatval($pop->LNG2));
	}
  }
  
  if(DRAW_CIRCLES){
  	$kml_body.="</Folder></Folder>\n\n";
  }
  
  $kml_body.="<Folder><name>PoP Location PlaceMarks</name>";
  foreach($LOC_2_POP_MAP as $placeMark)
  {
  	$kml_body.=kmlPlaceMark($placeMark);
  }
  $kml_body.="</Folder></Folder>\n\n";

  foreach($EDGES_xml->children() as $edge)
  {
	if(((string)$edge->Source_PoPID != (string)$edge->Dest_PoPID))
	{
		if((INTRA_CON && (intval($edge->SourceAS)==intval($edge->DestAS))) || (INTER_CON && (intval($edge->SourceAS)!=intval($edge->DestAS))))
		{
		    $edge_str = $edge->Source_PoPID.$edge->Dest_PoPID;
		    if(!array_key_exists($edge_str, $EDGES)){
		     	$EDGES[$edge_str] = array("SourceAS"=>intval($edge->SourceAS),
		     							  "DestAS"=>intval($edge->DestAS),
		     							  "SourcePoP"=>(string)($edge->Source_PoPID),
		     							  "DestPoP"=>(string)($edge->Dest_PoPID),
		     							  "Source_LAT"=>floatval($edge->Source_LAT),
		     							  "Source_LNG"=>floatval($edge->Source_LNG),
		     							  "Dest_LAT"=>floatval($edge->Dest_LAT),
		     							  "Dest_LNG"=>floatval($edge->Dest_LNG),
		     							  "numOfEdges"=>1,
		     							  "median_lst"=>array(floatval($edge->Median)),
		     							  "edgeID_lst"=>array($edge->edgeid),
		     							  "src_ip_lst"=>array($edge->SourceIP),
		     							  "dest_ip_lst"=>array($edge->DestIP));
		    } else {
		    	$EDGES[$edge_str]["numOfEdges"]++;
				$EDGES[$edge_str]["median_lst"][] = floatval($edge->Median);
		    	$EDGES[$edge_str]["edgeID_lst"][] = $edge->edgeid;
		    	$EDGES[$edge_str]["src_ip_lst"][] = $edge->SourceIP;
		    	$EDGES[$edge_str]["dest_ip_lst"][] = $edge->DestIP;
	    	}
		}
  	}
  }
  
  $kml_body.="<Folder><name>PoP Edges</name>";
  foreach($EDGES as $link)
  {
  	$kml_body.=kmlLink($link);
  }
  $kml_body.="</Folder>\n\n";
  
  $kmlString = $kml_header.$kml_body.$kml_footer;

 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//write generated KML file to disk
// use a random 5-digit number appended to the date for the name of the kml file
$day = date("m-d-y-");
srand( microtime() * 1000000);
$randomnum = rand(10000,99999);
$file_ext = $day.$randomnum.'.kml';
$filename = ('temp/'.$file_ext);

// define initial write and appends
$filewrite = fopen($filename, "w");
//$fileappend = fopen($filename, "a");

// open file and write header:
fwrite($filewrite, $kmlString);
fclose($filewrite);
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  $full_url = "http://" . $_SERVER['HTTP_HOST']  .dirname( $_SERVER['REQUEST_URI'])."/temp/".$file_ext;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
           <script src="//www.google.com/jsapi?key=ABQIAAAAMYziiEA_p76rk0jQj-KuSxT2nJhKI2M38oLtuci1HSJ_Ifdp2xRIFnyKy8GINXA9s1Ks6Q-g9-O8Pw"></script>
           <script type="text/javascript">
              var ge;
              google.load("earth", "1");

              function init() {
                 google.earth.createInstance('map3d', initCB, failureCB);
              }

              function initCB(instance) {
                 ge = instance;
                 ge.getWindow().setVisibility(true);

                 var link = ge.createLink('');
                 var href = '<?php echo("$full_url");?>'
                 link.setHref(href);

                 var networkLink = ge.createNetworkLink('');
                 networkLink.set(link, true,true); // Sets the link, refreshVisibility, and flyToView.

                 ge.getFeatures().appendChild(networkLink);
              }

              function failureCB(errorCode) {
              }

              google.setOnLoadCallback(init);
           </script>
    </head>
    <body>
          <?php
            if(file_exists($filename)) {
              echo ("<p>to download source kml file <a href=\"temp/$file_ext\">click here</a>.</p>");
            } else {
              echo( "If you can see this, something is wrong..." );
            }
        ?>
         <div id="map3d" style="height:800px; width:1200px;"></div>
    </body>
</html>
