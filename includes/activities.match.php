<?
	if ($_SESSION['login'] != 1)
	{
		$tool->ShowMsgPage('請先登入', '註冊帳號', 'index.php?act=register');
	}

	if ($_GET['sel'] == "overview")
	{
		//----------------------------------------
		// 觀看新版活動配對填寫狀況
		//----------------------------------------
		
		$aid = $_GET['aid'];

		$linkmysql->init();
		
		// 取出活動資料
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['use_newmatch'] == 'NO')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動尚未設定使用聯繫資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}
		
		// 活動時間開始後24小時內可提填寫配對資料
		$fillout_start = date("Y-m-d H:i", mktime($time[0], $time[1], 0, $date[1], $date[2], $date[0]));
		
		if (date("Y-m-d H:i") < $fillout_start)
		{
			$message  = "目前無法檢視所有會員給予的聯繫資料<br />";
			$message .= "可檢視資料的時間 $fillout_start";
			$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}	

		$user_list = array();
		$join_data = array();

		// 有參加活動的會員
		$sql  = "SELECT `u`.`uid`, `u`.`sex`, `u`.`username`, `u`.`nickname` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY `aj`.`no` ASC ";
		$linkmysql->query($sql);

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$user_list[$data['uid']] = $data['username'];
			//array_push( $join_data, $data);
		}

		// 取出新版活動配對資料
		$sql  = "SELECT `uid`, `email`, `msn`, `tel` ";
		$sql .= "FROM `activitiematch` ";
		$sql .= "WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		$act_matches = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			// 轉換 id 為 username
			$emails = explode(",", $data['email']);
			$data['email'] = '';

			foreach($emails as $email)
			{
				if (trim($email) != "")
				{
					$data['email'] .= $user_list[trim($email)] . ", ";
				}
			}

			// 轉換 id 為 username
			$msns = explode(",", $data['msn']);
			$data['msn'] = '';

			foreach($msns as $msn)
			{
				if (trim($msn) != "")
				{
					$data['msn'] .= $user_list[trim($msn)] . ", ";
				}
			}

			// 轉換 id 為 username
			$tels = explode(",", $data['tel']);
			$data['tel'] = '';

			foreach($tels as $tel)
			{
				if (trim($tel) != "")
				{
					$data['tel'] .= $user_list[trim($tel)] . ", ";
				}
			}

			$data['username'] =  $user_list[$data['uid']];

			array_push($act_matches, $data);
		}

		$linkmysql->close_mysql();

		$tpl->assign("act_matches", $act_matches);
		$tpl->assign("mainpage", "activities/activities.match.overview.html");
	}
	else if ($_GET['sel'] == "fillout_match")
	{
		//----------------------------------------
		// 選填新版活動配對資料
		//----------------------------------------
		
		$aid = $_GET['aid'];
		$uid = $_SESSION['uid'];

		$linkmysql->init();
		
		// 檢查是否有使用新版配對
		$sql  = "SELECT `uid` FROM `activitiematch` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);
		
		if (!mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("此活動尚未設定聯繫資料相關資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		
		// 檢查會員參加活動出席狀態
		$sql  = "SELECT `aj`.`attendance` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";
		$linkmysql->query($sql);

		if ($join_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($join_data['attendance'] == 'false')
			{
				$tool->ShowMsgPage("您沒有出席活動，所以無法選填資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['use_newmatch'] == 'NO')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動尚未設定使用聯繫資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}
		
		$date = explode("-", $act_data["act_date"]);
		$time = explode(":", $act_data["act_time"]);

		// 活動時間開始後24小時內可提填寫配對資料
		$fillout_start = date("Y-m-d H:i", mktime($time[0], $time[1], 0, $date[1], $date[2], $date[0]));
		$fillout_end = date("Y-m-d H:i", mktime($time[0]+24, $time[1], 0, $date[1], $date[2], $date[0]));
		
		if (date("Y-m-d H:i") > $fillout_end || date("Y-m-d H:i") < $fillout_start)
		{
			$message  = "目前無法選擇要給予的聯繫資料 <br />";
			$message .= "可選擇資料的時間為 $fillout_start ~ $fillout_end";
			$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}		

		// 取出會員的新版活動配對資料
		$sql  = "SELECT `email`, `msn`, `tel` ";
		$sql .= "FROM `activitiematch` ";
		$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid'";
		$linkmysql->query($sql);
		
		list($email, $msn, $tel) = mysql_fetch_array($linkmysql->listmysql);
		
		$emails = explode(",", $email);
		$msns 	= explode(",", $msn);
		$tels 	= explode(",", $tel);
		
		$join_data = array();

		$girl_count = 1;
		$boy_count = 1;
		
		// 有參加活動的會員
		$sql  = "SELECT `u`.`uid`, `u`.`sex`, `u`.`nickname`, `aj`.`no` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY `aj`.`no` ASC ";
		
		$linkmysql->query($sql);

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["no"] != '')
			{
				if ($data["sex"] == '男')
				{
					$data["sex_count"] = sprintf("%d 號男生", $boy_count++);
					$data["sex"] = "<font color=\"blue\">男</font>";
				}
				else if ($data["sex"] == '女')
				{
					$data["sex_count"] = sprintf("%d 號女生", $girl_count++);
					$data["sex"] = "<font color=\"red\">女</font>";
				}
			}
			
			$data['match_email'] 	= in_array($data['uid'], $emails) ? 'checked' : '';
			$data['match_msn'] 		= in_array($data['uid'], $msns) ? 'checked' : '';
			$data['match_tel'] 		= in_array($data['uid'], $tels) ? 'checked' : '';
			
			array_push( $join_data, $data);
		}		

		$linkmysql->close_mysql();

		$tpl->assign("aid", $aid);
		$tpl->assign("uid", $uid);
		$tpl->assign("act_matches", $join_data);
		$tpl->assign("mainpage", "activities/activities.match.fillout.html");
	}
	else if ($_GET['sel'] == "match_result")
	{
		//----------------------------------------
		// 觀看新版活動配對結果
		//----------------------------------------
		
		$aid = $_GET['aid'];
		$uid = $_SESSION['uid'];

		$linkmysql->init();
		
		// 檢查是否有使用新版配對
		$sql  = "SELECT `uid` FROM `activitiematch` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);
		
		if (!mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("此活動尚未設定聯繫資料相關資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		
		// 檢查會員參加活動出席狀態
		$sql  = "SELECT `aj`.`attendance` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";
		$linkmysql->query($sql);

		if ($join_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($join_data['attendance'] == 'false')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("您沒有出席活動，所以無法觀看結果", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}

		// 取出活動資料
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['use_newmatch'] == 'NO')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動尚未設定使用聯繫資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}
		
		$date = explode("-", $act_data["act_date"]);
		$time = explode(":", $act_data["act_time"]);

		// 活動時間開始24小時後可觀看新版配對資料
		$fillout_end = date("Y-m-d H:i", mktime($time[0]+24, $time[1], 0, $date[1], $date[2], $date[0]));
		
		if (date("Y-m-d H:i") < $fillout_end)
		{
			$linkmysql->close_mysql();
			$message  = "目前無法觀看聯繫資料處理結果，等待其他會員填寫完成，<br />";
			$message .= "開放的時間為 ". $fillout_end . "，請稍後。";
			$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		
		$girl_count = 1;
		$boy_count = 1;
		$join_data = array();
		
		// 有參加活動的會員
		$sql  = "SELECT `u`.`uid`, `u`.`sex`, `u`.`nickname`, `aj`.`no`, ";
		$sql .= "`u`.`email`, `u`.`msn`, `u`.`tel`, `aj`.`attendance`, ";
		$sql .= "`am`.`email` AS `match_email`, ";
		$sql .= "`am`.`msn` AS `match_msn`, ";
		$sql .= "`am`.`tel` AS `match_tel` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `activitiematch` am ON `aj`.`uid` = `am`.`uid` AND `aj`.`aid` = `am`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY  `aj`.`no` ASC";
		$linkmysql->query($sql);

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{			
			if ($data['no'] != '')
			{
				if ($data["sex"] == '男')
				{
					$data["sex_count"] = sprintf("%d 號男生", $boy_count++);
					$data["sex"] = "<font color=\"blue\">男</font>";
				}
				else if ($data["sex"] == '女')
				{
					$data["sex_count"] = sprintf("%d 號女生", $girl_count++);
					$data["sex"] = "<font color=\"red\">女</font>";
				}
			}
			
			if ($data['attendance'] == 'false')
			{
				$data['match_email'] 	= '未出席';
				$data['match_msn'] 		= '未出席';
				$data['match_tel'] 		= '未出席';
				$data['match_view']		= '0';
			}
			else
			{
				$emails = explode(",", $data['match_email']);
				$msns = explode(",", $data['match_msn']);
				$tels = explode(",", $data['match_tel']);
				
				$data['match_view']		= '1';
				$data['match_email'] 	= in_array($uid, $emails) ? $data['email'] : 'Reserved';
				$data['match_msn'] 		= in_array($uid, $msns) ? $data['msn'] : 'Reserved';
				$data['match_tel'] 		= in_array($uid, $tels) ? $data['tel'] : 'Reserved';
				
				if ($data['match_email'] == 'Reserved' && $data['match_msn'] == 'Reserved' && $data['match_tel'] == 'Reserved')
				{
					$data['match_view']	= '0';
				}
			}
			
			array_push($join_data, $data);
		}
		
		$linkmysql->close_mysql();
		
		$tpl->assign("act_matches", $join_data);
		$tpl->assign("mainpage", "activities/activities.match.result.html");
	}
	else
	{
		$tool->ShowMsgPage('Activities Match ERROR: unknown command.');
	}

?>