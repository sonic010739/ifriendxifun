<?
	$tpl->assign("mainpage", "member/member.html");

	if (!isset($_GET["sel"]))
	{
		$_GET["sel"] = "detail";
	}

	if ($_GET["sel"] == "modify" && $_SESSION["login"] = 1)
	{
		//----------------------------------------------------------
		// 修改個人資料
		//----------------------------------------------------------

		$username = $_SESSION["username"];

		$linkmysql->init();
		$sql = "SELECT * FROM `user` WHERE `username` = '$username'";
		$linkmysql->query($sql);

		$educations = array(
			"國小", "國中", "高中職", "四技二專", "大學", "碩士", "博士"
		);

		$interest = array(
			"閱讀寫作", "逛街購物", "電腦網路", "聊天哈拉", "音樂欣賞",
			"登山健行", "電玩對戰", "星象命理", "電視電影", "運動釣魚",
			"下棋彈琴", "塑身美容",	"動畫漫畫", "美食烹調", "語言學習",
			"投資理財", "攝影繪畫", "唱歌跳舞", "兜風閒晃", "遊山玩水",
			"吃喝玩樂", "古董收藏",	"發呆睡覺", "占星算命", "園藝花卉",
			"豢養寵物"
		);

		$career = array(
			"批發/零售/傳直銷業", "文教業", "大眾傳播業", "休閒/旅遊/運動業",
			"軟體/半導體/電子/資訊業", "一般服務業", "一般製造業", "農林漁牧/水電資源業",
			"運輸物流及倉儲", "社福/政治/政府/宗教業", "金融/投顧/保險業", "會計/法律/研發/顧問業",
			"建築營造及不動產相關業", "醫療保健/環境衛生業" ,"土石採取/礦業", "家管", "待業中", "其他"
		);

		$citys = array("台北縣市", "高雄縣市", "新竹縣市", "台中縣市", "桃園縣", "基隆市", "宜蘭縣",
			"台南縣市", "雲林縣", "嘉義縣市", "彰化縣", "苗栗縣", "南投縣", "屏東縣", "花蓮縣",
		"台東縣", "澎湖縣", "金門縣", "連江縣", "其他");

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$tmp = explode("-", $data["tel"]);
			$data["tel1"] = $tmp[0];
			$data["tel2"] = $tmp[1];
			$data["tel3"] = $tmp[2];

			$data["career_type"] = "";
			$data["career_detail"] = "";
			$data["career_other"] = "";
			$data["career_title"] = "";
			$career_count = count($career);

			// 教育程度下拉式選單
			$str = "";

			foreach($educations as $edu)
			{
				if ($edu == $data["education"])
				{
					$str .= sprintf("<option value=\"%s\" selected>%s\n", $edu, $edu);
				}
				else
				{
					$str .= sprintf("<option value=\"%s\">%s\n", $edu, $edu);
				}
			}

			$data["education"] = $str;

			if (preg_match("/career: (.+) career_detail: (.+) career_title: (.+)/" , $data["career"], $matche))
			{
				$career_type = trim($matche[1]);
				$career_detail = trim($matche[2]);
				$career_title = trim($matche[3]);

				$data["career_type"] .= "<option value=\"-1\">請選擇\n";

				for ($i = 0; $i < $career_count; $i++)
				{
					if ($career_type == $career[$i]) {
						$data["career_type"] .= sprintf("<option value=\"%s\" selected>%s\n", $career[$i], $career[$i]);
					} else {
						$data["career_type"] .= sprintf("<option value=\"%s\">%s\n", $career[$i], $career[$i]);
					}

					if ($career_type == "其他")
					{
						$data["career_detail"] = sprintf("<option value=\"%s\">%s\n", "請在右邊填寫", "請在右邊填寫");
						$data["career_other"] = "<input name=\"career_other\" type=\"text\" size=\"20\" maxlength=\"20\" value=\"$career_detail\">\n";
					}
					else
					{
						$data["career_detail"] = sprintf("<option value=\"%s\">%s\n", $career_detail, $career_detail);
						$data["career_other"] = "<input name=\"career_other\" type=\"text\" size=\"20\" maxlength=\"20\" disabled>\n";
					}

					$data["career_title"] = "<input name=\"career_title\" type=\"text\" size=\"20\" maxlength=\"20\" class=\"input\" value=\"$career_title\">\n";
				}

			}
			else
			{
				$data["career_type"] .= "<option value=\"-1\" selected>請選擇\n";

				for ($i = 0; $i < $career_count; $i++)
				{
					$data["career_type"] .= sprintf("<option value=\"%s\">%s\n", $career[$i], $career[$i]);
				}

				$data["career_detail"] = "<option value=\"-1\">請先選擇職業類別\n";
				$data["career_other"] = "<input name=\"career_other\" type=\"text\" size=\"20\" maxlength=\"20\" disabled>\n";
				$data["career_title"] = "<input name=\"career_title\" type=\"text\" size=\"20\" maxlength=\"20\" class=\"input\">\n";
			}

			$tmp = explode(",", $data["interest"]);
			$tmp[0] = trim($tmp[0]);
			$tmp[1] = trim($tmp[1]);
			$tmp[2] = trim($tmp[2]);

			$interest_count = count($interest);

			$interest_string = "";
			for ($i = 0; $i < $interest_count; $i++)
			{
				if ($tmp[0] == $interest[$i] || $tmp[1] == $interest[$i] || $tmp[2] == $interest[$i]) {
					$interest_string .= sprintf("<input name=\"interest[]\" type=\"checkbox\" value=\"%s\" checked>%s \n",
						$interest[$i], $interest[$i]);
				} else {
					$interest_string .= sprintf("<input name=\"interest[]\" type=\"checkbox\" value=\"%s\" >%s \n",
						$interest[$i], $interest[$i]);
				}

				if (($i+1)%5 == 0)
				{
					$interest_string .= "\n<br>\n";
				}
			}

			if (preg_match("/其他: (.+)/" , $tmp[0], $matche) ||
				preg_match("/其他: (.+)/" , $tmp[1], $matche) ||
				preg_match("/其他: (.+)/" , $tmp[2], $matche) )
			{
				$interest_string .= sprintf("<input name=\"interest[]\" type=\"checkbox\" value=\"%s\" onClick=\"foo();\" checked>%s \n",
					"其他", "其他，請填寫");
				$interest_other = sprintf("<input name=\"interest_other\" type=\"text\" style=\"width: 190px\" value=\"%s\">",
					$matche[1]);
			}
			else
			{
				$interest_string .= sprintf("<input name=\"interest[]\" type=\"checkbox\" value=\"%s\" onClick=\"foo();\">%s \n",
					"其他", "其他，請填寫");
				$interest_other = "<input name=\"interest_other\" type=\"text\" style=\"width: 190px\" disabled>";
			}

			$data["interest"] = $interest_string;
			$data["interest_other"] = $interest_other;

			// 居住地下拉式選單
			$str = "";

			foreach($citys as $city)
			{
				if ($city == $data["inhabit"])
				{
					$str .= sprintf("<option value=\"%s\" selected>%s\n", $city, $city);
				}
				else
				{
					$str .= sprintf("<option value=\"%s\">%s\n", $city, $city);
				}
			}

			$data["inhabit"] = $str;

			$data["promote"] = ($data["promote"] == "OK") ? 'checked' : '';

			$linkmysql->close_mysql();

			$tpl->assign("memberdata", $data);
			$tpl->assign("memberpage", "member/member.modify.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->URL("index.php");
		}
	}
	else if ($_GET["sel"] == "adminmodify" && $_SESSION["authority"] == "Admin")
	{
		/*
		//----------------------------------------------------------
		// 修改個人資料 - 此功能已經關閉
		//----------------------------------------------------------
		$uid = $_GET["uid"];

		$linkmysql->init();
		$sql = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query( $sql );

		if ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$sexary = array('男', '女');
			$sex_str = "";

			foreach ($sexary as $sex)
			{
				if ($sex == $data[7]) {
					$sex_str .= sprintf("<option value=\"%s\" selected>%s</option>\n", $sex, $sex);
				} else {
					$sex_str .= sprintf("<option value=\"%s\" >%s</option>\n", $sex, $sex);
				}
			}

			$data[7] = $sex_str;

			$constellation = array('牡羊座', '金牛座', '雙子座', '巨蟹座', '獅子座', '處女座',
				'天秤座', '天蠍座', '射手座', '魔羯座', '水瓶座', '雙魚座');
			$constel_str = "";

			foreach ($constellation as $constel)
			{
				if ($constel == $data[12]) {
					$constel_str .= sprintf("<option value=\"%s\" selected>%s</option>\n", $constel, $constel);
				} else {
					$constel_str .= sprintf("<option value=\"%s\" >%s</option>\n", $constel, $constel);
				}
			}

			$data[12] = $constel_str;

			$education = array('國小', '國中', '高中職', '四技二專', '大學', '碩士', '博士');
			$edu_str = "";

			foreach ($education as $edu)
			{
				if ($edu == $data[15]) {
					$edu_str .= sprintf("<option value=\"%s\" selected>%s</option>\n", $edu, $edu);
				} else {
					$edu_str .= sprintf("<option value=\"%s\" >%s</option>\n", $edu, $edu);
				}
			}

			$data[15] = $edu_str;

			$linkmysql->close_mysql();

			$tpl->assign("memberdata", $data);
			$tpl->assign("memberpage", "member/member.modify.admin.html");
		}
		else
		{
			$linkmysql->close_mysql();

			$message = "找不到該會員(編號:$uid)的資料";
			$message = urlencode($message);
			$tool->URL("index.php?act=msg&msg=$message");
		}
		*/
	}
	else if ($_GET["sel"] == "detail" && $_SESSION["login"] = 1)
	{
		//-------------------------------------------------
		// 檢視會員詳細資料
		//-------------------------------------------------

		$uid = $_SESSION["uid"];

		$linkmysql->init();
		$sql = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["interest"] = nl2br($data["interest"]);

			if (preg_match("/career: (.+) career_detail: (.+) career_title: (.+)/" , $data["career"], $matche))
			{
				$data["career"] = $matche[1];
				$data["career_detail"] = $matche[2];
				$data["career_title"] = $matche[3];
			}

			$data["status"] = ($data["status"] == "Invalidate") ? "<font color=\"red\">未驗證</font>" : "<font color=\"blue\">已驗證</font>";

			if ($data["authority"] == "User")
			{
				$data["authority"] = "使用者";
			}
			else if ($data["authority"] == "EO")
			{
				$data["authority"] = "EO";
			}
			else if ($data["authority"] == "Admin")
			{
				$data["authority"] = "<font color=\"red\">管理員</font>";
			}

			// 帳號狀態，是否被停權
			$sql  = "SELECT `black_serial` ";
			$sql .= "FROM `blacklist` ";
			$sql .= "WHERE `black_id` = '$uid' AND `lock` = 'true' ";
			$linkmysql->query($sql);

			if (list($data["black_status"]) = mysql_fetch_array($linkmysql->listmysql))
			{
				// 可再加入停權的詳細資訊
				$data["black_status"] = "<font color=\"red\">停權中</font>";
			}
			else
			{
				$data["black_status"] = "<font color=\"green\">正常</font>";
			}


			$data["show_myact"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;uid=%d\">檢視我的活動記錄</a>", $data["uid"]);
			$data["show_coupon"] = sprintf("<a href=\"./index.php?act=coupon&amp;sel=list&amp;uid=%d\">檢視我的優惠卷記錄</a>", $data["uid"]);

			// 取出會員報名活動紀錄
			$data["act_join_curr"] = 0;
			$data["act_join_past"] = 0;

			$sql  = "SELECT `a`.`aid`, `a`.`status`, `aj`.`join_status` ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `u`.`uid` = '$uid'";
			$linkmysql->query($sql);

			while ($act_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if (($act_data["status"] == "OPEN" || $act_data["status"] == "PROCEED") && $act_data["join_status"] == "join") {
					$data["act_join_curr"]++;
				}

				if ($act_data["status"] == "CLOSE" && $act_data["join_status"] == "join") {
					$data["act_join_past"]++;
				}
			}

			$data["act_join_curr"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currjoin&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_curr"]);
			$data["act_join_past"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pastjoin&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_past"]);

			// 取出會員取消活動記錄
			$data["act_join_cancel"] = 0;

			$sql = "SELECT COUNT(*) FROM `activitiecancel` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["act_join_cancel"]) = mysql_fetch_array($linkmysql->listmysql);
			
			$data["act_join_cancel"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancel&amp;uid=%d\">%s</a>", $data["uid"], $data["act_join_cancel"]);

			// 優惠卷張數
			$sql = "SELECT COUNT(*) FROM `coupon` WHERE `uid` = '$uid' AND `use_time` IS NULL";
			$linkmysql->query($sql);
			list($data["coupon_unuse"]) = mysql_fetch_array($linkmysql->listmysql);

			$sql = "SELECT COUNT(*) FROM `coupon` WHERE `uid` = '$uid' AND `use_time` IS NOT NULL";
			$linkmysql->query($sql);
			list($data["coupon_used"]) = mysql_fetch_array($linkmysql->listmysql);

			$data["coupon_total"] = $data["coupon_unuse"] + $data["coupon_used"];
			$data["coupon_total"] = sprintf("<a href=\"./index.php?act=coupon&amp;sel=list&amp;uid=%d\">%d</a>", $data["uid"], $data["coupon_total"]);

			// 參加活動繳費資料
			$data["charge_total_pay"] = 0;
			$data["charge_unpay"] = 0;
			$data["charge_unpay_overdue"] = 0;
			$data["charge_paid"] = 0;

			$sql  = "SELECT `fees`, `process_time`, `pay_time` ";
			$sql .= "FROM `charge_ibon` ";
			$sql .= "WHERE `uid` = '$uid'";
			$linkmysql->query($sql);

			while ($charge_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($charge_data["pay_time"] != "")
				{
					$data["charge_paid"]++;
					$data["charge_total_pay"] += $charge_data["fees"];
				}

				if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $charge_data["process_time"], $matche))
				{
					$charge_data["process_time"] = date("Y-m-d", mktime(0, 0, 0, $matche[2], $matche[3]+7, $matche[1]));

					if ($charge_data["pay_time"] == "" && $charge_data["process_time"] > date("Y-m-d"))
					{
						$data["charge_unpay"]++;
					}
					else if ($charge_data["pay_time"] == "" && $charge_data["process_time"] <= date("Y-m-d"))
					{
						$data["charge_unpay_overdue"]++;
					}
				}
			}

			// 舉辦活動次數
			$data["act_hold_open"] = 0;
			$data["act_hold_proceed"] = 0;
			$data["act_hold_close"] = 0;
			$data["act_hold_cancel"] = 0;

			$sql  = "SELECT `status` ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$linkmysql->query($sql);

			while ($act_data = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($act_data["status"] == "OPEN")
				{
					$data["act_hold_open"]++;
				}
				else if ($act_data["status"] == "PROCEED")
				{
					$data["act_hold_proceed"]++;
				}
				else if ($act_data["status"] == "CLOSE")
				{
					$data["act_hold_close"]++;
				}
				else if ($act_data["status"] == "CANCEL")
				{
					$data["act_hold_cancel"]++;
				}
			}

			$data["act_hold_open"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_open"]);
			$data["act_hold_proceed"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_proceed"]);
			$data["act_hold_close"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pasthold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_close"]);
			$data["act_hold_cancel"] = sprintf("<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancelhold&amp;uid=%d\">%s</a>", $data["uid"], $data["act_hold_cancel"]);

			// 總計舉辦活動收入
			$sql  = "SELECT SUM(`c`.`fees`) ";
			$sql .= "FROM `charge_ibon` c ";
			$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `c`.`aid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' AND `c`.`pay_time` IS NOT NULL";
			$linkmysql->query($sql);

			list($data["act_charge_income"]) = mysql_fetch_array($linkmysql->listmysql);

			if ($data["act_charge_income"] == "")
			{
				$data["act_charge_income"] = 0;
			}

			// 我介紹的朋友人數
			$sql = "SELECT COUNT(*) FROM `introduction` WHERE `intro_uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["intro_count"]) = mysql_fetch_array($linkmysql->listmysql);

			// 透過我報名的人數
			$sql = "SELECT `count` FROM `recommand` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			list($data["intro_join"]) = mysql_fetch_array($linkmysql->listmysql);

			if ($data["intro_join"] == "")
			{
				$data["intro_join"] = "0";
			}

			$linkmysql->close_mysql();
			$tpl->assign("memberdata", $data);
			$tpl->assign("memberpage", "member/member.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();

			$message = "找不到該會員資料";
			$message = urlencode($message);
			$tool->URL("index.php?act=msg&msg=$message");
		}
	}
	else if ($_GET["sel"] == "forgotpasswd")
	{
		//-------------------------------------------------
		// 顯示忘記密碼輸入頁面
		//-------------------------------------------------

		$tpl->assign("mainpage", "member/member.forgotpasswd.html");
	}
	else if ($_GET["sel"] == "revalidater")
	{
		//-------------------------------------------------
		// 顯示忘記重新寄送驗證信頁面
		//-------------------------------------------------

		$tpl->assign("mainpage", "member/member.revalidater.html");
	}
	else if ($_GET["sel"] == "accuseblack" && $_SESSION["authority"] != "User")
	{
		//-------------------------------------------------
		// 提報某會員為黑名單
		//-------------------------------------------------

		$uid = $_GET["uid"];
		$aid = $_GET["aid"];
		$accuse_id = $_SESSION["uid"];
		$accusedata = array();

		$linkmysql->init();

		// 取出被提報會員的資料
		$sql = "SELECT `username` FROM `user` WHERE `user`.`uid` = '$uid'";
		$linkmysql->query($sql);

		if (list($username) = mysql_fetch_row($linkmysql->listmysql))
		{
			$accusedata["uid"] = $uid;
			$accusedata["username"] = $tool->ShowMemberLink( $uid, $username);
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該會員資料");
		}

		// 取出提報者的資料
		$sql = "SELECT `username` FROM `user` WHERE `user`.`uid` = '$accuse_id'";
		$linkmysql->query($sql);

		if (list($accuse_name) = mysql_fetch_row($linkmysql->listmysql))
		{
			$accusedata["accusename"] = $tool->ShowMemberLink( $accuse_id, $accuse_name);
			$accusedata["accuseid"] = $accuse_id;
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該會員資料");
		}

		// 於哪場活動被提報活動資料
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		if ($actdata = mysql_fetch_array($linkmysql->listmysql))
		{
			$accusedata["act_name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\" >%s</a>", $aid, $actdata["name"]);
			$accusedata["aid"] = $aid;

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
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料", "回到活動列表", "index.php?act=activitielist");
		}

		$tpl->assign("accusedata", $accusedata);
		$tpl->assign("mainpage", "member/blacklist.accuse.html");
	}
	else
	{
		$tool->URL("index.php");
	}

?>