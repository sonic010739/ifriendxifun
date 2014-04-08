<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	if ($_SESSION["authority"] != "Admin" && $_SESSION["authority"] != "EO")
	{
		$tool->ShowMsgPage("權限不足");
	}

	if (!isset($_GET["sel"]))
	{
		$_GET["sel"] = "list";
	}

	if ($_GET["sel"] == "list" && $_SESSION["authority"] == "Admin")
	{
		//-------------------------------------------------
		// 檢視會員列表
		//-------------------------------------------------

		$linkmysql->init();
		$where_clause = "";
		if ($_GET['filter'] == "search")
		{
			$condiction = $_SESSION["search"];

			$where_clause  = "WHERE `username` LIKE '%". $condiction["username"] ."%' ";
			$where_clause .= "AND `nickname` LIKE '%". $condiction["nickname"] ."%' ";
			$where_clause .= "AND `realname` LIKE '%". $condiction["realname"] ."%' ";

			if ($condiction["sex"] != "不限")
				$where_clause .= "AND `sex` = '". $condiction["sex"] ."' ";

			if ($condiction["birth_year"] != "不限")
				$where_clause .= "AND `birth_year` = '". $condiction["birth_year"] ."' ";

			if ($condiction["birth_month"] != "不限")
				$where_clause .= "AND `birth_month` = '". $condiction["birth_month"] ."' ";

			if ($condiction["birth_day"] != "不限")
				$where_clause .= "AND `birth_day` = '". $condiction["birth_day"] ."' ";

			$year = date("Y");

			if ($condiction["age_lb"] != "")
				$where_clause .= "AND '$year' - `birth_year` >= '". $condiction["age_lb"] ."' ";

			if ($condiction["age_ub"] != "")
				$where_clause .= "AND '$year' - `birth_year` <= '". $condiction["age_ub"] ."' ";

			if ($condiction["email"] != "")
				$where_clause .= "AND `email` LIKE '%". $condiction["email"] ."%' ";

			if ($condiction["msn"] != "")
				$where_clause .= "AND `msn` LIKE '%". $condiction["msn"] ."%' ";

			if ($condiction["tel"] != "")
				$where_clause .= "AND `tel` LIKE '%". $condiction["tel"] ."%' ";

			if (count($condiction["constellation"]) > 0 && count($condiction["constellation"]) != 12)
			{
				$where_clause .= "AND (`constellation` = '". $condiction["constellation"][0] ."' ";

				for ($i=1; $i<count($condiction["constellation"]); $i++)
					$where_clause .= "OR `constellation` = '". $condiction["constellation"][$i] ."' ";

				$where_clause .= ") ";
			}

			if (count($condiction["education"]) > 0 && count($condiction["education"]) != 7)
			{
				$where_clause .= "AND (`education` = '". $condiction["education"][0] ."' ";

				for ($i=1; $i<count($condiction["education"]); $i++)
					$where_clause .= "OR `education` = '". $condiction["education"][$i] ."' ";

				$where_clause .= ") ";
			}

			if ($condiction["top_education"] != "")
				$where_clause .= "AND `top_education` LIKE '%". $condiction["top_education"] ."%' ";

			if (count($condiction["interest"]) > 0)
			{
				$where_clause .= "AND (`interest` LIKE '%". $condiction["interest"][0] ."%' ";

				for ($i=1; $i<count($condiction["interest"]); $i++)
					$where_clause .= "OR `interest` LIKE '%". $condiction["interest"][$i] ."%' ";

				$where_clause .= ") ";
			}

			if (count($condiction["career"]) > 0)
			{
				$where_clause .= "AND (`career` LIKE '%". $condiction["career"][0] ."%' ";

				for ($i=1; $i<count($condiction["career"]); $i++)
					$where_clause .= "OR `career` LIKE '%". $condiction["career"][$i] ."%' ";

				$where_clause .= ") ";
			}

			if (count($condiction["inhabit"]) > 0)
			{
				$where_clause .= "AND (`inhabit` LIKE '%". $condiction["inhabit"][0] ."%' ";

				for ($i=1; $i<count($condiction["inhabit"]); $i++)
					$where_clause .= "OR `inhabit` LIKE '%". $condiction["inhabit"][$i] ."%' ";

				$where_clause .= ") ";
			}

			if ($condiction["status"] != "不限")
				$where_clause .= "AND `status` = '". $condiction["status"] ."' ";

			if (count($condiction["authority"]) > 0 && count($condiction["authority"]) != 3)
			{
				$where_clause .= "AND (`authority` = '". $condiction["authority"][0] ."' ";

				for ($i=1; $i<count($condiction["authority"]); $i++)
						$where_clause .= "OR `authority` = '". $condiction["authority"][$i] ."' ";

				$where_clause .= ") ";
			}

			if ($condiction["blackstatus"] != "不限")
			{
				if ($condiction["blackstatus"] == "有案底")
				{
					$where_clause .= "AND `uid` IN ( ";
					$where_clause .= "SELECT `black_id` FROM `blacklist` ";
					$where_clause .= "WHERE `result` = 'Pass' AND `lock` = 'false' )";
				}
				else if ($condiction["blackstatus"] == "停權中")
				{
					$where_clause .= "AND `uid` IN ( ";
					$where_clause .= "SELECT `black_id` FROM `blacklist` ";
					$where_clause .= "WHERE `result` = 'Pass' AND `lock` = 'true' )";
				}
			}

			$sql  = "SELECT COUNT(*) FROM `user` ";
			$sql .= $where_clause;
		}
		else if ($_GET['filter'] == "User")
		{
			$sql  = "SELECT COUNT(*) FROM `user` ";
			$sql .= "WHERE `authority` = 'User'";
		}
		else if ($_GET['filter'] == "EO")
		{
			$sql  = "SELECT COUNT(*) FROM `user` ";
			$sql .= "WHERE `authority` = 'EO'";
		}
		else if ($_GET['filter'] == "Admin")
		{
			$sql  = "SELECT COUNT(*) FROM `user` ";
			$sql .= "WHERE `authority` = 'Admin'";
		}
		else
		{
			$sql = "SELECT COUNT(*) FROM `user` ";
		}

		$linkmysql->query($sql);
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));

		// 分頁設定
		$itemperpage = 25;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );

		if ($_GET['filter'] == "search")
		{
			$key = urldecode($_GET['key']);
			$sql  = "SELECT * ";
			$sql .= "FROM `user` ";
			$sql .= $where_clause;
			$sql .= "ORDER BY `username` ASC LIMIT $head , $itemperpage";

			$list_title = "搜尋會員名單";
			$url = "index.php?act=membercenter&amp;sel=list&amp;filter=search";

			$options  ="<a href=\"./index.php?act=membercenter&amp;sel=list\">所有會員名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=User\">使用者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=EO\">EO名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=Admin\">管理者名單</a> |\n";
			$options .="<b>會員篩選</b>\n";
		}
		else if ($_GET['filter'] == "User")
		{
			$sql  = "SELECT * ";
			$sql .= "FROM `user` ";
			$sql .= "WHERE `authority` = 'User'";
			$sql .= "ORDER BY `username` ASC LIMIT $head , $itemperpage";

			$list_title = "使用者名單";
			$url = "index.php?act=membercenter&amp;sel=list&amp;filter=User";

			$options  ="<a href=\"./index.php?act=membercenter&amp;sel=list\">所有會員名單</a> |\n";
			$options .="<b>使用者名單</b> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=EO\">EO名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=Admin\">管理者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=search\">會員篩選</a>\n";
		}
		else if ($_GET['filter'] == "EO")
		{
			$sql  = "SELECT * ";
			$sql .= "FROM `user` ";
			$sql .= "WHERE `authority` = 'EO'";
			$sql .= "ORDER BY `username` ASC LIMIT $head , $itemperpage";

			$list_title = "EO名單";
			$url = "index.php?act=membercenter&amp;sel=list&amp;filter=EO";

			$options  ="<a href=\"./index.php?act=membercenter&amp;sel=list\">所有會員名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=User\">使用者名單</a> |\n";
			$options .="<b>EO名單</b> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=Admin\">管理者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=search\">會員篩選</a>\n";
		}
		else if ($_GET['filter'] == "Admin")
		{
			$sql  = "SELECT * ";
			$sql .= "FROM `user` ";
			$sql .= "WHERE `authority` = 'Admin'";
			$sql .= "ORDER BY `username` ASC LIMIT $head , $itemperpage";

			$list_title = "管理者名單";
			$url = "index.php?act=membercenter&amp;sel=list&amp;filter=Admin";

			$options  ="<a href=\"./index.php?act=membercenter&amp;sel=list\">所有會員名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=User\">使用者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=EO\">EO名單</a> |\n";
			$options .="<b>管理者名單</b> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=search\">會員篩選</a>\n";
		}
		else
		{
			$sql  = "SELECT * ";
			$sql .= "FROM `user` ";
			$sql .= "ORDER BY `username` ASC LIMIT $head , $itemperpage";

			$list_title = "所有會員名單";
			$url = "index.php?act=membercenter&amp;sel=list";

			$options  ="<b>所有會員名單</b> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=User\">使用者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=EO\">EO名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=Admin\">管理者名單</a> |\n";
			$options .="<a href=\"./index.php?act=membercenter&amp;sel=list&amp;filter=search\">會員篩選</a>\n";
		}

		$linkmysql->query($sql);

		$memberlist = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["age"] = date("Y") - $data["birth_year"];
			$data["status"] = ($data["status"] == "Invalidate") ? "<font color=\"red\">未驗證</font>" : "<font color=\"green\">已驗證</font>";
			$data["detail"] = sprintf("<a href=\"./index.php?act=membercenter&amp;sel=detail&amp;uid=%s\">%s</a>", $data["uid"], "檢視");

			if ($data["authority"] == "User")
			{
				$data["authority"] = "使用者";
				$data["del"] = sprintf( "<a href=\"./member.act.php?act=Del&amp;uid=%d\" onClick='return confirm(\"確定要刪除\")'>刪除</a>", $data["uid"]);
			}
			else if ($data["authority"] == "EO")
			{
				$data["authority"] = "EO";
				$data["del"] = sprintf( "<a href=\"./member.act.php?act=Del&amp;uid=%d\" onClick='return confirm(\"確定要刪除\")'>刪除</a>", $data["uid"]);
			}
			else if ($data["authority"] == "Admin")
			{
				$data["authority"] = "<font color=\"red\">管理員</font>";
				$data["del"] = "<font color=\"gray\">刪除</font>";
			}

			$data["modify"] = sprintf("<a href=\"./index.php?act=member&amp;sel=adminmodify&amp;uid=%d\">修改</a>", $data["uid"]);

			array_push($memberlist, $data);
		}

		if ($_GET['filter'] == "search")
		{
			$search = array();
			$search["visable"] = 1;
			$year = date("Y") - 18;

			$search["birth_year"] = sprintf("<option value=\"不限\" selected>不限</option>\n");

			for ($i=1900; $i<=$year; $i++)
			{
				$search["birth_year"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}

			$search["birth_month"] = sprintf("<option value=\"不限\" selected>不限</option>\n");

			for ($i=1; $i<13; $i++)
			{
				$search["birth_month"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}

			$search["birth_day"] = sprintf("<option value=\"不限\" selected>不限</option>\n");

			for ($i=1; $i<32; $i++)
			{
				$search["birth_day"] .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
			}
		}

		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);

		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);

		$tpl->assign("key", $key);
		$tpl->assign("search", $search);
		$tpl->assign("options", $options);
		$tpl->assign("list_title", $list_title);
		$tpl->assign("memberlist", $memberlist);
		$tpl->assign("list_count", count($memberlist));
		$tpl->assign("mainpage", "member/member.list.html");

		$linkmysql->close_mysql();
	}
	else if ($_GET["sel"] == "detail" && $_SESSION["login"] = 1)
	{
		//-------------------------------------------------
		// 檢視會員詳細資料
		//-------------------------------------------------

		if (!isset($_GET["uid"])) {
			$uid = $_SESSION["uid"];
		} else {
			$uid = $_GET["uid"];
		}

		// 非管理員無法檢視其他人的資料
		if ($_SESSION["authority"] != "Admin" && $_SESSION["uid"] != $uid)
		{
			$tool->ShowMsgPage("權限不足，無法檢視其他會員的資料");
		}

		$linkmysql->init();
		$sql = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($_SESSION["authority"] == "Admin")
			{
				$data["manage"] = 1;
			}

			$data["interest"] = nl2br($data["interest"]);

			if (preg_match("/career: (.+) career_detail: (.+) career_title: (.+)/" , $data["career"], $matche))
			{
				$data["career"] = $matche[1];
				$data["career_detail"] = $matche[2];
				$data["career_title"] = $matche[3];
			}

			$data["status"] = ($data["status"] == "Invalidate") ? "<font color=\"red\">未驗證</font>" : "<font color=\"blue\">已驗證</font>";

			if ($data["authority"] == "User")
			{
				$data["authority"] = "使用者";

				if ($_SESSION["authority"] == "Admin")
				{
					$data["auth_opertaion"] = sprintf( "[<a href=\"./member.act.php?act=setEO&amp;uid=%d\" onClick='return confirm(\"確定要升級為EO?\")'>升級為EO</a>]", $data["uid"]);
				}
			}
			else if ($data["authority"] == "EO")
			{
				$data["authority"] = "EO";

				if ($_SESSION["authority"] == "Admin")
				{
					$data["auth_opertaion"] = sprintf( "[<a href=\"./member.act.php?act=setUser&amp;uid=%d\" onClick='return confirm(\"確定要降級為使用者?\")'>降級為使用者</a>] | ", $data["uid"]);
					$data["auth_opertaion"] .= sprintf( "[<a href=\"./member.act.php?act=setAdmin&amp;uid=%d\" onClick='return confirm(\"確定要升級為管理者?\")'>升級為管理者</a>]", $data["uid"]);
				}
			}
			else if ($data["authority"] == "Admin")
			{
				$data["authority"] = "<font color=\"red\">管理員</font>";

				if ($_SESSION["authority"] == "Admin")
				{
					$data["auth_opertaion"] = "<font color=\"red\">無法變更權限</font>";
				}
			}

			// 帳號狀態，是否被停權
			$sql  = "SELECT `black_serial` ";
			$sql .= "FROM `blacklist` ";
			$sql .= "WHERE `black_id` = '$uid' AND `lock` = 'true' ";
			$linkmysql->query($sql);

			if (list($data["black_status"]) = mysql_fetch_array($linkmysql->listmysql))
			{
				// 可再加入停權的詳細資訊
				$data["black_status"] = "<font color=\"red\">停權中</font>";
			}
			else
			{
				$data["black_status"] = "<font color=\"green\">正常</font>";
			}

			$data["show_myact"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;uid=%d\">檢視我的活動記錄</a>", $data["uid"]);
			$data["show_coupon"] = sprintf("<a href=\"./index.php?act=coupon&amp;sel=list&amp;uid=%d\">檢視我的優惠卷記錄</a>", $data["uid"]);
			$data["give_coupon"] = sprintf("<a href=\"./index.php?act=coupon&amp;sel=give&amp;uid=%d\">給予該會員優惠卷</a>", $data["uid"]);

			// 取出會員參加活動紀錄
			$data["act_join_curr"] = 0;
			$data["act_join_past"] = 0;

			$sql  = "SELECT `a`.`aid`, `a`.`status`, `aj`.`join_status` ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `u`.`uid` = '$uid'";
			$linkmysql->query($sql);

			while ($act_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if (($act_data["status"] == "OPEN" || $act_data["status"] == "PROCEED") && $act_data["join_status"] == "join") {
					$data["act_join_curr"]++;
				}

				if ($act_data["status"] == "CLOSE" && $act_data["join_status"] == "join") {
					$data["act_join_past"]++;
				}
			}

			$data["act_join_curr"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currjoin&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_curr"]);
			$data["act_join_past"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pastjoin&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_past"]);

			// 取出會員取消活動記錄
			$data["act_join_cancel"] = 0;

			$sql = "SELECT COUNT(*) FROM `activitiecancel` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["act_join_cancel"]) = mysql_fetch_array($linkmysql->listmysql);

			$data["act_join_cancel"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancel&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_cancel"]);

			// 優惠卷張數
			$sql = "SELECT COUNT(*) FROM `coupon` WHERE `uid` = '$uid' AND `use_time` IS NULL";
			$linkmysql->query($sql);
			list($data["coupon_unuse"]) = mysql_fetch_array($linkmysql->listmysql);

			$sql = "SELECT COUNT(*) FROM `coupon` WHERE `uid` = '$uid' AND `use_time` IS NOT NULL";
			$linkmysql->query($sql);
			list($data["coupon_used"]) = mysql_fetch_array($linkmysql->listmysql);

			$data["coupon_total"] = $data["coupon_unuse"] + $data["coupon_used"];
			$data["coupon_total"] = sprintf("<a href=\"./index.php?act=coupon&amp;sel=list&amp;uid=%d\">%d</a>", $data["uid"], $data["coupon_total"]);

			// 參加活動繳費資料
			$data["charge_total_pay"] = 0;
			$data["charge_unpay"] = 0;
			$data["charge_unpay_overdue"] = 0;
			$data["charge_paid"] = 0;

			$sql  = "SELECT `fees`, `process_time`, `pay_time` ";
			$sql .= "FROM `charge_ibon` ";
			$sql .= "WHERE `uid` = '$uid'";
			$linkmysql->query($sql);

			while ($charge_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($charge_data["pay_time"] != "")
				{
					$data["charge_paid"]++;
					$data["charge_total_pay"] += $charge_data["fees"];
				}

				if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $charge_data["process_time"], $matche))
				{
					$charge_data["process_time"] = date("Y-m-d", mktime(0, 0, 0, $matche[2], $matche[3]+7, $matche[1]));

					if ($charge_data["pay_time"] == "" && $charge_data["process_time"] > date("Y-m-d"))
					{
						$data["charge_unpay"]++;
					}
					else if ($charge_data["pay_time"] == "" && $charge_data["process_time"] <= date("Y-m-d"))
					{
						$data["charge_unpay_overdue"]++;
					}
				}
			}

			// 舉辦活動次數
			$data["act_hold_open"] = 0;
			$data["act_hold_proceed"] = 0;
			$data["act_hold_close"] = 0;
			$data["act_hold_cancel"] = 0;

			$sql  = "SELECT `status` ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$linkmysql->query($sql);

			while ($act_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($act_data["status"] == "OPEN" || $act_data["status"] == "APPLY_CANCEL")
				{
					$data["act_hold_open"]++;
				}
				else if ($act_data["status"] == "PROCEED")
				{
					$data["act_hold_proceed"]++;
				}
				else if ($act_data["status"] == "CLOSE")
				{
					$data["act_hold_close"]++;
				}
				else if ($act_data["status"] == "CANCEL")
				{
					$data["act_hold_cancel"]++;
				}
			}

			$data["act_hold_open"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_open"]);
			$data["act_hold_proceed"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_proceed"]);
			$data["act_hold_close"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pasthold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_close"]);
			$data["act_hold_cancel"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancelhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_cancel"]);

			$sql  = "SELECT SUM(`c`.`fees`) ";
			$sql .= "FROM `charge_ibon` c ";
			$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `c`.`aid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' AND `c`.`pay_time` IS NOT NULL";
			$linkmysql->query($sql);

			list($data["act_charge_income"]) = mysql_fetch_array($linkmysql->listmysql);

			if ($data["act_charge_income"] == "")
			{
				$data["act_charge_income"] = 0;
			}

			// 我介紹的朋友人數
			$sql = "SELECT COUNT(*) FROM `introduction` WHERE `intro_uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["intro_count"]) = mysql_fetch_array($linkmysql->listmysql);

			// 透過我報名的人數
			$sql = "SELECT `count` FROM `recommand` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["intro_join"]) = mysql_fetch_array($linkmysql->listmysql);

			if ($data["intro_join"] == "")
			{
				$data["intro_join"] = "0";
			}

			$linkmysql->close_mysql();

			$tpl->assign("memberdata", $data);
			$tpl->assign("mainpage", "member/member.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該會員資料", "回到會員中心", "index.php?act=membercenter");
		}
	}
	else if ($_GET["sel"] == "blacklist" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 黑名單列表
		//----------------------------------------

		$linkmysql->init();

		if (!isset($_GET['type'])) {
			$type = "All";
		} else {
			$type = $_GET['type'];
		}

		if ($type == "All") {
			$sql = "SELECT COUNT(*) FROM `blacklist`";
		} else if ($type == "Unverify") {
			$sql = "SELECT COUNT(*) FROM `blacklist` WHERE `blacklist`.`result` = ''";
		} else if ($type == "Verify") {
			$sql = "SELECT COUNT(*) FROM `blacklist` WHERE `blacklist`.`result` != ''";
		} else if ($type == "Lock") {
			$sql = "SELECT COUNT(*) FROM `blacklist` WHERE `blacklist`.`lock` = 'true'";
		} else if ($type == "Unlock") {
			$sql = "SELECT COUNT(*) FROM `blacklist` WHERE `blacklist`.`lock` = 'false'";
		}

		$linkmysql->query($sql);
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));

		// 分頁設定
		$itemperpage = 15;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );

		if ($type == "All")
		{
			$sql  = "SELECT `b`.`black_serial`, `b`.`black_id`, `u`.`username`, `b`.`accuse_time`, ";
			$sql .= "`b`.`result`, `b`.`start_date`, `b`.`days`, `b`.`lock`, ";
			$sql .= "FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
			$sql .= "FROM `blacklist` b ";
			$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `b`.`black_id` ";
			$sql .= "ORDER BY `b`.`black_id` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "./index.php?act=membercenter&amp;sel=blacklist&amp;type=All";

			$options  = "<b>所有的名單</b> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unverify\">未審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Verify\">已審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Lock\">未解除的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unlock\">已解除的名單</a> ";
		}
		else if ($type == "Unverify")
		{
			$sql  = "SELECT `b`.`black_serial`, `b`.`black_id`, `u`.`username`, `b`.`accuse_time`, ";
			$sql .= "`b`.`result`, `b`.`start_date`, `b`.`days`, `b`.`lock`, ";
			$sql .= "FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
			$sql .= "FROM `blacklist` b ";
			$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `b`.`black_id` ";
			$sql .= "WHERE `b`.`result` IS NULL ";
			$sql .= "ORDER BY `b`.`black_id` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unverify";

			$options  = "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=All\">所有的名單</a> | \n";
			$options .= "<b>未審核的名單</b> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Verify\">已審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Lock\">未解除的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unlock\">已解除的名單</a> ";
		}
		else if ($type == "Verify")
		{
			$sql  = "SELECT `b`.`black_serial`, `b`.`black_id`, `u`.`username`, `b`.`accuse_time`, ";
			$sql .= "`b`.`result`, `b`.`start_date`, `b`.`days`, `b`.`lock`, ";
			$sql .= "FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
			$sql .= "FROM `blacklist` b ";
			$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `b`.`black_id` ";
			$sql .= "WHERE `b`.`result` IS NOT NULL ";
			$sql .= "ORDER BY `b`.`black_id` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "./index.php?act=membercenter&amp;sel=blacklist&amp;type=Verify";

			$options  = "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=All\">所有的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unverify\">未審核的名單</a> | \n";
			$options .= "<b>已審核的名單</b> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Lock\">未解除的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unlock\">已解除的名單</a> ";
		}
		else if ($type == "Lock")
		{
			$sql  = "SELECT `b`.`black_serial`, `b`.`black_id`, `u`.`username`, `b`.`accuse_time`, ";
			$sql .= "`b`.`result`, `b`.`start_date`, `b`.`days`, `b`.`lock`, ";
			$sql .= "FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
			$sql .= "FROM `blacklist` b ";
			$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `b`.`black_id` ";
			$sql .= "WHERE `b`.`lock` = 'true' ";
			$sql .= "ORDER BY `b`.`black_id` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "./index.php?act=membercenter&amp;sel=blacklist&amp;type=Lock";

			$options  = "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=All\">所有的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unverify\">未審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Verify\">已審核的名單</a> | \n";
			$options .= "<b>未解除的名單</b> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unlock\">已解除的名單</a> ";
		}
		else if ($type == "Unlock")
		{
			$sql  = "SELECT `b`.`black_serial`, `b`.`black_id`, `u`.`username`, `b`.`accuse_time`, ";
			$sql .= "`b`.`result`, `b`.`start_date`, `b`.`days`, `b`.`lock`, ";
			$sql .= "FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
			$sql .= "FROM `blacklist` b ";
			$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `b`.`black_id` ";
			$sql .= "WHERE `b`.`lock` = 'false' ";
			$sql .= "ORDER BY `b`.`black_id` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unlock";

			$options  = "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=All\">所有的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Unverify\">未審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Verify\">已審核的名單</a> | \n";
			$options .= "<a href=\"./index.php?act=membercenter&amp;sel=blacklist&amp;type=Lock\">未解除的名單</a> | \n";
			$options .= "<b>已解除的名單</b> ";
		}

		$linkmysql->query($sql);

		$blacklistdata = array();
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = $tool->ShowMemberLink( $data["black_id"], $data["username"]);
			$data["detail"] = sprintf("<a href=\"./index.php?act=membercenter&amp;sel=blackdetail&amp;black_serial=%d\">檢視</a>", $data["black_serial"]);

			if ($data["lock"] == "true") {
				$data["lock"] = "<font color=\"red\">停權</font>";
			} else if ($data["lock"] == "false") {
				$data["lock"] = "<font color=\"green\">已解除</font>";
			}
			else
			{
				$data["lock"] = "--";
			}

			if ($data["result"] == "Pass")
			{
				$data["result"] = "<font color=\"green\">通過</font>";
			}
			else if ($data["result"] == "Refuse")
			{
				$data["result"] = "<font color=\"red\">拒絕</font>";
				$data["start_date"] = "--";
				$data["end_date"] = "--";
				$data["days"] = "--";
			}
			else
			{
				$data["result"] = "<font color=\"blue\">尚未審核</font>";
				$data["start_date"] = "--";
				$data["end_date"] = "--";
				$data["days"] = "--";
			}

			array_push( $blacklistdata, $data);
		}

		$linkmysql->close_mysql();

		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);

		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);

		$tpl->assign("options", $options);
		$tpl->assign("list_count", count($blacklistdata));
		$tpl->assign("blacklistdata", $blacklistdata);
		$tpl->assign("mainpage", "member/member.blacklist.html");
	}
	else if ($_GET["sel"] == "blackdetail")
	{
		//----------------------------------------
		// 黑名單詳細資料
		//----------------------------------------

		$black_serial = $_GET["black_serial"];

		$linkmysql->init();

		$sql  = "SELECT `b`.*, `u1`.`username` AS `black_name`, `u2`.`username` AS `accuse_name`, `u3`.`username` AS `verify_name`, ";
		$sql .= "`a`.`name` AS `act_name`, FROM_DAYS( TO_DAYS( `b`.`start_date` ) + `b`.`days` ) AS `end_date` ";
		$sql .= "FROM `blacklist` b ";
		$sql .= "LEFT JOIN `user` u1 ON `u1`.`uid` = `b`.`black_id` ";
		$sql .= "LEFT JOIN `user` u2 ON `u2`.`uid` = `b`.`accuse_id` ";
		$sql .= "LEFT JOIN `user` u3 ON `u3`.`uid` = `b`.`verify_id` ";
		$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `b`.`aid` ";
		$sql .= "WHERE `b`.`black_serial` = '$black_serial' ";

		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["act_name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["act_name"]);

			if ($data["result"] == "" && $_SESSION["authority"] == "Admin") {
				$data["verified"] = 0;
			} else {
				$data["verified"] = 1;
			}

			$sql  = "SELECT * ";
			$sql .= "FROM `blacklist` ";
			$sql .= "WHERE `black_id`='" . $data["black_id"] . "' ";
			$sql .= "AND `black_serial` != '" . $data["black_serial"] . "' ";
			$sql .= "ORDER BY accuse_time ASC ";

			$linkmysql->query($sql);

			$data["blackrecord"] = "";

			while ($blackdata = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($blackdata["result"] == "Pass")
				{
					$blackdata["result"] = "<font color=\"green\">通過</font>";
				}
				else if ($blackdata["result"] == "Refuse")
				{
					$blackdata["result"] = "<font color=\"red\">拒絕</font>";
				}
				else
				{
					$blackdata["result"] = "<font color=\"blue\">未審核</font>";
					$blackReviewing = 1;
				}

				$detail = sprintf("<a href=\"./index.php?act=membercenter&amp;sel=blackdetail&amp;black_serial=%d\">檢視</a>", $blackdata["black_serial"]);
				$data["blackrecord"] .= sprintf("提出時間: %s %s %s<br/>", $blackdata["accuse_time"], $blackdata["result"], $detail);
			}

			if ($data["blackrecord"] == "")
			{
				$data["blackrecord"] = "無";
			}

			$data["reason"] = nl2br($tool->AddLink2Text($data["reason"]));
			$data["black_name"] = $tool->ShowMemberLink( $data["black_id"], $data["black_name"]);
			$data["accuse_name"] = $tool->ShowMemberLink( $data["accuse_id"], $data["accuse_name"]);

			if ($data["result"] == "")
			{
				$data["verify_name"] = "--";
				$data["verify_time"] = "--";
				$data["comment"] = "--";
				$data["days"] = "--";
			}
			else
			{
				$data["black_name"] = $tool->ShowMemberLink( $data["black_id"], $data["black_name"]);
				$data["accuse_name"] = $tool->ShowMemberLink( $data["accuse_id"], $data["accuse_name"]);
				$data["verify_name"] = $tool->ShowMemberLink( $data["verify_id"], $data["verify_name"]);
				$data["comment"] = nl2br($tool->AddLink2Text($data["comment"]));
			}

			if ($data["lock"] == "true")
			{
				$data["lock"]  = "<font color=\"red\">停權中</fon>";
				$data["lock"] .= "&nbsp;&nbsp;&nbsp;&nbsp;";
				$data["lock"] .= sprintf("<a href=\"member.act.php?act=Unlock&amp;black_serial=%d\">解除</a>", $data["black_serial"]);
			}
			else if ($data["lock"] == "false")
			{
				$data["lock"] = "<font color=\"green\">已解除</fon>";
			}
			else
			{
				$data["lock"] = "--";
				$data["start_date"] = "--";
				$data["end_date"] = "--";
			}

			if ($data["result"] == "Pass") {
				$data["result"] = "<font color=\"green\">通過</fon>";
			} else if ($data["result"] == "Refuse") {
				$data["result"] = "<font color=\"red\">拒絕</fon>";
			} else {
				$data["result"] = "<font color=\"blue\">尚未審核</fon>";
			}

			$linkmysql->close_mysql();

			$tpl->assign("blackdata", $data);
			$tpl->assign("mainpage", "member/member.blackdetail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到黑名單資料");
		}
	}
	else if ($_GET["sel"] == "sendmail" || $_GET["sel"] == "sendmessage" || $_GET["sel"] == "exportmail" || $_GET["sel"] == "exportphone")
	{
		//-------------------------------------------------
		// 寄送信件、寄送簡訊及匯出email
		//-------------------------------------------------

		if ($_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("限定管理者才可使用");
		}

		$linkmysql->init();
		$where_clause = "";

		$condiction = $_SESSION["search"];

		$where_clause  = "WHERE `username` LIKE '%". $condiction["username"] ."%' ";
		$where_clause .= "AND `nickname` LIKE '%". $condiction["nickname"] ."%' ";
		$where_clause .= "AND `realname` LIKE '%". $condiction["realname"] ."%' ";

		if ($condiction["sex"] != "不限")
			$where_clause .= "AND `sex` = '". $condiction["sex"] ."' ";

		if ($condiction["birth_year"] != "不限")
			$where_clause .= "AND `birth_year` = '". $condiction["birth_year"] ."' ";

		if ($condiction["birth_month"] != "不限")
			$where_clause .= "AND `birth_month` = '". $condiction["birth_month"] ."' ";

		if ($condiction["birth_day"] != "不限")
			$where_clause .= "AND `birth_day` = '". $condiction["birth_day"] ."' ";

		$year = date("Y");

		if ($condiction["age_lb"] != "")
			$where_clause .= "AND '$year' - `birth_year` >= '". $condiction["age_lb"] ."' ";

		if ($condiction["age_ub"] != "")
			$where_clause .= "AND '$year' - `birth_year` <= '". $condiction["age_ub"] ."' ";

		if ($condiction["email"] != "")
			$where_clause .= "AND `email` LIKE '%". $condiction["email"] ."%' ";

		if ($condiction["msn"] != "")
			$where_clause .= "AND `msn` LIKE '%". $condiction["msn"] ."%' ";

		if ($condiction["tel"] != "")
			$where_clause .= "AND `tel` LIKE '%". $condiction["tel"] ."%' ";

		if (count($condiction["constellation"]) > 0 && count($condiction["constellation"]) != 12)
		{
			$where_clause .= "AND (`constellation` = '". $condiction["constellation"][0] ."' ";

			for ($i=1; $i<count($condiction["constellation"]); $i++)
				$where_clause .= "OR `constellation` = '". $condiction["constellation"][$i] ."' ";

			$where_clause .= ") ";
		}

		if (count($condiction["education"]) > 0 && count($condiction["education"]) != 7)
		{
			$where_clause .= "AND (`education` = '". $condiction["education"][0] ."' ";

			for ($i=1; $i<count($condiction["education"]); $i++)
				$where_clause .= "OR `education` = '". $condiction["education"][$i] ."' ";

			$where_clause .= ") ";
		}

		if ($condiction["top_education"] != "")
			$where_clause .= "AND `top_education` LIKE '%". $condiction["top_education"] ."%' ";

		if (count($condiction["interest"]) > 0)
		{
			$where_clause .= "AND (`interest` LIKE '%". $condiction["interest"][0] ."%' ";

			for ($i=1; $i<count($condiction["interest"]); $i++)
				$where_clause .= "OR `interest` LIKE '%". $condiction["interest"][$i] ."%' ";

			$where_clause .= ") ";
		}

		if (count($condiction["career"]) > 0)
		{
			$where_clause .= "AND (`career` LIKE '%". $condiction["career"][0] ."%' ";

			for ($i=1; $i<count($condiction["career"]); $i++)
				$where_clause .= "OR `career` LIKE '%". $condiction["career"][$i] ."%' ";

			$where_clause .= ") ";
		}

		if (count($condiction["inhabit"]) > 0)
		{
			$where_clause .= "AND (`inhabit` LIKE '%". $condiction["inhabit"][0] ."%' ";

			for ($i=1; $i<count($condiction["inhabit"]); $i++)
				$where_clause .= "OR `inhabit` LIKE '%". $condiction["inhabit"][$i] ."%' ";

			$where_clause .= ") ";
		}

		if ($condiction["status"] != "不限")
			$where_clause .= "AND `status` = '". $condiction["status"] ."' ";

		if (count($condiction["authority"]) > 0 && count($condiction["authority"]) != 3)
		{
			$where_clause .= "AND (`authority` = '". $condiction["authority"][0] ."' ";

			for ($i=1; $i<count($condiction["authority"]); $i++)
				$where_clause .= "OR `authority` = '". $condiction["authority"][$i] ."' ";

			$where_clause .= ") ";
		}

		if ($condiction["blackstatus"] != "不限")
		{
			if ($condiction["blackstatus"] == "有案底")
			{
				$where_clause .= "AND `uid` IN ( ";
				$where_clause .= "SELECT `black_id` FROM `blacklist` ";
				$where_clause .= "WHERE `result` = 'Pass' AND `lock` = 'false' )";
			}
			else if ($condiction["blackstatus"] == "停權中")
			{
				$where_clause .= "AND `uid` IN ( ";
				$where_clause .= "SELECT `black_id` FROM `blacklist` ";
				$where_clause .= "WHERE `result` = 'Pass' AND `lock` = 'true' )";
			}
		}

		$sql  = "SELECT * ";
		$sql .= "FROM `user` ";
		$sql .= $where_clause;
		$sql .= " ORDER BY `uid` ASC ";
		$linkmysql->query($sql);

		$memberlist = array();

		if ($_GET["sel"] == "sendmail" || $_GET["sel"] == "exportmail" || $_GET["sel"] == "exportphone")
		{
			while ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				$data['promote'] = ($data['promote'] == 'OK') ? 'checked' : '';
				array_push($memberlist, $data);
			}
		}
		else if ($_GET["sel"] == "sendmessage")
		{
			while ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				$phone = explode("-", $data["tel"]);

				if ($phone[0][0] == "0" && $phone[0][1] == "9")
				{
					$data["tel"] = $phone[0].$phone[1].$phone[2];
					array_push($memberlist, $data);
				}
			}
		}

		if (count($memberlist) == 0)
		{
			$tool->ShowMsgPage("找不到符合的會員", "回到會員篩選頁面", "index.php?act=membercenter&sel=list&filter=search");
		}

		if ($_GET["sel"] == "sendmail")
		{
			//-------------------------------------------------
			// 寄送信件
			//-------------------------------------------------
			unset($_SESSION["search"]);

			$tpl->assign("memberlist", $memberlist);
			$tpl->assign("mainpage", "member/member.sendmail.html");
		}
		else if ($_GET["sel"] == "sendmessage")
		{
			//-------------------------------------------------
			// 傳送簡訊
			//-------------------------------------------------
			unset($_SESSION["search"]);

			$tpl->assign("sms_point", $iFSMS->query_point());
			$tpl->assign("memberlist", $memberlist);
			$tpl->assign("mainpage", "member/member.sendmessage.html");
		}
		else if ($_GET["sel"] == "exportmail")
		{
			//-------------------------------------------------
			// 匯出篩選出的email為CSV檔
			//-------------------------------------------------
			unset($_SESSION["search"]);

			$filename = date('Ymd').'_iFUserEmail.csv';
			$fp = fopen("upload/".$filename, 'w');

			$str = "頭銜,名字,中間名,姓氏,稱謂,公司,部門,職稱,商務 - 街,商務 - 街 2,商務 - 街 3,商務 - 市/鎮,商務 - 縣/市,商務 - 郵遞區號,商務 - 國家/地區,住家 - 街,住家 - 街 2,住家 - 街 3,住家 - 市/鎮,住家 - 縣/市,住家 - 郵遞區號,住家 - 國家/地區,其他 - 街,其他 - 街 2,其他 - 街 3,其他 - 市/鎮,其他 - 縣/市,其他 - 郵遞區號,其他 - 國家,助理電話,商務傳真,商務電話,商務電話 2,回撥電話,汽車電話,公司代表線,住家傳真,住家電話,住家電話 2,ISDN,行動電話,其他傳真,其他電話,呼叫器,代表電話,無線電話,TTY/TDD 電話,Telix,子女,公司 ID,公司地址郵政信箱,引用,主管名稱,生日,目錄伺服器,地點,住家地址郵政信箱,助理,私人,身份證字號,使用者 1,使用者 2,使用者 3,使用者 4,其他地址郵政信箱,性別,津貼,紀念日,記事,配偶,專業,帳目資訊,帳號,敏感度,嗜好,電子郵件地址,電子郵件類型,電子郵件顯示名稱,電子郵件 2 地址,電子郵件 2 類型,電子郵件 2 顯示名稱,電子郵件 3 地址,電子郵件 3 類型,電子郵件 3 顯示名稱,網頁,網際網路空閒-忙碌中,語言,辦公室,優先順序,縮寫,關鍵字,類別\n";
			fwrite($fp, utf8_2_big5($str));

			foreach($memberlist as $member)
			{
				$str = sprintf(",%s,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,%s,,,,,,,,,,,,,,,,\n", $member["realname"], $member["email"]);
				fwrite($fp, utf8_2_big5($str));
			}

			fclose($fp);

			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=" . urlencode($filename));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");
			header("Content-Length: " . filesize("upload/".$filename));

			$fp = fopen("upload/".$filename, "r");

			while (!feof($fp))
			{
				echo fread($fp, 65536);
				flush();
			}

			fclose($fp);
		}
		else if ($_GET["sel"] == "exportphone")
		{
			//-------------------------------------------------
			// 匯出 手機號碼 資料為純文字檔
			//-------------------------------------------------

			$str = '';

			foreach($memberlist as $member)
			{
				$phone = explode("-", $member["tel"]);

				if ($phone[0][0] == "0" && $phone[0][1] == "9")
				{
					$str .= sprintf("%s,", $phone[0].$phone[1].$phone[2]);
				}
			}

			$filename = date('Ymd').'_iFUserPhone.txt';
			$fp = fopen("upload/".$filename, 'w');
			fwrite($fp, utf8_2_big5($str));
			fclose($fp);

			header("Content-type: text/txt");
			header("Content-Disposition: attachment; filename=" . urlencode($filename));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");
			header("Content-Length: " . filesize("upload/".$filename));

			$fp = fopen("upload/".$filename, "r");

			while (!feof($fp))
			{
				echo fread($fp, 65536);
				flush();
			}

			fclose($fp);
		}
	}
?>