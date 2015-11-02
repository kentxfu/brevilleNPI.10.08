<?php error_reporting(0);

include_once "$_SERVER[DOCUMENT_ROOT]/auth.php";
include_once "$_SERVER[DOCUMENT_ROOT]/PRC-Data/includes/adminConnect.php";
include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Breville NPI</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" >
	<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
	
	<script src="js/jquery-2.1.0.min.js"></script>


	<!--<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>-->
	<!--<script src="tableSorter/jquery-latest.js"></script>-->
	
	<!-- tableSorter Libraries -->
	<link rel="stylesheet" href="tableSorter/themes/blue/style.css">	
	<link rel="stylesheet" href="tableSorter/addons/pager/jquery.tablesorter.pager.css">	
	<script type="text/javascript" src="tableSorter/jquery.tablesorter.js"></script> 
	<script type="text/javascript" src="tableSorter/addons/pager/jquery.tablesorter.pager.js"></script>
	<script type="text/javascript" src="tableSorter/jquery.tablesorter.widgets.js"></script>

	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

	<!--- main css -->
	<link rel="stylesheet" href="css/main.css" />

	<!-- To resize based on screen size -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- sidebar -->
	<link href="css/simple-sidebar.css" rel="stylesheet">	 
	
	<!-- for js Tree -->
	<link rel="stylesheet" href="jsTree/dist/themes/default/style.min.css" />
	<script src="jsTree/dist/jstree.min.js"></script>

<script>
$(function(){

	$(".tablesorter")
	.tablesorter({

		theme : 'blue',
		//cssInfoBlock : "avoid-sort", 
		widthFixed : true,
		showProcessing: true,

		headerTemplate : '{content} {icon}', // Add icon for jui theme; new in v2.7!

		widgets: [ 'uitheme', 'stickyHeaders', 'filter' ],

		widgetOptions: {
		
			stickyHeaders_offset : 0,
			// extra css class name applied to the sticky header row (tr) - changed in v2.11
			stickyHeaders : '',

			// adding zebra striping, using content and default styles - the ui css removes the background from default
			// even and odd class names included for this demo to allow switching themes
			zebra   : ["ui-widget-content even", "ui-state-default odd"],

			// use uitheme widget to apply defauly jquery ui (jui) class names
			// see the uitheme demo for more details on how to change the class names
			uitheme : 'jui'

		}

	})
	//.tablesorterPager({container: $(".pager")});

});
</script>

</head>
<?php
$dbo = new dbo();
$dbo->ny_connect();
?>

<body id="MainBody" style="background-color:#F9F8F6;">

	<div class="loader"></div>

    <div id="wrapper" class="toggled" >

        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="noprint" style="background-color:black;">		

			<div class="noprint form-inline sidebar-top">
			<!-- BUTTONS: Expand and help -->

				<?php include "nav.php"; ?>
				
			</div>           

            <div style="color:white;"> <!--class =  sidebar- nav (?)-->

				<div class="text-center">
					<?php 
					/*
					if($_SESSION['SESS_MEMBER_ID'] == 1 OR $_SESSION['SESS_MEMBER_ID'] == 1223 OR 
					   $_SESSION['SESS_MEMBER_ID'] == 1383 OR $_SESSION['SESS_MEMBER_ID'] == 111 OR
					   $_SESSION['SESS_MEMBER_ID'] == 1735){
						echo '<img src="img/point-animate2.gif" style="position: relative; width: 100%; margin-bottom: -22px;">';
					}
					else{
						echo '<img src="img/point3.png" style="position: relative; width: 200px; margin-bottom: -44px;">';
					}
					*/
					?>
						<!--
						<a class="btn btn-default btn-sm" href="#"> Summary View </a>	
						<a class="btn btn-default btn-sm" href="#"> Detailed View   </a> 
						<br><br>
						-->

					<h3 style="background-color: #000;
							   height: 60px;
							   line-height: 50px;
							   padding-top: 10px;
							   color: #fff;">
    				Choose Columns:</h3>
				</div>
			
				<div id="treeData" >
					<ul>

						<?php

						$sql = "SELECT 
								ParentLevelID,
								p.LevelDescription as 'ParentLevel',
								c.LevelDescription as 'ChildLevel'
								FROM npi.level_relations lr
								join npi.levels p on lr.ParentLevelID = p.LevelID
								join npi.levels c on lr.ChildLevelID = c.LevelID
								where ParentLevelID != 18
								GROUP BY ParentLevel
								ORDER BY Field(p.LevelID, 1,2,3,4,5,6);";

						$r = $dbo->run_query($sql);

						for($i = 0; $i < count($r); $i++){
							echo "
								<li data-jstree='{\"selected\":true,\"opened\":false}'>". $r[$i]->ParentLevel ."</li>";

							#echo "
							#		<ul>";

							$sql_child = "	SELECT 
											c.DisplayName as 'Name'
											FROM npi.level_relations lr
											join npi.levels p on lr.ParentLevelID = p.LevelID
											join npi.levels c on lr.ChildLevelID = c.LevelID
											where ParentLevelID = ". $r[$i]->ParentLevelID .";";

							$r_child = $dbo->run_query($sql_child);

							/*
							for($j = 0; $j < count($r_child); $j++){

								?>
										<li><?php echo $r_child[$j]->Name; ?></li>
								<?php
							}
							*/
							#	echo "
							#		</ul>
							#	</li>";
						}

						?>

					</ul>
				</div>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
         <div class="container-fluid">
         	<!-- <img src="img/Header.jpg" style="position: absolute; width: 95%;"> -->
            <div class="row">
                <div class="noprint">			
				<a href="#menu-toggle" class="btn btn-default" id="menu-toggle" title="More options"><span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span></a>		
				<!-- end of buttons -->		
				</div>

				<h2 style="text-align: center; font-weight: bold;">Product Status</h2>
			</div> <!-- end of row1 -->

		<br/>
		<br/>

		<hr>
		
		<!-- page data; table -->
		<div class="col-sm-12" style="padding: 0px;">
	
		<!--<h2 style="text-align:left; font-family:Helvetica, Arial;"> Breville NPI Status </h2>-->

		<!-- Loop getting values from DB -->
		<?php	

		$modelFilter = (isset($_GET['model']) && !empty($_GET['model'])) ? "and ModelName = '" . $_GET['model'] . "'" : "";
		$listFilter = (isset($_GET['list']) && !empty($_GET['list'])) ? "LIMIT " . $_GET['list'] : "";

		$sql = "SELECT
				SKU,
				MAX(CASE WHEN Level = 'Work Instruction' THEN ReLvlID END) 'WI_LvlID',
				MAX(CASE WHEN Level = 'Work Instruction' THEN Status END) 'WI',
				MAX(CASE WHEN Level = 'Work Instruction' THEN `CSS_Class` END) 'WIClass',
				MAX(CASE WHEN Level = 'ETL Certification' THEN ReLvlID END) 'ETL_LvlID',
				MAX(CASE WHEN Level = 'ETL Certification' THEN Status END) 'ETL',
				MAX(CASE WHEN Level = 'ETL Certification' THEN `CSS_Class` END) 'ETLClass',
				MAX(CASE WHEN Level = 'Art Work' THEN ReLvlID END) 'AW_LvlID',
				MAX(CASE WHEN Level = 'Art Work' THEN Status END) 'AW',
				MAX(CASE WHEN Level = 'Art Work' THEN `CSS_Class` END) 'AWClass',
				MAX(CASE WHEN Level = 'Packaging' THEN ReLvlID END) 'Packaging_LvlID',
				MAX(CASE WHEN Level = 'Packaging' THEN Status END) 'Packaging',
				MAX(CASE WHEN Level = 'Packaging' THEN `CSS_Class` END) 'PackagingClass',
				MAX(CASE WHEN Level = 'FAR' THEN ReLvlID END) 'FAR_LvlID',
				MAX(CASE WHEN Level = 'FAR' THEN Status END) 'FAR',
				MAX(CASE WHEN Level = 'FAR' THEN `CSS_Class` END) 'FARClass',
				MAX(CASE WHEN Level = 'TRACS' THEN ReLvlID END) 'TRACS_LvlID',
				MAX(CASE WHEN Level = 'TRACS' THEN Status END) 'TRACS',
				MAX(CASE WHEN Level = 'TRACS' THEN `CSS_Class` END) 'TRACSClass',
				IFNULL(
				(SELECT PPI FROM `breville`.`summary_report_temp`
				where SKU = x.SKU
				order by ID desc limit 1), 0) as 'PPI'
				FROM
				(
				SELECT 
				m.ModelName as 'SKU',
				m.MainID as 'ReLvlID',
				c.LevelDescription as 'Level',
				s.Description as 'Status',
				s.CSS_Class
				FROM npi.main m
				join npi.level_relations lr on m.RelationID = lr.LRID
	            join npi.levels p on lr.ParentLevelID = p.LevelID
				join npi.levels c on lr.ChildLevelID = c.LevelID
				join npi.status s on m.StatusID = s.StatusID
	            where lr.ParentLevelID = 18
	            $modelFilter
				) x
				GROUP BY SKU
				ORDER BY PPI DESC
				$listFilter
				;";

		$r = $dbo->run_query($sql);

		?>

		<!--
		<div id="pager" class="pager">
		  <form>
		    <img src="first.png" class="first"/>
		    <img src="prev.png" class="prev"/>
		    <span class="pagedisplay"></span>
		    <img src="next.png" class="next"/>
		    <img src="last.png" class="last"/>
		    <select class="pagesize">
		      <option value="10">10</option>
		      <option value="20">20</option>
		      <option value="30">30</option>
		      <option value="40">40</option>
		    </select>
		  </form>
		</div>
		-->

		<table id="myTable" class="tablesorter table table-striped table-bordered table-condensed hasFilters" role="grid">
			<thead id="theadId">
			<tr role="row" class="tablesorter-headerRow">
				<th>SKU</th>
				<th class="Work_Instruction">Work Instruction</th>
				<th class="ETL_Certification">ETL Certification</th>
				<th class="Art_Work">Art Work</th>
				<th class="Packaging">Packaging</th>
				<th class="FAR">FAR</th>
				<th class="TRACS">TRACS</th>
				<th class="PPI">PPI</th>
			</tr>
				
			</thead>
		
			<tbody id="tbodyId">
		

		<?php

		for($i = 0; $i < count($r); $i++){

			echo "
			<tr>
				<td class='neutralCell items' data-sku='". $r[$i]->SKU ."'><a href='javascript:void(0);'>". $r[$i]->SKU ."</a></td>
				<td class='".$r[$i]->WIClass." Work_Instruction'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='1' data-ReLvlID='". $r[$i]->WI_LvlID ."'>". $r[$i]->WI ."</a></td>
				<td class='".$r[$i]->ETLClass." ETL_Certification'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='2' data-ReLvlID='". $r[$i]->ETL_LvlID ."'>". $r[$i]->ETL ."</a></td>
				<td class='".$r[$i]->AWClass." Art_Work'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='3' data-ReLvlID='". $r[$i]->AW_LvlID ."'>". $r[$i]->AW ."</a></td>
				<td class='".$r[$i]->PackagingClass." Packaging'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='4' data-ReLvlID='". $r[$i]->Packaging_LvlID ."'>". $r[$i]->Packaging ."</a></td>
				<td class='".$r[$i]->FARClass." FAR'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='5' data-ReLvlID='". $r[$i]->FAR_LvlID ."'>". $r[$i]->FAR ."</a></td>
				<td class='".$r[$i]->TRACSClass." TRACS'><a href='javascript:void(0);' class='level3Detail' data-sku='". $r[$i]->SKU ."' data-levelID='6' data-ReLvlID='". $r[$i]->TRACS_LvlID ."'>". $r[$i]->TRACS ."</a></td>
				<td class='text-center neutralCell'>". $r[$i]->PPI ."</td>
			</tr>";	

		}
		?>
		
			</tbody>		
		</table>

		</div>
		<!-- end of col-lg-12; table -->
		
		<footer><hr>
		
		  <p style="text-align:left; font-family:Helvetica, Arial; "> © PRC Industries</p>
		  
		</footer>
		
		
                        
                    </div> <!-- end of  <div class="col-lg-12"> -->
                </div>
            </div>
		
		</div>     <!-- "container-fluid" -->
		</div> <!-- "page-content-wrapper" -->

	
    <!-- Menu Toggle Script -->
    <script>

	var list = [];
	$("#myTable th").each(function(){
		if($(this).text() != "SKU" && $(this).text() != "PPI"){
			list.push($(this).text());
		}
	})

	$("#menu-toggle").click(function(e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});
	
	$.jstree.defaults.core.themes.icons = false;
	$('#treeData').jstree({
		"plugins" : [ "checkbox" ]
	
	});

	$('#treeData').on('changed.jstree', function (e, data) {
	    var i, j, r = [];
	    for(i = 0, j = data.selected.length; i < j; i++) {
	      r.push(data.instance.get_node(data.selected[i]).text);
	    }

	 	for(var x = 0; x < list.length; x++){
	 		for(i = 0; i < j; i++){
	 			var class_name = list[x].split(' ').join('_');

	 			if(jQuery.inArray(list[x], r) !== -1){
	 				$("." + class_name).show();
	 				$(".filter").closest("td").eq(parseInt(x) + 1).show();
	 			}
	 			else{
	 				$("." + class_name).hide();
	 				$(".filter").closest("td").eq(parseInt(x) + 1).hide();
	 			}
	 		}
	 	}
	    
	}).jstree();


	$(".level3Detail").click(function(){
		$.ajax({
			url: "AjaxFunction.php",
			type: "post",
			data: {"function":"detailLvl3","SKU":$(this).attr("data-sku"),"LevelID":$(this).attr("data-levelID"),"ReLvlID":$(this).attr("data-ReLvlID")},
			success: function(r){
				$("#detailLvl3Body").html(r);
				$("#detailLvl3").modal("show");
			}
		})
		
	})

	$(".items").click(function(){
		var Title = $(this).attr("data-sku");

		$.ajax({
			url: "AjaxFunction.php",
			type: "post",
			data: {"function":"itemDetails","SKU":$(this).attr("data-sku")},
			success: function(r){
				$("#modalItemTitle").html(Title);
				$("#modalItemBody").html(r);
				$("#modalItem").modal("show");
			}
		})			
	})
    </script>

	<!-- LEGEND Modal -->
	<div class="modal fade" id="info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		
		  <div class="modal-header" style="background-color: #93CBF9; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">			  
			<button type="button" class="close close-round" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> </button>				
			<h4 class="modal-title" id="myModalLabel">
			<strong> Legend </strong>				</h4> 
		  </div>	<!-- end of header -->	  
		  
		  <div class="modal-body">					  
			<!--<img style="text-align:center;" src="img/Legend.jpg" alt="Mountain View"> </img>-->
			<table>
				<tr>
					<td class="notStarted" style="width: 75px; margin-bottom: 10px;"></td>
					<td style="padding-left: 10px;">No Action</td>
				</tr>
				<tr>
					<td class="pendingApproval"></td>
					<td style="padding-left: 10px;">Breville Action</td>
				</tr>
				<tr>
					<td class="pending"></td>
					<td style="padding-left: 10px;">PRC Action</td>
				</tr>
				<tr>
					<td class="completed"></td>
					<td style="padding-left: 10px;">Completed</td>
				</tr>
			</table>
		  </div><!-- end of modal Body -->
		  
		</div>
	  </div>
	</div>
	<!-- end of LEGEND modal -->

	<div class="modal fade" id="transactions" tabindex="-1" role="dialog" aria-labelledby="modalTrans">
	  <div class="modal-dialog" role="document" style="width: 800px;">
		<div class="modal-content" style="width: 800px;">
		
		  <div class="modal-header" style="background-color: #93CBF9; border-top-left-radius: 5px; border-top-right-radius: 5px;">
		  </div>
		  
		  <div class="modal-body" id="transactionsBody">
		  </div><!-- end of modal Body -->
		  
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		  </div>
		</div>
	  </div>
	</div>


	<!-- LVL 3 Detail -->
	<div class="modal fade" id="detailLvl3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
	  <div class="modal-dialog" role="document" style="width: 800px;">
		<div class="modal-content" style="width: 800px;">

		  <div class="modal-header" id="detailLvl3Header" style="background-color: #93CBF9; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
		  	<button type="button" class="close close-round" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> </button>
		  </div>
		  
		  <div class="modal-body" id="detailLvl3Body">
		  </div><!-- end of modal Body -->
		</div>
	  </div>
	</div>
	<!-- end LVL 3 Detail modal -->

	<div class="modal fade" id="trans-modal" data-modal-index="1" style="display: none;">
	  <div class="modal-dialog" style="width: 800px;">
	    <div class="modal-content" style="width: 800px;">
	      <div class="modal-header" style="background-color: #93CBF9; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
	        <button type="button" class="close close-round" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <h4 class="modal-title" id="transModelTitle">Transaction History</h4>
	      </div>
	      <div class="modal-body" id="transModalBody">
	        <p>One fine body&hellip;</p>
	             <button class="btn btn-default" data-toggle="modal" data-target="#test-modal-2">Launch Modal 2</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="modalStatusUpdate" data-modal-index="1" style="display: none;">
	  <div class="modal-dialog" style="width: 800px;">
	    <div class="modal-content" style="width: 800px;">
	      <div class="modal-header" style="background-color: #93CBF9; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
	        <button type="button" class="close close-round" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <h4 class="modal-title" id="modalStatusUpdateTitle">Status Update</h4>
	      </div>
	      <div class="modal-body" id="modalStatusUpdateBody"></div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="modalItem" data-modal-index="1" style="display: none;">
	  <div class="modal-dialog" style="width: 800px;">
	    <div class="modal-content" style="width: 800px;">
		  <div class="modal-header" id="modalItemHeader" style="background-color: #93CBF9; color:#fff; font-weight: bold; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
		  	<button type="button" class="close close-round" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> </button>
		  	<h4 class="modal-title" id="modalItemTitle">Item Details</h4>

		  </div>

	      <div class="modal-body" id="modalItemBody"></div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="modalTransUpdate" data-modal-index="1" style="display: none;">
	  <div class="modal-dialog" style="width: 800px;">
	    <div class="modal-content" style="width: 800px;">
		  <div class="modal-header" id="modalTransUpdateHeader" style="background-color: #93CBF9; font-weight: bold; height: 50px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
		  	<button type="button" class="close close-round" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> </button>
		  	<h4 class="modal-title" id="modalTransUpdateTitle">Transaction Update</h4>

		  </div>

	      <div class="modal-body" id="modalTransUpdateBody"></div>
	    </div>
	  </div>
	</div>

	<!--
	<div class="modal fade" id="test-modal-2" data-modal-index="2">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <h4 class="modal-title">Modal title 2</h4>
	      </div>
	      <div class="modal-body">
	        <p>One fine body&hellip;</p>
	        <button class="btn btn-default" data-toggle="modal" data-target="#test-modal-3">Launch Modal 3</button>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="button" class="btn btn-primary">Save changes</button>
	      </div>
	    </div>
	  </div>
	</div>

    <div class="modal fade" id="test-modal-3">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <h4 class="modal-title">Modal title 3</h4>
	      </div>
	      <div class="modal-body">
	        <p>One fine body&hellip;</p>
	        
	        <button class="btn btn-default" data-toggle="modal" data-target="#test-modal-4">Launch Modal 4</button>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="button" class="btn btn-primary">Save changes</button>
	      </div>
	    </div>
	  </div>
	</div>


    <div class="modal fade" id="test-modal-4">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <h4 class="modal-title">Modal title 4</h4>
	      </div>
	      <div class="modal-body">
	        <p>One fine body&hellip;</p>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="button" class="btn btn-primary">Save changes</button>
	      </div>
	    </div>
	  </div>
	</div>
	-->

</body>

</html>
