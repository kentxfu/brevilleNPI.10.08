<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$dbo = new dbo();
$dbo->ny_connect();

$sql = "SELECT
		*
		FROM `npi`.`subCategories`
		;";

$rSubCat = $dbo->run_query($sql);

if(count($rSubCat) > 0){
	?>
	<select name="subCat" id="subCat" class="required" style="width: 175px; height: 32px;">
	<?php
	for($i = 0; $i < count($rSubCat); $i++){
		?>
		<option value="<?php echo $rSubCat[$i]->SubCategoryID; ?>" <?php echo ($current_subCat == $rSubCat[$i]->SubCategoryID) ? " selected " : ""; ?>><?php echo $rSubCat[$i]->SubCategoryName; ?></option>
		<?php
	}
	?>
	</select>
	<?php
}

?>
	