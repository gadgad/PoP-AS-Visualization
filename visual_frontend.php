<?php
require_once("verify.php");	
require_once("bin/idgen.php");
require_once("bin/kml_render_globals.php");

$queryID = isset($_REQUEST["QID"])? $_REQUEST["QID"] : '2df5efc4b99b9486e245a49f6400a90f';
$idg = new idGen($queryID);
$filename='queries/'.$idg->getDirName().'/result.kmz';
$full_url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI'])."/".$filename."?".rand(0,10000000000);

$key = (stristr(PHP_OS, 'WIN'))? "ABQIAAAAMYziiEA_p76rk0jQj-KuSxT2yXp_ZAY8_ufC3CFXhHIE1NvwkxRpJH3_NoHEcRApDRZWpWCuTc7H3A": 
								 "ABQIAAAAMYziiEA_p76rk0jQj-KuSxT8m87T_heG5MPAsfTIBlWdaTx20xTeGeqv3LruUshUL4J6wufrjMVp7Q";
?>
<html>
<head>
    <title>DIMES PoP/AS Visual FrontEnd</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<!-- includes for extJS 2.2 -->
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/resources/css/ext-all.css">
    <script type="text/javascript" src="js/ext-2.2/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="js/ext-2.2/ext-all.js"></script>
    
    <!-- includes for jQuery -->
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script type="text/javascript" src="js/loadData.js"></script>
    
    <!--<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-2.2/resources/css/ext-all.css" />-->
    <!--<script type="text/javascript" src="http://extjs.cachefly.net/builds/ext-cdn-611.js"></script>-->
    <link rel="stylesheet" type="text/css" href="css/Ext.ux.GEarthPanel-1.1.css" />
    <script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo $key ?>"></script>
  	<script type="text/javascript" src="js/Ext.ux.GEarthPanel-1.1.js"></script>
    <script type="text/javascript">
    	
    	var ge;
        google.load("earth", "1");
        google.load("maps", "2.xx");
        
        Ext.onReady(function(){
        	
        	/*
        	Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
        		expires: new Date(new Date().getTime()+(1000*60*60*24*365)), //1 year from now
        	}));
        	*/


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
            
	    	 var userPanel = new Ext.Panel({
	            title: 'User Admin',
		        border: false,
		        autoHeight: true,
		        autoWidth: true,
		        buttonAlign: 'center',
		        html: 'currently connected as: <?php echo $username; ?>',
		        buttons: [{
		        	text: 'Logout',
                	handler: function() {
                   		location.href='logout.php'; 
                	}
		        }]
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
					MIN_LINE_WIDTH: 	 { value: <?php echo MIN_LINE_WIDTH; ?>, 	type: 'number',		name: 'Min Edge Line Width'},
					MAX_LINE_WIDTH: 	 { value: <?php echo MAX_LINE_WIDTH; ?>,	type: 'number',		name: 'Max Edge Line Width'},
					INITIAL_ALTITUDE:	 { value: <?php echo INITIAL_ALTITUDE; ?>,	type: 'number',		name: 'Initial ASN Altitude'},
					ALTITUDE_DELTA:		 { value: <?php echo ALTITUDE_DELTA; ?>,	type: 'number',		name: 'ASN Altitude Delta'},
					STDEV_THRESHOLD: 	 { value: <?php echo STDEV_THRESHOLD; ?>,	type: 'number',		name: 'Standard Deviation Threshold'},
					DRAW_CIRCLES:		 { value: <?php echo DRAW_CIRCLES; ?>,	type: 'checkbox',	name: 'PoP Location Convergence Radiuses'},
					INTER_CON: 			 { value: <?php echo INTER_CON; ?>,	type: 'checkbox',	name: 'Inter-Connectivity'},
					INTRA_CON: 			 { value: <?php echo INTRA_CON; ?>,	type: 'checkbox',	name: 'Intra-Connectivity'},
					CONNECTED_POPS_ONLY: { value: <?php echo CONNECTED_POPS_ONLY; ?>,	type: 'checkbox',	name: 'Connected PoPs only'},
					USE_COLOR_PICKER: 	 { value: <?php echo USE_COLOR_PICKER; ?>,	type: 'checkbox',	name: 'Web-Safe Color-Picking'}
			   };
			
			
			    var items = [];
			    items.push({
			    	xtype: 'hidden',
			    	name: 'queryID',
			    	value: '<?php echo $queryID; ?>'
			    });
			    items.push({
			    	xtype: 'hidden',
			    	name: 'submitted',
			    	value: 'yes'
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
			        border: false,
			        autoHeight: true,
			        autoWidth: true,
			        formId: 'globalsForm',
			        labelWidth: 120,
			        //stateful: true,
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
                controlPanel.add(userPanel);
                controlPanel.add(earthPanel.getLocationPanel());
                controlPanel.add(earthPanel.getLayersPanel());
                controlPanel.add(earthPanel.getOptionsPanel());
                controlPanel.items.items[0].add(downloadPanel);
                
                controlPanel.doLayout();
                
                ge = earthPanel.earth;
				google.earth.addEventListener(ge.getView(), 'viewchangeend', function() {
					ge.getFeatures().removeChild(earthPanel.networkLink);
				});
                
	            //set a click listener that affects all placemarks
				google.earth.addEventListener(
				    ge.getGlobe(), 'click', function(event) {
				    var obj = event.getTarget();
				    if (obj.getType() == 'KmlPlacemark'){
				      event.preventDefault();
				      var placemark = obj;
				      var placemark_name = placemark.getName();
				      //get the full balloon html
				      var placemark_desc_active = placemark.getBalloonHtmlUnsafe();
				      //same as above, except with 'active' content like JS stripped out
				      //var placemark_desc = placemark.getBalloonHtml();
				      //create new balloon with rendered content
				      var balloon = ge.createHtmlStringBalloon('');
				      balloon.setFeature(placemark);
				      //balloon.setMaxWidth(300);
				      //balloon.setContentString('<h3>' + placemark_name + '</h3>' + placemark_desc_active);
				      balloon.setContentString(placemark_desc_active);
				      if(placemark_name.toLowerCase().indexOf('edge')!=-1){
				      	balloon.setMinWidth(800);
				      	balloon.setMinHeight(400);
				      }
				      ge.setBalloon(balloon);
				    }
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