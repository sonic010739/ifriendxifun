<?
	//-------------------------------------------
	// 變數檢查程式 - 避免 sql injection
	// Author: Sonic
	// Email:  sonic010739@gmail.com
	// Auguest 2008
	//-------------------------------------------
	
	class VarCheck_h
	{
		var $error = "";
		
		// initial
		function init()
		{
			$this->error = "";
		}
		
		function destory()
		{
			unset($this->error);
		}
		
		function IsError()
		{
			if ($this->error == "")
			{
				return false;
			}
			else
			{
				return true;
			}
		}		
		
		function GetError()
		{
			return $this->error;
		}
		
		function CheckVar($var, $type, $sign)
		{
			/*
			http://tw2.php.net/manual/en/function.gettype.php
			
			settype() 
			is_array() 
			is_bool() 
			is_float() 
			is_int() 
			is_null() 
			is_numeric() 
			is_object() 
			is_resource() 
			is_scalar() 
			*/
		}		
	}
?>