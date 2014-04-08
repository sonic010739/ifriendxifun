<?	
	$year = date("Y") - 18;
	
	$birth_year = sprintf("<option value=\"-1\" selected>請選擇</option>\n");
	
	for ($i=1900; $i<=$year; $i++)
	{
		$birth_year .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
	}
		
	$birth_month = sprintf("<option value=\"-1\" selected>請選擇</option>\n");
	
	for ($i=1; $i<13; $i++)
	{
		$birth_month .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
	}
	
	$birth_day = sprintf("<option value=\"-1\" selected>請選擇</option>\n");
	
	for ($i=1; $i<32; $i++)
	{
		$birth_day .= sprintf("<option value=\"%d\">%d</option>\n", $i, $i);
	}

	$tpl->assign("birth_year", $birth_year);
	$tpl->assign("birth_month", $birth_month);
	$tpl->assign("birth_day", $birth_day);
	$tpl->assign("mainpage", "register.html");
?>