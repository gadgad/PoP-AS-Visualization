<?php
define('MIN_LINE_WIDTH',3);
define('MAX_LINE_WIDTH',5);
define('INITIAL_ALTITUDE',10);
define('ALTITUDE_DELTA',5000);
define('MAX_EDGES_RESULTS',10);
define('DRAW_CIRCLES',true);
define('INTER_CON',true);
define('INTRA_CON',true);
define('CONNECTED_POPS_ONLY',false);
define('USE_COLOR_PICKER',false);
define('DEFAULT_COLOR_PICKER_POOL_SIZE',isset($_POST["num_of_asns"])?$_POST["num_of_asns"]:10);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include("bin/kml_writer.php");

//write generated KML file to disk
$kmlWriter = new kmlWriter('xml','temp');
$kmlWriter->writeKMZ();
$filename=$kmlWriter->getFileName();
$full_url = "http://".$_SERVER['HTTP_HOST'].dirname( $_SERVER['REQUEST_URI'])."/".$filename;

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
              echo ("<p>to download source kml file <a href=\"$filename\">click here</a>.</p>");
            } else {
              echo( "If you can see this, something is wrong..." );
            }
        ?>
         <div id="map3d" style="height:800px; width:1200px;"></div>
    </body>
</html>
