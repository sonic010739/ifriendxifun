<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	if ($_GET["sel"] == "list" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 列出所有iBon的繳費資料
		//----------------------------------------

		// Unpay 		未繳費		(預設以活動時間現在到未來排序)
		// UnpayOverdue	未繳費過期	(預設以使用者帳號排序)
		// Paid 		已繳費		(預設以活動日期現在到過去排序)

		$type = !isset($_GET['type']) ? "Unpay" : $_GET['type'];

		$linkmysql->init();

		if ($type == "Unpay")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `charge_ibon` ";
			$sql .= "WHERE `pay_time` IS NULL AND TO_DAYS(`process_time`)+7 > TO_DAYS(NOW()) ";
		}
		else if ($type == "UnpayOverdue")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `charge_ibon` ";
			$sql .= "WHERE `pay_time` IS NULL AND TO_DAYS(`process_time`)+7 <= TO_DAYS(NOW()) ";
		}
		else if ($type == "Paid")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `charge_ibon` ";
			$sql .= "WHERE `pay_time` IS NOT NULL ";
		}

		$linkmysql->query($sql);
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));

		// 分頁設定
		$itemperpage = 15;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );

		if ($type == "Unpay")
		{
			$sql  = "SELECT `i`.`uid`, `u`.`username`, `u`.`nickname`, `i`.`aid`, `a`.`name`, ";
			$sql .= "`a`.`act_date`, `i`.`fees`, `i`.`process_time`, `i`.`pay_time` ";
			$sql .= "FROM `charge_ibon` i ";
			$sql .= "LEFT JOIN `activitie` a ON `i`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `i`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `i`.`pay_time` IS NULL AND TO_DAYS(`i`.`process_time`)+7 > TO_DAYS(NOW()) ";
			$sql .= "ORDER BY `a`.`act_date` DESC, `a`.`aid` ASC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "index.php?act=ibon&sel=list&amp;type=Unpay";

			$options  = "<b>未繳費</b> |\n";
			$options .= "<a href=\"./index.php?act=ibon&sel=list&amp;type=UnpayOverdue\">未繳費過期</a> |\n";
			$options .= "<a href=\"./index.php?act=ibon&sel=list&amp;type=Paid\">已繳費</a> \n";
		}
		else if ($type == "UnpayOverdue")
		{
			$sql  = "SELECT `i`.`uid`, `u`.`username`, `u`.`nickname`, `i`.`aid`, `a`.`name`, ";
			$sql .= "`a`.`act_date`, `i`.`fees`, `i`.`process_time`, `i`.`pay_time` ";
			$sql .= "FROM `charge_ibon` i ";
			$sql .= "LEFT JOIN `activitie` a ON `i`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `i`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `i`.`pay_time` IS NULL AND TO_DAYS(`i`.`process_time`)+7 <= TO_DAYS(NOW()) ";
			$sql .= "ORDER BY `u`.`username` ASC, `a`.`aid` ASC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "index.php?act=ibon&sel=list&amp;type=UnpayOverdue";

			$options  = "<a href=\"./index.php?act=ibon&sel=list&amp;type=Unpay\">未繳費</a> |\n";
			$options .= "<b>未繳費過期</b> |\n";
			$options .= "<a href=\"./index.php?act=ibon&sel=list&amp;type=Paid\">已繳費</a> \n";
		}
		else if ($type == "Paid")
		{
			$sql  = "SELECT `i`.`uid`, `u`.`username`, `u`.`nickname`, `i`.`aid`, `a`.`name`, ";
			$sql .= "`a`.`act_date`, `i`.`fees`, `i`.`process_time`, `i`.`pay_time` ";
			$sql .= "FROM `charge_ibon` i ";
			$sql .= "LEFT JOIN `activitie` a ON `i`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `i`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `i`.`pay_time` IS NOT NULL ";
			$sql .= "ORDER BY `a`.`act_date` DESC, `a`.`aid` ASC ";
			$sql .= "LIMIT $head , $itemperpage";

			$url = "index.php?act=ibon&sel=list&amp;type=Paid";

			$options  = "<a href=\"./index.php?act=ibon&sel=list&amp;type=Unpay\">未繳費</a> |\n";
			$options .= "<a href=\"./index.php?act=ibon&sel=list&amp;type=UnpayOverdue\">未繳費過期</a> |\n";
			$options .= "<b>已繳費</b> \n";
		}

		$linkmysql->query($sql);

		$ibonlist = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["name"] = $tool->UTF8_CuttingStr($data["name"], 18);
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);

			if (empty($data["pay_time"]))
			{
				$data["charge_status"] = "<font color=\"red\">未繳費</font>";
				$data["pay_time"] = "--";
			}
			else
			{
				$data["charge_status"] = "<font color=\"green\">已繳費</font>";
			}

			if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $data["process_time"], $matche))
			{
				// iBon繳費期限
				$days = ($matche[4] + 3 >= 24) ? 6 : 7;

				$data["ibon_deadline"]  = date("Y-m-d", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
				$data["ibon_deadline"] .= "<br/>";
				$data["ibon_deadline"] .= date("H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
			}

			$data["charge_status"] = sprintf("<a href=\"index.php?act=ibon&amp;sel=detail&amp;uid=%d&amp;aid=%d\">%s</a>",
				$data["uid"], $data["aid"], $data["charge_status"]);

			array_push( $ibonlist, $data);
		}

		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);

		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);

		$tpl->assign("options", $options);
		$tpl->assign("list_title", $list_title);
		$tpl->assign("ibon_count", count($ibonlist));
		$tpl->assign("ibonlist", $ibonlist);
		$tpl->assign("mainpage", "ibon.html");
	}
	else if ($_GET["sel"] == "detail")
	{
		//----------------------------------------
		// iBon繳費詳細資料
		//----------------------------------------

		$uid = $_GET["uid"];
		$aid = $_GET["aid"];

		$linkmysql->init();
		$sql  = "SELECT `i`.*, `u`.`username`, `a`.`name`, `a`.`act_date` ";
		$sql .= "FROM `charge_ibon` i ";
		$sql .= "LEFT JOIN `activitie` a ON `i`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `i`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `i`.`uid` = $uid AND `i`.`aid` = $aid ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>",
				$data["aid"], $data["name"]);

			if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $data["process_time"], $matche))
			{
				$days = ($matche[4] + 3 >= 24) ? 6 : 7;
				$tmp = explode("-", $data["act_date"]);

				// 計算繳費期限
				if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]))
				{
					$data["ibon_deadline"] = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
				}
				else
				{
					$data["ibon_deadline"] = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
				}
			}

			if (empty($data["pay_time"]))
			{
				$data["charge_status"] = "<font color=\"red\">未繳費</font>";
				$data["pay_time"] = "--";

				if (date("Y-m-d H:i:s") > $data["ibon_deadline"])
				{
					$data["reGenerate"] = sprintf("<a href=\"index.php?act=ibon&amp;sel=reGenerate&amp;uid=%d&amp;aid=%d\" onClick='return confirm(\"重新產生iBon繳費代碼?\");'>重新產生iBon繳費代碼</a>", $uid, $aid);
				}
				else
				{
					$data["reGenerate"] = "iBon繳費代碼未失效";
				}
			}
			else
			{
				$data["charge_status"] = "<font color=\"green\">已繳費</font>";
				$data["reGenerate"] = "已使用iBon繳費完成";
			}

			$linkmysql->close_mysql();
			$tpl->assign("ibondata", $data);
			$tpl->assign("mainpage", "ibon.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆iBon繳費資料", "回到iBon繳費資料列表", "index.php?act=ibon&sel=list");
		}
	}
	else if ($_GET["sel"] == "reGenerate")
	{
		//----------------------------------------
		// 重新產生iBon繳費代碼
		//----------------------------------------

		$aid = $_GET["aid"];
		$uid = $_GET["uid"];

		$linkmysql->init();

		$sql  = "SELECT `i`.*, `u`.`username`, `a`.`name`, `a`.`act_date`, `a`.`status`, `aj`.`join_status` ";
		$sql .= "FROM `charge_ibon` i ";
		$sql .= "LEFT JOIN `activitie` a ON `i`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj ON `i`.`aid` = `aj`.`aid` AND `i`.`uid` = `aj`.`uid` ";
		$sql .= "LEFT JOIN `user` u ON `i`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `i`.`uid` = $uid AND `i`.`aid` = $aid ";
		$linkmysql->query($sql);

		if ($ibondata = mysql_fetch_array($linkmysql->listmysql))
		{
			// iBon繳費期限
			if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $ibondata["process_time"], $matche))
			{
				$days = ($matche[4] + 3 >= 24) ? 6 : 7;
				$ibondata["ibon_deadline"] = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
			}

			// 繳費代碼未過期
			if (date("Y-m-d H:i:s") < $ibondata["ibon_deadline"])
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("繳費代碼未過期", "回到iBon繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 未使用此繳費代碼付款
			if (!empty($ibondata["pay_time"]))
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("您已經使用iBon繳費完成",
					"回到iBon繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 有參加此活動
			if ($ibondata["join_status"] != "join")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("已取消參加活動，無法重新產生iBon繳費代碼",
					"回到iBon繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 活動為開放報名的狀態
			if ($ibondata["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名狀態，無法重新產生iBon繳費代碼",
					"回到iBon繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 未超過活動的繳費期限
			$tmp = explode("-", $ibondata["act_date"]);
			$pay_deadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));

			if (date("Y-m-d") > $pay_deadline)
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("已超過活動的繳費期限(" . $pay_deadline . ")",
					"回到iBon繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			$linkmysql->close_mysql();

			// 送出繳費單號
			$ibon_url = "http://ts.payonline.com.tw/ibon_echo.php";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ibon_url);
			curl_setopt($ch, CURLOPT_POST, 5);

			$post_var  = "client="  . urlencode($config["store_no"]);
			$post_var .= "&amount=" . urlencode($ibondata["fees"]);
			$post_var .= "&od_sob=" . urlencode($ibondata["charge_ibon_id"]);
			$post_var .= "&roturl=" . urlencode($config["base_url"] .'ibon_echo.php');
			$post_var .= "&okurl=" . urlencode($config["base_url"] .'ibon_ok.php');

			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_var);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);		//php safe mode can't active
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);

			$result = curl_exec ($ch);

			list($header, $data) = explode("\n\n", $result, 2);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code == 301 || $http_code == 302)
			{
				$matches = array();
				preg_match('/Location:(.*?)\n/', $header, $matches);
				$url = trim($matches[1]);

				// 顯示 iBon code 於畫面上
				header("Location: http://ts.payonline.com.tw/" . $url);
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆iBon繳費資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
?>
