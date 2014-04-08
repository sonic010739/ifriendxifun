<?
	session_start();
	include "smarty.lib.php";
	
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}	
	
	if ($_GET["act"] == "give" && $_SESSION["authority"] == "Admin")
	{
		//---------------------------------------
		// 新增給予的優惠卷至資料庫中
		//---------------------------------------
		
		$uid = $_POST["uid"];
		$reason = $_POST["reason"];
		$give_id = $_SESSION["uid"];
		
		$linkmysql->init();		
		$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
		$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
		$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
		$sql .= "'$reason', NOW() , NULL , NULL ); ";		
		$linkmysql->query($sql);
		$linkmysql->close_mysql();
		
		$tool->ShowMsgPage("優惠卷給予完成");		
	}
	else if ($_GET["act"] == "search" && $_SESSION["authority"] == "Admin")
	{
		//---------------------------------------
		// 優惠卷資料篩選
		//---------------------------------------
		
		$owner = $_POST["owner"];
		$giver = $_POST["giver"];
		$status = $_POST["status"];
		
		$url = sprintf("index.php?act=coupon&sel=list&type=search&owner=%s&giver=%s&status=%s", $owner, $giver, $status);
		
		$tool->URL($url);
	}
	else if ($_GET["act"] == "del" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 刪除優惠卷資料
		//----------------------------------------
		
		$id = $_GET["id"];
		
		$linkmysql->init();
		
		$sql  = "SELECT * ";
		$sql .= "FROM `coupon`";		
		$sql .= "WHERE `coupon_id` = '$id';";
		$linkmysql->query($sql);
		
		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$sql = sprintf("DELETE FROM `coupon` WHERE `coupon_id` = '%d' LIMIT 1;", $id);
			$linkmysql->query($sql);
			
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("所選擇的優惠卷已刪除", "回到優惠卷資料管理 ", "index.php?act=coupon&sel=list");
		}	
		
		$linkmysql->close_mysql();
		$tool->ShowMsgPage("找不到所選擇的優惠卷資料", "回到優惠卷資料管理 ", "index.php?act=coupon&sel=list");
	}
	else
	{
		$tool->ShowMsgPage("錯誤的操作");		
	}
?>