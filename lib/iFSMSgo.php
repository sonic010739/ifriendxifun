<?
	class iFSMSgo
	{
		private $base_url 	= "";
		private $username 	= "";
		private $password 	= "";		
		private $encoding	= "";
		private $dstaddr	= "";
		private $smbody 	= "";
		private $dlvtime 	= "";
		
		
		/**
		* 建構子
		* @param null
		* @return void
		*/
		
		public function __construct()
		{
			//簡訊購接收程式位置
			$this->base_url = "http://www.smsgo.com.tw/sms_gw/sendsms.aspx";
			
			// 簡訊購 SMSGO 帳號密碼
			$this->username = 'if@ifriendxifun.net';	
			$this->password = 'forsonic';		
			
			$this->encoding = "BIG5";		
			$this->dstaddr	= "";
			$this->smbody 	= "";
			$this->dlvtime	= "";
		}
		
		/**
		* 剩餘點數查詢
		* @return int
		*/
		
		public function query_point()
		{
			$this->base_url = "http://www.smsgo.com.tw/sms_gw/query_point.asp";
			
			$url = sprintf("%s?username=%s&password=%s", 
				$this->base_url, urlencode($this->username), urlencode($this->password));
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			$result = curl_exec ($ch);
			
			return $result;
		}
		
		/**
		* 傳送簡訊
		* @param string $dstaddr
		* @param string	$smbody
		* @param string	$dlvtim
		* @return array
		*/
		
		public function Send_SMS( $dstaddr = '', $smbody = '', $dlvtime = '')
		{
			$msg_result = array();
			$msg_result['msgid'] = '';
			$msg_result['statuscode'] = '';
			$msg_result['statusstr'] = '';
			$msg_result['point'] = '';
			
			if ($dstaddr == "")
			{
				$msg_result['msgid'] = -9;
				return $msg_result;
			}
			
			if ($smbody == "")
			{
				$msg_result['msgid'] = -10;
				return $msg_result;
			}
			
			//簡訊購接收程式位置
			$this->base_url = "http://www.smsgo.com.tw/sms_gw/sendsms.aspx";
			
			$this->dstaddr	= $dstaddr;
			$this->smbody 	= $smbody;
			$this->dlvtime	= $dlvtime;
			
			$url = sprintf("%s?username=%s&password=%s&dstaddr=%s&encoding=%s&smbody=%s&dlvtime=%s", 
				$this->base_url, urlencode($this->username),
				urlencode($this->password), urlencode($this->dstaddr),
				urlencode($this->encoding), urlencode($this->smbody),
				urlencode($this->dlvtime));
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			$result = curl_exec ($ch);
			
			
			
			if (preg_match("/msgid=(.+)/", $result, $matche))
			{
				$msg_result['msgid'] = $matche[1];
			}
			
			if (preg_match("/statuscode=(.+)/", $result, $matche))
			{
				$msg_result['statuscode'] = $matche[1];
			}
			
			if (preg_match("/statusstr=(.+)/", $result, $matche))
			{
				$msg_result['statusstr'] = $matche[1];
			}
			
			if (preg_match("/point=([0-9]+)/", $result, $matche))
			{
				$msg_result['point'] = $matche[1];
			}
			
			return $msg_result;
		}
		
		/**
		* 顯示錯誤訊息
		* @param int $error_id
		* @return string
		*/
		
		public function GetErrorCode($error_id)
		{
			$error_msg = "";
			
			switch ($error_id)
			{
				case -1:
					$error_msg = "CGI string error or Unknow";
					break;
				case -2:
					$error_msg = "授權錯誤(帳號/密碼錯誤/IP錯誤)";
					break;
				case -3:
					$error_msg = "Priority錯誤";
					break;
				case -4:
					$error_msg = "A number違反規則";
					break;
				case -5:
					$error_msg = "B number違反規則";
					break;
				case -6:
					$error_msg = "Closed User";
					break;
				case -7:
					$error_msg = "Invalid Encoding";
					break;
				case -8:
					$error_msg = "點數不足";
					break;
				case -9:
					$error_msg = "手機號碼不可為空白";
					break;
				case -10:
					$error_msg = "傳送訊息內容不可空白";
					break;
				default:
					$error_msg = "找不到錯誤訊息，錯誤代碼: $error_id";
					break;
			}
			
			return $error_msg;
		}
		
		/**
		* iBon 繳費通知簡訊
		* @param string $dstaddr
		* @param string $iBon_code
		* @param string $deadline
		* @return string
		*/
		
		public function iBonNotify($dstaddr, $iBon_code, $deadline)
		{						
			$message = sprintf("親愛的iF會員，您報名活動繳費代碼%16s，繳費期限至%s請儘速至全家FamiPort完成繳費",
				$iBon_code, $deadline);
			
			return $this->Send_SMS( $dstaddr, $message, '');
		}
		
		/**
		* iBon 已付款通知簡訊
		* @param string $dstaddr
		* @return string
		*/
		
		public function iBonPaid($dstaddr)
		{
			$message = '親愛的iF會員，感謝您順利完成繳費程序，歡迎邀請朋友們一起參加iF活動，拓展彼此交友圈，讓人生有更多可能性!Good Luck!iF活動平台';
			
			return $this->Send_SMS( $dstaddr, $message, '');
		}
		
		/**
		* iBon 繳費提醒簡訊A
		* @param string $dstaddr
		* @param string $iBon_code
		* @param string $deadline
		* @return string
		*/
		
		public function iBonNotifyA($dstaddr, $iBon_code, $deadline)
		{			
			$message = sprintf("親愛的iF會員，再次提醒您，活動報名之繳費代碼為%s，繳費期限至%s止，請儘速完成繳費! iF活動平台",
				$iBon_code, $deadline);
			
			return $this->Send_SMS( $dstaddr, $message, '');
		}
		
		/**
		* iBon 繳費提醒簡訊B
		* @param string $dstaddr
		* @param string $iBon_code
		* @param string $deadline
		* @return string
		*/
		
		public function iBonNotifyB($dstaddr, $iBon_code)
		{			
			$message = sprintf("親愛的iF會員，報名之繳費代碼%s，請今日完成繳費以保障您的活動權益!GooDay!iF活動平台",
				$iBon_code);
			
			return $this->Send_SMS( $dstaddr, $message, '');
		}		
		
		/**
		* iF活動取消簡訊
		* @param string $dstaddr
		* @param string $act_date
		* @return string
		*/
		
		public function ActCancelNotify($dstaddr, $act_date)
		{			
			$message = sprintf("親愛的iF會員，您報名%siF活動因故取消，若造成您任何的不便，請多包含。EO會儘速向您說明，若有問題歡迎向客服信箱聯繫",
				$act_date);
			
			return $this->Send_SMS( $dstaddr, $message, '');
		}		
	}
?>