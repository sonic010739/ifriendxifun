	function checkactmodify(activitiesmodify)
	{
		if (activitiesmodify.name.value == "")
		{
			alert("活動名稱未填寫");
			activitiesmodify.name.focus();
			return false;
		}
		else if (activitiesmodify.name.value.length > 30)
		{
			alert("活動名稱不可大於30個字");
			activitiesmodify.name.focus();
			return false;
		}
		
		if (activitiesmodify.topic.value == -1)
		{
			alert("活動主題未選擇");
			activitiesmodify.topic.focus();
			return false;
		}
		
		if (activitiesmodify.group.value == -1)
		{
			alert("活動族群未選擇");
			activitiesmodify.group.focus();
			return false;
		}
		
		if (activitiesmodify.sex_limit[0].checked)
		{
			if (activitiesmodify.male_limit.value == "")
			{
				alert("男性人數限制未填寫");
				activitiesmodify.male_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.male_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.male_limit.value = "";
					activitiesmodify.male_limit.focus();
					return false;
				}
			}
			
			if (activitiesmodify.female_limit.value == "")
			{
				alert("女性人數限制未填寫");
				activitiesmodify.female_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.female_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.female_limit.value = "";
					activitiesmodify.female_limit.focus();
					return false;
				}
			}
		}
		else if (activitiesmodify.sex_limit[1].checked)
		{
			if (activitiesmodify.people_limit.value == "")
			{
				alert("人數限制未填寫");
				activitiesmodify.people_limit.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.people_limit.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.people_limit.value = "";
					activitiesmodify.people_limit.focus();
					return false;
				}
			}
		}
		else if (activitiesmodify.sex_limit[2].checked)
		{
			//nothing
		}
		else
		{
			alert("活動人數限制未選擇");
			return false;
		}
		
		if (activitiesmodify.age_limit[0].checked)
		{			
			if (activitiesmodify.age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.age_lb.value = "";
					activitiesmodify.age_lb.focus();
					return false;
				}
			}
			
			if (activitiesmodify.age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.age_ub.value = "";
					activitiesmodify.age_ub.focus();
					return false;
				}
			}
			
			if (activitiesmodify.age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesmodify.age_lb.focus();
				return false;
			}
			
			if (activitiesmodify.age_ub.value - activitiesmodify.age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesmodify.age_lb.focus();
				return false;
			}
		}
		else if (activitiesmodify.age_limit[1].checked)
		{
			if (activitiesmodify.male_age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.male_age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.male_age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.male_age_lb.value = "";
					activitiesmodify.male_age_lb.focus();
					return false;
				}
			}
			
			if (activitiesmodify.male_age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.male_age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.male_age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.male_age_ub.value = "";
					activitiesmodify.male_age_ub.focus();
					return false;
				}
			}
			
			if (activitiesmodify.female_age_lb.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.female_age_lb.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.female_age_lb.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.female_age_lb.value = "";
					activitiesmodify.female_age_lb.focus();
					return false;
				}
			}
			
			if (activitiesmodify.female_age_ub.value == "")
			{
				alert("年齡限制未填寫");
				activitiesmodify.female_age_ub.focus();
				return false;
			}
			else
			{
				regularExpression = /^[0-9]+$/;
			
				if (!regularExpression.test(activitiesmodify.female_age_ub.value)) 
				{
					alert("只能填寫數字");
					activitiesmodify.female_age_ub.value = "";
					activitiesmodify.female_age_ub.focus();
					return false;
				}
			}
			
			if (activitiesmodify.male_age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesmodify.male_age_lb.focus();
				return false;
			}
			
			if (activitiesmodify.female_age_lb.value < 18)
			{
				alert("年齡限制不可低於18歲");
				activitiesmodify.female_age_lb.focus();
				return false;
			}
			
			if (activitiesmodify.male_age_ub.value - activitiesmodify.male_age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesmodify.male_age_lb.focus();
				return false;
			}
			
			if (activitiesmodify.female_age_ub.value - activitiesmodify.female_age_lb.value < 0)
			{
				alert("年齡限制設定錯誤");
				activitiesmodify.female_age_lb.focus();
				return false;
			}
		}
		else if (activitiesmodify.age_limit[2].checked)
		{
			//nothing
		}
		else
		{
			alert("年齡限制未選擇");
			return false;
		}		
		
		/*
		if (activitiesmodify.decription.value == "")
		{
			alert("活動敘述未填寫");
			activitiesmodify.decription.focus();
			return false;
		}
		*/
		
		if (confirm('確定要修改活動?')) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}