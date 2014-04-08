<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	if ($_GET["sel"] == "list")
	{
		//---------------------------------------
		// 優惠卷列表
		// 可檢視所有的 和 檢視個人的
		//---------------------------------------
		
		$statuslist = array(
			"All" => "不限",
			"Used" => "已使用",
			"Unuse" => "未使用"
		);
		
		$type = $_GET["type"];		
		$owner = $_GET["owner"];
		$giver = $_GET["giver"];
		$status = $_GET["status"];
		
		if ($_SESSION["authority"] == "User")
		{
			if ($_GET["uid"] == $_SESSION["uid"]) 
			{
				$uid = $_SESSION["uid"];
				$all = false;
			}
			else 
			{
				$tool->ShowMsgPage("沒有權限去觀看其他人的優惠卷資料");				
			}			
		}
		else
		{
			if (!empty($_GET["uid"]))
			{
				$uid = $_GET["uid"];
				$all = false;
			}
			else
			{
				$all = true;
			}
		}
			
		$linkmysql->init();
		
		if (!$all)
		{
			$sql = "SELECT COUNT(*) FROM `coupon` WHERE `uid` = '$uid'";
		}
		else if ($type == "search")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `coupon` c ";
			$sql .= "LEFT JOIN `user` u1 ON `c`.`uid` = `u1`.`uid` ";
			$sql .= "LEFT JOIN `user` u2 ON `c`.`give_id` = `u2`.`uid` ";
			$sql .= "WHERE `u1`.`username` LIKE '%$owner%' AND `u2`.`username` LIKE '%$giver%' ";
			
			if ($status == "Used") {			
				$sql .= "AND `c`.`use_time` IS NOT NULL ";
			} else if ($status == "Unuse") {
				$sql .= "AND `c`.`use_time` IS NULL ";
			}
		}
		else
		{
			$sql = "SELECT COUNT(*) FROM `coupon`";
		}
		
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 分頁設定
		$itemperpage = 25;		
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];		
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );    	
		
		if (!$all)
		{
			$sql  = "SELECT `c`.`coupon_id`, `c`.`coupon_type`, `c`.`uid`, `c`.`give_id`, `c`.`give_time`, `c`.`use_time`, ";
			$sql .= "`c`.`reason`, `u1`.`username` AS `username`, `u2`.`username` AS `givename` ";
			$sql .= "FROM `coupon` c ";
			$sql .= "LEFT JOIN `user` u1 ON `c`.`uid` = `u1`.`uid` ";
			$sql .= "LEFT JOIN `user` u2 ON `c`.`give_id` = `u2`.`uid` ";
			$sql .= "WHERE `c`.`uid` = '$uid' ";
			$sql .= "ORDER BY `c`.`give_time` DESC, `u1`.`username` ASC ";		
			$sql .= "LIMIT $head , $itemperpage";
			
			$url = "index.php?act=coupon&amp;sel=list&amp;uid=$uid";
		}
		else if ($type == "search")
		{
			$sql  = "SELECT `c`.`coupon_id`, `c`.`coupon_type`, `c`.`uid`, `c`.`give_id`, `c`.`give_time`, `c`.`use_time`, ";
			$sql .= "`c`.`reason`, `u1`.`username` AS `username`, `u2`.`username` AS `givename` ";
			$sql .= "FROM `coupon` c ";
			$sql .= "LEFT JOIN `user` u1 ON `c`.`uid` = `u1`.`uid` ";
			$sql .= "LEFT JOIN `user` u2 ON `c`.`give_id` = `u2`.`uid` ";
			$sql .= "WHERE `u1`.`username` LIKE '%$owner%' AND `u2`.`username` LIKE '%$giver%' ";
			
			if ($status == "Used") {			
				$sql .= "AND `c`.`use_time` IS NOT NULL ";
			} else if ($status == "Unuse") {
				$sql .= "AND `c`.`use_time` IS NULL ";
			}
			
			$sql .= "ORDER BY `c`.`give_time` DESC, `u1`.`username` ASC ";			
			$sql .= "LIMIT $head , $itemperpage";
			
			$url = sprintf("index.php?act=coupon&sel=list&type=search&owner=%s&giver=%s&status=%s", $owner, $giver, $status);
		}
		else
		{
			$sql  = "SELECT `c`.`coupon_id`, `c`.`coupon_type`, `c`.`uid`, `c`.`give_id`, `c`.`give_time`, `c`.`use_time`, ";
			$sql .= "`c`.`reason`, `u1`.`username` AS `username`, `u2`.`username` AS `givename` ";
			$sql .= "FROM `coupon` c ";
			$sql .= "LEFT JOIN `user` u1 ON `c`.`uid` = `u1`.`uid` ";
			$sql .= "LEFT JOIN `user` u2 ON `c`.`give_id` = `u2`.`uid` ";
			$sql .= "ORDER BY `c`.`give_time` DESC, `u1`.`username` ASC ";		
			$sql .= "LIMIT $head , $itemperpage";
			
			$url = "index.php?act=coupon&amp;sel=list";
		}

		$linkmysql->query($sql);
		print mysql_error();
		$couponlist = array();
		
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{			
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["givename"] = $tool->ShowMemberLink( $data["give_id"], $data["givename"]);
			$data["reason"] = $tool->UTF8_CuttingStr($data["reason"], 50);
			
			if (empty($data["use_time"]))
			{
				$data["status"] = "<font color=\"red\">未使用</font>";
				$data["use_time"] = "未使用";
			}
			else 
			{
				$data["status"] = "<font color=\"green\">已使用</font>";
			}
			
			$data["detail"] = sprintf("<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">檢視</a>", $data["coupon_id"]);
						
			array_push($couponlist, $data);
		}
		
		$linkmysql->close_mysql();
		
		
		
		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("owner", $owner);
		$tpl->assign("giver", $giver);
		$tpl->assign("status", $status);
		$tpl->assign("statuslist", $statuslist);
		$tpl->assign("coupon_count", count($couponlist));
		$tpl->assign("couponlist", $couponlist);
		$tpl->assign("mainpage", "coupon.list.html");
	}
	else if ($_GET["sel"] == "detail")
	{
		//---------------------------------------
		// 檢視該優惠卷的所有詳細資料
		//---------------------------------------
		
		$id = $_GET["id"];
		
		$linkmysql->init();
		
		$sql  = "SELECT `c`.*, `u1`.`username` AS `username`, `u2`.`username` AS `givename`, `a`.`name` ";
		$sql .= "FROM `coupon` c ";
		$sql .= "LEFT JOIN `activitie` a ON `c`.`use_act` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u1 ON `c`.`uid` = `u1`.`uid` ";
		$sql .= "LEFT JOIN `user` u2 ON `c`.`give_id` = `u2`.`uid` ";
		$sql .= "WHERE `c`.`coupon_id` = '$id';";
		$linkmysql->query($sql);
		
		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["uid"] != $_SESSION["uid"] && $_SESSION["authority"] == "User")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("沒有權限去觀看其他人的優惠卷資料");
			}
			
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["givename"] = $tool->ShowMemberLink( $data["give_id"], $data["givename"]);
			$data["reason"] = nl2br($tool->AddLink2Text($data["reason"]));
			
			if (!empty($data["name"])) 
			{
				$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["use_act"], $data["name"]);
			}
			else 
			{
				$data["name"] = "--";
			}
							
			if (empty($data["use_time"]))
			{
				$data["status"] = "<font color=\"red\">未使用</font>";
				$data["use_time"] = "--";
			}
			else 
			{
				$data["status"] = "<font color=\"green\">已使用</font>";
			}
			
			$data["del_link"] = sprintf("(<a href=\"./coupon.act.php?act=del&amp;id=%d\">刪除</a>)", $data["coupon_id"]);
			
			$data["detail"] = sprintf("<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">檢視</a>", $data["coupon_id"]);
			
			$linkmysql->close_mysql();
			$tpl->assign("coupon", $data);
			$tpl->assign("mainpage", "coupon.detail.html");
		}
		else
		{			
			$tool->ShowMsgPage("找不到該筆優惠卷資料", "回到優惠卷資料管理 ", "index.php?act=coupon&sel=list");
		}
	}
	else if ($_GET["sel"] == "give" && $_SESSION["authority"] == "Admin")
	{
		//---------------------------------------
		// 給予優惠卷
		//---------------------------------------
		
		$uid = $_GET["uid"];
		
		$linkmysql->init();
		$sql  = "SELECT `username` FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);
		
		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{	
			$data["uid"] = $uid;
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
						
			$linkmysql->close_mysql();
			
			$tpl->assign("data", $data);
			$tpl->assign("mainpage", "coupon.give.html");
		}
		else
		{
			$linkmysql->close_mysql();			
			$tool->ShowMsgPage("錯誤: 沒有這個會員，會員編號". $uid);
		}
	}
?>