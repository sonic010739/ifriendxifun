<?
	function JoinExitSection()
	{
		unset($_SESSION["step"]);
		unset($_SESSION["charge_methed"]);
		unset($_SESSION["coupon_id"]);
		unset($_SESSION["link_username"]);
		unset($_SESSION["usediscount"]);
		unset($_SESSION["ids"]);
		unset($_SESSION["emails"]);
	}

	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}

	if ($_GET["sel"] == "join")
	{
		//---------------------------------------
		// 報名活動
		//---------------------------------------

		$step = !isset($_GET["step"]) ? 0 : $_GET["step"];

		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		if ($step == 0)
		{
			if ($aid > 0)
			{
				/*
					報名活動檢查項目
					1. 是否停權中
					2. 是否已經報名
					3. 活動是否為可報名的狀態
					4. 是否是自己舉辦的活動
					5. 活動年齡限制
					6. 報名人數限制
				*/

				$linkmysql->init();

				// 檢查黑名單，是否為停權狀態
				$sql  = "SELECT `black_serial` ";
				$sql .= "FROM `blacklist` ";
				$sql .= "WHERE `black_id` = '$uid' AND `lock` = 'true' ";
				$linkmysql->query( $sql );

				$data = mysql_fetch_array($linkmysql->listmysql);

				if (!empty($data["black_serial"]))
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("您現在被停權中，無法報名此活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}

				// 檢查是否為已報名
				$sql = "SELECT `serial`, `join_status` FROM `activitiejoin` WHERE `aid` = '$aid' AND `uid` = '$uid'";
				$linkmysql->query( $sql );
				$data = mysql_fetch_array($linkmysql->listmysql);

				if (!empty($data["serial"]))
				{
					if ($data == "join" || $data == "apply_cancel" || $data == "refuse_cancel")
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("已經報名此活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}

				$sql  = "SELECT `ownerid`, `age_limit`, `status`, `people_limit`, ";
				$sql .= "`males`, `females` ";
				$sql .= "FROM `activitie` WHERE `aid` = '$aid'";
				$linkmysql->query($sql);
				$data = mysql_fetch_array($linkmysql->listmysql);

				// 檢查是否是可以報名的活動
				if ($data["status"] != "OPEN")
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("活動非開放報名的狀態，無法報名活動。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}

				// 檢查是否是自己舉行的活動
				if ($_SESSION["uid"] == $data["ownerid"])
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("無法報名自己舉辦的活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}

				// 解析年齡限制字串
				if (preg_match("/Age limit lb: (.+), ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$age_type = "type1";
					$age_lb = intval($matche[1]);
					$age_ub = intval($matche[2]);
				}
				else if (preg_match("/Age limit male_lb: (.+), male_ub: (.+), female_lb: (.+), female_ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$age_type = "type2";
					$male_age_lb = intval($matche[1]);
					$male_age_ub = intval($matche[2]);
					$female_age_lb = intval($matche[3]);
					$female_age_ub = intval($matche[4]);
				}
				else if (preg_match("/No age limit\./" , $data["age_limit"], $matche))
				{
					$age_type = "type3";
				}

				// 檢查年齡限制
				$sql  = "SELECT `birth_year`, `sex` FROM `user` WHERE `uid` = '$uid'";
				$linkmysql->query( $sql );
				list($birth_year, $sex) = mysql_fetch_row($linkmysql->listmysql);

				$age = date("Y") - $birth_year;

				if ($age_type == "type1")
				{
					if ($age > $age_ub || $age < $age_lb)
					{
						$linkmysql->close_mysql();
						$message  = "年齡限制不符，您的年齡" . $age . "歲<br/>";
						$message .= "活動年齡限制，上限" . $age_ub . "歲，下限". $age_lb. "歲<br/>";
						$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}
				else if ($age_type == "type2")
				{
					if ($sex == "男")
					{
						if ($age > $male_age_ub || $age < $male_age_lb)
						{
							$linkmysql->close_mysql();
							$message  = "年齡限制不符，您的年齡" . $age . "歲<br/>";
							$message .= "<b>活動年齡限制" . $male_age_lb . "歲至". $male_age_ub. "歲</b><br/>";
							$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
						}
					}
					else if ($sex == "女")
					{
						if ($age > $female_age_ub || $age < $female_age_lb)
						{
							$linkmysql->close_mysql();
							$message  = "年齡限制不符，您的年齡" . $age . "歲<br/>";
							$message .= "<b>活動年齡限制" . $female_age_lb . "歲至". $female_age_ub. "歲</b><br/>";
							$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
						}
					}
				}

				// 解析人數與性別限制字串
				if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $data["people_limit"], $matche))
				{
					$sex_limit = 1;
					$data["male_limit"] = intval($matche[1]);
					$data["female_limit"] = intval($matche[2]);
				}
				else if (preg_match("/No limit total: (.+)\./" , $data["people_limit"], $matche))
				{
					$data["total_limit"] = intval($matche[1]);
					$sex_limit = 0;
				}
				else if (preg_match("/No limit./" , $data["people_limit"], $matche))
				{
					$data["total_limit"] = 999999;
					$sex_limit = 0;
				}

				// 根據對應的性別檢查活動資料的參加人數
				if ($sex_limit == 1)
				{
					if ($sex == "男" && $data["males"] >= $data["male_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("男生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
					else if ($sex == "女" && $data["females"] >= $data["female_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("女生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}
				else if ($sex_limit == 0)
				{
					if (($data["males"] + $data["females"]) >= $data["total_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("總人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}

				// 取出活動相關資料
				$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`ownerid`, `a`.`act_date`,";
				$sql .= "`a`.`act_time`, `a`.`join_deadline`, `a`.`place`, `p`.`placename`, `a`.`charge`, `a`.`use_discount`, `a`.`use_coupon` ";
				$sql .= "FROM `activitie` a ";
				$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
				$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
				$sql .= "LEFT JOIN `group` g ON `a`.`group` = `g`.`gid` ";
				$sql .= "WHERE `a`.`aid` = '$aid'";
				$linkmysql->query($sql);

				if ($data = mysql_fetch_array($linkmysql->listmysql))
				{
					$week =	array("日", "一", "二", "三", "四", "五", "六");
					$date = explode("-", $data["act_date"]);
					$time = explode(":", $data["act_time"]);

					// 將活動日期，拆解成年月日並且加上星期
					$data["act_date"] .= " (" . $week[date("w", mktime(0, 0, 0, $date[1], $date[2], $date[0]))] .")";
					$data["act_time"] = sprintf("%02d:%02d", intval($time[0]), intval($time[1]));

					//取消報名活動期限
					$data["deadline"] = date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2]-1, $date[0]));
					$data["deadline"] .= " (" . $week[date("w", mktime(0, 0, 0, $date[1], $date[2]-1, $date[0]))] .")";

					// iF 繳費期限
					$iFdeadline = date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2]-1, $date[0]));

					// 檢查報名截止日
					if (date("Y-m-d") > $data['join_deadline'])
					{
						$linkmysql->close_mysql();
						$message = "已超過活動報名期限(" . $data['join_deadline'] . ")";
						$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}

					$data["ibon_deadline"] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+7, date("Y")));

					if ($data["ibon_deadline"] > $iFdeadline)
					{
						$data["ibon_deadline"] = $data["deadline"];
					}

					$data["ibon_deadline"] .= " 23:59:59";

					// 男女不同價位收費
					if (preg_match("/All: (.+)/", $data["charge"], $matche))
					{
						$data["charge"] = intval($matche[1]);
					}
					else if (preg_match("/Male: (.+), Female: (.+)/" , $data["charge"], $matche))
					{
						if ($sex == "男")
						{
							$data["charge"] = intval($matche[1]);
						}
						else if ($sex == "女")
						{
							$data["charge"] = intval($matche[2]);
						}
					}
					else
					{
						$data["charge"] = $data["charge"];
					}

					$sql  = "SELECT `coupon_id`, `coupon_type`, `give_time` ";
					$sql .= "FROM `coupon`";
					$sql .= "WHERE `uid` = '$uid' AND `use_time` IS NULL";
					$linkmysql->query($sql);

					$mycoupon = "";
					while($coupondata = mysql_fetch_array($linkmysql->listmysql))
					{
						$mycoupon .= sprintf("<option value=\"%d\">%s，取得時間%s</option>",
							$coupondata["coupon_id"], "活動優惠卷", $coupondata["give_time"]);
					}

					if ($mycoupon == "")
					{
						$mycoupon = "<option value=\"-1\">無任何優惠卷</option>";
						$data["coupon_disable"] = "disabled";
						$data["coupon_info"] = "*您目前尚無任何優惠卷可使用。";
					}

					if ($data["use_coupon"] == "NO")
					{
						$data["coupon_disable"] = "disabled";
						$data["coupon_info"] = "*本活動無法使用優惠卷。";
					}

					$data["mycoupon"] = $mycoupon;
					$_SESSION["step"] = 0;
					$linkmysql->close_mysql();

					$tpl->assign("activitiedata", $data);
					$tpl->assign("mainpage", "activities/activities.join.html");
				}
				else
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("找不到該活動資料");
				}
			}
			else
			{
				$tool->ShowMsgPage("找不到該活動資料");
			}
		}
		else if ($step == 1 && $_SESSION["step"] == ($step-1))
		{
			//-----------------------------------
			// 報名活動 -- 顯示/產生繳費資訊
			//-----------------------------------
			$charge_type = $_SESSION["charge_methed"];

			$aid = $_GET["aid"];
			$uid = $_SESSION["uid"];

			$link_discount = 0;
			$mail_discount = 0;

			$linkmysql->init();

			if ($_SESSION["usediscount"] == "YES")
			{
				//檢查專屬連結的正確性，並且計算折扣金額
				$username = $_SESSION["link_username"];

				if ($username != "")
				{
					$sql = "SELECT `uid` FROM `user` WHERE `username` = '$username'";
					$linkmysql->query($sql);

					if ($data = mysql_fetch_array($linkmysql->listmysql))
					{
						if ($data["uid"] == $_SESSION["uid"])
						{
							JoinExitSection();
							$linkmysql->close_mysql();
							$tool->ShowMsgPage("無法使用自己的專屬連結", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
						}

						$link_discount = 100;
					}
					else
					{
						JoinExitSection();
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("專屬連結不存在", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}

				//檢查 email 的正確性，並且計算折扣金額
				$count = 0;

				for ($i = 0; $i < 8; $i++)
				{
					$email = $_SESSION["emails"][$i];

					if ($email != "")
					{
						$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_email` = '$email'";
						$linkmysql->query($sql);

						if ($data = mysql_fetch_array($linkmysql->listmysql))
						{
							JoinExitSection();
							$linkmysql->close_mysql();
							$tool->ShowMsgPage("第".($i+1)."個電子信箱已經輸入過了。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
						}
						else
						{
							$count++;
						}
					}
				}

				if ($count < 3)
				{
					$mail_discount = 0;
				}
				else if ($count >= 3 && $count < 8)
				{
					$mail_discount = 50;
				}
				else if ($count == 8)
				{
					$mail_discount = 100;
				}
			}

			// 取出該會員的資料
			$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			$member = mysql_fetch_array($linkmysql->listmysql);

			$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
			$linkmysql->query($sql);

			if ($activitie = mysql_fetch_array($linkmysql->listmysql))
			{
				// 解析人數與性別限制字串
				if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $activitie["people_limit"], $matche))
				{
					$sex_limit = 1;
					$activitie["male_limit"] = intval($matche[1]);
					$activitie["female_limit"] = intval($matche[2]);
				}
				else if (preg_match("/No limit total: (.+)\./" , $activitie["people_limit"], $matche))
				{
					$activitie["total_limit"] = intval($matche[1]);
					$sex_limit = 0;
				}
				else if (preg_match("/No limit./" , $activitie["people_limit"], $matche))
				{
					$activitie["total_limit"] = 999999;
					$sex_limit = 0;
				}

				// 根據對應的性別檢查活動資料的參加人數
				if ($sex_limit == 1)
				{
					if ($member["sex"] == "男" && $activitie["males"] >= $activitie["male_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("男生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
					else if ($member["sex"] == "女" && $activitie["females"] >= $activitie["female_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("女生人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
				}
				else if ($sex_limit == 0)
				{
					if (($activitie["males"] + $activitie["females"]) >= $activitie["total_limit"])
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("總人數超過上限", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}
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
				else
				{
					JoinExitSection();
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("找不到報名的會員資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}

				$linkmysql->StartTransaction();

				if (!$linkmysql->query($sql))
				{
					JoinExitSection();
					$linkmysql->MysqlError();
					$linkmysql->RollBack();
				}

				if ($charge_type == "iBon")
				{
					// 介紹的email存入資料庫中，繳費成功後才寄出介紹信件。
					if ($_SESSION["usediscount"] == "YES" && $activitie["use_discount"] == "YES")
					{
						for ($i = 0; $i < 8; $i++)
						{
							$email = $_SESSION["emails"][$i];
							$id = $_SESSION["ids"][$i];

							if (!empty($email))
							{
								$sql  = "INSERT INTO `introduction` (`intro_id`, `intro_uid`, `intro_aid`, `intro_name`, `intro_email`) ";
								$sql .= "VALUES (NULL, '$uid', '$aid', '$id', '$email'); ";

								if (!$linkmysql->query($sql))
								{
									JoinExitSection();
									$linkmysql->MysqlError();
									$linkmysql->RollBack();
								}
							}
						}
					}

					// 取得專屬連結的 uid
					$username = $_SESSION["link_username"];

					if ($username != "")
					{
						$sql = "SELECT `uid` FROM `user` WHERE `username` = '$username'";
						$linkmysql->query($sql);
						list($intro_id)= mysql_fetch_array($linkmysql->listmysql);
					}

					// 取得活動費用
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

					// 計算優惠的折扣
					if ($_SESSION["usediscount"] == "YES" && $activitie["use_discount"] == "YES")
					{
						$fees = $fees - $link_discount - $mail_discount;
					}

					// 新增ibon繳費單號到資料庫中
					$ibon_code = sprintf("%08d%08d%05d", $uid, $aid,  $fees);

					$sql  = "INSERT INTO `charge_ibon` (`charge_ibon_id`, `uid`, `aid`, `fees`, `success`, ";
					$sql .= "`ibon_no` , `gwsr` , `process_time` , `pay_time` ) ";
					$sql .= "VALUES ( '$ibon_code', '$uid', '$aid', '$fees', NULL , ";
					$sql .= "NULL , NULL , NULL , NULL ); ";

					if (!$linkmysql->query($sql))
					{
						JoinExitSection();
						$linkmysql->MysqlError();
						$linkmysql->RollBack();
					}

					// 將此會員加入參加活動資料中
					$sql  = "INSERT INTO `activitiejoin` ( ";
					$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `intro_id`, ";
					$sql .= "`no`, `option1`, `option2`, `option3`, `join_time`, `join_status` ) ";
					$sql .= "VALUES ( ";
					$sql .= "'', '$aid', '$uid', '$charge_type', '$ibon_code', '$intro_id', ";
					$sql .= "NULL, NULL, NULL, NULL, NOW(), 'join')";

					JoinExitSection();
					$linkmysql->EndTransaction();

					if (!$linkmysql->query($sql))
					{
						JoinExitSection();
						$linkmysql->RollBack();
						$linkmysql->MysqlError();
					}
					else
					{
						JoinExitSection();
						$linkmysql->EndTransaction();
					}

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
				else if ($charge_type == "coupon" && $activitie["use_coupon"] == "YES")
				{
					$coupon_id = $_SESSION["coupon_id"];

					// 這裡更新優惠卷的使用時間
					$sql  = "UPDATE `coupon` SET ";
					$sql .= "`use_time` = NOW(), ";
					$sql .= "`use_act` = '$aid' ";
					$sql .= "WHERE `coupon_id` = '$coupon_id' LIMIT 1; ";

					if (!$linkmysql->query($sql))
					{
						$linkmysql->RollBack();
						$linkmysql->MysqlError();
					}

					// 將此會員加入參加活動資料中
					$sql  = "INSERT INTO `activitiejoin` ( ";
					$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `intro_id`, ";
					$sql .= "`no`, `option1`, `option2`, `option3`, `join_time`, `join_status` ) ";
					$sql .= "VALUES ( ";
					$sql .= "'', '$aid', '$uid', '$charge_type', '$coupon_id', NULL, ";
					$sql .= "NULL, NULL, NULL, NULL, NOW(), 'join' )";

					if (!$linkmysql->query($sql))
					{
						$linkmysql->RollBack();
						$linkmysql->MysqlError();
					}
					else
					{
						$linkmysql->EndTransaction();

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
				}
				else
				{
					$tool->ShowMsgPage("報名活動錯誤的操作");
				}
			}
		}
	}
	else if ($_GET["sel"] == "iboncode")
	{
		//---------------------------------------
		// 參加活動 -- 顯示iBon繳費資訊
		//---------------------------------------

		$ibon_code = $_SESSION["ibon_code"];
		$linkmysql->init();

		$sql = "SELECT `aid`, `ibon_no`, `fees`, `process_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '$ibon_code'";
		$linkmysql->query($sql);

		if (list($aid, $ibon_no, $fees, $process_time) = mysql_fetch_array($linkmysql->listmysql))
		{
			// 取出活動資料
			$sql  = "SELECT `act_date` FROM `activitie` WHERE `aid` = '$aid' ";
			$linkmysql->query($sql);
			
			list($act_date) = mysql_fetch_array($linkmysql->listmysql);
			
			$ibon_deadline = '';
			if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $process_time, $matche))
			{
				$days = ($matche[4] + 3 >= 24) ? 6 : 7;
				$tmp = explode("-", $act_date);	
				
				// 計算繳費期限
				if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]))
				{				
					$ibon_deadline = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
				}
				else
				{
					$ibon_deadline = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
				}
			}

			$tpl->assign("aid", $aid);
			$tpl->assign("ibon_no", $ibon_no);
			$tpl->assign("fees", $fees);
			$tpl->assign("ibon_deadline", $ibon_deadline);
			$tpl->assign("charge_type", "iBon");
			$tpl->assign("mainpage", "activities/activities.charge.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到iBon繳費資料");
		}

		$linkmysql->close_mysql();
	}
	else if ($_GET["sel"] == "joindetail")
	{
		//---------------------------------------
		// 參加活動 -- 詳細資料
		//---------------------------------------
		$aid = $_GET["aid"];
		$uid = $_GET["uid"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if (($_SESSION["authority"] != "EO" || $_SESSION["uid"] != $act_data["ownerid"]) && $_SESSION["authority"] != "Admin")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("沒有權限觀看參加活動的資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			// 活動狀態
			$act_data["status"] = $tool->ShowActStatus($act_data["status"]);

			// 活動資料連結
			$act_data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $aid, $act_data["name"]);
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}

		// 取出該會員報名的詳細資料
		$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, `aj`.`join_status`, ";
		$sql .= "`aj`.`join_time`, `aj`.`no`, `aj`.`option1`, `aj`.`option2`, `aj`.`option3`, `aj`.`intro_id`, ";
		$sql .= "`u`.`username`, `u`.`realname`, `u`.`nickname`, `u`.`sex`, `u`.`birth_year`, `u`.`tel`, ";
		$sql .= "`u1`.`username` AS `intro_username` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `user` u1 ON `aj`.`intro_id` = `u1`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";

		$linkmysql->query( $sql );

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			// 使用者名稱連結
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);

			// 推薦會員名稱連結
			$data["intro_username"] = $tool->ShowMemberLink( $data["intro_id"], $data["intro_username"]);

			$match_type_email = 0;
			$match_type_msn = 0;
			$match_type_tel = 0;

			$match_type = explode(",", $act_data["match_type"]);

			if (is_array($match_type))
			{
				foreach ($match_type as $type)
				{
					if ($type == "email")
					{
						$match_type_email = 1;
					}
					else if ($type == "msn")
					{
						$match_type_msn = 1;
					}
					else if ($type == "tel")
					{
						$match_type_tel = 1;
					}
				}
			}

			// 我介紹的朋友人數
			$sql = "SELECT COUNT(*) FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";
			$linkmysql->query($sql);
			list($data["intro_count"]) = mysql_fetch_array($linkmysql->listmysql);

			//年齡
			$data["age"] = date("Y") - $data["birth_year"];

			// 參加活動的配對編號
			$data["no"] = !empty($data["no"]) ? sprintf("%3d 號", $data["no"]) : '--';

			// 參加活動選擇的編號
			if ($data["option1"] == "")
			{
				$data["option1"] = "--";
			}
			else
			{
				$option = explode(",", $data["option1"]);
				$data["option1"] = "";

				foreach ($option as $opt)
				{
					$data["option1"] .= sprintf("%d 號, ", $opt);
				}
			}

			// 參加活動被選擇的編號

			$option2 = array();

			if ($data["option2"] == "")
			{
				$data["option2"] = "--";
			}
			else
			{
				$option = explode(",", $data["option2"]);

				foreach ($option as $opt)
				{
					$sql  = "SELECT `aj`.`no`, `a`.`males`, `a`.`females`, ";
					$sql .= "`u`.`nickname`, `u`.`sex`, `u`.`email`, `u`.`msn`, `u`.`tel` ";
					$sql .= "FROM `activitiejoin` aj ";
					$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
					$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
					$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`no` = '$opt' ";
					$linkmysql->query($sql);

					if ($memberinfo = mysql_fetch_array($linkmysql->listmysql))
					{
						if ($memberinfo['no'] > $memberinfo['males'])
						{
							$memberinfo['no'] = sprintf("%d 號%s生", $memberinfo['no'] - $memberinfo['males'], $memberinfo['sex']);
						}
						else
						{
							$memberinfo['no'] = sprintf("%d 號%s生", $memberinfo['no'], $memberinfo['sex']);
						}

						$memberinfo['opt_info'] = '';

						if ($match_type_email == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>Email</b>:\n %s<br />\n", $memberinfo['email']);
						}

						if ($match_type_msn == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>MSN</b>:\n %s<br />\n", $memberinfo['msn']);
						}

						if ($match_type_tel == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>手機號碼</b>:\n %s<br />\n", $memberinfo['tel']);
						}

						array_push($option2, $memberinfo);
					}
				}
			}

			// 參加活動配對成功的編號

			$option3 = array();

			if ($data["option3"] == "")
			{
				$data["option3"] = "--";
			}
			else
			{
				$option = explode(",", $data["option3"]);

				foreach ($option as $opt)
				{
					$sql  = "SELECT `aj`.`no`, `a`.`males`, `a`.`females`, ";
					$sql .= "`u`.`nickname`, `u`.`sex`, `u`.`email`, `u`.`msn`, `u`.`tel` ";
					$sql .= "FROM `activitiejoin` aj ";
					$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
					$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
					$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`no` = '$opt' ";
					$linkmysql->query($sql);

					if ($memberinfo = mysql_fetch_array($linkmysql->listmysql))
					{
						if ($memberinfo['no'] > $memberinfo['males'])
						{
							$memberinfo['no'] = sprintf("%d 號%s生", $memberinfo['no'] - $memberinfo['males'], $memberinfo['sex']);
						}
						else
						{
							$memberinfo['no'] = sprintf("%d 號%s生", $memberinfo['no'], $memberinfo['sex']);
						}

						$memberinfo['opt_info'] = '';

						if ($match_type_email == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>Email</b>:\n %s<br />\n", $memberinfo['email']);
						}

						if ($match_type_msn == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>MSN</b>:\n %s<br />\n", $memberinfo['msn']);
						}

						if ($match_type_tel == 1) {
							$memberinfo['opt_info'] .= sprintf("<b>手機號碼</b>:\n %s<br />\n", $memberinfo['tel']);
						}

						array_push($option3, $memberinfo);
					}
				}
			}

			// 繳費方式及繳費狀態
			if ($data["charge_type"] == "iBon")
			{
				$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

				if (empty($pay_time)) {
					$data["charge"] = "<font color=\"red\">未繳費</font>";
				} else {
					$data["charge"] = "<font color=\"green\">已繳費</font>";
				}

				$data["charge_detail"] = sprintf("<a href=\"index.php?act=ibon&amp;sel=detail&amp;uid=%d&amp;aid=%d\">%s</a>",
					$data["uid"], $aid, "詳細資料");
			}
			else if ($data["charge_type"] == "coupon")
			{
				$data["charge"] = "<font color=\"blue\">優惠卷</font>";
				$data["charge"] = sprintf("<a href=\"index.php?act=coupon&sel=detail&id=%d\">%s</a>",
					$data["charge_id"], $data["charge"]);
			}

			// 顯示活動參與狀態
			if ($data["join_status"] == "join")
			{
				$data["join_status"] = "<font color=\"green\">參與</font>";
			}
			else if ($data["join_status"] == "apply_cancel")
			{
				$data["join_status"] = "<font color=\"red\">取消申請中</font>";
				$data["join_status"] = sprintf("<a href=\"./index.php?act=activitiesjoin&sel=getverifydetail&amp;uid=%d&amp;aid=%d\">%s</s>",
				$data["uid"], $aid, $data["join_status"]);

			}
			else if ($data["join_status"] == "cancel")
			{
				$data["join_status"] = "<font color=\"blue\">取消</font>";
				$data["join_status"] = sprintf("<a href=\"./index.php?act=activitiesjoin&sel=getverifydetail&amp;uid=%d&amp;aid=%d\">%s</s>",
				$data["uid"], $aid, $data["join_status"]);
			}
			else if ($data["join_status"] == "refuse_cancel")
			{
				$data["join_status"] = "<font color=\"red\">取消被拒</font>";
				$data["join_status"] = sprintf("<a href=\"./index.php?act=activitiesjoin&sel=getverifydetail&amp;uid=%d&amp;aid=%d\">%s</s>",
				$data["uid"], $aid, $data["join_status"]);
			}
			else if ($data["join_status"] == "EO_cancel")
			{
				$data["join_status"] = "<font color=\"red\">強制取消</font>";
			}

			$sql = "SELECT * FROM `blacklist` WHERE `black_id`='$uid' AND `aid`='$aid'";
			$linkmysql->query($sql);

			$data["blackrecord"] = "";

			$blackReviewing = 0;
			while ($blackdata = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($blackdata["result"] == "Pass")
				{
					$blackdata["result"] = "<font color=\"green\">通過</font>";
				}
				else if ($blackdata["result"] == "Refuse")
				{
					$blackdata["result"] = "<font color=\"red\">拒絕</font>";
				}
				else
				{
					$blackdata["result"] = "<font color=\"blue\">未審核</font>";
					$blackReviewing = 1;
				}

				$detail = sprintf("<a href=\"./index.php?act=membercenter&amp;sel=blackdetail&amp;black_serial=%d\">檢視</a>", $blackdata["black_serial"]);
				$data["blackrecord"] .= sprintf("提出時間: %s %s %s<br/>", $blackdata["accuse_time"], $blackdata["result"], $detail);
			}

			if ($data["blackrecord"] == "")
			{
				$data["blackrecord"] = "無黑名單提報記錄";
			}

			// 黑名單提報連結
			if ($blackReviewing == 0) {
				$data["accuseblack"] = sprintf("<a href=\"./index.php?act=member&amp;sel=accuseblack&amp;uid=%d&amp;aid=%d\">提報為黑名單</a>", $data["uid"], $aid);
			} else {
				$data["accuseblack"] = "此會員於此活動的黑名單提報尚未審核，所以無法再次提出";
			}

			$str = "確定要取消該會員的活動報名?";
			$data["cancel"] = sprintf("<a href=\"./activities.join.act.php?act=forcecancel&amp;aid=%d&amp;uid=%d\" onClick='return confirm(\"%s\");'>取消此會員的活動報名</a>", $aid, $uid, $str);

			$linkmysql->close_mysql();
			$tpl->assign("act_data", $act_data);
			$tpl->assign("joindata", $data);
			$tpl->assign("option2", $option2);
			$tpl->assign("option3", $option3);
			$tpl->assign("option2_count", count($option2));
			$tpl->assign("option3_count", count($option3));
			$tpl->assign("mainpage", "activities/activities.joindetail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該會員的活動詳細資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["sel"] == "canceldetail")
	{
		//---------------------------------------
		// 參加活動 -- 詳細資料
		//---------------------------------------
		$serial = $_GET["serial"];

		$linkmysql->init();
		
		// 已取消活動的會員紀錄
		$sql  = "SELECT `ac`.*, `u`.`username`, `u`.`sex`, `a`.`name` ";
		$sql .= "FROM `activitiecancel` ac ";
		$sql .= "LEFT JOIN `activitie` a ON `ac`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `ac`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `ac`.`serial` = '$serial' ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			// 活動資料連結
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);

			// 使用者名稱連結
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);

			// 繳費方式及繳費狀態
			if ($data["charge_type"] == "iBon")
			{
				$data["charge_type"]  = sprintf("%s&nbsp;&nbsp;繳費代碼:<b>%s</b>", $data["charge_type"], $data["ibon_no"]);
				
				if ($data["charge_status"] == 'Paid') {
					$data["charge_status"] = "<font color=\"green\">已繳費</font>";
				} else {
					$data["charge_status"] = "<font color=\"red\">未繳費</font>";
				}
			}
			else if ($data["charge_type"] == "coupon")
			{			
				$data["charge_type"] = "<font color=\"blue\">優惠卷</font>";
				$data["charge_status"] = sprintf("<a href=\"index.php?act=coupon&sel=detail&id=%d\">%s</a>",
					$data["charge_id"], $data["charge_type"]);
			}
			
			$linkmysql->close_mysql();
			$tpl->assign("cancel_data", $data);
			$tpl->assign("mainpage", "activities/activities.canceldetail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到資料');
		}
	}
	else if ($_GET["sel"] == "cancel")
	{
		//----------------------------------------
		// 取消參加活動
		//----------------------------------------

		/*
		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		// 若該會員有參加此活動
		$sql  = "SELECT `a`.`act_date`, `a`.`status`, `a`.`males`, `a`.`females`, ";
		$sql .= "`aj`.`charge_type`, `aj`.`charge_id`, `aj`.`join_status`, `u`.`sex` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
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
			$data["deadline"] = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-6, $tmp[0]));

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

			// 更新會員參與狀態
			$sql  = "UPDATE `activitiejoin` ";
			$sql .= "SET `join_status` = 'cancel' ";
			$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid'";
			$linkmysql->query($sql);

			if ($data["charge_type"] == "iBon")
			{
				// 使用iBon繳費
				$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
				$linkmysql->query($sql);
				list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

				if (!empty($pay_time))
				{
					$linkmysql->close_mysql();
					$message  = "已取消參加活動，您已使用iBon繳費完成，活動結束後系統將會統一給予活動優惠卷。<br/> ";
					$message .= "若您還想再重新參加活動，請點選 <b>活動參與選項</b> 「重新報名參加」。";
					$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
				else
				{
					$linkmysql->close_mysql();
					$message  = "已取消參加活動，您尚未使用iBon繳費完成，<br/> ";
					$message .= "若之後使用iBon繳費完成，活動結束後系統將會統一給予活動優惠卷。<br/> ";
					$message .= "若您還想再重新參加活動，請點選 <b>活動參與選項</b> 「重新報名參加」。";
					$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
			}
			else if ($data["charge_type"] == "coupon")
			{
				// 使用活動優惠卷
				$linkmysql->close_mysql();
				$message  = "已取消參加活動，您使用活動優惠卷報名，活動結束後系統將會根據優惠卷等級給予活動優惠卷。<br/> ";
				$message .= "若您還想再重新參加活動，請點選 <b>活動參與選項</b> 「重新報名參加」。";
				$tool->ShowMsgPage($message, "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("沒有參加或是無此活動", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
		*/
	}
	else if ($_GET["sel"] == "MatchCount")
	{
		//----------------------------------------
		// 設定活動配對可選擇的會員個數
		//----------------------------------------

		$aid = $_GET["aid"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid';";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["status"] != "PROCEED")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("目前無法填寫問卷結果", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			$upperbound = ($data["males"] > $data["females"]) ? $data["males"] : $data["females"];
			$default = ceil($upperbound/3);
			$option = "";

			for ($i = 1; $i <= $upperbound; $i++)
			{
				if ($i == $default)
				{
					$option .= sprintf("<option value=\"%d\" selected>%d</option>", $i, $i);
				}
				else
				{
					$option .= sprintf("<option value=\"%d\">%d</option>", $i, $i);
				}
			}

			$tpl->assign("activitiedata", $data);
			$tpl->assign("option", $option);
			$tpl->assign("mainpage", "activities/activities.MatchCount.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到指定的活動資料");
		}
	}
	else if ($_GET["sel"] == "questionary")
	{
		//----------------------------------------
		// 問卷結果填寫的功能
		//----------------------------------------
		$aid = $_GET["aid"];
		$match_count = $_GET["count"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['use_match'] != 'YES')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動尚未設定使用活動配對", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($act_data['status'] != "PROCEED")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("目前無法填寫問卷結果", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}

		$sql  = "SELECT `aj`.`uid`, `aj`.`no`, `aj`.`option1`, `aj`.`attendance`, ";
		$sql .= "`u`.`username`, `u`.`sex` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY `aj`.`no` ASC";
		$linkmysql->query($sql);

		$memberdata = array();
		$index = 1;

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["index"] = $index++;

			if ($data["no"] != '')
			{
				if ($data["sex"] == '男')
				{
					$data["sex_count"] = sprintf("%d 號男生", $data["no"]);
				}
				else if ($data["sex"] == '女')
				{
					$data["sex_count"] = sprintf("%d 號女生", $data["no"] - $act_data['males']);
				}
			}

			$tmp_nos = explode(",", $data["option1"]);

			$data["option1"] = array();

			for ($j = 0; $j < $match_count; $j++)
			{
				$no = $tmp_nos[$j];

				$str = sprintf("<option value=\"\">未選擇</option>\n");

				if ($data['sex'] == '女')
				{
					for ($i = 1; $i <= $act_data['males']; $i++)
					{
						if ($i == $no) {
							$str .= sprintf("<option value=\"%d\" selected>%d 號男生</option>\n", $i, $i);
						} else {
							$str .= sprintf("<option value=\"%d\">%d 號男生</option>\n", $i, $i);
						}
					}

					for ($i = $act_data['males'] + 1; $i <= $act_data['males'] + $act_data['females']; $i++)
					{
						if ($i == $no) {
							$str .= sprintf("<option value=\"%d\" selected>%d 號女生</option>\n", $i, $i - $act_data['males']);
						} else {
							$str .= sprintf("<option value=\"%d\">%d 號女生</option>\n", $i, $i - $act_data['males']);
						}
					}
				}
				else if ($data['sex'] == '男')
				{
					for ($i = $act_data['males'] + 1; $i <= $act_data['males'] + $act_data['females']; $i++)
					{
						if ($i == $no) {
							$str .= sprintf("<option value=\"%d\" selected>%d 號女生</option>\n", $i, $i - $act_data['males']);
						} else {
							$str .= sprintf("<option value=\"%d\">%d 號女生</option>\n", $i, $i - $act_data['males']);
						}
					}

					for ($i = 1; $i <= $act_data['males']; $i++)
					{
						if ($i == $no) {
							$str .= sprintf("<option value=\"%d\" selected>%d 號男生</option>\n", $i, $i);
						} else {
							$str .= sprintf("<option value=\"%d\">%d 號男生</option>\n", $i, $i);
						}
					}
				}

				array_push($data["option1"], $str);
			}

			if ($data["attendance"] == 'true')
			{
				$data["attendance"] = 'checked';
			}
			else
			{
				$data["attendance"] = '';
			}

			array_push( $memberdata, $data);
		}

		$count = count($memberdata);

		$tpl->assign("aid", $aid);
		$tpl->assign("match_count", $match_count);
		$tpl->assign("member_count", $count);
		$tpl->assign("memberdata", $memberdata);
		$tpl->assign("mainpage", "activities/activities.questionary.html");
	}
	else if ($_GET["sel"] == "matchresult")
	{
		//----------------------------------------
		// 取得活動的配對結果，顯示有參加配對的會員
		//----------------------------------------

		$aid = $_GET["aid"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['use_match'] != 'YES')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動尚未設定使用活動配對", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if ($act_data['status'] != "PROCEED" && $act_data['status'] != "CLOSE")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("目前無法檢視配對結果", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}

		$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, `aj`.`attendance`, `aj`.`join_status`, ";
		$sql .= "`aj`.`no`, `aj`.`option1`, `aj`.`option2`, `aj`.`option3`, `u`.`username`, `u`.`sex`";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY `aj`.`no` ASC";
		$linkmysql->query($sql);

		$memberdata = array();

		$match_result = array();
		$match_result["hot_boy"] = "";
		$match_result["hot_boy_count"] = 0;
		$match_result["hot_girl"] = "";
		$match_result["hot_girl_count"] = 0;

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);

			if ($data["no"] != '')
			{
				if ($data["sex"] == '男')
				{
					$data["sex_count"] = sprintf("%d 號男生", $data["no"]);
				}
				else if ($data["sex"] == '女')
				{
					$data["sex_count"] = sprintf("%d 號女生", $data["no"] - $act_data['males']);
				}
			}

			if ($data["attendance"] == "true") {
				$data["attendance"] = "<font color=\"green\">是</font>";
			} else if ($data["attendance"] == "false") {
				$data["attendance"] = "<font color=\"red\">否</font>";
			} else {
				$data["attendance"] = "--";
			}

			if ($data["option1"] == "")	{
				$data["option1"] = "--";
			}

			if ($data["option2"] == "")
			{
				$data["option2"] = "--";
			}
			else
			{
				$option = explode(",", $data["option2"]);

				if ($data["sex"] == "男")
				{
					if (count($option) > $match_result["hot_boy_count"])
					{
						$match_result["hot_boy"] = $data["username"];
						$match_result["hot_boy_count"] = count($option);
					}
					else if (count($option) == $match_result["hot_boy_count"])
					{
						$match_result["hot_boy"] .= ", " . $data["username"];
						$match_result["hot_boy_count"] = count($option);
					}
				}
				else if ($data["sex"] == "女")
				{
					if (count($option) > $match_result["hot_girl_count"])
					{
						$match_result["hot_girl"] = $data["username"];
						$match_result["hot_girl_count"] = count($option);
					}
					else if (count($option) == $match_result["hot_girl_count"])
					{
						$match_result["hot_girl"] .= ", " . $data["username"];
						$match_result["hot_girl_count"] = count($option);
					}
				}
			}

			if ($data["option3"] == "") {
				$data["option3"] = "--";
			}

			array_push( $memberdata, $data);
		}

		if ($match_result["hot_boy"] == "")
		{
			$match_result["hot_boy"] = "--";
		}

		if ($match_result["hot_girl"] == "")
		{
			$match_result["hot_girl"] = "--";
		}

		$tpl->assign("memberdata", $memberdata);
		$tpl->assign("match_result", $match_result);
		$tpl->assign("mainpage", "activities/activities.result.html");
	}
	else if ($_GET["sel"] == "verifylist")
	{
		//----------------------------------------
		// 取得申請取消的列表
		//----------------------------------------
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		$sql = "SELECT COUNT(*) FROM `charge_ibon` ";

		$sql  = "SELECT  COUNT(`ca`.`id`) ";
		$sql .= "FROM `cancelapply` ca ";
		$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `ca`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `ca`.`uid` ";

		if ($_SESSION["authority"] != "Admin")
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";

		$linkmysql->query($sql);
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));

		// 分頁設定
		$itemperpage = 15;

		$pageinfo["totalpage"] = $pageinfo["count"] / $itemperpage;

		if ($pageinfo["count"]%$itemperpage != 0) {
			$pageinfo["totalpage"] = intval($pageinfo["totalpage"]) + 1;
		} else {
			$pageinfo["totalpage"] = intval($pageinfo["totalpage"]);
		}

		if (!isset($_GET["page"])) {
			$pageinfo["nowpage"] = 1;
		} else {
			$pageinfo["nowpage"] = $_GET["page"];
		}

		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );

		$sql  = "SELECT `ca`.`id`, `u`.`username`, `ca`.`type`, `a`.`name`, `ca`.`apply_time`, `ca`.`result` ";
		$sql .= "FROM `cancelapply` ca ";
		$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `ca`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `ca`.`uid` ";

		if ($_SESSION["authority"] != "Admin")
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";

		$sql .= "ORDER BY `ca`.`type` ASC, `ca`.`apply_time` DESC ";
		$sql .= "LIMIT $head , $itemperpage";
		$linkmysql->query($sql);

		$verifylist = array();
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$data["username"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=verifydetail&amp;id=%d\" >%s</a>", $data["id"], $data[username]);

			if ($data["type"] == "EOCancel") {
				$data["type"] = "<font color=\"red\">EO取消活動</font>";
			} else if ($data["type"] == "UserCancel") {
				$data["type"] = "<font color=\"blue\">User取消參與</font>";
			}

			if ($data["result"] == "Pass") {
				$data["result"] = "<font color=\"green\">通過</font>";
			} else if ($data["result"] == "Refuse") {
				$data["result"] = "<font color=\"red\">拒絕</font>";
			} else {
				$data["result"] = "<font color=\"blue\">尚未審核</font>";
			}

			array_push( $verifylist, $data);
		}

		$url ="./index.php?act=activitiesjoin&amp;sel=verifylist";

		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);

		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);

		$linkmysql->close_mysql();
		$tpl->assign("verifylist", $verifylist);
		$tpl->assign("mainpage", "activities\activities.verifylist.html");
	}
	else if ($_GET["sel"] == "getverifydetail")
	{
		//----------------------------------------
		// 取得審查申請的詳細資料位置
		//----------------------------------------
		$uid = $_GET["uid"];
		$aid = $_GET["aid"];

		$linkmysql->init();
		$sql  = "SELECT `id` FROM `cancelapply` WHERE `uid` = '$uid' AND `aid` = '$aid'";
		$linkmysql->query($sql);

		if (list($id) = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->URL("index.php?act=activitiesjoin&sel=verifydetail&id=$id");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到審查的資料。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}
	}
	else if ($_GET["sel"] == "verifydetail")
	{
		//----------------------------------------
		// 檢視審查申請的詳細資料
		//----------------------------------------
		$id = $_GET["id"];
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		$sql  = "SELECT `ca`.`id`, `u`.`uid`, `u`.`username`, `ca`.`type`, `ca`.`reason`, `a`.`ownerid`, ";
		$sql .= "`a`.`aid`, `a`.`name`, `ca`.`apply_time`, `ca`.`verify_id`, `u1`.`username` AS `verify_name`, `ca`.`comment`, ";
		$sql .= "`ca`.`result`, `ca`.`verify_time`, `aj`.`charge_type`, `aj`.`charge_id` ";
		$sql .= "FROM `cancelapply` ca ";
		$sql .= "LEFT JOIN `activitie` a ON `a`.`aid` = `ca`.`aid` ";
		$sql .= "LEFT JOIN `activitiejoin` aj ON `aj`.`aid` = `ca`.`aid` AND `aj`.`uid` = `ca`.`uid` ";
		$sql .= "LEFT JOIN `user` u ON `u`.`uid` = `ca`.`uid` ";
		$sql .= "LEFT JOIN `user` u1 ON `u1`.`uid` = `ca`.`verify_id` ";
		$sql .= "WHERE `ca`.`id` = '$id'; ";
		$linkmysql->query($sql);

		$verify = 0;

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($_SESSION["authority"] != "Admin" && $data["ownerid"] != $uid)
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("無法檢視申請資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=" . $data["aid"]);
			}

			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);
			$data["reason"] = nl2br($tool->AddLink2Text($data["reason"]));

			if ($data["result"] == "" && $_SESSION["authority"] == "Admin")
			{
				$verify = 1;
			}

			if ($data["type"] == "EOCancel")
			{
				$data["charge_type"] = "N/A";
				$data["charge_status"] = "N/A";
				$data["type"] = "<font color=\"red\">EO取消活動</fon>";
			}
			/*
			else
			{
				$data["type"] = "<font color=\"blue\">User取消參與</fon>";

				if ($data["result"] == "" && (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")) {
					$verify = 1;
				} else {
					$verify = 0;
				}

				if ($data["charge_type"] == "iBon")
				{
					$sql = sprintf("SELECT `pay_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $data["charge_id"]);
					$linkmysql->query($sql);
					list($pay_time) = mysql_fetch_array($linkmysql->listmysql);

					if (empty($pay_time)) {
						$data["charge_status"] = "<font color=\"red\">未繳費</font>";
					} else {
						$data["charge_status"] = "<font color=\"green\">已繳費</font>";
					}
				}
				else if ($data["charge_type"] == "coupon")
				{
					$data["charge_status"] = "<font color=\"blue\">優惠卷</font>";
				}
			}
			*/

			if ($verify == 0)
			{
				$data["verify_name"] = sprintf("<a href=\"./index.php?act=member&sel=detail&uid=%d\">%s</a>", $data["verify_id"], $data["verify_name"]);
				$data["comment"] = nl2br($tool->AddLink2Text($data["comment"]));

				if ($data["result"] == "Pass") {
					$data["result"] = "<font color=\"green\">通過</fon>";
				} else if ($data["result"] == "Refuse") {
					$data["result"] = "<font color=\"red\">拒絕</fon>";
				} else {
					$data["result"] = "<font color=\"blue\">尚未審核</fon>";
				}
			}

			$linkmysql->close_mysql();

			$tpl->assign("verify", $verify);
			$tpl->assign("verifydata", $data);
			$tpl->assign("mainpage", "activities/activities.verifydetail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到審查的資料。");
		}
	}
	else if ($_GET["sel"] == "addUser")
	{
		//----------------------------------------
		// 管理員從後台邀請使用者報名活動
		//----------------------------------------

		$aid = $_GET["aid"];

		if ($_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("權限不足無法使用此項功能", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$linkmysql->init();

		$sql  = "SELECT * FROM `activitie` WHERE `aid` = '$aid'";
		$linkmysql->query($sql);


		if (!$data = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所選擇的活動資料");
		}

		$linkmysql->close_mysql();

		if ($data["status"] != "OPEN")
		{
			$tool->ShowMsgPage("活動非開放報名的狀態無法邀請會員", "index.php?act=activities&sel=detail&aid=$aid");
		}

		$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);

		// 解析活動費用字串
		if (preg_match("/All: (.+)/", $data["charge"], $matche))
		{
			$data["charge"] = sprintf("新台幣 <b>%d</b> 元", $matche[1]);
		}
		else if (preg_match("/Male: (.+), Female: (.+)/" , $data["charge"], $matche))
		{
			$data["charge"] = sprintf("男: <b>%d</b> 元 / 女: <b>%d</b> 元", intval($matche[1]), intval($matche[2]));
		}
		else
		{
			$data["charge"] = sprintf("新台幣 <b>%d</b> 元", $data["charge"]);
		}

		$tpl->assign("activitiedata", $data);
		$tpl->assign("mainpage", "activities/activities.addUser.html");
	}
	else if ($_GET["sel"] == "ReviseMemberNo")
	{
		//----------------------------------------
		// 重新調整會員的活動編號
		//----------------------------------------

		$aid = $_GET["aid"];

		$linkmysql->init();

		$sql = "SELECT * FROM `activitie` WHERE `aid` = '$aid' ";
		$linkmysql->query($sql);

		if ($act_data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($act_data['status'] != 'PROCEED')
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("此活動狀態不是現在進行中，無法變更會員活動編號", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage('找不到活動資料');
		}

		$sql  = "SELECT `aj`.`uid`, `aj`.`no`, `u`.`sex`, `u`.`username` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`join_status` = 'join' ";
		$sql .= "ORDER BY `aj`.`no` ASC";
		$linkmysql->query($sql);

		$join_data = array();
		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data['sex'] == '男')
			{
				$str = '';
				for ($i = 1; $i <= $act_data['males']; $i++)
				{
					if ($i == $data['no'])
					{
						$str .= sprintf("<option value=\"%d\" selected>%d 號男生</option>\n", $i, $i);
					}
					else
					{
						$str .= sprintf("<option value=\"%d\">%d 號男生</option>\n", $i, $i);
					}
				}

				$data['sex_no'] = sprintf("%d 號男生\n", $data['no']);
			}
			else if ($data['sex'] == '女')
			{
				$str = '';
				for ($i = $act_data['males'] + 1; $i <= $act_data['males'] + $act_data['females']; $i++)
				{
					if ($i == $data['no'])
					{
						$str .= sprintf("<option value=\"%d\" selected>%d 號女生</option>\n", $i, $i-$act_data['males']);
					}
					else
					{
						$str .= sprintf("<option value=\"%d\">%d 號女生</option>\n", $i, $i-$act_data['males']);
					}
				}

				$data['sex_no'] = sprintf("%d 號女生\n", $data['no'] - $act_data['males']);
			}

			$data["username"] = $tool->ShowMemberLink( $data["uid"], $data["username"]);
			$data['options'] = $str;

			array_push($join_data, $data);
		}

		$tpl->assign("aid", $aid);
		$tpl->assign("males", $act_data['males']);
		$tpl->assign("revise_count", count($join_data));
		$tpl->assign("joindata", $join_data);
		$tpl->assign("mainpage", "activities/activities.revisememberno.html");
	}
	else
	{
		$tool->ShowMsgPage("Activities Join ERROR: unknown command.");
	}
?>