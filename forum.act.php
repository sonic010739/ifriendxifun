<?
	session_start();
	include "smarty.lib.php";
	
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	
	if ($_GET["act"] == "add" && isset($_SESSION["login"]))
	{
		//----------------------------------------
		// 新增文章
		//----------------------------------------
		
		$uid = $_POST["uid"];
		$topic = $_POST["topic"];
		$message = $_POST["message"];
				
		// open mysql connection
		$linkmysql->init();

		// insert new topic
		$sql = "INSERT INTO `forum` ( `fid`, `topic`, `reply`, `post_id`, `post_time`, `reply_Id`, `reply_time`, `top`, `display`) ";
		$sql .= "VALUES ( '' , '$topic ', '0', '$uid ', NOW() , '$uid ', NOW() , 'false', 'true')";
		$linkmysql->query( $sql );	
		
		// get the newest forum id
		$sql = "SELECT `fid` FROM `forum` ORDER BY `fid` DESC";
		$linkmysql->query( $sql );	
		
		// insert new forum
		list($fid) = mysql_fetch_row($linkmysql->listmysql);
		
		$sql = "INSERT INTO `forumdetail` ( `serial`, `fid`, `uid`, `message`, `modify`, `display`, `post_time`, `ip`) ";
		$sql .= "VALUES ( '', '$fid', '$uid', '$message', '', 'true', NOW(), '$ip');";
		$linkmysql->query($sql);	
		
		// close mysql connection
		$linkmysql->close_mysql();		
		$tool->URL("index.php?act=forum");
	}
	else if ($_GET["act"] == "reply" && isset($_SESSION["login"]))
	{
		//----------------------------------------
		// 回覆文章
		//----------------------------------------
		
		$uid = $_POST["uid"];
		$fid = $_POST["fid"];
		$message = $_POST["message"];
		
		// open mysql connection
		$linkmysql->init();
		
		$sql = "SELECT `reply` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($reply) = mysql_fetch_row($linkmysql->listmysql))
		{
			$reply++;
			
			$sql = "UPDATE `forum` SET `reply` = '$reply', `reply_id` = '$uid', `reply_time` = NOW()  WHERE `forum`.`fid` = '$fid' LIMIT 1";
			$linkmysql->query($sql);
			
			$sql = "INSERT INTO `forumdetail` ( `serial`, `fid`, `uid`, `message`, `modify`, `display`, `post_time`, `ip`) ";
			$sql .= "VALUES ( '', '$fid', '$uid', '$message', '', 'true', NOW(), '$ip');";
			$linkmysql->query($sql);	
			
			$linkmysql->close_mysql();	
			$tool->URL("index.php?act=forum&sel=view&fid=$fid");
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無法回覆不存在的文章", "回到討論區", "index.php?act=forum");
		}	
	}
	else if ($_GET["act"] == "modifymessage" && isset($_SESSION["login"]))
	{
		//----------------------------------------
		// 修改文章內容
		//----------------------------------------
		
		$uid = $_POST["uid"];
		$fdid = $_POST["fdid"];
		$message = $_POST["message"];
		
		$linkmysql->init();
		$sql = "SELECT `fid`, `uid`, `modify` FROM `forumdetail` WHERE `serial` = '$fdid '";
		$linkmysql->query($sql);
		
		if (list($fid, $fd_uid, $modify) = mysql_fetch_row(($linkmysql->listmysql)))
		{
			if ($fd_uid != $_SESSION["uid"] && $_SESSION["authority"] != "Admin")
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("權限不足無法進行此項操作");
			}
			else
			{
				// 加入修改者及時間的字串				
				$time = date("Y-m-d H:i:s");					
				$modify .= sprintf("\n本文章經&nbsp;%12s&nbsp;(%21s)於%19s修改<br>", $_SESSION["username"], $ip, $time );
				
				$sql = "UPDATE `forumdetail` SET `message`='$message', `modify`='$modify' WHERE `forumdetail`.`serial`='$fdid' LIMIT 1";
				$linkmysql->query($sql);
					
				$linkmysql->close_mysql();	
				$tool->URL("index.php?act=forum&sel=view&fid=$fid");
			}
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法修改不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "Del" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 刪除文章
		//----------------------------------------
		
		$fid = $_GET["fid"];
		
		$linkmysql->init();
		
		$sql = "SELECT `fid` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($del_fid) = mysql_fetch_row($linkmysql->listmysql))
		{
			$top = "true";
			
			$sql = sprintf("DELETE FROM `forum` WHERE `forum`.`fid` = '%d' LIMIT 1;", $del_fid);							
			$linkmysql->query($sql);			
			
			$delfdids = array();
			$index = 0;
			
			$sql = sprintf("SELECT `serial` FROM `forumdetail` WHERE `forumdetail`.`fid` = '%d'", $del_fid);
			$linkmysql->query($sql);
			
			while ($data = mysql_fetch_row($linkmysql->listmysql))
			{
				array_push($delfdids, $data);
			}
			
			foreach ($delfdids as $delid)
			{
				$sql = sprintf("DELETE FROM `forumdetail` WHERE `forumdetail`.`serial` = %d LIMIT 1;",$delid[0]);							
				$linkmysql->query($sql);
			}
			
			$linkmysql->close_mysql();		
			$tool->URL("index.php?act=forum");
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法刪除不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "top" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 設為置頂
		//----------------------------------------
		
		$fid = $_GET["fid"];

		$linkmysql->init();
		
		$sql = "SELECT `top` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($top) = mysql_fetch_row($linkmysql->listmysql))
		{
			$top = "true";
			
			$sql = "UPDATE `forum` SET `top` = '$top'  WHERE `forum`.`fid` = '$fid' LIMIT 1";
			$linkmysql->query($sql);			
			
			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章為置頂");
		}
	}
	else if ($_GET["act"] == "down" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 置頂取消
		//----------------------------------------
		
		$fid = $_GET["fid"];

		$linkmysql->init();
		
		$sql = "SELECT `top` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($top) = mysql_fetch_row($linkmysql->listmysql))
		{
			$top = "false";
			
			$sql = "UPDATE `forum` SET `top` = '$top'  WHERE `forum`.`fid` = '$fid' LIMIT 1";
			$linkmysql->query($sql);			
			
			$linkmysql->close_mysql();			
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章為解除置頂", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "hidden" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 設為隱藏
		//----------------------------------------
		
		$fid = $_GET["fid"];
	
		$linkmysql->init();
		
		$sql = "SELECT `display` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($display) = mysql_fetch_row($linkmysql->listmysql))
		{
			$display = "false";
			
			$sql = "UPDATE `forum` SET `display` = '$display'  WHERE `forum`.`fid` = '$fid' LIMIT 1";
			$linkmysql->query($sql);			

			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "display" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 取消隱藏
		//----------------------------------------
		
		$fid = $_GET["fid"];
		
		$linkmysql->init();
		
		$sql = "SELECT `display` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);
			
		if (list($display) = mysql_fetch_row($linkmysql->listmysql))
		{
			$display = "true";
			
			$sql = "UPDATE `forum` SET `display` = '$display'  WHERE `forum`.`fid` = '$fid' LIMIT 1";
			$linkmysql->query($sql);			
			
			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "fd_hidden" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 隱藏文章內容
		//----------------------------------------
		
		$serial = $_GET["fdid"];
	
		$linkmysql->init();
		
		$sql = "SELECT `display` FROM `forumdetail` WHERE `forumdetail`.`serial` = '$serial'";
		$linkmysql->query($sql);
			
		if (list($display) = mysql_fetch_row($linkmysql->listmysql))
		{
			$display = "false";
			
			$sql = "UPDATE `forumdetail` SET `display` = '$display'  WHERE `forumdetail`.`serial` = '$serial' LIMIT 1";
			$linkmysql->query($sql);			
			
			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章", "回到討論區", "index.php?act=forum");
		}
		
	}
	else if ($_GET["act"] == "fd_display" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 顯示文章內容
		//----------------------------------------
		
		$serial = $_GET["fdid"];
		
		$linkmysql->init();
		
		$sql = "SELECT `display` FROM `forumdetail` WHERE `forumdetail`.`serial` = '$serial'";
		$linkmysql->query($sql);
			
		if (list($display) = mysql_fetch_row($linkmysql->listmysql))
		{
			$display = "true";
			
			$sql = "UPDATE `forumdetail` SET `display` = '$display'  WHERE `forumdetail`.`serial` = '$serial' LIMIT 1";
			$linkmysql->query($sql);			
			
			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			$linkmysql->close_mysql();		
			$tool->ShowMsgPage("無法設定不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else if ($_GET["act"] == "fd_del" && $_SESSION["authority"] == "Admin")
	{
		//----------------------------------------
		// 刪除文章內容
		//----------------------------------------
		
		$serial = $_GET["fdid"];
				
		$linkmysql->init();
		
		$sql = sprintf("SELECT `serial`, `fid` FROM `forumdetail` WHERE `forumdetail`.`serial` = '%d'", $serial);
		$linkmysql->query($sql);
			
		if (list($del_serial, $fid) = mysql_fetch_row($linkmysql->listmysql))
		{			
			// 刪除文章
			$sql = sprintf("DELETE FROM `forumdetail` WHERE `forumdetail`.`serial` = %d LIMIT 1", $del_serial);							
			$linkmysql->query($sql);
			
			// 取出文章回覆數
			$sql = "SELECT `reply` FROM `forum` WHERE `forum`.`fid` = '$fid'";
			$linkmysql->query($sql);
			list($reply) = mysql_fetch_row($linkmysql->listmysql);
			
			// 文章回覆數 - 1
			$reply--;
			
			// 取出最後回覆的id和時間
			$sql = sprintf("SELECT `uid`, `post_time` FROM `forumdetail` WHERE `forumdetail`.`fid` = '%d' ORDER BY `serial` DESC", $fid);
			$linkmysql->query($sql);
			list($reply_id, $reply_time) = mysql_fetch_row($linkmysql->listmysql);
			
			// 更新文章的回覆數、最後回覆id、最後回覆時間
			$sql = "UPDATE `forum` SET `reply`='$reply', `reply_id`='$reply_id', `reply_time`='$reply_time'  WHERE `forum`.`fid`='$fid' LIMIT 1";
			$linkmysql->query($sql);			
			
			// close mysql connection
			$linkmysql->close_mysql();		
			$tool->URL($_SERVER['HTTP_REFERER']);
		}
		else
		{
			// close mysql connection
			$linkmysql->close_mysql();			
			$tool->ShowMsgPage("無法刪除不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
?>