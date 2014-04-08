<?
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	require "smarty.lib.php";

	$time_start = microtime_float();

	$linkmysql->init();
	$act_data = array();

	$sql  = "SELECT * FROM `activitie` ";
	$sql .= "WHERE `status` = 'OPEN' ";
	$sql .= "ORDER BY `act_date` ASC ";
	$linkmysql->query($sql);

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["name"] = sprintf("<a href=\"%sindex.php?act=activities&amp;sel=detail&amp;aid=%d\" target=\"_blank\">%s</a>",
			$config["base_url"], $data["aid"], $data["name"]);
		array_push($act_data, $data);
	}

	for ($i = 0; $i < count($act_data); $i++)
	{
		$aid = $act_data[$i]['aid'];

		// 昨日累積報名人數
		$act_data[$i]['join_males'] = 0;
		$act_data[$i]['join_females'] = 0;

		// 目前繳費人數
		$act_data[$i]['paid_males'] = 0;
		$act_data[$i]['paid_females'] = 0;

		// 未繳費人數
		$act_data[$i]['unpay_males'] = 0;
		$act_data[$i]['unpay_females'] = 0;

		// 目前繳費金額
		$act_data[$i]['paid_amount'] = 0;

		// 昨日累積報名人數 (男)
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND (TO_DAYS(NOW()) - TO_DAYS(`aj`.`join_time`) > 1) ";
		$sql .= "AND `u`.`sex` = '男' ";
		$sql .= "AND `aj`.`join_status` = 'join'";
		$linkmysql->query($sql);

		list($act_data[$i]['join_males']) = mysql_fetch_array($linkmysql->listmysql);

		// 昨日累積報名人數 (女)
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND (TO_DAYS(NOW()) - TO_DAYS(`aj`.`join_time`) > 1) ";
		$sql .= "AND `u`.`sex` = '女' ";
		$sql .= "AND `aj`.`join_status` = 'join'";
		$linkmysql->query($sql);

		list($act_data[$i]['join_females']) = mysql_fetch_array($linkmysql->listmysql);

		// 新增報名人數 = 目前報名人數 - 昨日報名累積人數
		$act_data[$i]['today_join_males'] = $act_data[$i]['males'] - $act_data[$i]['join_males'];
		$act_data[$i]['today_join_females'] = $act_data[$i]['females'] - $act_data[$i]['join_females'];

		$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, ";
		$sql .= "`aj`.`join_time`, `u`.`sex` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$linkmysql->query($sql);

		$join_data = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			array_push( $join_data, $data);
		}

		foreach ($join_data as $joindata)
		{
			// 統計繳費方式及繳費狀態
			if ($joindata["charge_type"] == "iBon")
			{
				$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $joindata["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

				if ($joindata["sex"] == "男" && empty($pay_time))
				{
					$act_data[$i]['unpay_males']++;
				}
				else if ($joindata["sex"] == "男" && !empty($pay_time))
				{
					$act_data[$i]['paid_males']++;
					$act_data[$i]['paid_amount'] += $fees;
				}
				else if ($joindata["sex"] == "女" && empty($pay_time))
				{
					$act_data[$i]['unpay_females']++;
				}
				else if ($joindata["sex"] == "女" && !empty($pay_time))
				{
					$act_data[$i]['paid_females']++;
					$act_data[$i]['paid_amount'] += $fees;
				}
			}
			else if ($joindata["charge_type"] == "coupon")
			{
				if ($joindata["sex"] == "男")
				{
					$act_data[$i]['paid_males']++;
				}
				else if ($joindata["sex"] == "女")
				{
					$act_data[$i]['paid_females']++;
				}
			}
		}
	}

	// 取出所有管理員
	$sql  = "SELECT * ";
	$sql .= "FROM `user` ";
	$sql .= "WHERE `authority` = 'Admin' ";
	$sql .= "ORDER BY `uid` ASC ";
	$linkmysql->query($sql);

	$userlist = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		array_push($userlist, $data);
	}

	$linkmysql->close_mysql();

	$time_end = microtime_float();
	$time = $time_end - $time_start;

	$time_start = microtime_float();

	// 寄送活動報名情況統計信件
	if (count($act_data) > 0)
	{
		$mailinfo = array();
		$mailinfo["actlist"] = $act_data;
		$mailinfo['execution_time'] = $time;

		foreach ($userlist as $user)
		{
			$mailinfo["username"] = $user["username"];
			$mailinfo["realname"] = $user["realname"];

			$iFMail->ActJoinSTATMail($user["email"], $user["username"], $mailinfo);
		}

		unset($mailinfo);
	}

	$time_end = microtime_float();
	$time = $time_end - $time_start;

	print date("Y-m-d H:i:s") . " Send Act Join STAT Mail in $time seconds\n";
?>