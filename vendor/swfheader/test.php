<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>SWF header info</title>
</head>
<body>
<? require ("swfheader.class.php") ;?>
<?
// Create a new SWF header object with debug info, open with
// disabled debug (false) for silent processing
$swf = new swfheader(true) ;
// Open the swf file...
// Replace filename accordingly to your test environment...
$swf->loadswf("test.swf") ;
// Show data as a block... you can also access data within the object
$swf->show() ;
?>
</body>
</html>
