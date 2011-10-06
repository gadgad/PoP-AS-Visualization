
function sql2html(targetID,sqlstr) {
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
