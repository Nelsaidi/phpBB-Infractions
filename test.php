<?php

/**
 * A Huge testing file, not splitted up or unit tested or my own fancy script to save time and make it work more.
 * *COULD* split it into controllers, but to hassle free, wont be requiring individual tests on a complicated project, it should meet all
 */
 
// Init DB
$dbUser = 
$dbPass =
$dbHost =
$dbName = 

$db = new PDO


// Display/Formatting Functions
// All of these will output

// Colors
$bgColor = 
$textColor = 
$passColor = 
$failColor = 
$anomolyColor = 
$noteColor = 

function seperatedEcho($msg)
{
	$header = '====='
	echo sprintf($header . ' %s ' . $header, $msg);
	echo "<br/>";
}

function compareResult($expected, $actual)
{
	global $passColor, $failColor
	
	// Wont typecast, so equal not identical
	if($expected == $actual)
	{
		colorEcho($passColor, 'PASS');
		return 1; // $errorCount + result
	}
	
	colorEcho($failColor, 'FAIL');
	return 0;

}

function colorEcho($color, $message)
{
	echo '<span style="color: ' . $color . '">' . $message . '</span>';
}

function br()
{
	echo '<br/>';
}


// Start Page
?>
<!doctype html>
<html>
<head>
<style type="text/css">
body
{
	font: 'Courier new';
	background: <?=$bgColor?>;
	color: <?=$textColor?>;
}
</style>
</head>
<body>
<div>
<?php

// Start Testing
seperatedEcho('Basic Compare');
$data = array();
$infractions->insert(...);



?>
</div>
</body>
</html>
	
	








