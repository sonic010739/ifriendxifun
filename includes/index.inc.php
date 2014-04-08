<?	
	//----------------------------------------
	// 根據使用的功能載入不同的頁面
	//----------------------------------------
		
	if (isset($_GET["act"]))
	{
		switch ($_GET["act"])
		{
			case "forum":
				$main_page = "forum.php";
				break;
			case "register":
				$main_page = "register.php";
				break;
			case "member":
				$main_page = "member.php";
				break;
			case "membercenter":
				$main_page = "membercenter.php";
				break;
			case "activities":
				$main_page = "activities.php";
				break;
			case "activitielist":
				$main_page = "activities.list.php";
				break;
			case "activitieEO":
				$main_page = "activities.EO.php";
				break;
			case "activitiemanage":
				$main_page = "activities.manage.php";
				break;
			case "activitiesjoin":
				$main_page = "activities.join.php";
				break;
			case "activitiesmatch":
				$main_page = "activities.match.php";
				break;
			case "place":
				$main_page = "place.php";
				break;
			case "group":
				$main_page = "group.php";
				break;
			case "topic":
				$main_page = "topic.php";
				break;
			case "ibon":
				$main_page = "ibon.php";
				break;
			case "coupon":
				$main_page = "coupon.php";
				break;
			case "msg":
				$main_page = "msg.php";
				break;
			case "statistics":
				$main_page = "statistics.php";
				break;
			case "layout":
				$main_page = "layout.php";
				break;
			case "upload":
				$main_page = "upload.php";
				break;
			default :
				$main_page = "error.php";
		}
	}
	else
	{
		$main_page="main.php";
	}
		
	include $main_page;
?>