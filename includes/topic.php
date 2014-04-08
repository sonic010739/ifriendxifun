<?
	if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "list")
	{
		//-------------------------------------------------
		// 檢視主題列表，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$linkmysql->init();
		
		$sql = "SELECT COUNT(*) FROM `topic`";
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 分頁設定
		$itemperpage = 25;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];		
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );    	
			
		$sql = "SELECT `tid`, `tname` FROM `topic` ORDER BY `tid` ASC LIMIT $head , $itemperpage";
		$linkmysql->query($sql);
		
		$topiclist = array();
		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$data[2] = sprintf("<a href=\"./index.php?act=topic&amp;sel=modify&amp;tid=%s\">%s</a>", $data[0], "修改");
			$data[3] = sprintf("<a href=\"./topic.act.php?act=del&amp;tid=%s\" onClick='return confirm(\"確定要刪除\")'>%s</a>", $data[0], "刪除");			
			
			array_push($topiclist, $data);
		}		
		
		// 頁碼
		$page = $tool->showpages("index.php?act=topic&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page("index.php?act=topic&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("topiclist", $topiclist);
		$tpl->assign("mainpage","topic/topic.html");
		
		$linkmysql->close_mysql();
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "modify")
	{
		//-------------------------------------------------
		// 修改主題資料，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$tid = $_GET["tid"];
		
		$linkmysql->init();
		$sql = "SELECT * FROM `topic` WHERE `tid` = '$tid'";		
		$linkmysql->query($sql);
			
		if ($data = mysql_fetch_row($linkmysql->listmysql))
		{								
			$linkmysql->close_mysql();
			$tpl->assign("topicdata", $data);
			$tpl->assign("mainpage", "topic/topic.modify.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆主題資料");
		}
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "add")
	{
		//-------------------------------------------------
		// 新增場地資料，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$tpl->assign("mainpage","topic/topic.add.html");
	}
	else
	{
		$tool->ShowMsgPage("主題資料，錯誤的操作");
	}
?>