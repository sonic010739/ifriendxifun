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
		form = document.membermodify;
		
		var num = form.career.selectedIndex;		
			
		for(var ctr=0; ctr <= key[num].length; ctr++)
		{
			form.career_detail.options[ctr] = new Option(key[num][ctr],key[num][ctr]);
		}
		
		form.career_detail.length = key[num].length;
		
				// 選擇其他的產業類別
		if (num == "18") 
		{			
			form.career_other.disabled = false;
			form.career_other.style.display = "inline";
			form.career_other.focus();			
		}
		else 
		{
			form.career_other.disabled = true;
			form.career_other.style.display = "none";
		}		
	}
	
	function buildkey2()
	{	
		form = document.membermodify;
		
		if (form.career_detail.value == "其他相關業") {
			form.career_other.disabled = false;
			form.career_other.style.display = "";
			form.career_other.focus();
		} else {
			form.career_other.disabled = true;
			form.career_other.style.display = "none";
		}		
	}
	
	function MemberModPasswd()
	{		
		form = document.modifypassword;
		
		if (form.oldpasswd.value == "")
		{
			alert("請輸入舊密碼");
			form.oldpasswd.focus();
			return false;
		}
		
		if (form.newpasswd.value == "")
		{
			alert("請輸入新密碼");
			form.newpasswd.focus();
			return false;
		}
		else
		{
			regularExpression = /^[A-Za-z0-9]{6,12}$/;
			
			if (!regularExpression.test(form.newpasswd.value)) 
			{
				alert("新密碼格式不符");
				form.newpasswd.value = "";
				form.newpasswd.focus();
				return false;
			}
		}
		
		if (form.confirmnewpasswd.value == "")
		{
			alert("請再次輸入新密碼");
			form.confirmnewpasswd.focus();
			return false;
		}		
	
		if (form.confirmnewpasswd.value != form.newpasswd.value)
		{
			alert("密碼與密碼確認不符");
			form.newpasswd.value = "";
			form.confirmnewpasswd.value = "";
			form.newpassword.focus();
			return false;
		}
		
		if (form.passwordhint.value == "")
		{
			alert("請輸入新密碼提示");
			form.passwordhint.focus();
			return false;
		}
		
		if (confirm('確定要修改密碼?')) {
			return true;
		} else {
			return false;
		}
	}
	
	function MemberMod(Member)
	{
		if (Member.backupemail.value != "")
		{
			regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
			if (!regularExpression.test(Member.backupemail.value)) 
			{
				alert("請填正確格式的備用電子信箱");
				Member.backupemail.focus();
				return false;
			}
		}
		
		if (Member.msn.value == "")
		{
			alert("請填寫MSN或即時通");
			Member.msn.focus();
			return false;
		}
		else if (Member.msn.value != "")
		{
			regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
			if (!regularExpression.test(Member.msn.value)) 
			{
				alert("請填正確格式的MSN或即時通");
				Member.msn.focus();
				return false;
			}
		}
		
		if (Member.career.value == "其他" && Member.career_other.value == "")
		{
			alert("選擇其他職業類別，請右邊填寫您的職業");
			Member.career_other.focus();
			return false
		}
		else if (Member.career_detail.value == "其他相關業" && Member.career_other.value == "")
		{
			alert("選擇其他相關業，請右邊填寫您的職業");
			Member.career_other.focus();
			return false
		}
		
		if (Member.career_title.value == "")
		{
			alert("職務未填寫");
			Member.career_title.focus();
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
		
		if (checkboxs[checkboxs.length-1].checked && Member.interest_other.value == "")
		{
			alert("點選其他興趣，請填寫。");
			Member.interest_other.focus();
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
	
		if (confirm('確定要修改個人資料?')) {
			return true;
		} else {
			return false;
		}
	}

	function foo()
	{
		if (document.membermodify.interest_other.disabled == true)
		{
			document.membermodify.interest_other.disabled = false;
		}
		else
		{
			document.membermodify.interest_other.disabled = true;
			document.membermodify.interest_other.value = "";
		}
	}