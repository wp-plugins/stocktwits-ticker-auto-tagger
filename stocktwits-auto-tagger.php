<?php
/*
Plugin Name: StockTwits Auto Tagger
Plugin URI: http://thisismyurl.com/plugins/stocktwits-auto-tagger/
Description: Automatically find stock symboltags based on content of the blog post.
Version: 1.0.0
Author: christopherross
Author URI: http://thisismyurl.com/
*/

/**
 * StockTwits Auto Tagger core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/stocktwits-auto-tagger/
 *
 * @package 		StockTwits Auto Tagger
 * @copyright		Copyright (c) 2008, Chrsitopher Ross
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		StockTwits Auto Tagger 1.0
 */

add_action('wp_insert_post', 'stocktwits_auto_stocktwits_gettags', 10, 2);
function stocktwits_auto_stocktwits_gettags($post_id, $post) {
	$tags=$post->tags_input;
	if(is_array($tags)) $tags=implode(',',$tags);
	if(empty($tags) && !empty($_POST['tags_input'])) $tags=$_POST['tags_input'];
	$tags=stocktwits_gettags($post->post_title,$post->post_content,$tags);
	if(!is_array($tags)) return;
	wp_add_post_tags($post_id,$tags);

}

function stocktwits_gettags($title,$content,$tags) {
	
	
	$symbols = explode("$",strip_tags($content));
	
	foreach ($symbols as $symbol) {
		$include = false;
		$symbol = str_replace(","," ",$symbol);
		$temp = explode(" ",$symbol);
		$symbol = trim($temp[0]);
		if (strlen($symbol) >0 ) {
			if (substr($content, 0, 1) == "$" && $count==0) {
				$include = true;	
			} elseif ($count > 0) {
				$include = true;
			}
			
			
			$exclude = "1,2,3,4,5,6,7,8,9,0";
			$exclude = explode(",",$exclude);
			foreach ($exclude as $excludeitem) {
				if (substr_count($temp[0],$excludeitem)> 0) {
					$include = false;
				}
			}
			
			if ($include) {
				$symbollist[] = $symbol;
			}
		}
	
		$count++;
	}
	
	if (is_array($symbollist)) {	
		$symbollist = array_unique($symbollist);
		
		foreach ($symbollist as $symbol) {
			
			if(function_exists('curl_init')) {
						
				$ch = curl_init();
				$url = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query='.$symbol.'&callback=YAHOO.Finance.SymbolSuggest.ssCallback';
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$response = curl_exec($ch);
				if(curl_errno($ch)) return curl_error($ch);
				curl_close($ch);
				$results=unserialize($response);
			
			}	 else {
				$response = file_get_contents($url);
			}
			
				$yahoostock=($response);
				$yahoostock=explode('"name": "',$yahoostock);
				$yahoostock=explode('","',$yahoostock[1]);
				unset($stock);
				$stock = trim($yahoostock[0]);
				
				if ($stock != "") {
					$tags[] = $stock;
				}
				$tags[] = $symbol;
		}
	}
	
	if (is_array($tags)) {
		$tags = array_unique($tags);
	}
	return $tags;
}