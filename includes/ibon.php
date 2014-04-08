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
				$days = 7;
				$tmp = explode("-", $data["act_date"]);

				// 計算繳費期限
				if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]))
				{
					$data["ibon_deadline"] = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
				}
				else
				{
					$data["ibon_deadline"] = date("Y-m-d H:i:s", mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]));
				}
			}

			if (empty($data["pay_time"]))
			{
				$data["charge_status"] = "<font color=\"red\">未繳費</font>";
				$data["pay_time"] = "--";

				if (date("Y-m-d H:i:s") > $data["ibon_deadline"])
				{
					$data["reGenerate"] = sprintf("<a href=\"index.php?act=ibon&amp;sel=reGenerate&amp;uid=%d&amp;aid=%d\" onClick='return confirm(\"重新產生超商繳費代碼?\");'>重新產生超商繳費代碼</a>", $uid, $aid);
				}
				else
				{
					$data["reGenerate"] = "超商繳費代碼未失效";
				}
			}
			else
			{
				$data["charge_status"] = "<font color=\"green\">已繳費</font>";
				$data["reGenerate"] = "已使用超商代碼繳費完成";
			}

			$linkmysql->close_mysql();
			$tpl->assign("ibondata", $data);
			$tpl->assign("mainpage", "ibon.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆超商代碼繳費資料", "回到超商代碼繳費資料列表", "index.php?act=ibon&sel=list");
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

		// 取出會員資料
		$sql = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);

		if (!$member = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所指定的會員資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$uid = $member["uid"];

		// 取出活動資料
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		if (!$activitie = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所指定的活動資料");
		}

		$sql  = "SELECT `i`.*, `u`.`username`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `aj`.`join_status` ";
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
				$days = 7;
				$ibondata["ibon_deadline"] = date("Y-m-d H:i:s", mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]));
			}

			// 繳費代碼未過期
			if (date("Y-m-d H:i:s") < $ibondata["ibon_deadline"])
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("繳費代碼未過期", "回到超商代碼繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 已使用此繳費代碼付款
			if (!empty($ibondata["pay_time"]))
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("您已經使用超商代碼繳費完成",
					"回到超商代碼繳費資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 有參加此活動
			if ($ibondata["join_status"] != "join")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("已取消參加活動，無法重新產生超商繳費代碼",
					"回到超商繳費代碼資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 活動為開放報名的狀態
			if ($ibondata["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名狀態，無法重新產生超商繳費代碼",
					"回到超商繳費代碼資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 未超過活動的繳費期限
			$tmp = explode("-", $ibondata["act_date"]);
			$pay_deadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));

			if (date("Y-m-d") > $pay_deadline)
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("已超過活動的繳費期限(" . $pay_deadline . ")",
					"回到超商繳費代碼資料", "index.php?act=ibon&sel=detail&uid=$uid&aid=$aid");
			}

			// 商品說明及備註。(會出現在超商繳費平台螢幕上)

			$od_sob = $ibondata["charge_ibon_id"];
			$fees 	= $ibondata["fees"];
			$prd_desc = rawurlencode($ibondata['name']);
			$desc1 	= rawurlencode($ibondata['act_date']);
			$desc2 	= rawurlencode($ibondata['act_time']);
			$desc3 	= rawurlencode($ibondata['username']);
			$desc4 	= rawurlencode('付款後請保留繳費收據');
			$ok_url	= rawurlencode($config["base_url"] .'cvs_ok.php');

			// ECBank 超商代碼繳費代碼取號網址
			$ecbank_auth_url = 'https://ecbank.com.tw/gateway.php?payment_type=cvs' .
					'&mer_id=' 		. $config["store_no"] 	.	// 商店代號
					'&enc_key=' 	. $config['enc_key'] 	.
					'&od_sob=' 		. $od_sob				.	// iF 系統單號
					'&amt=' 		. $fees					.
					'&prd_desc=' 	. $prd_desc				.
					'&desc1=' 		. $desc1				.
					'&desc2=' 		. $desc2				.
					'&desc3=' 		. $desc3				.
					'&desc4=' 		. $desc4				.
					'&ok_url='		. $ok_url;					// 付款完成通知網址

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $ecbank_auth_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			$result = curl_exec($ch);
			
			$process_time = date("Y-m-d H:i:s");				// 處理時間
			parse_str($result, $res);

			if (!isset($res['error']) || $res['error'] != '0')
			{
				//echo 'get code error!! error: ' . $res['error'] . '<br />';
				$tool->ShowMsgPage("取得繳費代碼失敗，請洽系統管理員。");
			}

			$ibon_no 	= $res['payno'];
			$tsr		= $res['tsr'];
			
			// 刪除舊的繳費資料
			$sql = sprintf("DELETE FROM `charge_ibon` WHERE `charge_ibon_id` = '%s' LIMIT 1;", $od_sob);
			$linkmysql->query($sql);

			// 加入新的繳費資料
			$sql  = "INSERT INTO `charge_ibon` (`charge_ibon_id`, `uid`, `aid`, `fees`, `success`, ";
			$sql .= "`ibon_no` , `gwsr` , `process_time` , `pay_time` ) ";
			$sql .= "VALUES ('$od_sob', '$uid', '$aid', '$fees', 1, ";
			$sql .= "'$ibon_no', '$tsr', NOW(), NULL ); ";
			$linkmysql->query($sql);

			$linkmysql->close_mysql();

			// iF繳費截止日
			$tmp = explode("-", $activitie['act_date']);
			$iFDeadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));

			if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $process_time, $matche))
			{
				$days = 7;

				// 計算繳費期限:  $ibonDeadline: 信件用，$ibonDeadline2: 簡訊用
				if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]))
				{
					$ibonDeadline = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
					$ibonDeadline2 = date("Y/m/d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));
				}
				else
				{
					$ibonDeadline = date("Y-m-d H:i:s", mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]));
					$ibonDeadline2 = date("Y/m/d", mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]));
				}
			}

			// 取出活動資料
			$sql  = "SELECT `t`.`tname`, `a`.`act_date`, `a`.`act_time`, `a`.`name`, `p`.`placename`, `a`.`decription` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `group` g ON `a`.`group` = `g`.`gid` ";
			$sql .= "WHERE `a`.`aid` = '$aid'";
			$linkmysql->query($sql);

			list($tname, $act_date, $act_time, $name, $placename, $decription) = mysql_fetch_array($linkmysql->listmysql);

			// 寄送繳費提醒信。
			$mailinfo = array();
			$mailinfo["realname"] = $member["realname"];
			$mailinfo["act_name"] = $activitie['act_date'];
			$mailinfo["act_date"] = $act_date;
			$mailinfo["act_time"] = $act_time;
			$mailinfo["act_place"] = $placename;
			$mailinfo["ibon_code"] = $ibon_no;
			$mailinfo["ibon_deadline"] = $ibonDeadline;
			$mailinfo["iF_deadline"] = $iFDeadline;

			$iFMail->PayDeadlineMailA($member["email"], $member["realname"], $mailinfo);

			$phone = explode("-", $member["tel"]);
			$member["tel"] = $phone[0].$phone[1].$phone[2];

			// 寄送繳費通知簡訊
			$iFSMS->iBonNotify($member["tel"], $mailinfo["ibon_code"], $ibonDeadline2);

			$_SESSION["ibon_code"] = $od_sob;
			$tool->URL("index.php?act=activitiesjoin&sel=iboncode");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該筆超商代碼繳費資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
?>
