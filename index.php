<?
	//----------------------------------------
	// iF 活動報名平台
	// www.iFriendxiFun.net
	// Author: Sonic <sonic010739@gmail.com>
	// 2009/08/19
	//----------------------------------------
	session_start();

	require "smarty.lib.php";
	include "includes/login.php";
	include "includes/index.inc.php";
	include "includes/menu.php";
	
	$linkmysql->init();
	
	$sql = "SELECT * FROM `layout` WHERE `serial` = '1';";
	$linkmysql->query($sql);
	$layout = mysql_fetch_array(($linkmysql->listmysql));

	$linkmysql->close_mysql();
	
	$tpl->assign("layout", $layout);
	$tpl->assign("base_url", $config["base_url"]);
	$tpl->assign("gmap_key", $config["gmap_key"]);	
	
	$tpl->display("index.html");
?>