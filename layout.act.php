<?
	session_start();
	
	include "smarty.lib.php";
	
	$scroll_text 	= $_POST['scroll_text'];
	
	$left_1_image 	= $_POST['left_1_image'];
	$left_1_link	= $_POST['left_1_link'];
	$left_2_image 	= $_POST['left_2_image'];
	$left_2_link	= $_POST['left_2_link'];
	$left_3_image 	= $_POST['left_3_image'];
	$left_3_link	= $_POST['left_3_link'];
	
	$right_1_image 	= $_POST['right_1_image'];
	$right_1_link	= $_POST['right_1_link'];
	$right_2_image 	= $_POST['right_2_image'];
	$right_2_link	= $_POST['right_2_link'];
	$right_3_image 	= $_POST['right_3_image'];
	$right_3_link	= $_POST['right_3_link'];
	$right_4_image 	= $_POST['right_4_image'];
	$right_4_link	= $_POST['right_4_link'];
	$right_5_image 	= $_POST['right_5_image'];
	$right_5_link	= $_POST['right_5_link'];
	
	$linkmysql->init();
	
	$sql  = "UPDATE `layout` SET ";
	$sql .= "`scroll_text` = '$scroll_text', ";
	$sql .= "`left_1_image` = '$left_1_image', ";
	$sql .= "`left_1_link` = '$left_1_link', ";
	$sql .= "`left_2_image` = '$left_2_image', ";
	$sql .= "`left_2_link` = '$left_2_link', ";
	$sql .= "`left_3_image` = '$left_3_image', ";
	$sql .= "`left_3_link` = '$left_3_link', ";
	$sql .= "`right_1_image` = '$right_1_image', ";
	$sql .= "`right_1_link` = '$right_1_link', ";
	$sql .= "`right_2_image` = '$right_2_image', ";
	$sql .= "`right_2_link` = '$right_2_link', ";
	$sql .= "`right_3_image` = '$right_3_image', ";
	$sql .= "`right_3_link` = '$right_3_link', ";
	$sql .= "`right_4_image` = '$right_4_image', ";
	$sql .= "`right_4_link` = '$right_4_link', ";
	$sql .= "`right_5_image` = '$right_5_image', ";
	$sql .= "`right_5_link` = '$right_5_link' ";	
	$sql .= "WHERE `serial` = '1'; ";		
	
	$linkmysql->query($sql);
	
	$linkmysql->close_mysql();
	
	$tool->URL("index.php?act=layout");

?>