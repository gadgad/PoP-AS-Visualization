<?php
	include("bin/load_config.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>query dispatcher</title>
		<meta name="author" content="Gadi">
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/loadData.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			$("#testConnection").click(function() {
					$.preLoadImages("images/ajax-loader.gif");
  					$('#myContainer').html('<p><img src="images/ajax-loader.gif"/></p>');
					$.post("query_backend.php", { func: "testConnection", blade: $("#mySelect").val() },
					function(data){
						 var result = data.result + "</BR>";
						 $("#myContainer").html(result);
					}, "json");	
			});
		});
		</script>
		<style type="text/css"></style>
	</head>
	<body>
		<form>
			<select id="mySelect">
				<?php
					foreach($Blades as $blade)
					{
						$name = $blade["@attributes"]["name"];
						if($name!="")
							echo "<option>$name</option>";
					}
				?>
			</select>
			<input type="button" id="testConnection" value="test connection" />
		</form>
		<div id="myContainer"></div>
	</body>
</html>
