<?php	
	date_default_timezone_set('Asia/Taipei');
	mb_internal_encoding("UTF-8");
	
	require "lib/smarty_lib/Smarty.class.php";
	require "lib/lib.php";
	require "config.php";
	require "lib/iFMailer.php";
	require "lib/iFSMSgo.php";
	
	$linkmysql = new phpMysql_h;	
	$linkmysql->ip = $config["mysql_ip"];
	$linkmysql->user = $config["mysql_user"];
	$linkmysql->password = $config["mysql_password"];
	$linkmysql->database = $config["mysql_database"];
	
    $tool = new tools_h;
	$iFMail = new iFMailer();
	$iFSMS = new iFSMSgo();
	
    define('__SITE_ROOT', '/var/www/vhosts/ifriendxifun.net/httpdocs');
	
    $tpl = new Smarty();
    $tpl->template_dir = __SITE_ROOT . "/templates/";
    $tpl->compile_dir = __SITE_ROOT . "/templates_c/";
    $tpl->config_dir = __SITE_ROOT . "/configs/";
    $tpl->cache_dir = __SITE_ROOT . "/cache/";
    $tpl->left_delimiter = '<{';
    $tpl->right_delimiter = '}>';
	
	if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
	{
		$temp_ip = split(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
		$ip = $temp_ip[0];
	}
	else
	{
		$ip = $_SERVER["REMOTE_ADDR"];  
	}
?>