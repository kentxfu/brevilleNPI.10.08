<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/prc/receiving/function.php";
include_once "$_SERVER[DOCUMENT_ROOT]/prc/inspection/function.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1"/>
<title>PRC Inventories, Inc - QA</title>
<?php
include_once "$_SERVER[DOCUMENT_ROOT]/prc/master.php";
print "<link rel='stylesheet' type='text/css' href='$_SERVER[SERVER_ROOT]/prc/css/bootstrap-combined.min.css' />";
print "<script type = 'text/javascript' src='$_SERVER[SERVER_ROOT]/prc/js/bootstrap.min.js'></script>";

print "<link rel='stylesheet' href='$_SERVER[SERVER_ROOT]/prc/css/jquery-ui.css' />";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery-ui.js'></script>";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery.validate.min.js'></script>";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery.validate.js'></script>";
?>
<script>
$(document).ready(function(){
	
});
</script>
</head>
<body>

</body>
</html>