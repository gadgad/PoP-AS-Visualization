<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Ext ColorPicker User Extension Demo</title>
    
    <!-- includes for extJS 2.2 -->
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/resources/css/ext-all.css">
    <script type="text/javascript" src="js/ext-2.2/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="js/ext-2.2/ext-all.js"></script>
	
    <script language="javascript" src="js/ColorPicker/ux/widgets/ColorPicker.js"></script>
    <script language="javascript" src="js/ColorPicker/ux/widgets/form/ColorPickerField.js"></script>
    <link rel="stylesheet" type="text/css" href="js/ColorPicker/resources/color-picker.ux.css" />
    
	<link rel="stylesheet" type="text/css" href="js/ext-2.2/examples/grid/grid-examples.css" />
    <script type="text/javascript">
    /*
    Ext.onReady(function(){

	
	    Ext.QuickTips.init();
	    
	    var cp = new Ext.ux.ColorPicker();  // initial selected color
		cp.render("standalone");
	    
	    cp.on('select', function(picker, color){
	        Ext.example.msg('Color Selected', 'You chosed {0}.', color);
	    });    
	    
	    
	    var panel = new Ext.Panel({
	        items: [cp]
	    });
	    panel.render("standalone");
	
	    var colorMenu = new Ext.ux.ColorPickerMenu({
	        hideOnClick: false
	    });
	    colorMenu.on('select', function(cm, color) {
	        Ext.example.msg('Color Selected', 'You chose {0}.', color);
	    });
	    
	    
	    var button = new Ext.Button({
	        text: 'clik me',
	        menu: colorMenu
	    });
	    button.render("colormenu");
	
		
	    
	    var cpf = new Ext.ux.ColorPickerField({
	        fieldLabel: 'Choose a color',
	        value: '#0A9F50'
	    });
	    cpf.on('valid', function(field) {
	        //Ext.example.msg('Color Selected', 'You chose {0}.', field.getValue());
	    });
	
	    var form = new Ext.FormPanel({
	        border: false,
	        width: 300,
	        bodyStyle: {'background': 'transparent'},
	        items: [{
	            xtype: 'fieldset',
	            autoHeight: true,
	            border: false,
	            items: [cpf]
	        }]
	    });
	
	    form.render("colorfield");
	    
	   
	
	});
	
	
	Ext.example = function(){
	    var msgCt;
	
	    function createBox(t, s){
	        return ['<div class="msg">',
	                '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
	                '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
	                '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
	                '</div>'].join('');
	    }
	    return {
	        msg : function(title, format){
	            if(!msgCt){
	                msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
	            }
	            msgCt.alignTo(document, 't-t');
	            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
	            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
	            m.slideIn('t').pause(1).ghost("t", {remove:true});
	        }
	    }   
	}();
	*/
	
	Ext.onReady(function(){

    var myData = [
        ['174','#0A9F50'],
        ['209','#E9111F'],
    ];

	/*
    // example of custom renderer function
    function change(val){
        if(val > 0){
            return '<span style="color:green;">' + val + '</span>';
        }else if(val < 0){
            return '<span style="color:red;">' + val + '</span>';
        }
        return val;
    }


    // example of custom renderer function
    function pctChange(val){
        if(val > 0){
            return '<span style="color:green;">' + val + '%</span>';
        }else if(val < 0){
            return '<span style="color:red;">' + val + '%</span>';
        }
        return val;
    }
    */
   
   function simpleColorRenderer(value){
   	return '<div style="background-color: ' + value + ';">' + value + '</div>';
   }
   
    function renderColorPicker(value, metadata, record, rowIndex, colIndex, store){
    	var id = Ext.id();
    	createWidget.defer(1, this, [value, id, record]);
    	return '<div id="' + id + '"></div>';
    }
    
    
    function createWidget(val, id, r) {
    	/*
        new Ext.Button({
            text: value
            ,iconCls: IconManager.getIcon('package_add')
            ,handler : function(btn, e) {
                // do whatever you want here
            }
        }).render(document.body, id);
        */
       
        var cpf = new Ext.ux.ColorPickerField({
	        //fieldLabel: '',
	        value: val
	    });
	    cpf.on('valid', function(field) {
	        //Ext.example.msg('Color Selected', 'You chose {0}.', field.getValue());
	        r.set('color',field.getValue());
	    });
	    
	    cpf.render(document.body, id);
    }

    // create the data store
    var store = new Ext.data.SimpleStore({
        fields: [
           {name: 'asn', type: 'int'},
           {name: 'color' },
        ]
    });
    store.loadData(myData);
    
    /*
    store.on('update', function(str,rec,op) {
    	//alert(rec.get('color')+' '+op);
    	Ext.example.msg('Color Selected', 'You chose {0} operation: {1}.', rec.get('color'), op);
    });
    */

    // create the Grid
    var grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {id:'asn',header: "ASN", width: 50, align: 'center', sortable: true, dataIndex: 'asn'},
            {id:'color', header: "Color", width: 75, align: 'center', sortable: true, renderer: renderColorPicker, dataIndex: 'color'},
        ],
        stripeRows: true,
         viewConfig: {
         	autoFill:true,
            forceFit:true
        },
        autoExpandColumn:'color',
        autoHeight:true,
        width: 350,
        //autoExpandMin: 100,
        //border: false,
        // TODO: add buttons array - button with submit function
        bbar: [{
        	text: 'Submit',
        	handler: function() {
        		 //grid.getSelectionModel().selectAll();
    			 //var sm = grid.getSelectionModel().getSelections();
    			 var sm = store.getModifiedRecords();
    			 var temp = '';
    			 for (i=0; i<=sm.length-1; i++) {
    			 	var div = (i==0)? '' : '|';
        			temp = temp + div + sm[i].get('asn') + ':' + sm[i].get('color');
    			 }
    			 alert(temp);
    			 Ext.Ajax.request({
        		 	url: 'process_data.php',
        			method: 'POST',
        			params: 'color_string=' + temp,
        			success: function(obj) {
            			var resp = obj.responseText;
            			if (resp != 0) {
                			Ext.MessageBox.alert('Success', resp + ' Processed');
            			} else {
                			Ext.MessageBox.alert('Failed', 'No Processed');
            			}
        			}
    			});
        	}
        }]
    });

    grid.render('grid-example');
});
</script>
</head>
<body>
<!--<div id="colorfield"></div>-->
<div id="grid-example"></div>
</body>
</html>
