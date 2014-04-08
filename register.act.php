<?
	session_start();
	include "smarty.lib.php";

	if ($_GET["act"] == "register")
	{
		//----------------------------------------------------------
		// 會員註冊，並且產生驗證碼，並且寄送驗證連結至所註冊的信箱
		//----------------------------------------------------------

		$regcode 		= $_POST["regcode"];		// 註冊碼
		$username 		= $_POST["username"];		// 使用者帳號
		$password 		= md5($_POST["password"]);	// 密碼
		$passwordhint 	= $_POST["passwordhint"];	// 密碼提示
		$email 			= $_POST["email"];			// 電子信箱
		$backupemail 	= $_POST["backupemail"];	// 備用電子信箱
		$realname 		= $_POST["realname"];		// 真實姓名
		$sex 			= $_POST["sex"];			// 性別
		$nickname 		= $_POST["nickname"];		// 暱稱
		$birth_year 	= $_POST["birth_year"];		// 出生年
		$birth_month 	= $_POST["birth_month"];	// 出生月
		$birth_day 		= $_POST["birth_day"];		// 出生日
		$constellation 	= $_POST["constellation"];	// 星座
		$tel1		 	= $_POST["tel1"];			// 手機
		$tel2		 	= $_POST["tel2"];			// 手機
		$tel3		 	= $_POST["tel3"];			// 手機
		$msn 			= $_POST["msn"];			// MSN
		$education 		= $_POST["education"];		// 教育程度
		$top_education 	= $_POST["top_education"];	// 畢業學校
		$career 		= $_POST["career"];			// 職業
		$career_detail	= $_POST["career_detail"];	// 職業
		$career_other 	= $_POST["career_other"];	// 職業
		$career_title 	= $_POST["career_title"];	// 職業
		$interest 		= $_POST["interest"];		// 興趣
		$interest_other	= $_POST["interest_other"];	// 其他興趣
		$inhabit		= $_POST["inhabit"];		// 居住地
		$promote		= $_POST["promote"];		// 宣傳信件選項
		
		$tel = sprintf("%s-%s-%s", $tel1, $tel2, $tel3);

		$username_check	= $_POST["username_check"];
		$email_check	= $_POST["email_check"];

		if ($username_check != "OK")
		{
			$tool->ShowMsgPage("帳號名稱未檢查");
		}

		if ($email_check != "OK")
		{
			$tool->ShowMsgPage("電子郵件未檢查");
		}

		// 職業
		if ($career == "其他") {
			$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_other, $career_title);
		} else if ($career_detail == "其他相關業") {
			$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_other, $career_title);
		} else {
			$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_detail, $career_title);
		}

		// 興趣
		$str = "";
		foreach ($interest as $value)
		{
			if ($value == "其他") {
				$str .= "其他: " . $interest_other .", ";
			} else {
				$str .= $value . ", ";
			}
		}

		$str = substr($str, 0, strlen($str)-2);
		$interest = $str;

		// open mysql connection
		$linkmysql->init();

		// check same username
		$sql = "SELECT `username` FROM `user` WHERE `username` = '$username'";
		$linkmysql->query( $sql );

		if (list($user) = mysql_fetch_row($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("此帳號名稱已經有人使用了，請選擇其他帳號名稱");
		}

		// add new member
		$sql  = "INSERT INTO `user` ( `uid`, `username`, `password`, `passwordhint`, `email`, ";
		$sql .= "`backupemail`, `realname`, `sex`, `nickname`, `birth_year`, `birth_month`, ";
		$sql .= "`birth_day`, `constellation`, `tel`, `msn`, `education`, `top_education`, `career`, ";
		$sql .= "`interest`, `inhabit`, `promote`, `status`, `authority`, `register_time`, `login_time`) ";
		$sql .= "VALUES ( '', '$username', '$password', '$passwordhint', '$email', '$backupemail', ";
		$sql .= "'$realname', '$sex', '$nickname', '$birth_year', '$birth_month', '$birth_day', '$constellation', ";
		$sql .= "'$tel', '$msn', '$education', '$top_education', '$career', ";
		$sql .= "'$interest', '$inhabit', 'OK', 'Invalidate', 'User', NOW(), NOW());";

		if (!$linkmysql->query($sql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('會員註冊失敗，您輸入的內容可能含有不合法的字元');
		}

		// produce validate code
		$md5_code = md5( $username.$password.time() );
		$sql = "INSERT INTO `validate` ( `username` , `code` ) VALUES ('$username', '$md5_code')";
		$linkmysql->query($sql);

		// close mysql connection
		$linkmysql->close_mysql();

		// create active link
		$active_link = sprintf("%sregister.act.php?act=validater&amp;username=%s&amp;code=%s", $config["base_url"], $username, $md5_code );
		$active_link = sprintf("<a href=\"%s\" target=\"new\">%s</a>", $active_link, $active_link);

		$mailinfo = array();
		$mailinfo["realname"] = $realname;
		$mailinfo["username"] = $username;
		$mailinfo["passwordhint"] = $passwordhint;
		$mailinfo["activelink"] = $active_link;

		if(!$iFMail->RegisterMail($email, $realname, $mailinfo))
		{
			$message = "會員認證信發送失敗!<br/>錯誤原因: " . $iFMail->ErrorInfo;
			$tool->ShowMsgPage($message);
		}
		else
		{
			$message  = '已寄出會員認證信，請至您註冊所填寫的信箱收信以啟用帳號。';
			$message .= '<br />';
			$message .= '若在30分鐘內沒有收到認證信件，請使用<b>重寄驗證信</b>功能重新寄送認證信';
			$tool->ShowMsgPage($message);
		}
	}
	else if ($_GET["act"] == "validater")
	{
		//-------------------------------
		// 註冊確認驗證
		//-------------------------------

		$username = $_GET["username"];
		$code = $_GET["code"];

		// open mysql connection
		$linkmysql->init();

		$sql = "SELECT `code` FROM `validate` WHERE `username` = '$username'";
		$linkmysql->query( $sql );

		if (list($Incode) = mysql_fetch_row($linkmysql->listmysql))
		{
			if ($code == $Incode)
			{
				$sql = "UPDATE `user` SET `status` = 'Validate' WHERE `user`.`username` ='$username'";
				$linkmysql->query( $sql );

				$sql = "DELETE FROM `validate` WHERE `username` = '$username' LIMIT 1";
				$linkmysql->query( $sql );

				$linkmysql->close_mysql();

				$tool->ShowMsgPage("帳號啟用成功，請從首頁登入");
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("帳號驗證碼錯誤");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無此帳號，或此帳號已經驗證成功");
		}
	}
	else if ($_GET["act"] == "revalidater")
	{
		//-------------------------------
		// 重新寄送註冊確認信
		//-------------------------------
		$username = $_POST["username"];
		$email = $_POST["email"];

		// open mysql connection
		$linkmysql->init();

		// get member information
		$sql = "SELECT `username`, `realname`, `email`, `password`, `passwordhint`, `status` FROM `user` WHERE `username` = '$username'";
		$linkmysql->query($sql);
		list($user, $realname, $email_check, $password, $passwordhint, $status) = mysql_fetch_row($linkmysql->listmysql);

		if ($user != $username || $email != $email_check)
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("錯誤: 所提供的帳號與電子信箱不符。");
		}
		else if ($status != "Invalidate")
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無法重新寄送驗證信，該會員已經驗證帳號。");
		}
		else
		{
			// produce validate code
			$md5_code = md5( $username.$password.time() );
			$sql = "UPDATE `validate` SET `code` = '$md5_code ' WHERE `validate`.`username` ='$username'";

			// create active link
			$active_link = sprintf("%sregister.act.php?act=validater&amp;username=%s&amp;code=%s", $config["base_url"], $username, $md5_code );
			$active_link = sprintf("<a href=\"%s\" target=\"new\">%s</a>", $active_link, $active_link);

			$mailinfo = array();
			$mailinfo["realname"] = $realname;
			$mailinfo["username"] = $user;
			$mailinfo["passwordhint"] = $passwordhint;
			$mailinfo["activelink"] = $active_link;

			if (!$iFMail->RegisterMail($email, $realname, $mailinfo))
			{
				$message = "會員註冊確認信發送失敗!<br/>錯誤原因: " . $iFMail->ErrorInfo;
				$tool->ShowMsgPage($message);
			}
			else
			{
				$tool->ShowMsgPage("系統已重新寄出會員註冊確認信，請至信箱收信以啟用帳號");
			}
		}
	}
	else if ($_GET["act"] == "checkuser")
	{
		//-------------------------------------------------
		// AJAX 查詢 帳號名稱是否已註冊
		//-------------------------------------------------
		$username = $_GET["username"];

		$linkmysql->init();
		$sql = "SELECT `username` FROM `user` WHERE `username` = '$username'";
		$linkmysql->query( $sql );

		if (list($user) = mysql_fetch_row($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			print "Used";
		}
		else
		{
			$linkmysql->close_mysql();
			print "OK";
		}
	}
	else if ($_GET["act"] == "checkemail")
	{
		//-------------------------------------------------
		// AJAX 查詢 email是否已註冊
		//-------------------------------------------------
		$email = $_GET["email"];

		$linkmysql->init();
		$sql = "SELECT `uid` FROM `user` WHERE `email` = '$email'";
		$linkmysql->query( $sql );

		if (list($uid) = mysql_fetch_row($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			print "Used";
		}
		else
		{
			$linkmysql->close_mysql();
			print "OK";
		}
	}
	else
	{
		$tool->URL("index.php");
	}
?>