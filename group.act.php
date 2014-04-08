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
		// 新增活動族群資料
		//----------------------------------------
		
		$gname = $_POST['gname'];
		$linkmysql->init();
		
		$sql = sprintf("INSERT INTO `group` ( `gid`, `gname`) VALUES ( '' , '%s');", $gname);
		$linkmysql->query( $sql );

		$tool->URL("index.php?act=group&sel=list");
	}
	else if ($_GET["act"] == "modify" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 修改活動族群資料
		//----------------------------------------
		
		$gid = $_POST['gid'];
		$gname = $_POST['gname'];
		
		$linkmysql->init();
		
		$sql  = "UPDATE `group` SET ";
		$sql .= "`gname`='$gname' ";
		$sql .= "WHERE `group`.`gid` = '$gid'";		
		
		$linkmysql->query( $sql );
		
		$tool->URL("index.php?act=group&sel=list");
		
	}
	else if ($_GET["act"] == "del" && $_SESSION["authority"] == "Admin")
	{	
		//----------------------------------------
		// 刪除活動族群資料
		//----------------------------------------
		
		$gid = $_GET['gid'];
		
		$linkmysql->init();
		$sql = sprintf("DELETE FROM `group` WHERE `group`.`gid` = '%d' LIMIT 1;", $gid);
		$linkmysql->query( $sql );
		
		$url = sprintf("index.php?act=group&sel=list");
		$tool->URL($url);
	}
	else
	{
		$tool->ShowMsgPage("錯誤的操作");
	}

?>