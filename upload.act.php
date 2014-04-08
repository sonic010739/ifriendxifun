<?
	session_start();
	include "smarty.lib.php";
	
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
	
	if ($_GET['act'] == 'upload')
	{
		$upload_dir = "upload/";
		
		$new_file = $_FILES['image'];		
		$file_name = $new_file['name'];
		$file_tmp = $new_file['tmp_name'];
		$file_size = $new_file['size'];

		// 若有上傳檔，則取出該檔案的副檔名
		$ext = strrchr($file_name, '.');
		$limited_ext = array(".gif",	".jpg",	".jpeg", ".png", ".bmp");
		
		// 檢查上傳的檔案類型
		if (!in_array(strtolower($ext), $limited_ext))
		{
			@unlink($file_tmp);
			$tool->ShowMsgPage("系統不支援您上傳的檔案類型", '回到上傳檔案頁面', 'index.php?act=upload');
		}

		// 檢查上傳的檔案大小
		$size_bytes = 1024*1024;
		
		if ($file_size > $size_bytes)
		{
			@unlink($file_tmp);
			$tool->ShowMsgPage("您上傳的檔案大小為". ceil($file_size/1024) ."KB，大於1024KB!", '回到上傳檔案頁面', 'index.php?act=upload' );			
		}

		$linkmysql->init();

		// 檢查是否有相同的檔案名稱
		$sql  = "SELECT `serial` FROM `images` WHERE `filename` = '". $new_file['name'] ."' ";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array($linkmysql->listmysql))
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("檔案名稱已存在，請使用其他的檔案名稱", '回到上傳檔案頁面', 'index.php?act=upload');
		}
		
		// 新增至資料庫
		$sql  = "INSERT INTO `images` (`serial`, `filename`, `upload_time`) ";
		$sql .= "VALUES ('', '". $new_file['name']."', NOW()); ";
		
		if ($linkmysql->query($sql))
		{
			//$new_file['name'] = iconv("UTF-8", "big5", $new_file['name']);
			
			if (move_uploaded_file($file_tmp, $upload_dir.$new_file['name']))
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage('檔案上傳完成!', '回到上傳檔案頁面', 'index.php?act=upload');
			}
			else
			{
				$linkmysql->close_mysql();
				$tool->ShowMsgPage("無法上傳檔案", '回到上傳檔案頁面', 'index.php?act=upload');
			}
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("寫入資料庫失敗", '回到上傳檔案頁面', 'index.php?act=upload');
		}
	}
	else if ($_GET['act'] == 'del')
	{
		$serial = $_GET['serial'];
	
		$linkmysql->init();
		
		$sql = "SELECT * FROM `images` WHERE `serial` = '$serial'";
		$linkmysql->query($sql);

		if ($data = mysql_fetch_array(($linkmysql->listmysql)))
		{
			$data['filename'] = iconv("UTF-8", "big5", $data['filename']);
			@unlink('upload/' . $data['filename']);
			
			$sql = "DELETE FROM `images` WHERE `serial` = '$serial' LIMIT 1;";
			$linkmysql->query($sql);
			
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("檔案已刪除", '回到上傳檔案頁面', 'index.php?act=upload');
		}
		else
		{
			$linkmysql->close_mysql();
			$tool->ShowMsgPage("找不到所選擇的檔案", '回到上傳檔案頁面', 'index.php?act=upload');
		}
	}
	else
	{
		$tool->ShowMsgPage("Request Error!!");
	}
?>