<?
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	require "smarty.lib.php";

	$linkmysql->init();

	$time_start = microtime_float();

	// ���X��ú�O����iBon���
	$sql  = "SELECT * ";
	$sql .= "FROM `charge_ibon` ";
	$sql .= "WHERE `pay_time` IS NULL";
	$linkmysql->query($sql);

	$ibon_list = array();

	while ($data = mysql_fetch_array($linkmysql->listmysql))
	{
		if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/" , $data["process_time"], $matche))
		{
			$days = ($matche[4] + 3 >= 24) ? 6 : 7;
			$ibon_deadline = date("Y-m-d H:i:s", mktime($matche[4]+3, $matche[5]+3, 0, $matche[2], $matche[3]+$days, $matche[1]));

			// �Y�W�Lú�O������ú�O�A���B�z
			if (date("Y-m-d H:i:s") > $ibon_deadline)
			{
				array_push($ibon_list, $data);
			}
		}
	}

	foreach ($ibon_list as $ibon)
	{
		$uid = $ibon["uid"];
		$aid = $ibon["aid"];

		// ���X�|�������W���
		$sql  = "SELECT `a`.`name`, `a`.`act_date`, `a`.`status`, `a`.`males`, `a`.`females`, `a`.`use_coupon`, ";
		$sql .= "`aj`.`charge_type`, `aj`.`charge_id`, `aj`.`intro_id`, `aj`.`join_status`, `aj`.`join_time`, `u`.`sex` ";
		$sql .= "FROM `activitiejoin` aj ";
		$sql .= "LEFT JOIN `activitie` a ON `aj`.`aid` = `a`.`aid` ";
		$sql .= "LEFT JOIN `user` u ON `aj`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `a`.`aid` = '$aid' AND `aj`.`uid` = '$uid'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			// ��s�Ӭ��ʪ��k�k�ѻP�H��
			if ($data["sex"] == "�k")
			{
				$males = --$data["males"];
				$sql = "UPDATE `activitie` SET `males` = '$males' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}
			else if ($data["sex"] == "�k")
			{
				$females = --$data["females"];
				$sql = "UPDATE `activitie` SET `females` = '$females' WHERE `activitie`.`aid` ='$aid' LIMIT 1;";
			}

			$linkmysql->query($sql);

			// �ˬdú�O���A
			$charge_status = 'unPay';
			$ibon_number = $ibon["ibon_no"];
			$charge_type = $data["charge_type"];
			$charge_id = $data["charge_id"];
			$join_time = $data["join_time"];
			$reason = 'Your iBon code have been expired! System automatically cancel your registration.';
			$ip = '127.0.0.1';

			// �N���W��Ʃ�J���W���ʨ����O����
			$sql  = "INSERT INTO `activitiecancel` ( ";
			$sql .= "`serial`, `aid`, `uid`, `charge_type`, `charge_id`, `ibon_no`, `charge_status`, ";
			$sql .= "`join_time`, `cancel_time`, `cancel_by`, `cancel_ip`, `cancel_reason` ";
			$sql .= ") VALUES ( ";
			$sql .= "NULL , '$aid', '$uid', '$charge_type', '$charge_id', '$ibon_number', '$charge_status', ";
			$sql .= "'$join_time', NOW(), '1', '$ip', '$reason');";
			$linkmysql->query($sql);

			// �R���|�������ʳ��W���
			$sql  = "DELETE FROM `activitiejoin` ";
			$sql .= "WHERE `aid` = '$aid' AND `uid` = '$uid' LIMIT 1;";
			$linkmysql->query($sql);

			// �R���|����ú�O���
			$sql = "DELETE FROM `charge_ibon` WHERE `charge_ibon_id` = '$charge_id' LIMIT 1;";
			$linkmysql->query($sql);

			if ($data['intro_id'] > 0)
			{
				// �N���иӦ�|�����|�����м� - 1
				$intro_id = $data['intro_id'];

				$sql  = "UPDATE `recommand` SET `count` = `count` - 1 ";
				$sql .= "WHERE `uid` = '$intro_id' LIMIT 1;";
				$linkmysql->query($sql);
			}

			// ���X�ӷ|���󦹬��ʱ��˪����
			$sql = "SELECT * FROM `introduction` WHERE `intro_uid` = '$uid' AND `intro_aid` = '$aid'";
			$linkmysql->query($sql);

			$intros = array();

			while ($introdata = mysql_fetch_array($linkmysql->listmysql))
			{
				array_push($intros, $introdata);
			}

			// �R���ӷ|���󦹬��ʱ��˪����
			foreach ($intros as $intro)
			{
				$intro_id = $intro['intro_id'];

				$sql  = "DELETE FROM `introduction` ";
				$sql .= "WHERE `intro_id` = '$intro_id' ";
				$linkmysql->query($sql);
			}

			unset($intros);

			echo "�w�������W�O�� �|���s��: $uid, ����: $aid, ú�O�N�� : " . $data['charge_id'] . "<br />\n";
		}
		else
		{
			echo "�䤣�쬡�ʳ��W��� ���ʽs��: $aid <br />\n";
		}
	}

	$linkmysql->close_mysql();

	$time_end = microtime_float();
	$time = $time_end - $time_start;

	$fp = fopen ("/var/www/vhosts/ifriendxifun.net/httpdocs/log/Act_AutoCancel_log.txt", "a+");
	fwrite($fp, date("Y-m-d H:i:s") . "�۰ʨ����W�Lú�O�����B��ú�O���|�������W�O�� in $time seconds\n");
	fclose($fp);

	print date("Y-m-d H:i:s") . " �۰ʨ����W�Lú�O�����B��ú�O���|�������W�O�� in $time seconds\n";
?>

