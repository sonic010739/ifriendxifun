<?
	/*
	**	activites status
	**
	**	OPEN 			: 活動開放報名
	**	PROCEED			: 活動現正進行
	**	CLOSE			: 活動已經關閉
	**	APPLY_CANCEL	: 活動申請取消
	**	CANCEL			: 活動已經取消
	**
	**	state transition
	**	OPEN --> PROCEED -->  CLOSE
	**	      |-> APPLY_CANCEL --> CANCEL
	*/

	if ($_GET["sel"] == "add")
	{
		//---------------------------------------
		// 新增活動
		//---------------------------------------

		if ($_SESSION["login"] != 1)
		{
			$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
		}
		else
		{
			if ($_SESSION["authority"] != "Admin" && $_SESSION["authority"] != "EO")
			{
				$tool->ShowMsgPage("權限不足，無法新增活動");
			}
		}

		$linkmysql->init();

		// 場地資料下拉式選單
		$sql = "SELECT `pid`, `placename`, `placecity` FROM `place` ORDER BY `placecity` ASC";
		$linkmysql->query($sql);

		$placedata = "";

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$placedata .= sprintf("<option value=\"%d\">[%s] %s</option>\n", $data["pid"], $data["placecity"], $data["placename"]);
		}

		// 主題資料下拉式選單
		$topicdata = "";

		$sql = "SELECT `tid`, `tname` FROM `topic`";
		$linkmysql->query($sql);

		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$topicdata .= sprintf("<option value=\"%d\">%s</option>\n", $data[0], $data[1]);
		}

		// 族群資料下拉式選單
		$groupdata = "";

		$sql = "SELECT `gid`, `gname` FROM `group`";
		$linkmysql->query($sql);

		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$groupdata .= sprintf("<option value=\"%d\">%s</option>\n", $data[0], $data[1]);
		}

		$linkmysql->close_mysql();

		$tpl->assign("placedata", $placedata);
		$tpl->assign("topicdata", $topicdata);
		$tpl->assign("groupdata", $groupdata);
		$tpl->assign("mainpage", "activities/activities.add.html");
	}
	else if ($_GET["sel"] == "detail")
	{
		//---------------------------------------
		// 活動詳細資料
		//---------------------------------------

		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		$memberjoin = 0;
		$use_mail_message = 0;

		if ($aid > 0)
		{
			$linkmysql->init();

			// 會員的性別與年齡資料
			$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			$member = mysql_fetch_array($linkmysql->listmysql);

			$birth_year = $member["birth_year"];
			$sex = $member["sex"];

			// 活動詳細資料
			$sql  = "SELECT `a`.*, `u`.`username`, `p`.`placename`, `p`.`placecity`, `t`.`tname`, `g`.`gname` ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `group` g ON `a`.`group` = `g`.`gid` ";
			$sql .= "WHERE `a`.`aid` = '$aid'";
			$linkmysql->query($sql);

			if ($data = mysql_fetch_array($linkmysql->listmysql))
			{
				// 控制是否可使用寄信及簡訊功能，設定值為七天
				$date = explode("-", $data["act_date"]);
				$time = explode(":", $data["act_time"]);
				$deadline = date("Y-m-d H:i:s", mktime($time[0], $time[1], $time[2], $date[1], $date[2]+7, $date[0]));

				if ($deadline >= date("Y-m-d H:i:s"))
				{
					$use_mail_message = 1;
				}

				// 將活動的日期，拆解成年月日並且加上星期
				$week =	array("日", "一", "二", "三", "四", "五", "六");
				$data["act_date"] .= " (" . $week[date("w", mktime(0, 0, 0, $date[1], $date[2], $date[0]))] .")";
				$data["act_time"] = sprintf("%02d:%02d", intval($time[0]), intval($time[1]));

				// 報名截止日
				$join_deadline = $data["join_deadline"];
				$tmp = explode("-", $data["join_deadline"]);
				$data["join_deadline"] .= " (" . $week[date("w", mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]))] .")";
				$data["current_join"] = "";

				if (mktime(0, 0, 0, $tmp[1], $tmp[2]-14, $tmp[0]) >= time())
				{
					$data["current_join"] = "熱烈報名中";
				}
				else if (mktime(0, 0, 0, $tmp[1], $tmp[2]-4, $tmp[0]) >= time())
				{
					$data["current_join"] = "即將額滿";
				}
				else
				{
					$data["current_join"] = "無法報名";
				}

				// 活動說明
				$data["decription"] = nl2br($data["decription"]);

				// 解析人數與性別限制字串
				if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $data["people_limit"], $matche))
				{
					$sex_limit = 1;
					$data["male_limit"] = intval($matche[1]);
					$data["female_limit"] = intval($matche[2]);
					$data["people_limit"] = sprintf("男 %d 人 / 女 %d 人", intval($matche[1]), intval($matche[2]));
				}
				else if (preg_match("/No limit total: (.+)\./" , $data["people_limit"], $matche))
				{
					$sex_limit = 0;
					$data["total_limit"] = intval($matche[1]);
					$data["people_limit"] = sprintf("不限性別，共 %d 人", intval($matche[1]));
				}
				else if (preg_match("/No limit./" , $data["people_limit"], $matche))
				{
					$sex_limit = 0;
					$data["total_limit"] = 999999;
					$data["people_limit"] = "不限人數";
				}

				// 是否可使用優惠方案
				$data["use_discount"] = $data["use_discount"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";
				// 是否可使用優惠卷
				$data["use_coupon"] = $data["use_coupon"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";

				// 是否可使用活動配對
				$use_match = $data["use_match"] == "YES" ? 1 : 0;
				$data["use_match"] = $data["use_match"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";

				// 是否可使用新版活動配對
				$use_newmatch = $data["use_newmatch"] == "YES" ? 1 : 0;
				$data["use_newmatch"] = $data["use_newmatch"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";

				// 解析年齡限制字串
				if (preg_match("/Age limit lb: (.+), ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$data["age_limit"] = sprintf("男女年齡限制&nbsp;%2d&nbsp;歲至&nbsp;%2d&nbsp;歲",
						intval($matche[1]), intval($matche[2]));
				}
				else if (preg_match("/Age limit male_lb: (.+), male_ub: (.+), female_lb: (.+), female_ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$data["age_limit"]  = sprintf("男 %2d 至 %2d 歲<br/>", intval($matche[1]), intval($matche[2]));
					$data["age_limit"] .= sprintf("女 %2d 至 %2d 歲<br/>", intval($matche[3]), intval($matche[4]));
				}
				else if (preg_match("/No age limit\./" , $data["age_limit"], $matche))
				{
					$data["age_limit"] = "不限制年齡";
				}

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

				if ($uid != $data["ownerid"])
				{
					if ($data["status"] == "OPEN")
					{
						//$data["option"] = "<font color=\"green\">立即登記報名</font>";
						$data["option"] = "<img src=\"images/join.gif\" alt=\"立即登記報名\" border=\"0\"/>";

						if (date("Y-m-d") > $join_deadline)
						{
							$data["option"] = "<font color=\"red\">報名已截止!</font>";
						}
						else
						{
							$data["option"] = sprintf("<a href=\"index.php?act=activitiesjoin&amp;sel=join&amp;aid=%d\">%s</a>", $data["aid"], $data["option"]);

							// 根據對應的性別檢查活動參加人數
							if ($sex_limit == 1)
							{
								if ($sex == "男" && $data["males"] >= $data["male_limit"])
								{
									$data["option"] = "<font color=\"blue\">男生名額已滿</font>";
								}
								else if ($sex == "女" && $data["females"] >= $data["female_limit"])
								{
									$data["option"] = "<font color=\"blue\">女生名額已滿</font>";
								}
							}
							else if ($sex_limit == 0)
							{
								if (($data["males"] + $data["females"]) >= $data["total_limit"])
								{
									$data["option"] = "<font color=\"blue\">名額已滿</font>";
								}
							}
						}
					}
					else if ($data["status"] == "PROCEED")
					{
						$data["option"] = "<font color=\"gray\">無法報名</font>";
					}
					else if ($data["status"] == "CLOSE")
					{
						$data["option"] = "<font color=\"gray\">無法報名</font>";
					}
					else if ($data["status"] == "APPLY_CANCEL")
					{
						// $data["option"] = "<font color=\"green\">立即登記報名</font>";
						$data["option"] = "<img src=\"images/join.gif\" alt=\"立即登記報名\" />";
					}
					else if ($data["status"] == "CANCEL")
					{
						$data["option"] = "<font color=\"gray\">無法報名</font>";
					}
				}
				else
				{
					$data["option"] = "--";
				}
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("找不到活動資料", "回到活動列表", "index.php?act=activitielist");
			}

			///////////////////////////////////////////////////////////////////
			//			以上為活動資料
			///////////////////////////////////////////////////////////////////

			// 活動管理介面
			$managedata = array();
			$managedata["paid_charge"] = 0;
			$managedata["paid_males"] = 0;
			$managedata["paid_females"] = 0;
			$managedata["Unpay_males"] = 0;
			$managedata["Unpay_females"] = 0;
			$managedata["coupon_males"] = 0;
			$managedata["coupon_females"] = 0;
			$managedata['act_owner'] = 0;
			$managedata['EO'] = 0;
			$managedata['Admin'] = 0;
			$managedata['sendmail'] = $use_mail_message;
			$managedata['sendmessage'] = $use_mail_message;
			$managedata['use_match'] = $use_match;
			$managedata['use_newmatch'] = $use_newmatch;

			if ($_SESSION["authority"] == "Admin" || $_SESSION["authority"] == "EO")
			{
				$managedata['EO'] = 1;
			}

			if ($_SESSION["authority"] == "Admin")
			{
				$managedata['Admin'] = 1;
				$managedata['modify2'] = sprintf("<a href=\"./index.php?act=activities&amp;sel=modify2&amp;aid=%d\"><b>修改活動</b></a>", $data["aid"]);
				$managedata['addUser'] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=addUser&amp;aid=%d\"><b>邀請會員</b></a>", $data["aid"]);
			}

			if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")
			{
				$managedata['act_owner'] = 1;

				// 活動管理的字串。
				$managedata['manage'] = "";

				$managedata['modify'] 	= "<font color=\"gray\">修改活動</font>";
				$managedata['proceed'] 	= "<font color=\"gray\">進行活動</font>";
				$managedata['close'] 	= "<font color=\"gray\">關閉活動</font>";
				$managedata['cancel'] 	= "<font color=\"gray\">取消活動</font>";

				$managedata["signintable"] = "<font color=\"gray\">活動簽到表(男/女)</font>";
				$managedata["questionary"] = "<font color=\"gray\">填寫問卷結果</font>";
				$managedata["matchresult"] = "<font color=\"gray\">檢視配對結果</font>";
				$managedata["newmatchresult"] = "<font color=\"gray\">檢視新版配對結果</font>";
				$managedata["revise_member_no"] = "<font color=\"gray\">調整會員活動編號</font>";

				// 重新統計活動人數的功能
				$managedata["ReCount"] = sprintf("<a href=\"./activities.act.php?act=ReCount&amp;aid=%d\"><b>重新統計</b></a>", $aid);

				if ($data["status"] == "OPEN")
				{
					$managedata['modify'] 	= sprintf("<a href=\"./index.php?act=activities&amp;sel=modify&amp;aid=%d\"><b>修改活動</b></a>", $data["aid"]);
					$managedata['proceed'] 	= sprintf("<a href=\"./activities.act.php?act=SetProceed&amp;aid=%d\" onClick='return confirm(\"確定要進行活動?\\n進行活動後將無法取消活動\")'><b>進行活動</b></a>", $data["aid"]);
					$managedata['cancel'] 	= sprintf("<a href=\"./index.php?act=activities&amp;sel=cancel&amp;aid=%d\" ><b>取消活動</b></a>", $data["aid"]);
				}
				else if ($data["status"] == "PROCEED")
				{
					$managedata['close'] 	= sprintf("<a href=\"./activities.act.php?act=SetClose&amp;aid=%d\" onClick='return confirm(\"確定要關閉活動?\\n\\n若本活動需要填寫問卷請先確認完成問卷填寫再關閉活動!\")'><b>關閉活動</b></a>", $data["aid"]);

					// 活動簽到表
					$signintable1 = sprintf("<a href=\"./signintable.php?aid=%d&amp;type=%d\" target=\"_blank\"><b>男</b></a>", $aid, 0);
					$signintable2 = sprintf("<a href=\"./signintable.php?aid=%d&amp;type=%d\" target=\"_blank\"><b>女</b></a>", $aid, 1);
					$managedata["signintable"] = "活動簽到表(" . $signintable1 ."/" . $signintable2 .")";

					$managedata["revise_member_no"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=ReviseMemberNo&amp;aid=%d\">調整會員活動編號</a>", $aid);

					if ($managedata['use_match'] == 1)
					{
						$managedata["questionary"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=MatchCount&amp;aid=%d\">填寫問卷結果</a>", $aid);
						$managedata["matchresult"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=matchresult&amp;aid=%d\">檢視配對結果</a>", $aid);
					}

					if ($managedata['use_newmatch'] == 1)
					{
						$managedata["newmatchresult"] = sprintf("<a href=\"./index.php?act=activitiesmatch&amp;sel=overview&amp;aid=%d\">檢視新版配對結果</a>", $aid);
					}
				}
				else if ($data["status"] == "CLOSE")
				{
					if ($managedata['use_match'] == 1)
					{
						$managedata["matchresult"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=matchresult&amp;aid=%d\">檢視配對結果</a>", $aid);
					}

					if ($managedata['use_newmatch'] == 1)
					{
						$managedata["newmatchresult"] = sprintf("<a href=\"./index.php?act=activitiesmatch&amp;sel=overview&amp;aid=%d\">檢視新版配對結果</a>", $aid);
					}
				}

				// 已參加的會員
				$join_tmp = array();
				$act_joindata = array();

				// 有參加活動的會員
				$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, `aj`.`join_status`, ";
				$sql .= "`aj`.`no`, `u`.`username`, `u`.`nickname`, `u`.`sex`, `u`.`birth_year`";
				$sql .= "FROM `activitiejoin` aj ";
				$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
				$sql .= "WHERE `aj`.`aid` = '$aid' ";
				$sql .= "ORDER BY `u`.`sex` ASC, `aj`.`no` ASC, `aj`.`serial` ASC";
				$linkmysql->query($sql);

				while ($joindata = mysql_fetch_array($linkmysql->listmysql))
				{
					array_push($join_tmp, $joindata);
				}

				foreach ($join_tmp as $joindata)
				{
					// 參加活動的配對編號
					if (!empty($joindata["no"]))
					{
						if ($joindata["sex"] == "男")
						{
							$joindata["no"] = sprintf("%d 號男生", $joindata["no"]);
						}
						else if ($joindata["sex"] == "女")
						{
							$joindata["no"] = sprintf("%d 號女生", $joindata["no"] - $data["males"]);
						}
					}
					else
					{
						$joindata["no"] = "--";
					}

					// 年齡
					$joindata["age"] = date("Y") - $joindata["birth_year"];

					// 統計繳費方式及繳費狀態
					if ($joindata["charge_type"] == "iBon")
					{
						$sql = sprintf("SELECT `pay_time`, `fees` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $joindata["charge_id"]);
						$linkmysql->query($sql);
						list($pay_time, $fees) = mysql_fetch_array($linkmysql->listmysql);

						if ($joindata["join_status"] == "join")
						{
							if ($joindata["sex"] == "男" && empty($pay_time))
							{
								$managedata["Unpay_males"]++;
								$joindata["charge"] = "<font color=\"red\">未繳費</font>";
							}
							else if ($joindata["sex"] == "男" && !empty($pay_time))
							{
								$managedata["paid_males"]++;
								$managedata["paid_charge"] += $fees;
								$joindata["charge"] = "<font color=\"green\">已繳費</font>";
							}
							else if ($joindata["sex"] == "女" && empty($pay_time))
							{
								$managedata["Unpay_females"]++;
								$joindata["charge"] = "<font color=\"red\">未繳費</font>";
							}
							else if ($joindata["sex"] == "女" && !empty($pay_time))
							{
								$managedata["paid_females"]++;
								$managedata["paid_charge"] += $fees;
								$joindata["charge"] = "<font color=\"green\">已繳費</font>";
							}
						}

						$joindata["charge"] = sprintf("<a href=\"index.php?act=ibon&amp;sel=detail&amp;uid=%d&amp;aid=%d\">%s</a>", $joindata["uid"], $aid, $joindata["charge"]);
					}
					else if ($joindata["charge_type"] == "coupon")
					{
						$joindata["charge"] = "<font color=\"blue\">優惠卷</font>";
						$joindata["charge"] = sprintf("<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">%s</a>", $joindata["charge_id"], $joindata["charge"]);

						if ($joindata["sex"] == "男" && $joindata["join_status"] == "join")
						{
							$managedata["coupon_males"]++;
						}
						else if ($joindata["sex"] == "女" && $joindata["join_status"] == "join")
						{
							$managedata["coupon_females"]++;
						}
					}

					if ($joindata["join_status"] == "join") {
						$joindata["join_status"] = "<font color=\"green\">參與</font>";
					} else if ($joindata["join_status"] == "cancel") {
						$joindata["join_status"] = "<font color=\"blue\">已取消</font>";
					} else if ($joindata["join_status"] == "EO_cancel") {
						$joindata["join_status"] = "<font color=\"red\">EO取消</font>";
					}

					// 使用者名稱連結
					$joindata["username"] = $tool->ShowMemberLink( $joindata["uid"], $joindata["username"]);

					// 活動詳細資料
					$joindata["detail"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=joindetail&amp;uid=%d&amp;aid=%d\">檢視</a>", $joindata["uid"], $aid);

					array_push($act_joindata, $joindata);
				}

				// 已取消的會員
				unset($join_tmp);
				$join_tmp = array();
				$act_canceldata = array();

				// 已取消活動的會員紀錄
				$sql  = "SELECT `ac`.*, `u`.`username`, `u`.`nickname`, `u`.`sex`, `u`.`birth_year` ";
				$sql .= "FROM `activitiecancel` ac ";
				$sql .= "LEFT JOIN `user` u ON `ac`.`uid` = `u`.`uid` ";
				$sql .= "WHERE `ac`.`aid` = '$aid' ";
				$sql .= "ORDER BY `ac`.`serial` ASC";
				$linkmysql->query($sql);

				while ($joindata = mysql_fetch_array($linkmysql->listmysql))
				{
					array_push($join_tmp, $joindata);
				}

				foreach ($join_tmp as $joindata)
				{
					// 年齡
					$joindata["age"] = date("Y") - $joindata["birth_year"];

					if ($joindata["charge_type"] == "iBon")
					{
						if ($joindata["charge_status"] == "Paid")
						{
							$joindata["charge"] = "<font color=\"green\">已繳費</font>";
						}
						else
						{
							$joindata["charge"] = "<font color=\"red\">未繳費</font>";
						}
					}
					else  if ($joindata["charge_type"] == "coupon")
					{
						$joindata["charge"] = "<font color=\"blue\">優惠卷</font>";
						$joindata["charge"] = sprintf("<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">%s</a>", $joindata["charge_id"], $joindata["charge"]);
					}

					// 使用者名稱連結
					$joindata["username"] = $tool->ShowMemberLink( $joindata["uid"], $joindata["username"]);

					// 活動詳細資料
					$joindata["detail"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=canceldetail&amp;serial=%d\">檢視</a>", $joindata["serial"]);

					array_push($act_canceldata, $joindata);
				}

				unset($join_tmp);

				// 活動取消審核
				$sql  = "SELECT `id`, `apply_time`, `result` ";
				$sql .= "FROM `cancelapply` WHERE `aid` = '$aid' ";
				$sql .= "ORDER BY `apply_time` ASC";
				$linkmysql->query($sql);

				$managedata['cancelapply'] = "";

				while ($reviewdata = mysql_fetch_array($linkmysql->listmysql))
				{
					if ($reviewdata["result"] == "Pass") {
						$reviewdata["result"] = "<font color=\"green\">通過</font>";
					} else if ($reviewdata["result"] == "Refuse") {
						$reviewdata["result"] = "<font color=\"red\">拒絕</font>";
					} else {
						$reviewdata["result"] = "<font color=\"blue\">尚未審核</font>";
					}

					$managedata['cancelapply'] .= sprintf("提出時間: %s %s <a href=\"index.php?act=activitiesjoin&amp;sel=verifydetail&amp;id=%d\">%s</a><br/>",
						$reviewdata["apply_time"], $reviewdata["result"], $reviewdata["id"], "檢視");
				}

				if ($managedata['cancelapply'] == "")
				{
					$managedata['cancelapply'] = "--";
				}

				$tpl->assign("act_join", count($act_joindata));
				$tpl->assign("act_joindata", $act_joindata);
				$tpl->assign("act_cancel", count($act_canceldata));
				$tpl->assign("act_canceldata", $act_canceldata);
			}

			// EO名稱連結
			$data["username"] = $tool->ShowMemberLink( $data["ownerid"], $data["username"]);
			$data["status"] = $tool->ShowActStatus($data["status"]);

			///////////////////////////////////////////////////////////////////
			//			以上為活動資料及活動管理相關資料
			///////////////////////////////////////////////////////////////////

			// 若該會員有參加此活動，顯示資料
			$Isjoin = 0;

			$sql  = "SELECT `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, ";
			$sql .= "`aj`.`join_status`, `aj`.`attendance`, `aj`.`option2`, ";
			$sql .= "`aj`.`no`, `a`.`status`, `a`.`match_type`, `u`.`username`, ";
			$sql .= "`u`.`nickname`, `u`.`sex`, `u`.`birth_year` ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `aj`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";
			$linkmysql->query($sql);

			if ($myjoindata = mysql_fetch_array($linkmysql->listmysql))
			{
				$Isjoin = 1;

				// 報名連結
				$data["option"] = "<font color=\"blue\">已報名此活動</font>";

				// 年齡
				$myjoindata["age"] = date("Y") - $myjoindata["birth_year"];

				// 活動配對被選擇的編號
				$option2 = array();

				if ($managedata['use_match'] == 1)
				{
					if ($myjoindata["status"] != "CLOSE")
					{
						$myjoindata["option2"] = "若您在活動中有選擇會員，活動關閉後才看得到結果哦!";
					}
					else if ($myjoindata["status"] == "CLOSE")
					{
						if ($myjoindata["option2"] == "")
						{
							$myjoindata["option2"] = "很抱歉，沒有會員選擇您，若您在問卷中有選擇其他會員，請靜待佳音。";
						}
						else
						{
							$match_type_email = 0;
							$match_type_msn = 0;
							$match_type_tel = 0;

							$match_type = explode(",", $data["match_type"]);

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

							$option = explode(",", $myjoindata["option2"]);

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
					}
				}
				else
				{
					$myjoindata["option2"] = "本活動未使用活動配對功能!";
				}

				// 新版活動配對
				$myjoindata["fillout_match"] = "";
				$myjoindata["match_result"] = "<font color=\"gray\">檢視結果</font>";

				// 顯示新版活動配對選項
				if ($managedata['use_newmatch'] == 1)
				{
					if ($myjoindata["join_status"] == "join" &&	($myjoindata["status"] == "PROCEED" || $myjoindata["status"] == "CLOSE"))
					{
						$myjoindata["fillout_match"] = sprintf("<a href=\"./index.php?act=activitiesmatch&amp;sel=fillout_match&amp;aid=%d\"><img src=\"images/iF.jpg\" border=\"0\" alt=\"\"> >> 點選iF logo 輸入您的選擇</a>\n |", $aid);
						$myjoindata["match_result"] = sprintf("<a href=\"./index.php?act=activitiesmatch&amp;sel=match_result&amp;aid=%d\">檢視結果</a>", $aid);
					}
				}

				// 繳費方式及繳費狀態
				if ($myjoindata["charge_type"] == "iBon")
				{
					$sql  = "SELECT * FROM `charge_ibon` ";
					$sql .= "WHERE `charge_ibon_id` = '" . $myjoindata["charge_id"] ."' ";
					$linkmysql->query($sql);

					if ($ibondata = mysql_fetch_array($linkmysql->listmysql))
					{
						if (empty($ibondata["pay_time"]))
						{
							$ibondata["charge_status"] = "<font color=\"red\">未繳費</font>";
							$ibondata["pay_time"] = "--";
						}
						else
						{
							$ibondata["charge_status"] = "<font color=\"green\">已繳費</font>";
						}

						if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $ibondata["process_time"], $matche))
						{
							// 取出活動資料
							$sql  = "SELECT `act_date` FROM `activitie` WHERE `aid` = '$aid' ";
							$linkmysql->query($sql);
							
							list($act_date) = mysql_fetch_array($linkmysql->listmysql);
			
							$days = 7;
							$tmp = explode("-", $act_date);	
							
							// 計算繳費期限
							if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]))
							{				
								$ibondata["ibon_deadline"] = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
							}
							else
							{
								$ibondata["ibon_deadline"] = date("Y-m-d H:i:s", mktime($matche[4], $matche[5], 0, $matche[2], $matche[3]+$days, $matche[1]));
							}
						}

						$detail_link = sprintf("<a href=\"index.php?act=ibon&amp;sel=detail&amp;uid=%d&amp;aid=%d\">%s</a>", $uid, $aid, "詳細資料");

						$myjoindata["charge"]  = "繳費狀態 : " . $ibondata["charge_status"] . " (".$detail_link.")<br/>";
						$myjoindata["charge"] .= "繳費代碼 : <b>" . $ibondata["ibon_no"] . "</b><br/>";
						$myjoindata["charge"] .= "繳費金額 : <b>" . $ibondata["fees"] . "</b> 元<br/>";
						$myjoindata["charge"] .= "繳費期限 : " . $ibondata["ibon_deadline"] . "<br/>";
						$myjoindata["charge"] .= "繳費時間 : " . $ibondata["pay_time"] . "";
					}
					
					$myjoindata["charge_type"] = "FamiPort";
				}
				else if ($myjoindata["charge_type"] == "coupon")
				{
					$myjoindata["charge_type"]  = "<font color=\"blue\">優惠卷</font>";

					$myjoindata["charge"]  = $myjoindata["charge_type"];
					$myjoindata["charge"] .= sprintf("&nbsp;<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">%s</a>", $myjoindata["charge_id"], "詳細資料");
				}

				// 顯示會員活動參與狀態
				if ($myjoindata["join_status"] == "join")
				{
					$myjoindata["cancel"] = sprintf("<a href=\"activities.join.act.php?act=usercancel&amp;aid=%d\" onClick='return confirm(\"確定要取消報名活動?\")'>取消報名活動</a>", $aid);
					$myjoindata["join_status"] = "<font color=\"green\">已報名</font>";
				}
				else if ($myjoindata["join_status"] == "cancel")
				{
					$rejoin = sprintf("<a href=\"activities.join.act.php?act=rejoin&amp;aid=%s\" onClick='return confirm(\"確定要重新報名參加活動?\")'>重新報名參加</a>", $aid);
					$myjoindata["cancel"] = "已取消報名 / ". $rejoin;
					$myjoindata["join_status"] = "<font color=\"blue\">已取消報名</font>";
				}
				else if ($myjoindata["join_status"] == "EO_cancel")
				{
					$myjoindata["cancel"] = "無法再報名此活動。";
					$myjoindata["join_status"] = "<font color=\"red\">EO取消您的活動報名</font>";
				}
			}

			$join_tmp = array();
			$mycanceldata = array();

			// 該會員的取消活動紀錄
			$sql  = "SELECT `ac`.*, `u`.`username`, `u`.`nickname`, `u`.`sex` ";
			$sql .= "FROM `activitiecancel` ac ";
			$sql .= "LEFT JOIN `user` u ON `ac`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `ac`.`aid` = '$aid' AND `ac`.`uid` = '$uid' ";
			$sql .= "ORDER BY `ac`.`serial` ASC";
			$linkmysql->query($sql);

			while ($joindata = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($join_tmp, $joindata);
			}

			foreach ($join_tmp as $joindata)
			{
				if ($joindata["charge_type"] == "iBon")
				{
					if ($joindata["charge_status"] == "Paid")
					{
						$joindata["charge"] = "<font color=\"green\">已繳費</font>";
					}
					else
					{
						$joindata["charge"] = "<font color=\"red\">未繳費</font>";
					}
					$joindata["charge_type"] = "FamiPort";
				}
				else  if ($joindata["charge_type"] == "coupon")
				{
					$joindata["charge"] = "<font color=\"blue\">優惠卷</font>";
					$joindata["charge"] = sprintf("<a href=\"index.php?act=coupon&amp;sel=detail&amp;id=%d\">%s</a>", $joindata["charge_id"], $joindata["charge"]);
				}

				
				// 使用者名稱連結
				$joindata["username"] = $tool->ShowMemberLink( $joindata["uid"], $joindata["username"]);
				
				// 活動詳細資料
				$joindata["detail"] = sprintf("<a href=\"./index.php?act=activitiesjoin&amp;sel=canceldetail&amp;serial=%d\">檢視</a>", $joindata["serial"]);

				array_push($mycanceldata, $joindata);
			}

			unset($join_tmp);

			$linkmysql->close_mysql();

			// 簡訊系統剩餘點數
			$tpl->assign("sms_point", $iFSMS->query_point());
			$tpl->assign("member", $member);

			// 活動管理資料
			$tpl->assign("managedata", $managedata);

			// 活動資料
			$tpl->assign("activitiedata", $data);

			// 會員的報名相關資訊
			$tpl->assign("Isjoin", $Isjoin);
			$tpl->assign("myjoindata", $myjoindata);

			// 活動配對結果
			$tpl->assign("option2", $option2);
			$tpl->assign("option2_count", count($option2));

			// 會員的活動取消報名記錄
			$tpl->assign("mycancel", count($mycanceldata));
			$tpl->assign("mycanceldata", $mycanceldata);

			$tpl->assign("mainpage", "activities/activities.detail.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到活動資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
	else if ($_GET["sel"] == "modify")
	{
		//---------------------------------------
		// 修改活動 - EO修改用
		//---------------------------------------

		if ($_SESSION["login"] != 1)
		{
			$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
		}
		else
		{
			if ($_SESSION["authority"] != "Admin" && $_SESSION["authority"] != "EO")
			{
				$tool->ShowMsgPage("會員權限不足，無法修改活動資料");
			}
		}

		$aid = $_GET["aid"];

		$linkmysql->init();

		$sql  = "SELECT * FROM `activitie` WHERE `activitie`.`aid` = '$aid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["status"] != "OPEN")
			{
				$tool->ShowMsgPage("活動非開放狀態無法修改資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}

			if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data[1]) || $_SESSION["authority"] == "Admin")
			{
				// 時間
				$time = explode(":", $data["act_time"]);
				$act_time_hour = $time[0];
				$act_time_minute = $time[1];

				// 場地
				$sql = "SELECT `pid`, `placename` FROM `place` WHERE `pid` = '" . $data["place"] ."'";
				$linkmysql->query($sql);
				$placedata = mysql_fetch_array($linkmysql->listmysql);

				$data["place"] = sprintf("<a href=\"index.php?act=place&sel=detail&pid=%d\">%s</a>", $placedata["pid"], $placedata["placename"]);

				// 主題資料 下拉式選單
				$sql = "SELECT `tid`, `tname` FROM `topic`";
				$linkmysql->query($sql);

				$output_str = "";
				while ($selectdata = mysql_fetch_row($linkmysql->listmysql))
				{
					if ($data["topic"] == $selectdata[0]) {
						$output_str.= sprintf("<option value=\"%d\" selected>%s</option>\n", $selectdata[0], $selectdata[1]);
					} else {
						$output_str.= sprintf("<option value=\"%d\">%s</option>\n", $selectdata[0], $selectdata[1]);
					}
				}

				$data["topic"] = $output_str;

				// 族群資料 下拉式選單
				$sql = "SELECT `gid`, `gname` FROM `group`";
				$linkmysql->query($sql);

				$output_str = "";
				while ($selectdata = mysql_fetch_row($linkmysql->listmysql))
				{
					if ($data["group"] == $selectdata[0]) {
						$output_str.= sprintf("<option value=\"%d\" selected>%s</option>\n", $selectdata[0], $selectdata[1]);
					} else {
						$output_str.= sprintf("<option value=\"%d\">%s</option>\n", $selectdata[0], $selectdata[1]);
					}
				}

				$data["group"] = $output_str;

				// 解析人數與性別限制字串
				if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $data["people_limit"], $matche))
				{
					$data["sex_limit_type1"] = "checked";
					$data["male_limit"] = intval($matche[1]);
					$data["female_limit"] = intval($matche[2]);
				}
				else if (preg_match("/No limit total: (.+)\./" , $data["people_limit"], $matche))
				{
					$data["sex_limit_type2"] = "checked";
					$data["total_limit"] = intval($matche[1]);
				}
				else if (preg_match("/No limit./" , $data["people_limit"], $matche))
				{
					$data["sex_limit_type3"] = "checked";
				}


				// 解析年齡限制字串
				if (preg_match("/Age limit lb: (.+), ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$data["age_limit_type1"] = "checked";
					$data["age_lb"] = intval($matche[1]);
					$data["age_ub"] = intval($matche[2]);
				}
				else if (preg_match("/Age limit male_lb: (.+), male_ub: (.+), female_lb: (.+), female_ub: (.+)\./" , $data["age_limit"], $matche))
				{
					$data["age_limit_type2"] = "checked";
					$data["male_age_lb"] 	= intval($matche[1]);
					$data["male_age_ub"] 	= intval($matche[2]);
					$data["female_age_lb"] 	= intval($matche[3]);
					$data["female_age_ub"] 	= intval($matche[4]);
				}
				else if (preg_match("/No age limit\./" , $data["age_limit"], $matche))
				{
					$data["age_limit_type3"] = "checked";
				}

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

				// 是否可使用優惠折扣
				$data["use_discount"] = $data["use_discount"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";
				// 是否可使用優惠卷
				$data["use_coupon"] = $data["use_coupon"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";

				// 是否可使用活動配對
				$data["use_match"] = $data["use_match"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";
				// 是否可使用新版活動配對
				$data["use_newmatch"] = $data["use_newmatch"] == "YES" ? "<font color=\"green\">適用</font>" : "<font color=\"red\">不適用</font>";

				$linkmysql->close_mysql();

				$tpl->assign("act_time_hour", $act_time_hour);
				$tpl->assign("act_time_minute", $act_time_minute);
				$tpl->assign("activitiedata", $data);
				$tpl->assign("mainpage", "activities/activities.modify.html");
			}
			else
			{
				$tool->ShowMsgPage("您的權限不足無法修改活動資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料");
		}
	}
	else if ($_GET["sel"] == "modify2")
	{
		//---------------------------------------
		// 修改活動 - 管理者修改用
		//---------------------------------------

		if ($_SESSION["login"] != 1)
		{
			$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
		}
		else
		{
			if ($_SESSION["authority"] != "Admin")
			{
				$tool->ShowMsgPage("會員權限不足，無法修改活動資料");
			}
		}

		$linkmysql->init();
		$aid = $_GET["aid"];

		$sql  = "SELECT * FROM `activitie` WHERE `activitie`.`aid` = '$aid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			/*
			if ($data["status"] != "OPEN")
			{
				$tool->ShowMsgPage("活動非開放狀態無法修改資料", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
			*/

			// 活動時間
			$time = explode(":", $data["act_time"]);

			// 時
			$act_time_hour = "";
			for ($i = 0; $i < 24; $i++)
			{
				if ($i == $time[0]) {
					$act_time_hour .= sprintf("<option value=\"%02d\" selected>%02d</option>\n", $i, $i);
				} else {
					$act_time_hour .= sprintf("<option value=\"%02d\">%02d</option>\n", $i, $i);
				}
			}

			// 分
			$act_time_minute = "";
			for ($i = 0; $i < 60; $i+=5)
			{
				if ($i == $time[1]) {
					$act_time_minute .= sprintf("<option value=\"%02d\" selected>%02d</option>\n", $i, $i);
				} else {
					$act_time_minute .= sprintf("<option value=\"%02d\">%02d</option>\n", $i, $i);
				}
			}

			// 場地資料下拉式選單
			$sql = "SELECT `pid`, `placename`, `placecity` FROM `place` ORDER BY `placecity` ASC";
			$linkmysql->query($sql);

			$output_str = "";
			while ($selectdata = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($data["place"] == $selectdata["pid"])
				{
					$output_str .= sprintf("<option value=\"%d\" selected>[%s] %s</option>\n",
						$selectdata["pid"], $selectdata["placecity"], $selectdata["placename"]);
				}
				else
				{
					$output_str .= sprintf("<option value=\"%d\">[%s] %s</option>\n",
						$selectdata["pid"], $selectdata["placecity"], $selectdata["placename"]);
				}
			}

			$data["place"] = $output_str;

			// 主題資料 下拉式選單
			$sql = "SELECT `tid`, `tname` FROM `topic`";
			$linkmysql->query($sql);

			$output_str = "";
			while ($selectdata = mysql_fetch_row($linkmysql->listmysql))
			{
				if ($data["topic"] == $selectdata[0]) {
					$output_str.= sprintf("<option value=\"%d\" selected>%s</option>\n", $selectdata[0], $selectdata[1]);
				} else {
					$output_str.= sprintf("<option value=\"%d\">%s</option>\n", $selectdata[0], $selectdata[1]);
				}
			}

			$data["topic"] = $output_str;

			// 族群資料 下拉式選單
			$sql = "SELECT `gid`, `gname` FROM `group`";
			$linkmysql->query($sql);

			$output_str = "";
			while ($selectdata = mysql_fetch_row($linkmysql->listmysql))
			{
				if ($data["group"] == $selectdata[0]) {
					$output_str.= sprintf("<option value=\"%d\" selected>%s</option>\n", $selectdata[0], $selectdata[1]);
				} else {
					$output_str.= sprintf("<option value=\"%d\">%s</option>\n", $selectdata[0], $selectdata[1]);
				}
			}

			$data["group"] = $output_str;

			// 解析人數與性別限制字串
			if (preg_match("/Sex limit males: (.+), females: (.+)\./" , $data["people_limit"], $matche))
			{
				$data["sex_limit_type1"] = "checked";
				$data["male_limit"] = intval($matche[1]);
				$data["female_limit"] = intval($matche[2]);
			}
			else if (preg_match("/No limit total: (.+)\./" , $data["people_limit"], $matche))
			{
				$data["sex_limit_type2"] = "checked";
				$data["total_limit"] = intval($matche[1]);
			}
			else if (preg_match("/No limit./" , $data["people_limit"], $matche))
			{
				$data["sex_limit_type3"] = "checked";
			}

			// 解析年齡限制字串
			if (preg_match("/Age limit lb: (.+), ub: (.+)\./" , $data["age_limit"], $matche))
			{
				$data["age_limit_type1"] = "checked";
				$data["age_lb"] = intval($matche[1]);
				$data["age_ub"] = intval($matche[2]);
			}
			else if (preg_match("/Age limit male_lb: (.+), male_ub: (.+), female_lb: (.+), female_ub: (.+)\./" , $data["age_limit"], $matche))
			{
				$data["age_limit_type2"] = "checked";
				$data["male_age_lb"] 	= intval($matche[1]);
				$data["male_age_ub"] 	= intval($matche[2]);
				$data["female_age_lb"] 	= intval($matche[3]);
				$data["female_age_ub"] 	= intval($matche[4]);
			}
			else if (preg_match("/No age limit\./" , $data["age_limit"], $matche))
			{
				$data["age_limit_type3"] = "checked";
			}

			// 解析活動費用字串
			if (preg_match("/All: (.+)/", $data["charge"], $matche))
			{
				$data["charge_limit_type1"] = "checked";
				$data["charge"] = intval($matche[1]);
			}
			else if (preg_match("/Male: (.+), Female: (.+)/" , $data["charge"], $matche))
			{
				$data["charge_limit_type2"] = "checked";
				$data["charge"] = "";
				$data["male_charge"] = intval($matche[1]);
				$data["female_charge"] = intval($matche[2]);
			}
			else
			{
				$data["charge_limit_type1"] = "checked";
				$data["charge"] = $data["charge"];
			}

			// 是否可使用優惠折扣
			if ($data["use_discount"] == "YES")
			{
				$data["use_discount_YES"] = "checked";
			}
			else
			{
				$data["use_discount_NO"] = "checked";
			}

			// 是否可使用優惠卷
			if ($data["use_coupon"] == "YES")
			{
				$data["use_coupon_YES"] = "checked";
			}
			else
			{
				$data["use_coupon_NO"] = "checked";
			}

			// 是否可使用活動配對
			if ($data["use_match"] == "YES")
			{
				$data["use_match_YES"] = "checked";
			}
			else
			{
				$data["use_match_NO"] = "checked";
			}

			// 是否可使用新版活動配對
			if ($data["use_newmatch"] == "YES")
			{
				$data["use_newmatch_YES"] = "checked";
			}
			else
			{
				$data["use_newmatch_NO"] = "checked";
			}

			// 活動配對要給予的資料
			$match_type = explode(",", $data["match_type"]);

			if (is_array($match_type))
			{
				foreach ($match_type as $type)
				{
					if ($type == "email")
					{
						$data["match_type_email"] = 'checked';
					}
					else if ($type == "msn")
					{
						$data["match_type_msn"] = 'checked';
					}
					else if ($type == "tel")
					{
						$data["match_type_tel"] = 'checked';
					}
				}
			}

			// 取出所有EO權限以上的會員。
			$sql = "SELECT * FROM `user` WHERE `authority` IN ('EO', 'Admin') AND `uid` > 1";
			$linkmysql->query($sql);

			$output_str = "";
			while ($selectdata = mysql_fetch_array($linkmysql->listmysql))
			{
				if ($data["ownerid"] == $selectdata["uid"]) {
					$output_str .= sprintf("<option value=\"%d\" selected>%s (%s)</option>\n",
						$selectdata["uid"], $selectdata["username"], $selectdata["realname"]);
				} else {
					$output_str .= sprintf("<option value=\"%d\">%s (%s)</option>\n",
						$selectdata["uid"], $selectdata["username"], $selectdata["realname"]);
				}
			}

			$data["EO"] = $output_str;

			$data["status_key"] = array("OPEN", "PROCEED", "CLOSE", "APPLY_CANCEL", "CANCEL");
			$data["status_name"] = array("開放報名", "現正進行", "已經關閉", "申請取消", "已經取消");

			$output_str = "";
			for ($i = 0; $i < 5; $i++)
			{
				if ($data["status"] == $data["status_key"][$i])
				{
					$output_str .= sprintf("<option value=\"%s\" selected>%s</option>\n",
						$data["status_key"][$i], $data["status_name"][$i]);
				}
				else
				{
					$output_str .= sprintf("<option value=\"%s\">%s</option>\n",
						$data["status_key"][$i], $data["status_name"][$i]);
				}
			}

			$data["status"] = $output_str;

			// 重新統計活動人數的功能
			$data["ReCount"] = sprintf("<a href=\"./activities.act.php?act=ReCount&amp;aid=%d\"><b>重新統計</b></a>", $aid);

			$linkmysql->close_mysql();

			$tpl->assign("act_time_hour", $act_time_hour);
			$tpl->assign("act_time_minute", $act_time_minute);
			$tpl->assign("activitiedata", $data);
			$tpl->assign("mainpage", "activities/activities.modify2.html");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到該活動資料");
		}
	}
	else if ($_GET["sel"] == "myactivities")
	{
		//---------------------------------------
		// 我的活動記錄
		//---------------------------------------

		if ($_SESSION["login"] != 1)
		{
			$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
		}

		$linkmysql->init();

		if (isset($_GET["uid"]) && $_SESSION["authority"] == "Admin")
		{
			$uid = $_GET["uid"];
			$uid_suffix = sprintf("&amp;uid=%d", $uid);
		}
		else
		{
			$uid = $_SESSION["uid"];
			$uid_suffix = "";
		}

		$sql = "SELECT `username` FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);
		list($username) = mysql_fetch_row(($linkmysql->listmysql));

		$username_prefix = $username . "&nbsp;";

		if (!isset($_GET['type'])) {
			$type = "currjoin";
		} else {
			$type = $_GET['type'];
		}

		if ($type == "currjoin")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `aj`.`uid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'OPEN' OR `a`.`status` = 'PROCEED' OR `a`.`status` = 'APPLY_CANCEL')";
		}
		else if ($type == "pastjoin")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitiejoin` aj ";
			$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `aj`.`uid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'CLOSE' OR `a`.`status` = 'CANCLEL')";
		}
		else if ($type == "cancel")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitiecancel` ac ";
			$sql .= "LEFT JOIN `activitie` a ON `ac`.`aid` = `a`.`aid` ";
			$sql .= "LEFT JOIN `user` u ON `ac`.`uid` = `u`.`uid` ";
			$sql .= "WHERE `ac`.`uid` = '$uid' ";
			$sql .= "GROUP BY `a`.`aid`";
		}
		else if ($type == "currhold")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND (`status` = 'OPEN' OR `status` = 'PROCEED' OR `status` = 'APPLY_CANCEL')";
		}
		else if ($type == "pasthold")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND `status` = 'CLOSE'";
		}
		else if ($type == "cancelhold")
		{
			$sql  = "SELECT COUNT(*) ";
			$sql .= "FROM `activitie` ";
			$sql .= "WHERE `ownerid` = '$uid' ";
			$sql .= "AND `status` = 'CANCEL'";
		}

		$linkmysql->query($sql);

		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));

		// 分頁設定
		$itemperpage = 15;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );

		//---------------------------------------
		// 顯示活動記錄資料
		//---------------------------------------

		$sql  = "SELECT `aid`, `name`, `act_date`, `act_time`, `status` ";
		$sql .= "FROM `activitie` ";
		$sql .= "WHERE `activitie`.`status` = 'Open' ";
		$sql .= "LIMIT $head , $itemperpage";
		$linkmysql->query($sql);

		$option[0] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currjoin".$uid_suffix."\">已報名的活動</a> |\n";
		$option[1] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pastjoin".$uid_suffix."\">已參加的活動</a> |\n";
		$option[2] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancel".$uid_suffix."\">取消報名的活動</a> |\n";
		$option[3] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=currhold".$uid_suffix."\">待舉辦的活動</a> |\n";
		$option[4] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=pasthold".$uid_suffix."\">已完成的活動</a> |\n";
		$option[5] = "<a href=\"./index.php?act=activities&amp;sel=myactivities&amp;type=cancelhold".$uid_suffix."\">已取消的活動</a> \n";

		if ($type == "currjoin")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "WHERE `aj`.`uid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'OPEN' OR `a`.`status` = 'PROCEED' OR `a`.`status` = 'APPLY_CANCEL') ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ." 已報名的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=currjoin".$uid_suffix."";

			$option[0] = "<b>已報名的活動</b> |\n";
		}
		else if ($type == "pastjoin")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
			$sql .= "WHERE `aj`.`uid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'CLOSE' OR `a`.`status` = 'CANCLEL') ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ."已參加的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=pastjoin".$uid_suffix."";

			$option[1] = "<b>已參加的活動</b> |\n";
		}
		else if ($type == "cancel")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "LEFT JOIN `activitiecancel` ac  ON `ac`.`aid` = `a`.`aid` ";
			$sql .= "WHERE `ac`.`uid` = '$uid' ";
			$sql .= "GROUP BY `a`.`aid` ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ."取消報名的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=cancel".$uid_suffix."";

			$option[2] = "<b>取消報名的活動</b> |\n";
		}
		else if ($type == "currhold")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND (`a`.`status` = 'OPEN' OR `a`.`status` = 'PROCEED' OR `a`.`status` = 'APPLY_CANCEL') ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ."待舉辦的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=currhold".$uid_suffix."";

			$option[3] = "<b>待舉辦的活動</b> |\n";
		}
		else if ($type == "pasthold")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND `a`.`status` = 'CLOSE' ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ."已完成的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=pasthold".$uid_suffix."";

			$option[4] = "<b>已完成的活動</b> |\n";
		}
		else if ($type == "cancelhold")
		{
			$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`act_date`, `a`.`act_time`, ";
			$sql .= "`a`.`status`, `u`.`username`, `p`.`placecity`, `t`.`tname`  ";
			$sql .= "FROM `activitie` a ";
			$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
			$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
			$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
			$sql .= "WHERE `a`.`ownerid` = '$uid' ";
			$sql .= "AND `a`.`status` = 'CANCEL' ";
			$sql .= "ORDER BY `a`.`act_date` DESC ";
			$sql .= "LIMIT $head , $itemperpage";

			$list_title = $username_prefix ."已取消的活動";
			$url = "./index.php?act=activities&amp;sel=myactivities&amp;type=cancelhold".$uid_suffix."";

			$option[5] = "<b>已取消的活動</b>\n";
		}



		$linkmysql->query($sql);

		$activitielist = array();

		while ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if (preg_match("/(.+) (.+)/", $data["tname"], $matches))
			{
				$data["tname"]  = $matches[1];
				$data["tname"] .= "<br />";
				$data["tname"] .= $matches[2];
			}

			$data["name"] = $tool->UTF8_CuttingStr($data["name"], 48);
			$data["name"] = sprintf("<a href=\"index.php?act=activities&amp;sel=detail&amp;aid=%d\">%s</a>", $data["aid"], $data["name"]);

			$data["status"] = $tool->ShowActStatus($data["status"]);

			array_push($activitielist, $data);
		}

		$linkmysql->close_mysql();

		// 選單
		$options = $option[0] . $option[1] . $option[2] . $option[3] . $option[4] . $option[5];


		// 頁碼
		$page = $tool->showpages($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);

		// 跳頁選單
		$totalpage = $tool->total_page($url, $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);

		$tpl->assign("options", $options);
		$tpl->assign("list_title", $list_title);
		$tpl->assign("list_count", count($activitielist));
		$tpl->assign("activitielist", $activitielist);
		$tpl->assign("mainpage", "activities/activities.my.html");
	}
	else if ($_GET["sel"] == "cancel")
	{
		if ($_SESSION["login"] != 1)
		{
			$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
		}

		//---------------------------------------
		// EO 取消活動申請
		//---------------------------------------

		$aid = $_GET["aid"];
		$uid = $_SESSION["uid"];

		$linkmysql->init();

		$sql  = "SELECT `a`.`aid`, `a`.`name`, `a`.`ownerid`, `u`.`username`, ";
		$sql .= "`a`.`act_date`, `a`.`males`, `a`.`females`, `a`.`status`";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "WHERE `a`.`aid` = '$aid'; ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			if ($data["status"] == "OPEN")
			{
				if (($_SESSION["authority"] == "EO" && $_SESSION["uid"] == $data["ownerid"]) || $_SESSION["authority"] == "Admin")
				{
					// 找出是否有申請取消參與且尚未審核的會員
					$sql  = "SELECT `serial` FROM `activitiejoin`  ";
					$sql .= "WHERE `aid` = '$aid' AND `join_status` = 'apply_cancel' ";
					$linkmysql->query($sql);

					if ($joindata = mysql_fetch_array($linkmysql->listmysql))
					{
						$linkmysql->close_mysql();
						$tool->ShowMsgPage("尚有申請取消參與的會員尚未審核，<br/>無法申請取消活動。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
					}

					$data["uid"] = $uid;
					$tpl->assign("activitiedata", $data);
					$tpl->assign("mainpage", "activities/activities.EOcancel.html");
				}
				else
				{
					$linkmysql->close_mysql();
					$tool->ShowMsgPage("權限不足。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
				}
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("活動不是開放狀態。", "回到活動詳情", "index.php?act=activities&sel=detail&aid=$aid");
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到活動資料", "回到活動列表", "index.php?act=activitielist");
		}
	}
	else
	{
		$tool->ShowMsgPage("Activities ERROR: unknown command.");
	}
?>