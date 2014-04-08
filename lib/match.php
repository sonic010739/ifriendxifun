<?
	//-------------------------------------------
	// 7md �t��έp�{��
	// Author: Sonic
	// Email:  sonic010739@gmail.com
	// July 2008
	//-------------------------------------------
	
	class Match_h
	{
		// �Ҧ����|���ﶵ
		var $options = "";
		
		// ���Y
		// from 	: ��ܪ�
		// to   	: �Q��ܪ�
		// single 	: true: �u������Afalse: �ۤ��������
		var $relations  = "";
		
		// �@�ӷ|���i�H��g�X�ӽs��
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
		
		// �t��Ҧ��|��
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
		
		// ��X�O�_�w�s�b relation ����
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
		
		// �C�X�Ҧ������Y
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
		
		// �C�X�t�令�\���s��
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
		
		// ���o�Ҧ������Y
		function get_relations()
		{
			return $this->relations;
		}
		
		// �p�⦳�h�ֺ����Y
		function get_relations_count()
		{
			return count($this->relations);
		}
	}
?>