<?
	session_start();

	include "smarty.lib.php";

	$linkmysql->init();

	if( $_GET["act"] == "login")
	{
		//----------------------------------------
		// 會員登入檢查程式
		//----------------------------------------
		
		$username = $_POST["username"];
		$password_md5 = md5( $_POST["password"] );  //md5編碼

		$sql = sprintf("SELECT `uid`, `username`, `password`, `status`, `authority` FROM `user` WHERE `username` = '%s'",$username);
		$linkmysql->query( $sql );
		list($uid, $username_check, $password_check, $status, $authority)=mysql_fetch_row($linkmysql->listmysql);

		if ( $username_check != $username )
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("沒有這個帳號");
		}
		else if ($password_md5 != $password_check)
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("密碼錯誤");
		}
		else if( $username_check == $username && $password_md5 == $password_check )
		{
			if ($status == "Invalidate")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("您的帳號目前尚未啟用，請至您申請帳號時所填寫的信箱，進行啟用帳號程序。");
			}
			else
			{
				//密碼正確 開始登入程序
				$_SESSION["login"] = 1;
				$_SESSION["uid"] = $uid;
				$_SESSION["username"] = $username;
				$_SESSION["authority"] = $authority;
				$_SESSION["status"] = $status;

				setcookie ("Username", $username, time() + 5184000);
		
				// 更新最後登入時間
				$sql  = "UPDATE `user` SET ";
				$sql .= "`login_time` = NOW() ";
				$sql .= "WHERE `user`.`username` = '$username'";
				$linkmysql->query($sql);

				$linkmysql->close_mysql();
				$tool->URL("index.php?act=activitielist");
			}
		}
	}
	else if( $_GET["act"] == "out")
	{
		//----------------------------------------
		// 會員登出
		//----------------------------------------
		
		session_unset();
		session_destroy();
		$_SESSION = array();

		$tool->URL("index.php");
	}
	else
	{
		$tool->URL("index.php");
	}
?>
