<?
	require "smarty.lib.php";
	//include_once('lib/iFMailer.php');
	
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	$time_start = microtime_float();
	
	$mymail = new iFMailer();
	
	$to_address = "sonic010739@gmail.com";
	$to_name = "Sonic";
	
	//---------------------------------------------------------------
	// L1 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "謝朝任";
	$mailinfo["username"] = "sonic010739";
	$mailinfo["passwordhint"] = "1001個密碼";
	$mailinfo["activelink"] = "1001個密碼";
	
	$mymail->RegisterMail($to_address, $to_name, $mailinfo);

	/*
	//---------------------------------------------------------------
	// L2 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放介紹人名字";
	$mailinfo["intro_name"] = "這裡放被介紹人名字";
	$mailinfo["discount_link"] = "這裡放優惠連結";
	
	$mailinfo["act_topic"] = "這裡放活動主題";
	$mailinfo["act_date"] = "這裡放活動日期";
	$mailinfo["act_name"] = "這裡放活動名稱";
	$mailinfo["act_place"] = "這裡放活動地點";
	$mailinfo["decription"] = "這裡放活動詳情";
		
	$mymail->InviteMail($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L3 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	
	$mailinfo["act_name"] = "這裡放活動名稱";
	$mailinfo["act_date"] = "這裡放活動日期";
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";
	
	$mailinfo["ibon_deadline"] = "這裡放iBon繳費截止日";
	$mailinfo["ibon_code"] = "這裡放iBon繳費代碼";
	$mailinfo["iF_deadline"] = "這裡放iF繳費截止時間";
		
	$mymail->PayDeadlineMailA($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L4 
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	
	$mailinfo["act_name"] = "這裡放活動名稱";
	$mailinfo["act_date"] = "這裡放活動日期";
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";	
	
	$mailinfo["ibon_deadline"] = "這裡放iBon繳費截止日";
	$mailinfo["iF_deadline"] = "這裡放iF繳費截止時間";
		
	$mymail->PayDeadlineMailB($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L5 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "真實姓名";
	$mailinfo["act_date"] = "活動日期";
	$mailinfo["act_time"] = "活動時間";
	$mailinfo["act_topic"] = "活動主題";
	$mailinfo["act_name"] = "活動名稱";
	$mailinfo["ibon_code"] = "iBon繳費代碼";
	$mailinfo["ibon_paytime"] = "iBon付款時間";
	
	$mymail->PaidMail($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L7 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";

	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_topic"] = "這裡放活動主題";
	$mailinfo["act_place"] = "這裡放活動地點";	
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->ActNotifyMail($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L8 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["EOname"] = "這裡放EO真實姓名";
		
	$mymail->ActThanksMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L9 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";

	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_topic"] = "這裡放活動主題";
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->UserCancelMail($to_address, $to_name, $mailinfo);
	
	
	//---------------------------------------------------------------
	// L10
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
		
	$mymail->UserCloseAccuntMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L11 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";	
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_name"] = "這裡放活動名稱";	
		
	$mymail->ActCancelMailA($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L12 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->ActCancelMailB($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L13 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
		
	$mymail->GetCouponMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L14 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["username"] = "這裡放帳號名稱";
	$mailinfo["password"] = "這裡放臨時密碼";
		
	$mymail->ResetPasswordMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L15 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
		
	$mymail->UpgrdeEOMail($to_address, $to_name, $mailinfo);
	
	//---------------------------------------------------------------
	// L16 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->AddActMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L17
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";	
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->EOActNotifyMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L18 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";	
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->CancelActApplyMail($to_address, $to_name, $mailinfo);
	
	//---------------------------------------------------------------
	// L19 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";	
	
	$mailinfo["act_date"] = "這裡放活動日期";	
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";	
	$mailinfo["act_name"] = "這裡放活動名稱";
	
	$mymail->CancelActResultMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L20 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	$mailinfo["act_name"] = "這裡放活動名稱";
		
	$mymail->EOActThanksMail($to_address, $to_name, $mailinfo);	
	
	//---------------------------------------------------------------
	// L22 Done
	//---------------------------------------------------------------
	$mailinfo = array();
	$mailinfo["realname"] = "這裡放真實姓名";
	
	$mailinfo["act_name"] = "這裡放活動名稱";
	$mailinfo["act_date"] = "這裡放活動日期";
	$mailinfo["act_time"] = "這裡放活動時間";
	$mailinfo["act_place"] = "這裡放活動地點";
	
	$mailinfo["ibon_deadline"] = "這裡放iBon繳費截止日";
	$mailinfo["ibon_code"] = "這裡放iBon繳費代碼";
	$mailinfo["iF_deadline"] = "這裡放iF繳費截止時間";
	*/
		
	$mymail->PayDeadlineMailC($to_address, $to_name, $mailinfo);
	
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	
	echo "Did nothing in $time seconds\r\n";
	
?>