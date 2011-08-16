<?php
    if (!isset($_POST['queryID'])) {
    	echo "need queryID!";
    	die();
    }
?>

<!DOCTYPE html> 
<html> 
  <head> 
    <title>Ext JS Form Panel - Simple Form</title> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    
 	<!--
    <link type="text/css" rel="stylesheet" href="http://extjs.cachefly.net/ext-4.0.2/resources/css/ext-all.css"/> 
    <script type="text/javascript" src="http://extjs.cachefly.net/ext-4.0.2/ext-all.js"></script> 
    -->
    <!-- includes for extJS 2.2 -->
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/resources/css/ext-all.css">
    <script type="text/javascript" src="js/ext-2.2/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="js/ext-2.2/ext-all.js"></script>
    <script type="text/javascript">
    
    	//Ext.require('Ext.form.Panel');
		//Ext.require('Ext.form.field.Checkbox');
		
		   	
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
	   	}
	
	
	    // Create checkbox for each layer
	    var items = [];
	    items.push({
	    	xtype: 'hiddenfield',
	    	name: 'queryID',
	    	value: '<?php echo $_POST['queryID']; ?>'
	    });
	    for (param in globalParams){
	    	if(globalParams[param]['type']=='checkbox') {
				items.push({
					xtype: 'checkboxfield',
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
	                //hideTrigger: true,
	                //allowDecimals: true,
	                //decimalPrecision: 1,
	                //step: 0.4
	                name: param
				});
			}
	    }

		
		Ext.onReady(function() {
		    Ext.create('Ext.form.Panel', {
		        renderTo: Ext.getBody(),
		        //title: 'Kml Renderer Options',
		        //height: 340,
		        width: 280,
		        border: false,
		        bodyPadding: 10,
		        //defaultType: 'checkbox',
		        url: 'render_kml.php',
		        defaults: {
				},
		        items: items,
		        buttons: [
	            {
	                text: 'Submit',
	                handler: function() {
	                    var form = this.up('form').getForm(); // get the basic form
	                    if (form.isValid()) { // make sure the form contains valid data before submitting
	                        form.submit({
	                            success: function(form, action) {
	                               Ext.Msg.alert('Success', action.result.msg);
	                            },
	                            failure: function(form, action) {
	                                Ext.Msg.alert('Failed', action.result.msg);
	                            }
	                        });
	                    } else { // display error alert if the data is invalid
	                        Ext.Msg.alert('Invalid Data', 'Please correct form errors.')
	                    }
	                }
	            }
	        ]
		    });
		});
    </script> 
 
  </head> 
  <body> 
  </body> 
</html>