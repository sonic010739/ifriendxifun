<?
	$tpl->assign("msg", $_GET['msg']);
	$tpl->assign("name", $_GET['n']);
	$tpl->assign("link", htmlspecialchars($_GET['l']));
	$tpl->assign("mainpage","msg.html");
?>