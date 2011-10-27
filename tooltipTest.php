

<!DOCTYPE html>

<html>

<!--
	This is a jQuery Tools standalone demo. Feel free to copy/paste.
	                                                         
	http://flowplayer.org/tools/demos/
	
	Do *not* reference CSS files and images from flowplayer.org when in production  

	Enjoy!
-->

<head>
	<title>jQuery Tools standalone demo</title>

	<!-- include the Tools -->
	<script src="http://cdn.jquerytools.org/1.2.6/full/jquery.tools.min.js"></script>
	 

	<!-- standalone page styling (can be removed) -->
	<link rel="stylesheet" type="text/css" href="http://static.flowplayer.org/tools/css/standalone.css"/>	




<!-- tooltip styling -->
<style>

/* tooltip styling. by default the element to be styled is .tooltip  */
.tooltip {
	display:none;
	background:transparent url(/images/tooltips/black_arrow.png);
	font-size:12px;
	height:70px;
	width:160px;
	padding:25px;
	color:#fff;	
}

/* style the trigger elements */
#demo img {
	border:0;
	cursor:pointer;
	margin:0 8px;
}
</style>
</head>

<body>



<!-- use gif image for IE -->
<!--[if lt IE 7]>
<style>
.tooltip {
	background-image:url(/tools/img/tooltip/black_arrow.gif);
}
</style>
<![endif]-->

<!-- a couple of trigger elements -->
<div id="demo">
	<img src="http://static.flowplayer.org/tools/img/photos/1.jpg"
		title="A must have tool for designing better layouts and more intuitive user-interfaces."/>

	<img src="http://static.flowplayer.org/tools/img/photos/2.jpg"
		title="Tooltips can be positioned anywhere relative to the trigger element."/>

	<img src="http://static.flowplayer.org/tools/img/photos/3.jpg"
		title="Tooltips can contain any HTML such as links, images, forms, tables, etc."/>

	<img src="http://static.flowplayer.org/tools/img/photos/4.jpg" style="margin-right:0px"
		title="There are many built-in show/hide effects and you can also make your own."/>
</div>


<script>



$("#demo img[title]").tooltip();
</script>



</body>

</html>
