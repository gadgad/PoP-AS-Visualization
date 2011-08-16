<?php
require_once("bin/idgen.php");
$queryID = isset($_POST["QID"])? $_POST("QID") : '2df5efc4b99b9486e245a49f6400a90f';
$idg = new idGen($queryID);
$filename='queries/'.$idg->getDirName().'/result.kmz';
$full_url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI'])."/".$filename."?".rand(0,10000000000);
?>
<html>
<head>
    <title>DIMES PoP/AS Visual FrontEnd</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<!-- includes for extJS 2.2 -->
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/resources/css/ext-all.css">
    <script type="text/javascript" src="js/ext-2.2/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="js/ext-2.2/ext-all.js"></script>

    <!--<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-2.2/resources/css/ext-all.css" />-->
    <!--<script type="text/javascript" src="http://extjs.cachefly.net/builds/ext-cdn-611.js"></script>-->
    <link rel="stylesheet" type="text/css" href="css/Ext.ux.GEarthPanel-1.1.css" />
    <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAAvQLslBJZByOlS8Y3iPXgexSV5romlzgkIRRVpZz7TQ7Jsa0ZQxRh2GVXWb7jYX7ajChOO9olKH0Sgg"></script>
  	<script type="text/javascript" src="js/Ext.ux.GEarthPanel-1.1.js"></script>
    <script type="text/javascript">
    
        google.load("earth", "1");
        google.load("maps", "2.xx");
        
        Ext.onReady(function(){
        	
        	Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
        		expires: new Date(new Date().getTime()+(1000*60*60*24*365)), //1 year from now
        	}));


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
			
			/*
			var globalsPanel = new Ext.Panel({
				title: 'Kml Renderer Options',
				autoLoad: {url: 'extjs_form.php',scripts:true, params: 'queryID=<?php echo $queryID; ?>'}
			});
			*/
			
			function  getGlobalsPanel(){
		
				var globalParams = {
					MIN_LINE_WIDTH: 	 { value: 3,	 type: 'number',	name: 'Min Edge Line Width'},
					MAX_LINE_WIDTH: 	 { value: 5,	 type: 'number',	name: 'Max Edge Line Width'},
					INITIAL_ALTITUDE:	 { value: 10,	 type: 'number',	name: 'Initial ASN Altitude'},
					ALTITUDE_DELTA:		 { value: 5000,  type: 'number',	name: 'ASN Altitude Delta'},
					STDEV_THRESHOLD: 	 { value: 2,	 type: 'number',	name: 'Standard Deviation Threshold'},
					DRAW_CIRCLES:		 { value: true,	 type: 'checkbox',	name: 'PoP Location Convergence Radiuses'},
					INTER_CON: 			 { value: true,	 type: 'checkbox',	name: 'Inter-Connectivity'},
					INTRA_CON: 			 { value: true,	 type: 'checkbox',	name: 'Intra-Connectivity'},
					CONNECTED_POPS_ONLY: { value: false, type: 'checkbox',	name: 'Connected PoPs only'},
					USE_COLOR_PICKER: 	 { value: false, type: 'checkbox',	name: 'Web-Safe Color-Picking'}
			   };
			
			
			    var items = [];
			    items.push({
			    	xtype: 'hidden',
			    	name: 'queryID',
			    	value: '<?php echo $queryID; ?>'
			    });
			    for (param in globalParams){
			    	if(globalParams[param]['type']=='checkbox') {
						items.push({
							xtype: 'checkbox',
				            boxLabel: globalParams[param]['name'],
				            checked: globalParams[param]['value'],
				            hideLabel: true,
				            name: param
						});
					}
					if(globalParams[param]['type']=='number') {
						items.push({
				            xtype: 'numberfield',
			                fieldLabel: globalParams[param]['name'],
			                value: globalParams[param]['value'],
			               	minValue: 0,
			                //maxValue: 100,
			                //allowDecimals: true,
			                //decimalPrecision: 1,
			                name: param
						});
					}
			    }
			    
	
		        // Create FormPanel with all layers
		        var globalsPanel = new Ext.FormPanel({
		            title: 'Kml Renderer Options',
			        //height: 340,
			        //width: 280,
			        border: false,
			        autoHeight: true,
			        autoWidth: true,
			        formId: 'globalsForm',
			        labelWidth: 120,
			        stateful: true,
			        url: 'render_kml.php',
			        defaults: {
			        	//stateful: true,
			        	//stateEvents: ['change'],
			        	//getState: function() {return this.getValue();},
    					//applyState: function(state) {this.setValue(state);}
			        },
			        items: items
		        });
		        
		        
		        var submit = globalsPanel.addButton({
		        	text: 'Submit',
	                handler: function() {
	                    var form = globalsPanel.getForm(); // get the basic form
	                    if (form.isValid()) { // make sure the form contains valid data before submitting
	                        form.submit({
	                        	waitMsg: 'rendering kml...',
	                        	waitTitle: 'kml-render-engine',
	                            success: function(form, action) {
	                               reloadKML();
	                            },
	                            failure: function(form, action) {
	                                Ext.Msg.alert('Failed', action.result.msg);
	                            }
	                        });
	                    } else { // display error alert if the data is invalid
	                        Ext.Msg.alert('Invalid Data', 'Please correct form errors.')
	                    }
	                }
		        });
		        
		        return globalsPanel;
		    }
		    
		    function reloadKML(){
		    	earthPanel.resetKml();
		    	earthPanel.fetchKml('<?php echo("$full_url");?>?'+Math.random()*10000000000);
		    }

            // Add panels to browser viewport
            var viewport = new Ext.Viewport({
                layout: 'border',
                defaults: {
				    collapsible: true,
				    split: true,
				    //bodyStyle: 'padding:15px'
				},
                items: [ controlPanel, earthPanel ]
            });

            // Build control panel
            earthPanel.on('earthLoaded', function(){

                // Display KMLs
                earthPanel.fetchKml('<?php echo("$full_url");?>');

                // Add panels
                controlPanel.add(earthPanel.getKmlPanel());
                controlPanel.add(getGlobalsPanel());
                //controlPanel.add(globalsPanel);
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
    <div id="globalsForm"></div>
</body>
</html>