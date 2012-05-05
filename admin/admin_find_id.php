<?php
// 기본 클래스를 부른다 @sirini
$preRoute = '../';
$headerGiven = 'Content-type: text/xml; charset=utf-8';
require $preRoute.'class/common.php';
$GR = new COMMON;

// 관리자인지 확인한다. @sirini
if($_SESSION['no'] != 1) { 
	header('HTTP/1.1 406 Not Acceptable');
	exit('관리자만 접근가능합니다.'); 
}

// DB 연결 @sirini
$GR->dbConn();

// 결과값 주기 @sirini
if($_POST['id'])
{
	$xml  = '<?xml version="1.0" encoding="utf-8"?><lists>';
	$sql = 'select id, nickname, realname from '.$dbFIX.'member_list where id'." like '%{$id}%' limit 10";
	$test = $GR->getArray($sql);
	if(!$test[0]) {
		$xml .= '<ids name="x" real="x">검색 결과가 없습니다.</ids></lists>';
		die($xml);
	}

	$result = $GR->query($sql);
	while($list = $GR->fetch($result)) {
		$xml .= '<ids name="'.strip_tags($list['nickname']).'" real="'.strip_tags($list['realname']).'">'.$list['id'].'</ids>';
	}
	$xml .= '</lists>';
	echo $xml;
}
?>
