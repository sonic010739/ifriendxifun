<?
	//---------------------------------------
	// 活動列表	- Member
	//---------------------------------------

	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	$uid = $_SESSION["uid"];

	$linkmysql->init();
	$where_clause = "";

	// Open 	可報名
	// UnPay	登記未繳費
	// Paid		待參加
	// Close	過去參加
	// Filter   活動搜尋

	$type = !isset($_GET['type']) ? "Open" : $_GET['type'];

	if ($type == "Open")
	{
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitie` ";
		$sql .= "WHERE `status` = 'OPEN' AND `ownerid` != '$uid' AND TO_DAYS(`join_deadline`) >= TO_DAYS(NOW()) ";
		$sql .= "AND `aid` NOT IN ( ";
		$sql .= "SELECT `a`.`aid` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' ";
		$sql .= ") ";
	}
	else if ($type == "UnPay")
	{
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `charge_ibon` c  ON `c`.`aid` = `a`.`aid` AND `c`.`uid` = `aj`.`uid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' AND `aj`.`charge_type` = 'iBon' AND `c`.`pay_time` IS NULL  ";
		$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
	}
	else if ($type == "Paid")
	{
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `charge_ibon` c  ON `c`.`aid` = `a`.`aid` AND `c`.`uid` = `aj`.`uid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND (`a`.`status` = 'OPEN' OR `a`.`status` = 'PROCEED') AND `aj`.`join_status` = 'join' AND ((`aj`.`charge_type` = 'iBon' AND `c`.`pay_time` IS NOT NULL) OR `aj`.`charge_type` = 'coupon') ";
		$sql .= "ORDER BY `a`.`act_date` DESC, `a`.`aid` DESC ";
	}
	else if ($type == "Close")
	{
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' AND (`a`.`status` = 'CLOSE' OR `a`.`status` = 'CANCEL') ";
		$sql .= "ORDER BY `a`.`act_date` DESC, `a`.`aid` DESC ";
	}
	else if ($type == "Filter")
	{
		if ($_SESSION["topic"] != -1) {
			$where_clause .= "WHERE `t`.`tid` = '" .$_SESSION["topic"] ."' ";
		} else {
			$where_clause .= "WHERE `t`.`tid` LIKE '%%' ";
		}

		if ($_SESSION["city"] != -1) {
			$where_clause .= "AND `p`.`placecity` = '" .$_SESSION["city"] ."' ";
		}

		$act_date_lb = date("Y-m-d", mktime(0, 0, 0, $_SESSION["act_month_lb"], $_SESSION["act_day_lb"], $_SESSION["act_year_lb"]));
		$where_clause .= "AND `a`.`act_date` >= '$act_date_lb' ";

		$act_date_ub = date("Y-m-d", mktime(0, 0, 0, $_SESSION["act_month_ub"], $_SESSION["act_day_ub"], $_SESSION["act_year_ub"]));
		$where_clause .= "AND `a`.`act_date` <= '$act_date_ub' ";

		$where_clause .= "AND `a`.`status` != 'CANCEL' ";

		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= $where_clause;
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
		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "WHERE `a`.`status` = 'OPEN' AND `a`.`ownerid` != '$uid' AND TO_DAYS(`a`.`join_deadline`) >= TO_DAYS(NOW()) ";
		$sql .= "AND `a`.`aid` NOT IN (";
		$sql .= "SELECT `a`.`aid` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' ";
		$sql .= ") ";
		$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
		$sql .= "LIMIT $head , $itemperpage";

		$list_title = "可報名的活動";
		$url = "index.php?act=activitielist&amp;type=Open";

		$options = "<b>可報名的活動</b> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Filter\">活動搜尋</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=UnPay\">登記未繳費的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Paid\">待參加的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Close\">過去參加的活動</a>\n";
	}
	else if ($type == "Filter")
	{
		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= $where_clause;
		$sql .= "ORDER BY `a`.`act_date` DESC, `a`.`aid` DESC ";
		$sql .= "LIMIT $head , $itemperpage";

		$list_title = "活動搜尋";
		$url = "index.php?act=activitielist&amp;type=Filter";

		$options = "<a href=\"./index.php?act=activitielist&amp;type=Open\">可報名的活動</a> |\n";
		$options .= "<b>活動搜尋</b> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=UnPay\">登記未繳費的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Paid\">待參加的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Close\">過去參加的活動</a>\n";
	}
	else if ($type == "UnPay")
	{
		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `charge_ibon` c  ON `c`.`aid` = `a`.`aid` AND `c`.`uid` = `aj`.`uid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' AND `aj`.`charge_type` = 'iBon' AND `c`.`pay_time` IS NULL  ";
		$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
		$sql .= "LIMIT $head , $itemperpage";

		$list_title = "登記未繳費的活動";
		$url = "index.php?act=activitielist&amp;type=UnPay";

		$options = "<a href=\"./index.php?act=activitielist&amp;type=Open\">可報名的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Filter\">活動搜尋</a> |\n";
		$options .= "<b>登記未繳費的活動</b> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Paid\">待參加的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Close\">過去參加的活動</a>\n";
	}
	else if ($type == "Paid")
	{
		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `charge_ibon` c  ON `c`.`aid` = `a`.`aid` AND `c`.`uid` = `aj`.`uid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND (`a`.`status` = 'OPEN' OR `a`.`status` = 'PROCEED') AND `aj`.`join_status` = 'join' AND ((`aj`.`charge_type` = 'iBon' AND `c`.`pay_time` IS NOT NULL) OR `aj`.`charge_type` = 'coupon') ";
		$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
		$sql .= "LIMIT $head , $itemperpage";

		$list_title = "可報名的活動";
		$url = "index.php?act=activitielist&amp;type=OpenAct";

		$options = "<a href=\"./index.php?act=activitielist&amp;type=Open\">可報名的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Filter\">活動搜尋</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=UnPay\">登記未繳費的活動</a> |\n";
		$options .= "<b>待參加的活動</b> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Close\">過去參加的活動</a>\n";
	}
	else if ($type == "Close")
	{
		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' AND (`a`.`status` = 'CLOSE' OR `a`.`status` = 'CANCEL') ";
		$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
		$sql .= "LIMIT $head , $itemperpage";

		$list_title = "過去參加的活動";
		$url = "index.php?act=activitielist&amp;type=Close";

		$options = "<a href=\"./index.php?act=activitielist&amp;type=Open\">可報名的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Filter\">活動搜尋</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=UnPay\">登記未繳費的活動</a> |\n";
		$options .= "<a href=\"./index.php?act=activitielist&amp;type=Paid\">待參加的活動</a> |\n";
		$options .= "<b>過去參加的活動</b>\n";
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

	$filter = array();

	if ($type == "Filter")
	{
		$filter["visable"] = 1;
		$filter["EOoption"] = 0;
		$filter["citys"] = array(
			-1 => "不限", "台北縣市" => "台北縣市", "高雄縣市" => "高雄縣市",
			"新竹縣市" => "新竹縣市", "台中縣市" => "台中縣市", "桃園縣" => "桃園縣",
			"基隆市" => "基隆市", "宜蘭縣" => "宜蘭縣", "台南縣市" => "台南縣市",
			"雲林縣" => "雲林縣", "嘉義縣市" => "嘉義縣市", "彰化縣" => "彰化縣",
			"苗栗縣" => "苗栗縣", "南投縣" => "南投縣", "屏東縣" => "屏東縣",
			"花蓮縣" => "花蓮縣", "台東縣" => "台東縣", "澎湖縣" => "澎湖縣",
			"金門縣" => "金門縣", "連江縣" => "連江縣", "其他" => "其他"
		);

		// 主題資料下拉式選單
		$sql = "SELECT `tid`, `tname` FROM `topic`";
		$linkmysql->query($sql);

		$filter["topic"] = "<option value=\"-1\">不限</option>";

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($_SESSION["topic"] == $data["tid"]) {
				$filter["topic"] .= sprintf("<option value=\"%d\" selected>%s</option>\n", $data["tid"], $data["tname"]);
			} else {
				$filter["topic"] .= sprintf("<option value=\"%d\">%s</option>\n", $data["tid"], $data["tname"]);
			}
		}

		// EO名單下拉式選單
		$sql = "SELECT `uid`, `username` FROM `user` WHERE `authority` = 'EO' OR `authority` = 'Admin'";
		$linkmysql->query($sql);

		$filter["EO"] = "<option value=\"-1\">不限</option>";
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($_SESSION["EO"] == $data["uid"]) {
				$filter["EO"] .= sprintf("<option value=\"%d\" selected>%s</option>\n", $data["uid"], $data["username"]);
			} else {
				$filter["EO"] .= sprintf("<option value=\"%d\">%s</option>\n", $data["uid"], $data["username"]);
			}
		}

		// 搜尋時間日期區間設定
		if ($_SESSION["act_year_lb"] == "")
		{
			$_SESSION["act_year_lb"] = date("Y");
			$_SESSION["act_month_lb"] = date("m");
			$_SESSION["act_day_lb"] = date("d");

			if (date("m") == 12)
			{
				$_SESSION["act_year_ub"] = date("Y")+1;
				$_SESSION["act_month_ub"] = 1;
			}
			else
			{
				$_SESSION["act_year_ub"] = date("Y");
				$_SESSION["act_month_ub"] = date("m")+1;
			}

			$_SESSION["act_day_ub"] = date("d");
		}

		$year = date("Y") + 1;

		$filter["act_year_lb"] = "";

		for ($i=2008; $i<=$year; $i++)
		{
			if ($_SESSION["act_year_lb"] == $i) {
				$filter["act_year_lb"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_year_lb"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		$filter["act_month_lb"] = "";

		for ($i=1; $i<13; $i++)
		{
			if ($_SESSION["act_month_lb"] == $i) {
				$filter["act_month_lb"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_month_lb"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		$filter["act_day_lb"] = "";

		for ($i=1; $i<32; $i++)
		{
			if ($_SESSION["act_day_lb"] == $i) {
				$filter["act_day_lb"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_day_lb"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		$filter["act_year_ub"] = "";

		for ($i=2008; $i<=$year; $i++)
		{
			if ($_SESSION["act_year_ub"] == $i) {
				$filter["act_year_ub"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_year_ub"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		$filter["act_month_ub"] = "";

		for ($i=1; $i<13; $i++)
		{
			if ($_SESSION["act_month_ub"] == $i) {
				$filter["act_month_ub"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_month_ub"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		$filter["act_day_ub"] = "";

		for ($i=1; $i<32; $i++)
		{
			if ($_SESSION["act_day_ub"] == $i) {
				$filter["act_day_ub"] .= sprintf("<option value=\"%d\" selected>%d</option>\n", $i, $i);
			} else {
				$filter["act_day_ub"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}
	}

	$linkmysql->close_mysql();

	// 頁碼
	$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
	$tpl->assign("page",$page);

	// 跳頁選單
	$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
	$tpl->assign("totalpage",$totalpage);

	$tpl->assign("filter", $filter);
	$tpl->assign("citys", $filter["citys"]);

	if (isset($_SESSION["city"]))
	{
		$tpl->assign("sel_city", $_SESSION["city"]);
	}

	$tpl->assign("options", $options);
	$tpl->assign("list_title", $list_title);
	$tpl->assign("list_count", count($activitielist));
	$tpl->assign("activitielist", $activitielist);
	$tpl->assign("mainpage", "activities/activities.list.html");
?>