<?
	set_time_limit(9999);
	
	require "smarty.lib.php";	
	
	/*
		gwsr	= 便利付單號
		ibon_no	= 繳費代碼
		od_sob	= 您的單號
		amount	= 金額
		dt		= 時間 格式:YYYYMMDDHHIISS
	*/

	/*
	$gwsr = $_POST['gwsr'];
	$ibon_no = $_POST['ibon_no'];	
	$od_sob = $_POST['od_sob'];
	$amount = $_POST['amount'];
	$dt = $_POST['dt'];	
	
	$linkmysql->init();
	
	// 更新 ibon 繳費時間
	$sql  = "UPDATE `charge_ibon` SET ";
	$sql .= "`pay_time` = '$dt' ";
	$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' LIMIT 1;";
	$linkmysql->query($sql);

	// 從ibon中取得 uid and aid
	$sql  = "SELECT `uid`, `aid` ";
	$sql .= "FROM `charge_ibon` ";
	$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' LIMIT 1;";
	$linkmysql->query($sql);
	
	list($uid, $aid) = mysql_fetch_array($linkmysql->listmysql);
	
	$sql = "SELECT * FROM `recommand` WHERE `uid` = '$uid'";	
	$linkmysql->query($sql);
	
	// 檢查是否可以開始使用自己的專屬連結，如果還不能使用設定啟用
	if (!$recommand = mysql_fetch_array($linkmysql->listmysql))
	{
		$sql = "INSERT INTO `recommand` (`uid`, `count`, `coupons`) VALUES ( '$uid', '0', '0' );";
		$linkmysql->query($sql);
	}	
	*/
	
	$linkmysql->init();
	
	//$uid = 109;
	//$aid = 2;
		
	// 取出活動參與的紀錄
	$sql  = "SELECT `a`.`status`, `aj`.`intro_id`, `aj`.`join_status` ";
	$sql .= "FROM `activitie` a ";
	$sql .= "LEFT JOIN `activitiejoin` aj  ON `aj`.`aid` = `a`.`aid` ";
	$sql .= "WHERE `a`.`aid` = '$aid' AND `aj`.`uid` = '$uid' ";	
	$linkmysql->query($sql);	
	
	if ($data = mysql_fetch_array($linkmysql->listmysql))
	{	
		/*
		$give_id = 1; //system帳號的id
		
		//---------------------------------------------------------------------
		// 統計推薦的資料
		//---------------------------------------------------------------------
		
		
		$intro_id = $data["intro_id"];
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
			}			
		}
		*/

		// 取出會員資料
		$sql  = "SELECT * FROM `user` WHERE `uid` = '$uid'";
		$linkmysql->query($sql);
		$member = mysql_fetch_array($linkmysql->listmysql);
		
		// 取出活動資料
		$sql  = "SELECT `t`.`tname`, `a`.`act_date`, `a`.`name`, `p`.`placename`, `a`.`decription` ";
		$sql .= "FROM `activitie` a ";
		$sql .= "LEFT JOIN `user` u ON `a`.`ownerid` = `u`.`uid` ";
		$sql .= "LEFT JOIN `place` p ON `a`.`place` = `p`.`pid` ";
		$sql .= "LEFT JOIN `topic` t ON `a`.`topic` = `t`.`tid` ";
		$sql .= "LEFT JOIN `group` g ON `a`.`group` = `g`.`gid` ";
		$sql .= "WHERE `a`.`aid` = '$aid'";
		$linkmysql->query($sql);
		list($tname, $act_date, $name, $placename, $decription) = mysql_fetch_array($linkmysql->listmysql);
		
		// 活動詳情連結
		$act_link = sprintf("%sindex.php?act=activities&amp;sel=detail&amp;aid=%d", $config["base_url"], $aid);
		$act_link = sprintf("<a href=\"%s\">%s</a>", $act_link, $act_link);							
		
		// 會員專屬連結
		$member_link = sprintf("%sindex.php?id=%s",$config["base_url"], $member["username"]);
		$member_link = sprintf("<a href=\"%s\">%s</a>", $member_link, $member_link);
		
		// 取出該會員推薦的資料
		$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";			
		$linkmysql->query($sql);	

		// 寄出信件
		while ($introdata = mysql_fetch_array($linkmysql->listmysql))
		{
			$email = $introdata["intro_email"];
			$id = $introdata["intro_name"];
			
			// title
			$mailtitle = $member["realname"] . "邀你參加iF活動";
			
			// mail body
			$mailmessage  = "親愛的 ". $id . ":<br/>";
			$mailmessage .= "<br/>";
			$mailmessage .= "別說我沒報好康喔，我剛報名了iF的活動，這真是一個充滿歡樂的實體活動平台，<br/>";
			$mailmessage .= "裡面有好玩有趣的活動，不僅讓我工作繁忙之餘，享受多采多姿的生活，<br/>";
			$mailmessage .= "還讓我認識不同領域的朋友，大大的拓展交友圈，使我的人生充滿無限可能！<br/>";
			$mailmessage .= "下面就是我這次參加的活動喔！<br/>";
			$mailmessage .= "<br/>";
			$mailmessage .= "主題: ". $tname ."<br/>";
			$mailmessage .= "日期: ". $act_date ."<br/>";
			$mailmessage .= "名稱: ". $name ."<br/>";
			$mailmessage .= "地點: ". $placename ."<br/>";
			$mailmessage .= "活動敘述: ". nl2br($tool->AddLink2Text($decription)) ."<br/>";
			$mailmessage .= "活動詳情: ". $act_link ."<br/>";			
			$mailmessage .= "<br/>";
			$mailmessage .= "除此之外，在報名參加iF活動時置入我的專屬連結，就可以享有優惠，<br/>";
			$mailmessage .= "幫你省點荷包喔！我的專屬連結如下>><br/>";
			$mailmessage .= $member_link . "<br/>";
			$mailmessage .= "<br/>";
			$mailmessage .= "Have a Good Date!!<br/>";
			$mailmessage .= "<br/>";
			$mailmessage .= $member["realname"] . " <br/>";
			$mailmessage .= "_____________________________________________________________________<br/>";
			$mailmessage .= "此封信件是您的好友 " . $member["realname"] . " 透過iF系統寄送給您的私人訊息請勿回覆。<br/>";
			$mailmessage .= "iF 活動小組 iFiFriends@gmail.com<br/>";
			$mailmessage .= "如有任何問題請直接與我們連絡。<br/>";
			$mailmessage .= "<a href=\"". $config["base_url"] ."\">". $config["base_url"] ."</a><br/>";
						
			if ($email != "qkaduncan@msn.com") {
				$tool->SendMail($email, $id, $mailtitle, $mailmessage, '');
			}
		}
					
		if ($data["status"] == "CANCEL")
		{
			/*
			// 1. 檢查活動的狀態，若已經取消需給予優惠卷
			$reason = "EO已經取消活動，使用iBon繳費完成的會員給予活動抵用卷。";
			
			$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
			$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
			$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
			$sql .= "'$reason', NOW() , NULL , NULL ); ";
			$linkmysql->query($sql);
			*/
		}
		else if ($data["join_status"] == "EO_cancel")
		{
			// 2. 檢查參加的狀態。若被EO強制取消則需給予優惠卷			
			$reason = "EO強制取消參加活動，已經使用超商代碼繳費的會員給予活動優惠卷。";
			
			$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
			$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
			$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
			$sql .= "'$reason', NOW() , NULL , NULL ); ";
			$linkmysql->query($sql);
		}
		else if ($data["join_status"] == "cancel")
		{
			/*
			// 3. 檢查參加的狀態，若為cancel需給予優惠卷
			$reason = "使用者取消參與活動，<br/>使用iBon繳費的會員給予活動抵用卷。";
			
			$sql  = "INSERT INTO `coupon` ( `coupon_id`, `coupon_type`, `uid`, `give_id`, ";
			$sql .= "`reason`, `give_time`, `use_time`, `use_act` ) ";
			$sql .= "VALUES ( NULL , '活動抵用卷', '$uid', '$give_id', ";
			$sql .= "'$reason', NOW() , NULL , NULL ); ";
			$linkmysql->query($sql);
			*/
		}
	}
	
	$linkmysql->close_mysql();
	
	// TODO:
	// 寄送已付款的訊息。
?>