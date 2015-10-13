<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/email.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$file = $_FILES['file'];

$d = array();

parse_str($_POST['d'], $d);

// upload file
if(!empty($file['name'])){
	$sourcePath = $file['tmp_name']; // Storing source path of the file in a variable
	$targetPath = "files/".$file['name']; // Target path where file is to be stored

	if(move_uploaded_file($sourcePath,$targetPath));
}

/*
print "<pre>";
print_r($d);
print "</pre>";
*/

$dim = $d['width'] . "," . $d['length'] . "," . $d['height'];
$upc = (!empty($d['upc'])) ? "'".$d['upc']."'" : "NULL";
$reman_upc = (!empty($d['reman_upc'])) ? "'".$d['reman_upc']."'" : "NULL";
$sfid = (!empty($d['sfid'])) ? "'".$d['sfid']."'" : "NULL";
$sfid_reman = (!empty($d['reman_sfid'])) ? "'".$d['reman_sfid']."'" : "NULL";
$sfid_rmb = (!empty($d['rmb_sfid'])) ? "'".$d['rmb_sfid']."'" : "NULL";

$sql = "UPDATE `npi`.`items`
		SET `Description` = '".addslashes(trim($d['desc']))."',
		`reman_status` = ".$d['reman_status'].",
		`UPC` = ". $upc . ",
		`reman_upc` = ". $reman_upc .",
		`prod_cat` = ".$d['category'].",
		`prod_sub_cat` = ".$d['subCat'].",
		`pack_dim` = '". $dim ."',
		`pack_weight` = ".$d['weight'].",
		`SFID` = ". $sfid .",
		`SFID_REMAN` = " . $sfid_reman . ",
		`SFID_RMB` = " . $sfid_rmb . "
		WHERE ModelNumber = '" . $d['sku'] . "';";


#print $sql;

if($dbo->run_query($sql)){
	$d = array(	"status"	=> true,
				"msg"		=> "Update successfully");
}
else{
	$d = array( "status"	=> false,
				"msg"		=> "Error! Update failed.",
				"sql"		=> $sql);
}

print json_encode($d);

?>