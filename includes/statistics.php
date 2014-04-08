<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	if ($_SESSION["authority"] != "Admin")
	{
		$tool->ShowMsgPage("權限不足無法觀看統計資料");
	}
	
	$linkmysql->init();
	$statistics = array();
	///////////////////////////////////////////////////////////////////////////
	// 人數統計資料
	
	// 會員總數
	$sql  = "SELECT COUNT(*) FROM `user`";
	$linkmysql->query($sql);
	list($statistics["totalmember"]) = mysql_fetch_array($linkmysql->listmysql);
	
	// 已認證會員數
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `status` = 'Validate'";
	$linkmysql->query($sql);
	list($statistics["Validate"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["Validate_percentage"] = sprintf("%2.2f%%", $statistics["Validate"] / $statistics["totalmember"] *100);
	
	// 未認證會員數
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `status` = 'Invalidate'";
	$linkmysql->query($sql);
	list($statistics["Invalidate"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["Invalidate_percentage"] = sprintf("%2.2f%%", $statistics["Invalidate"] / $statistics["totalmember"] *100);
	
	// 男性會員數
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `sex` = '男'";
	$linkmysql->query($sql);
	list($statistics["males"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["males_percentage"] = sprintf("%2.2f%%", $statistics["males"] / $statistics["totalmember"] *100);
	
	// 女性會員數
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `sex` = '女'";
	$linkmysql->query($sql);
	list($statistics["females"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["females_percentage"] = sprintf("%2.2f%%", $statistics["females"] / $statistics["totalmember"] *100);
	
	// 管理員
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `authority` = 'Admin'";
	$linkmysql->query($sql);
	list($statistics["Admin"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["Admin_percentage"] = sprintf("%2.2f%%", $statistics["Admin"] / $statistics["totalmember"] *100);
	
	// EO
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `authority` = 'EO'";
	$linkmysql->query($sql);
	list($statistics["EO"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["EO_percentage"] = sprintf("%2.2f%%", $statistics["EO"] / $statistics["totalmember"] *100);
	
	// user
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `authority` = 'User'";
	$linkmysql->query($sql);
	list($statistics["User"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["User_percentage"] = sprintf("%2.2f%%", $statistics["User"] / $statistics["totalmember"] *100);
	
	///////////////////////////////////////////////////////////////////////////
	// 活動統計資料
		
	$act_data = array();
	
	$sql  = "SELECT * FROM `activitie` ";
	$sql .= "WHERE `status` = 'OPEN' ";
	$sql .= "ORDER BY `act_date` ASC ";
	$linkmysql->query($sql);

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);
		array_push($act_data, $data);
	}
	
	for ($i = 0; $i < count($act_data); $i++)
	{
		$aid = $act_data[$i]['aid'];

		// 男性報名者人數
		$sql  = "SELECT COUNT(*)";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' AND `u`.`sex` = '男'";
		$linkmysql->query($sql);

		list($males) = mysql_fetch_array($linkmysql->listmysql);

		// 女性報名者人數
		$sql  = "SELECT COUNT(*)";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' AND `u`.`sex` = '女'";
		$linkmysql->query($sql);

		list($females) = mysql_fetch_array($linkmysql->listmysql);

		//更新活動人數
		$sql = "UPDATE `activitie` SET `males` = '$males', `females` = '$females' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
		$linkmysql->query($sql);
		
		// 昨日累積報名人數
		$act_data[$i]['join_males'] = 0;
		$act_data[$i]['join_females'] = 0;
		
		// 目前繳費人數
		$act_data[$i]['paid_males'] = 0;
		$act_data[$i]['paid_females'] = 0;
		
		// 未繳費人數
		$act_data[$i]['unpay_males'] = 0;
		$act_data[$i]['unpay_females'] = 0;
		
		// 目前繳費金額
		$act_data[$i]['paid_amount'] = 0;
		
		// 昨日累積報名人數 (男)
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND (TO_DAYS(NOW()) - TO_DAYS(`aj`.`join_time`) > 0) ";
		$sql .= "AND `u`.`sex` = '男' ";
		$sql .= "AND `aj`.`join_status` = 'join'";
		$linkmysql->query($sql);
		
		list($act_data[$i]['join_males']) = mysql_fetch_array($linkmysql->listmysql);
		
		// 昨日累積報名人數 (女)
		$sql  = "SELECT COUNT(*) ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND (TO_DAYS(NOW()) - TO_DAYS(`aj`.`join_time`) > 0) ";
		$sql .= "AND `u`.`sex` = '女' ";
		$sql .= "AND `aj`.`join_status` = 'join'";
		$linkmysql->query($sql);
		
		list($act_data[$i]['join_females']) = mysql_fetch_array($linkmysql->listmysql);
		
		// 新增報名人數 = 目前報名人數 - 昨日報名累積人數
		$act_data[$i]['today_join_males'] = $act_data[$i]['males'] - $act_data[$i]['join_males'];
		$act_data[$i]['today_join_females'] = $act_data[$i]['females'] - $act_data[$i]['join_females'];			
		
		$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, ";
		$sql .= "`aj`.`join_time`, `u`.`sex` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$linkmysql->query($sql);
		
		$join_data = array();
		
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{		
			array_push( $join_data, $data);
		}
		
		foreach ($join_data as $joindata)
		{
			// 統計繳費方式及繳費狀態
			if ($joindata["charge_type"] == "iBon")
			{
				$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $joindata["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

				if ($joindata["sex"] == "男" && empty($pay_time))
				{
					$act_data[$i]['unpay_males']++;
				}
				else if ($joindata["sex"] == "男" && !empty($pay_time))
				{
					$act_data[$i]['paid_males']++;
					$act_data[$i]['paid_amount'] += $fees;
				}
				else if ($joindata["sex"] == "女" && empty($pay_time))
				{
					$act_data[$i]['unpay_females']++;
				}
				else if ($joindata["sex"] == "女" && !empty($pay_time))
				{
					$act_data[$i]['paid_females']++;
					$act_data[$i]['paid_amount'] += $fees;
				}
			}
			else if ($joindata["charge_type"] == "coupon")
			{
				if ($joindata["sex"] == "男")
				{
					$act_data[$i]['paid_males']++;
				}
				else if ($joindata["sex"] == "女")
				{
					$act_data[$i]['paid_females']++;
				}
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////
	// 會員統計資料
	
	// 學歷為 博士
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '博士'";
	$linkmysql->query($sql);
	list($statistics["phd"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["phd_percentage"] = sprintf("%2.2f%%", $statistics["phd"] / $statistics["totalmember"] *100);
	
	// 學歷為 碩士
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '碩士'";
	$linkmysql->query($sql);
	list($statistics["master"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["master_percentage"] = sprintf("%2.2f%%", $statistics["master"] / $statistics["totalmember"] *100);
	
	// 學歷為 大學
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '大學'";
	$linkmysql->query($sql);
	list($statistics["university"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["university_percentage"] = sprintf("%2.2f%%", $statistics["university"] / $statistics["totalmember"] *100);
	
	// 學歷為 高中職
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '高中職'";
	$linkmysql->query($sql);
	list($statistics["senior"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["senior_percentage"] = sprintf("%2.2f%%", $statistics["senior"] / $statistics["totalmember"] *100);
	
	// 學歷為 四技二專
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '四技二專'";
	$linkmysql->query($sql);
	list($statistics["training"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["training_percentage"] = sprintf("%2.2f%%", $statistics["training"] / $statistics["totalmember"] *100);	
	
	// 學歷為 國中
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `education` = '國中'";
	$linkmysql->query($sql);
	list($statistics["junior"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["junior_percentage"] = sprintf("%2.2f%%", $statistics["junior"] / $statistics["totalmember"] *100);
	

	// 星座統計	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '牡羊座'";
	$linkmysql->query($sql);
	list($statistics["aries"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["aries_percentage"] = sprintf("%2.2f%%", $statistics["aries"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '金牛座'";
	$linkmysql->query($sql);
	list($statistics["taurus"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["taurus_percentage"] = sprintf("%2.2f%%", $statistics["taurus"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '雙子座'";
	$linkmysql->query($sql);
	list($statistics["genini"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["genini_percentage"] = sprintf("%2.2f%%", $statistics["genini"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '巨蟹座'";
	$linkmysql->query($sql);
	list($statistics["cancer"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["cancer_percentage"] = sprintf("%2.2f%%", $statistics["cancer"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '獅子座'";
	$linkmysql->query($sql);
	list($statistics["leo"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["leo_percentage"] = sprintf("%2.2f%%", $statistics["leo"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '處女座'";
	$linkmysql->query($sql);
	list($statistics["virgo"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["virgo_percentage"] = sprintf("%2.2f%%", $statistics["virgo"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '天秤座'";
	$linkmysql->query($sql);
	list($statistics["libra"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["libra_percentage"] = sprintf("%2.2f%%", $statistics["libra"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '天蠍座'";
	$linkmysql->query($sql);
	list($statistics["scorpio"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["scorpio_percentage"] = sprintf("%2.2f%%", $statistics["scorpio"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '射手座'";
	$linkmysql->query($sql);
	list($statistics["sagittarius"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["sagittarius_percentage"] = sprintf("%2.2f%%", $statistics["sagittarius"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '魔羯座'";
	$linkmysql->query($sql);
	list($statistics["capricorn"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["capricorn_percentage"] = sprintf("%2.2f%%", $statistics["capricorn"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '水瓶座'";
	$linkmysql->query($sql);
	list($statistics["aquarius"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["aquarius_percentage"] = sprintf("%2.2f%%", $statistics["aquarius"] / $statistics["totalmember"] *100);
	
	$sql  = "SELECT COUNT(*) FROM `user` WHERE `constellation` = '雙魚座'";
	$linkmysql->query($sql);
	list($statistics["pisces"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["pisces_percentage"] = sprintf("%2.2f%%", $statistics["pisces"] / $statistics["totalmember"] *100);	
	
	// 水象星座
	$statistics["water"] = $statistics["pisces"] + $statistics["scorpio"] + $statistics["cancer"];
	$statistics["water_percentage"] = sprintf("%2.2f%%", $statistics["water"] / $statistics["totalmember"] *100);
	
	// 火象星座
	$statistics["fire"] = $statistics["aries"] + $statistics["leo"] + $statistics["sagittarius"];
	$statistics["fire_percentage"] = sprintf("%2.2f%%", $statistics["fire"] / $statistics["totalmember"] *100);
	
	// 風象星座
	$statistics["wind"] = $statistics["genini"] + $statistics["aquarius"] + $statistics["libra"];
	$statistics["wind_percentage"] = sprintf("%2.2f%%", $statistics["wind"] / $statistics["totalmember"] *100);
	
	// 土象星座
	$statistics["soil"] = $statistics["taurus"] + $statistics["virgo"] + $statistics["capricorn"];
	$statistics["soil_percentage"] = sprintf("%2.2f%%", $statistics["soil"] / $statistics["totalmember"] *100);

	// 統計年齡分佈
	// 16 至 20 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '16' AND YEAR(NOW()) - `birth_year` <= '20'";
	$linkmysql->query($sql);
	list($statistics["age_1"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_1_percentage"] = sprintf("%2.2f%%", $statistics["age_1"] / $statistics["totalmember"] *100);	
	
	// 21 至 25 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '21' AND YEAR(NOW()) - `birth_year` <= '25'";
	$linkmysql->query($sql);
	list($statistics["age_2"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_2_percentage"] = sprintf("%2.2f%%", $statistics["age_2"] / $statistics["totalmember"] *100);
	
	// 26 至 30 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '26' AND YEAR(NOW()) - `birth_year` <= '30'";
	$linkmysql->query($sql);
	list($statistics["age_3"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_3_percentage"] = sprintf("%2.2f%%", $statistics["age_3"] / $statistics["totalmember"] *100);
	
	// 31 至 35 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '31' AND YEAR(NOW()) - `birth_year` <= '35'";
	$linkmysql->query($sql);
	list($statistics["age_4"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_4_percentage"] = sprintf("%2.2f%%", $statistics["age_4"] / $statistics["totalmember"] *100);
	
	// 36 至 40 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '36' AND YEAR(NOW()) - `birth_year` <= '40'";
	$linkmysql->query($sql);
	list($statistics["age_5"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_5_percentage"] = sprintf("%2.2f%%", $statistics["age_5"] / $statistics["totalmember"] *100);
	
	// 41 至 45 歲
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` >= '41' AND YEAR(NOW()) - `birth_year` <= '45'";
	$linkmysql->query($sql);
	list($statistics["age_6"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_6_percentage"] = sprintf("%2.2f%%", $statistics["age_6"] / $statistics["totalmember"] *100);
	
	// 45 歲以上
	$sql  = "SELECT COUNT(*) FROM `user` WHERE YEAR(NOW()) - `birth_year` > '45'";
	$linkmysql->query($sql);
	list($statistics["age_7"]) = mysql_fetch_array($linkmysql->listmysql);
	
	$statistics["age_7_percentage"] = sprintf("%2.2f%%", $statistics["age_7"] / $statistics["totalmember"] *100);
	
	// 統計興趣分佈
	$interest = array(		
		"閱讀寫作", "逛街購物", "電腦網路", "聊天哈拉", "音樂欣賞",
		"登山健行", "電玩對戰", "星象命理", "電視電影", "運動釣魚",
		"下棋彈琴", "塑身美容",	"動畫漫畫", "美食烹調", "語言學習",
		"投資理財", "攝影繪畫", "唱歌跳舞", "兜風閒晃", "遊山玩水",
		"吃喝玩樂", "古董收藏",	"發呆睡覺", "占星算命", "園藝花卉",
		"豢養寵物", "其他"
	);
	
	for ($i = 0; $i < count($interest); $i++)
	{
		$val = $interest[$i];
		
		$sql  = "SELECT COUNT(*) FROM `user` WHERE `interest` LIKE '%$val%'";
		$linkmysql->query($sql);
		list($statistics["interest_$i"]) = mysql_fetch_array($linkmysql->listmysql);
		
		$statistics["interest_". $i ."_percentage"] = sprintf("%2.2f%%", $statistics["interest_$i"] / $statistics["totalmember"] *100);
	}
	
	$tpl->assign("act_data", $act_data);
	$tpl->assign("statistics", $statistics);
	$tpl->assign("mainpage", "statistics.html");	
?>