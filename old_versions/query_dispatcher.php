<?php
	require_once("bin/load_config.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>query dispatcher</title>
		<meta name="author" content="Gadi">
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/loadData.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			globalData.blade = $("#mySelect").val();
			testConnection("#myContainer");
			//sql2html("show processlist","#tableArea");
			$("#mySelect").change(function() {
				globalData.blade = $("#mySelect").val();
				testConnection("#myContainer");
				//sql2html("#tableArea","show processlist");
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
						if($name!="" && $Blade_Map[$name]["db"]=="DIMES_DISTANCES")
							echo "<option>$name</option>";
					}
				?>
			</select>
			<div id="myContainer"></div></br>
			<div id="tableArea"></div></br>
		</form>
	</body>
</html>
