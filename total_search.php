<?php
$headerGiven = 'Content-Type: text/xml; charset=utf-8';
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

include 'class/search.php';
$S = new SEARCH;

// 결과값 주기
if($_POST['searchText']) {
	$list = $S->totalSearch($_POST['searchText'], 'subject', 0, $_POST['listNum'], $_POST['type']);
	$size = @count($list);
	if($size > $_POST['listNum']) $size = $_POST['listNum'];
	if(!$list[0]['no']) {
		$xml  = '<?xml version="1.0" encoding="utf-8"?><lists><item no="0"><title>찾지 못했습니다</title><boardID>0</boardID></item></lists>';
		die($xml);
	}
	$xml  = '<?xml version="1.0" encoding="utf-8"?><lists>';
	for($i=0; $i<$size; $i++) {
		$xml .= '<item no="'.$list[$i]['no'].'"><title>'.strips_tags($list[$i]['subject']).'</title>';
		$xml .= '<boardID>'.$list[$i]['id'].'</boardID></item>';
	}
	$xml .= '</lists>';
	echo $xml;
}
?>
