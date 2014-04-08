	function Showplacelink() 
	{
		if (document.activitiesadd.place.value != -1)
		{
			document.getElementById("placelink").innerHTML = "<a href=\"index.php?act=place&sel=detail&pid=" + document.activitiesadd.place.value + "\" target=\"new\">詳情</a>"; 
		}
		else
		{
			document.getElementById("placelink").innerText = "未選擇";
		}
	}	
	
	function checkactadd(activitiesadd)
	{
		if (activitiesadd.name.value == "")
		{
			alert("活動名稱未填寫");
			activitiesadd.name.focus();
			return false;
		}
		else if (activitiesadd.name.value.length > 30)
		{
			alert("活動名稱不可大於30個字");
			activitiesadd.name.focus();
			return false;
		}
		
		if (activitiesadd.act_date.value == "")
		{
			alert("活動日期未選擇");
			activitiesadd.act_date.focus();
			return false;
		}
		
		if (activitiesadd.act_time_hour.value == -1)
		{
			alert("活動時間未選擇");
			activitiesadd.act_time_hour.focus();
			return false;
		}
		
		if (activitiesadd.act_time_minute.value == -1)
		{
			alert("活動時間未選擇");
			activitiesadd.act_time_minute.focus();
			return false;
		}
		
		if (activitiesadd.place.value == -1)
		{
			alert("活動場地未選擇");
			activitiesadd.place.focus();
			return false;
		}
		
		if (activitiesadd.topic.value == -1)
		{
			alert("活動主題未選擇");
			activitiesadd.topic.focus();
			return false;
		}
		
		if (activitiesadd.group.value == -1)
		{
			alert("活動族群未選擇");
			activitiesadd.group.focus();
			return false;
		}
		
		if (activitiesadd.sex_limit[0].checked)
		{
			if (activitiesadd.male_limit.value == "")
			{
				alert("男性人數限制未填寫");
				activitiesadd.male_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.male_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.male_limit.value = "";
					activitiesadd.male_limit.focus();
					return false;
				}
			}
			
			if (activitiesadd.female_limit.value == "")
			{
				alert("女性人數限制未填寫");
				activitiesadd.female_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.female_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.female_limit.value = "";
					activitiesadd.female_limit.focus();
					return false;
				}
			}
		}
		else if (activitiesadd.sex_limit[1].checked)
		{
			if (activitiesadd.people_limit.value == "")
			{
				alert("人數限制未填寫");
				activitiesadd.people_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.people_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.people_limit.value = "";
					activitiesadd.people_limit.focus();
					return false;
				}
			}
		}
		else if (activitiesadd.sex_limit[2].checked)
		{
			//nothing
		}
		else
		{
			alert("活動人數限制未選擇");
			return false;
		}
		
		if (activitiesadd.age_limit[0].checked)
		{			
			if (activitiesadd.age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.age_lb.value = "";
					activitiesadd.age_lb.focus();
					return false;
				}
			}
			
			if (activitiesadd.age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.age_ub.value = "";
					activitiesadd.age_ub.focus();
					return false;
				}
			}
			
			if (activitiesadd.age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesadd.age_lb.focus();
				return false;
			}
			
			if (activitiesadd.age_ub.value - activitiesadd.age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesadd.age_lb.focus();
				return false;
			}
		}
		else if (activitiesadd.age_limit[1].checked)
		{
			if (activitiesadd.male_age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.male_age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.male_age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.male_age_lb.value = "";
					activitiesadd.male_age_lb.focus();
					return false;
				}
			}
			
			if (activitiesadd.male_age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.male_age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.male_age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.male_age_ub.value = "";
					activitiesadd.male_age_ub.focus();
					return false;
				}
			}
			
			if (activitiesadd.female_age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.female_age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.female_age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.female_age_lb.value = "";
					activitiesadd.female_age_lb.focus();
					return false;
				}
			}
			
			if (activitiesadd.female_age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesadd.female_age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.female_age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.female_age_ub.value = "";
					activitiesadd.female_age_ub.focus();
					return false;
				}
			}
			
			if (activitiesadd.male_age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesadd.male_age_lb.focus();
				return false;
			}
			
			if (activitiesadd.female_age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesadd.female_age_lb.focus();
				return false;
			}
			
			if (activitiesadd.male_age_ub.value - activitiesadd.male_age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesadd.male_age_lb.focus();
				return false;
			}
			
			if (activitiesadd.female_age_ub.value - activitiesadd.female_age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesadd.female_age_lb.focus();
				return false;
			}
		}
		else if (activitiesadd.age_limit[2].checked)
		{
			//nothing
		}
		else
		{
			alert("年齡限制未選擇");
			return false;
		}
		
		if (activitiesadd.charge_limit[0].checked)
		{
			if (activitiesadd.charge.value == "")
			{
				alert("活動費用未填寫");
				activitiesadd.charge.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.charge.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.charge.value = "";
					activitiesadd.charge.focus();
					return false;
				}
				else if (activitiesadd.charge.value < 30)
				{
					alert("活動費用最低為30元");
					activitiesadd.charge.value = "";
					activitiesadd.charge.focus();
					return false;
				}
				else if (activitiesadd.charge.value > 20000)
				{
					alert("活動費用最高為2萬元");
					activitiesadd.charge.value = "";
					activitiesadd.charge.focus();
					return false;
				}			
			}
		}
		else if (activitiesadd.charge_limit[1].checked)
		{
			if (activitiesadd.male_charge.value == "")
			{
				alert("活動費用未填寫");
				activitiesadd.male_charge.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.male_charge.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.male_charge.value = "";
					activitiesadd.male_charge.focus();
					return false;
				}
				else if (activitiesadd.male_charge.value < 30)
				{
					alert("活動費用最低為30元");
					activitiesadd.male_charge.value = "";
					activitiesadd.male_charge.focus();
					return false;
				}
				else if (activitiesadd.male_charge.value > 20000)
				{
					alert("活動費用最高為2萬元");
					activitiesadd.male_charge.value = "";
					activitiesadd.male_charge.focus();
					return false;
				}			
			}
			
			if (activitiesadd.female_charge.value == "")
			{
				alert("活動費用未填寫");
				activitiesadd.female_charge.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesadd.female_charge.value)) 
				{
					alert("只能填寫數字");
					activitiesadd.female_charge.value = "";
					activitiesadd.female_charge.focus();
					return false;
				}
				else if (activitiesadd.female_charge.value < 30)
				{
					alert("活動費用最低為30元");
					activitiesadd.female_charge.value = "";
					activitiesadd.female_charge.focus();
					return false;
				}
				else if (activitiesadd.female_charge.value > 20000)
				{
					alert("活動費用最高為2萬元");
					activitiesadd.female_charge.value = "";
					activitiesadd.female_charge.focus();
					return false;
				}			
			}
		}
		else
		{
			alert("活動費用未選擇");
			return false;
		}
		
		/*if (activitiesadd.decription.value == "")
		{
			alert("活動敘述未填寫");
			activitiesadd.decription.focus();
			return false;
		}
		*/
		
		if (activitiesadd.use_discount[0].checked && activitiesadd.charge.value < 235)
		{
			alert("若選擇使用活動優惠折扣，活動費用不可低於235元!");
			activitiesadd.charge.focus();
			return false;
		}
		
		activitiesadd.Submit.disabled = true;
		
		if (confirm('確定要新增活動?')) 
		{
			return true;
		}
		else 
		{
			activitiesadd.Submit.disabled = false;
			return false;
		}
	}