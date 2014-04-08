<?
	// 定時執行的程式，每天早上九點執行
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	require "smarty.lib.php";

	$linkmysql->init();

	$time_start = microtime_float();

	// 取出可以報名的活動
	$sql  = "SELECT `aid`, `name`, `act_date`, `act_time` ";
	$sql .= "FROM `activitie` ";
	$sql .= "WHERE `status` = 'OPEN' ";
	$sql .= "AND TO_DAYS(`act_date`)-3 >= TO_DAYS(NOW()) ";
	$sql .= "ORDER BY `act_date` ASC, `aid` DESC ";
	$linkmysql->query($sql);

	$actlist = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		array_push($actlist, $data);
	}

	foreach ($actlist as $act)
	{
		// iF繳費期限
		$tmp = explode("-", $act["act_date"]);
		$Deadline1 = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-5, $tmp[0]));
		$Deadline2 = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-3, $tmp[0]));
		$iF_expired = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));

		$aid = $act["aid"];

		// 取出有登記報名活動的會員
		$sql  = "SELECT `aj`.`charge_type`, `aj`.`charge_id`, `u`.`tel` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' AND `aj`.`charge_type` = 'iBon' ";
		$sql .= "ORDER BY `u`.`sex` ASC, `aj`.`serial` ASC";
		$linkmysql->query($sql);

		$members = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$phone = explode("-", $data["tel"]);
			$data["tel"] = $phone[0].$phone[1].$phone[2];
			array_push($members, $data);
		}

		foreach ($members as $user)
		{
			$sql = sprintf("SELECT `pay_time`, `ibon_no`, `process_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $user["charge_id"]);
			$linkmysql->query($sql);
			list($pay_time, $ibon_no, $process_time) = mysql_fetch_array($linkmysql->listmysql);

			if (empty($pay_time))
			{
				if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $process_time, $matche))
				{
					// iBon繳費期限
					$days = ($matche[4] + 3 >= 24) ? 6 : 7;
					$ibonDeadline = date("Y-m-d", mktime(0, 0, 0, $matche[2], $matche[3]+$days, $matche[1]));

					if (date("Y-m-d") > $ibonDeadline)
					{
					}
					if (date("Y-m-d") == $Deadline1)
					{
						$dstaddr = $user['tel'];
						$iBon_code = $ibon_no;
						$deadline = ($iF_expired < $ibonDeadline) ? $iF_expired : $ibonDeadline;
						
						print "send NotifyA to $dstaddr code: $iBon_code Deadline: $deadline<br />\n";
						$iFSMS->iBonNotifyA($dstaddr, $iBon_code, $deadline);
					}
					else if (date("Y-m-d") == $Deadline2)
					{
						$dstaddr = $user['tel'];
						$iBon_code = $ibon_no;

						print "Send NotifyB to $dstaddr code: $iBon_code Deadline: $Deadline2<br />\n";
						$iFSMS->iBonNotifyB($dstaddr, $iBon_code);
					}
				}
			}
		}
	}

	$linkmysql->close_mysql();

	$time_end = microtime_float();
	$time = $time_end - $time_start;

	$fp = fopen ("/var/www/vhosts/ifriendxifun.net/httpdocs/log/iBonSMSNotify_log.txt", "a+");
	fwrite($fp, date("Y-m-d H:i:s") . " Send iBon Notify SMS in $time seconds\n");
	fclose($fp);

	print "Send iBon Notify SMS in $time seconds\n";
?>