<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	
	if ($_SESSION["authority"] == "User")
	{
		$tool->ShowMsgPage("沒有權限可以觀看討論區");
	}
	
	if ($_GET['sel'] == "add")
	{		
		//----------------------------------------
		// 新增文章
		//----------------------------------------
		
		if (isset($_SESSION["uid"]))
		{
			$tpl->assign("uid", $_SESSION["uid"]);
			$tpl->assign("mainpage", "forum/forum.add.html");
		}
		else
		{
			$tool->ShowMsgPage("發表文章請先登入");
		}		
	}
	else if ($_GET['sel'] == "view")
	{
		//----------------------------------------
		// 檢視文章內容
		//----------------------------------------
		
		$fid = $_GET['fid'];
					
		// open mysql connection
		$linkmysql->init();
		
		$sql = "SELECT `display` FROM `forum` WHERE `forum`.`fid` = '$fid'";
		$linkmysql->query($sql);		
		list($display) = mysql_fetch_row(($linkmysql->listmysql));
		
		// 如果是隱藏文章，只有管理員才能觀看。
		if ($display == "false" && $_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("無法觀看隱藏文章", "回到討論區", "index.php?act=forum");
		}
		
		// 分頁設定
		$sql = "SELECT COUNT(*) FROM `forumdetail` WHERE `forumdetail`.`fid` = '$fid'";
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));		
		
		$itemperpage = 10;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );
		
		$sql  = "SELECT `f`.`fid`, `f`.`topic`, `fd`.`serial`, `fd`.`uid`, `u`.`username`, ";
		$sql .= "`fd`.`message`, `fd`.`modify`, `fd`.`display`, `fd`.`post_time`, `fd`.`ip` , `f`.`display` ";
		$sql .= "FROM `forum` f ";
		$sql .= "INNER JOIN `forumdetail` fd ON `f`.`fid` = `fd`.`fid` ";
		$sql .= "LEFT JOIN `user` u ON `fd`.`uid` = `u`.`uid` ";
		$sql .= "WHERE `f`.`fid` = '$fid' ";
		$sql .= "ORDER BY `fd`.`post_time` ASC ";
		$sql .= "LIMIT $head , $itemperpage";
		
		$linkmysql->query($sql);
		
		$forumdata = array();
		
		$index = $head + 1;
		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{							
			if ($data[4] == "") {
				$data[4] = "作者已被刪除";
			} else {
				$data[4] = $tool->ShowMemberLink( $data[3], $data[4]);
			}
			
			if ($data[7] == "false")
			{
				if ($_SESSION["authority"] != "Admin" && $_SESSION["uid"] != $data[3]) {
					$data[5] = "<font color=\"red\">隱藏文章</font>";
				} else {
					$data[5] = "<font color=\"red\">隱藏文章</font><br/>" . $data[5];
				}				
			} 
			
			if ($_SESSION["authority"] == "Admin")
			{
				// 顯示隱藏/開放前置碼，及隱藏/開放選項
				if ( $data[7] == "true") {
					$hidden = sprintf("<a href=\"./forum.act.php?act=fd_hidden&amp;fdid=%d\">隱藏</a>", $data[2]);
				} else {
					$hidden = sprintf("<a href=\"./forum.act.php?act=fd_display&amp;fdid=%d\">開放</a>", $data[2]);
				}
				
				$modify = sprintf("<a href=\"./index.php?act=forum&amp;sel=modify&amp;fdid=%d\">修改</a>", $data[2]);
				
				if ($index != 1) {
					$del = sprintf("<a href=\"./forum.act.php?act=fd_del&amp;fdid=%d\" onClick='return confirm(\"確定要刪除\")'>刪除</a>", $data[2]);	
				} else {
					$del = sprintf("<a href=\"#\" onClick='alert(\"首篇文章無法單獨刪除\")'>刪除</a>");	
				}
					
				
				$manage = sprintf("<font color=\"red\">文章選項</font>&nbsp;[%s,%s,%s]", $hidden, $modify, $del);
			}
			else if ($_SESSION["uid"] == $data[3])
			{
				$modify = sprintf("<a href=\"./index.php?act=forum&amp;sel=modify&amp;fdid=%d\">修改</a>", $data[2]);
				$manage = sprintf("<font color=\"red\">文章選項</font>&nbsp;[%s]", $hidden. $modify, $del);
			}
			
			$data[10] = $manage;
			$data[11] = $index++;
			$data[5] = nl2br($tool->AddLink2Text($data[5]));
			
			if ($data[6] != "")
			{
				$data[6] = "文章修改記錄<br>" . $data[6];
			}
			
			array_push($forumdata, $data);
		}
		
		$linkmysql->close_mysql();
		
		// 頁碼
		$page = $tool->showpages("index.php?act=forum&amp;sel=view&amp;fid=$fid", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page("index.php?act=forum&amp;sel=view&amp;fid=$fid", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("forumdata", $forumdata);
		$tpl->assign("mainpage", "forum/forum.view.html");
	}
	else if ($_GET['sel'] == "reply")
	{
		//----------------------------------------
		// 回復文章
		//----------------------------------------
		
		$fid = $_GET['fid'];		
		
		if (isset($_SESSION["uid"]))
		{
			// open mysql connection
			$linkmysql->init();
			
			$sql  = "SELECT `topic` FROM `forum` WHERE `forum`.`fid` = '$fid'";
			$linkmysql->query($sql);
			
			if (list($topic) = mysql_fetch_row($linkmysql->listmysql))
			{
				// close mysql connection
				$linkmysql->close_mysql();
		
				$tpl->assign("topic", $topic);
				$tpl->assign("fid", $fid);
				$tpl->assign("uid", $_SESSION["uid"]);
				$tpl->assign("mainpage", "forum/forum.reply.html");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("無法回覆不存在的文章", "回到討論區", "index.php?act=forum");
			}
		}
		else
		{
			$tool->ShowMsgPage("回復文章請先登入");
		}	
	}
	else if ($_GET['sel'] == "modify")
	{
		//----------------------------------------
		// 修改文章
		//----------------------------------------
		$fdid = $_GET['fdid'];	
		
		// open mysql connection
		$linkmysql->init();
		
		$sql = "SELECT `uid`, `message` FROM `forumdetail` WHERE `serial` = '$fdid '";
		$linkmysql->query($sql);		
		
		if (list($uid, $message) = mysql_fetch_row(($linkmysql->listmysql)))
		{
			if ($uid != $_SESSION["uid"] && $_SESSION["authority"] != "Admin")
			{
				// close mysql connection
				$linkmysql->close_mysql();		
				$tool->ShowMsgPage("權限不足無法進行此項操作", "回到討論區", "index.php?act=forum");
			}
			else
			{
				// close mysql connection
				$linkmysql->close_mysql();
		
				$tpl->assign("uid", $_SESSION["uid"]);
				$tpl->assign("fdid", $fdid);
				$tpl->assign("message", $message);
				$tpl->assign("mainpage", "forum/forum.modify.message.html");
			}
		}
		else
		{
			// close mysql connection
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("無法修改不存在的文章", "回到討論區", "index.php?act=forum");
		}
	}
	else
	{
		//----------------------------------------
		// 文章列表
		//----------------------------------------
		$linkmysql->init();
		
		$sql = "SELECT COUNT(*) FROM `forum`";
		
		if ($_SESSION["authority"] != "Admin")
		{
			$sql .= "WHERE `display` = 'true'";
		}
		
		// 分頁設定
		$linkmysql->query($sql);		
		list($pageinfo["count"]) = mysql_fetch_row(($linkmysql->listmysql));		
		
		$itemperpage = 15;
		$pageinfo["totalpage"] = ceil($pageinfo["count"] / $itemperpage);
		$pageinfo["nowpage"] = !isset($_GET["page"]) ? 1 : $_GET["page"];
		$head = 0 + $itemperpage * ( $pageinfo["nowpage"] - 1 );
		
		$forumdata = array();			
		
		// SQL 字串 由 `forum`  與 `user` join
		$sql  = "SELECT `f`.`fid`, `f`.`topic`, `f`.`reply`, `u1`.`uid`, `u1`.`username`, ";
		$sql .= "`f`.`post_time`, `u2`.`uid`, `u2`.`username`, `f`.`reply_time`, `f`.`top`, `f`.`display` ";
		$sql .= "FROM `forum` f ";
		$sql .= "LEFT JOIN `user` u1 ON `f`.`post_id` = `u1`.`uid` ";
		$sql .= "LEFT JOIN `user` u2 ON `f`.`reply_id` = `u2`.`uid` ";
		
		if ($_SESSION["authority"] != "Admin")
		{
			$sql .= "WHERE `f`.`display` = 'true'";
		}

		$sql .= "ORDER BY `f`.`top` ASC , `f`.`reply_time` DESC ";
		$sql .= "LIMIT $head , $itemperpage";
		
		$linkmysql->query($sql);
				
		while ($data = mysql_fetch_row($linkmysql->listmysql))
		{
			$myforumdata = array();
						
			if ($data[9] == "true" && $_SESSION["authority"] != "Admin") 
			{
				// 顯示置頂前置碼
				$data[1] = $tool->UTF8_CuttingStr($data[1], 36);
				$data[1] = sprintf("<span style=\"color: green; font-weight: bold;\">%s</span>", $data[1]);
				$data[1] = sprintf("<a href=\"./index.php?act=forum&amp;sel=view&amp;fid=%d\">%s</a>", $data[0], $data[1]);	
				
			}
			else if ($data[9] == "true" && $_SESSION["authority"] == "Admin")
			{
				// 顯示置頂前置碼，管理模式顯示文章顯示狀態
				$data[1] = $tool->UTF8_CuttingStr($data[1], 14);
				$data[1] = sprintf("<span style=\"color: green; font-weight: bold;\">%s</span>", $data[1]);
				$data[1] = sprintf("<a href=\"./index.php?act=forum&amp;sel=view&amp;fid=%d\">%s</a>", $data[0], $data[1]);	
				
				// 置頂/解除選項
				$top = sprintf("<a href=\"./forum.act.php?act=down&amp;fid=%d\">解除</a>", $data[0]);
			}
			else if ($data[9] != "true" && $_SESSION["authority"] == "Admin")
			{
				//管理模式顯示文章顯示狀態
				$data[1] = $tool->UTF8_CuttingStr($data[1], 14);
				$data[1] = sprintf("<a href=\"./index.php?act=forum&amp;sel=view&amp;fid=%d\">%s</a>", $data[0], $data[1]);
				
				// 置頂/解除選項
				$top = sprintf("<a href=\"./forum.act.php?act=top&amp;fid=%d\">置頂</a>", $data[0]);
			}
			else
			{
				// 無
				$data[1] = $tool->UTF8_CuttingStr($data[1], 36);
				$data[1] = sprintf("<a href=\"./index.php?act=forum&amp;sel=view&amp;fid=%d\">%s</a>", $data[0], $data[1]);
			}
						
			if ($_SESSION["authority"] == "Admin")
			{
				// 顯示隱藏/開放前置碼，及隱藏/開放選項
				if ( $data[10] == "true") 
				{
					$hidden = sprintf("<a href=\"./forum.act.php?act=hidden&amp;fid=%d\">隱藏</a>", $data[0]);
					$data[1] = sprintf("<font color=\"blue\">[開放]</font>&nbsp;%s", $data[1]);					
				}
				else
				{
					$hidden = sprintf("<a href=\"./forum.act.php?act=display&amp;fid=%d\">開放</a>", $data[0]);
					$data[1] = sprintf("<font color=\"red\">[隱藏]</font>&nbsp;%s", $data[1]);
				}
				
				$data[1] = sprintf("<span class=\"l1\">%s</span>\n", $data[1]);									
				$del = sprintf("<a href=\"./forum.act.php?act=Del&amp;fid=%d\" onClick='return confirm(\"確定要刪除\")'>刪除</a>", $data[0]);
								
				$data[1] .= sprintf("<span class=\"r1\">[%s,%s,%s]</span>", $hidden, $top, $del);
			}

			// 發表者ID及發表時間
			$data[4] = sprintf("%s<br/><a href=\"./index.php?act=member&amp;sel=detail&amp;uid=%d\">%s</a>", $data[5], $data[3], $data[4]);
			
			// 回覆者ID及回覆時間
			$data[7] = sprintf("%s<br/><a href=\"./index.php?act=member&amp;sel=detail&amp;uid=%d\">%s</a>", $data[8], $data[6], $data[7]);
			
			$myforumdata[0] = $data[0];
			$myforumdata[1] = $data[1];
			$myforumdata[2] = $data[2];
			$myforumdata[3] = $data[4];
			$myforumdata[4] = $data[7];
			
			array_push($forumdata, $myforumdata);
		}		
		
		// close mysql connection
		$linkmysql->close_mysql();
		
		// 頁碼
		$page = $tool->showpages("index.php?act=forum", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("page",$page);
		
		// 跳頁選單
		$totalpage = $tool->total_page("index.php?act=forum", $pageinfo["totalpage"], $pageinfo["nowpage"]);
		$tpl->assign("totalpage",$totalpage);
		
		$tpl->assign("forumdata", $forumdata);
		$tpl->assign("mainpage", "forum/forum.html");
	}
?>