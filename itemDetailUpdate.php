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


	$sql = "INSERT INTO `npi`.`logs`
			(SKU, MainID, LevelID, StatusID, IsFile, FileName, FileLocation, Comments, DateTimeCreated, UserID)
			VALUES
			('". $r[0]->ModelName . "', 0, 0, 8, 0, NULL, NULL, NULL, NOW(), ". $_SESSION['SESS_MEMBER_ID'] .");";

	#echo $sql;

	if($dbo->run_query($sql)){
		// send notification
		$notification = true;

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
					s.Description as 'Status'
					/*
					IFNULL((SELECT s.Description FROM `npi`.`logs` l JOIN `npi`.`Status` s on l.StatusID = s.StatusID 
					 WHERE MainID = $ReLvlID ORDER BY LogID desc limit 1), 'Not Started') as 'PreviousStatus'
					 */
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
								Item details has been updated.
								<br/>
								Please log in to view the changes. <br/>
							</p>
						</body>
						</html>";

			$email->send($to, $subject, $content);

		}
	}
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