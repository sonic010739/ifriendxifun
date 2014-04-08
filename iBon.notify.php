<?
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	require "smarty.lib.php";
	
	$linkmysql->init();
	
	$time_start = microtime_float();
	
	// 取出可以報名的活動
	$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `p`.`placename`, `t`.`tname` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
	$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
	$sql .= "WHERE `a`.`status` = 'OPEN' ";
	$sql .= "AND TO_DAYS(`a`.`act_date`)-2 >= TO_DAYS(NOW()) ";
	$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";
	$linkmysql->query($sql);
	
	$actlist = array();
	
	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{	
		array_push($actlist, $data);
	}
	
	foreach ($actlist as $act)
	{
		// 信件關於活動部分的資訊
		$mailinfo = array();			
		$mailinfo["act_date"] = $act["act_date"];
		$mailinfo["act_time"] = $act["act_time"];
		$mailinfo["act_place"] = $act["placename"];
		$mailinfo["act_name"] = $act["name"];
		
		// iF繳費期限
		$tmp = explode("-", $act["act_date"]);
		$iFDeadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-2, $tmp[0])); 
		$iFDeadline2 = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-6, $tmp[0])); 
		$iF_expired = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0])); 
		
		$aid = $act["aid"];
		
		// 取出有登記報名活動的會員
		$sql  = "SELECT `aj`.`charge_type`, `aj`.`charge_id`, `u`.`username`, `u`.`realname`, `u`.`email` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' AND `aj`.`charge_type` = 'iBon' ";
		$sql .= "ORDER BY `u`.`sex` ASC, `aj`.`serial` ASC";		
		$linkmysql->query($sql);
		
		$members = array();
		
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
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
					$ibonDeadline = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
					
					// 繳費期限前72小時
					$ibonDeadline2 = date("Y-m-d", mktime(0, 0, 0, $matche[2], $matche[3]+4, $matche[1]));
					
					// iF繳費期限前72小時
					$iFDeadline2 = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-6, $tmp[0])); 
					
					if (date("Y-m-d") == $iF_expired)
					{
						//寄送已超過iF繳費期限信件
						$mailinfo["realname"] = $user["realname"];
						$mailinfo["ibon_deadline"] = $ibonDeadline;
						$mailinfo["ibon_code"] = $ibon_no;
						$mailinfo["iF_deadline"] = $iFDeadline;
						
						$iFMail->PayDeadlineMailC($user["email"], $user["username"], $mailinfo);
					}
					else if (date("Y-m-d") == $iFDeadline2)
					{
						// iF繳費期限前72小時
						//寄送iF繳費提醒信件
						$mailinfo["realname"] = $user["realname"];
						$mailinfo["ibon_deadline"] = $ibonDeadline;
						$mailinfo["ibon_code"] = $ibon_no;
						$mailinfo["iF_deadline"] = $iFDeadline;
							
						$iFMail->PayDeadlineMailA($user["email"], $user["username"], $mailinfo);
					}
					else if (date("Y-m-d") == $ibonDeadline2)
					{
						// 繳費期限前72小時
						//寄送iF繳費提醒信件
						$mailinfo["realname"] = $user["realname"];
						$mailinfo["ibon_deadline"] = $ibonDeadline;
						$mailinfo["ibon_code"] = $ibon_no;
						$mailinfo["iF_deadline"] = $iFDeadline;
						
						$iFMail->PayDeadlineMailA($user["email"], $user["username"], $mailinfo);
					}
					else if ($ibonDeadline < date("Y-m-d H:i:s"))
					{
						//寄送繳費代碼已過期信件
						$mailinfo["realname"] = $user["realname"];
						$mailinfo["ibon_deadline"] = $ibonDeadline;
						$mailinfo["ibon_code"] = $ibon_no;
						$mailinfo["iF_deadline"] = $iFDeadline;
							
						$iFMail->PayDeadlineMailB($user["email"], $user["username"], $mailinfo);
					}
				}
			}
		}
	}	
	$linkmysql->close_mysql();
	
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	
	$fp = fopen ("/var/www/vhosts/ifriendxifun.net/httpdocs/log/iBonNotify_log.txt", "a+");	
	fwrite($fp, date("Y-m-d H:i:s") . " Send iBon Notify Mail in $time seconds\n");
	fclose($fp);
	
	print date("Y-m-d H:i:s") . " Send iBon Notify Mail in $time seconds\n";
	
?>