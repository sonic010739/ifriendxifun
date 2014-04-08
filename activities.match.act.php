<?
	session_start();
	include "smarty.lib.php";

	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	if ($_GET["act"] == "fillout")
	{
		$aid 	= $_POST['aid'];
		$uid 	= $_POST['uid'];
		$email 	= $_POST['email'];
		$msn 	= $_POST['msn'];
		$tel 	= $_POST['tel'];
		
		$str = "";
		if (is_array($email))
		{
			foreach($email as $e)
			{
				$str .= $e . ", ";
			}
		}
		
		$email = $str;
		///////////////////////////////////////////
		
		$str = "";		
		if (is_array($msn))
		{
			foreach($msn as $m)
			{
				$str .= $m . ", ";
			}
		}
		
		$msn = $str;
		///////////////////////////////////////////
		
		$str = "";		
		if (is_array($tel))
		{
			foreach($tel as $t)
			{
				$str .= $t . ", ";
			}
		}
		
		$tel = $str;
		
		$linkmysql->init();
		
		$sql  = "UPDATE `activitiematch` SET ";
		$sql .= "`email` = '$email', ";
		$sql .= "`msn` = '$msn', ";
		$sql .= "`tel` = '$tel' ";
		$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1;";
		$linkmysql->query($sql);
		
		$linkmysql->close_mysql();
		
		$tool->ShowMsgPage("資料選擇完成", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
	}
	else
	{
		$tool->ShowMsgPage("活動配對處理程式收到無法辨識的指令");
	}
?>