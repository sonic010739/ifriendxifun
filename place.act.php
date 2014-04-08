<?
	session_start();

	include "smarty.lib.php";

	if ($_GET["act"] == "add" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 新增活動場地資料
		//----------------------------------------
		
		$name = $_POST['name'];
		$tel = $_POST['tel'];
		$address = $_POST['address'];
		$city = $_POST['city'];
		$link = $_POST['link'];
		$decription = $_POST['decription'];
		$lat = $_POST['lat'];
		$lng = $_POST['lng'];

		$linkmysql->init();
		
		$sql  = "INSERT INTO `place` ( `pid`, `placename`, `placetel`, `placeaddress`, ";
		$sql .= "`placecity`, `placelink`, `decription`, `lat`, `lng`, `status`, `updatetime`) ";
		$sql .= "VALUES ( NULL, '$name', '$tel', '$address', ";
		$sql .= "'$city', '$link', '$decription', '%$lat', '$lng', 'Open', NOW())";
		$linkmysql->query($sql);

		$tool->URL("index.php?act=place&sel=list");
	}
	else if ($_GET["act"] == "modify" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 修改活動場地
		//----------------------------------------
		
		$pid = $_POST['pid'];
		$name = $_POST['name'];
		$tel = $_POST['tel'];
		$address = $_POST['address'];
		$city = $_POST['city'];
		$link = $_POST['link'];
		$decription = $_POST['decription'];
		$lat = $_POST['lat'];
		$lng = $_POST['lng'];
		
		$linkmysql->init();
		$sql  = "UPDATE `place` SET ";
		$sql .= "`placename` = '$name', `placetel` = '$tel', `placeaddress` = '$address', `placecity` = '$city', ";
		$sql .= "`placelink` = '$link', `decription` = '$decription', `lat` = '$lat', `lng` = '$lng', `updatetime` = NOW() ";
		$sql .= "WHERE `place`.`pid`='$pid'";		
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=place&sel=detail&pid=%d", $pid);
		$tool->URL($url);
	}
	else if ($_GET["act"] == "del" && $_SESSION["authority"] == "Admin")
	{	
		//----------------------------------------
		// 刪除活動場地資料
		//----------------------------------------
		
		$pid = $_GET['pid'];
		
		$linkmysql->init();
		$sql = sprintf("DELETE FROM `place` WHERE `place`.`pid` = %d LIMIT 1;", $pid);
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=place&sel=list");
		$tool->URL($url);
	}
	else if ($_GET["act"] == "Open" && $_SESSION["authority"] == "Admin")
	{	
		//----------------------------------------
		// 設為可使用
		//----------------------------------------
		
		$pid = $_GET['pid'];
		
		$linkmysql->init();
		$sql = "UPDATE `place` SET ";
		$sql .= "`status`='Open', `updatetime` = NOW() ";
		$sql .= "WHERE `place`.`pid`='$pid'";	
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=place&sel=modify&pid=%d", $pid);
		$tool->URL($url);
	}
	else if ($_GET["act"] == "Close" && $_SESSION["authority"] == "Admin")
	{	
		//----------------------------------------
		// 設為無法使用
		//----------------------------------------
		
		$pid = $_GET['pid'];
		
		$linkmysql->init();
		$sql = "UPDATE `place` SET ";
		$sql .= "`status`='Close', `updatetime` = NOW() ";
		$sql .= "WHERE `place`.`pid`='$pid'";	
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=place&sel=modify&pid=%d", $pid);
		$tool->URL($url);
	}
	else if ($_GET["act"] == "search" && $_SESSION["authority"] == "Admin")
	{	
		$city = urlencode($_POST['city']);
		$url = sprintf("index.php?act=place&sel=list&filter=%s", $city);
		$tool->URL($url);
	}
	else
	{
		$tool->ShowMsgPage("錯誤的操作");
	}

?>