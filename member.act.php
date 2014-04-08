<?
	session_start();
	include "smarty.lib.php";
	$linkmysql->init();

	if ($_GET["act"] == "modify")
	{
		//-------------------------------
		// 修改會員資料處理
		//-------------------------------

		if (isset($_POST["modifypassword"]))
		{
			//-------------------------------
			// 修改會員密碼
			//-------------------------------

			$uid = $_POST["uid"];
			$username = $_POST["username"];
			$oldpasswd = md5($_POST["oldpasswd"]);
			$newpasswd = md5($_POST["newpasswd"]);
			$passwordhint = $_POST["passwordhint"];

			if ($oldpasswd == "" && $newpasswd == "")
			{
				$tool->ShowMsgPage("密碼未輸入");
			}

			$sql = sprintf("SELECT `username`, `password` FROM `user` WHERE `user`.`uid` = '%d'", $uid);
			$linkmysql->query( $sql );
			list($username_check, $password_check) = mysql_fetch_row($linkmysql->listmysql);

			if ($password_check == $oldpasswd && $username_check == $username)
			{
				$sql = "UPDATE `user` SET `password`='$newpasswd', `passwordhint`='$passwordhint' WHERE `user`.`username` = '$username'";
				$linkmysql->query( $sql );

				// close mysql connection
				$linkmysql->close_mysql();

				// login out
				unset($_SESSION["login"]);
				unset($_SESSION["username"]);
				unset($_SESSION["authority"]);
				unset($_SESSION["status"]);

				$tool->ShowMsgPage("密碼修改完成，請重新登入");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("密碼錯誤！");
			}
		}
		else if (isset($_POST["modifyinfomation"]))
		{
			//-------------------------------
			// 修改會員資料
			//-------------------------------

			$uid 			= $_POST["uid"];
			$username 		= $_POST["username"];
			$nickname 		= $_POST["nickname"];
			$backupemail 	= $_POST["backupemail"];	// 備用電子信箱
			$msn 			= $_POST["msn"];			// MSN
			$tel1		 	= $_POST["tel1"];			// 手機
			$tel2		 	= $_POST["tel2"];			// 手機
			$tel3		 	= $_POST["tel3"];			// 手機
			$education		= $_POST["education"];
			$top_education	= $_POST["top_education"];
			$career 		= $_POST["career"];			// 職業
			$career_detail	= $_POST["career_detail"];	// 職業
			$career_other 	= $_POST["career_other"];	// 職業
			$career_title 	= $_POST["career_title"];	// 職業
			$interest 		= $_POST["interest"];		// 興趣
			$interest_other	= $_POST["interest_other"];	// 其他興趣
			$inhabit		= $_POST["inhabit"];		// 居住地
			$promote		= $_POST["promote"];		// 宣傳信件選項

			// 手機號碼
			$tel = sprintf("%s-%s-%s", $tel1, $tel2, $tel3);

			// 職業
			if ($career == "其他") {
				$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_other, $career_title);
			} else if ($career_detail == "其他相關業") {
				$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_other, $career_title);
			} else {
				$career = sprintf("career: %s career_detail: %s career_title: %s", $career, $career_detail, $career_title);
			}

			// 興趣選項
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

			// 宣傳信件選項
			$promote = ($promote == 'OK') ? 'OK' : 'NO';

			$sql = sprintf("SELECT `username` FROM `user` WHERE `username` = '%s'", $username);
			$linkmysql->query( $sql );
			list($username_check) = mysql_fetch_row($linkmysql->listmysql);

			if ($username_check == $username)
			{
				$sql  = "UPDATE `user` SET ";
				$sql .= "`backupemail` = '$backupemail', ";
				$sql .= "`nickname` = '$nickname', ";
				$sql .= "`msn` = '$msn', ";
				$sql .= "`tel` = '$tel', ";
				$sql .= "`education` = '$education', ";
				$sql .= "`top_education` = '$top_education', ";
				$sql .= "`career` = '$career', ";
				$sql .= "`interest` = '$interest', ";
				$sql .= "`inhabit` = '$inhabit', ";
				$sql .= "`promote` = '$promote' ";
				$sql .= "WHERE `user`.`username` = '$username'";
				$linkmysql->query($sql);

				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("資料修改完成");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("錯誤，會員名稱不符");
			}
		}

		$tool->ShowMsgPage("錯誤的操作");
	}
	else if ($_GET["act"] == "adminmodify" && $_SESSION["authority"] == "Admin")
	{
		//-------------------------------
		// 修改會員資料處理 -- 此功能已經關閉
		//-------------------------------

		/*
		if (isset($_POST["modifypassword"]))
		{
			//-------------------------------
			// 修改會員密碼
			//-------------------------------

			$uid 		= $_POST["uid"];
			$username 	= $_POST["username"];
			$password 	= $_POST["password"];
			$password_md5 = md5($password);
			$passwordhint = $_POST["passwordhint"];

			$sql = sprintf("SELECT `email` FROM `user` WHERE `user`.`uid` = '%d'", $uid);
			$linkmysql->query( $sql );

			if (list($email) = mysql_fetch_row($linkmysql->listmysql))
			{
				// title
				$mailtitle = "7MD會員密碼通知";

				// mail body
				$mailmessage ="這是一封密碼信函，您的密碼已被管理員修改，請登入後儘速修改密碼<br/>";
				$mailmessage .= "會員帳號: " . $username."<br/>";
				$mailmessage .= "會員密碼: " . $password."<br/>";
				$mailmessage .= "<br/><br/>此為系統自動送出，請勿回復此封郵件!<br/>";

				// using phpMailer
				include_once('./lib/phpmailer/class.phpmailer.php');

				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->CharSet = "UTF-8";
				$mail->IsHTML(true);
				$mail->From = "Admin@sonic.twbbs.org";
				$mail->FromName = "7MD網管";
				$mail->Subject = $mailtitle;
				$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
				$mail->MsgHTML($mailmessage);
				$mail->AddAddress($email, '');

				if(!$mail->Send())
				{
					// close mysql connection
					$linkmysql->close_mysql();

					$message = "會員密碼信發送失敗!" . $mail->ErrorInfo;

					$tool->ShowMsgPage($message);
				}
				else
				{
					$sql = "UPDATE `user` SET `password` = '$password_md5', `passwordhint`='$passwordhint' WHERE `user`.`uid` = '$uid'";
					$linkmysql->query( $sql );

					// close mysql connection
					$linkmysql->close_mysql();

					$tool->ShowMsgPage("系統已寄出新密碼至會員的註冊信箱。");
				}

				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("密碼修改完成，密碼以寄送至會員信箱");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("密碼錯誤！");
			}
		}
		else if ( isset($_POST["modifyinfomation"]))
		{
			//-------------------------------
			// 修改會員資料處理
			//-------------------------------

			$uid 			= $_POST["uid"];

			$sql = sprintf("SELECT `uid` FROM `user` WHERE `uid` = '%s'", $uid);
			$linkmysql->query( $sql );

			if (list($uid_check) = mysql_fetch_row($linkmysql->listmysql))
			{
				$username 		= $_POST["username"];		// 使用者帳號
				$email 			= $_POST["email"];			// 電子信箱
				$backupemail 	= $_POST["backupemail"];	// 備用電子信箱
				$realname 		= $_POST["realname"];		// 真實姓名
				$sex 			= $_POST["sex"];			// 性別
				$nickname 		= $_POST["nickname"];		// 暱稱
				$birth_year 	= $_POST["birth_year"];		// 出生年
				$birth_month 	= $_POST["birth_month"];	// 出生月
				$birth_day 		= $_POST["birth_day"];		// 出生日
				$constellation 	= $_POST["constellation"];	// 星座
				$tel		 	= $_POST["tel"];			// 星座
				$msn 			= $_POST["msn"];			// MSN
				$education 		= $_POST["education"];		// 教育程度
				$top_education 	= $_POST["top_education"];	// 畢業學校
				$career 		= $_POST["career"];			// 職業
				$interest 		= $_POST["interest"];		// 興趣

				$sql  = "UPDATE `user` SET `username` = '$username', ";
				$sql .= "`email` = '$email', ";
				$sql .= "`backupemail` = '$backupemail', ";
				$sql .= "`realname` = '$realname', ";
				$sql .= "`sex` = '$sex', ";
				$sql .= "`nickname` = '$nickname', ";
				$sql .= "`birth_year` = '$birth_year', ";
				$sql .= "`birth_month` = '$birth_month', ";
				$sql .= "`birth_day` = '$birth_day', ";
				$sql .= "`constellation` = '$constellation', ";
				$sql .= "`tel` = '$tel', ";
				$sql .= "`msn` = '$msn', ";
				$sql .= "`education` = '$education', ";
				$sql .= "`top_education` = '$top_education', ";
				$sql .= "`career` = '$career', ";
				$sql .= "`interest` = '$interest' ";
				$sql .= "WHERE `user`.`uid` = '$uid' LIMIT 1 ;";
				$linkmysql->query( $sql );

				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("資料修改完成");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();

				$tool->ShowMsgPage("錯誤：修改不存在的會員資料");
			}
		}
		*/
	}
	else if ($_GET["act"] == "forgotpasswd")
	{
		//----------------------------------------------
		// 忘記密碼，重新設定密碼，並寄送新密碼至會員信箱
		//----------------------------------------------
		$username = $_POST["username"];
		$email = $_POST["email"];

		$sql = sprintf("SELECT `realname`, `password` ,`email` FROM `user` WHERE `username` = '%s'", $username);
		$linkmysql->query($sql);

		if (list($realname, $password, $email_check) = mysql_fetch_row($linkmysql->listmysql))
		{
			if ($email != $email_check)
			{
				$tool->ShowMsgPage("錯誤：所提供的帳號與電子信箱不符");
				die;
			}

			// set new password
			$password = substr($password, 6, 6);

			$mailinfo = array();
			$mailinfo["realname"] = $realname;
			$mailinfo["username"] = $username;
			$mailinfo["password"] = $password;

			if (!$iFMail->ResetPasswordMail($email, $realname, $mailinfo))
			{
				$message = "會員密碼信發送失敗!<br/>錯誤原因: " . $iFMail->ErrorInfo;
				$tool->ShowMsgPage($message);
			}
			else
			{
				$password = md5($password);
				$sql = "UPDATE `user` SET `password` = '$password' WHERE `user`.`username` = '$username'";
				$linkmysql->query( $sql );

				$linkmysql->close_mysql();
				$tool->ShowMsgPage("系統已寄出會員密碼至註冊信箱，登入後請儘速修改密碼");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無此帳號");
		}
	}
	else if ($_GET["act"] == "Del" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 刪除會員資料
		//----------------------------------------

		$uid = $_GET['uid'];

		$linkmysql->init();
		$sql = sprintf("SELECT `username`, `authority` FROM `user` WHERE `uid` = '%s'", $uid);
		$linkmysql->query($sql);

		if (list($username, $authority) = mysql_fetch_row($linkmysql->listmysql))
		{
			if ($authority == "Admin")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("無法刪除管理員帳號", "回會員中心", "index.php?act=membercenter");
			}
			else
			{
				$sql = sprintf("DELETE FROM `user` WHERE `user`.`uid` = '%d' LIMIT 1;", $uid);
				$linkmysql->query($sql);

				$sql = sprintf("DELETE FROM `validate` WHERE `validate`.`username` = '%s' LIMIT 1;", $username);
				$linkmysql->query($sql);

				$linkmysql->close_mysql();
				$message = sprintf("會員帳號 %s 已刪除", $username);
				$tool->ShowMsgPage($message, "回會員中心", "index.php?act=membercenter");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該會員資料", "回會員中心", "index.php?act=membercenter");
		}
	}
	else if ($_GET["act"] == "setAdmin" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 提升會員權限為管理員
		//----------------------------------------

		$uid = $_GET['uid'];

		$linkmysql->init();
		$sql  = "UPDATE `user` SET ";
		$sql .= "`authority`='Admin' ";
		$sql .= "WHERE `user`.`uid`='$uid'";
		$linkmysql->query( $sql );

		$linkmysql->close_mysql();
		$tool->URL($_SERVER['HTTP_REFERER']);
	}
	else if ($_GET["act"] == "setEO" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 提升會員權限為EO
		//----------------------------------------
		$linkmysql->init();

		$uid = $_GET['uid'];

		$sql  = "UPDATE `user` SET ";
		$sql .= "`authority` = 'EO' ";
		$sql .= "WHERE `user`.`uid` = '$uid'";
		$linkmysql->query( $sql );

		// 取出會員資料
		$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);
		$member = mysql_fetch_array($linkmysql->listmysql);

		$mailinfo = array();
		$mailinfo["realname"] = $member["realname"];

		//寄送升級為EO的信件
		$iFMail->UpgrdeEOMail($member["email"], $member["username"], $mailinfo);

		$linkmysql->close_mysql();
		$tool->URL($_SERVER['HTTP_REFERER']);
	}
	else if ($_GET["act"] == "setUser" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 設定會員權限為一般使用者
		//----------------------------------------

		$uid = $_GET['uid'];

		$linkmysql->init();
		$sql  = "UPDATE `user` SET ";
		$sql .= "`authority`='User' ";
		$sql .= "WHERE `user`.`uid`='$uid'";
		$linkmysql->query( $sql );

		$linkmysql->close_mysql();
		$tool->URL($_SERVER['HTTP_REFERER']);
	}
	else if ($_GET["act"] == "search" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 會員篩選功能
		//----------------------------------------

		unset ($_SESSION["search"]);
		$_SESSION["search"] = array();
		$_SESSION["search"]["username"] 	= $_POST["username"];
		$_SESSION["search"]["nickname"] 	= $_POST["nickname"];
		$_SESSION["search"]["realname"] 	= $_POST["realname"];
		$_SESSION["search"]["sex"] 			= $_POST["sex"];
		$_SESSION["search"]["birth_year"] 	= $_POST["birth_year"];
		$_SESSION["search"]["birth_month"] 	= $_POST["birth_month"];
		$_SESSION["search"]["birth_day"] 	= $_POST["birth_day"];
		$_SESSION["search"]["age_lb"] 		= $_POST["age_lb"];
		$_SESSION["search"]["age_ub"] 		= $_POST["age_ub"];
		$_SESSION["search"]["email"] 		= $_POST["email"];
		$_SESSION["search"]["msn"] 			= $_POST["msn"];
		
		if ($_POST["tel1"] != "" || $_POST["tel2"] != "" || $_POST["tel3"] != "") {
			$_SESSION["search"]["tel"] 			= sprintf("%s-%s-%s", $_POST["tel1"], $_POST["tel2"], $_POST["tel3"]);
		}
		
		$_SESSION["search"]["constellation"] = $_POST["constellation"];	// array
		$_SESSION["search"]["education"] 	= $_POST["education"];		// array
		$_SESSION["search"]["top_education"] = $_POST["top_education"];
		$_SESSION["search"]["interest"] 	= $_POST["interest"];		// array
		$_SESSION["search"]["career"] 		= $_POST["career"];			// array
		$_SESSION["search"]["inhabit"] 		= $_POST["inhabit"];		// array
		$_SESSION["search"]["status"] 		= $_POST["status"];
		$_SESSION["search"]["authority"] 	= $_POST["authority"];		// array
		$_SESSION["search"]["blackstatus"] 	= $_POST["blackstatus"];
		$_SESSION["search"]["action"] 	    = $_POST["action"];

		if ($_SESSION["search"]["action"] == "list")
		{
			$tool->URL("index.php?act=membercenter&sel=list&filter=search");
		}
		else if ($_SESSION["search"]["action"] == "sendmail")
		{
			$tool->URL("index.php?act=membercenter&sel=sendmail");
		}
		else if ($_SESSION["search"]["action"] == "message")
		{
			$tool->URL("index.php?act=membercenter&sel=sendmessage");
		}
		else if ($_SESSION["search"]["action"] == "exportemail")
		{
			$tool->URL("index.php?act=membercenter&sel=exportmail");
		}
		else if ($_SESSION["search"]["action"] == "exportphone")
		{
			$tool->URL("index.php?act=membercenter&sel=exportphone");
		}
	}
	else if ($_GET["act"] == "accuseblack" && $_SESSION["authority"] != "User")
	{
		//----------------------------------------
		// 提報會員為黑名單
		//----------------------------------------

		$reason = $_POST["reason"];
		$uid = $_POST["uid"];
		$accuse_id =$_POST["accuse_id"];
		$aid = $_POST["aid"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		if ($actdata = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($actdata["status"] != "CLOSE")
			{
				$linkmysql->close_mysql();
				$message = "活動必須是關閉狀態才可以提報黑名單";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$date = explode("-", $actdata["act_date"]);
			$time = explode(":", $actdata["act_time"]);
			$deadline = date("Y-m-d H:i:s", mktime($time[0]+72, $time[1], $time[2], $date[1], $date[2], $date[0]));

			if ($deadline < date("Y-m-d H:i:s"))
			{
				$linkmysql->close_mysql();
				$message = "活動關閉後72小時內才可以提報黑名單";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$deadline = date("Y-m-d H:i:s", mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));

			if ($deadline > date("Y-m-d H:i:s"))
			{
				$linkmysql->close_mysql();
				$message = "活動關閉後72小時內才可以提報黑名單";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$sql  = "INSERT INTO `blacklist` (`black_serial`, `black_id`, `accuse_id`, `aid`, `reason`, ";
			$sql .= "`accuse_time`, `verify_id`, `result`, `comment`, `verify_time`, `days`, `start_date`) ";
			$sql .= "VALUES ( NULL , '$uid', '$accuse_id', '$aid', '$reason', ";
			$sql .= "NOW() , NULL , NULL , NULL , NULL , NULL , NULL); ";
			$linkmysql->query($sql);

			$linkmysql->close_mysql();
			$tool->ShowMsgPage("黑名單提報已送出", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
	else if ($_GET["act"] == "verifyblack" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 審核被提報為黑名單的會員
		//----------------------------------------

		$black_serial = $_POST["black_serial"];
		$result = $_POST["result"];
		$days = $_POST["days"];
		$comment = $_POST["comment"];
		$verify_id = $_SESSION["uid"];

		if ($result == "Pass")
		{
			$lock = "true";
		}
		else
		{
			$days = 0;
			$lock = "false";
		}

		$linkmysql->init();

		$sql  = "UPDATE `blacklist` SET `verify_id` = '$verify_id', ";
		$sql .= "`result` = '$result' , ";
		$sql .= "`comment` = '$comment', ";
		$sql .= "`verify_time` = NOW(), ";
		$sql .= "`days` = '$days', ";
		$sql .= "`start_date` = NOW(), ";
		$sql .= "`lock` = '$lock'  WHERE `blacklist`.`black_serial` = '$black_serial' LIMIT 1;";
		$linkmysql->query( $sql );

		$linkmysql->close_mysql();
		$tool->ShowMsgPage("黑名單審查完成", "回黑名單管理", "index.php?act=membercenter&sel=blacklist");
	}
	else if ($_GET["act"] == "Unlock" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 解除黑名單會員
		//----------------------------------------

		$black_serial = $_GET["black_serial"];

		$linkmysql->init();
		$sql  = "UPDATE `blacklist` SET `lock` = 'false' WHERE `black_serial` = '$black_serial' LIMIT 1;";
		$linkmysql->query( $sql );

		$linkmysql->close_mysql();
		$tool->ShowMsgPage("已從黑名單解除", "回黑名單管理", "index.php?act=membercenter&sel=blacklist");
	}
	else if ($_GET["act"] == "ExportMail")
	{
		//----------------------------------------
		// 匯出電子郵件位置為檔案
		//----------------------------------------

		if ($_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("限定管理者才可使用");
		}

		$filename = date('Ymd').'_iFUserEmail.csv';
		$fp = fopen("log/".$filename, 'w');

		$str = "頭銜,名字,中間名,姓氏,稱謂,公司,部門,職稱,商務 - 街,商務 - 街 2,商務 - 街 3,商務 - 市/鎮,商務 - 縣/市,商務 - 郵遞區號,商務 - 國家/地區,住家 - 街,住家 - 街 2,住家 - 街 3,住家 - 市/鎮,住家 - 縣/市,住家 - 郵遞區號,住家 - 國家/地區,其他 - 街,其他 - 街 2,其他 - 街 3,其他 - 市/鎮,其他 - 縣/市,其他 - 郵遞區號,其他 - 國家,助理電話,商務傳真,商務電話,商務電話 2,回撥電話,汽車電話,公司代表線,住家傳真,住家電話,住家電話 2,ISDN,行動電話,其他傳真,其他電話,呼叫器,代表電話,無線電話,TTY/TDD 電話,Telix,子女,公司 ID,公司地址郵政信箱,引用,主管名稱,生日,目錄伺服器,地點,住家地址郵政信箱,助理,私人,身份證字號,使用者 1,使用者 2,使用者 3,使用者 4,其他地址郵政信箱,性別,津貼,紀念日,記事,配偶,專業,帳目資訊,帳號,敏感度,嗜好,電子郵件地址,電子郵件類型,電子郵件顯示名稱,電子郵件 2 地址,電子郵件 2 類型,電子郵件 2 顯示名稱,電子郵件 3 地址,電子郵件 3 類型,電子郵件 3 顯示名稱,網頁,網際網路空閒-忙碌中,語言,辦公室,優先順序,縮寫,關鍵字,類別\n";
		fwrite($fp, utf8_2_big5($str));

		$linkmysql->init();
		$sql = "SELECT `realname`, `email` FROM `user` WHERE `status` = 'Validate'";
		$linkmysql->query($sql);

		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$str = sprintf(",%s,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,%s,,,,,,,,,,,,,,,,\n", $data[0], $data[1]);
			fwrite($fp, utf8_2_big5($str));
		}

		fclose($fp);

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . urlencode($filename));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Length: " . filesize("log/".$filename));

		$fp = fopen("log/".$filename, "r");

		while (!feof($fp))
		{
			echo fread($fp, 65536);
			flush(); // this is essential for large downloads
		}

		fclose($fp);
	}
	else if ($_GET["act"] == "SendMail")
	{
		//----------------------------------------
		// 使用郵件系統寄送郵件
		//----------------------------------------
		$mails = $_POST["mail"];
		$subject = $_POST["subject"];
		$others = explode(",", $_POST["others"]);
		$body = $_POST["body"];

		$upload_dir = "upload/";
		$new_file = $_FILES['attach'];
		$file_name = $new_file['name'];
		$file_tmp = $new_file['tmp_name'];

		//$new_file['name'] = iconv("UTF-8", "big5", $new_file['name']);
		move_uploaded_file($file_tmp, $upload_dir.$new_file['name']);

		// 將 \" 轉成 "
		$body = str_replace('\\"', '"', $body);

		$count = 0;
		if (is_array($mails))
		{
			foreach($mails as $mail)
			{
				$iFMail->SendMail($mail, '', $subject, $body, $upload_dir.$new_file['name']);
				$count++;
			}
		}

		foreach($others as $mail)
		{
			if (trim($mail) != "")
			{
				$iFMail->SendMail(trim($mail), '', $subject, $body, $upload_dir.$new_file['name']);
				$count++;
			}
		}

		if ($count == 0)
		{
			$tool->ShowMsgPage("未寄出任何信件", "回到會員中心", "index.php?act=membercenter");
		}
		else
		{
			$tool->ShowMsgPage("信件寄送完成! 已寄出 $count 封信件", "回到會員中心", "index.php?act=membercenter");
		}

	}
	else if ($_GET["act"] == "SendMessage")
	{
		//----------------------------------------
		// 使用簡訊系統寄送簡訊
		//----------------------------------------

		$phones = $_POST["phones"];
		$others = explode(",", $_POST["others"]);
		$message = $_POST["message"];

		$sendtime = $_POST["sendtime"];
		$year = $_POST["year"];
		$month = $_POST["month"];
		$day = $_POST["day"];
		$hour = $_POST["hour"];
		$minute = $_POST["minute"];

		$dlvtime = '';

		if ($sendtime == 'SETTIME')
		{
			$dlvtime = sprintf("%04d/%02d/%02d %02d:%02d:00", $year, $month, $day, $hour, $minute);
		}

		if ($iFSMS->query_point() < count($phones) + count($others))
		{
			$tool->ShowMsgPage("簡訊系統點數不足，無法寄送簡訊，請聯絡系統管理員", "回到會員中心", "index.php?act=membercenter");
		}

		$count = 0;
		if (is_array($phones))
		{
			foreach($phones as $phone)
			{
				$msg_result = $iFSMS->Send_SMS( $phone, $message, $dlvtime);

				if ($msg_result['msgid'] > 0)
				{
					$count++;
				}
			}
		}

		foreach($others as $phone)
		{
			if (trim($phone) != "")
			{
				$msg_result = $iFSMS->Send_SMS( $phone, $message, $dlvtime);

				if ($msg_result['msgid'] > 0)
				{
					$count++;
				}
			}
		}

		if ($count == 0)
		{
			$tool->ShowMsgPage("未送出任何簡訊", "回到會員中心", "index.php?act=membercenter");
		}
		else
		{
			$tool->ShowMsgPage("簡訊寄送完成! 已寄出 $count 封簡訊", "回到會員中心", "index.php?act=membercenter");
		}
	}
	else
	{
		$tool->URL("index.php");
	}
?>