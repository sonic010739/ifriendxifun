<?
	session_start();
	include "smarty.lib.php";

	if ($_GET["act"] == "add" && ($_SESSION["authority"] == "Admin" || $_SESSION["authority"] == "EO"))
	{
		//---------------------------------------
		//  新增活動
		//---------------------------------------

		$uid = $_SESSION["uid"];
		$name 			= $_POST["name"];
		$act_date 		= $_POST["act_date"];
		$act_time_hour 	= $_POST["act_time_hour"];
		$act_time_minute= $_POST["act_time_minute"];
		$join_deadline	= $_POST["join_deadline"];
		$place 			= $_POST["place"];
		$topic 			= $_POST["topic"];
		$group 			= $_POST["group"];

		$sex_limit   	= $_POST["sex_limit"];
		$male_limit 	= $_POST["male_limit"];
		$female_limit 	= $_POST["female_limit"];
		$people_limit 	= $_POST["people_limit"];

		$age_limit 		= $_POST["age_limit"];
		$age_lb 		= $_POST["age_lb"];
		$age_ub 		= $_POST["age_ub"];
		$male_age_lb 	= $_POST["male_age_lb"];
		$male_age_ub 	= $_POST["male_age_ub"];
		$female_age_lb 	= $_POST["female_age_lb"];
		$female_age_ub 	= $_POST["female_age_ub"];

		$charge_limit 	= $_POST["charge_limit"];
		$charge 		= $_POST["charge"];
		$male_charge 	= $_POST["male_charge"];
		$female_charge 	= $_POST["female_charge"];

		$use_discount	= $_POST["use_discount"];
		$use_coupon 	= $_POST["use_coupon"];
		$use_match		= $_POST["use_match"];
		$use_newmatch 	= $_POST["use_newmatch"];
		$decription 	= $_POST["decription"];

		$act_time = $act_time_hour . ":" . $act_time_minute;

		// 人數及性別限制
		// type1	=>	限制男女人數，男__人，女__人
		// type2	=> 	不限性別，共__人
		// type3	=>	不限人數
		switch ($sex_limit)
		{
			case type1:
				$people_limit = sprintf("Sex limit males: %2d, females: %2d.", $male_limit, $female_limit);
				break;
			case type2:
				$people_limit = sprintf("No limit total: %3d.", $people_limit);
				break;
			case type3:
				$people_limit = sprintf("No limit.");
				break;
			default:
				$tool->ShowMsgPage("人數及性別限制設定錯誤");
				die;
				break;
		}

		// 年齡限制
		// type1	=>	男女年齡限制___歲 至__歲
		// type2	=> 	男性年齡限制限制___歲 至__歲，女性年齡限制限制___歲 至__歲
		// type3	=>	不限制年齡
		switch ($age_limit)
		{
			case type1:
				$age_limit = sprintf("Age limit lb: %2d, ub: %2d.", $age_lb, $age_ub);
				break;
			case type2:
				$age_limit = sprintf("Age limit male_lb: %2d, male_ub: %2d, female_lb: %2d, female_ub: %2d.",
					$male_age_lb, $male_age_ub, $female_age_lb, $female_age_ub);
				break;
			case type3:
				$age_limit = sprintf("No age limit.");
				break;
			default:
				$tool->ShowMsgPage("年齡限制設定錯誤");
				die;
				break;
		}

		// 男女不同價位收費機制
		switch ($charge_limit)
		{
			case type1:
				$charge_limit = sprintf("All: %d", $charge);
				break;
			case type2:
				$charge_limit = sprintf("Male: %d, Female: %d", $male_charge, $female_charge);
				break;
			default:
				$tool->ShowMsgPage("活動費用設定錯誤");
				break;
		}

		$linkmysql->init();

		$sql  = "INSERT INTO `activitie`( ";
		$sql .= "`aid`, `ownerid`, `name`, `place`, `topic`, `group`,`act_date`, `act_time`, ";
		$sql .= "`join_deadline`, `people_limit`, `age_limit`, `charge`, `decription`, ";
		$sql .= "`use_discount`, `use_coupon`, `use_match`, `use_newmatch`, `males`, `females`, `status`) ";
		$sql .= "VALUES ('', '$uid', '$name', '$place', '$topic', '$group', '$act_date', '$act_time', ";
		$sql .= "'$join_deadline', '$people_limit', '$age_limit', '$charge_limit', '$decription', ";
		$sql .= "'$use_discount', '$use_coupon', '$use_match', '$use_newmatch', '0', '0', 'OPEN');";
		$linkmysql->query($sql);

		// 取出會員資料
		$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);
		$member = mysql_fetch_array($linkmysql->listmysql);

		$mailinfo = array();
		$mailinfo["realname"] = $member["realname"];;
		$mailinfo["act_date"] = $act_date;
		$mailinfo["act_name"] = $name;

		$iFMail->AddActMail($member["email"], $member["realname"], $mailinfo);

		$linkmysql->close_mysql();
		$tool->ShowMsgPage("活動新增完成", "回活動系統", "index.php?act=activitieEO");
	}
	else if ($_GET["act"] == "modify" && ($_SESSION["authority"] == "Admin" || $_SESSION["authority"] == "EO"))
	{
		//---------------------------------------
		// 修改活動
		//---------------------------------------

		$aid 			= $_POST["aid"];
		$name 			= $_POST["name"];
		$topic 			= $_POST["topic"];
		$group 			= $_POST["group"];

		$sex_limit   	= $_POST["sex_limit"];
		$male_limit 	= $_POST["male_limit"];
		$female_limit 	= $_POST["female_limit"];
		$people_limit 	= $_POST["people_limit"];

		$age_limit 		= $_POST["age_limit"];
		$age_lb 		= $_POST["age_lb"];
		$age_ub 		= $_POST["age_ub"];
		$male_age_lb 	= $_POST["male_age_lb"];
		$male_age_ub 	= $_POST["male_age_ub"];
		$female_age_lb 	= $_POST["female_age_lb"];
		$female_age_ub 	= $_POST["female_age_ub"];

		$decription 	= $_POST["decription"];

		// 人數及性別限制
		// type1	=>	限制男女人數，男__人，女__人
		// type2	=> 	不限性別，共__人
		// type3	=>	不限人數
		switch ($sex_limit)
		{
			case type1:
				$people_limit = sprintf("Sex limit males: %2d, females: %2d.", $male_limit, $female_limit);
				break;
			case type2:
				$people_limit = sprintf("No limit total: %3d.", $people_limit);
				break;
			case type3:
				$people_limit = sprintf("No limit.");
				break;
			default:
				$tool->ShowMsgPage("人數及性別限制設定錯誤");
				die;
				break;
		}

		// 年齡限制
		// type1	=>	男女年齡限制___歲 至__歲
		// type2	=> 	男性年齡限制限制___歲 至__歲，女性年齡限制限制___歲 至__歲
		// type3	=>	不限制年齡
		switch ($age_limit)
		{
			case type1:
				$age_limit = sprintf("Age limit lb: %2d, ub: %2d.", $age_lb, $age_ub);
				break;
			case type2:
				$age_limit = sprintf("Age limit male_lb: %2d, male_ub: %2d, female_lb: %2d, female_ub: %2d.",
					$male_age_lb, $male_age_ub, $female_age_lb, $female_age_ub);
				break;
			case type3:
				$age_limit = sprintf("No age limit.");
				break;
			default:
				$tool->ShowMsgPage("年齡限制設定錯誤");
				die;
				break;
		}

		$linkmysql->init();

		$sql  = "UPDATE `activitie` SET ";
		$sql .= "`name` = '$name', ";
		$sql .= "`topic` = '$topic', ";
		$sql .= "`group` = '$group', ";
		$sql .= "`people_limit` = '$people_limit', ";
		$sql .= "`age_limit` = '$age_limit', ";
		$sql .= "`decription` = '$decription' ";
		$sql .= "WHERE `activitie`.`aid` = '$aid'";
		$linkmysql->query($sql);

		$linkmysql->close_mysql();
		
		$tool->URL("index.php?act=activities&sel=detail&aid=$aid");
	}
	else if ($_GET["act"] == "modify2" && $_SESSION["authority"] == "Admin")
	{
		//---------------------------------------
		// 修改活動 - 管理者用
		//---------------------------------------

		$aid 			= $_POST["aid"];
		$name 			= $_POST["name"];
		$act_date 		= $_POST["act_date"];
		$act_time_hour 	= $_POST["act_time_hour"];
		$act_time_minute= $_POST["act_time_minute"];
		$join_deadline	= $_POST["join_deadline"];
		$place 			= $_POST["place"];
		$topic 			= $_POST["topic"];
		$group 			= $_POST["group"];

		$sex_limit   	= $_POST["sex_limit"];
		$male_limit 	= $_POST["male_limit"];
		$female_limit 	= $_POST["female_limit"];
		$people_limit 	= $_POST["people_limit"];

		$age_limit 		= $_POST["age_limit"];
		$age_lb 		= $_POST["age_lb"];
		$age_ub 		= $_POST["age_ub"];
		$male_age_lb 	= $_POST["male_age_lb"];
		$male_age_ub 	= $_POST["male_age_ub"];
		$female_age_lb 	= $_POST["female_age_lb"];
		$female_age_ub 	= $_POST["female_age_ub"];

		$charge_limit 	= $_POST["charge_limit"];
		$charge 		= $_POST["charge"];
		$male_charge 	= $_POST["male_charge"];
		$female_charge 	= $_POST["female_charge"];

		$use_discount	= $_POST["use_discount"];
		$use_coupon 	= $_POST["use_coupon"];
		$use_match		= $_POST["use_match"];
		$match_type		= $_POST["match_type"];
		$use_newmatch 	= $_POST["use_newmatch"];
		$decription 	= $_POST["decription"];
		$EO 			= $_POST["EO"];
		$status 		= $_POST["status"];

		$act_time = $act_time_hour . ":" . $act_time_minute;

		// 人數及性別限制
		// type1	=>	限制男女人數，男__人，女__人
		// type2	=> 	不限性別，共__人
		// type3	=>	不限人數
		switch ($sex_limit)
		{
			case type1:
				$people_limit = sprintf("Sex limit males: %2d, females: %2d.", $male_limit, $female_limit);
				break;
			case type2:
				$people_limit = sprintf("No limit total: %3d.", $people_limit);
				break;
			case type3:
				$people_limit = sprintf("No limit.");
				break;
			default:
				$tool->ShowMsgPage("人數及性別限制設定錯誤");
				die;
				break;
		}

		// 年齡限制
		// type1	=>	男女年齡限制___歲 至__歲
		// type2	=> 	男性年齡限制限制___歲 至__歲，女性年齡限制限制___歲 至__歲
		// type3	=>	不限制年齡
		switch ($age_limit)
		{
			case type1:
				$age_limit = sprintf("Age limit lb: %2d, ub: %2d.", $age_lb, $age_ub);
				break;
			case type2:
				$age_limit = sprintf("Age limit male_lb: %2d, male_ub: %2d, female_lb: %2d, female_ub: %2d.",
					$male_age_lb, $male_age_ub, $female_age_lb, $female_age_ub);
				break;
			case type3:
				$age_limit = sprintf("No age limit.");
				break;
			default:
				$tool->ShowMsgPage("年齡限制設定錯誤");
				die;
				break;
		}

		// 男女不同價位收費機制
		switch ($charge_limit)
		{
			case type1:
				$charge_limit = sprintf("All: %d", $charge);
				break;
			case type2:
				$charge_limit = sprintf("Male: %d, Female: %d", $male_charge, $female_charge);
				break;
			default:
				$tool->ShowMsgPage("活動費用設定錯誤");
				die;
				break;
		}

		$str = '';
		if (is_array($match_type))
		{
			foreach ($match_type as $type)
			{
				$str .= $type . ',';
			}
		}
		else
		{
			if ($use_match == 'YES')
			{
				$tool->ShowMsgPage("未設定活動配對要給予的資料");
			}
		}

		$match_type = $str;


		$linkmysql->init();

		$sql  = "UPDATE `activitie` SET ";
		$sql .= "`name` = '$name', ";
		$sql .= "`ownerid` = '$EO', ";
		$sql .= "`place` = '$place', ";
		$sql .= "`topic` = '$topic', ";
		$sql .= "`group` = '$group', ";
		$sql .= "`act_date` = '$act_date', ";
		$sql .= "`act_time` = '$act_time', ";
		$sql .= "`join_deadline` = '$join_deadline', ";
		$sql .= "`people_limit` = '$people_limit', ";
		$sql .= "`age_limit` = '$age_limit', ";
		$sql .= "`charge` = '$charge_limit', ";
		$sql .= "`decription` = '$decription', ";
		$sql .= "`use_discount` = '$use_discount', ";
		$sql .= "`use_coupon` = '$use_coupon', ";
		$sql .= "`use_match` = '$use_match', ";
		$sql .= "`match_type` = '$match_type', ";
		$sql .= "`use_newmatch` = '$use_newmatch', ";
		$sql .= "`status` = '$status' ";
		$sql .= "WHERE `activitie`.`aid` = '$aid'";
		$linkmysql->query($sql);

		$linkmysql->close_mysql();

		$tool->URL("index.php?act=activities&sel=detail&aid=$aid");
	}
	else if ($_GET["act"] == "EOcancel")
	{
		//---------------------------------------
		// EO取消舉辦活動
		//---------------------------------------

		$aid = $_POST["aid"];
		$uid = $_POST["uid"];
		$reason = $_POST["reason"];

		$linkmysql->init();

		// 取出活動資料
		$sql  = "SELECT `a`.*, `p`.`placename` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "WHERE `a`.`aid` = '$aid'";
		$linkmysql->query($sql);
		
		if (!($data = mysql_fetch_array($linkmysql->listmysql)))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料");
		}
		
		if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")
		{
			if ($data["status"] == "OPEN")
			{
				// 取消原因
				$sql  ="INSERT INTO `cancelapply` ( `id`, `type`, `uid`, `aid`, ";
				$sql .="`reason`, `apply_time`, `verify_id`, `comment`, `result`, `verify_time`) ";
				$sql .="VALUES ('', 'EOCancel', '$uid', '$aid', '$reason', NOW(), NULL , NULL , NULL , NULL );";
				$linkmysql->query($sql);

				//變更活動的狀態
				$sql  = "UPDATE `activitie` ";
				$sql .= "SET `status` = 'Apply_Cancel' ";
				$sql .= "WHERE `activitie`.`aid` = '$aid' LIMIT 1; ";
				$linkmysql->query($sql);

				// 取出EO會員資料
				$sql  = "SELECT * FROM `user` WHERE `uid` = '". $data["ownerid"] . "'";
				$linkmysql->query($sql);
				$member = mysql_fetch_array($linkmysql->listmysql);

				$mailinfo = array();
				$mailinfo["realname"] = $member["realname"];
				$mailinfo["act_date"] = $data["act_date"];
				$mailinfo["act_time"] = $data["act_time"];
				$mailinfo["act_place"] = $data["placename"];
				$mailinfo["act_name"] = $data["name"];

				$iFMail->CancelActApplyMail($member["email"], $member["username"], $mailinfo);

				$linkmysql->close_mysql();
				$tool->ShowMsgPage("取消申請已送出", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名的狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("權限不足無法取消活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["act"] == "SetProceed")
	{
		//---------------------------------------
		// 設定活動狀態為進行中
		//---------------------------------------

		$aid = $_GET["aid"];
		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		if (!($data = mysql_fetch_array($linkmysql->listmysql)))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料");
		}

		if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")
		{
			if ($data["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名的狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}			
			
			// 活動報名截止時間過後才可變更為進行活動
			$date = explode("-", $data["join_deadline"]);
			$deadline = date("Y-m-d H:i:s", mktime(23, 59, 59, $date[1], $date[2], $date[0]));
			
			if ($deadline > date("Y-m-d H:i:s"))
			{
				$linkmysql->close_mysql();
				$message = "活動狀態無法變更為進行活動，" . $deadline . " 後才可變更為進行活動";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$use_match = $data["use_match"] == "YES" ? 1 : 0;
			$use_newmatch = $data["use_newmatch"] == "YES" ? 1 : 0;

			// 找出是否有申請取消參與且尚未審核的會員
			$sql  = "SELECT `serial` FROM `activitiejoin`  ";
			$sql .= "WHERE `aid` = '$aid' AND `join_status` = 'apply_cancel' ";
			$linkmysql->query($sql);

			if ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				$linkmysql->close_mysql();
				$message = "尚有申請取消參與的會員尚未審核，<br/>無法進行活動";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			// 檢查登記參加但未繳費的會員
			$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id` ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join'";
			$linkmysql->query($sql);

			$joinlist = array();

			while ($joindata = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push( $joinlist, $joindata);
			}

			foreach ($joinlist as $join)
			{
				if ($join["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

					if (empty($pay_time))
					{
						$linkmysql->close_mysql();
						$message = "尚有登記參加但未繳費的會員，<br/>無法進行活動";
						$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}
			}

			// 取出之前新增的新版配對資料
			$sql  = "SELECT * ";
			$sql .= "FROM `activitiematch`";
			$sql .= "WHERE `aid` = '$aid'";
			$linkmysql->query($sql);

			$act_matches = array();

			while ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($act_matches, $data);
			}

			// 刪除之前新版配對的資料
			foreach($act_matches as $matches)
			{
				$serial = $matches["serial"];
				$sql  = "DELETE FROM `activitiematch` ";
				$sql .= "WHERE `activitiematch`.`serial` = '$serial' LIMIT 1; ";
				$linkmysql->query($sql);
			}

			unset($act_matches);

			// 變更活動的狀態
			$sql  = "UPDATE `activitie` ";
			$sql .= "SET `status` = 'PROCEED' ";
			$sql .= "WHERE `activitie`.`aid` = '$aid' LIMIT 1; ";
			$linkmysql->query($sql);

			// 取出有參與活動的會員，設定編號
			$sql  = "SELECT `aj`.`uid`, `u`.`sex` ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
			$sql .= "ORDER BY `u`.`sex` ASC, `aj`.`serial` ASC";
			$linkmysql->query($sql);

			$joinmembers = array();

			while ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($joinmembers, $data);
			}

			if ($use_newmatch == 1)
			{
				$email = "";
				$msn   = "";
				$tel   = "";

				foreach ($joinmembers as $join)
				{
					$uid = $join["uid"];
					$email .= $uid . ", ";
					$msn   .= $uid . ", ";
				}

				foreach ($joinmembers as $join)
				{
					$uid = $join["uid"];

					// 加入新版配對資料表中
					$sql  = "INSERT INTO `activitiematch` ( ";
					$sql .= "`serial`, `aid`, `uid`, `email`, `msn`, `tel` ) ";
					$sql .= "VALUES ( ";
					$sql .= "'', '$aid', '$uid', '$email', '$msn', '$tel' ) ";
					$linkmysql->query($sql);
				}
			}

			if ($use_match == 1 || $use_newmatch == 1)
			{
				$no = 0;

				foreach ($joinmembers as $join)
				{
					$uid = $join["uid"];
					$no++;

					// 設定所有會員的編號
					$sql  = "UPDATE `activitiejoin` SET `no` = '$no' ";
					$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1 ; ";

					$linkmysql->query($sql);
				}
			}

			$linkmysql->close_mysql();
			$tool->ShowMsgPage("活動狀態已變更為進行中", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");			
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("權限不足無法變更活動狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["act"] == "SetClose")
	{
		//---------------------------------------
		// 設定活動狀態為關閉
		//---------------------------------------

		$aid = $_GET["aid"];
		$linkmysql->init();

		$sql  = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);
		
		if (!($data = mysql_fetch_array($linkmysql->listmysql)))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料");
		}
		
		if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")
		{
			if ($data["status"] == "PROCEED")
			{
				$date = explode("-", $data["act_date"]);
				$time = explode(":", $data["act_time"]);

				// 活動時間過後才可關閉活動
				$deadline = date("Y-m-d H:i:s", mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));

				if ($deadline > date("Y-m-d H:i:s"))
				{
					$linkmysql->close_mysql();
					$message = "活動狀態無法變更為關閉活動，<br/>" . $deadline . "後才可變更為關閉活動";
					$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}

				// 取消參加的會員，退還優惠卷
				$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id` ";
				$sql .= "FROM `activitiejoin` aj ";
				$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'cancel'";
				$linkmysql->query($sql);

				$joinlist = array();
				while ($joindata = mysql_fetch_array($linkmysql->listmysql))
				{
					array_push( $joinlist, $joindata);
				}

				foreach ($joinlist as $join)
				{
					$give_id = 1;
					$uid = $join["uid"];

					if ($join["charge_type"] == "iBon")
					{
						$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
						$linkmysql->query($sql);
						list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

						if (!empty($pay_time))
						{
							// 已繳費者，需給予活動抵用卷
							$reason = "會員取消參加活動「". $data["name"]. "」，使用FamiPort繳費完成的會員給予活動優惠卷。";

							$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
							$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
							$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
							$sql .= "'$reason', NOW() , NULL , NULL ); ";
							$linkmysql->query($sql);
						}
					}
					else if ($join["charge_type"] == "coupon" && $data["use_coupon"] = "YES")
					{
						$reason = "會員取消參加活動「". $data["name"]. "」，使用活動優惠卷報名的會員給予活動優惠卷。";

						$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
						$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
						$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
						$sql .= "'$reason', NOW() , NULL , NULL ); ";
						$linkmysql->query($sql);
					}
				}

				// 將活動狀態變更為關閉
				$sql  = "UPDATE `activitie` ";
				$sql .= "SET `status` = 'CLOSE' ";
				$sql .= "WHERE `activitie`.`aid` = '$aid' LIMIT 1; ";
				$linkmysql->query($sql);

				// 取出EO會員資料
				$sql  = "SELECT * FROM `user` WHERE `uid` = '". $data["ownerid"] . "'";
				$linkmysql->query($sql);
				$EOmember = mysql_fetch_array($linkmysql->listmysql);

				// 取出有參加的會員資料
				$sql  = "SELECT `u`.`username`, `u`.`realname`, `u`.`email` ";
				$sql .= "FROM `activitiejoin` aj ";
				$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
				$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
				$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
				$sql .= "ORDER BY `aj`.`join_status` ASC , `u`.`sex` ASC, `aj`.`serial` ASC";
				$linkmysql->query($sql);
				
				while ($member = mysql_fetch_array($linkmysql->listmysql))
				{
					$mailinfo = array();
					$mailinfo["realname"] = $member["realname"];
					$mailinfo["EOname"] = $EOmember["realname"];
				
					// 寄送會員活動感謝信
					$iFMail->ActThanksMail($member["email"], $member["username"], $mailinfo);
					
					// 寄送新版配對通知信
					if ($data['use_newmatch'] == 'YES')
					{
						$iFMail->NewMatchNotify($member["email"], $member["username"], $mailinfo);
					}
				}

				// 寄送EO活動感謝信
				$mailinfo = array();
				$mailinfo["realname"] = $EOmember["realname"];
				$mailinfo["act_name"] = $data["name"];

				$iFMail->EOActThanksMail($EOmember["email"], $EOmember["username"], $mailinfo);

				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動狀態已變更為已經關閉", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非進行中的狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("權限不足無法變更活動狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["act"] == "search")
	{
		//----------------------------------------
		// 篩選活動
		//----------------------------------------
		
		$topic = $_POST["topic"];
		$city = $_POST["city"];
		$EO = $_POST["EO"];
		$act_year_lb = $_POST["act_year_lb"];
		$act_month_lb = $_POST["act_month_lb"];
		$act_day_lb = $_POST["act_day_lb"];
		$act_year_ub = $_POST["act_year_ub"];
		$act_month_ub = $_POST["act_month_ub"];
		$act_day_ub = $_POST["act_day_ub"];

		$_SESSION["topic"] = $topic;
		$_SESSION["city"] = $city;
		$_SESSION["EO"] = $EO;
		$_SESSION["act_year_lb"] = $act_year_lb;
		$_SESSION["act_month_lb"] = $act_month_lb;
		$_SESSION["act_day_lb"] = $act_day_lb;
		$_SESSION["act_year_ub"] = $act_year_ub;
		$_SESSION["act_month_ub"] = $act_month_ub;
		$_SESSION["act_day_ub"] = $act_day_ub;

		if (isset($_POST["EO"]))
		{
			$tool->URL("index.php?act=activitiemanage&type=Filter");
		}
		else
		{
			$tool->URL("index.php?act=activitielist&type=Filter");
		}
	}
	else if ($_GET["act"] == "ReCount")
	{
		//----------------------------------------
		// 重新統計活動人數
		//----------------------------------------
		
		$linkmysql->init();

		$aid = $_GET["aid"];

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

		$linkmysql->close_mysql();
		$tool->URL($_SERVER['HTTP_REFERER']);
	}
	else if ($_GET["act"] == "SendMail")
	{
		//----------------------------------------
		// EO使用信件系統寄送郵件
		//----------------------------------------
		
		$uid = $_SESSION["uid"];
		$aid = $_POST["aid"];
		$to = $_POST["to"];
		$subject = $_POST["subject"];
		$body_prefix = $_POST["body_prefix"];
		$body = $_POST["body"];
		$body_suffix = $_POST["body_suffix"];

		// 上傳的附件
		$upload_dir = "upload/";
		$new_file = $_FILES['attach'];
		$file_name = $new_file['name'];
		$file_tmp = $new_file['tmp_name'];

		//$new_file['name'] = iconv("UTF-8", "big5", $new_file['name']);
		move_uploaded_file($file_tmp, $upload_dir.$new_file['name']);

		if ($body == ""){
			$tool->ShowMsgPage("信件的內文未填寫");
		}

		// 將 \" 轉成 "
		$body = str_replace('\\"', '"', $body);

		// 合併內文及文首文尾
		$message_body  = $body_prefix;
		$message_body .= $body;
		$message_body .= "<hr /><br />";
		$message_body .= $body_suffix;
		$message_body .= "<hr /><br />";

		$linkmysql->init();

		// 取出寄送者的資料
		$sql  = "SELECT * ";
		$sql .= "FROM `user` ";
		$sql .= "WHERE `uid` = '$uid' ";
		$linkmysql->query($sql);

		$user_data = mysql_fetch_array($linkmysql->listmysql);

		// 取出活動資料
		$sql  = "SELECT * ";
		$sql .= "FROM `activitie` ";
		$sql .= "WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		$act_data = mysql_fetch_array($linkmysql->listmysql);

		// 取出所有參加活動的會員
		$sql  = "SELECT `u`.`realname`, `u`.`email`, `aj`.`charge_type`, `aj`.`charge_id` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND `aj`.`join_status` = 'join' ";
		$linkmysql->query($sql);

		$joindata = array();

		while ($tmp = mysql_fetch_array($linkmysql->listmysql))
		{
			print "@";
			array_push( $joindata, $tmp);
		}

		if (count($joindata) == 0)
		{
			$tool->ShowMsgPage("目前沒有參加活動的會員");
		}

		$count = 0;

		// 根據選擇的類別寄送信件
		if ($to == "All")
		{
			foreach($joindata as $join)
			{
				$count++;
				$iFMail->SendMail($join["email"], $join["realname"], $subject, $message_body, $upload_dir.$new_file['name']);
			}
		}
		else if ($to == "Paid")
		{
			foreach($joindata as $join)
			{
				if ($join["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

					if (!empty($pay_time))
					{
						$count++;
						$iFMail->SendMail($join["email"], $join["realname"], $subject, $message_body, $upload_dir.$new_file['name']);
					}
				}
				else if ($join["charge_type"] == "coupon")
				{
					$count++;
					$iFMail->SendMail($join["email"], $join["realname"], $subject, $message_body, $upload_dir.$new_file['name']);
				}
			}
		}
		else if ($to == "Unpaid")
		{
			foreach($joindata as $join)
			{
				if ($join["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

					if (empty($pay_time))
					{
						$count++;
						$iFMail->SendMail($join["email"], $join["realname"], $subject, $message_body, $upload_dir.$new_file['name']);
					}
				}
			}
		}
		else
		{
			$tool->ShowMsgPage("未選擇寄送對象");
		}

		$linkmysql->close_mysql();

		if ($count == 0)
		{
			$tool->ShowMsgPage("未寄出任何信件", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			// 寄送信件備份至EO及if@friendxifun.net
			$backup_subject = "[EO信件備份] 寄送時間: " . date("Y-m-d H:i:s");

			$backup_message  = "寄送者: " . $user_data["realname"] . " (". $user_data["username"] .") <br />";
			$backup_message .= "活動名稱: " . $act_data["name"] . "<br />";
			$backup_message .= "寄送數量: " . $count . "封<br /><br />";
			$backup_message .= "信件主旨: " . $subject . "<br />";
			$backup_message .= "<br />";
			$backup_message .= "信件主旨內文:<br />";
			$backup_message .= "<br />";
			$backup_message .= $message_body;

			$iFMail->SendMail('if@ifriendxifun.net', 'iF', $backup_subject, $backup_message, $upload_dir.$new_file['name']);
			$iFMail->SendMail($user_data["email"], $user_data["realname"], $backup_subject, $backup_message, $upload_dir.$new_file['name']);

			$tool->ShowMsgPage("信件寄送完成! $count 封信件", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["act"] == "SendMessage")
	{
		//----------------------------------------
		// EO 使用簡訊系統寄送簡訊
		//----------------------------------------
		
		$uid = $_SESSION["uid"];
		$aid = $_POST["aid"];
		$to = $_POST["to"];
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

		$linkmysql->init();

		// 取出寄送者的資料
		$sql  = "SELECT * ";
		$sql .= "FROM `user` ";
		$sql .= "WHERE `uid` = '$uid' ";
		$linkmysql->query($sql);

		$user_data = mysql_fetch_array($linkmysql->listmysql);

		// 取出活動資料
		$sql  = "SELECT * ";
		$sql .= "FROM `activitie` ";
		$sql .= "WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		$act_data = mysql_fetch_array($linkmysql->listmysql);

		// 取出所有參加活動的會員
		$sql  = "SELECT `u`.`realname`, `u`.`tel`, `aj`.`charge_type`, `aj`.`charge_id` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' ";
		$sql .= "AND `aj`.`join_status` = 'join' ";
		$linkmysql->query($sql);

		$joindata = array();

		while ($tmp = mysql_fetch_array($linkmysql->listmysql))
		{
			$phone = explode("-", $tmp["tel"]);

			if ($phone[0][0] == "0" && $phone[0][1] == "9")
			{
				$tmp["tel"] = $phone[0].$phone[1].$phone[2];
				array_push( $joindata, $tmp);
			}
		}

		if (count($joindata) == 0)
		{
			$tool->ShowMsgPage("目前沒有參加活動的會員");
		}

		$count = 0;

		// 根據選擇的類別寄送簡訊
		if ($to == "All")
		{
			foreach($joindata as $join)
			{
				$msg_result = $iFSMS->Send_SMS( $join["tel"], $message, $dlvtime);

				if ($msg_result['msgid'] > 0)
				{
					$count++;
				}
			}
		}
		else if ($to == "Paid")
		{
			foreach($joindata as $join)
			{
				if ($join["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

					if (!empty($pay_time))
					{
						$msg_result = $iFSMS->Send_SMS( $join["tel"], $message, $dlvtime);

						if ($msg_result['msgid'] > 0)
						{
							$count++;
						}
					}
				}
				else if ($join["charge_type"] == "coupon")
				{
					$msg_result = $iFSMS->Send_SMS( $join["tel"], $message, $dlvtime);

					if ($msg_result['msgid'] > 0)
					{
						$count++;
					}
				}
			}
		}
		else if ($to == "Unpaid")
		{
			foreach($joindata as $join)
			{
				if ($join["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

					if (empty($pay_time))
					{
						$msg_result = $iFSMS->Send_SMS( $join["tel"], $message, $dlvtime);

						if ($msg_result['msgid'] > 0)
						{
							$count++;
						}
					}
				}
			}
		}
		else
		{
			$tool->ShowMsgPage("未選擇寄送對象");
		}

		$linkmysql->close_mysql();

		if ($count == 0)
		{
			$tool->ShowMsgPage("未送出任何簡訊", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			// 寄送簡訊備份至EO及if@friendxifun.net
			$backup_subject = "[EO簡訊備份] 寄送時間: " . date("Y-m-d H:i:s");

			$backup_message  = "寄送者: " . $user_data["realname"] . " (". $user_data["username"] .") <br />";
			$backup_message .= "活動名稱: " . $act_data["name"] . "<br />";
			$backup_message .= "寄送數量: " . $count . "封<br /><br />";
			$backup_message .= "<br />";
			$backup_message .= "簡訊內容:<br />";
			$backup_message .= "<br />";
			$backup_message .= $message;

			$iFMail->SendMail('if@ifriendxifun.net', 'iF', $backup_subject, $backup_message, $upload_dir.$new_file['name']);
			$iFMail->SendMail($user_data["email"], $user_data["realname"], $backup_subject, $backup_message, $upload_dir.$new_file['name']);

			$tool->ShowMsgPage("簡訊寄送完成! $count 封簡訊", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else
	{
		$tool->ShowMsgPage("無法辨識的指令");
	}
?>