<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$filter = "";
switch($current_level){
	case "Art Work":
		$filter = "WHERE StatusID NOT IN (6,8)";
		break;
	case "Work Instruction":
	case "ETL Certification":
		$filter = "WHERE StatusID NOT IN (8)";
		break;
	case "Packaging":
		$filter = "WHERE StatusID NOT IN (4)";
		break;
}

$sql = "SELECT
		*
		FROM `npi`.`status`
		$filter
		ORDER BY Seq
		;";

$r = $dbo->run_query($sql);

if(count($r) > 0){
	?>
	<select name="status" id="status" class="required" style="height: 32px; border-radius:5px;">
	<?php
	for($i = 0; $i < count($r); $i++){
		?>
		<option class="<?php echo $r[$i]->CSS_Class; ?>" value="<?php echo $r[$i]->StatusID; ?>" <?php echo ($current_status == $r[$i]->StatusID) ? " selected " : ""; ?>><?php echo $r[$i]->Description; ?></option>
		<?php
	}
	?>
	</select>
	<?php
}

?>
	