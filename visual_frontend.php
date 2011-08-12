<?php
require_once("bin/idgen.php");
$queryID = isset($_POST["QID"])? $_POST("QID") : '2df5efc4b99b9486e245a49f6400a90f';
$idg = new idGen($queryID);
$filename='queries/'.$idg->getDirName().'/result.kmz';
$full_url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI'])."/".$filename;
?>
<html>
<head>
    <title>Control Panel Example</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-2.2/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="css/Ext.ux.GEarthPanel-1.1.css" />
    <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAAvQLslBJZByOlS8Y3iPXgexSV5romlzgkIRRVpZz7TQ7Jsa0ZQxRh2GVXWb7jYX7ajChOO9olKH0Sgg"></script>
    <script type="text/javascript" src="http://extjs.cachefly.net/builds/ext-cdn-611.js"></script>
  	<script type="text/javascript" src="js/Ext.ux.GEarthPanel-1.1.js"></script>
    <script type="text/javascript">

        google.load("earth", "1");
        google.load("maps", "2.xx");

        Ext.onReady(function(){

            // Create Google Earth panel
            var earthPanel = new Ext.ux.GEarthPanel({
                region: 'center',
                contentEl: 'eastPanel',
                margins: '5 5 5 0'
            });

            // Create control panel
            var controlPanel = new Ext.Panel({
                region: 'west',
                contentEl: 'westPanel',
                title: 'Control Panel',
                width: 280,
                border: true,
                collapsible: true,
                // top , right , bottom , left
                margins: '5 0 5 0',
                layout: 'accordion',
                layoutConfig: {
                    animate: true
                },
                defaultType: 'panel',
                defaults: {
                    bodyStyle: 'padding: 10px'
                }
            });
            
            var bottomPanel = new Ext.Panel({
                region: 'south',
                contentEl: 'southPanel',
                title: 'more info',
                border: true,
                collapsible: true,
                // top , right , bottom , left
                //margins: '5 5 5 5',
                //cmargins: '5 0 0 0',
                height: 150,
    			minSize: 75,
    			maxSize: 250,
                defaultType: 'panel',
                defaults: {
                    bodyStyle: 'padding: 10px'
                }
            });
            
            var basicPanel = new Ext.Panel({
				contentEl: 'downloadPanel',
				border: false,
				defaultType: 'panel'	
			});

            // Add panels to browser viewport
            var viewport = new Ext.Viewport({
                layout: 'border',
                items: [ controlPanel, earthPanel ]
            });

            // Build control panel
            earthPanel.on('earthLoaded', function(){

                // Display KMLs
                earthPanel.fetchKml('<?php echo("$full_url");?>');

                // Add panels
                controlPanel.add(earthPanel.getKmlPanel());
                controlPanel.add(earthPanel.getLocationPanel());
                controlPanel.add(earthPanel.getLayersPanel());
                controlPanel.add(earthPanel.getOptionsPanel());
                controlPanel.items.items[0].add(basicPanel);
                
                controlPanel.doLayout();
                
                var ge = earthPanel.earth;
				google.earth.addEventListener(ge.getView(), 'viewchangeend', function() {
					ge.getFeatures().removeChild(earthPanel.networkLink);
				});
                

            });
        });

    </script>
</head>
<body>
    <div id="westPanel"></div>
    <div id="eastPanel"></div>
    <div id="southPanel"></div>
    <div id="downloadPanel">
    	  <?php
            if(file_exists($filename)) {
              echo ("<p><a href=\"$filename\">click here</a> to download kml file</p>");
            } else {
              echo( "If you can see this, something is wrong..." );
            }
        ?>
    </div>
</body>
</html>