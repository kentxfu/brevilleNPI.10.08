
<?php

switch($_SESSION['LOGIN_GROUP']){
	case "Breville":
	default:
		$logo = "Breville.jpg";
		break;
	/*default:
		$logo = "PRC.png";
		break;
		*/
}

// get permissions
include_once "$_SERVER[DOCUMENT_ROOT]/npi/npi.class.php";
$npi = new npi();
$permissions = $npi->getUserPermissions($_SESSION['SESS_MEMBER_ID']);

/*
print "<pre>";
print_r ($permissions);
print "</pre>";
*/

?>

<div id="logo"></div>

<div id="mynavbar">
	<ul class="nav navbar-nav navbar-left">
		<li class="dropdown">
			<a href="javascript:void(0);" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
			<?php echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME']; ?> <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<?php
				if($_SESSION['SESS_MEMBER_ID'] == 1){
				?>
				<li><a href="javascript:void(0);">Email List</a></li>
				<?php
				}
				?>
				<li role="separator" class="divider"></li>
				<li><a href="../logout">Sign out</a></li>
			</ul>
		</li>	
	</ul>		
	<ul class="nav navbar-nav navbar-right" style="margin: 0 !important">
		<li>
			<button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#info" aria-label="Left Align">
				<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> Help 
			</button>
		</li>	
	</ul>
</div>

<script>
$("#logo").css("background-image","url(img/logos/<?php echo $logo; ?>)");
</script>