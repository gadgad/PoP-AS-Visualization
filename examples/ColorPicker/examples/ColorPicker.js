Ext.onReady(function(){

    Ext.QuickTips.init();
    
    var cp = new Ext.ux.ColorPicker();  // initial selected color
//    cp.render("standalone");
    
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
        Ext.example.msg('Color Selected', 'You chose {0}.', field.getValue());
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
