<?
	session_start();
	
	include "smarty.lib.php";
	
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	if ($_GET["act"] == "add" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 新增活動主題
		//----------------------------------------
		
		$tname = $_POST['tname'];
		$linkmysql->init();
		
		$sql = sprintf("INSERT INTO `topic` ( `tid`, `tname`) VALUES ( '' , '%s');", $tname);
		$linkmysql->query( $sql );

		$tool->URL("index.php?act=topic&sel=list");
	}
	else if ($_GET["act"] == "modify" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 修改活動主題
		//----------------------------------------
		
		$tid = $_POST['tid'];
		$tname = $_POST['tname'];
		
		$linkmysql->init();
		
		$sql  = "UPDATE `topic` SET ";
		$sql .= "`tname`='$tname' ";
		$sql .= "WHERE `topic`.`tid` = '$tid'";		
		
		$linkmysql->query( $sql );
		
		$tool->URL("index.php?act=topic&sel=list");
		
	}
	else if ($_GET["act"] == "del" && $_SESSION["authority"] == "Admin")
	{	
		//----------------------------------------
		// 刪除活動主題
		//----------------------------------------
		
		$tid = $_GET['tid'];
		
		$linkmysql->init();
		$sql = sprintf("DELETE FROM `topic` WHERE `topic`.`tid` = '%d' LIMIT 1;", $tid);
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=topic&sel=list");
		$tool->URL($url);
	}
	else
	{
		$tool->ShowMsgPage("錯誤的操作");
	}

?>