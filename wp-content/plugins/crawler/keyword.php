<?php
function crawler_keyword(){
		global $wpdb;
		
		$arrCondition = array(
			'word_status' => 1,
			'word_level' => 1,
			'is_crawler' => 1
		);
		$match = [
            '', 'a', 'b', 'c', 'd', 'g', 'h','k', 'l', 'm', 'n','p', 'q', 'r', 's', 't', 'v', '2', '3', '4', '5', '6', '7'
        ];
		$strWhere = builtWhere($arrCondition);
		$word_crawler = $wpdb->get_row( "SELECT * FROM wp_word WHERE 1 = 1 " . $strWhere . " LIMIT 1");
		$count_number = count(explode(" ",$word_crawler->word_name));
		if($count_number > 7){
			$wpdb->update( 'wp_word',
				array(
					'is_crawler'    => 1
				),
				array( 
					'word_id'    => $word_crawler->word_id
				)
			);
			crawler_keyword();
		}
		$strWhere = builtWhere($arrCondition);
		if(!empty($word_crawler)){
			$name = $word_crawler->word_name;
			foreach ($match as $key => $value) {
				if($key == 0){
					$url = 'http://www.google.com/complete/search?output=search&client=chrome&q=' . rawurlencode($name) . '&hl=vi&gl=vn';
					insertKeyword($url,$word_crawler);
				}else{
					for ($i = 0; $i < 2; $i++) {
						if ($i == 0) {
							$key_match = $name . ' ' . $value;
						} else {
							$key_match = $value . ' ' . $name;
						}
						$url = 'http://www.google.com/complete/search?output=search&client=chrome&q=' . rawurlencode($key_match) . '&hl=vi&gl=vn';
						insertKeyword($url,$word_crawler);
					}
				}
			}
			$wpdb->update( 'wp_word',
				array(
					'is_crawler'    => 1
				),
				array( 
					'word_id'    => $word_crawler->word_id
				)
			);
			crawler_keyword();
		}else{
			exit;
		}		
	}
	
	function insertKeyword($url,$word_crawler){
		global $wpdb;
		$content = curl($url);
		$arr_keyword = json_decode($content);
		if(empty($arr_keyword[1])){	
			sleep(10);
			return;
		}
		$strSql = "INSERT INTO wp_word (word_name, word_slug, word_parent, word_level, is_crawler, word_status) VALUE ";
		$level = $word_crawler->word_level + 1;

		foreach($arr_keyword[1] as $keyword){
			$check_word = $wpdb->get_row( "SELECT * FROM wp_word WHERE word_slug = '" . sanitize_title($keyword) . "'");
			if(!empty($check_word)){
				continue;
			}
			$strSql .= "('" . $keyword . "','" . sanitize_title($keyword) . "'," . $word_crawler->word_id . "," . $level . ",0,1),";
		}
		$strSql = rtrim($strSql,",") . ";";

		$result = $wpdb->query($strSql);
		sleep(10);
		return;
	}
	function builtWhere($arrCondition){
		$strWhere = '';
		if (empty($arrCondition)){
			return $strWhere;
		}
		if(isset($arrCondition['word_status'])){
			$strWhere .= " AND word_status = " . $arrCondition['word_status'];
		}
		
		if(isset($arrCondition['word_level'])){
			$strWhere .= " AND word_level <= 2";
		}
		
		if(isset($arrCondition['is_crawler'])){
			$strWhere .= " AND is_crawler = 0";
		}
		return $strWhere;
	}