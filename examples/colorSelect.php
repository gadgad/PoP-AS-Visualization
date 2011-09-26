<html>
<head>
    <title>DIMES PoP/AS Visual FrontEnd</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<!--
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/resources/css/ext-all.css">
    <script type="text/javascript" src="js/ext-2.2/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="js/ext-2.2/ext-all.js"></script>
   -->
    

    

  <script src="http://extjs.cachefly.net/ext-3.0.0/adapter/ext/ext-base.js" type="text/javascript"></script>
  <script src="http://extjs.cachefly.net/ext-3.0.0/ext-all-debug.js" type="text/javascript"></script>
  <link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.0.0/resources/css/ext-all.css"></link>

    
    <link rel="stylesheet" type="text/css" href="css/Ext.ux.ColorField.css"></link>
    <script type="text/javascript" src="js/Ext.ux.ColorField.js"></script>
    <script type="text/javascript">
    	Ext.onReady(function(){
    
	    var fg = new Ext.ux.ColorField({fieldLabel: 'Foreground Color', value: '#000000', msgTarget: 'qtip'});
	    var bg = new Ext.ux.ColorField({fieldLabel: 'Background Color', value: '#FFFFFF', msgTarget: 'qtip'});
	    fg.on('select', function(field, color){
	        Ext.select('body').setStyle('color', color);
	    });
	    bg.on('select', function(field, color){
	        Ext.select('body').setStyle('background', color);
	    });
	    var form = new Ext.FormPanel({
	        border: false,
	        width: 300,
	        bodyStyle: {'background': 'transparent'},
	        items: [{
	            xtype: 'fieldset',
	            autoHeight: true,
	            border: false,
	            items: [fg, bg]
	        }]
	    });
	    form.render('form-example');
	    
	    var fb = new Ext.ux.ColorField({fieldLabel: 'Fallback Picker', value: '#FFFFFF', msgTarget: 'qtip', fallback: true});
	    var form = new Ext.FormPanel({
	        border: false,
	        width: 300,
	        bodyStyle: {'background': 'transparent'},
	        items: [{
	            xtype: 'fieldset',
	            autoHeight: true,
	            border: false,
	            items: [fb]
	        }]
	    });  
	    form.render('fb-example');
	    
	    Ext.QuickTips.init();
    
});
</script>
<script type="text/javascript">
        Ext.onReady(function(){
		    var genericHandler = function(menuItem) {
		        Ext.MessageBox.alert('', 'Your choice is ' + menuItem.text);
		    }
		    var colorAndDateHandler = function(menuItem, choice) {
		        Ext.MessageBox.alert('', 'Your choice is ' + choice);
		    }
		
		    var colorMenu = {
		            text    : 'Choose Color',
		            menu    : {
		                xtype : 'colormenu',
		                handler : colorAndDateHandler
		            }
		    };
		    
		    var menu = new Ext.menu.Menu({
		        id        : 'myMenu',
		        items     : colorMenu,
		        listeners : {
		            'beforehide' : function() {
		                return false;
		            }
		        }
		    
		    });
		    menu.showAt([300,300]);
		});
    </script>

</head>
<body>
	<div id="my-div"></div>
	<h3>Form Field Example</h3>
  	<div id='form-example'></div>
  	<h3>Fallback Example</h3>
  	<div id='fb-example'></div>
</body>
</html>