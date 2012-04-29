<?php
if(!defined('__GRBOARD__')) exit();

// 게시물 페이징 @sirini
if(!$page) $page = 1;
$fromRecord = ($page - 1) * $tmpFetchBoard['page_num'];
$tableType = ($searchOption == 'co_subject' || $searchOption == 'co_content' || $searchOption == 'co_name') ? 'comment_' : 'bbs_'; // 2010-01-28 @좋아, @이동규
if($tableType == 'comment_' && $searchOption) $addCountOption = ' where '.str_replace('co_', '', $searchOption).' like \'%'.$searchText.'%\'';
elseif($clickCategory && !$searchOption) $addCountOption = ' where category = \''.$clickCategory.'\'';
elseif($searchOption) $addCountOption = ' where '.$searchOption.' like \'%'.$searchText.'%\'';
else $addCountOption = '';
$getTotalNum = $GR->getArray('select count(*) from '.$dbFIX.$tableType.$id.$addCountOption);
$totalCount = $getTotalNum[0];
$getMaxNo = $GR->getArray('select max(no) from '.$dbFIX.$tableType.$id.$addCountOption);
$maxNo = $getMaxNo[0];
$arrange = 1000;

// 범주의 크기를 구분해서 처리 @sirini
if($maxNo > $arrange) {
	if(!$division) { $division = ceil($maxNo / $arrange); $originDivision = $division; }
	$moreThanMe = ($division - 1) * $arrange;
	$lessThanMe = $division * $arrange;
	if(($originDivision == $division) && ($maxNo > $lessThanMe)) $lessThanMe = $maxNo;
	$getRealMax = $GR->getArray('select count(*) from '.$dbFIX.$tableType.$id.' where no > '.$moreThanMe.' and no <= '.$lessThanMe.str_replace(' where ', ' and ', $addCountOption));
	$totalPage = ceil($getRealMax[0] / $tmpFetchBoard['page_num']);
} else {
	if(!$division) { $division = 0; $originDivision = 0; }
	$moreThanMe = 0;
	$lessThanMe = $arrange;
	$totalPage = ceil($totalCount / $tmpFetchBoard['page_num']);
}

// 검색어 혹은 카테고리 혹은 일반 검색 @sirini
$searchText = str_replace(array('_', '%', '\\'), array('\\_', '\\%', '\\\\\\\\'), $searchText);
if($tableType == 'comment_') {
	$coOption = str_replace('co_', '', $searchOption);
	$searchQue = 'where is_secret != 1 and '.$coOption.' like \'%'.$searchText.'%\'';
	if(!$tmpFetchBoard['is_full']) $getField = 'no, board_no, member_key, name, signdate, bad, subject'; else $getField = '*';
	$getList = $GR->query('select '.$getField.' from '.$dbFIX.'comment_'.$id.' '.$searchQue.' order by '.$sortList.' '.$sortBy.' limit '.$fromRecord.', '.$tmpFetchBoard['page_num']);
} else {
	if($searchOption && $searchText && !$clickCategory) $searchQue = 'where no >= '.$moreThanMe.' and no <= '.$lessThanMe.' and '.$searchOption.' like \'%'.$searchText.'%\'';
	elseif(!$searchOption && !$searchText && $clickCategory) $searchQue = 'where no >= '.$moreThanMe.' and no <= '.$lessThanMe.' and category = \''.$clickCategory.'\'';
	else $searchQue = 'where no >= '.$moreThanMe.' and no <= '.$lessThanMe;
	if(!$tmpFetchBoard['is_full']) $getField = 'no, member_key, name, signdate, hit, bad, category, subject, is_secret, comment_count'; else $getField = '*';
	$getList = $GR->query('select '.$getField.' from '.$dbFIX.'bbs_'.$id.' '.$searchQue.' order by '.$sortList.' '.$sortBy.' limit '.$fromRecord.', '.$tmpFetchBoard['page_num']);
}

// 공지글 목록 따로 뽑고, 개수 저장 @sirini
$getNotice = $GR->query('select '.$getField.' from '.$dbFIX.'bbs_'.$id.' where is_notice = \'1\' order by no desc');
$totalResultNotice = $GR->getNumRows($getNotice);
$numNotice = $totalResultNotice;

// 페이징 저장하고 가상번호를 설정한다. @sirini
$goURL = 'board.php?id='.$id.'&amp;originDivision='.$originDivision.'&amp;sortList='.$sortList.'&amp;sortBy='.$sortBy.'&amp;page=';
$printPage = $GR->getPaging($tmpFetchBoard['page_per_list'], $page, $totalPage, $goURL, $division, $originDivision, $searchOption, $searchText, $clickCategory);
if($division < $originDivision) {
	$getOldestCount = $GR->getArray('select count(*) from '.$dbFIX.'bbs_'.$id.' where no > '.($moreThanMe + $arrange));
	$number = ($getTotalNum[0] - $getOldestCount[0] - 1) - (($page - 1) * $tmpFetchBoard['page_num']);
} else $number = $getTotalNum[0] - (($page - 1) * $tmpFetchBoard['page_num']);

// 게시판 상단 부분과 목록출력부분, 하단부분 불러오기 @sirini
include $theme.'/head.php';
include $theme.'/list.php';
include $theme.'/list_foot.php';
?>
