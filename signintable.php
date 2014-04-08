<?
	session_start();
	include "smarty.lib.php";
	
	//----------------------------------------
	// 活動簽到表
	//----------------------------------------

	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	$aid = $_GET["aid"];
	$uid = $_SESSION["uid"];
	$type = $_GET["type"];

	if ($type == 0)
	{
		$sex = "男";
	}
	else if ($type == 1)
	{
		$sex = "女";
	}
	else
	{
		$tool->ShowMsgPage("參數錯誤");
	}


	$linkmysql->init();

	$sql  = "SELECT `a`.*, `u`.`username`, `u`.`realname`, `p`.`placename`, `p`.`placeaddress`, `t`.`tname` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
	$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
	$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
	$sql .= "WHERE `a`.`aid` = '$aid'";
	$linkmysql->query($sql);

	if ($actdata = mysql_fetch_array($linkmysql->listmysql))
	{
		if (($_SESSION["authority"] != "EO" || $_SESSION["uid"] != $actdata["ownerid"]) && $_SESSION["authority"] != "Admin")
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("權限不足");
		}

		if ($actdata["status"] != "PROCEED")
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("必須是進行活動的狀態才可以列印簽到表", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$time = explode(":", $actdata["act_time"]);
		$actdata["act_time"] = sprintf("%02d:%02d", intval($time[0]), intval($time[1]));

		$actdata["status"] = $tool->ShowActStatus($actdata["status"]);
	}
	else
	{
		$linkmysql->close_mysql();
		$tool->ShowMsgPage("找不到這個活動");
	}


	// 存放所有報名的會員，不分狀態
	$joinmember = array();

	$girl_count = 1;
	$boy_count = 1;

	// 有報名活動的會員
	$sql  = "SELECT `aj`.`charge_type`, `aj`.`join_status`, `aj`.`no`, `u`.* ";
	$sql .= "FROM `activitiejoin` aj ";
	$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
	$sql .= "WHERE `aj`.`aid` = '$aid' AND `u`.`sex` = '$sex'";
	$sql .= "ORDER BY `aj`.`join_status` ASC , `u`.`sex` ASC, `aj`.`serial` ASC";
	$linkmysql->query($sql);

	while ($joindata = mysql_fetch_array($linkmysql->listmysql))
	{
		if ($joindata["charge_type"] == "iBon")
		{
			$joindata["charge_type"] = "iBon";
		}
		else if ($joindata["charge_type"] == "coupon")
		{
			$joindata["charge_type"] = "優惠卷";
		}

		if ($joindata["no"] != '')
		{
			if ($joindata["sex"] == '男')
			{
				$joindata["sex_count"] = sprintf("%d 號男生", $joindata["no"]);
			}
			else if ($joindata["sex"] == '女')
			{
				$joindata["sex_count"] = sprintf("%d 號女生", $joindata["no"] - $actdata['males']);
			}
		}

		if ($joindata["join_status"] == "join")
		{
			$joindata["join_status"] = "<font color=\"green\">已報名</font>";
		}
		else if ($joindata["join_status"] == "cancel")
		{
			$joindata["join_status"] = "<font color=\"blue\">已取消</font>";
		}
		else if ($joindata["join_status"] == "EO_cancel")
		{
			$joindata["join_status"] = "<font color=\"red\">EO取消</font>";
		}

		array_push( $joinmember, $joindata);
	}

	$linkmysql->close_mysql();

	$tpl->assign("actdata", $actdata);
	$tpl->assign("joinmember", $joinmember);
	$tpl->display("signintable.html");
?>
