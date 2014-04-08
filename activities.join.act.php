<?
	session_start();
	include "smarty.lib.php";

	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	if ($_GET["act"] == "join" && $_SESSION["step"] == 0)
	{
		//---------------------------------------
		// 會員報名活動，檢查輸入的帳號密碼
		//---------------------------------------

		$aid = $_POST["aid"];
		$password = md5($_POST["password"]);
		$charge_methed = $_POST["charge_methed"];

		$linkmysql->init();

		$sql = sprintf("SELECT `username`, `password` FROM `user` WHERE `uid`='%d'", $_SESSION["uid"]);
		$linkmysql->query( $sql );
		list( $username_check, $password_check )=mysql_fetch_row($linkmysql->listmysql);

		if ($username_check != $_SESSION["username"])
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("報名帳號錯誤! 請聯絡網站管理員", "回到活動報名頁面", "index.php?act=activitiesjoin&sel=join&aid=$aid");
		}
		else if ($password != $password_check)
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("密碼輸入錯誤!", "回到活動報名頁面", "index.php?act=activitiesjoin&sel=join&aid=$aid");
		}

		unset($_SESSION["charge_methed"]);
		unset($_SESSION["link_username"]);
		unset($_SESSION["emails"]);
		unset($_SESSION["ids"]);

		if ($charge_methed == "iBon")
		{
			$_SESSION["charge_methed"] = "iBon";
			$_SESSION["link_username"] = $_POST["username"];
			$_SESSION["usediscount"] = $_POST["usediscount"];

			$_SESSION["ids"] = array();
			$_SESSION["ids"][0] = $_POST["id1"];
			$_SESSION["ids"][1] = $_POST["id2"];
			$_SESSION["ids"][2] = $_POST["id3"];
			$_SESSION["ids"][3] = $_POST["id4"];
			$_SESSION["ids"][4] = $_POST["id5"];
			$_SESSION["ids"][5] = $_POST["id6"];
			$_SESSION["ids"][6] = $_POST["id7"];
			$_SESSION["ids"][7] = $_POST["id8"];
			$_SESSION["emails"] = array();

			$_SESSION["emails"][0] = $_POST["email1"];
			$_SESSION["emails"][1] = $_POST["email2"];
			$_SESSION["emails"][2] = $_POST["email3"];
			$_SESSION["emails"][3] = $_POST["email4"];
			$_SESSION["emails"][4] = $_POST["email5"];
			$_SESSION["emails"][5] = $_POST["email6"];
			$_SESSION["emails"][6] = $_POST["email7"];
			$_SESSION["emails"][7] = $_POST["email8"];
		}
		else if ($charge_methed == "coupon")
		{
			$_SESSION["charge_methed"] = "coupon";
			$coupon_id = $_POST["coupon_id"];

			$linkmysql->init();
			$sql = "SELECT `use_time` FROM `coupon` WHERE `coupon_id` = '$coupon_id' ";
			$linkmysql->query( $sql );

			if ($data =mysql_fetch_array($linkmysql->listmysql))
			{
				$linkmysql->close_mysql();

				if (!empty($data["use_time"])) {
					$tool->ShowMsgPage("優惠卷已經使用過", "回到活動報名頁面", "index.php?act=activitiesjoin&sel=join&aid=$aid");
				}
				else
				{
					unset($_SESSION["coupon_id"]);
					$_SESSION["coupon_id"] = $coupon_id;
				}
			}
		}
		else
		{
			$tool->ShowMsgPage("未指定繳費方式", "回到活動報名頁面", "index.php?act=activitiesjoin&sel=join&aid=$aid");
		}

		$tool->URL("index.php?act=activitiesjoin&sel=join&aid=$aid&step=1");
	}
	else if ($_GET["act"] == "rejoin")
	{
		//---------------------------------------
		// 會員重新報名活動 -- 已取消的功能
		//---------------------------------------

		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		// 取出會員資料
		$sql = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);

		if (!$member = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到會員資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		// 取出活動資料
		$sql  = "SELECT `a`.`act_date`, `a`.`join_deadline`, `a`.`people_limit`, ";
		$sql .= "`a`.`males`, `a`.`females`, `a`.`status`, `aj`.`serial`, ";
		$sql .= "`aj`.`charge_type`, `aj`.`charge_id`, `aj`.`join_status` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";
		$linkmysql->query($sql);

		if ($joindata = mysql_fetch_array($linkmysql->listmysql))
		{
			// 報名截止日
			$tmp = explode("-", $joindata["act_date"]);
			$joindeadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-2, $tmp[0]));

			if (date("Y-m-d") > $joindata['join_deadline'])
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("超過活動報名截止日期，無法重新報名活動",
					"回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($joindata["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名狀態，無法重新報名活動",
					"回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($joindata["join_status"] != "cancel")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("未取消參加此活動，無法重新報名活動",
					"回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			// 解析人數與性別限制字串
			if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $joindata["people_limit"], $matche))
			{
				$sex_limit = 1;
				$joindata["male_limit"] = intval($matche[1]);
				$joindata["female_limit"] = intval($matche[2]);
			}
			else if (preg_match("/No limit total: (.+)\./" , $joindata["people_limit"], $matche))
			{
				$joindata["total_limit"] = intval($matche[1]);
				$sex_limit = 0;
			}
			else if (preg_match("/No limit./" , $joindata["people_limit"], $matche))
			{
				$joindata["total_limit"] = 999999;
				$sex_limit = 0;
			}

			// 根據對應的性別檢查活動資料的參加人數
			if ($sex_limit == 1)
			{
				if ($member["sex"] == "男" && $joindata["males"] >= $joindata["male_limit"])
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("男生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
				else if ($member["sex"] == "女" && $joindata["females"] >= $joindata["female_limit"])
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("女生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
			}
			else if ($sex_limit == 0)
			{
				if (($joindata["males"] + $joindata["females"]) >= $joindata["total_limit"])
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("總人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
			}

			$males = $joindata["males"] + 1;
			$females = $joindata["females"] + 1;

			// 根據對應的性別更新活動資料的參加人數
			if ($member["sex"] == "男")
			{
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}
			else if ($member["sex"] == "女")
			{
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			// 變更會員報名活動的狀態
			$sql  = "UPDATE `activitiejoin` ";
			$sql .= "SET `join_status` = 'join' ";
			$sql .= "WHERE `serial` = '" . $joindata["serial"] . "' LIMIT 1 ";
			$linkmysql->query($sql);

			$linkmysql->close_mysql();
			$tool->ShowMsgPage("重新報名活動完成!", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到參加活動資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
	else if ($_GET["act"] == "addUser")
	{
		//---------------------------------------
		// 管理員從後端邀請會員報名活動
		//---------------------------------------

		if ($_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("權限不足無法使用此項功能", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$aid = $_POST["aid"];
		$username = $_POST["username"];
		$discount = $_POST["discount"];
		$charge_method = $_POST["charge_method"];

		$linkmysql->init();

		// 取出會員資料
		$sql = "SELECT * FROM `user` WHERE `username` = '$username'";
		$linkmysql->query($sql);

		if (!$member = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所指定的會員資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$uid = $member["uid"];

		// 取出活動資料
		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);

		if (!$activitie = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所指定的活動資料");
		}

		if ($activitie["ownerid"] == $member["uid"])
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無法將EO加入該位EO舉辦的活動中");
		}

		// 檢查是否已經報名完成
		$sql = "SELECT * FROM `activitiejoin` WHERE `aid` = '$aid' AND `uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("該會員已經報名此活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		// 根據選擇的繳費類型來加入活動
		if ($charge_method == "iBon")
		{
			// 解析活動費用字串
			if (preg_match("/All: (.+)/", $activitie["charge"], $matche))
			{
				$fees = intval($matche[1]);
			}
			else if (preg_match("/Male: (.+), Female: (.+)/" , $activitie["charge"], $matche))
			{
				// 男女不同價位收費
				if ($member["sex"] == "男")
				{
					$fees = intval($matche[1]);
				}
				else if ($member["sex"] == "女")
				{
					$fees = intval($matche[2]);
				}
			}
			else
			{
				$fees = $activitie["charge"];
			}

			$discount = intval($discount);
			$fees = $fees - $discount;

			if ($fees < 30)
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("折扣金額不可超過活動費用");
			}

			$males = $activitie["males"] + 1;
			$females = $activitie["females"] + 1;

			// 根據對應的性別更新活動資料的參加人數
			if ($member["sex"] == "男")
			{
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}
			else if ($member["sex"] == "女")
			{
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			// 新增ibon繳費單號到資料庫中
			$ibon_code = sprintf("%08d%08d%05d", $uid, $aid,  $fees);

			$sql  = "INSERT INTO `charge_ibon` (`charge_ibon_id`, `uid`, `aid`, `fees`, `success`, ";
			$sql .= "`ibon_no` , `gwsr` , `process_time` , `pay_time` ) ";
			$sql .= "VALUES ( '$ibon_code', '$uid', '$aid', '$fees', NULL , ";
			$sql .= "NULL , NULL , NULL , NULL ); ";
			$linkmysql->query($sql);

			// 將此會員加入參加活動資料中
			$sql  = "INSERT INTO `activitiejoin` ( ";
			$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `intro_id`, ";
			$sql .= "`no`, `option1`, `option2`, `option3`, `join_time`, `join_status` ) ";
			$sql .= "VALUES ( ";
			$sql .= "'', '$aid', '$uid', 'iBon', '$ibon_code', '0', ";
			$sql .= "NULL, NULL, NULL, NULL, NOW(), 'join' )";
			$linkmysql->query($sql);

			$linkmysql->close_mysql();

			// 送出繳費單號至綠界科技系統
			//$ibon_url = "ibon_echo.php";
			$ibon_url = "http://ts.payonline.com.tw/new3in1_echo.php";

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $ibon_url);
			curl_setopt($ch, CURLOPT_POST, 7);

			$post_var  = "client="  . urlencode($config["store_no"]);
			$post_var .= "&amount=" . urlencode($fees);
			$post_var .= "&od_sob=" . urlencode($ibon_code);
			$post_var .= "&Store=" . urlencode('famiport');	//暫時先用全家為主
			$post_var .= "&h_back=" . urlencode('0');					
			$post_var .= "&roturl=" . urlencode($config["base_url"] .'ibon_echo.php');
			$post_var .= "&okurl=" . urlencode($config["base_url"] .'ibon_ok.php');

			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_var);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	//php safe mode can't active
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);

			$result = curl_exec ($ch);

			list($header, $data) = explode("\n\n", $result, 2);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code == 301 || $http_code == 302)
			{
				$matches = array();
				preg_match('/Location:(.*?)\n/', $header, $matches);
				$url = trim($matches[1]);

				// 顯示 iBon code 於畫面上
				header("Location: http://ts.payonline.com.tw/" . $url);
			}
			else
			{
				$tool->ShowMsgPage("連結超商代收後台失敗，請洽系統管理員。");
			}
		}
		else if ($charge_method == "coupon")
		{
			$give_id = $_SESSION["uid"];
			$sql = "SELECT * FROM `user` WHERE `uid` = '$give_id'";
			$linkmysql->query($sql);

			$Admin = mysql_fetch_array($linkmysql->listmysql);

			$reason  = $Admin["username"] . " 邀請您參加活動 「". $activitie["name"]. "」。";

			$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
			$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
			$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
			$sql .= "'$reason', NOW(), NOW(), $aid); ";
			$linkmysql->query($sql);

			$sql = "SELECT LAST_INSERT_ID();";
			$linkmysql->query($sql);

			list($coupon_id) = mysql_fetch_array($linkmysql->listmysql);

			// 將此會員加入參加活動資料中
			$sql  = "INSERT INTO `activitiejoin` ( ";
			$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `intro_id`, ";
			$sql .= "`no`, `option1`, `option2`, `option3`, `join_time`, `join_status` )  ";
			$sql .= "VALUES ( ";
			$sql .= "'', '$aid', '$uid', 'coupon', '$coupon_id', NULL, ";
			$sql .= "NULL, NULL, NULL, NULL, NOW(), 'join' )";
			$linkmysql->query($sql);

			$males = $activitie["males"] + 1;
			$females = $activitie["females"] + 1;

			// 根據對應的性別更新活動資料的參加人數
			if ($member["sex"] == "男")
			{
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}
			else if ($member["sex"] == "女")
			{
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` = '$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			//---------------------------------------------
			// 報名完成，送出完成報名信件
			//---------------------------------------------

			// 取出活動資料
			$sql  = "SELECT `t`.`tname`, `a`.`act_date`, `a`.`act_time`, `a`.`name`, `p`.`placename`, `a`.`decription` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `group` g ON `a`.`group` = `g`.`gid` ";
			$sql .= "WHERE `a`.`aid` = '$aid'";
			$linkmysql->query($sql);

			list($tname, $act_date, $act_time, $name, $placename, $decription) = mysql_fetch_array($linkmysql->listmysql);

			$linkmysql->close_mysql();

			$coupon_detail = sprintf("<a href=\"%sindex.php?act=coupon&sel=detail&id=%d\">優惠卷使用詳細資料</a>",
				$config["base_url"], $coupon_id);

			// 寄出報名成功的信件
			$mailinfo = array();
			$mailinfo["realname"] = $member["realname"];
			$mailinfo["act_date"] = $act_date;
			$mailinfo["act_time"] = $act_time;
			$mailinfo["act_topic"] = $tname;
			$mailinfo["act_name"] = $name;
			$mailinfo["ibon_code"] = "使用優惠卷";
			$mailinfo["ibon_paytime"] = $coupon_detail;
			$mailinfo["discount_link"] = "無";

			$iFMail->PaidMail($member["email"], $member["realname"], $mailinfo);

			$tool->ShowMsgPage("使用優惠卷報名活動完成", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("未選擇繳費方式");
		}
	}
	else if ($_GET["act"] == "usercancel")
	{
		//----------------------------------------
		// 會員自行取消報名活動
		//----------------------------------------
		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		// 若該會員有參加此活動
		$sql  = "SELECT `a`.`name`, `a`.`act_date`, `a`.`act_time`, `a`.`status`, `a`.`males`, `a`.`females`, ";
		$sql .= "`a`.`use_coupon`, `aj`.`charge_type`, `aj`.`charge_id`, `aj`.`intro_id`, `aj`.`join_status`, `aj`.`join_time`, ";
		$sql .= "`u`.`sex`, `u`.`realname`, `u`.`email`, `t`.`tname` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "WHERE `a`.`aid` = '$aid' AND `aj`.`uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["join_status"] != "join")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("不是參加活動狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($data["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放報名狀態，無法取消", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$tmp = explode("-", $data["act_date"]);
			$data["deadline"] = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));

			if (date("Y-m-d") > $data["deadline"])
			{
				$linkmysql->close_mysql();
				$message = "超過取消參加活動期限 (". $data["deadline"] .")，無法取消參加活動";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			// 更新該活動的男女參與人數
			if ($data["sex"] == "男")
			{
				$males = --$data["males"];
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}
			else if ($data["sex"] == "女")
			{
				$females = --$data["females"];
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			// 檢查繳費狀態
			$charge_status = '';
			$ibon_number = '';

			if ($data["charge_type"] == "iBon")
			{
				$sql = sprintf("SELECT `pay_time`, `ibon_no` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time, $ibon_no) = mysql_fetch_array($linkmysql->listmysql);

				$ibon_number = $ibon_no;

				if (empty($pay_time))
				{
					$charge_status = 'unPay';
				}
				else if (!empty($pay_time))
				{
					$charge_status = 'Paid';
					$give_id = 1;

					if ($data["use_coupon"] = "YES")
					{
						// 已繳費者，需給予活動抵用卷
						$reason = "會員取消參加活動「". $data["name"]. "」，使用iBon繳費完成的會員給予活動優惠卷。";

						$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
						$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
						$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
						$sql .= "'$reason', NOW() , NULL , NULL ); ";
						$linkmysql->query($sql);
					}
				}
				
				$sql = sprintf("DELETE FROM `charge_ibon` WHERE `charge_ibon_id` = '%s' LIMIT 1;", $data["charge_id"]);
				$linkmysql->query($sql);				
			}
			else if ($data["charge_type"] == "coupon")
			{
				$charge_status = 'Paid';
				$ibon_number = 0;
				$give_id = 1;
				$reason = "會員取消參加活動「". $data["name"]. "」，使用活動優惠卷報名的會員給予活動優惠卷。";

				$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
				$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
				$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
				$sql .= "'$reason', NOW() , NULL , NULL ); ";
				$linkmysql->query($sql);
			}

			$charge_type = $data["charge_type"];
			$charge_id = $data["charge_id"];
			$join_time = $data["join_time"];

			// 將報名資料放入報名活動取消記錄中
			$sql  = "INSERT INTO `activitiecancel` ( ";
			$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `ibon_no`, `charge_status`, ";
			$sql .= "`join_time`, `cancel_time`, `cancel_by`, `cancel_ip`, `cancel_reason` ";
			$sql .= ") VALUES ( ";
			$sql .= "NULL , '$aid', '$uid', '$charge_type', '$charge_id', '$ibon_number', '$charge_status', ";
			$sql .= "'$join_time', NOW(), '$uid', '$ip', '會員自行取消活動報名' );";
			$linkmysql->query($sql);

			// 刪除會員的活動報名資料
			$sql  = "DELETE FROM `activitiejoin` ";
			$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1;";
			$linkmysql->query($sql);
			
			if ($data['intro_id'] > 0)
			{
				// 將介紹該位會員的會員介紹數 - 1
				$intro_id = $data['intro_id'];

				$sql  = "UPDATE `recommand` SET `count` = `count` - 1 ";
				$sql .= "WHERE `uid` = '$intro_id' LIMIT 1;";
				$linkmysql->query($sql);
			}

			// 取出該會員於此活動推薦的資料
			$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";
			$linkmysql->query($sql);

			$intros = array();

			while ($introdata = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($intros, $introdata);
			}

			// 刪除該會員於此活動推薦的資料
			foreach ($intros as $intro)
			{
				$intro_id = $intro['intro_id'];

				$sql  = "DELETE FROM `introduction` ";
				$sql .= "WHERE `intro_id` = '$intro_id' ";
				$linkmysql->query($sql);
			}

			unset($intros);

			// 寄出使用者取消參加活動信件
			$mailinfo = array();
			$mailinfo["realname"] = $data["realname"];
			$mailinfo["act_date"] = $data["act_date"];
			$mailinfo["act_time"] = $data["act_time"];
			$mailinfo["act_topic"] = $data["tname"];
			$mailinfo["act_name"] = $data["name"];

			$iFMail->UserCancelMail($data["email"], $data["realname"], $mailinfo);

			if ($data["charge_type"] == "iBon")
			{
				// 使用iBon繳費
				$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

				if (!empty($pay_time))
				{
					if ($data["use_coupon"] = "YES")
					{
						$linkmysql->close_mysql();
						$message  = "已經取消報名活動，您已繳費完成，系統將退回一張活動優惠卷。";
						$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
					else
					{
						$linkmysql->close_mysql();
						$message  = "已經取消報名活動，您已繳費完成，此活動無法退還優惠卷，請與該場EO聯絡退費方式。<br/> ";
						$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}
				else
				{
					$linkmysql->close_mysql();
					$message  = "已經取消報名活動，您尚未完成繳費，請勿前往繳費!";
					$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
			}
			else if ($data["charge_type"] == "coupon")
			{
				// 使用活動優惠卷
				$linkmysql->close_mysql();
				$message  = "已經取消報名活動，您使用活動優惠卷報名，系統將退回一張活動優惠卷。<br/> ";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("沒有報名記錄或是無此活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["act"] == "forcecancel")
	{
		//-------------------------------------------------
		// 強制取消會員報名活動
		// 需檢查iBon繳費期限
		// 若使用優惠卷，直接退還
		//-------------------------------------------------

		$aid = $_GET["aid"];
		$uid = $_GET["uid"];
		$EO_id = $_SESSION["uid"];

		if ($_SESSION["authority"] != "EO" && $_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("您的權限不足!", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$linkmysql->init();

		// 取出會員的報名資料
		$sql  = "SELECT `a`.`name`, `a`.`act_date`, `a`.`status`, `a`.`males`, `a`.`females`, `a`.`use_coupon`, ";
		$sql .= "`aj`.`charge_type`, `aj`.`charge_id`, `aj`.`intro_id`, `aj`.`join_status`, `aj`.`join_time`, `u`.`sex` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `a`.`aid` = '$aid' AND `aj`.`uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["status"] != "OPEN")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動非開放的狀態，無法強制取消會員的參與", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($data["join_status"] != "join")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("該會員不是已報名活動的狀態", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			// 更新該活動的男女參與人數
			if ($data["sex"] == "男")
			{
				$males = --$data["males"];
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}
			else if ($data["sex"] == "女")
			{
				$females = --$data["females"];
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			// 檢查繳費狀態
			$charge_status = '';
			$ibon_number = '';

			if ($data["charge_type"] == "iBon")
			{
				$sql = sprintf("SELECT `pay_time`, `ibon_no` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time, $ibon_no) = mysql_fetch_array($linkmysql->listmysql);

				$ibon_number = $ibon_no;

				if (empty($pay_time))
				{
					$charge_status = 'unPay';
				}
				else if (!empty($pay_time))
				{
					$charge_status = 'Paid';

					if ($data["use_coupon"] = "YES")
					{
						// 已繳費者，需給予活動抵用卷
						$reason = "EO取消會員報名活動「". $data["name"]. "」，已繳費完成的會員給予活動優惠卷。";

						$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
						$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
						$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$EO_id', ";
						$sql .= "'$reason', NOW() , NULL , NULL ); ";
						$linkmysql->query($sql);
					}
				}

				$sql = sprintf("DELETE FROM `charge_ibon` WHERE `charge_ibon_id` LIKE '%s' LIMIT 1;", $data["charge_id"]);
				//$linkmysql->query($sql);
				
				if (!$linkmysql->query($sql))
				{
					$linkmysql->MysqlError();
				}
			}
			else if ($data["charge_type"] == "coupon")
			{
				$charge_status = 'Paid';
				$ibon_number = 0;
				$reason = "EO取消會員報名活動「". $data["name"]. "」，使用活動優惠卷報名的會員給予活動優惠卷。";

				$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
				$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
				$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$EO_id', ";
				$sql .= "'$reason', NOW() , NULL , NULL ); ";
				$linkmysql->query($sql);
			}

			$charge_type = $data["charge_type"];
			$charge_id = $data["charge_id"];
			$join_time = $data["join_time"];

			// 將報名資料放入報名活動取消記錄中
			$sql  = "INSERT INTO `activitiecancel` ( ";
			$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `ibon_no`, `charge_status`, ";
			$sql .= "`join_time`, `cancel_time`, `cancel_by`, `cancel_ip`, `cancel_reason` ";
			$sql .= ") VALUES ( ";
			$sql .= "NULL , '$aid', '$uid', '$charge_type', '$charge_id', '$ibon_number', '$charge_status', ";
			$sql .= "'$join_time', NOW(), '$EO_id', '$ip', 'EO取消會員活動報名' );";
			$linkmysql->query($sql);

			// 刪除會員的活動報名資料
			$sql  = "DELETE FROM `activitiejoin` ";
			$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1;";
			$linkmysql->query($sql);

			if ($data['intro_id'] > 0)
			{
				// 將介紹該位會員的會員介紹數 - 1
				$intro_id = $data['intro_id'];

				$sql  = "UPDATE `recommand` SET `count` = `count` - 1 ";
				$sql .= "WHERE `uid` = '$intro_id' LIMIT 1;";
				$linkmysql->query($sql);
			}

			// 取出該會員於此活動推薦的資料
			$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";
			$linkmysql->query($sql);

			$intros = array();

			while ($introdata = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($intros, $introdata);
			}

			// 刪除該會員於此活動推薦的資料
			foreach ($intros as $intro)
			{
				$intro_id = $intro['intro_id'];

				$sql  = "DELETE FROM `introduction` ";
				$sql .= "WHERE `intro_id` = '$intro_id' ";
				$linkmysql->query($sql);
			}

			unset($intros);

			$linkmysql->close_mysql();
			$tool->ShowMsgPage("已取消該會員報名活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到活動資料");
		}
	}
	else if ($_GET["act"] == "MatchCount")
	{
		//---------------------------------------
		// 選擇可填寫人數完成，轉向填寫問卷頁面
		//---------------------------------------

		$aid = $_POST["aid"];
		$count = $_POST["count"];
		$url = sprintf("index.php?act=activitiesjoin&sel=questionary&aid=%d&count=%d", $aid, $count);

		$tool->URL($url);
	}
	else if ($_GET["act"] == "questionary")
	{
		//---------------------------------------
		// 問卷填寫完成，存入資料庫
		//---------------------------------------

		$aid = $_POST["aid"];
		$match_count = $_POST["match_count"];
		$member_count = $_POST["member_count"];
		$attendance = $_POST["attendance"];
		$option = $_POST["option"];

		$result = array();

		require "./lib/match.php";

		$MyMatch = new Match_h;
		$MyMatch->init();
		$MyMatch->match_count = $match_count;

		// 每位會員選擇的編號
		for ($i = 1; $i <= $member_count; $i++)
		{
			$option[$i]["no"] = $i;

			// 有出席活動才進行配對
			if ($attendance[$i]) {
				$MyMatch->push($option[$i]);
			}

			$option[$i]["no"]="";
			$result[$i]["opt1"] = "";


			for ($j = 0; $j < $match_count; $j++)
			{
				if ($option[$i][$j] != "") {
					$result[$i]["opt1"] .= $option[$i][$j] . ", ";
				}
			}

			if ($result[$i]["opt1"] != "")
			{
				$result[$i]["opt1"] = substr($result[$i]["opt1"], 0, -2);
			}
		}

		// 根據每位會員選擇的編號來進行配對
		$MyMatch->match_all();

		//$MyMatch->show_relations(); //debug

		// 取出所有配對的關係
		$data = $MyMatch->get_relations();

		$MyMatch->destory();

		$option2 = array();	// 有填寫他的編號
		$option3 = array();	// 兩邊都相互填寫的編號

		for ($i = 1; $i <= $member_count; $i++)
		{
			$option2[$i] = array();
			$option3[$i] = array();
		}

		$count = count($data);

		for($i=0; $i<$count; $i++)
		{
			if ($data[$i]["single"])
			{
				if ($data[$i]["to"] >= 0 && $data[$i]["to"] < $count)
				{
					@array_push( $option2[ $data[$i]["to"] ], $data[$i]["from"]);
				}
			}
			else
			{
				if ($data[$i]["to"] >= 0 && $data[$i]["to"] < $count)
				{
					array_push( $option2[ $data[$i]["to"] ], $data[$i]["from"]);
					array_push( $option2[ $data[$i]["from"] ], $data[$i]["to"]);
					array_push( $option3[ $data[$i]["to"] ], $data[$i]["from"]);
					array_push( $option3[ $data[$i]["from"] ], $data[$i]["to"]);
				}
			}
		}

		$linkmysql->init();

		for ($i = 1; $i <= $member_count; $i++)
		{
			sort($option2[$i]);
			sort($option3[$i]);

			// 有填寫他的會員的活動編號
			$result[$i]["opt2"] = "";

			sort($option2[$i]);
			foreach( $option2[$i] as $opt)
			{
				$result[$i]["opt2"] .= $opt. ", ";
			}

			if ($result[$i]["opt2"] != "")
			{
				$result[$i]["opt2"] = substr($result[$i]["opt2"], 0, -2);
			}

			//---------------------------------------------
			// 配對成功的會員的活動編號
			$result[$i]["opt3"] = "";
			foreach( $option3[$i] as $opt)
			{
				$result[$i]["opt3"] .= $opt. ", ";
			}

			if ($result[$i]["opt3"] != "")
			{
				$result[$i]["opt3"] = substr($result[$i]["opt3"], 0, -2);
			}

			//---------------------------------------------

			// 出席與否
			if ($attendance[$i]) {
				$attend = "true";
			} else {
				$attend = "false";
			}

			// 更新會員配對資料
			$sql  ="UPDATE `activitiejoin` SET ";
			$sql .="`attendance` = '". $attend ."', ";
			$sql .="`option1` = '". $result[$i]["opt1"] ."', ";
			$sql .="`option2` = '". $result[$i]["opt2"] ."', ";
			$sql .="`option3` = '". $result[$i]["opt3"] ."' ";
			$sql .="WHERE `aid` ='". $aid ."' AND `no` = '". $i ."' LIMIT 1;";
			$linkmysql->query($sql);
		}

		$linkmysql->close_mysql();

		$message = "問卷結果已送出並統計完成，<br/>在活動關閉之前，問卷內容若有需要還可以再進行修改。";
		$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
	}
	else if ($_GET["act"] == "verify" && $_SESSION["authority"] == "Admin")
	{
		//-------------------------------------------------
		// EO申請取消舉辦活動後續的檢查程式
		//-------------------------------------------------

		$id = $_POST["id"];
		$result = $_POST["result"];
		$commnet = $_POST["comment"];
		$verify_id  = $_SESSION["uid"];

		$linkmysql->init();

		// 取出會員編號、活動編號和取消類型
		$sql = "SELECT `type`, `uid`, `aid` FROM `cancelapply` WHERE `id` = '$id' ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			// 將審核結果存入資料庫中
			$sql  = "UPDATE `cancelapply` SET ";
			$sql .= "`verify_id` = '$verify_id', ";
			$sql .= "`comment` = '$commnet', ";
			$sql .= "`result` = '$result', ";
			$sql .= "`verify_time` = NOW() ";
			$sql .= "WHERE `cancelapply`.`id` = '$id' LIMIT 1 ; ";
			$linkmysql->query($sql);

			// 取出活動資料
			$sql  = "SELECT `a`.*, `p`.`placename` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "WHERE `a`.`aid` = '" . $data["aid"] . "'";
			$linkmysql->query($sql);

			$actdata = mysql_fetch_array($linkmysql->listmysql);

			// 取出EO會員資料
			$sql  = "SELECT * FROM `user` WHERE `uid` = '". $actdata["ownerid"] . "'";
			$linkmysql->query($sql);
			$EOmember = mysql_fetch_array($linkmysql->listmysql);

			// 寄送 EO取消活動審核結果信
			$mailinfo = array();
			$mailinfo["realname"] = $EOmember["realname"];
			$mailinfo["act_date"] = $actdata["act_date"];
			$mailinfo["act_time"] = $actdata["act_time"];
			$mailinfo["act_place"] = $actdata["placename"];
			$mailinfo["act_name"] = $actdata["name"];

			$iFMail->CancelActResultMail($EOmember["email"], $EOmember["username"], $mailinfo);

			if ($data["type"] == "EOCancel")
			{
				// EO取消舉辦活動，審查結果成立，退給報名完成的會員活動優惠卷
				if ($result == "Pass")
				{
					$mailinfo = array();
					$mailinfo["realname"] = $EOmember["realname"];
					$mailinfo["act_date"] = $actdata["act_date"];
					$mailinfo["act_name"] = $actdata["name"];

					$iFMail->ActCancelMailB($EOmember["email"], $EOmember["username"], $mailinfo);

					// 有參加活動的的會員
					$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id` ";
					$sql .= "FROM `activitiejoin` aj ";
					$sql .= "WHERE `aj`.`aid` = '" . $data["aid"] . "' ";
					$sql .= "AND (`aj`.`join_status` = 'join' OR `aj`.`join_status` = 'cancel') ";
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

						// 取出會員資料
						$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
						$linkmysql->query($sql);
						$member = mysql_fetch_array($linkmysql->listmysql);

						$mailinfo = array();
						$mailinfo["realname"] = $member["realname"];
						$mailinfo["act_date"] = $actdata["act_date"];
						$mailinfo["act_name"] = $actdata["name"];

						// 活動取消通知信
						$iFMail->ActCancelMailA($member["email"], $member["username"], $mailinfo);

						$phone = explode("-", $member["tel"]);
						$member["tel"] = $phone[0].$phone[1].$phone[2];
						// 活動取消通知簡訊
						$iFSMS->ActCancelNotify($member["tel"], $actdata["act_date"]);

						if ($join["charge_type"] == "iBon")
						{
							$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $join["charge_id"]);
							$linkmysql->query($sql);
							list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

							if (!empty($pay_time))
							{
								// 已繳費者，需給予活動抵用卷
								$reason = "EO取消舉辦活動「". $actdata["name"]. "」，已繳費完成的會員給予活動優惠卷。";

								$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
								$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
								$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
								$sql .= "'$reason', NOW() , NULL , NULL ); ";
								$linkmysql->query($sql);
							}
						}
						else if ($join["charge_type"] == "coupon")
						{
							$reason = "EO取消舉辦活動「". $actdata["name"]. "」，使用活動優惠卷的會員給予活動優惠卷。";

							$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
							$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
							$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
							$sql .= "'$reason', NOW() , NULL , NULL ); ";
							$linkmysql->query($sql);
						}
					}

					$sql = sprintf("UPDATE `activitie` SET `status` = 'CANCEL' WHERE `aid` ='%d' LIMIT 1;", $data["aid"]);
					$linkmysql->query($sql);
					$message = "活動已取消，系統自動給予已繳費或使用活動抵用卷的會員活動抵用卷。";
				}
				else if ($result == "Refuse")
				{
					$sql = sprintf("UPDATE `activitie` SET `status` = 'OPEN' WHERE `aid` ='%d' LIMIT 1;", $data["aid"]);
					$linkmysql->query($sql);
					$message = "活動未取消。";
				}
			}

			$linkmysql->close_mysql();
			$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=" . $data["aid"]);
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到活動取消申請的資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=" . $data["aid"]);
		}
	}
	else if ($_GET["act"] == "checklink")
	{
		//-------------------------------------------------
		// 檢查優惠方案使用者輸入的連結
		//-------------------------------------------------
		$username = $_GET["username"];

		if ($username != "")
		{
			$linkmysql->init();
			$sql = "SELECT `uid` FROM `user` WHERE `username` = '$username'";
			$linkmysql->query($sql);

			if (list($uid) = mysql_fetch_row($linkmysql->listmysql))
			{
				$sql = "SELECT * FROM `recommand` WHERE `uid` = '$uid'";
				$linkmysql->query($sql);

				// 檢查專屬連結是否可以使用
				if (!$recommand = mysql_fetch_array($linkmysql->listmysql))
				{
					print "輸入的專屬連結無法使用";
					die;
				}

				if ($uid == $_SESSION["uid"])
				{
					print "無法使用自己的專屬連結";
					die;
				}

				$linkmysql->close_mysql();

				print "OK";
				die;
			}
			else
			{
				$linkmysql->close_mysql();
				print "輸入的專屬連結不存在";
				die;
			}
		}
		else
		{
			print "Empty";
			die;
		}
	}
	else if ($_GET["act"] == "checkemail")
	{
		//-------------------------------------------------
		// 檢查優惠方案使用者輸入的emails
		//-------------------------------------------------

		$uid = $_SESSION["uid"];
		$linkmysql->init();

		$count = 0;

		for ($i=1; $i<=8; $i++)
		{
			$email = $_GET["email".$i];

			if ($email != "")
			{
				$sql = "SELECT `intro_id` FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_email` = '$email'";
				$linkmysql->query($sql);

				if (list($intro_id) = mysql_fetch_row($linkmysql->listmysql))
				{
					print "第".$i."個電子信箱已經輸入過了。";
					$linkmysql->close_mysql();
					die;
				}
				else
				{
					$count++;
				}
			}
		}

		print "OK";

		$linkmysql->close_mysql();
	}
	else if ($_GET["act"] == "ReviseMemberNo")
	{
		//-------------------------------------------------
		// 變更會員的活動編號處理程式
		//-------------------------------------------------

		$aid 			= $_POST['aid'];
		$revise_count 	= $_POST['revise_count'];
		$uid_list		= $_POST['uid'];
		$new_no_list	= $_POST['new_no'];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['status'] != 'PROCEED')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動狀態不是現在進行中，無法變更會員的活動編號", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}

		for ($i = 0; $i < $revise_count; $i++)
		{
			$uid = $uid_list[$i];
			$no = $new_no_list[$i];

			// 更新會員的活動編號
			$sql  = "UPDATE `activitiejoin` SET `no` = '$no' ";
			$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1 ; ";

			$linkmysql->query($sql);
		}

		$linkmysql->close_mysql();

		$tool->ShowMsgPage("所有會員的活動編號均已更新，若之前有填寫活動配對資料，請重新填寫。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
	}
	else
	{
		$tool->ShowMsgPage("活動報名程式收到無法辨識的指令");
	}
?>