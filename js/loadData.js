///////////-JQuery Plugins-////////////////////////////////////////////////
(function($) {
  var cache = [];
  // Arguments are image paths relative to the current page.
  $.preLoadImages = function() {
    var args_len = arguments.length;
    for (var i = args_len; i--;) {
      var cacheImage = document.createElement('img');
      cacheImage.src = arguments[i];
      cache.push(cacheImage);
    }
  }
})(jQuery)
///////////-Global-Data-////////////////////////////////////////////////
globalData = {};
globalData.blade = "";
///////////-JQuery-ajaxSetup-//////////////////////////////////////////
$().ready(function(){
	$.ajaxSetup({
		error:function(x,e){
			if(x.status==0){
			alert('You are offline!!\n Please Check Your Network.');
			}else if(x.status==404){
			alert('Requested URL not found.');
			}else if(x.status==500){
			alert('Internel Server Error.');
			}else if(e=='parsererror'){
			alert('Error.\nParsing JSON Request failed.');
			}else if(e=='timeout'){
			alert('Request Time out.');
			}else {
			alert('Unknow Error.\n'+x.responseText);
			}
		}
	});
});
/////////////////////////////////////////////////////////////////////
function testConnection(targetID) {
	$.preLoadImages("images/ajax-loader.gif");
	$(targetID).html('<p><img src="images/ajax-loader.gif"/></p>');
	$.post("query_backend.php", { func: "testConnection", blade: globalData.blade },
	function(data){
		 var result = data.result + "</BR>";
		 $(targetID).html(result);
	}, "json");	
}

function sql2html(sqlstr,targetID) {
  $.preLoadImages("images/ajax-bar.gif");
  $(targetID).html('<p><img src="images/ajax-bar.gif" width="220" height="19" /></p>');
  $.post("query_backend.php",{ func: "SQL2XML", blade: globalData.blade, sql: sqlstr}, 
	function(data,textStatus){
		if(textStatus == "error" || textStatus == "parseerror") {
			$(targetID).html('<p>There was an error making the AJAX request</p>');
			return;
		}
		if(data.result) {
			$(targetID).html("<P>"+data.result+"</P>");
			return;
		}
		var htmlstr = "<table border='1'>\n<tr>\n";
		$('ROW',data).each(function(i){
			if(i==0){ // add table column names
				var $childs = $(this).children(); 
				var length = $childs.length; 
				while(length--){
					htmlstr+="<td>"+$childs[length].tagName+"</td>";
				}
			}
			htmlstr+="</tr>\n<tr>"
			$(this).children().each(function() {
				htmlstr+="<td>"+$(this).text()+"</td>";
			});
			htmlstr+="</tr>"
		});
		htmlstr+="</table>";
		$(targetID).html(htmlstr);
	});
}