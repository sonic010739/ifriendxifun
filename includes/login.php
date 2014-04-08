<?
	if (empty($_SESSION["login"]))
	{
		$_SESSION["login"] = -1;
	}
	
	if ($_SESSION["login"] == 1)
	{		
		//-------------------------------------------------
		// 登入顯示的資訊
		//-------------------------------------------------
		
		$login = 1;
		$tpl->assign("uid", $_SESSION["uid"]);
		$tpl->assign("username", $_SESSION["username"]);
		
		
		if ($_SESSION["authority"] == "Admin") 
		{
			$tpl->assign("authority", "網站管理員");
		}
		else if ($_SESSION["authority"] == "EO") 
		{
			$tpl->assign("authority", "EO");
		}
		else
		{
			$tpl->assign("authority", "使用者");
		}
		
		/*
		if ($_SESSION["status"] == "Validate") 
		{
			$tpl->assign("status", "已認證");
		} 
		else 
		{
			$str = "未認證" . "<br/><a href=\"./register.act.php?act=revalidate\">重新寄發註冊確認信</a>";
			$tpl->assign("status", $str);			
		}
		*/
		
		if ($_SESSION["authority"] == "Admin" || $_SESSION["authority"] == "EO") {
			$tpl->assign("EO", 1);
		} else {
			$tpl->assign("EO", 0);
		}
	
		if ($_SESSION["authority"] == "Admin") {
			$tpl->assign("admin", 1);
		} else {
			$tpl->assign("admin", 0);
		}	
	}
	else
	{
		$login = 0;
		$tpl->assign("Username", $_COOKIE["Username"]);
		
		$_SESSION["login"] = -1;
		$_SESSION["uid"] = -1;
		$_SESSION["username"] = "None";
		$_SESSION["authority"] = "None";
		$_SESSION["status"] = "None";
	}
	
	$tpl->assign("login", $login);
?>