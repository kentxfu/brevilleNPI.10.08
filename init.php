<?php
require_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/noErrors.php";
include_once "$_SERVER[DOCUMENT_ROOT]/prc/receiving/function.php";
?>
<!doctype html>
<head>
<title>PRC Inventories, Inc - Inventory</title>
<?php 
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/bootstrap.min.js'></script>";
?>
<script>
$(function(){

	$('#dvLoading').modal({
	  backdrop: 'static',
	  keyboard: false
	});
	
	$("#dvLoading").modal("show");

	$.ajax({
		url: "brevilleNPI.php",
		type: "POST",
		success: function(result)
		{
			$("#dvLoading").modal("hide");
			
			$("#dataTableDiv").html(result);
		}
	});
});
</script>
</head>
<body>

<div id="dataTableDiv"></div>

<div id='dvLoading' class='modal fade' style='width:50%; left: 25%; margin-left: auto; margin-right: auto;'>
	<div class='modal-dialog'>
		<div class='modal-content'>
			<div class='modal-body' id='message-content' style='text-align: center;'><h3>Loading... Please wait.</h3><img src="img/page-loader.gif" style="width: 50px;" /></div>
		</div>
	</div>
</div>

</body>
</html>