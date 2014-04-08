<?
	$citys = array(
		-1 => "請選擇",
		"台北縣市" => "台北縣市",
		"台北縣市" => "台北縣市",
		"高雄縣市" => "高雄縣市",
		"新竹縣市" => "新竹縣市",
		"台中縣市" => "台中縣市",
		"桃園縣" => "桃園縣",
		"基隆市" => "基隆市",
		"宜蘭縣" => "宜蘭縣",
		"台南縣市" => "台南縣市",
		"雲林縣" => "雲林縣",
		"嘉義縣市" => "嘉義縣市",
		"彰化縣" => "彰化縣",
		"苗栗縣" => "苗栗縣",
		"南投縣" => "南投縣",
		"屏東縣" => "屏東縣",
		"花蓮縣" => "花蓮縣",
		"台東縣" => "台東縣",
		"澎湖縣" => "澎湖縣",
		"金門縣" => "金門縣",
		"連江縣" => "連江縣",
		"其他" => "其他"
	);
		
	if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "list")
	{
		//-------------------------------------------------
		// 檢視場地列表，非管理員無法進行此項操作
		//-------------------------------------------------
		
		// open mysql connection
		$linkmysql->init();
		$filter = urldecode($_GET['filter']);
		
		if ($filter != "" && $filter != -1)
		{
			$sql = "SELECT COUNT(*) FROM `place` WHERE `placecity` = '$filter'";
		}
		else
		{
			$sql = "SELECT COUNT(*) FROM `place`";
		}
		
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 分頁設定
		$itemperpage = 25;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];		
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );    	
		
		$sql  = "SELECT * FROM `place` ";
		$sql .= "ORDER BY `placecity` ASC ";
		$sql .= "LIMIT $head , $itemperpage";
		
		if ($filter != "" && $filter != -1)
		{
			$sql  = "SELECT * FROM `place` ";
			$sql .= "WHERE `placecity` = '$filter' ";
			$sql .= "ORDER BY `placecity` ASC ";
			$sql .= "LIMIT $head , $itemperpage";
		}
		else
		{
			$sql  = "SELECT * FROM `place` ";
			$sql .= "ORDER BY `placecity` ASC ";
			$sql .= "LIMIT $head , $itemperpage";
		}
		
		$linkmysql->query($sql);
		
		$placelist = array();
		
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["view"] = sprintf("<a href=\"./index.php?act=place&amp;sel=detail&amp;pid=%s\">%s</a>", $data["pid"], "檢視");
			$data["placename"] = $tool->UTF8_CuttingStr($data["placename"], 40);
			
			if ($data["status"] == "Open") {
				$data["status"] = "開放";
			} else {
				$data["status"] = "關閉";
			}
			
			$data["modify"] = sprintf("<a href=\"./index.php?act=place&amp;sel=modify&amp;pid=%s\">%s</a>", $data["pid"], "修改");
			$data["del"] = sprintf("<a href=\"./place.act.php?act=del&amp;pid=%s\" onClick='return confirm(\"確定要刪除\")'>%s</a>", $data["pid"], "刪除");
			
			array_push($placelist, $data);
		}
				
		// 頁碼
		$page = $tool->showpages("index.php?act=place&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page("index.php?act=place&amp;sel=list", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("citys", $citys);
		$tpl->assign("sel_city", $filter);
		$tpl->assign("placelist", $placelist);
		$tpl->assign("list_count", count($placelist));
		$tpl->assign("mainpage","place/place.html");
		
		$linkmysql->close_mysql();
	}
	else if ($_GET["sel"] == "detail")
	{
		//-------------------------------------------------
		// 檢視場地詳細資料
		//-------------------------------------------------
		
		$pid = $_GET["pid"];
		
		$linkmysql->init();
		$sql = "SELECT * FROM `place` WHERE `pid` = '$pid'";		
		$linkmysql->query($sql);
		
		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["decription"] = nl2br($tool->AddLink2Text($data["decription"]));
			
			$data["map"] = sprintf("./googlemap.php?lat=%f&amp;lng=%f&amp;title=%s&amp;address=%s",
				$data["lat"], $data["lng"], urlencode($data["placename"]), urlencode($data["placeaddress"]));
			
			$data["placelink"] = sprintf("<a href=\"%s\" target=\"new\">%s</a>", $data["placelink"], $data["placelink"]);
			
			$linkmysql->close_mysql();
			$tpl->assign("placedata", $data);
			$tpl->assign("mainpage", "place/place.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆場地資料");
		}
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "modify")
	{
		//-------------------------------------------------
		// 修改場地資料，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$pid = $_GET["pid"];
		
		$linkmysql->init();
		$sql = "SELECT * FROM `place` WHERE `pid` = '$pid'";		
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{			
			if ($data["status"] == "Open") {
				$data["status"] = sprintf("開放&nbsp;&nbsp;[<a href=\"./place.act.php?act=Close&amp;pid=%d\">設為關閉</a>]", $data["pid"]);
			} else {
				$data["status"] = sprintf("關閉&nbsp;&nbsp;[<a href=\"./place.act.php?act=Open&amp;pid=%d\">設為開放</a>]", $data["pid"]);
			}
			
			$linkmysql->close_mysql();
			
			$tpl->assign("placedata", $data);
			$tpl->assign("citys", $citys);
			$tpl->assign("mainpage", "place/place.modify.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆場地資料");
		}
	}
	else if ($_SESSION["authority"] == "Admin" && $_GET["sel"] == "add")
	{
		//-------------------------------------------------
		// 新增場地資料，非管理員無法進行此項操作
		//-------------------------------------------------
		
		$tpl->assign("mainpage","place/place.add.html");
	}
	else
	{
		$tool->ShowMsgPage("場地資料，錯誤的操作");
	}
?>