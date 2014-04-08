<?
	if ($_SESSION["login"] != 1)
	{
		$tool->ShowMsgPage("請先登入", "註冊帳號", "index.php?act=register");
	}
	else
	{
		if ($_SESSION["authority"] != "Admin")
		{
			$tool->ShowMsgPage("您的會員權限不足，無法檢視此頁面");
		}
	}

	$linkmysql->init();

	$sql = "SELECT * FROM `images` ORDER BY `upload_time` DESC;";
	$linkmysql->query($sql);

	$images = array();

	while ($data = mysql_fetch_array(($linkmysql->listmysql)))
	{
		$data['link'] = $config["base_url"]. 'upload/' . $data['filename'];
		$data['filename'] = 'upload/' . $data['filename'];

		array_push($images, $data);
	}

	$linkmysql->close_mysql();

	$tpl->assign("images", $images);
	$tpl->assign("mainpage","upload.html");
?>