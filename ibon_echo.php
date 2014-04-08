<?
	// iBon echo
	session_start();
	require "smarty.lib.php";	
	
	/*
		結果:  			succ 			int 	1	1[成功] 0[失敗] 
		ibon 繳費代碼:  ibon_no 		char	16 	去門市繳費要用的號碼 
		單號:  			gwsr 			int 	10 	系統交易單號 
		處理日期:  		process_date 	int 	8 	日期: yyyymmdd 
		處理時間: 		process_time 	int 	6 	時間: hhmmss 
		自訂單號:  		od_sob 			char 	30 	您的購物單號 or keyword 最好是您系統的唯一值 
		金額:  			amount 			int 	5 	金額 
		自定欄位:  		od_hoho 		char 	500 交易的內容(店家的自訂欄位,統包在此變數回傳) 
	*/

	$succ 			= $_POST['succ'];
	//$ibon_no 		= $_POST['ibon_no'];
	$ibon_no 		= $_POST['tspay_no'];	
	$gwsr 			= $_POST['gwsr'];
	$process_date 	= $_POST['process_date'];
	$process_time 	= $_POST['process_time'];
	$od_sob 		= $_POST['od_sob'];
	$amount 		= $_POST['amount'];
	$store 			= $_POST['store'];
	$od_hoho 		= $_POST['od_hoho'];
	
	// process_time 處理
	$year = substr($process_date, 0, 4);
	$month = substr($process_date, 4, 2);
	$day = substr($process_date, 6, 2);
	$hour = substr($process_time, 0, 2);
	$minute = substr($process_time, 2, 2);
	$second = substr($process_time, 4, 2);		
	$process_time = date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
	
	if ($succ == 1)
	{	
		$linkmysql->init();
		
		// 從ibon資料中取得 process_time
		$sql  = "SELECT `process_time`, `aid` ";
		$sql .= "FROM `charge_ibon` ";
		$sql .= "WHERE `charge_ibon_id` = '$od_sob' LIMIT 1;";
		$linkmysql->query($sql);
		
		list($p_time_check, $aid) = mysql_fetch_array($linkmysql->listmysql);

		// 檢查目前存在資料庫中的 process_time 是否相同，避免重複執行此網頁
		if ($p_time_check == $process_time)
		{
			$tool->URL("index.php?act=activities&sel=detail&aid=$aid");
		}
		
		// 更新 ibon 訂單
		$sql  = "UPDATE `charge_ibon` SET ";
		$sql .= "`success` = '$succ', ";
		$sql .= "`ibon_no` = '$ibon_no', ";
		$sql .= "`gwsr` = '$gwsr', ";
		$sql .= "`process_time` = '$process_time' ";
		$sql .= "WHERE `charge_ibon`.`charge_ibon_id` = '$od_sob' LIMIT 1 ; ";
		$linkmysql->query($sql);
		
		// 從ibon資料中取得 uid and aid
		$sql  = "SELECT `uid`, `aid`, `ibon_no`, `process_time` ";
		$sql .= "FROM `charge_ibon` ";
		$sql .= "WHERE `charge_ibon_id` = '$od_sob' AND `gwsr` = '$gwsr' LIMIT 1;";
		$linkmysql->query($sql);
		
		list($uid, $aid, $ibon_no, $process_time) = mysql_fetch_array($linkmysql->listmysql);		
		
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
		
		$linkmysql->close_mysql();
		
		// iF繳費截止日
		$tmp = explode("-", $act_date);
		$iFDeadline = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));
		
		if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $process_time, $matche))
		{
			$days = ($matche[4] + 3 >= 24) ? 6 : 7;
		
			// 計算繳費期限:  $ibonDeadline: 信件用，$ibonDeadline2: 簡訊用
			if (mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]) < mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]))
			{				
				$ibonDeadline = date("Y-m-d H:i:s", mktime(23, 59, 59, $tmp[1], $tmp[2]-1, $tmp[0]));
				$ibonDeadline2 = date("Y/m/d", mktime(0, 0, 0, $tmp[1], $tmp[2]-1, $tmp[0]));
			}
			else
			{
				$ibonDeadline = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));
				$ibonDeadline2 = date("Y/m/d", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));			
			}
		}		
		
		// 寄送繳費提醒信。
		$mailinfo = array();
		$mailinfo["realname"] = $member["realname"];
		$mailinfo["act_name"] = $name;
		$mailinfo["act_date"] = $act_date;
		$mailinfo["act_time"] = $act_time;
		$mailinfo["act_place"] = $placename;
		$mailinfo["ibon_code"] = $ibon_no;
		$mailinfo["ibon_deadline"] = $ibonDeadline;
		$mailinfo["iF_deadline"] = $iFDeadline;
		
		$iFMail->PayDeadlineMailA($member["email"], $member["realname"], $mailinfo);
		
		$phone = explode("-", $member["tel"]);
		$member["tel"] = $phone[0].$phone[1].$phone[2];
			
		// 寄送繳費通知簡訊
		$iFSMS->iBonNotify($member["tel"], $mailinfo["ibon_code"], $ibonDeadline2);
		
		$_SESSION["ibon_code"] = $od_sob;
		$tool->URL("index.php?act=activitiesjoin&sel=iboncode");
	}
	else
	{
		$tool->ShowMsgPage("產生ibon碼錯誤請洽系統管理員");
	}
?>