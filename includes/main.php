<?
	//--------------------------------------------
	// 萬年曆程式 使用 php date function 
	// Sonic  <sonic010739@gmail.com>
	//--------------------------------------------
	
	if (!isset($_GET["y"]) && !isset($_GET["m"]))
	{
		$y = date('Y');	
		$m = date('m');
	}
	else 
	{
		$y = $_GET["y"];
		$m = $_GET["m"];
	}

	$m = $m % 13;

	$next_m = $m + 1;
	$next_y = $y;
	$previous_m = $m - 1;
	$previous_y = $y;

	if ($next_m == 13) 
	{
		$next_m = 1;
		$next_y++;
	}
	
	if ($previous_m == 0) 
	{
		$previous_m = 12;
		$previous_y--;
	}
	
	$offset = date("w" , mktime( 0, 0, 1, $m, 1, $y) );

	$calendar = "";	
	$month_eng = array("January", "February", "March", "April", "May", "June", "July", "Augest", "September", "October", "November", "December");
	
	$previous = sprintf("./index.php?y=%d&amp;m=%d", $previous_y , $previous_m );
	$current = sprintf("%s %d", $month_eng[$m - 1], $y);
	$next = sprintf("./index.php?y=%d&amp;m=%d", $next_y , $next_m );			
	
	$days = $tool->GetDays( $previous_m, $previous_y );
	$days = $days - $offset+1;
	
	for( $i=0;  $i < $offset; $i++) 
	{
		$calendar .= sprintf("<div class=\"past\">%d</div>" , $days + $i);	 
	}
	
	$days = $tool->GetDays( $m, $y );
		
	if ($_SESSION["login"] == 1) {
		$uid = $_SESSION["uid"];
	} else {
		$uid = -1;
	}	
	
	$linkmysql->init();
	
	$act_name_length = 50;
	$eventdata = array();
	
	// 已經參加的活動	
	$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, ";
	$sql .= "YEAR(`a`.`act_date`) AS `year`, MONTH(`a`.`act_date`) AS `month`, DAY(`a`.`act_date`) AS `day` ";	
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
	$sql .= "WHERE YEAR(`a`.`act_date`) = '$y' AND MONTH(`a`.`act_date`) = '$m' AND `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join'";	
	
	$linkmysql->query($sql);
	
	while( $data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["type"] = "JoinAct";
		$data["name"] = $tool->UTF8_CuttingStr($data["name"], $act_name_length);
		array_push( $eventdata, $data);
	}
	
	// 自己舉辦的活動	
	$sql  = "SELECT `aid`, `name`, `act_date`, `act_time`, `status`, ";
	$sql .= "YEAR(act_date) AS `year`, MONTH(act_date) AS `month`, DAY(act_date) AS `day` ";	
	$sql .= "FROM `activitie` ";
	$sql .= "WHERE YEAR(act_date) = '$y' AND MONTH(act_date) = '$m' AND `ownerid` = '$uid' "; 
	
	$linkmysql->query($sql);
	
	while( $data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["type"] = "HoldAct";
		$data["name"] = $tool->UTF8_CuttingStr($data["name"], $act_name_length);
		array_push( $eventdata, $data);
	}
	
	// 可以報名的活動		
	$sql  = "SELECT `aid`, `name`, `act_date`, `act_time`, `status`, ";
	$sql .= "YEAR(act_date) AS `year`, MONTH(act_date) AS `month`, DAY(act_date) AS `day` ";	
	$sql .= "FROM `activitie` ";
	$sql .= "WHERE YEAR(act_date) = '$y' AND MONTH(act_date) = '$m' AND `status` = 'OPEN' AND `ownerid` != '$uid'";
	$sql .= "AND TO_DAYS(`act_date`)-2 >= TO_DAYS(NOW()) ";	
	$sql .= "AND `aid` NOT IN ( ";
	$sql .= "SELECT `a`.`aid` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
	$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' ";
	$sql .= ") ";
	
	$linkmysql->query($sql);
			
	while( $data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["type"] = "OpenAct";
		$data["name"] = $tool->UTF8_CuttingStr($data["name"], $act_name_length);
		array_push( $eventdata, $data);
	}
	
	// 過去活動	(會員未報名，且活動未取消)
	$sql  = "SELECT `aid`, `name`, `act_date`, `act_time`, `status`, ";
	$sql .= "YEAR(act_date) AS `year`, MONTH(act_date) AS `month`, DAY(act_date) AS `day` ";	
	$sql .= "FROM `activitie` ";
	$sql .= "WHERE YEAR(act_date) = '$y' AND MONTH(act_date) = '$m' AND `status` != 'CANCEL' AND `ownerid` != '$uid'";
	$sql .= "AND TO_DAYS(`act_date`)-2 < TO_DAYS(NOW()) ";
	$sql .= "AND `aid` NOT IN ( ";
	$sql .= "SELECT `a`.`aid` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
	$sql .= "WHERE `aj`.`uid` = '$uid' AND `aj`.`join_status` = 'join' ";
	$sql .= ") ";
	
	$linkmysql->query($sql);
			
	while( $data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["type"] = "PastAct";
		$data["name"] = $tool->UTF8_CuttingStr($data["name"], $act_name_length);
		array_push( $eventdata, $data);
	}
	
	$linkmysql->close_mysql();
	
	$count = count($eventdata);
	
	for ($i = 0;  $i < $days; $i++) 
	{
		$str  = "";
		$str .= $i+1 . "<br/>";
		
		for ( $j=0; $j<$count; $j++)
		{
			if ($eventdata[$j]["year"] == $y && $eventdata[$j]["month"] == $m && $eventdata[$j]["day"] == $i+1) 
			{
				if ($eventdata[$j]["type"] == "JoinAct")
				{
					$str .= sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\"><font color=\"green\">%s</font></a><br/>", $eventdata[$j]["aid"] ,$eventdata[$j]["name"]);
				}
				else if ($eventdata[$j]["type"] == "HoldAct")
				{
					$str .= sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\"><font color=\"red\">%s</font></a><br/>", $eventdata[$j]["aid"] ,$eventdata[$j]["name"]);
				}
				else if ($eventdata[$j]["type"] == "OpenAct")
				{
					$str .= sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\"><font color=\"blue\">%s</font></a><br/>", $eventdata[$j]["aid"] ,$eventdata[$j]["name"]);
				}
				else if ($eventdata[$j]["type"] == "PastAct")
				{
					$str .= sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\"><font color=\"gray\">%s</font></a><br/>", $eventdata[$j]["aid"] ,$eventdata[$j]["name"]);
				}
			}
		}
	
		if ( $i+1 == date('d') && $y == date('Y') && $m == date('m')) {
			$calendar .= sprintf("<div class=\"today\">%s</div>", $str);
		} else {
			$calendar .= sprintf("<div class=\"days\">%s</div>", $str);
		}	
	}
	
	if( ($offset + $days)%7 != 0)
	{
		$offset = 7-(($offset + $days)%7);
		
		for( $i=0;  $i < $offset; $i++) 
		{
			$calendar .= sprintf("<div class=\"past\">%d</div>" , $i+1 );	 
		}
	}
	
	$tpl->assign("activitielist", $eventdata);
	$tpl->assign("list_count", count($eventdata));
	$tpl->assign("calendar_pre", $previous);
	$tpl->assign("calendar_curr", $current);
	$tpl->assign("calendar_next", $next);
	$tpl->assign("calendar", $calendar);
	$tpl->assign("mainpage","main.html");
?>