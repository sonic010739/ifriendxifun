<?
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	set_time_limit(9999);
	
	require "smarty.lib.php";
		
	$linkmysql->init();
	
	// 取出未來一個月內可報名的活動
	$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `t`.`tname`  ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
	$sql .= "WHERE `a`.`status` = 'OPEN' ";
	$sql .= "AND TO_DAYS(`a`.`act_date`)-2 >= TO_DAYS(NOW()) ";
	$sql .= "AND TO_DAYS(`a`.`act_date`) <= TO_DAYS(NOW())+30 ";
	$sql .= "ORDER BY `a`.`act_date` ASC, `a`.`aid` DESC ";	
	$linkmysql->query($sql);
	
	$actlist = array();
	
	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{	
		$data["name"] = sprintf("<a href=\"%sindex.php?act=activities&amp;sel=detail&amp;aid=%d\" target=\"_blank\">%s</a>",
			$config["base_url"], $data["aid"], $data["name"]);
		array_push($actlist, $data);
	}
	
	// 取出所有已驗證的會員
	$sql  = "SELECT * ";
	$sql .= "FROM `user` ";
	$sql .= "WHERE `status` = 'Validate' AND `promote` = 'OK'";	
	$sql .= "ORDER BY `uid` ASC ";	
	$linkmysql->query($sql);
	
	$userlist = array();
	
	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{	
		array_push($userlist, $data);
	}
	
	$linkmysql->close_mysql();
	
	$time_start = microtime_float();
	
	if (count($actlist) > 0)
	{
		$mailinfo = array();
		$mailinfo["actlist"] = $actlist;
		
		foreach ($userlist as $user)
		{						
			$mailinfo["username"] = $user["username"];
			$mailinfo["realname"] = $user["realname"];			
			
			$iFMail->ActADMail($user["email"], $user["username"], $mailinfo);			
		}
		
		unset($mailinfo);
	}

	
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	
	$fp = fopen ("/var/www/vhosts/ifriendxifun.net/httpdocs/log/SendAD_log.txt", "a+");	
	fwrite($fp, date("Y-m-d H:i:s") . " Send Act AD Mail in $time seconds\n");
	fclose($fp);
	
	print date("Y-m-d H:i:s") . " Send Act AD Mail in $time seconds\n";
?>