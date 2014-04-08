<?
	//---------------------------------------
	// 活動系統 - EO
	//---------------------------------------
	
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	if ($_SESSION["authority"] == "User")
	{
		$tool->ShowMsgPage("權限不足");
	}
		
	if (!isset($_GET["sel"]))
	{
		$_GET["sel"] = "list";
	}

	if ($_GET["sel"] == "list")
	{
		$uid = $_SESSION["uid"];
		
		$linkmysql->init();
	
		// Open			開放報名
		// ApplyCancel  待取消
		// Proceed		待進行
		// Close		過去舉辦			
		// Cancel		已取消
					
		$type = !isset($_GET['type']) ? "Open" : $_GET['type'];
	
		if ($type == "Open")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND (`status` = 'OPEN' AND TO_DAYS(`join_deadline`) >= TO_DAYS(NOW()))";
		}
		else if ($type == "ApplyCancel")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND `status` = 'APPLY_CANCEL'";
		}
		else if ($type == "Proceed")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND ((`status` = 'OPEN' AND TO_DAYS(`join_deadline`) < TO_DAYS(NOW())) OR `status` = 'PROCEED')";
		}
		else if ($type == "Close")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND `status` = 'CLOSE'";
		}
		else if ($type == "Cancel")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND `status` = 'CANCEL'";
		}
				
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 分頁設定
		$itemperpage = 15;		
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];		
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );    	
		
		if ($type == "Open")
		{			
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'OPEN' AND TO_DAYS(`a`.`join_deadline`) >= TO_DAYS(NOW())) ";
			$sql .= "LIMIT $head , $itemperpage";
			
			$list_title = "開放報名的活動";
			$url = "./index.php?act=activitieEO&amp;sel=list&amp;type=Open";
			
			$options  = "<b>開放報名的活動</b> |\n";			
			$options .= "<a href=\"./index.php?act=activities&amp;sel=add\">新增活動</a> |\n";
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=ApplyCancel\">待取消的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Proceed\">待進行的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Close\">過去舉辦的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Cancel\">已取消的活動</a> \n";			
		}
		else if ($type == "ApplyCancel")
		{			
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND `a`.`status` = 'APPLY_CANCEL' ";
			$sql .= "LIMIT $head , $itemperpage";
			
			$list_title = "待取消的活動";
			$url = "./index.php?act=activitieEO&amp;sel=list&amp;type=ApplyCancel";
			
			$options  = "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Open\">開放報名的活動</a> |\n";			
			$options .= "<a href=\"./index.php?act=activities&amp;sel=add\">新增活動</a> |\n";
			$options .= "<b>待取消的活動</b> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Proceed\">待進行的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Close\">過去舉辦的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Cancel\">已取消的活動</a> \n";			
		}
		else if ($type == "Proceed")
		{			
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND ((`a`.`status` = 'OPEN' AND TO_DAYS(`a`.`join_deadline`) < TO_DAYS(NOW())) OR `a`.`status` = 'PROCEED') ";
			$sql .= "LIMIT $head , $itemperpage";
			
			$list_title = "開放報名的活動";
			$url = "./index.php?act=activitieEO&amp;sel=list&amp;type=Proceed";
			
			$options  = "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Open\">開放報名的活動</a> |\n";			
			$options .= "<a href=\"./index.php?act=activities&amp;sel=add\">新增活動</a> |\n";
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=ApplyCancel\">待取消的活動</a> |\n";	
			$options .= "<b>待進行的活動</b> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Close\">過去舉辦的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Cancel\">已取消的活動</a> \n";			
		}
		else if ($type == "Close")
		{			
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND `a`.`status` = 'CLOSE' ";
			$sql .= "LIMIT $head , $itemperpage";
			
			$list_title = "開放報名的活動";
			$url = "./index.php?act=activitieEO&amp;sel=list&amp;type=Close";
			
			$options  = "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Open\">開放報名的活動</a> |\n";			
			$options .= "<a href=\"./index.php?act=activities&amp;sel=add\">新增活動</a> |\n";
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=ApplyCancel\">待取消的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Proceed\">待進行的活動</a> |\n";	
			$options .= "<b>過去舉辦的活動</b> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Cancel\">已取消的活動</a> \n";			
		}
		else if ($type == "Cancel")
		{			
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND `a`.`status` = 'CANCEL' ";
			$sql .= "LIMIT $head , $itemperpage";
			
			$list_title = "開放報名的活動";
			$url = "./index.php?act=activitieEO&amp;sel=list&amp;type=Cancel";
			
			$options  = "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Open\">開放報名的活動</a> |\n";			
			$options .= "<a href=\"./index.php?act=activities&amp;sel=add\">新增活動</a> |\n";
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=ApplyCancel\">待取消的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Proceed\">待進行的活動</a> |\n";	
			$options .= "<a href=\"./index.php?act=activitieEO&amp;sel=list&amp;type=Close\">過去舉辦的活動</a> |\n";	
			$options .= "<b>已取消的活動</b> \n";			
		}
		
		$linkmysql->query($sql);	

		$activitielist = array();
		
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if (preg_match("/(.+) (.+)/", $data["tname"], $matches)) 
			{
				$data["tname"]  = $matches[1];
				$data["tname"] .= "<br />";
				$data["tname"] .= $matches[2];
			}
			
			$data["name"] = $tool->UTF8_CuttingStr($data["name"], 48);
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);
			
			$data["status"] = $tool->ShowActStatus($data["status"]);	
		
			array_push($activitielist, $data);
		}
		
		$linkmysql->close_mysql();
		
		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("options", $options);
		$tpl->assign("list_title", $list_title);
		$tpl->assign("list_count", count($activitielist));
		$tpl->assign("activitielist", $activitielist);
		$tpl->assign("mainpage", "activities/activities.list.html");			
	}
?>