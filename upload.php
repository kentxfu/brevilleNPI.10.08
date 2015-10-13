<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/email.class.php";

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
$UseLastFile = $params['UseLastFile'];

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

// use the last uploaded file
if($UseLastFile == "on"){
	$sql = "SELECT l.*
			FROM `npi`.`logs` l
			JOIN `npi`.`main` m on l.SKU = m.ModelName and l.LevelID = m.RelationID and l.StatusID = m.StatusID
			WHERE m.mainID = $ReLvlID
			ORDER BY LogID DESC LIMIT 1;";
	$r_last = $dbo->run_query($sql);

	$isFile = 1;
	$FileName = "'".$r_last[0]->FileName."'";
	$FileLocation = "'".$r_last[0]->FileLocation."'";
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

		// send notification
		$notification = false;

		if($notification){

			$notification_comment = (!empty($params['comment'])) ? $params['comment'] : "None";

			$sql = "SELECT m.MainID, m.ModelName, 
					CASE lp.LevelDescription 
						WHEN 'Root' THEN lc.DisplayName
					    ELSE lp.LevelDescription 
					END AS 'ParentLevel',
					CASE lp.LevelDescription
						WHEN 'ROOT' THEN ''
					    ELSE lc.DisplayName
					END AS 'ChildLevel',
					s.Description as 'Status',
						(SELECT s.Description 
						 FROM `npi`.`logs` l 
						 JOIN `npi`.`Status` s on l.StatusID = s.StatusID
						 WHERE SKU = m.ModelName and l.LevelID = $ReLvlID
					     ORDER BY LogID desc limit 1) as 'PreviousStatus'
					FROM `npi`.`main` m JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID 
					JOIN `npi`.`levels` lp on lr.ParentLevelID = lp.LevelID
					JOIN `npi`.`levels` lc on lr.ChildLevelID = lc.LevelID
					JOIN `npi`.`status` s on m.StatusID = s.StatusID
					WHERE m.MainID = $ReLvlID;";

			$r = $dbo->run_query($sql);

			$email = new email();

			$to = $email->getEmailList("NPI");

			$initialStatus = (!empty($r[0]->PreviousStatus)) ? $r[0]->PreviousStatus : "Not Started";

			$subject = "Item master has been modified for ". $r[0]->ModelName;
			$content = "
						<html>
						<head>
						<style>
						body {
							font-family: arial, san-serif;
						}
						</style>
						</head>
						<body>
							<h3 style='color: red;'>Please do not reply. This is automatically generated. </h3>
							<p>	SKU # ". $r[0]->ModelName ." have been modified by ". $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME'] ." on ".date("m/d/Y H:m:s").". <hr/><br/>
								".
								$r[0]->ParentLevel . " " . $r[0]->ChildLevel ." has been updated from \"" . $initialStatus . "\" to \"" . $r[0]->Status . "\".
								<br/>
								Comments: ".$notification_comment."
								<br/>
								<br/>
								Please log in to view the changes. <br/>
							</p>
						</body>
						</html>";

			$email->send($to, $subject, $content);

		}

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