<html>
<head>
<title>Error</title>
<style type="text/css">

body {
background-color:	#fff;
margin:				40px;
font-family:		Lucida Grande, Verdana, Sans-serif;
font-size:			12px;
color:				#000;
}

#content  {
border:				#999 1px solid;
background-color:	#fff;
padding:			20px 20px 12px 20px;
}

h1 {
font-weight:		normal;
font-size:			14px;
color:				#990000;
margin:				0 0 4px 0;
}
</style>
</head>
<body>
	<div id="content">
		<h1>Se produjo un error</h1>
		<?php echo $message; ?>
		<br />
		<pre><?php //print_r(array_slice(debug_backtrace(),0, 4)); ?></pre>
	</div>
</body>
</html>