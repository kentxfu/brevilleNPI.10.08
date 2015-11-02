<?php error_reporting(0);

class npi {

	private $link  	 = null;

	function __construct(){
		include_once "$_SERVER[DOCUMENT_ROOT]/classes/dbo.class.php";
		$dbo = new dbo();
		$dbo->ny_connect();
		$this->link = $dbo;
	}

	function getUserPermissions($userId){
		$sql = "SELECT * FROM `npi`.`permission_users`
				WHERE userId = $userId;";

		$r = $this->link->run_query($sql);

		if(count($r) > 0)
			return $r;
		else
			return null;
	}

}

?>