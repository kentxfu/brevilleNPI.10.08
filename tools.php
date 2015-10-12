<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$sql = "select * from npi.items
		where modelnumber not in ('BBL560XL');
		#where modelnumber = '800CPXL';";

$r = $dbo->run_query($sql);

$sql = "select * from
		`npi`.level_relations
		WHERE LRID = 28";

$lr = $dbo->run_query($sql);

for($i = 0; $i < count($r); $i++){
	for($j = 0; $j < count($lr); $j++){
		
		$sql = "INSERT INTO `npi`.`main`
				(ModelName, RelationID, StatusID)
				VALUES
				('". $r[$i]->ModelNumber."', ".$lr[$j]->LRID.", 1);";

		echo "#".($i+1).": ".$sql . "........";

		
		if(!$dbo->run_query($sql)){
			echo "<span style='color:red;'>Error!!!</span>";
		}
		else{
			echo "<span style='color:green;'>Done</span>";
		}
		

		echo "<br/>";
	}
}

?>