<?
	//-------------------------------------------
	// 活動列表更新程式 新增 join_deadline 欄位
	// iF系統 第二次修改使用
	//-------------------------------------------

	include "smarty.lib.php";

	$linkmysql->init();

	//-------------------------------------------
	// 更新報名截止日期
	//-------------------------------------------
	
	$sql = "SELECT * FROM `activitie` ";
	$linkmysql->query($sql);

	$actlist = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		array_push($actlist, $data);
	}

	foreach($actlist as $act)
	{
		$aid = $act['aid'];

		$date = explode("-", $act["act_date"]);
		$deadline = date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2]-2, $date[0]));

		print 'Act_date:' . $act["act_date"] . '&nbsp;&nbsp; Deadline: '. $deadline . '<br />';

		$sql  = "UPDATE `activitie` SET ";
		$sql .= "`join_deadline` = '$deadline' ";
		$sql .= "WHERE `activitie`.`aid` = '$aid'";
		$linkmysql->query($sql);
	}

	//-------------------------------------------
	// 更新報名時間 join_time
	//-------------------------------------------
	
	$sql  = "SELECT `aj`.`aid`, `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, ";
	$sql .= "`aj`.`join_time` ";
	$sql .= "FROM `activitiejoin` aj ";
	$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
	$linkmysql->query($sql);

	$join_data = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		array_push( $join_data, $data);
	}

	foreach ($join_data as $joindata)
	{
		$aid = $joindata["aid"];
		$uid = $joindata["uid"];

		// 統計繳費方式及繳費狀態
		if ($joindata["charge_type"] == "iBon")
		{
			$sql = sprintf("SELECT `process_time` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $joindata["charge_id"]);
			$linkmysql->query($sql);
			list($join_time) = mysql_fetch_array($linkmysql->listmysql);
		}
		else if ($joindata["charge_type"] == "coupon")
		{
			$sql = sprintf("SELECT `use_time` FROM `coupon` WHERE `coupon_id` = '%s'", $joindata["charge_id"]);
			$linkmysql->query($sql);
			list($join_time) = mysql_fetch_array($linkmysql->listmysql);
		}

		if (empty($join_time))
		{
			$join_time = date("Y-m-d H:i:s");
		}

		$sql  = "UPDATE `activitiejoin` SET ";
		$sql .= "`join_time` = '$join_time' ";
		$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid'";
		$linkmysql->query($sql);
		//print $sql;
		//print "\n<br />";
	}
	
	//-------------------------------------------
	// 取消報名活動
	//-------------------------------------------
	
	$sql  = "SELECT `aj`.`aid`, `aj`.`uid`, `aj`.`charge_type`, `aj`.`charge_id`, ";
	$sql .= "`aj`.`join_time`, `aj`.`join_status` ";
	$sql .= "FROM `activitiejoin` aj ";
	$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
	$sql .= "WHERE `aj`.`join_status` != 'join' ";
	$linkmysql->query($sql);

	$join_data = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		array_push( $join_data, $data);
	}

	foreach ($join_data as $joindata)
	{
		$aid = $joindata["aid"];
		$uid = $joindata["uid"];

		$reason = '';
		$charge_status = '';
		$ibon_number = '';
		$charge_type = $joindata["charge_type"];
		$charge_id = $joindata["charge_id"];
		$join_time = $joindata["join_time"];

		if ($joindata["join_status"] == 'EO_cancel')
		{
			$reason = 'EO取消會員活動報名';
		}
		else if ($joindata["join_status"] == 'cancel')
		{
			$reason = '會員自行取消報名活動';
		}

		if ($joindata["charge_type"] == "iBon")
		{
			$sql = sprintf("SELECT `pay_time`, `ibon_no` FROM `charge_ibon` WHERE `charge_ibon_id` = '%s'", $joindata["charge_id"]);
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
			}

			$sql = sprintf("DELECT FROM `charge_ibon` WHERE `charge_ibon_id` = '%s' LIMIT 1;", $joindata["charge_id"]);
			$linkmysql->query($sql);
		}
		else if ($joindata["charge_type"] == "coupon")
		{
			$charge_status = 'Paid';
			$ibon_number = 0;
		}

		// 將報名資料放入報名活動取消記錄中
		$sql  = "INSERT INTO `activitiecancel` ( ";
		$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `ibon_no`, `charge_status`, ";
		$sql .= "`join_time`, `cancel_time`, `cancel_by`, `cancel_ip`, `cancel_reason` ";
		$sql .= ") VALUES ( ";
		$sql .= "NULL , '$aid', '$uid', '$charge_type', '$charge_id', '$ibon_number', '$charge_status', ";
		$sql .= "'$join_time', NOW(), '$uid', '$ip', '$reason' );";
		$linkmysql->query($sql);

		// 刪除會員的活動報名資料
		$sql  = "DELETE FROM `activitiejoin` ";
		$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1;";
		$linkmysql->query($sql);
	}
?>