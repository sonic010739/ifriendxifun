<?
class phpMysql_h
{
	var $linkmysql 	= 0;		// MySQL 連線資訊
	var $ip			= "";		// MySQL 主機位置
	var $user 		= "";		// MySQL 帳號
	var $password 	= "";		// MySQL 密碼
	var $listmysql 	= "";		// MySQL 主機位置
	var $database 	= "";		// MySQL 資料庫名稱

	function link_mysql()
	{
		$this->linkmysql = @mysql_connect($this->ip, $this->user, $this->password);

		if (!$this->linkmysql)
		{
			echo ("Link Database Error!");
			exit;
		}
	}

	function close_mysql()
	{
		mysql_close($this->linkmysql);
	}

	function select_db()
	{
		$link_Isok = @mysql_select_db($this->database, $this->linkmysql);
		if (!$link_Isok)
		{
			echo ("Select Database Error");
			mysql_close($this->linkmysql);
			exit;
		}
	}

	function init()
	{
		$this->link_mysql();
		$this->select_db();

		$sql = "SET NAMES 'utf8'";
		$this->query($sql);
	}

	function query($str)
	{
		$this->listmysql = mysql_query($str,$this->linkmysql);
		return $this->listmysql;
	}

	function StartTransaction()
	{
		if (!$this->query("SET AUTOCOMMIT=0"))
		{
			die('Invalid query: ' . mysql_error());
		}

		if (!$this->query("START TRANSACTION"))
		{
			die('Invalid query: ' . mysql_error());
		}
	}

	function EndTransaction()
	{
		if (!$this->query("COMMIT"))
		{
			die('Invalid query: ' . mysql_error());
		}
	}

	function RollBack()
	{
		if (!$this->query("ROLLBACK"))
		{
			die('Invalid query: ' . mysql_error());
		}
	}

	function MysqlError()
	{
		die('Invalid query: ' . mysql_error());
	}

	function sql_safe($string)
	{
		if (get_magic_quotes_gpc())
		{
			$string = stripslashes($string);
		}

		$badWords = "(delete)|(update)|(union)|(insert)|(drop)|(http)|(--)";
		$string = eregi_replace($badWords, "", $string);

		if (phpversion() >= '4.3.0')
		{
			$string = mysql_real_escape_string($string);
		}
		else
		{
			$string = mysql_escape_string($string);
		}

		return $string;
	}
}

class tools_h
{
	function showmessage($str)
	{
		echo "<script  language=\"JavaScript\">";
		echo "alert('$str');";
		echo "</script>";
	}

	function submitURL($str)
	{
	    echo "<form name=\"submitfrm\"action=\"$str\" method=\"POST\">";
		echo "</form>";
		echo "<script  language=\"JavaScript\">";
		echo "submitfrm.submit();";
		echo "</script>";
	}

	function ShowMsgPage($message, $name='', $link='')
	{
		$message = urlencode($message);
		$name = urlencode($name);
		$link = urlencode($link);
		$this->URL("index.php?act=msg&msg=$message&n=$name&l=$link");
	}

	function URL($str)
	{
		header("Location:$str");
		die;
	}

	function addzero($str,$size)
	{
		$tstr="";
		$strl = strlen($str);
		$sum = $size - $strl;
		
		for ($i = 0; $i < $sum; $i++)
		{
			$tstr = $tstr."0";
		}
		
		$tstr = $tstr.$str;
		
		return $tstr;
	}

	function makedir($path)
	{
		mkdir($path,0777);
		$file = fopen($path."/check.tg","w");
		fclose($file);
	}

	function addslash($str)
	{
		$tstr = addslashes($str);
		return $tstr;
	}

	function stripslash($str)
	{
		$tstr = stripslashes($str);
		return $tstr;
	}

	// 自動轉換連結
	function AddLink2Text($str)
	{
		$str = preg_replace("#(http://[0-9-a-z.,_~8/!?=&%;]+)#i","<a href=\"\\1\" target=\"_blank\">\\1</a>", $str);
		$str = preg_replace("#(ftp://[0-9a-z._\/]+)#i","<a href=\"\\1\" target=\"_blank\">\\1</a>", $str);
		$str = preg_replace("#([0-9a-z._]+@[0-9a-z._?=]+)#i","<a href=\"mailto:\\1\">\\1</a>", $str);

	   return $str;
	}

	// 清除特殊字元
	function clearhtmltag( $str )
	{
		$str = preg_replace( "#<a(.*)>(.*)<\/a>#i" , "" , $str );
		$str = preg_replace( "#<table(.*)>(.*)<\/table>#i" , "" , $str );
		$str = preg_replace( "#<td(.*)>(.*)<\/td>#i" , "" , $str );
		$str = preg_replace( "#<tr(.*)>(.*)<\/tr>#i" , "" , $str );
		$str = preg_replace( "#<script(.*)>(.*)<\/script>#i" , "" , $str );
		$str = preg_replace( "#[url=(.*)](.*)[\/url]#i" , "" , $str );
		//$str = htmlspecialchars( $str );
		return $str;
	}

	// 替換特殊字元
	function undo_htmlspecialchars($input)
	{
		$input = preg_replace("/&gt;/i", ">", $input);
		$input = preg_replace("/&lt;/i", "<", $input);
		$input = preg_replace("/&quot;/i", "\"", $input);
		$input = preg_replace("/&amp;/i", "&", $input);

		return $input;
	}

	// 顯示頁碼
	function showpages( $url, $count, $pages )
	{
		$page = array();

		// first page
		if ($pages != 1)
		{
			$tmp[0] = $url."&amp;page=1";
			$tmp[1] = "第一頁";
			array_push($page, $tmp);
		}

		// pervious page
		if (($pages-1) > 0)
		{
			$tmp[0] = $url . "&amp;page=" . ($pages-1);
			$tmp[1] = "上一頁";
			array_push($page, $tmp);
		}

		// middle pages
		$head = $pages - 2;

		if ($head < 1)
		{
			$head = 1;
		}
		$tail = $head + 4;

		if ($tail > $count)
		{
			$tail = $count;
		}

		while ($head > 1 && ($tail - $head)<4)
		{
			$head--;
		}

		for ($i = $head; $i <= $tail; $i++)
		{
			$num=$i;
			if($pages == $i)
			{
				$tmp[0] = $url."&amp;page=" . $i;
				$tmp[1] = "<b>[".$i."]</b>";
				array_push($page, $tmp);
			}
			else
			{
				$tmp[0] = $url."&amp;page=".$i;
				$tmp[1] = $i;
				array_push($page, $tmp);
			}

			if ($i == $count)
			{
				break;
			}
		}

		// next page
		if ( ($pages+1) <= $count )
		{
			$tmp[0] = $url . "&amp;page=" . ($pages+1);
			$tmp[1] = "下一頁";
			array_push($page,$tmp);
		}

		//  last page
		if ($pages != $count && $count > 0)
		{
			$tmp[0] = $url . "&amp;page=" . $count;
			$tmp[1] = "最末頁";
			array_push($page,$tmp);
		}

		return $page;
	}

	// 跳頁選單
	function total_page( $url, $count, $current_page )
	{
		$str = '';
		if ($count != 0) {
			$str .= "快速跳頁選單&nbsp;<select onchange=\"location.href=this.options[selectedIndex].value\">";
		}

		for ($i = 1 ; $i <= $count ; $i++) {
			$str .= sprintf("<option value=\"".$url."&amp;page="."%d\" %s>第 %d 頁</option>\n", $i, (($i == $current_page) ? "selected" : "" ) , $i );
		}

		if ($count != 0) {
			$str .= "</select>";
		}

		return $str;
	}

	// 裁減字串
	function cuttingstr( $str, $ct )
	{
		if ( strlen( $str ) > $ct )
		{
			for ( $i=0; $i<$ct; $i++ )
			{
				$ch = substr( $str, $i, 1);
				if ( ord( $ch )>127 )
					$i++;
			}
			$str = substr( $str , 0 , $i);
			$str .= "...";
		}
		return $str;
	}

	// 裁減字串 UTF-8 專用
	function UTF8_CuttingStr($str, $strlen)
	{
		// code source http://snipplr.com/view.php?codeview&id=6625

		//擷取字串前幾個字並避免截掉半個中文字，$strlen要擷取的字串長度(以英文字母數計算，中文字需算二個字數)
		//此處直接傳入從資料庫讀出之UTF-8編碼字串

		//把' '先轉成空白
		$str = str_replace(' ', ' ', $str);

		$output_str_len = 0; //累計要輸出的擷取字串長度
		$output_str = ''; //要輸出的擷取字串

		//逐一讀出原始字串每一個字元
		for($i=0; $i<strlen($str);$i++)
		{
			//擷取字數已達到要擷取的字串長度，跳出回圈
			if($output_str_len >= $strlen)
			{
				break;
			}

			//取得目前字元的ASCII碼
			$str_bit = ord(substr($str, $i, 1));

			if($str_bit < 128)
			{
				//ASCII碼小於 128 為英文或數字字符
				$output_str_len += 1; //累計要輸出的擷取字串長度，英文字母算一個字數
				$output_str .= substr($str, $i, 1); //要輸出的擷取字串
			}
			elseif($str_bit > 191 && $str_bit < 224)
			{
				//第一字節為落於192~223的utf8的中文字(表示該中文為由2個字節所組成utf8中文字)
				$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
				$output_str .= substr($str, $i, 2); //要輸出的擷取字串
				$i++;
			}
			elseif($str_bit > 223 && $str_bit < 240)
			{
				//第一字節為落於223~239的utf8的中文字(表示該中文為由3個字節所組成的utf8中文字)
				$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
				$output_str .= substr($str, $i, 3); //要輸出的擷取字串
				$i+=2;
			}
			elseif($str_bit > 239 && $str_bit < 248)
			{
				//第一字節為落於240~247的utf8的中文字(表示該中文為由4個字節所組成的utf8中文字)
				$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
				$output_str .= substr($str, $i, 4); //要輸出的擷取字串
				$i+=3;
			}
		}

		if ($i < strlen($str))
		{
			$output_str = $output_str . "...";
		}

		//要輸出的擷取字串為空白時，輸出原始字串
		return ($output_str == '') ? $str : $output_str;
	}

	// 取得當月有幾天
	function GetDays( $m, $y )
	{
		if ($m == 2)
		{
			if (checkdate ( $m , 29 , $y)) {
				$days = 29;
			} else {
				$days = 28;
			}
		}
		else
		{
			/**					1   2    3  4   5   6   7   8   9  10  11  12 */
			$ndays = array( 0, 31, -1, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			$days = $ndays[ $m ];
		}
		return $days;
	}

	// 產生會員資料連結
	// 參數: 1. 會員id 2. 會員名稱
	function ShowMemberLink( $id, $username = '')
	{
		// 會員已被刪除
		if ($username == '')
		{
			return 'N/A';
		}

		if ($_SESSION["authority"] == "Admin")
		{
			return sprintf("<a href=\"./index.php?act=membercenter&amp;sel=detail&amp;uid=%d\">%s</a>", $id, $username);
		}
		else
		{
			return $username;
		}
	}

	function ShowActStatus($status)
	{
		if ($status == "OPEN")
		{
			$status = "<font color=\"green\">開放報名</font>";
		}
		else if ($status == "PROCEED")
		{
			$status = "<font color=\"blue\">現正進行</font>";
		}
		else if ($status == "CLOSE")
		{
			$status = "<font color=\"black\">已經關閉</font>";
		}
		else if ($status == "APPLY_CANCEL")
		{
			$status = "<font color=\"red\">申請取消</font>";
		}
		else if ($status == "CANCEL")
		{
			$status = "<font color=\"gray\">已經取消</font>";
		}

		return $status;
	}

	function SendMail($to_address, $to_name = "", $subject, $body, $attach = "")
	{
		/*
		include_once('lib/phpmailer/class.phpmailer.php');	// using phpMailer

		$mail = new PHPMailer();
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP(); 					// set mailer to use SMTP
		$mail->Encoding = "base64";

		$mail->From = "iFiFriends@gmail.com";
		$mail->FromName = "iF";

		$mail->Host = 'ssl://smtp.gmail.com';
		$mail->Port = 465; 					//default is 25, gmail is 465 or 587
		$mail->SMTPAuth = true;
		$mail->Username = "ififriends"; 	//Gmail 帳號
		$mail->Password = "ififif888"; 		//Gmail 密碼

		$mail->IsHTML(true);
		$mail->AddAddress($to_address, $to_name);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		$mail->AddAttachment($attach);
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";

		if(!$mail->Send())
		{
			echo "SendMail Error: " . $mail->ErrorInfo;
			return false;
		}
		else
		{
			return true;
		}
		*/
	}
}

function utf8_2_big5($utf8_str)
{
	$i = 0;
	$len = strlen($utf8_str);
	$big5_str = "";
	
	for ($i = 0; $i < $len; $i++)
	{
		$sbit = ord(substr($utf8_str,$i,1));
		
		if ($sbit < 128)
		{
			$big5_str.=substr($utf8_str,$i,1);
		}
		else if ($sbit > 191 && $sbit < 224)
		{
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,2));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i++;
		}
		else if ($sbit > 223 && $sbit < 240)
		{
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,3));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i+=2;
		}
		else if ($sbit > 239 && $sbit < 248)
		{
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,4));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i+=3;
		}
	}
	
	return $big5_str;
}


