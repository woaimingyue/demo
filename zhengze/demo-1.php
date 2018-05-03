<?php
header('Content-type:text/html; charset=utf-8');
//print_r($_SERVER);die;
$url1 = "http://m.miniso.cn/wap/category-channel-20.html";
$url2 = "http://m.miniso.cn/wap/product-192.html";
$url3 = "http://m.miniso.cn/wap/gallery-36.html";
$url4 = "http://m.miniso.cn/wap/topic-detail-13.html";
$url5 = "http://m.miniso.cn/wap/activity-20171024.html";

preg_match("/\/wap\/(.*).html/", $url1, $matches);

$type = !empty($matches[1]) ? substr($matches[1], 0, strrpos($matches[1], '-')) : '';

if(!empty($type)){
	$type_id = substr($matches[1], strlen($type)+1);
//	echo $type_id;
	switch ($type) {
		case 'category-channel':
			$params = array('cat_id'=>$type_id);
			$linkUrl = '/category/channel';
			$imgUrl = '';
			break;
		case 'product':
			$params = array('product_id'=>$type_id);
			$linkUrl = '/category/channel';
			$imgUrl = '';
			break;
		case 'topic-detail':
			$params = array('arc_id'=>$type_id);
			$linkUrl = '/topic/detail';
			$imgUrl = '';
			break;
		case 'activity':
			$params = array('tmpl_id'=>$type_id);
			$linkUrl = '/category/channel';
			$imgUrl = '';
			break;
		default:
			# code...
			break;
	}
}
//print_r($type); die;
#--------------------------------------------------------------------------------------------------

$search_params = array('is_search'=>true, 'keywords'=>'小明', 'filter'=>'去上学小明');

$search_func = function ($search_params) {
  if ($search_params['is_search'] && $search_params['keywords']) {
      return preg_match("/{$search_params['keywords']}/", $search_params['filter'], $result) ? true : false;
  }
};

$fitlers = $search_func($search_params);
var_dump((bool)'');