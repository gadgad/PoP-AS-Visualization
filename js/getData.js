$(document).ready(function() { //when doc is ready
	//$("#getData").click(function(){ //and someone clicked the "getData" button
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
				/*
				var firstname = $(this).find("firstname").text();
				var surname = $(this).find("surname").text();
				*/
			});
			data+="</table>";
			$("#container").html(data);
		});
	//});
});


