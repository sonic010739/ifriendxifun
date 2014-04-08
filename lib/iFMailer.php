<?
	// using phpMailer
	include('/var/www/vhosts/ifriendxifun.net/httpdocs/lib/phpmailer/class.phpmailer.php');
	include('/var/www/vhosts/ifriendxifun.net/httpdocs/lib/phpmailer/language/phpmailer.lang-en.php');


	class iFMailer
	{
		private $username 	= "";
		private $password 	= "";
		private $from 		= "";
		private $fromname 	= "";
		private $host 		= "";
		private $port 		= "";
		private $appendix 	= "";

		public $mail 		= "";
		public $ErrorInfo 	= "";

		/**
		* 建構子
		* @param null
		* @return void
		*/

		public function __construct()
		{
			// SMTP 帳號資訊
			$this->username = 'if@ifriendxifun.net';
			$this->password = 'forsonic';
			$this->from 	= 'if@ifriendxifun.net';
			$this->fromname = 'iF';
			$this->host 	= 'ifriendxifun.net';

			$this->mail 	= new PHPMailer();
			$this->mail->CharSet 	= 'UTF-8';
			$this->mail->IsSMTP();
			$this->mail->Encoding 	= 'base64';

			$this->mail->From 		= $this->from;
			$this->mail->FromName 	= $this->fromname;
			$this->mail->Host 		= $this->host;
			$this->mail->Port 		= $this->port;
			$this->mail->SMTPAuth 	= true;
			$this->mail->Username 	= $this->username;
			$this->mail->Password 	= $this->password;

			$this->ErrorInfo = "";

			// 信件結尾內容
			$this->appendix  = "<br />以上資訊若有任何問題，請與我們連絡，我們會儘速與您回覆。<br />";
			$this->appendix .= "若您並未在此網站中註冊會員，無須理會此信件。<br />";
			$this->appendix .= "<br />";
			$this->appendix .= "<font color=\"red\">Let’s Turn Our LiFe On!!</font><br />";
			$this->appendix .= "<br />";
			$this->appendix .= "網站> <a href=\"http://www.iFriendxiFun.net\">www.iFriendxiFun.net</a><br />";
			$this->appendix .= "部落格> <a href=\"http://www.wretch.cc/blog/ifriendxifun\">www.wretch.cc/blog/ifriendxifun</a><br />";
			$this->appendix .= "系統問題> <a href=\"mailto:system@ifriendxifun.net\">system@ifriendxifun.net</a><br />";
			$this->appendix .= "若有問題> <a href=\"mailto:service@ifriendxifun.net\">service@ifriendxifun.net</a><br />";
			$this->appendix .= "<br />";
			$this->appendix .= "iF小組<br />";
			$this->appendix .= "<br />";
			$this->appendix .= "<hr />";
			$this->appendix .= "iF是由許多新奇有趣的活動所組成，包括iFriend底下都會最流行的7 Minutes Dating(7MD)，";
			$this->appendix .= "陸續也將推出iParty、iFashion、iFood、iSport、iMovie、iMusic等各種主題活動。我們也歡迎";
			$this->appendix .= "由你創造出各種活動，讓iF的人際地圖由你為中心發展全新的視野。以各項實體的活動，以實";
			$this->appendix .= "際面對面(Face)的方式，讓你更有效率的發現 (Find)新的朋友 (Friends)，創造 (Found)每個i(我)";
			$this->appendix .= "人生中的新樂章。<br />";
			$this->appendix .= "<br />";
			$this->appendix .= "iF使你找到教你完成夢想的良師。<br />";
			$this->appendix .= "iF與你一起完成夢想的益友。<br />";
			$this->appendix .= "iF陪你渡過夢想的另一伴。<br />";
		}

		/**
		* 解構子
		* @param null
		* @return void
		*/

		public function __destruct()
		{
			$this->username = "";
			$this->password = "";
			$this->mail->SmtpClose();
		}

		/**
		* L1 - 會員註冊確認信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function RegisterMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF]會員 ". $mailinfo["realname"] ." 註冊確認信";

			// 信件內容
			$mailbody  = "親愛的新會員 ". $mailinfo["realname"] ." ，您好!<br />";
			$mailbody .= "<br />";
			$mailbody .= "歡迎您成為[iF]的新夥伴，此信的目的在幫助您確認在[iF]中所註冊之新帳號，經檢查下列帳";
			$mailbody .= "號資料確認無誤後，記得使用資料下方之連結才能正式啟用您的帳號並參與任何活動喔!!若有";
			$mailbody .= "其它問題，請連至[iF]的部落格或email詢問，我們將會盡快為您解答。以下是您的帳號資料";
			$mailbody .= "與啟用連結:<br />";
			$mailbody .= "<br />";
			$mailbody .= "會員帳號: ". $mailinfo["username"] ." <br />";
			$mailbody .= "密碼提示: ". $mailinfo["passwordhint"] ." <br />";
			$mailbody .= "經確認為您本人帳號後，請使用以下之連結啟用您的帳號:<br />";
			$mailbody .= $mailinfo["activelink"] ." <br />";
			$mailbody .= "<br />";
			$mailbody .= "提醒您，[iF]的活動中有一超值的<TURN OUR LIFE ON計劃>，讓您在報名參加活動時取得更";
			$mailbody .= "多的優惠喔~詳細優惠辦法請連至<a href=\"http://www.wretch.cc/blog/ifriendxifun/12";
			$mailbody .= "164639\">http://www.wretch.cc/blog/ifriendxifun/12164639</a>中進行了解。";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L2 - 優惠邀請信
		* @param string	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function InviteMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 邀您參加[iF]活動";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["intro_name"] ." ，您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "別說我沒報好康喔! 我剛報名了[iF]的活動，這真是一個充滿歡樂的實體活動平台，裡面有好玩";
			$mailbody .= "有趣的活動，不僅讓我工作繁忙之餘，享受多采多姿的生活，還讓我認識不同領域的朋友，大";
			$mailbody .= "大的拓展交友圈，使我的人生充滿無限可能！下面就是我這次參加活動的內容，邀請你一起來";
			$mailbody .= "體驗喔！<br />";
			$mailbody .= "<br />";
			$mailbody .= "主題: ". $mailinfo["act_topic"] ." <br />";
			$mailbody .= "日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "名稱: ". $mailinfo["act_name"] ." <br />";
			$mailbody .= "地點: ". $mailinfo["act_place"] ." <br />";
			$mailbody .= "活動敘述: ". $mailinfo["decription"] ." <br />";
			$mailbody .= "<br />";
			$mailbody .= "除此之外，如果想參與Turn Our LiFe On計畫，只要在登記報名其他iF活動時填入我的專屬連";
			$mailbody .= "結，馬上就可以讓你享有優惠喔！我的專屬連結如下:<br />";
			$mailbody .=  $mailinfo["discount_link"] ." <br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L3 - 繳費期限提醒信a
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function PayDeadlineMailA($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ."<iF>繳費期限提醒通知";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您已登記了[iF]網站 ”". $mailinfo["act_name"] ."” 的活動，繳費後才算是報名成功喔~否則會在該";
			$mailbody .= "活動繳費期限截止後取消您的登記，在此提醒您，記得在繳費期限內前往全省7-11以iBon完";
			$mailbody .= "成繳費喲!!您的該場活動詳細內容以及繳費期限如下<br />";
			$mailbody .= "<br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動地點: ". $mailinfo["act_place"] ." <br />";			
			$mailbody .= "iBon繳費號碼: ". $mailinfo["ibon_code"] ." <br />";
			$mailbody .= "繳費期限: ". $mailinfo["ibon_deadline"] ." (過此期限將從名單中刪除)<br />";
			$mailbody .= "<br />";
			$mailbody .= "在此提醒您，若當日無法如期前往參加此活動，煩請務必於網站中進行取消參與活動的動作，";
			$mailbody .= "讓其他想參加之會員能報名。若過了繳費期限系統將會自動取消您所登記活動之紀錄，如此";
			$mailbody .= "便會失去參與活動的資格，但若是多次未繳費被系統自動取消紀錄者，將會被列入黑名單而";
			$mailbody .= "限制會員權限，因此務必請注意活動的繳費期限。<br />";			
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L4 - 繳費期限提醒信b (iBon繳費代碼已過期者)
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function PayDeadlineMailB($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." iBon繳費代碼過期提醒";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您登記[iF]網站中名為 ”". $mailinfo["act_name"] ."” 的活動，但是您的iBon繳費代碼已過繳費期限，因此";
			$mailbody .= "iBon繳費代號已失效，請不要再以該繳費代號繳費。請重新登入[iF]會員介面產生新的有效iBon";
			$mailbody .= "繳費代號，並儘速至7-11以iBon完成繳費喲!!<br />";
			$mailbody .= "<br />";
			$mailbody .= "提醒您，若未在[iF]活動繳費期限前完成繳費，[iF]將取消您的登記。<br />";
			$mailbody .= "該場活動詳細內容以及繳費期限如下<br />";
			$mailbody .= "<br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動地點: ". $mailinfo["act_place"] ." <br />";
			$mailbody .= "iBon繳費期限: ". $mailinfo["ibon_deadline"] ." (此ibon繳費期限已過，請至網頁中產生新的ibon繳費代號)<br />";
			$mailbody .= "[iF]繳費期限: ". $mailinfo["iF_deadline"] ."  (過此期限將從名單中刪除)<br />";
			$mailbody .= "<br />";

			$mailbody .= "在此提醒您，若當日無法如期前往參加此活動，煩請務必於網站中進行取消參與活動的動作，";
			$mailbody .= "讓其他想參加之會員能報名。若過了[iF]繳費期限網站系統將會自動取消您所登記活動之紀錄，";
			$mailbody .= "如此便會失去參與活動的資格，但若是多次未繳費被系統自動取消紀錄者，將會被列入黑名單";
			$mailbody .= "而被限制會員權限，因此務必請注意活動的繳費期限。<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L5 - 報名成功確認信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$paidinfo
		* @return bool
		*/

		public function PaidMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 活動報名完成確認";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您所登記之[iF]活動，您的報名已確認完成，以下為您的個人資料以及報名場次:<br />";
			$mailbody .= "<br />";
			$mailbody .= "會員姓名: ". $mailinfo["realname"] ." <br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動主題: ". $mailinfo["act_topic"] ." <br />";
			$mailbody .= "活動名稱: ". $mailinfo["act_name"] ." <br />";
			$mailbody .= "iBon繳款代碼: ". $mailinfo["ibon_code"] ." <br />";
			$mailbody .= "繳費時間: ". $mailinfo["ibon_paytime"] ." <br />";
			$mailbody .= "<br />";
			$mailbody .= "此外，系統中已將您的專屬link啟用，您的朋友們已經可以經由此連結取得報名活動的優惠嘍~<br />";
			$mailbody .= "您個人的專屬link: ". $mailinfo["discount_link"] ." <br />";
			$mailbody .= "詳細優惠辦法請連至<a href=\"http://www.wretch.cc/blog/ifriendxifun/12164639\">&lt;TURN OUR LIFE ON計劃&gt;</a>中參考<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L6 - 活動宣傳信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$paidinfo
		* @return bool
		*/


		public function ActADMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF]本週新增活動 (". $mailinfo["username"] .")";

			// 信件內容
			$mailbody  = "親愛的[iF]會員 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "[iF]在接下來的幾個星期，舉辦了幾場精采刺激的活動，誠摯地邀請您或您的朋友們參與，以下";
			$mailbody .= "是相關活動內容:<br />";
			$mailbody .= "<br />";

			$mailbody .= "<table width=\"500\" border=\"0\">";
			$mailbody .= "<tr>";
			$mailbody .= "<td align=\"center\" width=\"100\">活動日期</td>";
			$mailbody .= "<td align=\"center\" width=\"120\">活動主題</td>";
			$mailbody .= "<td align=\"center\" width=\"280\">活動名稱</td>";
			$mailbody .= "</tr>";

			foreach ($mailinfo["actlist"] as $act)
			{
				$mailbody .= "<tr>";
				$mailbody .= "<td align=\"center\">" . $act["act_date"] ."</td>";
				$mailbody .= "<td >" . $act["tname"] ."</td>";
				$mailbody .= "<td >" . $act["name"] ."</td>";
				$mailbody .= "</tr>";
			}

			$mailbody .= "</table>";
			$mailbody .= "<br />";
			$mailbody .= "想看到更多[iF]有趣的活動，請至[iF]的網站中瀏覽。<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}


		/**
		* L7 - 會員活動提醒信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function ActNotifyMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF]活動提醒通知，後天記得要參加iF的活動喔！";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "在此提醒您，你報名了兩天後的[iF]活動，我們期待您的準時參與。此外，iF也希望邀請您的所<br />";
			$mailbody .= "有好朋友一起來參與我們將來的活動!!<br />";
			$mailbody .= "<br />";
			$mailbody .= "以下為您所報名之參加:<br />";
			$mailbody .= "<br />";
			$mailbody .= "會員姓名: ". $mailinfo["realname"] ." ( 請於活動當天攜帶身分證明文件前往 )<br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動主題: ". $mailinfo["act_topic"] ." <br />";
			$mailbody .= "活動名稱: ". $mailinfo["act_name"] ." <br />";
			$mailbody .= "<br />";
			$mailbody .= "詳細精采活動內容可透過[iF]的活動網址了解。<br />";
			$mailbody .= "活動當天別忘在提早出門，帶著愉悅的心情與所有iF夥伴們一同渡過美好時光。<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L8 - 會員活動感謝信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function ActThanksMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF]活動感謝信 For ". $mailinfo["realname"];

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";

			$mailbody .= "再次感謝您參與與支持此次[iF]所舉辦活動，相信今天的活動一定讓你有不少心得與收穫! 提醒";
			$mailbody .= "您，若您參加的場次於活動最後有填寫問卷，請記得返回系統中點選該活動查詢結果，系統將會";
			$mailbody .= "在活動結束後48小時內統計出並顯示。<br />";
			$mailbody .= "查詢方式步驟如下: 登入[iF]網站 → 活動列表 → 過去參加的活動 → 點選該場活動名稱<br />";
			$mailbody .= "則在會員參與活動資料中可得知結果。<br />";
			$mailbody .= "<br />";
			$mailbody .= "不論對於此次的活動有任何的心得或建議，[iF]都衷心的希望得知您真實的感受，期望能得到您";
			$mailbody .= "珍貴的意見來促使我們成長，您的滿意與建議，將會是[iF]繼續前進與努力的原動力。若有任何";
			$mailbody .= "意見感想請務必至[iF]部落格中與我們分享，[iF]網站中還有很多各種有趣的活動，希望您能在";
			$mailbody .= "其中找到開拓人生的新連結，開啟與眾不同的人生夢想並充實地渡過生活中每個精采時刻。<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活動當天期待";
			$mailbody .= "你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友變成您的朋友，讓我們的";
			$mailbody .= "朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			$mailbody .= "<br />以上資訊若有任何問題，請與我們連絡，我們會儘速與您回覆。<br />";
			$mailbody .= "若您並未在此網站中註冊會員，無須理會此信件。<br />";
			$mailbody .= "<br />";
			$mailbody .= "<font color=\"red\">Let’s Turn Our LiFe On!!</font><br />";
			$mailbody .= "<br />";
			$mailbody .= "網站> <a href=\"http://www.iFriendxiFun.net\">www.iFriendxiFun.net</a><br />";
			$mailbody .= "部落格> <a href=\"http://www.wretch.cc/blog/ifriendxifun\">www.wretch.cc/blog/ifriendxifun</a><br />";
			$mailbody .= "系統問題> <a href=\"mailto:system@ifriendxifun.net\">system@ifriendxifun.net</a><br />";
			$mailbody .= "若有問題> <a href=\"mailto:service@ifriendxifun.net\">service@ifriendxifun.net</a><br />";
			$mailbody .= "<br />";
			$mailbody .= $mailinfo["EOname"] ." & iF小組<br />";
			$mailbody .= "<br />";
			$mailbody .= "<hr />";
			$mailbody .= "iF是由許多新奇有趣的活動所組成，包括iFriend底下都會最流行的7 Minutes Dating(7MD)，";
			$mailbody .= "陸續也將推出iParty、iFashion、iFood、iSport、iMovie、iMusic等各種主題活動。我們也歡迎";
			$mailbody .= "由你創造出各種活動，讓iF的人際地圖由你為中心發展全新的視野。以各項實體的活動，以實";
			$mailbody .= "際面對面(Face)的方式，讓你更有效率的發現 (Find)新的朋友 (Friends)，創造 (Found)每個i(我)";
			$mailbody .= "人生中的新樂章。<br />";
			$mailbody .= "<br />";
			$mailbody .= "iF使你找到教你完成夢想的良師。<br />";
			$mailbody .= "iF與你一起完成夢想的益友。<br />";
			$mailbody .= "iF陪你渡過夢想的另一伴。<br />";

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L9 - 會員取消活動確認信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function UserCancelMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 取消活動確認";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "首先感謝您對於活動的支持與愛護，[iF]已收到您取消活動的申請，而該場活動中的名額也將釋";
			$mailbody .= "出給其他位會員報名參與，很遺憾在此次的活動中少了您的參與及分享，若對於此次活動有任何";
			$mailbody .= "意見，[iF]都衷心的希望您能至與我們分享。此外，[iF]網站中仍有很多各種有趣的活動，您必能";
			$mailbody .= "從中找到您所喜愛的活動主題與內容，[iF]期待您的出現與分享。<br />";
			$mailbody .= "<br />";
			$mailbody .= "您所取消的活動詳情如下:<br />";
			$mailbody .= "會員姓名: ". $mailinfo["realname"] ." <br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動主題: ". $mailinfo["act_topic"] ." <br />";
			$mailbody .= "活動名稱: ". $mailinfo["act_name"] ." <br />";
			$mailbody .= "<br />";
			$mailbody .= "再次提醒您，<br />";
			$mailbody .= "<ol>";
			$mailbody .= "<li>若您尚未繳費，切勿再使用此iBon代碼前往繳費</li>";
			$mailbody .= "<li>若您已經使用E-coupon完成繳費，iF系統將會致上一張E-coupon</li>";
			$mailbody .= "<li>若您已經針對可使用優惠卷之場次完成iBon繳費，iF將不會退費，iF系統會致上一張E-coupon</li>";
			$mailbody .= "(E-coupon優惠卷可無限期使用，您可在未來在登記iF中其他相同費用之活動時，使用該優惠";
			$mailbody .= "卷免費報名)<br />";
			$mailbody .= "<li>若您已經完成iBon繳費，而該活動限制為不可使用優惠券，則請您與該場活動EO聯絡並了解退費的機制與方法</li>";
			$mailbody .= "</ol>";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L10 - 會員關閉帳戶確認信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function UserCloseAccuntMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 會員關閉帳戶確認信";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "[iF]於近日內確認已收到您關閉帳戶的申請，因此為了避免有人假借您的帳戶與資料報名各場活";
			$mailbody .= "動，故於網站中已將您的帳戶關閉而無法登入。未來網站中若有任何您有興趣的活動，歡迎您隨";
			$mailbody .= "時寄信告訴我們，[iF]將會再次開啟您的帳號以便於登記報名任何一場活動。<br />";
			$mailbody .= "此外，若對於我們的活動有任何寶貴的意見或建議，[iF]都衷心的希望您能至部落格中與我們分";
			$mailbody .= "享。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L11 - 活動取消通知信A
		* @param string 	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function ActCancelMailA($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 活動取消通知信";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "很抱歉，您於 ". $mailinfo["act_date"] ." 所登記報名之活動場次 ”". $mailinfo["act_name"] ."” 已被取消，特";
			$mailbody .= "此通知請勿於當天前往活動現場參加活動，活動狀態請登入[iF]進行了解。此活動若尚未繳費，";
			$mailbody .= "歡迎參考網頁中其他各種有趣的活動，若已使用優惠卷或由ibon繳費完成報名，則[iF]將給予";
			$mailbody .= "您活動優惠卷，此卷可參與費用相同且可使用優惠卷之活動。若您對於我們的活動有任何寶貴";
			$mailbody .= "的意見或建議，[iF]都衷心的希望您能至部落格中與我們分享，因為這將是[iF]舉辦出更優質";
			$mailbody .= "活動的最佳動力。<br />";
			$mailbody .= "<br />";
			$mailbody .= "提醒您，<br />";
			$mailbody .= "<ol>";
			$mailbody .= "<li>若您已經使用E-coupon完成繳費，iF系統將會致上一張E-coupon</li>";
			$mailbody .= "<li>若您已經針對可使用優惠卷之場次完成iBon繳費，iF將不會退費，iF系統會致上一張E-coupon</li>";
			$mailbody .= "(E-coupon優惠卷可無限期使用，您可在未來在登記iF中其他相同費用之活動時，使用該優惠";
			$mailbody .= "卷免費報名)<br />";
			$mailbody .= "<li>若您已經完成iBon繳費，而該活動限制為不可使用優惠券，則請您與該場活動EO聯絡並了解退費的機制與方法</li>";
			$mailbody .= "</ol>";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L12 - 活動取消通知信B
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function ActCancelMailB($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 活動取消通知信";

			// 信件內容
			$mailbody  = "親愛的EO - ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您於 ". $mailinfo["act_date"] ." 所舉辦之活動場次 ”". $mailinfo["act_name"] ."” 已經被[iF]審核取消，特此通知請";
			$mailbody .= "勿於當天前往活動現場舉辦活動，請記得提醒您所有參與該場活動的朋友們此項訊息，活動情況請登錄";
			$mailbody .= "[iF]進行了解。針對此次活動的取消[iF]感到相當遺憾，希望您能再次舉辦任何能拓展會員們生活與成";
			$mailbody .= "長的優質活動，在此[iF]將會協助與您一同完成。若您在舉辦活動方面有任何寶貴的建議或疑問，[iF]";
			$mailbody .= "都衷心的希望您能至網站內的EO專屬的討論區中提出與我們分享，[iF]將會為針對您的疑問與建議做解";
			$mailbody .= "答與改進，感謝您對於所有會員舉辦活動所付出的心力。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L13 - 會員獲得優惠卷通知信
		* @param string	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function GetCouponMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 獲得優惠卷通知信";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "恭喜您!!經過[iF]的統計，您已於Turn our life on計畫中累積六位朋友使用您的會員專屬Link報名活動，";
			$mailbody .= "[iF]將贈予活動優惠卷乙張，可免費報名於接受優惠券之任何活動，您可連上[iF]確認可使用之相關場次。<br />";
			$mailbody .= "<br />";
			$mailbody .= "[iF]歡迎您繼續將各種活動推薦給您的朋友，開啟他們不同的生活領域並有所收穫，因為這正是我們不斷";
			$mailbody .= "舉辦各項優質活動的宗旨。若對於我們的活動有任何寶貴的意見或建議，[iF]都衷心的希望您能至部落格";
			$mailbody .= "中與我們分享<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L14 - 會員忘記密碼回覆告知信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function ResetPasswordMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 忘記密碼回覆告知信";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "此為一封密碼信函，文中提供一暫時登入之密碼，登入後請儘速修改個人密碼，以免帳號密碼資料誤為他人";
			$mailbody .= "使用。若還有任何問題請至[iF]部落格中提出或用email與我們分享，[iF]將會以最快的速度為您解決<br />";
			$mailbody .= "<br />";
			$mailbody .= "會員帳號: ". $mailinfo["username"] ."<br />";
			$mailbody .= "會員密碼: ". $mailinfo["password"] ."<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L15 - 升級EO審核通過確認信
		* @param string 	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function UpgrdeEOMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 升級EO審核通過確認信";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";

			$mailbody .= "您向[iF]提出升級為EO之申請經審查已通過，網站中已將您所使用之帳號權限提升，";
			$mailbody .= "歡迎您一同加入我們的行列!! 請記得在舉辦活動之前先確認是否已進行過以下各點事項:<br />";
			$mailbody .= "<br />";
			$mailbody .= "<ol>";
			$mailbody .= "<li>立即登入網站並且徹底了解與EO相關之責任與規範</li>";
			$mailbody .= "<li>與[iF]管理人員洽談了解網站舉辦活動宗旨與注意事項</li>";
			$mailbody .= "<li>熟悉所有EO的重要執行權限(如舉辦活動、提報黑名單…等)</li>";
			$mailbody .= "<li>接受EO能力訓練</li>";
			$mailbody .= "</ol>";
			$mailbody .= "若您已完成以上各點的事項，可於[If]任何社群內開啟你所想舉辦的任何活動!!提醒您，EO權限中";
			$mailbody .= "增加了討論區功能，其為專屬於EO與管理者之間的溝通平台，若有任何的建議與問題都歡迎在討論";
			$mailbody .= "區中提出，[iF]將會與您一同解決並不斷成長，朝向舉辦出更優質活動的目標前進。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L16 - EO新增活動確認信
		* @param string 	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function AddActMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 新增活動確認信";

			// 信件內容
			$mailbody  = "親愛的EO ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您於 ". $mailinfo["act_date"] ." 新增的活動 ”". $mailinfo["act_name"] ."” 已開啟成功並已開放報名";
			$mailbody .= "，可登入[iF]將活動訊息頁面傳送給任何的朋友，協助您越快您所希望的人數!同時請定時登入[iF]注意活動";
			$mailbody .= "報名的最新情形，過程中出現任何問題請在第一時間內通知我們，[iF]將會以最快的速度解決並通知您<br/>。";
			$mailbody .= "<br/>";
			$mailbody .= "以下兩點提醒您:<br/>";
			$mailbody .= "1.網頁中討論區功能為專屬於EO與管理者之間的溝通平台，若有任何的建議與問題都歡迎在討論區中提出，";
			$mailbody .= "讓[iF]能朝向舉辦出更優質活動的目標前進。<br/>";
			$mailbody .= "2.若您所舉辦之活動有限制無法使用E-Coupon優惠卷，請於活動內容中註明當會員參與活動被取消或您取";
			$mailbody .= "消該會員之參與權時的退費機制。<br/>";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L17 - EO活動提醒信
		* @param string	 $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function EOActNotifyMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] EO ". $mailinfo["realname"] ." 活動提醒信";

			// 信件內容
			$mailbody  = "親愛的EO ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";

			$mailbody .= "您將於" .$mailinfo["act_date"] ." ". $mailinfo["act_time"] ." 於 ". $mailinfo["act_place"]. " ";
			$mailbody .= "舉辦 ”" . $mailinfo["act_name"] ."” 活動，若尚有名額可報名，請持續將[iF]活動頁面傳送給您的任何";
			$mailbody .= "朋友!並提醒您活動前先行於活動管理頁面中列印活動簽到表方便您進行活動報到，並請提早到達現場，完成";
			$mailbody .= "您個人的前置作業，以舉辦一場完美的活動!至活動日期期間若有任何問題，請至[iF]部落格或網站討論區中";
			$mailbody .= "提出，我們將盡快協助您解決，共同為打造更優質活動的目標而努力!!<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}


		/**
		* L18 - EO取消活動通知信
		* @param string	$to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function CancelActApplyMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 取消活動申請通知信";

			// 信件內容
			$mailbody  = "親愛的EO ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "[iF]已收到 ". $mailinfo["act_date"] ." ". $mailinfo["act_time"] ." 於 ". $mailinfo["act_place"]. " ";
			$mailbody .= "您將舉辦 ”". $mailinfo["act_name"] ."” 活動之取消申請，目前已進入審核的階段，審核結果將會在近日內於 ";
			$mailbody .= "[iF]中以email通知您，若從未提出此申請，請盡速與我們聯繫以免活動取消後造成報名參加之會員的困擾。至審";
			$mailbody .= "核通過期間若有任何問題，請至[iF]部落格或網站討論區中提出，我們將會與您討論並盡快解決!!<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L19 - EO取消活動審核結果信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function CancelActResultMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." 取消活動審核結果信";

			// 信件內容
			$mailbody  = "親愛的EO ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= $mailinfo["act_date"] ." ". $mailinfo["act_time"] ." 於 ". $mailinfo["act_place"]. " 您將舉辦 ";
			$mailbody .= "”". $mailinfo["act_name"] ."” 活動之取消申請，經[iF]審核過之結果已於[iF]上更新並留訊息給您，";
			$mailbody .= "請立即登入[iF]了解目前活動情況。<br />";
			$mailbody .= "<br />";
			$mailbody .= "若經審核通過為可取消活動場次，請記得通知所有參加該場活動的朋友於當日勿前往活動地點，[iF]亦會";
			$mailbody .= "於網站中停止接受登記報名。但若經審核認為提出取消之理由並不成立，則網站中的活動將會繼續接受報";
			$mailbody .= "名登記，也請您盡速與我們聯繫並商討出讓活動如期舉辦之方案。若您尚有任何問題，請至[iF]部落格或";
			$mailbody .= "網站討論區中提出，我們將會與您討論並盡快解決!!<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L20 - EO活動感謝信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function EOActThanksMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF]感謝您成功舉辦 ”". $mailinfo["act_name"] ."” 活動";

			// 信件內容
			$mailbody  = "親愛的EO ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";

			$mailbody .= "感謝您成功舉辦 ”". $mailinfo["act_name"] ."”  活動!在此提醒您，今天場次若屬於需會員填寫問卷的";
			$mailbody .= "活動，請並將結果於今日登記於[iF]填寫問卷結果中，若尚未填寫完成並不算完成活動!<br />";
			$mailbody .= "<br />";
			$mailbody .= "對於活動若有任何的心得或建議，[iF]都衷心的希望能了解並一同努力，故務必請您將關於此次活動寶貴";
			$mailbody .= "的意見用email或在討論區中與我們分享，因為這將會是[iF]繼續成長與前進的動力。相信經過了今天的";
			$mailbody .= "活動，您對於[iF]的宗旨有了更深一層的體認，不斷的成長才能有更優質的活動，[iF]與會員都非常期待";
			$mailbody .= "您下次所舉辦的活動喔!!<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L22 - [iF]繳費期限已過通知信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$mailinfo
		* @return bool
		*/

		public function PayDeadlineMailC($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". $mailinfo["realname"] ." <iF>繳費期限已過";

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "您於日前所登記[iF]網站 ”". $mailinfo["act_name"] ."” 的活動，該場活 ";
			$mailbody .= "動詳細內容以及繳費期限如下:<br />";
			$mailbody .= "<br />";
			$mailbody .= "活動日期: ". $mailinfo["act_date"] ." <br />";
			$mailbody .= "活動時間: ". $mailinfo["act_time"] ." <br />";
			$mailbody .= "活動地點: ". $mailinfo["act_place"] ." <br />";
			$mailbody .= "iBon繳費期限: ". $mailinfo["ibon_deadline"] ." <br />";
			$mailbody .= "iBon繳費號碼: ". $mailinfo["ibon_code"] ." <br />";
			$mailbody .= "[iF]繳費期限: ". $mailinfo["iF_deadline"] ." (<b>此期限已到期!! 請勿前往繳費!!</b>)<br />";
			$mailbody .= "<br />";
			$mailbody .= "在此通知您，由於已過[iF]繳費期限而您尚未前往繳費，故[iF]系統已將您由此";
			$mailbody .= "活動之登記名單中刪除而失去參與該活動的權利，切記請勿前往繳費。提醒您!!";
			$mailbody .= "若是多次未繳費被系統自動取消紀錄者，將會被列入黑名單而限制會員權限，因";
			$mailbody .= "此務必請注意各項活動中[iF]的繳費期限。<br />";
			$mailbody .= "<br />";
			$mailbody .= "關於活動相關內容與規定請參考iF網站及部落格或iF夥伴的相關網站及部落格，活";
			$mailbody .= "動當天期待你的參與，更希望邀請您的好友一起來參與我們的活動，讓我們的朋友";
			$mailbody .= "變成您的朋友，讓我們的朋友成為您一起成長的夥伴!!<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* L23 - 活動報名統計信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$paidinfo
		* @return bool
		*/

		public function ActJoinSTATMail($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] ". date("Y/m/d") ." 活動報名情況統計";

			// 信件內容
			$mailbody  = "親愛的 iF 管理員 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "本日活動報名情況統計<br />";
			$mailbody .= "<b>統計時間</b>: ". date("Y/m/d H:i:s") . "<br />";
			$mailbody .= "<b>執行費時</b>: ". $mailinfo['execution_time'] . "sec<br />";
			$mailbody .= "<br />";

			$mailbody .= "<table width=\"650\" border=\"0\">";
			$mailbody .= "<tr>";
			$mailbody .= "<th align=\"center\" width=\"200\">活動名稱</th>";
			$mailbody .= "<th align=\"center\" width=\"100\">活動日期</th>";
			$mailbody .= "<th align=\"center\" width=\"60\">昨日累積報名人數</th>";
			$mailbody .= "<th align=\"center\" width=\"60\">目前報名人數</th>";
			$mailbody .= "<th align=\"center\" width=\"60\">新增報名人數</th>";
			$mailbody .= "<th align=\"center\" width=\"60\">目前繳費人數</th>";
			$mailbody .= "<th align=\"center\" width=\"60\">未繳費人數</th>";
			$mailbody .= "<th align=\"center\" width=\"70\">目前繳費金額</th>";
			$mailbody .= "</tr>";

			foreach ($mailinfo["actlist"] as $act)
			{
				$mailbody .= "<tr>";
					$mailbody .= "<td align=\"left\">". $act["name"] ."</td>";
					$mailbody .= "<td align=\"center\">". $act["act_date"] ."</td>";
					$mailbody .= "<td align=\"left\">";
						$mailbody .= "男: ". $act["join_males"] ."<br />";
						$mailbody .= "女: ". $act["join_females"];
					$mailbody .= "</td>";
					$mailbody .= "<td align=\"left\">";
						$mailbody .= "男: ". $act["males"] ."<br />";
						$mailbody .= "女: ". $act["females"];
					$mailbody .= "</td>";
					$mailbody .= "<td align=\"left\">";
						$mailbody .= "男: ". $act["today_join_males"] ."<br />";
						$mailbody .= "女: ". $act["today_join_females"];
					$mailbody .= "</td>";
					$mailbody .= "<td align=\"left\">";
						$mailbody .= "男: ". $act["paid_males"] ."<br />";
						$mailbody .= "女: ". $act["paid_females"];
					$mailbody .= "</td>";
					$mailbody .= "<td align=\"left\">";
						$mailbody .= "男: ". $act["unpay_males"] ."<br />";
						$mailbody .= "女: ". $act["unpay_females"];
					$mailbody .= "</td>";
					$mailbody .= "<td align=\"right\">";
						$mailbody .= $act["paid_amount"] . "元";
					$mailbody .= "</td>";
				$mailbody .= "</tr>";
			}

			$mailbody .= "</table>";

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}
		
		/**
		* L24 - 會員填寫朋友提醒信
		* @param string $to_address
		* @param string	$to_name
		* @param array	$paidinfo
		* @return bool
		*/

		public function NewMatchNotify($to_address, $to_name = "", $mailinfo)
		{
			// 信件主旨
			$subject = "[iF] 填寫朋友提醒信 For ". $mailinfo["realname"];

			// 信件內容
			$mailbody  = "親愛的 ". $mailinfo["realname"] ." 您好:<br />";
			$mailbody .= "<br />";
			$mailbody .= "再次感謝您參與與支持此次[iF]所舉辦活動，相信今天的活動一定讓";
			$mailbody .= "你有不少心得與收穫!";
			$mailbody .= "<font color=\"red\">提醒您，今日您所參加的活動需要您返回iF網站";
			$mailbody .= "中進行填寫活動中想進一步認識的朋友，因此請記得在<b>24小時內</b>完";
			$mailbody .= "成填寫，系統預設會將您的email及MSN給予所有參加者，詳細填寫步驟請";
			$mailbody .= "參閱iF網站或部落格中的說明。</font>";
			$mailbody .= "不論對於此次的活動有任何的心得或";
			$mailbody .= "建議，[iF]都衷心的希望得知您真實的感受，期望能得到您珍貴的意";
			$mailbody .= "見來促使我們成長，您的滿意與建議，將會是[iF]繼續前進與努力的原";
			$mailbody .= "動力。若有任何意見感想請務必至[iF]部落格中與我們分享，[iF]網";
			$mailbody .= "站中還有很多各種有趣的活動，希望您能在其中找到開拓人生的新連";
			$mailbody .= "結，開啟與眾不同的人生夢想並充實地渡過生活中每個精采時刻。<br />";
			$mailbody .= "<br />";
			$mailbody .= "本信件由系統發出，請勿回覆此信箱。<br />";

			// 信件結尾內容
			$mailbody .= $this->appendix;

			return $this->SendMail($to_address, $to_name = "", $subject, $mailbody, $attach = "");
		}

		/**
		* SendMail
		* @param string $to_address
		* @param string	$to_name
		* @param string	$subject
		* @param string	$body
		* @param string	$attach
		* @return bool
		*/

		public function SendMail($to_address, $to_name = "", $subject, $body, $attach = "")
		{
			$this->mail->ClearAllRecipients();
			$this->mail->ClearAttachments();

			$this->mail->IsHTML(true);
			$this->mail->AddAddress($to_address, $to_name);
			$this->mail->Subject = $subject;
			$this->mail->MsgHTML($body);
			$this->mail->AddAttachment($attach);
			$this->mail->AltBody = "To view the message, please use an HTML compatible email viewer!";

			if (!$this->mail->Send())
			{
				$this->ErrorInfo = $this->mail->ErrorInfo;
				return false;
			}
			else
			{
				return true;
			}
		}
	}
?>