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
    
	function getGlobalsPanel(){
		   	
		   	var globalParams = {
				MIN_LINE_WIDTH: [3,'number','Min Edge Line Width'],
				MAX_LINE_WIDTH: [5,'number','Max Edge Line Width'],
				INITIAL_ALTITUDE: [10,'number','Initial ASN Altitude'],
				ALTITUDE_DELTA: [5000,'number','ASN Altitude Delta'],
				DRAW_CIRCLES: [true,'checkbox','PoP Location Convergence Radiuses'],
				INTER_CON: [true,'checkbox','Inter-Connectivity'],
				INTRA_CON: [true,'checkbox','Intra-Connectivity'],
				CONNECTED_POPS_ONLY: [false,'checkbox','Connected PoPs only'],
				USE_COLOR_PICKER: [false,'checkbox','Web-Safe Color-Picking'],
				STDEV_THRESHOLD: [2,'number','Standard Deviation Threshold']
		   	}
		
		
		    // Create checkbox for each layer
		    var items = [];
		    for (param in globalParams){
		    	if(globalParams[param][1]=='checkbox') {
					items.push({
			            boxLabel: globalParams[param][2],
			            checked: globalParams[param][0],
			            hideLabel: true,
			            name: param
					});
				}
				if(globalParams[param][1]=='number') {
					items.push({
			            xtype: 'numberfield',
		                fieldLabel: globalParams[param][2],
		                minValue: 0,
		                value: globalParams[param][0],
		                //maxValue: 100,
		                //hideTrigger: true,
		                //allowDecimals: true,
		                //decimalPrecision: 1,
		                //step: 0.4
		                name: param
					});
				}
		    }
		
		    // Create FormPanel with all layers
		    var globalPanel = new Ext.FormPanel({
		        title: 'Kml Renderer Options',
		        defaultType: 'checkbox',
		        defaults: {
		            //hideLabel: true
		        },
		        items: items
		    });
		
		    return globalPanel;
		}

////////////////////////////////////////////////////////////////////////////////////////

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
            
            
            var downloadPanel = new Ext.Panel({
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
                controlPanel.add(getGlobalsPanel());
                controlPanel.add(earthPanel.getLocationPanel());
                controlPanel.add(earthPanel.getLayersPanel());
                controlPanel.add(earthPanel.getOptionsPanel());
                controlPanel.items.items[0].add(downloadPanel);
                
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