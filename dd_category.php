<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$sql = "SELECT
		*
		FROM `npi`.`categories`
		;";

$rCat = $dbo->run_query($sql);

if(count($rCat) > 0){
	?>
	<select name="category" id="category" class="required" style="width: 175px; height: 32px;">
	<?php
	for($i = 0; $i < count($rCat); $i++){
		?>
		<option value="<?php echo $rCat[$i]->CategoryID; ?>" <?php echo ($current_category == $rCat[$i]->CategoryID) ? " selected " : ""; ?>><?php echo $rCat[$i]->CategoryName; ?></option>
		<?php
	}
	?>
	</select>
	<?php
}

?>
	