<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$sql = "SELECT
		*
		FROM `npi`.`status`
		ORDER BY Seq
		;";

$r = $dbo->run_query($sql);

if(count($r) > 0){
	?>
	<select name="status" id="status" class="required" style="height: 32px; border-radius:5px;">
	<?php
	for($i = 0; $i < count($r); $i++){
		?>
		<option value="<?php echo $r[$i]->ID; ?>" <?php echo ($current_status == $r[$i]->ID) ? " selected " : ""; ?>><?php echo $r[$i]->Description; ?></option>
		<?php
	}
	?>
	</select>
	<?php
}

?>
	