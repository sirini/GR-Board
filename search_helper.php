<?php
$headerGiven = 'Content-Type: text/xml; charset=utf-8';
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 결과값 주기 @sirini
if($_POST['searchText']) {
	
	$boardID = $_POST['boardID'];
	$test = $GR->getArray("select view_level from ".$dbFIX."board_list where id='".$boardID."';");
	if($test['view_level'] != 1 && $test['view_level'] > $_SESSION['level']){
		//일단은 게시물보기 권한으로 판단. 차후 권한 세분화 필요 by pico
	        $xml  = '<?xml version="1.0" encoding="utf-8"?><lists>';
                $xml .= '<item no="0"><title>권한이 없습니다.</title></item></lists>';
		echo $xml; die();
	}
	$searchText = $_POST['searchText'];
	$searchOption = $_POST['searchOption'];
	$searchText = str_replace(array('_', '%', '\\'), array('\_', '\%', '\\\\\\\\'), $searchText);
	$xml  = '<?xml version="1.0" encoding="utf-8"?><lists>';
	$test = $GR->getArray('select no from '.$dbFIX.'bbs_'.$boardID.' where '.$searchOption." like '%".$searchText."%' limit 1");
	
	if(!$test['no']) {
		$xml .= '<item no="0"><title>검색 결과가 없습니다.</title></item></lists>';
		die($xml);
	}
	
	$result = $GR->query('select no, subject from '.$dbFIX.'bbs_'.$boardID.' where '.$searchOption." like '%".$searchText."%' limit 10");
	while($list = $GR->fetch($result)) {
		$xml .= '<item no="'.$list['no'].'"><title>'.htmlspecialchars($GR->unescape($list['subject'])).'</title></item>';
	}
	$xml .= '</lists>';
	echo $xml;
}
?>
