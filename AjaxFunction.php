<?php
include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/email.class.php";

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
				m.MainID as 'ReLvlID',
				m.RelationID,
				m.ModelName,
				m.StatusID,
				l.DisplayName as 'Main',
				l2.DisplayName as 'Sub',
				s.Description as 'SubStatus',
				s.CSS_Class as 'SubClass',
				date(m.DateTimeUpdated) as 'DateTimeUpdated',
				concat(e.en_first_name, ' ', e.en_last_name) as 'UserName',
				log.IsFile,
				log.FileName,
				log.FileLocation,
				log.Comments
				FROM
				npi.main m
				JOIN npi.level_relations lr on m.RelationID = lr.LRID
				JOIN npi.levels l on lr.ParentLevelID = l.LevelID
				JOIN npi.levels l2 on lr.ChildLevelID = l2.LevelID
				JOIN npi.status s on m.StatusID = s.StatusID
				LEFT JOIN mgmt_tools.employee_name e on m.UserID = e.en_id_num
				LEFT JOIN (	SELECT * FROM npi.logs 
							WHERE LogID in 
							(SELECT MAX(LogID) FROM npi.logs GROUP BY SKU, LevelID)) log on m.RelationID = log.LevelID and m.ModelName = log.SKU
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
				JOIN npi.status s on m.statusId = s.StatusID
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
		<h4 class="form-inline" style="text-align: center;" >
			<span style="background-color:#000; color:#fff; padding:5px; border-top-left-radius: 5px; border-bottom-left-radius: 5px;"> Status: </span>
			<span <?php echo "class='".$detailClass."' style='border-top-right-radius: 5px; border-bottom-right-radius: 5px;'>".$detailStatus ?></span>
			<?php 
			if($permitted){
				echo "
				<button class='btn' style='padding: 4px 12px;' id='btnChangeStatus' data-title='". $r[0]->Main ."' data-level='1' data-ReLvlID='".$ReLvlID."'>Update Status</button>";
			}
			?>
		</h4>

		<hr/>

		<table id="modalTable" class="table table-hover table-bordered table-condensed form-inline">				
			<thead style="background-color: #e6EEEE; font-size: 11px;">
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
							echo '<td>	<input type="checkbox" onclick="return false"'. $isChecked .' >'.$r[$i]->Sub.'</td>';
							echo '<td class="'.$r[$i]->SubClass.'">'. $r[$i]->SubStatus .'</td>';
							echo '<td>'. (($r[$i]->IsFile) ? "<a href='$_SERVER[SERVER_ROOT]/npi/files/". $r[$i]->FileLocation . "' title='". $r[$i]->FileName ."' target='_blank'>". substr($r[$i]->FileName, 0, 10) . "..." . "</a>": "Not available") .'</td>';
							echo '<td style="text-align: center;">'. (!empty($r[$i]->DateTimeUpdated) ? date("m/d/Y", strtotime($r[$i]->DateTimeUpdated)) : "-") .'
									<button class="btn btn-xs transModal" data-ID="'. $r[$i]->RelationID .'" data-SKU="'. $r[$i]->ModelName .'" data-Type="'. $r[$i]->Sub .'" data-toggle="tooltip" data-placement="left" style="float:right">
									<span class="glyphicon glyphicon-list" aria-hidden="true" title="View historical transaction(s)"></span></button>
									</td>';
							echo '<td style="text-align: center;">'. (!empty($r[$i]->UserName) ? $r[$i]->UserName : "-") .'</td>';
							echo '<td style="text-align: center;"><button class="btn btnModifyForm" data-title="'. $SKU . ", " . $SKUcolumnSelected . " " . $r[$i]->Sub .'" data-level="2" data-ReLvlID="'. $r[$i]->ReLvlID .'" data-SKU="' . $r[$i]->ModelName .'">Update</button></td>';
						echo '</tr>';
					} ?>
			</tbody>
		</table>

		<hr>
		
		<!-- if packaging information exists -->
		<?php
		if($SKUcolumnSelected == "Art Work" OR $SKUcolumnSelected == "Packaging"){
			if( isset($length)){
				echo '<p><strong>Outer Box Dimensions:  </strong>'.$length.'L x '.$width.'W x '.$height.'H </p>';						
			}
			
			if (isset($weight)){
				echo '<p><strong>Finished Good Weight:  </strong>'.$weight.' lbs.</p>';
				echo '<hr>';
			}
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
					$("#trans-modal").modal("show");
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
				data: {"function":"updateStatusForm","level":level,"ReLvlID":ReLvlID},
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

			$("#modalStatusUpdateTitle").html(title);
			$.ajax({
				url: "AjaxFunction.php",
				type: "POST",
				data: {"function":"updateStatusForm","level":level,"ReLvlID":ReLvlID},
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
				s.Description as 'Status',
				concat(e.en_first_name, ' ', e.en_last_name) as 'UserName'
				FROM 
				npi.logs l
				JOIN npi.status s on l.StatusID = s.StatusID
				JOIN mgmt_tools.employee_name e on l.UserID = e.en_id_num
				WHERE SKU = '". $sku ."'
				AND LevelID = $levelId
				ORDER BY LogID Desc;";

		#print $sql;

		$r = $dbo->run_query($sql);

		if(count($r) > 0){
			?>
			<table id="modalTable" class="table table-hover table-bordered table-condensed form-inline" style="font-size: 14px;">
				<thead style="background-color: #e6EEEE;">
				<tr style="font-size: 75%;">
					<th>Status</th>
					<th>File Name</th>
					<th>Comments</th>
					<th>Time Modified</th>
					<th>Modified By</th>
					<!--<th></th>-->
				</tr>
				</thead>
				
				<tbody>
			<?php
			for($i = 0; $i < count($r); $i++){
				echo "
				<tr>
					<td>". $r[$i]->Status ."</td>
					<td>". (($r[$i]->IsFile) ? "<a href='$_SERVER[SERVER_ROOT]/npi/files/". $r[$i]->FileLocation . "' target='_blank' title='".$r[$i]->FileName."'>". substr($r[$i]->FileName, 0, 20) . "...</a>": "Not available") . "</td>
					<td>". $r[$i]->Comments."</td>
					<td>". date("Y/m/d h:i:s", strtotime($r[$i]->DateTimeCreated)) ."</td>
					<td>". $r[$i]->UserName ."</td>";
					// only allowed to update the latest transaction
					/*
				echo "
					<td>";
					if($i == 0)
						echo "<button class='btnRevise btn btn-warning' data-id='".$r[$i]->LogID."'>Revise</button>";
					else
						echo "-";
				echo "
					</td>";
					*/
				echo "
				</tr>";
			}
			?>
			</table>
			<script>
			$(".btnRevise").click(function(){
				var title = "Revise Transaction (ID: " + $(this).attr("data-id") + ")",
				    TransId = $(this).attr("data-id");

				$("#modalTransUpdateTitle").html(title);

				$.ajax({
					url: "AjaxFunction.php",
					type: "POST",
					data: {"function":"reviseTransaction","TransId":TransId},
					success: function(r){
						$("#modalTransUpdateBody").html(r);
						$("#modalTransUpdate").modal("show");
					}
				})		
			})
			</script>
			<?php
		}
		else
		{
			echo "<div class='alert alert-danger'>No transaction found.</div>";
		}

		break;

	/* update level status form */
	case "updateStatusForm":
		print "<script src='js/validationKF.js'></script>";
		print "<script type='text/javascript' src='jQuery-TE_v.1.4.0/jquery-te-1.4.0.min.js'></script>";
		print "<link rel='stylesheet' type='text/css' href='jQuery-TE_v.1.4.0/jquery-te-1.4.0.css' />";

		?>
		<script>
		$(function(){
			$(".te").jqte();
		});
		</script>
		<?php

		$level = $_POST['level'];
		$ReLvlID = $_POST['ReLvlID'];

		switch($level){
			case "1":
				$sql = "SELECT 
						m.MainID,
						m.ModelName,
						m.StatusID as 'current_status',
						m.Comments,
						l2.*
						FROM `npi`.`main` m
						JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
						JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.LevelID
						JOIN `npi`.`status` s on m.StatusID = s.StatusID
						WHERE m.MainID = $ReLvlID;";

				#echo $sql;
				$r = $dbo->run_query($sql);

				?>
				<div id="mainForm" class="form-inline">

					<h4>Model: <?php echo $r[0]->ModelName; ?></h4>
					<br/>
					<b>Status: </b><br/>

					<?php 
					$current_level = $r[0]->LevelDescription;
					$current_status = $r[0]->current_status;

					include "dd_status.php"; 
					?>
					<br/>
					<br/>
					<b>Comment (includes last comment if any): </b>
					<textarea name="comment" id="comment" class="te" style="width: 600px; height: 100px; border-radius:5px;" class="required"><?php echo $r[0]->Comments; ?></textarea>
					<br/>
					<br/>
					<button class="btn btn-primary" id="btnStatusSave">Update</button>
					<button class="btn btn-default" data-dismiss="modal">Cancel</button>
					<?php
					/*
					if($_SESSION['SESS_MEMBER_ID'] == 1){
						echo '<div style="float:right"><button class="btn btn-default" id="btnNotification">Notification</button></div>';
					}*/
					?>
				
				</div>

				<script>
				$("#btnStatusSave").click(function(){
					if(validation("mainForm")){
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
					}
				})

				
				$("#btnNotification").click(function(){
					$.ajax({
						url: "AjaxFunction.php",
						type: "POST",
						data: {"function":"notification","level":"<?php echo $level; ?>","ReLvlID":"<?php echo $ReLvlID; ?>","Status":$("#status").val(),"comment":$("#comment").val()},
						success: function(r){
							var d = JSON.parse(r);

							alert(d.msg);
						}
					})					
				})
				

				</script>
				<?php
				break;

			case "2":
				#echo $ReLvlID . "<br/>";

				$sql = "SELECT 
						m.MainID as 'MainID',
						m.ModelName,
						l2.*,
						s.StatusID as 'current_status',
						m.Comments
						FROM `npi`.`main` m
						JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
						JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.levelID
						JOIN `npi`.`status` s on m.StatusID = s.StatusID
						WHERE m.MainID = $ReLvlID;";
				/*
				print "<pre>";
				print $sql;
				print "</pre>";
				*/
				$r = $dbo->run_query($sql);

				?>
				<div id="statusUpdateForm" class="form-inline">

				<form action="" method="post" enctype="multipart/form-data" id="uploadForm" class="form-inline">
					<input type="hidden" name="ReLvlID" id="ReLvlID" value="<?php echo $r[0]->MainID; ?>" >
					<h4 style="font-weight:bold; text-decoration: underline;">Model: <?php echo $r[0]->ModelName; ?></h4>
					<br/>
					<b>Status for "<?php echo $r[0]->LevelDescription . " " . $r[0]->DisplayName; ?>": </b><br/>

					<?php 
					$current_status = $r[0]->current_status;
					include "dd_status.php"; 
					?>
					<br/>
					<br/>
					<input name="upload" id="upload" type="file" style="height: 35px;">
					<?php 
					$sql = "SELECT l.*
							FROM `npi`.`logs` l
							JOIN `npi`.`main` m on l.SKU = m.ModelName and l.LevelID = m.RelationID and l.StatusID = m.StatusID
							WHERE m.mainID = $ReLvlID
							ORDER BY LogID DESC LIMIT 1;";

					#print $sql;
					$r_log = $dbo->run_query($sql);

					if(count($r_log) > 0){
						#print_r($r_log);
						if($r_log[0]->IsFile){
							echo "<input type='checkbox' name='UseLastFile' id='UseLastFile'>Use the same file last time uploaded (<a href='files/". $r_log[0]->FileLocation . "'>".$r_log[0]->FileName ."</a>) <br/>";
						}
					}
					?>
					<br/>
					<b>Comment (includes last comment if any): </b><br/>
					<textarea name="comment" id="comment" class="te" style="width: 600px; height: 100px; border-radius:5px;" class="required"><?php echo $r[0]->Comments; ?></textarea>
					<br/>
					<br/>
					<a class="btn btn-primary" id="btnStatusSave">Update</a>
					<a class="btn btn-default" data-dismiss="modal">Cancel</a>
					<?php
					/*
					if($_SESSION['SESS_MEMBER_ID'] == 1){
						echo '<div style="float:right"><a href="javascript:void(0);" class="btn btn-default" id="btnNotification">Notification</a></div>';
					}*/
					?>
				</form>
				
				</div>

				<script>
				$("#upload").change(function(){
					var fileType = $("#upload").prop("files")[0];
					/*
					console.log(fileType);
					console.log(fileType.type);
					*/

					if(!isValidFile(fileType.type)){
						alert("Only PDF or Word documents are allowed. Please choose another file format.");
						$("#upload").val("");
						return;
					}

					var sizeLimit = 5000000;

					if(!isGoodFileSize(sizeLimit, fileType.size)){
						alert("File size is too big. Max file size is " + parseInt(sizeLimit / 1000000) + "MB. Current size: " + parseInt(fileType.size / 1000000 ) + "MB.");
						$("#upload").val("");
						return;					
					}

					$("#UseLastFile").prop("checked", false);
				})

				$("#UseLastFile").change(function(){
					if($(this).is(":checked")){
						$("#upload").val("");
					}
				})

				$("#btnStatusSave").click(function(){
					if(validation("uploadForm")){
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
					}                
				})

				
				$("#btnNotification").click(function(){
					$.ajax({
						url: "AjaxFunction.php",
						type: "POST",
						data: {"function":"notification","level":"<?php echo $level; ?>","ReLvlID":"<?php echo $ReLvlID; ?>","Status":$("#status").val(),"comment":$("#comment").val()},
						success: function(r){
							var d = JSON.parse(r);

							alert(d.msg);

						}
					})					
				})
				

				function isValidFile(format){
					switch(format){
						case "pdf":
						case "application/pdf":
						case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
						case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
						case "doc":
							return true;
							break;
						default:
							return false;
							break;
					}
				}

				function isGoodFileSize(sizeLimit, fileSize){
					return (fileSize > sizeLimit) ? false : true;
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
				m.MainID,
				m.ModelName,
				l.LevelID
				#,l2.*
				FROM `npi`.`main` m
				JOIN `npi`.`level_relations` lr on m.RelationID = lr.LRID
				JOIN `npi`.`levels` l on lr.ParentLevelID = l.LevelID
				#JOIN `npi`.`levels` l2 on lr.ChildLevelID = l2.ID
				WHERE m.MainID = $ReLvlID;";

		$r = $dbo->run_query($sql);


		// previous transaction
		$sql = "SELECT s.Description FROM `npi`.`logs` l JOIN `npi`.`Status` s on l.StatusID = s.StatusID 
			 	WHERE MainID = $ReLvlID ORDER BY LogID desc limit 1";

		$r_preTrans = $dbo->run_query($sql);

		$prevTrans = (count($r_preTrans) == 1) ? $r_preTrans[0]->Description : "Not Started";


		$sql = "INSERT INTO `npi`.`logs`
				(SKU, MainID, LevelID, StatusID, IsFile, Comments, DateTimeCreated, UserID)
				VALUES
				('". $r[0]->ModelName . "', ". $ReLvlID .", ". $r[0]->LevelID .", ". $status . ", 0, ". $comment .", NOW(), ". $_SESSION['SESS_MEMBER_ID'] .");";

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
				$notification = true;

				if($notification){

					$notification_comment = (!empty($_POST['comment'])) ? $_POST['comment'] : "None";

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

					#$initialStatus = (!empty($r[0]->PreviousStatus)) ? $r[0]->PreviousStatus : "Not Started";
					$initialStatus = $prevTrans;

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
		break;

	case "notification":
		$level = $_POST['level'];
		$ReLvlID = $_POST['ReLvlID'];
		$status = $_POST['Status'];
		$comment = (!empty($_POST['comment'])) ? $_POST['comment'] : "None";

		#echo "$level, $ReLvlID, $status";

		// previous transaction
		$sql = "SELECT s.Description FROM `npi`.`logs` l JOIN `npi`.`Status` s on l.StatusID = s.StatusID 
			 	WHERE MainID = $ReLvlID ORDER BY LogID desc limit 1";

		$r_preTrans = $dbo->run_query($sql);

		$prevTrans = (count($r_preTrans) == 1) ? $r_preTrans[0]->Description : "Not Started";


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

		#echo $sql;

		$r = $dbo->run_query($sql);


		$email = new email();

		$to = $email->getEmailList("NPI");

		#$initialStatus = (!empty($r[0]->PreviousStatus)) ? $r[0]->PreviousStatus : "Not Started";
		$initialStatus = $prevTrans;

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
							Comments: ".$comment."
							<br/>
							<br/>
							Please log in to view the changes. <br/>
						</p>
					</body>
					</html>";

		#print_r($content); exit;

		if($email->send($to, $subject, $content)){
			$d = array(	"status"	=> true,
						"msg"		=> "Notification sent successfully");
		}
		else{
			$d = array( "status"	=> false,
						"msg"		=> "Error! Notification failed.");			
		}

		print json_encode($d);
		break;

	/*
	case "itemDetails":
		print "<script src='js/validationKF.js'></script>";

		$SKU = $_POST['SKU'];
		$sql = "SELECT 
				i.*,
				u.id_num as 'ImageID'
				FROM `npi`.`items` i
				JOIN `breville`.`upcreference` u on i.Modelnumber = u.ModelNumber
				WHERE i.ModelNumber = '$SKU';";

		#print $sql;

		$res = $dbo->run_query($sql);
		$r = $res[0];

		?>
		
		<div style="min-height: 600px;">
		<div class="col-md-2">
			<div>
			<?php
			if(file_exists("../breville/images/parts/".$r->ImageID."-0.jpg"))
				echo "<img src='../breville/images/parts/".$r->ImageID."-0.jpg' style='width: 100px;'>";
			else
				echo "<img src='img/skus/NA.jpg' style='width: 100px;'>";
			?>
			</div>
		</div>
		<div class="col-md-10">
			<form id="itemDetailForm">
			<input type="hidden" name="sku" value="<?php echo $SKU; ?>" >
	
			<table class="table table-bordered">
				<tr>
					<td style="width: 200px;">Description</td>
					<td><textarea name="desc" class="required" style="resize: none; width: 350px; height: 50px;"><?php echo $r->Description; ?></textarea></td>
				</tr>
				<tr>
					<td>Reman. Status</td>
					<td>
						<select name="reman_status" style="height: 32px;">
							<option value="0" <?php echo ($r->reman_status == 0) ? "" : "selected"; ?>>No</option>
							<option value="0" <?php echo ($r->reman_status == 0) ? "" : "selected"; ?>>Yes</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Original UPC</td>
					<td><input type="text" name="upc" class="required" value="<?php echo $r->UPC; ?>" class="required" /></td>
				</tr>
				<tr>
					<td>Reman. UPC</td>
					<td><input type="text" name="reman_upc" value="<?php echo $r->reman_upc; ?>" class="required" /></td>
				</tr>
				<tr>
					<td>Category</td>
					<td><?php 
						$current_category = $r->prod_cat;
						include "dd_category.php"; 
						?>
					</td>
				</tr>
				<tr>
					<td>Sub. Category</td>
					<td><?php
						$current_subCat = $r->prod_sub_cat;
						include "dd_subcategory.php";
						?>
					</td>
				</tr>
				<tr>
					<td>Out Box Dimension</td>
					<td><?php
						$dim = explode(",", $r->pack_dim);
						$width = $dim[0];
						$length = $dim[1];
						$height = $dim[2];
						?>
					    <input type="text" name="length" class="required" value="<?php echo $length; ?>" style="width: 50px; text-align: center;"> Length x 
					    <input type="text" name="width" class="required" value="<?php echo $width; ?>" style="width: 50px; text-align: center;"> Width x 
					    <input type="text" name="height" class="required" value="<?php echo $height; ?>" style="width: 50px; text-align: center;"> Height</td>
				</tr>
				<tr>
					<td>Reman. Package Weight</td>
					<td><input type="text" name="weight" class="required" value="<?php echo $r->pack_weight; ?>" style="width: 50px; text-align: center;" > Lbs.</td>
				</tr>
				<tr>
					<td>SFID</td>
					<td><input type="text" name="sfid" value="<?php echo $r->SFID; ?>" ></td>
				</tr>
				<tr>
					<td>Reman. SFID</td>
					<td><input type="text" name="reman_sfid" value="<?php echo $r->SFID_REMAN; ?>" ></td>
				</tr>
				<tr>
					<td>Reman. Base SFID</td>
					<td><input type="text" name="rmb_sfid" value="<?php echo $r->SFID_RMB; ?>" ></td>
				</tr>
			</table>

			</form>
			<a href="javascript:void(0);" class="btn btn-primary" id="btnItemDetailSave">Save</a>
			<a href="javascript:void(0);" class="btn btn-default" data-dismiss="modal">Cancel</a>
		</div>
		</div>
		<script>
		$("#btnItemDetailSave").click(function(){
			if(validation("itemDetailForm")){
				$.ajax({
					url: "ItemDetailUpdate.php",
					type: "POST",
					data: {"d":$("#itemDetailForm").serialize()},
					success: function(r){
						var d = JSON.parse(r);

						alert(d.msg);

						if(d.status){
							window.location.reload();
						}
					}
				})
			}				
		})		
		</script>

		<?php
		break;
		*/

	case "itemDetails":
		print "<script src='js/validationKF.js'></script>";

		$SKU = $_POST['SKU'];
		$sql = "SELECT 
				i.*,
				c.CategoryName,
				sc.SubCategoryName,
				u.id_num as 'ImageID'
				FROM `npi`.`items` i
				JOIN `npi`.`categories` c on i.prod_cat = c.CategoryID
				JOIN `npi`.`subcategories` sc on i.prod_sub_cat = sc.SubCategoryID
				JOIN `breville`.`upcreference` u on i.Modelnumber = u.ModelNumber
				WHERE i.ModelNumber = '$SKU';";

		#print $sql;

		$res = $dbo->run_query($sql);
		$r = $res[0];

		?>
		
		<div style="min-height: 600px;">
		<div class="col-md-2">
			<div>
			<?php
			/*
			if(file_exists("img/skus/$SKU.jpg"))
				echo "<img src='img/skus/$SKU.jpg' style='width: 100px;'>";
			else
				echo "<img src='img/skus/NA.jpg' style='width: 100px;'>";
			*/
			if(file_exists("../breville/images/parts/".$r->ImageID."-0.jpg"))
				echo "<img src='../breville/images/parts/".$r->ImageID."-0.jpg' style='width: 100px;'>";
			else
				echo "<img src='img/skus/NA.jpg' style='width: 100px;'>";
			?>
			</div>
		</div>
		<div class="col-md-10">
			<form id="itemDetailForm">
			<input type="hidden" name="sku" value="<?php echo $SKU; ?>" >

			<table class="table table-bordered table-striped" style="border-radius: 5px;">
				<tr>
					<td style="width: 200px;">Description</td>
					<td><p><?php echo $r->Description; ?></p></td>
				</tr>
				<tr>
					<td>Reman. Status</td>
					<td><?php echo ($r->reman_status == 0) ? "<span style='color: red;'>No</span>" : "<span style='color: green;'>Yes</span>"; ?></td>
				</tr>
				<tr>
					<td>Original UPC</td>
					<td><?php echo $r->UPC; ?></td>
				</tr>
				<tr>
					<td>Reman. UPC</td>
					<td><?php echo $r->reman_upc; ?></td>
				</tr>
				<tr>
					<td>Category</td>
					<td><?php echo $r->CategoryName; ?></td>
				</tr>
				<tr>
					<td>Sub. Category</td>
					<td><?php echo $r->SubCategoryName; ?></td>
				</tr>
				<tr>
					<td>Out Box Dimension</td>
					<td><?php
						$dim = explode(",", $r->pack_dim);
						$width = $dim[0];
						$length = $dim[1];
						$height = $dim[2];
						
						if(!empty($dim[0])){
							echo $length . " x " . $width . " x " . $height . " (Length x Width x Height)";
						}
						else{
							echo "NA";
						}
						?>
					</td>
				</tr>
				<tr>
					<td>Reman. Package Weight</td>
					<td><span style="font-weight:bold;"><?php echo $r->pack_weight; ?></span> Lbs.</td>
				</tr>
				<tr>
					<td>SFID</td>
					<td><?php echo $r->SFID; ?></td>
				</tr>
				<tr>
					<td>Reman. SFID</td>
					<td><?php echo (!empty($r->SFID_REMAN)) ? $r->SFID_REMAN : "NA"; ?></td>
				</tr>
				<tr>
					<td>Reman. Base SFID</td>
					<td><?php echo (!empty($r->SFID_RMB)) ? $r->SFID_RMB : "NA"; ?></td>
				</tr>
			</table>

			<h4 class="alert alert-info">Please use the original item master to update item information.</h4>
			
			</form>
		</div>
		</div>
		<?php
		break;

	case "reviseTransaction":
		print "<script src='js/validationKF.js'></script>";

		$TransId = $_POST['TransId'];

		$sql = "SELECT *
				FROM
				`npi`.`logs`
				WHERE LogID = $TransId;";

		$r = $dbo->run_query($sql);

		if(count($r) > 0){
			?>
			<div id="mainForm" class="form-inline">

				<h4>Model: <?php echo $r[0]->SKU; ?></h4>
				<br/>
				<b>Status: </b><br/>

				<?php 
				$current_status = $r[0]->StatusID;

				include "dd_status.php"; 
				?>
				<br/>
				<br/>
				<b>File uploaded:<b>
				<br/>
				<?php echo ($r[0]->IsFile) ? "<a href='files/".$r[0]->FileLocation."'>".$r[0]->FileName."</a>" : "None"; ?>
				<br/>
				<br/>
				<b>Comment: </b><br/>
				<textarea name="comment" id="comment" style="width: 600px; height: 100px; border-radius:5px;" class="required"><?php echo $r[0]->Comments; ?></textarea>
				<br/>
				<br/>
				<button class="btn btn-primary" id="btnTransUpdateSave">Update</button>
				<button class="btn btn-default" data-dismiss="modal">Cancel</button>

			</div>

			<script>
			$("#btnTransUpdateSave").click(function(){
				if(validation("mainForm")){
					$.ajax({
						url: "AjaxFunction.php",
						type: "POST",
						data: {"function":"reviseTransactionSave","TransId":<?php echo $TransId; ?>,"Status":$("#status").val(),"comment":$("#comment").val()},
						success: function(r){
							var d = JSON.parse(r);

							alert(d.msg);
						}
					})
				}
			})
			<?php
		}
		break;

	case "reviseTransactionSave":
		$TransId = $_POST['TransId'];
		$status = $_POST['Status'];
		$comments = (!empty($_POST['comment'])) ? "'".addslashes(trim($_POST['comment']))."'" : "NULL";

		$sql = "UPDATE
				`npi`.`logs`
				SET StatusID = $status,
				Comments = $comments
				WHERE LogID = $TransId;";

		if($dbo->run_query($sql)){
			$d = array(	"status"	=> true,
						"msg"		=> "Update successfully");
		}
		else{
			$d = array( "status"	=> false,
						"msg"		=> "Error! Update failed.");
		}

		print json_encode($d);
		break;
}

?>
		