<?
	require "smarty.lib.php";
	
	/*	
	綠界的檢查碼程式
	function gwSpcheck($s, $U) 
	{ 
		//算出認證用的字串
		$a = substr($U,0,1).substr($U,2,1).substr($U,4,1); //取出檢查碼的跳字組合 1,3,5 字元
		$b = substr($U,1,1).substr($U,3,1).substr($U,5,1); //取出檢查碼的跳字組合 2,4,6 字元
		$c = ( $s % $U ) + $s + $a + $b; //取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
		return $c; 
		//最大9碼輸出
	}
	
	$TOkSi = $process_time + $gwsr + $amount;
	$spcheck = gwSpcheck(商家檢查碼,	$TOkSi); //商店檢查碼,值	
	*/
	/*
		gwsr	= 便利付單號
		ibon_no	= 繳費代碼
		od_sob	= 您的單號
		amount	= 金額
		dt		= 時間 格式:YYYYMMDDHHIISS
		
		// 超商代收格式
		gwsr		=	便利付單號
		tspay_no	=	繳費代碼
		od_sob		=	您的單號
		amount		=	金額
		dt			=	日期時間 格式:YYYYMMDDHHIISS
		process_time=	處理時間
		spcheck		=	驗證碼 , 格式:數字
	*/

	$gwsr 			= $_POST['gwsr'];
	//$ibon_no 		= $_POST['ibon_no'];
	$ibon_no 		= $_POST['tspay_no'];
	$od_sob 		= $_POST['od_sob'];
	$amount 		= $_POST['amount'];
	$dt 			= $_POST['dt'];
	$process_time 	= $_POST['process_time'];
	$spcheck 		= $_POST['spcheck'];

	$linkmysql->init();

	$sql  = "SELECT * ";
	$sql .= "FROM `charge_ibon` ";
	$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' ";
	$linkmysql->query($sql);

	if ($ibon_data = mysql_fetch_array($linkmysql->listmysql))
	{
		// 更新 ibon 繳費時間
		$sql  = "UPDATE `charge_ibon` SET ";
		$sql .= "`pay_time` = '$dt' ";
		$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' LIMIT 1;";
		$linkmysql->query($sql);

		// 從ibon中取得 uid and aid
		$sql  = "SELECT `uid`, `aid`, `pay_time` ";
		$sql .= "FROM `charge_ibon` ";
		$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' LIMIT 1;";
		$linkmysql->query($sql);

		list($uid, $aid, $pay_time) = mysql_fetch_array($linkmysql->listmysql);

		// 檢查是否可以開始使用自己的專屬連結，如果還不能使用設定啟用
		$sql = "SELECT * FROM `recommand` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);

		if (!$recommand = mysql_fetch_array($linkmysql->listmysql))
		{
			$sql = "INSERT INTO `recommand` (`uid`, `count`, `coupons`) VALUES ( '$uid', '0', '0' );";
			$linkmysql->query($sql);
		}

		// 取出活動報名紀錄
		$sql  = "SELECT `a`.`status`, `aj`.`intro_id`, `aj`.`join_status` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "WHERE `a`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$give_id = 1; //system帳號的id

			//---------------------------------------------------------------------
			// 統計推薦的資料
			//---------------------------------------------------------------------

			$intro_id = $data["intro_id"];

			if ($intro_id != 0 && $data["intro_id"] != "")
			{
				$sql = "SELECT * FROM `recommand` WHERE `uid` = '$intro_id'";
				$linkmysql->query($sql);

				if ($recommand = mysql_fetch_array($linkmysql->listmysql))
				{
					$count = $recommand["count"] + 1;
					$coupons = $recommand["coupons"];

					$sql = "UPDATE `recommand` SET `count` = '$count' WHERE `uid` = '$intro_id' LIMIT 1 ;";
					$linkmysql->query($sql);

					if (($count/6) == ($coupons+1) && $count%6 == 0)
					{
						// 更新獲得的優惠卷數量
						$coupons++;
						$sql = "UPDATE `recommand` SET `coupons` = '$coupons' WHERE `uid` = '$intro_id' LIMIT 1 ;";
						$linkmysql->query($sql);

						// 推薦滿六個人繳費完成
						$reason = "推薦滿六個人參加活動，iF系統給予一張活動優惠卷。";

						$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
						$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
						$sql .= "VALUES ( NULL , '活動抵用卷', '$intro_id', '$give_id', ";
						$sql .= "'$reason', NOW() , NULL , NULL ); ";
						$linkmysql->query($sql);

						// 取出推薦的會員資料
						$sql  = "SELECT * FROM `user` WHERE `uid` = '$intro_id'";
						$linkmysql->query($sql);
						$member = mysql_fetch_array($linkmysql->listmysql);

						$mailinfo = array();
						$mailinfo["realname"] = $member["realname"];

						$iFMail->GetCouponMail($member["email"], $member["realname"], $mailinfo);
					}
				}
			}

			// 取出會員資料
			$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
			$linkmysql->query($sql);
			$member = mysql_fetch_array($linkmysql->listmysql);

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

			// 活動詳情連結
			$act_link = sprintf("%sindex.php?act=activities&amp;sel=detail&amp;aid=%d", $config["base_url"], $aid);
			$act_link = sprintf("<a href=\"%s\">%s</a>", $act_link, $act_link);

			// 會員專屬連結
			$member_link = sprintf("%sindex.php?id=%s",$config["base_url"], $member["username"]);
			$member_link = sprintf("<a href=\"%s\">%s</a>", $member_link, $member_link);

			// 取出該會員推薦的資料
			$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";
			$linkmysql->query($sql);

			// 寄出優惠邀請信
			while ($introdata = mysql_fetch_array($linkmysql->listmysql))
			{
				$mailinfo = array();
				$mailinfo["realname"] = $member["realname"];
				$mailinfo["intro_name"] = $introdata["intro_name"];
				$mailinfo["discount_link"] = $member_link;

				$mailinfo["act_topic"] = $tname;
				$mailinfo["act_date"] = $act_date;
				$mailinfo["act_name"] = $name;
				$mailinfo["act_place"] = $placename;
				$mailinfo["decription"] = nl2br($tool->AddLink2Text($decription));

				$iFMail->InviteMail($introdata["intro_email"], $introdata["intro_name"], $mailinfo);
			}

			// 寄送已付款的訊息。
			$mailinfo = array();
			$mailinfo["realname"] = $member["realname"];
			$mailinfo["act_date"] = $act_date;
			$mailinfo["act_time"] = $act_time;
			$mailinfo["act_topic"] = $tname;
			$mailinfo["act_name"] = $name;
			$mailinfo["ibon_code"] = $ibon_no;
			$mailinfo["ibon_paytime"] = $pay_time;
			$mailinfo["discount_link"] = $member_link;

			$iFMail->PaidMail($member["email"], $member["realname"], $mailinfo);

			$phone = explode("-", $member["tel"]);
			$member["tel"] = $phone[0].$phone[1].$phone[2];

			//寄送已付款簡訊
			$iFSMS->iBonPaid($member["tel"]);

			// 檢查對應的活動狀態
			if ($data["status"] == "CANCEL")
			{
				// 1. 檢查活動的狀態，若已經取消需給予優惠卷
				$reason = "EO已經取消活動，使用iBon繳費完成的會員給予活動抵用卷。";

				$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
				$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
				$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
				$sql .= "'$reason', NOW() , NULL , NULL ); ";
				$linkmysql->query($sql);
			}
		}
	}
	else
	{
		// 會員已取消報名活動，所以繳費資料不存在charge_ibon資料表中
		$sql  = "SELECT * ";
		$sql .= "FROM `activitiecancel` ";
		$sql .= "WHERE `ibon_no` = '$ibon_no'";		
		$linkmysql->query($sql);
		
		if ($cancel_data = mysql_fetch_array($linkmysql->listmysql))
		{
			$serial = $cancel_data['serial'];
			$uid = $cancel_data['uid'];
			$aid = $cancel_data['aid'];
			
			// 更新繳費狀態
			$sql  = "UPDATE `activitiecancel` SET ";
			$sql .= "`charge_status` = 'Paid' ";
			$sql .= "WHERE `serial` = '$serial' ";
			$linkmysql->query($sql);
			
			// 給予已取消卻收到繳費資料的會員優惠卷
			$reason = "已取消報名活動後收到繳費資料，使用iBon繳費完成的會員給予活動優惠卷。";

			$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
			$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
			$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '1', ";
			$sql .= "'$reason', NOW() , NULL , NULL ); ";
			$linkmysql->query($sql);
		}
	}

	$linkmysql->close_mysql();
?>