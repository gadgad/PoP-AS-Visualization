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
globalData = {blade: '', pq_running: false}; 
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

$(document).ready(function() {
		processQueries();
});
/////////////////////////////////////////////////////////////////////
function run_pq_script() {
	$.post("query_backend.php", { func: "processQueries" },
	function(data,textStatus){
		if(data!=null) {
			if(data.type == "ERROR"){
				//alert(data.result);
				return;
			}
			globalData.pq_running = true;
			$("#My_queries").trigger('update', data);
		}
	}, "json");	
}

function processQueries() {
	$.post("query_backend.php", { func: "pq-check" },
	function(data,textStatus){
		if(data!=null && data.type == "NOT-EMPTY") {
			run_pq_script();
			return;
		}
	}, "json");	
}