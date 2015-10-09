<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$file = $_FILES['file'];

$params = array();

parse_str($_POST['data'], $params);

$isFileStatus = (!empty($file['name'])) ? true : false;
$isFile = ($isFileStatus) ? 1 : 0;
$FileName = ($isFileStatus) ? "'".$file['name']."'" : "NULL";
$FileLocation = ($isFileStatus) ? "'".$file['name']."'" : "NULL";
$ReLvlID = $params['ReLvlID'];
$status = $params['status'];
$comment = (!empty($params['comment'])) ? "'" . addslashes($params['comment']) . "'": "NULL";

$sql = "SELECT 
		m.MainID,
		m.ModelName,
		m.RelationID,
		l.LevelID
		#,l2.*
		FROM `npi`.`main` m
		JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
		JOIN `npi`.`levels` l on lr.ParentLevelID = l.LevelID
		#JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.ID
		WHERE m.MainID = $ReLvlID;";

$r = $dbo->run_query($sql);

// uplaod file
if($isFileStatus){
	$sourcePath = $file['tmp_name']; // Storing source path of the file in a variable
	$targetPath = "files/".$file['name']; // Target path where file is to be stored

	if(move_uploaded_file($sourcePath,$targetPath));
}

$sql = "INSERT INTO `npi`.`logs`
		(SKU, LevelID, StatusID, IsFile, FileName, FileLocation, Comments, DateTimeCreated, UserID)
		VALUES
		('". $r[0]->ModelName . "', ". $r[0]->RelationID .", ". $status . ", $isFile, $FileName, $FileLocation, ". $comment .", NOW(), ". $_SESSION['SESS_MEMBER_ID'] .");";

#echo $sql;

if($dbo->run_query($sql)){
	// update datetime and user id on main level
	$sql = "UPDATE 
			`npi`.`main`
			SET StatusID = $status,
			Comments = ". $comment . ",
			DateTimeUpdated = NOW(),
			UserID = ". $_SESSION['SESS_MEMBER_ID'] ."
			WHERE MainID = $ReLvlID;";

	if($dbo->run_query($sql)){
		$d = array(	"status"	=> true,
					"msg"		=> "Update successfully");
	}
	else{
		$d = array( "status"	=> false,
					"msg"		=> "Error! Update failed.");
	}
}
else{
	$d = array( "status"	=> false,
				"msg"		=> "Error! Update failed.");			
}

print json_encode($d);

?>