<?
	//-------------------------------------------
	// 7md 配對統計程式
	// Author: Sonic
	// Email:  sonic010739@gmail.com
	// July 2008
	//-------------------------------------------
	
	class Match_h
	{
		// 所有的會員選項
		var $options = "";
		
		// 關係
		// from 	: 選擇者
		// to   	: 被選擇者
		// single 	: true: 只有單邊，false: 相互都有選擇
		var $relations  = "";
		
		// 一個會員可以填寫幾個編號
		var $match_count = "";
		
		// initial
		function init()
		{
			$this->options = array();
			$this->relations = array();
		}
		
		function destory()
		{
			unset($this->options);
			unset($this->relations);
		}
		
		function push( $option )
		{
			array_push($this->options, $option);
		}
		
		// 配對所有會員
		function match_all()
		{
			$count = count($this->options);
			for ($i=0; $i<$count; $i++)
			{
				for ($j=0; $j<$this->match_count; $j++)
				{
					$this->options[$i]["no"];
					$index = $this->find_relations($this->options[$i][$j], $this->options[$i]["no"]);

					if ($index != -1)
					{
						$this->relations[$index]["single"] = false;
					}
					else
					{
						$new_relations["from"] = $this->options[$i]["no"];
						$new_relations["to"] = $this->options[$i][$j];
						$new_relations["single"] = true;
						
						array_push($this->relations, $new_relations);
					}
				}	
			}
		}
		
		// 找出是否已存在 relation 之中
		function find_relations($from, $to)
		{
			$count = count($this->relations);
			
			for($i=0; $i<$count; $i++)
			{
				if ($this->relations[$i]["from"] == $from && $this->relations[$i]["to"] == $to)
					return $i;
			}
			return -1;
		}
		
		// 列出所有的關係
		function show_relations()
		{
			$count = count($this->relations);
			
			for($i=0; $i<$count; $i++)
			{
				if ($this->relations[$i]["single"])
				{
					print sprintf("%d -> %d<br/>", $this->relations[$i]["from"], $this->relations[$i]["to"]);
				}
				else
				{
					print sprintf("<b>%d <-> %d</b><br/>", $this->relations[$i]["from"], $this->relations[$i]["to"]);
				}
			}
		}
		
		// 列出配對成功的編號
		function get_pairs()
		{
			$count = count($this->relations);
			for($i=0; $i<$count; $i++)
			{
				if (!$this->relations[$i]["single"]) {
					print sprintf("<b>%d <-> %d</b><br/>", $this->relations[$i]["from"], $this->relations[$i]["to"]);
				}	
			}
		}
		
		// 取得所有的關係
		function get_relations()
		{
			return $this->relations;
		}
		
		// 計算有多少種關係
		function get_relations_count()
		{
			return count($this->relations);
		}
	}
?>