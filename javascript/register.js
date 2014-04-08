	var key = new Array(19);

	key[0] = new Array(1);
	key[1] = new Array(4);
	key[2] = new Array(6);
	key[3] = new Array(6);
	key[4] = new Array(4);
	key[5] = new Array(7);
	key[6] = new Array(10);
	key[7] = new Array(17);
	key[8] = new Array(5);
	key[9] = new Array(4);
	key[10] = new Array(6);
	key[11] = new Array(4);
	key[12] = new Array(4);
	key[13] = new Array(5);
	key[14] = new Array(3);
	key[15] = new Array(4);
	key[16] = new Array(1);
	key[17] = new Array(1);
	key[18] = new Array(1);

	key[0][0] = "請先選擇產業類別";

	key[1][0] = "批發業";
	key[1][1] = "零售業";
	key[1][2] = "傳直銷相關業";
	key[1][3] = "其他相關業";

	key[2][0] = "教育服務業";
	key[2][1] = "音樂舞蹈相關";
	key[2][2] = "學生";
	key[2][3] = "出版業";
	key[2][4] = "藝文相關業";
	key[2][5] = "其他相關業";

	key[3][0] = "電影業";
	key[3][1] = "廣播業";
	key[3][2] = "電視業";
	key[3][3] = "廣告行銷業";
	key[3][4] = "傳播經紀業";
	key[3][5] = "其他相關業";

	key[4][0] = "旅遊休閒服務業";
	key[4][1] = "運動業";
	key[4][2] = "住宿服務業";
	key[4][3] = "其他相關業";

	key[5][0] = "軟體與網路相關業";
	key[5][1] = "電信與通訊業";
	key[5][2] = "消費型電子與電腦業";
	key[5][3] = "光電業";
	key[5][4] = "電子零件組相關業";
	key[5][5] = "半導體產業";
	key[5][6] = "其他相關業";

	key[6][0] = "人力仲介代徵業";
	key[6][1] = "租賃業";
	key[6][2] = "餐飲業";
	key[6][3] = "汽機車服務或維修業";
	key[6][4] = "婚紗攝影業";
	key[6][5] = "美容美髮業";
	key[6][6] = "徵信業";
	key[6][7] = "保全樓管相關";
	key[6][8] = "自由業";
	key[6][9] = "其他相關業";


	key[7][0] = "紡織與紡織品製造業";
	key[7][1] = "食品菸草及飲料製造業";
	key[7][2] = "各類鞋業";
	key[7][3] = "家具及裝設品製造業";
	key[7][4] = "紙製品製造業";
	key[7][5] = "印刷相關業";
	key[7][6] = "化學相關製造業";
	key[7][7] = "石油與煤製品製造業";
	key[7][8] = "塑膠與橡膠製品製造業";
	key[7][9] = "非金屬礦物製品製造業";
	key[7][10] = "金屬相關製造業";
	key[7][11] = "機械設備製造修配業";
	key[7][12] = "運輸工具製造業";
	key[7][13] = "精密儀器業";
	key[7][14] = "醫療器材相關業";
	key[7][15] = "育樂用品製造業";
	key[7][16] = "其他相關業";

	key[8][0] = "林場伐木業";
	key[8][1] = "漁撈水產養殖業";
	key[8][2] = "水電能源供應業";
	key[8][3] = "農產畜牧相關業";
	key[8][4] = "其他相關業";

	key[9][0] = "運輸相關業";
	key[9][1] = "倉儲與運輸輔助業";
	key[9][2] = "郵政與快遞業";
	key[9][3] = "其他相關業";

	key[10][0] = "政府機關相關業";
	key[10][1] = "軍警消相關";
	key[10][2] = "政治機構相關業";
	key[10][3] = "宗教團體與職業組織";
	key[10][4] = "社會福利服務業";
	key[10][5] = "其他相關業";

	key[11][0] = "金融機構業";
	key[11][1] = "投資理財業";
	key[11][2] = "保險業";
	key[11][3] = "其他相關業";

	key[12][0] = "法律服務業";
	key[12][1] = "會計服務業";
	key[12][2] = "研發與顧問業";
	key[12][3] = "其他相關業";

	key[13][0] = "建築或土木工程業";
	key[13][1] = "建物裝修或空調工程業";
	key[13][2] = "建築規劃與設計業";
	key[13][3] = "不動產業";
	key[13][4] = "其他相關業";

	key[14][0] = "醫療服務業";
	key[14][1] = "環境衛生相關業";
	key[14][2] = "其他相關業";

	key[15][0] = "能源開採業";
	key[15][1] = "其他礦業";
	key[15][2] = "土石採取業";
	key[15][3] = "其他相關業";

	key[16][0] = "家管";

	key[17][0] = "待業中";

	key[18][0] = "請在右邊填寫";

	function buildkey()
	{
		var num = document.NewReg.career.selectedIndex;

		for(var ctr=0; ctr <= key[num].length; ctr++)
		{
			document.NewReg.career_detail.options[ctr] = new Option(key[num][ctr],key[num][ctr]);
		}

		document.NewReg.career_detail.length = key[num].length;

		// 選擇其他的產業類別
		if (num == "18")
		{
			document.NewReg.career_other.disabled = false;
			document.NewReg.career_other.style.display = "inline";
			document.NewReg.career_other.focus();
		}
		else
		{
			document.NewReg.career_other.disabled = true;
			document.NewReg.career_other.style.display = "none";
		}
	}

	function buildkey2()
	{
		if (document.NewReg.career_detail.value == "其他相關業")
		{
			document.NewReg.career_other.disabled = false;
			document.NewReg.career_other.style.display = "";
			document.NewReg.career_other.focus();
		}
		else
		{
			document.NewReg.career_other.disabled = true;
			document.NewReg.career_other.style.display = "none";
		}
	}

	function checkNewReg(NewReg)
	{
		if (NewReg.username.value == "")
		{
			alert("帳號未填寫");
			NewReg.username.focus();
			return false;
		}
		else
		{
			regularExpression = /^[a-z]{1}[a-z0-9]{5,11}$/;

			if (!regularExpression.test(NewReg.username.value))
			{
				alert("帳號格式不符");
				NewReg.username.focus();
				return false;
			}
		}

		if (document.NewReg.username_check.value != "OK")
		{
			alert("帳號未自動檢查，請手動點選檢查。");
			NewReg.username.focus();
		}

		if (NewReg.password.value == "")
		{
			alert("密碼未填寫");
			NewReg.password.focus();
			return false;
		}
		else
		{
			regularExpression = /^[A-Za-z0-9]{6,12}$/;

			if (!regularExpression.test(NewReg.password.value))
			{
				alert("密碼格式不符");
				NewReg.password.value = "";
				NewReg.password.focus();
				return false;
			}
		}

		if (NewReg.password.value == NewReg.username.value)
		{
			alert("帳號與密碼不可相同");
			return false;
		}
		else if (NewReg.confirmpassword.value != NewReg.password.value)
		{
			alert("密碼與密碼確認不符");
			NewReg.password.value = "";
			NewReg.confirmpassword.value = "";
			NewReg.password.focus();
			return false;
		}

		if (NewReg.passwordhint.value == "")
		{
			alert("密碼提示未填寫");
			NewReg.passwordhint.focus();
			return false;
		}
		else if (NewReg.confirmpassword.value == NewReg.passwordhint.value)
		{
			alert("密碼提示不可與密碼相同");
			NewReg.passwordhint.value = "";
			NewReg.passwordhint.focus();
			return false;
		}

		if (NewReg.email.value == "")
		{
			alert("電子郵件未填寫");
			NewReg.email.focus();
			return false;
		}
		else
		{
			regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
			if (!regularExpression.test(NewReg.email.value))
			{
				alert("請填正確格式的電子信箱");
				NewReg.email.focus();
				return false;
			}
		}

		if (document.NewReg.email_check.value != "OK")
		{
			alert("未自動檢查電子信箱，請手動點選檢查");
			NewReg.email.focus();
		}

		if (NewReg.backupemail.value != "")
		{
			regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
			if (!regularExpression.test(NewReg.backupemail.value))
			{
				alert("請填正確格式的備用電子信箱");
				NewReg.backupemail.focus();
				return false;
			}
		}

		if (NewReg.realname.value == "")
		{
			alert("真實姓名未填寫");
			NewReg.realname.focus();
			return false;
		}
		else if (NewReg.realname.value.length < 2 || NewReg.realname.value.length > 4)
		{
			alert("真實姓名長度不符合限制。");
			NewReg.realname.focus();
			return false;
		}

		if (NewReg.sex.value == -1)
		{
			alert("性別未選擇");
			NewReg.sex.focus();
			return false;
		}

		if (NewReg.nickname.value == "")
		{
			alert("暱稱未填寫");
			NewReg.nickname.focus();
			return false
		}

		if (NewReg.birth_year.value == -1)
		{
			alert("生日日期未選擇");
			NewReg.birth_year.focus();
			return false
		}

		if (NewReg.birth_month.value == -1)
		{
			alert("生日日期未選擇");
			NewReg.birth_month.focus();
			return false
		}

		if (NewReg.birth_day.value == -1)
		{
			alert("生日日期未選擇");
			NewReg.birth_day.focus();
			return false
		}

		if (NewReg.constellation.value == -1)
		{
			alert("星座未選擇");
			NewReg.constellation.focus();
			return false
		}

		if (NewReg.tel1.value == "" || NewReg.tel2.value == "" || NewReg.tel3.value == "")
		{
			alert("電話未填寫");
			NewReg.tel1.focus();
			return false
		}

		if (NewReg.msn.value == "")
		{
			alert("MSN或即時通未填寫");
			NewReg.msn.focus();
			return false;
		}
		else
		{
			regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
			if (!regularExpression.test(NewReg.msn.value))
			{
				alert("請填正確格式的MSN或即時通");
				NewReg.msn.focus();
				return false;
			}
		}

		if (NewReg.education.value == -1)
		{
			alert("教育程度未選擇");
			NewReg.education.focus();
			return false
		}

		if (NewReg.top_education.value == "")
		{
			alert("最高學歷未填寫");
			NewReg.top_education.focus();
			return false
		}

		if (NewReg.career.value == -1)
		{
			alert("職業類別未選擇");
			NewReg.career.focus();
			return false
		}


		if (NewReg.career.value == "其他" && NewReg.career_other.value == "")
		{
			alert("選擇其他職業類別，請右邊填寫您的職業");
			NewReg.career_other.focus();
			return false
		}
		else if (NewReg.career_detail.value == "其他相關業" && NewReg.career_other.value == "")
		{
			alert("選擇其他相關業，請右邊填寫您的職業");
			NewReg.career_other.focus();
			return false
		}

		if (NewReg.career_title.value == "")
		{
			alert("職務未填寫");
			NewReg.career_title.focus();
			return false
		}

		var count = 0;
		var checkboxs = document.getElementsByName('interest[]');

		for(var i=0; i<checkboxs.length; i++)
		{
			if (checkboxs[i].checked)
			{
				count++;
			}
		}

		if (checkboxs[checkboxs.length-1].checked && NewReg.interest_other.value == "")
		{
			alert("若點選其他興趣，請填寫。");
			NewReg.interest_other.focus();
			return false;
		}
		if (count == 0)
		{
			alert('請至少選擇一項興趣興趣');
			return false;
		}

		if (count > 3)
		{
			alert('興趣最多只能選三項');
			return false;
		}

		if (confirm('您所輸入的電子信箱為: ' + NewReg.email.value + " 是否正確?")) 
		{
			if (confirm('以上所填寫的資料均為正確?')) 
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
		else 
		{
			return false;
		}
	}

	function foo()
	{
		if (document.NewReg.interest_other.disabled == true)
		{
			document.NewReg.interest_other.disabled = false;
		}
		else
		{
			document.NewReg.interest_other.disabled = true;
			document.NewReg.interest_other.value = "";
		}
	}

	function CheckUsername()
	{
		var obj = document.getElementById("username_result");
		document.NewReg.username.value = document.NewReg.username.value.toLowerCase();
		document.NewReg.username_check.value = "";

		regularExpression = /^[a-z]{1}[a-z0-9]{5,11}$/;
		if (!regularExpression.test(document.NewReg.username.value))
		{
			obj.innerHTML = "<font color=red>您輸入的會員帳號格式不符</font>";
			document.NewReg.username.value = "";
			return;
		}

		var username = document.NewReg.username.value;

		var url = "register.act.php?&act=checkuser&username=" + username;
		var myXMLHttpRequest = false;

		if (window.XMLHttpRequest) {
			myXMLHttpRequest = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			myXMLHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
		} else {
			alert('您的瀏覽器不支援 AJAX');
		}

		if (myXMLHttpRequest)
		{
			myXMLHttpRequest.open("GET", url);
			myXMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

			myXMLHttpRequest.onreadystatechange = function()
			{
				if (myXMLHttpRequest.readyState == 4 && myXMLHttpRequest.status == 200)
				{
					if (myXMLHttpRequest.responseText == "OK")
					{
						obj.innerHTML = "<font color=green>您輸入的會員可以使用。</font>"
						document.NewReg.username_check.value = "OK";
					}
					else if (myXMLHttpRequest.responseText == "Used")
					{
						document.NewReg.username.value = "";
						obj.innerHTML = "<font color=red>您輸入的會員帳號 " + username + "已經被使用，請選擇其他的帳號</font>"
					}
				}
			}
			obj.innerHTML = "<img src=\"./images/loading.gif\">";
			myXMLHttpRequest.send(url);
		}
	}

	function CheckEmail()
	{
		var obj = document.getElementById("email_result");
		var email = document.NewReg.email.value;
		document.NewReg.email_check.value = "";

		regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
		if (!regularExpression.test(document.NewReg.email.value))
		{
			obj.innerHTML = "<font color=red>您輸入的電子信箱：" + email + "格式不符</font><br/>";
			document.NewReg.email.value = "";
			return;
		}

		document.NewReg.email_check.value = "OK";

		var url = "register.act.php?&act=checkemail&email=" + email;
		var xhr = false;

		if (window.XMLHttpRequest) {
			xhr = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			xhr = new ActiveXObject("Microsoft.XMLHTTP");
		} else {
			alert('您的瀏覽器不支援 AJAX');
		}

		if (xhr)
		{
			xhr.open("GET", url);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

			xhr.onreadystatechange = function()
			{
				if (xhr.readyState == 4 && xhr.status == 200)
				{
					if (xhr.responseText == "OK")
					{
						obj.innerHTML = "<font color=green>您輸入的電子信箱可以使用。</font><br/>";
						document.NewReg.email_check.value = "OK";
					}
					else if (xhr.responseText == "Used")
					{
						document.NewReg.email.value = "";
						obj.innerHTML = "<font color=red>" + email + "已經被使用，請選擇其他的電子信箱</font><br/>"
					}
				}
			}
			obj.innerHTML = "<img src=\"./images/loading.gif\">";
			xhr.send(url);
		}
	}

	function CheckEmail2()
	{
		var email = document.NewReg.email.value;
		var email2 = document.NewReg.email2.value;

		if (email != email2)
		{
			document.NewReg.email.focus();
			document.NewReg.email_check.value = "";
			alert('兩次填寫的電子信箱並不相同，請檢查您輸入的email!');
		}
	}

	function CheckBirth()
	{
		year = document.NewReg.birth_year.value;
		month = document.NewReg.birth_month.value;
		day = document.NewReg.birth_day.value;

		if (year != -1 && month != -1 && day != -1)
		{
			var birthday = new Date( year, month-1, day);
			var now = new Date();

			if (now.getFullYear() - year < 18)
			{
				alert("未滿18歲無法註冊");
				document.NewReg.birth_year.value = -1;
				document.NewReg.birth_month.value = -1;
				document.NewReg.birth_day.value = -1;
			}

			if ( year != birthday.getFullYear() || month != birthday.getMonth()+1 || day != birthday.getDate())
			{
				alert("不是正確的出生日期");
				document.NewReg.birth_day.value = -1;
			}
			else
			{
				var arr=[];
				arr.push(["魔羯座",new Date(year, 0, 1,0,0,0)])
				arr.push(["水瓶座",new Date(year, 0,20,0,0,0)])
				arr.push(["雙魚座",new Date(year, 1,19,0,0,0)])
				arr.push(["牡羊座",new Date(year, 2,21,0,0,0)])
				arr.push(["金牛座",new Date(year, 3,21,0,0,0)])
				arr.push(["雙子座",new Date(year, 4,21,0,0,0)])
				arr.push(["巨蟹座",new Date(year, 5,22,0,0,0)])
				arr.push(["獅子座",new Date(year, 6,23,0,0,0)])
				arr.push(["處女座",new Date(year, 7,23,0,0,0)])
				arr.push(["天秤座",new Date(year, 8,23,0,0,0)])
				arr.push(["天蠍座",new Date(year, 9,23,0,0,0)])
				arr.push(["射手座",new Date(year,10,22,0,0,0)])
				arr.push(["魔羯座",new Date(year,11,22,0,0,0)])

				for (var i=arr.length-1; i >= 0; i--)
				{
					if (birthday >= arr[i][1])
					{
						document.NewReg.constellation.value = arr[i][0];
						break;
					}
				}
			}
		}
	}

	function CheckTel1()
	{
		if (document.NewReg.tel1.value.length >= 4)
			document.NewReg.tel2.focus();
	}

	function CheckTel2()
	{
		if (document.NewReg.tel2.value.length >= 3)
			document.NewReg.tel3.focus();
	}

