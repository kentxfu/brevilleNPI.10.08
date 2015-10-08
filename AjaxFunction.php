<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

$function = $_POST['function'];

$dbo = new dbo();
$dbo->ny_connect();

switch($function)
{
	case "detailLvl3":
		$permitted = true;

		$SKU = $_POST['SKU'];
		$levelID = $_POST['LevelID'];
		$ReLvlID = $_POST['ReLvlID'];

		/*
		print "<pre>";
		print_r($_POST);
		print "</pre>";
		*/

		$sql = "SELECT 
				x.*,
				y.MainRelationID,
				y.Status as 'MainStatus',
				y.CSS_Class,
				y.pack_dim,
				y.pack_weight,
				y.Comments
				FROM

				(SELECT 
				m.ID as 'ReLvlID',
				m.RelationID,
				m.ModelName,
				m.StatusID,
				l.DisplayName as 'Main',
				l2.DisplayName as 'Sub',
				s.Description as 'SubStatus',
				date(m.DateTimeUpdated) as 'DateTimeUpdated',
				concat(e.en_first_name, ' ', e.en_last_name) as 'UserName',
				l.IsFile,
				l.FileName,
				l.FileLocation,
				l.Comments
				FROM
				npi.main m
				JOIN npi.level_relations lr on m.RelationID = lr.LRID
				JOIN npi.levels l on lr.ParentLevelID = l.ID
				JOIN npi.levels l2 on lr.ChildLevelID = l2.ID
				JOIN npi.status s on m.StatusID = s.ID
				LEFT JOIN mgmt_tools.employee_name e on m.UserID = e.en_id_num
				LEFT JOIN (	SELECT * FROM npi.logs 
							WHERE LogID in 
							(SELECT MAX(LogID) FROM npi.logs GROUP BY LevelID)
							GROUP BY LevelID) l on m.RelationID = l.LevelID and m.ModelName = l.SKU
				where ModelName = '". $SKU ."'
				AND ParentLevelID = ". $levelID .") x

				JOIN
				(
				Select 
				m.RelationID as 'MainRelationID',
				m.ModelName,
				s.Description as 'Status',
				s.CSS_Class,
				i.pack_dim,
				i.pack_weight,
				m.comments
				FROM npi.main m
				JOIN npi.level_relations lr on m.RelationID = lr.LRID
				JOIN npi.status s on m.statusId = s.ID
				JOIN npi.items i on m.ModelName = i.ModelNumber
				WHERE m.ModelName = '". $SKU ."' and ChildLevelID = ". $levelID .") y
				on x.ModelName = y.ModelName
				order by x.RelationID";

		/*
		print "<pre>";
		print $sql;
		print "</pre>";
		*/

		$r = $dbo->run_query($sql);

		/*
		print "<pre>";
		print_r($r);
		print "</pre>";
		*/

		$SKUcolumnSelected = $r[0]->Main;
		$iconFileName = $r[0]->Main;			// always .png and in /img/ folder
		$detailStatus = $r[0]->MainStatus;
		$detailClass = $r[0]->CSS_Class;				// same as class from cell
		
		// Packaging Dimensions & Weight (?)
		$dimensions = $r[0]->pack_dim;
		$dims = explode(",",$dimensions);
		$weight = $r[0]->pack_weight;
		$length = $dims[0];
		$width = $dims[1];
		$height  = $dims[2];	
		// Comments
		$comments = $r[0]->Comments;

		?>
		<img class="pull-left modalWindowIcon" src="<?php echo $_SERVER['SERVER_ROOT'] . "/npi/img/". ($iconFileName) .".png"; ?>" >
		<h4 class="modal-title" style="text-align:center" id="myModalLabel">
		<strong> <?php echo $SKU.': </strong>'.$SKUcolumnSelected ?>
		</h4> 
		<h4 style="text-align: center;" >
			<span style="background-color:#000; color:#fff; padding:5px; border-top-left-radius: 5px; border-bottom-left-radius: 5px;"> Status: </span>
			<span <?php echo "class='".$detailClass."' style='border-top-right-radius: 5px; border-bottom-right-radius: 5px;'>".$detailStatus ?></span>
			<?php 
			if($permitted){
				echo "
				<a href='javascript:void(0);' class='btn btn-default' id='btnChangeStatus' data-title='". $r[0]->Main ."' data-level='1' data-ReLvlID='".$ReLvlID."'>Update Status</a>";
			}
			?>
		</h4>

		<hr/>

		<table id="modalTable" class="table table-hover table-bordered table-condensed form-inline">				
			<thead style="background-color: #e6EEEE;">
				<tr>
					<th style="width: 200px;">Description</th>
					<th style="width: 100px;">Status</th>
					<th>File</th>
					<th style="width: 125px;">Date Modified</th>
					<th style="width: 100px;">Modified By</th>
					<th style="width: 100px;">Action</th>
				</tr>
			</thead>
			
			<tbody>
				<?php
					for ($i = 0; $i < count($r); $i++){
						$isChecked = ($r[$i]->SubStatus == "Not Started") ? "" : "checked";

						echo '<tr>';
							echo '<td>	<input type="checkbox" onclick="return false"'. $isChecked .' >'.$r[$i]->Sub.' 
										<button class="btn transModal" data-ID="'. $r[$i]->RelationID .'" data-SKU="'. $r[$i]->ModelName .'" data-Type="'. $r[$i]->Sub .'" data-toggle="tooltip" data-placement="left" style="float:right">
										<span class="glyphicon glyphicon-list" aria-hidden="true" title="View historical transaction(s)"></span></button>
								  </td>';
							echo '<td>'. $r[$i]->SubStatus .'</td>';
							echo '<td>'. (($r[$i]->IsFile) ? "<a href='$_SERVER[SERVER_ROOT]/npi/files/". $r[$i]->FileLocation . "' target='_blank'>". $r[$i]->FileName . "</a>": "Not available") .'</td>';
							echo '<td style="text-align: center;">'. (!empty($r[$i]->DateTimeUpdated) ? date("m/d/Y", strtotime($r[$i]->DateTimeUpdated)) : "-") .'</td>';
							echo '<td style="text-align: center;">'. (!empty($r[$i]->UserName) ? $r[$i]->UserName : "-") .'</td>';
							echo '<td style="text-align: center;"><button class="btn btnModifyForm" data-title="'. $r[$i]->Sub .'" data-level="2" data-ReLvlID="'. $r[$i]->ReLvlID .'" data-SKU="' . $r[$i]->ModelName .'">Update</button></td>';
						echo '</tr>';
					} ?>
			</tbody>
		</table>

		<hr>
		
		<!-- if packaging information exists -->
		<?php
			if( isset($length)){
				echo '<p><strong>Packaging Dimensions:  </strong>'.$length.'L x '.$width.'W x '.$height.'H </p>';						
			}
			
			if (isset($weight)){
				echo '<p><strong>Finished Good Weight:  </strong>'.$weight.' lbs.</p>';
				echo '<hr>';
			}				
		?>		
		
		<h4><u><b>Comments: </b></u></h4>
			<?php
				echo $comments;
			?>
			
		<br><br>

		<script>
		$(".transModal").click(function(){
			var ID = $(this).attr("data-ID"),
				SKU = $(this).attr("data-SKU"),
				Type = $(this).attr("data-Type");

			$.ajax({
				url: "AjaxFunction.php",
				type: "post",
				data: {"function":"transDetails","ID":ID,"SKU":SKU},
				success: function(r){
					$("#transModelTitle").html("Transaction history for " + SKU + " - " + Type)
					$("#transModalBody").html(r);
					$("#trans-modal").modal({
						backdrop: "static",
						keyboard: false
					},"show");
				}
			})
		})

		$("#btnChangeStatus").click(function(){
			var title = $(this).attr("data-title"),
				level = $(this).attr("data-level"),
			    ReLvlID = $(this).attr("data-ReLvlID");

			$("#modalStatusUpdateTitle").html("Status update for: " + title);
			$.ajax({
				url: "AjaxFunction.php",
				type: "POST",
				data: {"function":"updateStatusFrom","level":level,"ReLvlID":ReLvlID},
				success: function(r){
					$("#modalStatusUpdateBody").html(r);
					$("#modalStatusUpdate").modal("show");
				}
			})
		})

		$(".btnModifyForm").click(function(){
			var title = $(this).attr("data-title"),
				level = $(this).attr("data-level"),
			    ReLvlID = $(this).attr("data-ReLvlID");

			$("#modalStatusUpdateTitle").html("Status update for: " + title);
			$.ajax({
				url: "AjaxFunction.php",
				type: "POST",
				data: {"function":"updateStatusFrom","level":level,"ReLvlID":ReLvlID},
				success: function(r){
					$("#modalStatusUpdateBody").html(r);
					$("#modalStatusUpdate").modal("show");
				}
			})
		})
		</script>

		<?php

		break;

	case "transDetails":
		$levelId = $_POST['ID'];
		$sku = $_POST['SKU'];

		$sql = "SELECT
				l.*,
				concat(e.en_first_name, ' ', e.en_last_name) as 'UserName'
				FROM 
				npi.logs l
				JOIN mgmt_tools.employee_name e on l.UserID = e.en_id_num
				WHERE SKU = '". $sku ."'
				AND LevelID = $levelId
				ORDER BY LogID Desc;";

		#print $sql;

		$r = $dbo->run_query($sql);

		if(count($r) > 0){
			?>
			<table id="modalTable" class="table table-hover table-bordered table-condensed form-inline">
				<thead style="background-color: #e6EEEE;">
				<tr>
					<th>File Name</th>
					<th>Comments</th>
					<th>Time Modified</th>
					<th>Modified By</th>
				</tr>
				</thead>
				
				<tbody>
			<?php
			for($i = 0; $i < count($r); $i++){
				echo "
				<tr>
					<td>". (($r[$i]->IsFile) ? "<a href='$_SERVER[SERVER_ROOT]/npi/files/". $r[$i]->FileLocation . "' target='_blank'>". $r[$i]->FileName . "</a>": "Not available") . "</td>
					<td>". $r[$i]->Comments."</td>
					<td>". $r[$i]->DateTimeCreated ."</td>
					<td>". $r[$i]->UserName ."</td>
				</tr>";
			}
			?>
			</table>
			<?php
		}
		else
		{
			echo "<div class='alert alert-danger'>No transaction found.</div>";
		}

		break;

	/* update level status form */
	case "updateStatusFrom":
		$level = $_POST['level'];
		$ReLvlID = $_POST['ReLvlID'];

		switch($level){
			case "1":
				$sql = "SELECT 
						m.ID,
						m.ModelName,
						s.ID as 'current_status',
						l2.*
						FROM `npi`.`main` m
						JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
						JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.ID
						JOIN `npi`.`status` s on m.StatusID = s.ID
						WHERE m.ID = $ReLvlID;";

				$r = $dbo->run_query($sql);

				?>
				<div class="form-inline">

					<h4>Model: <?php echo $r[0]->ModelName; ?></h4>
					<br/>
					<b>Status: </b><br/>

					<?php 
					$current_status = $r[0]->current_status;
					include "dd_status.php"; 
					?>
					<br/>
					<br/>
					<b>Comment: </b><br/>
					<textarea name="comment" id="comment" style="width: 600px; height: 100px; border-radius:5px;"></textarea>
					<br/>
					<br/>
					<button class="btn btn-primary" id="btnStatusSave">Update</button>
				
				</div>

				<script>
				$("#btnStatusSave").click(function(){
					$.ajax({
						url: "AjaxFunction.php",
						type: "POST",
						data: {"function":"updateStatus","level":"<?php echo $level; ?>","ReLvlID":"<?php echo $ReLvlID; ?>","Status":$("#status").val(),"comment":$("#comment").val()},
						success: function(r){
							var d = JSON.parse(r);

							alert(d.msg);

							if(d.status){
								window.location.reload();
							}
						}
					})
				})
				</script>
				<?php
				break;

			case "2":
				#echo $ReLvlID . "<br/>";

				$sql = "SELECT 
						m.ID as 'MainID',
						m.ModelName,
						l2.*,
						s.ID as 'current_status'
						FROM `npi`.`main` m
						JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
						JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.ID
						JOIN `npi`.`status` s on m.StatusID = s.ID
						WHERE m.ID = $ReLvlID;";

				#echo $sql;
				$r = $dbo->run_query($sql);

				?>
				<div id="statusUpdateForm" class="form-inline">

				<form action="" method="post" enctype="multipart/form-data" id="uploadForm" >
					<input type="hidden" name="ReLvlID" id="ReLvlID" value="<?php echo $r[0]->MainID; ?>" >
					<h4>Model: <?php echo $r[0]->ModelName; ?></h4>
					<br/>
					<b>Status for "<?php echo $r[0]->DisplayName; ?>": </b><br/>

					<?php 
					$current_status = $r[0]->current_status;
					include "dd_status.php"; 
					?>
					<br/>
					<br/>
					<input name="upload" id="upload" type="file" style="height: 35px;">
					<br/>
					<b>Comment: </b><br/>
					<textarea name="comment" id="comment" style="width: 600px; height: 100px; border-radius:5px;"></textarea>
					<br/>
					<br/>
					<a class="btn btn-primary" id="btnStatusSave">Update</a>
					<a class="btn btn-default" data-dismiss="modal">Cancel</a>
				</form>
				
				</div>

				<script>
				$("#upload").change(function(){
					var fileType = $("#upload").prop("files")[0];

					if(!isValidFile(fileType.type)){
						alert("Only PDF or Word documents are allowed. Please choose another file format.");
						$("#upload").val("");
						return;
					}

					if(!isGoodFileSize(fileType.size)){
						alert("File size is too big. Please choose another file.");
						$("#upload").val("");
						return;					
					}
				})

				$("#btnStatusSave").click(function(){
					var file_data = $("#upload").prop("files")[0];

					var d = new FormData();

					d.append("file", file_data);
					d.append("data", $("#uploadForm").serialize());

					$.ajax({
						url: 'upload.php',
						cache: false,
						contentType: false,
						processData: false,
						//data: {"function":"upload","file":file_data,"status":$("#status").val(),"comment":$("#comment").val()},                      
						data: d,                   
						type: "POST",
						success: function(r){
							var d = JSON.parse(r);

							alert(d.msg);

							if(d.status){
								window.location.reload();
							}
						}
					});                
				})

				function isValidFile(format){
					switch(format){
						case "pdf":
						case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
						case "doc":
							return true;
							break;
						default:
							return false;
							break;
					}
				}

				function isGoodFileSize(fileSize){
					return (fileSize > 5000000) ? false : true;
				}

				</script>
				<?php
				break;
		}
		break;

	/* update level status */
	case "updateStatus":
		$level = $_POST['level'];
		$ReLvlID = $_POST['ReLvlID'];
		$status = $_POST['Status'];
		$comment = (!empty($_POST['comment'])) ? "'" . addslashes($_POST['comment']) . "'": "NULL";

		#echo "$level, $ReLvlID, $status";

		$sql = "SELECT 
				m.ID,
				m.ModelName,
				l.ID as 'LevelID'
				#,l2.*
				FROM `npi`.`main` m
				JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
				JOIN `npi`.`levels` l on lr.ParentLevelID = l.ID
				#JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.ID
				WHERE m.ID = $ReLvlID;";

		$r = $dbo->run_query($sql);

		$sql = "INSERT INTO `npi`.`logs`
				(SKU, LevelID, StatusID, IsFile, Comments, DateTimeCreated, UserID)
				VALUES
				('". $r[0]->ModelName . "', ". $r[0]->LevelID .", ". $status . ", 0, ". $comment .", NOW(), ". $_SESSION['SESS_MEMBER_ID'] .");";

		#echo $sql;

		if($dbo->run_query($sql)){
			// update datetime and user id on main level
			$sql = "UPDATE 
					`npi`.`main`
					SET StatusID = $status,
					Comments = ". $comment . ",
					DateTimeUpdated = NOW(),
					UserID = ". $_SESSION['SESS_MEMBER_ID'] ."
					WHERE ID = $ReLvlID;";

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
		break;

	case "upload":
		$d = $_POST["d"];

		echo "test";
		
		print_r($_FILES);

		print $d;
		break;
}

?>
		