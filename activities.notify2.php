<?
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	require "smarty.lib.php";
		
	$linkmysql->init();
	
	$time_start = microtime_float();
	
	// 取出 1, 3, 7天後進行的活動
	$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `p`.`placename`, ";
	$sql .= "`u`.`username`, `u`.`realname`, `u`.`email` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
	$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
	$sql .= "WHERE `a`.`status` = 'OPEN' ";
	$sql .= "AND ( ";
	$sql .= "TO_DAYS(`a`.`act_date`)-1 = TO_DAYS(NOW()) ";
	$sql .= "OR TO_DAYS(`a`.`act_date`)-3 = TO_DAYS(NOW()) ";
	$sql .= "OR TO_DAYS(`a`.`act_date`)-7 = TO_DAYS(NOW())) ";
	$sql .= "ORDER BY `a`.`aid` DESC ";		
	$linkmysql->query($sql);
	
	$actlist = array();
	
	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{	
		array_push($actlist, $data);
	}
	
	$linkmysql->close_mysql();
	
	// 寄送EO活動提醒信件
	foreach ($actlist as $act)
	{		
		$mailinfo = array();
		$mailinfo["realname"] = $act["realname"];
		$mailinfo["act_date"] = $act["act_date"];	
		$mailinfo["act_time"] = $act["act_time"];
		$mailinfo["act_place"] = $act["placename"];	
		$mailinfo["act_name"] = $act["name"];
			
		$iFMail->EOActNotifyMail($act["email"], $act["username"], $mailinfo);		
	}

	$time_end = microtime_float();
	$time = $time_end - $time_start;
	
	$fp = fopen ("/var/www/vhosts/ifriendxifun.net/httpdocs/log/EONotify_log.txt", "a+");	
	fwrite($fp, date("Y-m-d H:i:s") . " Send EO Notify Mail in $time seconds\n");
	fclose($fp);
	
	print date("Y-m-d H:i:s") . " Send EO Notify Mail in $time seconds\n";
?>
