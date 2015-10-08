<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/prc/receiving/function.php";
include_once "$_SERVER[DOCUMENT_ROOT]/prc/inspection/function.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1"/>
<title>PRC Inventories, Inc - QA</title>
<?php
include_once "$_SERVER[DOCUMENT_ROOT]/prc/master.php";
print "<link rel='stylesheet' type='text/css' href='$_SERVER[SERVER_ROOT]/prc/css/bootstrap-combined.min.css' />";
print "<script type = 'text/javascript' src='$_SERVER[SERVER_ROOT]/prc/js/bootstrap.min.js'></script>";

print "<link rel='stylesheet' href='$_SERVER[SERVER_ROOT]/prc/css/jquery-ui.css' />";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery-ui.js'></script>";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery.validate.min.js'></script>";
print "<script src='$_SERVER[SERVER_ROOT]/prc/js/jquery.validate.js'></script>";
?>
<script>
$(document).ready(function(){

});
</script>
</head>
<body>

<br/>

<div id="content-center" style="width: 80%; border-radius: 10px; border: 1px solid grey;">
	
	<h4>NPI</h4>
	<hr />
	
	<?php
	$dbo = new dbo();
	$dbo->ny_connect();

	$sql = "SELECT
			SKU,
			MAX(CASE WHEN Level = 'Work Instruction' THEN Status END) 'WI',
			MAX(CASE WHEN Level = 'ETL Certification' THEN Status END) 'ETL',
			MAX(CASE WHEN Level = 'Art Work' THEN Status END) 'AW',
			MAX(CASE WHEN Level = 'Packaging' THEN Status END) 'Packaging',
			MAX(CASE WHEN Level = 'FAR' THEN Status END) 'FAR',
			MAX(CASE WHEN Level = 'TRACS' THEN Status END) 'TRACS',
			(SELECT PPI FROM breville.summary_report_temp
			where SKU = x.SKU
			order by ID desc limit 1) as 'PPI'
			FROM
			(
			SELECT 
			m.ModelName as 'SKU',
			c.LevelDescription as 'Level',
			s.Description as 'Status',
			s.CSS
			FROM npi.main m
			join npi.level_relations lr on m.RelationID = lr.LRID
            join npi.levels p on lr.ParentLevelID = p.ID
			join npi.levels c on lr.ChildLevelID = c.ID
			join npi.status s on m.StatusID = s.ID
            where lr.ParentLevelID = 18
			) x
			GROUP BY SKU
			;";

	$r = $dbo->run_query($sql);

	/*
	print "<pre>";
	print_r($r);
	print "</pre>";
	*/

	?>
	<table class="table table-bordered">
		<thead id="theadId">
		<tr>
			<th>SKU</th>
			<th>Work Instruction</th>
			<th>ETL Certification</th>
			<th>Art Work</th>
			<th>Packaging</th>
			<th>FAR</th>
			<th>TRACS</th>
			<th>PPI</th>
		</tr>
		</thead>
		<tbody id="tbodyId">
		<?php
		for($i = 0; $i < count($r); $i++){
			echo "
			<tr>
				<td>". $r[$i]->SKU ."</td>
				<td>". $r[$i]->WI ."</td>
				<td>". $r[$i]->ETL ."</td>
				<td>". $r[$i]->AW ."</td>
				<td>". $r[$i]->Packaging ."</td>
				<td>". $r[$i]->FAR ."</td>
				<td>". $r[$i]->TRACS ."</td>
				<td>". $r[$i]->PPI ."</td>
			</tr>";
		}
		?>
		</tbody>
	</table>

</div>
</body>
</html>