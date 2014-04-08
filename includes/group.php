<?
	if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "list")
	{
		//-------------------------------------------------
		// 檢視族群列表，非管理員無法進行此項操作
		//-------------------------------------------------
		
		// open mysql connection
		$linkmysql->init();
		
		$sql = "SELECT COUNT(*) FROM `group`";
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 分頁設定
		$itemperpage = 25;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];		
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );    	
		
		$sql = "SELECT `gid`, `gname` FROM `group` ORDER BY `gid` ASC LIMIT $head , $itemperpage";
		$linkmysql->query($sql);
		
		$grouplist = array();
		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$data[2] = sprintf("<a href=\"./index.php?act=group&amp;sel=modify&amp;gid=%s\">%s</a>", $data[0], "修改");
			$data[3] = sprintf("<a href=\"./group.act.php?act=del&amp;gid=%s\" onClick='return confirm(\"確定要刪除\")'>%s</a>", $data[0], "刪除");			
			
			array_push($grouplist, $data);
		}
		
		
		// 頁碼
		$page = $tool->showpages("index.php?act=group&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page("index.php?act=group&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("grouplist", $grouplist);
		$tpl->assign("mainpage","group/group.html");
		
		$linkmysql->close_mysql();
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "modify")
	{
		//-------------------------------------------------
		// 修改族群資料，非管理員無法進行此項操作
		//-------------------------------------------------		
		
		$gid = $_GET["gid"];
		
		$linkmysql->init();
		$sql = "SELECT * FROM `group` WHERE `gid` = '$gid'";
		$linkmysql->query($sql);
		
		if ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$tpl->assign("groupdata", $data);
			$tpl->assign("mainpage", "group/group.modify.html");
		}
		
		$linkmysql->close_mysql();		
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "add")
	{
		//-------------------------------------------------
		// 新增族群資料，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$tpl->assign("mainpage","group/group.add.html");
	}
	else
	{
		$tool->ShowMsgPage("族群資料，錯誤的操作");
	}
?>