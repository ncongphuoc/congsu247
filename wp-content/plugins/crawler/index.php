<?php
/*
Plugin Name: Crawler
Plugin URI: http://www.wpdance.com/
Description: Crawler From Cp7
Author: Cp7
Version: 2.0.1
*/
include 'shortcode.php';
include 'simple_html_dom.php';
include 'curl.php';
include 'keyword.php';
include (ABSPATH . 'wp-admin/includes/image.php');

	function crawler() { 
		global $wpdb;
        
		$upload_dir = wp_upload_dir();
		if(!file_exists($upload_dir['path'])) 
			wp_mkdir_p($upload_dir['path']);
		
		$current_user = wp_get_current_user();
		$arr_data['post_author'] = $current_user->ID;
		$arr_data['post_excerpt'] = '';
		$arr_data['comment_status'] = 'open';
		$arr_data['post_password'] = '';
		$arr_data['to_ping'] = '';
		$arr_data['pinged'] = '';
		$arr_data['post_content_filtered'] = '';  
		$arr_data['post_parent'] = 0;  
		$arr_data['menu_order'] = 0;  
		$arr_data['comment_count'] = 0;
		$arr_data['post_status'] = 'publish';
		$arr_data['ping_status'] = 'open';
		$arr_data['guid'] = '';
		$arr_data['post_mime_type'] = '';
		$arr_data['post_type'] = 'post';
		
		$arr_cate = [
            2 => 'http://afamily.vn/an-ngon.chn',
			10 => 'http://kenh14.vn/fashion.chn',
            12 => 'http://afamily.vn/xa-hoi.chn',
			9 => 'https://www.ivivu.com/blog/category/viet-nam/',
            6 => 'http://afamily.vn/suc-khoe.chn',
            8 => 'http://www.khuyenmaivui.com/',
            7 => 'http://afamily.vn/dep.chn',
            13 => 'http://genk.vn/thu-thuat.chn',
            15 => 'http://afamily.vn/hau-truong.chn',
            14 => 'http://vietbao.vn/Suc-khoe/Suc-khoe-Me-va-Be',
        ];
        foreach ($arr_cate as $cate_id => $strURL) {
			switch($cate_id){
				case 2:
				case 6:
				case 7:
					$count_post = get_content_afamily($cate_id,$strURL,$arr_data,20);
					break;
				case 12:
				case 15:
					$count_post = get_content_afamily($cate_id,$strURL,$arr_data,2);
					break;
				case 9:
					$count_post = get_content_ivivu($cate_id,$strURL,$arr_data);
					break;
				case 8:
					$count_post = get_content_khuyenmaivui($cate_id,$strURL,$arr_data);
					break;
				case 13:
					$count_post = get_content_genk($cate_id,$strURL,$arr_data);
					break;
				case 10:
					$count_post = get_content_kenh14($cate_id,$strURL,$arr_data);
					break;
				case 14:
					$count_post = get_content_vietbao($cate_id,$strURL,$arr_data);
					break;
			}
						
			$term_count = $wpdb->get_row( "SELECT * FROM wp_term_taxonomy WHERE term_id = " . $cate_id );
			$total_post = $term_count ->count + $count_post;
			$wpdb->update( 'wp_term_taxonomy',
				array(
					'count'    => $total_post
				),
				array( 
					'term_id'    => $cate_id
				)
			);
        }
		unset($arr_cate);
		unset($strURL);
		unset($cate_id);
		unset($arr_data);
		
        return true;
    }
	
	function add_post($cate_id, $arr_data, $attachment, $file){
		global $wpdb;
		//insert post
		$time = current_time( 'mysql',1 );
		$arr_data['post_date'] = $time;
		$arr_data['post_date_gmt'] = $time;
		$arr_data['post_modified'] = $time;  
		$arr_data['post_modified_gmt'] = $time; 
		$wpdb->insert('wp_posts',$arr_data);
		
		$post_id = $wpdb->insert_id;
		
		//insert term relationships
		$arr_term['object_id'] = $post_id;
		$arr_term['term_taxonomy_id'] = $cate_id;
		$arr_term['term_order'] = 0;
		$wpdb->insert('wp_term_relationships',$arr_term);
		
		//insert feature image
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( $post_id, $attach_id );
		
		//insert word_post
		add_word_post($arr_data['post_title'],$post_id);
	}
	
	function add_word_post($title, $post_id){
		global $wpdb;
		$arr_result = $wpdb->get_results( "SELECT * FROM wp_word WHERE MATCH (word_name) AGAINST ('" . $title . "') LIMIT 10" );
		if(!empty($arr_result)){
			$arr_keyword = array();
			foreach($arr_result as $keyword){
				$arr_keyword[] = $keyword->word_name;
			}
			$strKeyword = implode(",", $arr_keyword);
			$arr_word_post = array(
				'post_id' => $post_id,
				'arr_word' => $strKeyword,
				'word_post_status' => 1
			);
			$wpdb->insert('wp_word_post',$arr_word_post);
		}
	}

	
	/******
	*
	* AFAMILY
	*
	*******/
	
	
	function get_content_afamily($cate_id, $url,$arr_data,$num_page){
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$category = end(explode('/', $url));
		$count_post = 0;
		for ($i = 1; $i <= $num_page; $i++){
			$url_child = 'http://afamily.vn/' . $category . '/trang-' . $i . '.chn';
			$content = curl($url_child);
			$dom = str_get_html($content);
			$results = $dom->find('div.catalogies div.sub_hot .sub_hotct h2 a, div.catalogies div.sub_hot .sub_hotct2 h3 a, div.catalogies div.list-news1 h4 a');
			if (count($results) <= 0) {
				return 0;
			}	
			foreach ($results as $key => $item) {
				$content = curl('http://afamily.vn/' . $item->href);
				//$content = curl('http://afamily.vn/day-con-biet-boi-ngay-tai-nha-chi-voi-4-buoc-don-gian-2016060811132636.chn');
				
				if ($content == false) {
					continue;
				}
				$html = str_get_html($content);
				
				if($html->find('.detail_content', 0)){
					$arr_data['post_title'] = trim($html->find("h1.d-title", 0)->plaintext);
					$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
					
					//check post exist
					$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
					if(!empty($obj_post)){
						continue;
					}
					
					$cont_detail = $html->find('.sapo', 0) -> outertext;
					
					$cont_detail .= $html->find('.detail_content', 0) -> outertext;
					$cont_detail = str_replace("<script>beforeAfter('.before-after');</script>", " " , $cont_detail);
					$arr_data['post_content'] = $cont_detail;

					$arr_image = $html->find("div.detail_content img");
					if (count($arr_image) > 0) {
						foreach ($arr_image as $key => $img) {
							$src = $img->src;
							$extension = end(explode('.', end(explode('/', $src))));
							$name_img = $arr_data['post_name'] . '_' . ($key + 1) . '.' . $extension;
							file_put_contents($upload_dir['path'] . '/' . $name_img, curl($src));
							$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
							if ($key == 0) {
								$wp_filetype = wp_check_filetype( $name_img, null );
								$attachment = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => sanitize_file_name( $name_img ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);
								$file = $upload_dir['path'] . '/' . $name_img;
							}
							
						}					
					}
					$post_id = add_post($cate_id, $arr_data, $attachment, $file);
					$count_post ++;	
				}
				sleep(3);
			}
		}
		return $count_post;		
	}
	
	
	/******
	*
	* IVIVU
	*
	*******/
	
	function get_content_ivivu($cate_id, $url,$arr_data){
		global $wpdb;
		$upload_dir = wp_upload_dir();
		
		$count_post = 0;
		for ($i = 1; $i <= 20; $i++){
			$url_child = $url . 'page/' . $i;
			$content = curl($url_child);
			$dom = str_get_html($content);
			$results = $dom->find('div.archive-postlist a');
			if (count($results) <= 0) {
				continue;
			}
			foreach ($results as $key => $item) {
				$content = curl($item->href);
				
				if ($content == false) {
					continue;
				}
				$html = str_get_html($content);
				
				if($html->find('.entry-content', 0)){
					$arr_data['post_title'] = trim($html->find("h1.entry-title", 0)->plaintext);
					$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
					
					//check post exist
					$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
					if(!empty($obj_post)){
						continue;
					}
					$html->find('.entry-content .top-sns-wrap', 0)-> innertext = '';
					$html->find('.entry-content .ltt-contentbox', 0)-> innertext = '';
					$contentbox = $html->find(".entry-content .ltt-contentbox", 0);
					$check = true;
					$i = 0;
					while($check){
						$html->find(".entry-content .ltt-contentbox", $i) ->innertext =  '';
						$i ++;
						if($html->find(".entry-content .ltt-contentbox", $i)){
							$check = true;
						}else{
							$check = false;
						}
					}
					$html->find('.entry-content .author', 0)-> innertext = '';
					$html->find('.entry-content .updated', 0)-> innertext = '';
					$html->find('.entry-content .post-rating-wrap', 0)-> innertext = '';
					$html->find('.entry-content .bottom-like-share', 0)-> innertext = '';
					$cont_detail = $html->find('.entry-content', 0) -> outertext;
					$arr_data['post_content'] = $cont_detail;

					$arr_image = $html->find("div.entry-content img");
					if (count($arr_image) > 0) {
						foreach ($arr_image as $key => $img) {
							$src = $img->src;
							$name_img = end(explode('/', $src));
							file_put_contents($upload_dir['path'] . '/' . $name_img, curl($src));
							$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
							if ($key == 0) {
								$wp_filetype = wp_check_filetype( $name_img, null );
								$attachment = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => sanitize_file_name( $name_img ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);
								$file = $upload_dir['path'] . '/' . $name_img;
							}
						}					
					}
					$post_id = add_post($cate_id, $arr_data, $attachment, $file);
					$count_post ++;	
				}
				sleep(3);
			}
			sleep(5);
		}
		return $count_post;		
	}
	
	/******
	*
	* KHUYẾN MÃI VUI
	*
	*******/
	
	function get_content_khuyenmaivui($cate_id, $url,$arr_data){
		global $wpdb;
		$upload_dir = wp_upload_dir();
			
		$content = curl($url);
		$dom = str_get_html($content);
		$results = $dom->find('div#top-posts a');
		if (count($results) <= 0) {
			return 0;
		}
		$count_post = 0;
		foreach ($results as $key => $item) {
			$content = curl($item->href);
			//$content = curl('http://afamily.vn/an-duong-nhieu-cuc-ky-co-hai-bai-viet-nay-se-chi-cho-ban-an-bao-nhieu-la-du-20160609083556824.chn');
			
			if ($content == false) {
				continue;
			}
			$html = str_get_html($content);
			
			if($html->find('.noselect', 0)){
				$arr_data['post_title'] = trim($html->find("h1.content-headline", 0)->plaintext);
				$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
				
				//check post exist
				$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
				if(!empty($obj_post)){
					$wpdb->update( 'wp_posts',
						array(
							'post_date'    => current_time( 'mysql',1 ),
							'post_date_gmt'    => current_time( 'mysql',1 ),
						),
						array( 
							'ID'    => $obj_post->ID
						)
					);
					continue;
				}
				
				$cont_detail = $html->find('.noselect', 0) -> outertext;
				$arr_data['post_content'] = $cont_detail;

				$arr_image = $html->find("div.text-center img");
				//$arr_image = $html->find("div.content img");
				if (count($arr_image) > 0) {
					foreach ($arr_image as $key => $img) {
						$src = $img->src;
						$name_img = end(explode('/', $src));

						file_put_contents($upload_dir['path'] . '/' . $name_img, curl($src));
						$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
						if ($key == 0) {
							$wp_filetype = wp_check_filetype( $name_img, null );
							$attachment = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_title'     => sanitize_file_name( $name_img ),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);
							$file = $upload_dir['path'] . '/' . $name_img;
						}
						
					}					
				}
				$post_id = add_post($cate_id, $arr_data, $attachment, $file);
				$count_post ++;	
			}die("khuyenmaivui");
			sleep(15);
		} 
		die();
		return $count_post;		
	}
	
	
	
	/******
	*
	* GENK
	*
	*******/
	
	
	function get_content_genk($cate_id, $url,$arr_data){
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$count_post = 0;
		for ($i = 1; $i <= 20; $i++){
			$url_child = $url . 'page-' . $i . '.chn';
			$content = curl($url_child);
			$dom = str_get_html($content);
			$results = $dom->find('div.news-stream div.list-news div.list-news-status h2 a');
			$results_img = $dom->find('div.news-stream div.list-news div.list-news-img a img');
			if (count($results) <= 0) {
				continue;
			}
			foreach ($results as $key => $item) {

				$content = curl('http://genk.vn/' . $item->href);
				//$content = curl('http://genk.vn/6-smartphone-gia-re-neu-khong-de-y-se-tuong-do-la-iphone-6-20160601160142512.chn');
				
				if ($content == false) {
					continue;
				}
				$html = str_get_html($content);
				
				if($html->find('div#ContentDetail', 0)){
					$arr_data['post_title'] = trim($html->find("div.news-showtitle h1", 0)->plaintext);
					$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
					
					//check post exist
					$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
					if(!empty($obj_post)){
						continue;
					}
					
					$cont_detail = $html->find('div.content h2.init_content', 0) -> outertext;
					
					if($html->find('div#ContentDetail div.link-content-footer', 0))
						$html->find('div#ContentDetail div.link-content-footer', 0) -> innertext = '';
					
					$cont_detail .= $html->find('div#ContentDetail', 0) -> outertext;
					$arr_data['post_content'] = $cont_detail;

					$arr_image = $html->find("div#ContentDetail img");
					if (count($arr_image) > 0) {
						foreach ($arr_image as $key_img => $img) {
							$rel = $img->rel;
							$src = $img->src;
							if(!empty($rel)){
								$name_img = end(explode('/', $rel));
								file_put_contents($upload_dir['path'] . '/' . $name_img, curl($rel));
								$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
							}					
						}					
					}
					
					$name_img_feature = end(explode('/', $results_img[$key]->src));
					file_put_contents($upload_dir['path'] . '/' . $name_img_feature, curl($results_img[$key]->src));
					$wp_filetype = wp_check_filetype( $name_img_feature, null );
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => sanitize_file_name( $name_img_feature ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);
					$file = $upload_dir['path'] . '/' . $name_img_feature;
					
					$post_id = add_post($cate_id, $arr_data, $attachment, $file);
					$count_post ++;	
				}
				sleep(3);
			}sleep(5);
		}
		return $count_post;		
	}
	
	
	/******
	*
	* KENH14
	*
	*******/
	
	
	function get_content_kenh14($cate_id, $url,$arr_data){ 
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$count_post = 0;
		for ($i = 1; $i <= 20; $i++){
			$url_child = $url . 'trang-' . $i . '.chn';
			$content = curl($url_child);
			$dom = str_get_html($content);
			$results = $dom->find('div.kbca-fashion-top-news h2.kbcaffn-title a, div.kbca-fashion-content h4.kbcaccfn-title a');
			if (count($results) <= 0) {
				continue;
			}
			foreach ($results as $key => $item) {
				$content = curl('http://kenh14.vn/' . $item->href);
				//$content = curl('http://afamily.vn/day-con-biet-boi-ngay-tai-nha-chi-voi-4-buoc-don-gian-2016060811132636.chn');
				
				if ($content == false) {
					continue;
				}
				$html = str_get_html($content);
				
				if($html->find('.kcccw-news-detail', 0)){
					$arr_data['post_title'] = trim($html->find("h1.knd-title", 0)->plaintext);
					$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
					
					//check post exist
					$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
					if(!empty($obj_post)){
						continue;
					}
					
					$cont_detail = $html->find('.knd-sapo', 0) -> outertext;
					
					$cont_detail .= $html->find('.knd-content', 0) -> outertext;
					$arr_data['post_content'] = $cont_detail;
					//p($cont_detail);die;

					$arr_image = $html->find("div.knd-content img");
					if (count($arr_image) > 0) {
						foreach ($arr_image as $key => $img) {
							$src = $img->src;
							$name_img = end(explode('/', $src));
	//$name_img = end(explode('/', $src));
							file_put_contents($upload_dir['path'] . '/' . $name_img, curl($src));
							$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
							if ($key == 0) {
								$wp_filetype = wp_check_filetype( $name_img, null );
								$attachment = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => sanitize_file_name( $name_img ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);
								$file = $upload_dir['path'] . '/' . $name_img;
							}
							
						}					
					}
					$post_id = add_post($cate_id, $arr_data, $attachment, $file);
					$count_post ++;	
				}
				sleep(3);
			}sleep(5);
		}
		return $count_post;		
	}
	
		
	/******
	*
	* VIETBAO
	*
	*******/
	
	
	function get_content_vietbao($cate_id, $url,$arr_data){
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$count_post = 0;
		$i = 0;
		while($i <= 20){
			$url_child = $url . 'p/20/' . $i*20 . '/';
			if($i == 0){
				$url_child = $url;
			}
			$content = curl($url_child);
			$dom = str_get_html($content);
			$results = $dom->find('div#ct_left div#vb-content-detailbox < a');
			if (count($results) <= 0) {
				$i ++;
				continue;
			}
		
			foreach ($results as $key => $item) {
				if(strpos($item->href,'/vietbao.vn/Suc-khoe')){
					$content = curl($item->href);
					//$content = curl('http://afamily.vn/day-con-biet-boi-ngay-tai-nha-chi-voi-4-buoc-don-gian-2016060811132636.chn');
					
					if ($content == false) {
						continue;
					}
					$html = str_get_html($content);
					
					if($html->find('#advenueINTEXT', 0)){
						$arr_data['post_title'] = trim($html->find("#textnd h1", 0)->plaintext);
						$arr_data['post_name'] = sanitize_title($arr_data['post_title']);
						
						//check post exist
						$obj_post = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_name = '" . $arr_data['post_name'] . "'" );
						//if(!empty($obj_post)){
							//continue;
						//}
						
						$html->find('ul.tinlienquan', 0) ->parent() -> innertext = '';
						$cont_detail = $html->find('span#advenueINTEXT', 0) -> outertext;
						
						$cont_detail = str_replace('<div style="background:#f5e3e9; border-radius: 3px; padding: 5px 0 5px 25px; margin: 10px 0 0;"></div>',' ',$cont_detail);
						$arr_data['post_content'] = $cont_detail;

						$arr_image = $html->find("span#advenueINTEXT img");
						if (count($arr_image) > 0) {
							foreach ($arr_image as $key => $img) {
								$src = $img->src;
								$name_img = end(explode('/', $src));
								file_put_contents($upload_dir['path'] . '/' . $name_img, curl($src));
								$arr_data['post_content'] = str_replace($src, $upload_dir['url'] . '/' . $name_img, $arr_data['post_content']);
								if ($key == 0) {
									$wp_filetype = wp_check_filetype( $name_img, null );
									$attachment = array(
										'post_mime_type' => $wp_filetype['type'],
										'post_title'     => sanitize_file_name( $name_img ),
										'post_content'   => '',
										'post_status'    => 'inherit'
									);
									$file = $upload_dir['path'] . '/' . $name_img;
								}
								
							}					
						}
						$post_id = add_post($cate_id, $arr_data, $attachment, $file);
						$count_post ++;	
					}
					sleep(3);
				}
			}$i ++; sleep(5);
		}
		return $count_post;		
	}
	
	function p($input){
		echo "<pre>";
		print_r($input);
		echo "</pre>";
	}