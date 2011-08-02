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

function example_ajax_request() {
  $.preLoadImages("images/ajax-bar.gif");
  $('#example-placeholder').html('<p><img src="images/ajax-bar.gif" width="220" height="19" /></p>');
  /*
  $('#example-placeholder').load("get_data.html", "",
        function(responseText, textStatus, XMLHttpRequest) {
            if(textStatus == 'error') {
                $('#example-placeholder').html('<p>There was an error making the AJAX request</p>');
            }
        }
    );
	*/
	$.get("sql2xml.php", function(toc){
	var data = "<table border='1'>\n<tr>\n";
	$('ROW',toc).each(function(i){
		if(i==0){ // add table column names
			var $childs = $(this).children(); 
			var length = $childs.length; 
			while(length--){
				data+="<td>"+$childs[length].tagName+"</td>";
			}
		}
		data+="</tr>\n<tr>"
		$(this).children().each(function() {
			data+="<td>"+$(this).text()+"</td>";
		});
		data+="</tr>"
	});
	data+="</table>";
	$("#example-placeholder").html(data);
	});
}