<?
	$linkmysql->init();

	$sql = "SELECT * FROM `layout` WHERE `serial` = '1';";
	$linkmysql->query($sql);
	$layout = mysql_fetch_array(($linkmysql->listmysql));

	$linkmysql->close_mysql();

	$tpl->assign("layout", $layout);
	$tpl->assign("mainpage","layout.html");
?>