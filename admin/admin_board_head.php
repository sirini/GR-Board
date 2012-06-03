<?php
// 기본 클래스를 부른다 @sirini
$preRoute = './';
require $preRoute.'class/common.php';
$GR = new COMMON;

// 관리자인지 확인한다. @sirini
if($_SESSION['no'] != 1) { 
	header('HTTP/1.1 406 Not Acceptable');
	exit('관리자만 접근가능합니다.'); 
}

// DB 연결 @sirini
$GR->dbConn();

// 변수 처리 @sirini
if($_GET['sortList']) $sortList = $_GET['sortList'];
if($_GET['boardID']) $boardID = $_GET['boardID'];
if($_GET['page']) $page = $_GET['page'];
if($_GET['delExtName']) $delExtName = $_GET['delExtName'];
if($_GET['addExtName']) $addExtName = $_GET['addExtName'];
if($_GET['addExtType']) $addExtType = $_GET['addExtType'];

// POST 변수 처리 @sirini
@extract($_POST);

// 필드 자동추가하기 기능 사용시 @sirini
if($_GET['fieldAutoExtend'] && $_GET['selectTheme']) {
	if(file_exists('theme/'.$_GET['selectTheme'].'/field.extend.ini')) {
		$queries = @file_get_contents('theme/'.$_GET['selectTheme'].'/field.extend.ini');
		$queArr = @explode("\n", $queries);
		for($q=0; $q<count($queArr); $q++) $GR->query("alter table {$dbFIX}bbs_{$boardID} add ".$queArr[$q]);
		$GR->error('이 스킨에 필요한 필드들을 모두 추가하였습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
	} else $GR->error('자동 필드추가를 위한 field.extend.ini 파일이 스킨 내에 없습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
}

// 게시판 설정 수정할 때 카테고리 변경(추가/삭제) 를 했다면 먼저 처리 후 턴 백
if($_GET['deleteCateAll'] == 'disableCategory') {
	$GR->query("update {$dbFIX}board_list set category = '' where id = '$boardID' limit 1");
	$GR->error('카테고리를 모두 삭제했습니다. 카테고리 기능을 사용하지 않습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page.'#categoryForm');
}
if($expertCreateCategory) {
	if($expertCreateCategory != $categoryAll) {
		$GR->query("update {$dbFIX}board_list set category = '$expertCreateCategory' where id = '$boardID' limit 1");
		$GR->error('카테고리를 수정하였습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page.'#categoryForm');
	}
}
if($createCategory) {
	if($modifyCateOriginal) {
		if(!$deleteCate) $categoryAll = str_replace($modifyCateOriginal, $createCategory, $categoryAll);
		else $categoryAll = str_replace(array('|'.$deleteCate, $deleteCate.'|'), '', $categoryAll);
		$GR->query("update {$dbFIX}bbs_{$boardID} set category = '$createCategory' where category = '$modifyCateOriginal'");
		$GR->query("update {$dbFIX}board_list set category = '$categoryAll' where id = '$boardID' limit 1");
	} else {
		$getIsCate = $GR->getArray('select category from '.$dbFIX.'board_list where id = \''.$boardID.'\'');
		if(!strpos($GR->escape($getIsCate['category']), $createCategory)) { //createCategory는 escape된 상태임 escape된것끼리 비교
			if($categoryAll) $createCategory = '|'.$createCategory;
			$categoryAll .= $createCategory;
			$GR->query("update {$dbFIX}board_list set category = '$categoryAll' where id = '$boardID' limit 1");
		} else $GR->error('이미 동일한 이름의 카테고리가 있습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page.'#categoryForm');
	}
	$GR->error('카테고리를 수정하였습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page.'#categoryForm');
} else $modifyCategory = $categoryAll;

// 필수변수 선언
$maxDefaultField = 22;
if(!$viewRows) $viewRows = 10;

// 추가 필드 삭제
if($delExtName) {
	$GR->query("alter table `{$dbFIX}bbs_{$boardID}` drop `{$delExtName}`");
	$GR->error($delExtName.' 필드를 '.$boardID.' 게시판 테이블에서 삭제하였습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
}

// 필드 추가하기
if($addExtName && $addExtType) {
	if($addExtType == 'int') $addExtQue = 'int(11) not null default \'0\'';
	elseif($addExtType == 'varchar') $addExtQue = 'varchar(255) not null default \'\'';
	else $addExtQue = 'text';
	$GR->query("alter table `{$dbFIX}bbs_{$boardID}` add `ext_{$addExtName}` {$addExtQue}");
	$GR->error($boardID.' 게시판에 '.$addExtType.' 타입의 ext_'.$addExtName.' 필드를 추가하였습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
}

// 해당 그룹에 속한 게시판에 동일설정 반영처리
if($sameModifyHeadFile) $GR->query("update {$dbFIX}board_list set head_file = '$modifyHeadFile' where group_no = '$modifyGroup'");
if($sameModifyFootFile) $GR->query("update {$dbFIX}board_list set foot_file = '$modifyFootFile' where group_no = '$modifyGroup'");
if($sameModifyHeadForm) $GR->query("update {$dbFIX}board_list set head_form = '$modifyHeadForm' where group_no = '$modifyGroup'");
if($sameModifyFootForm) $GR->query("update {$dbFIX}board_list set foot_form = '$modifyFootForm' where group_no = '$modifyGroup'");
if($sameModifyCategory) $GR->query("update {$dbFIX}board_list set category = '$modifyCategory' where group_no = '$modifyGroup'");
if($sameModifyMaster) $GR->query("update {$dbFIX}board_list set master = '$modifyMaster' where group_no = '$modifyGroup'");
if($sameModifyTheme) $GR->query("update {$dbFIX}board_list set theme = '$modifyTheme' where group_no = '$modifyGroup'");
if($sameModifyPageNum) $GR->query("update {$dbFIX}board_list set page_num = '$modifyPageNum' where group_no = '$modifyGroup'");
if($sameModifyPagePerList) $GR->query("update {$dbFIX}board_list set page_per_list = '$modifyPagePerList' where group_no = '$modifyGroup'");
if($sameModifyEnterLevel) $GR->query("update {$dbFIX}board_list set enter_level = '$modifyEnterLevel' where group_no = '$modifyGroup'");
if($sameModifyViewLevel) $GR->query("update {$dbFIX}board_list set view_level = '$modifyViewLevel' where group_no = '$modifyGroup'");
if($sameModifyWriteLevel) $GR->query("update {$dbFIX}board_list set write_level = '$modifyWriteLevel' where group_no = '$modifyGroup'");
if($sameModifyCommentWriteLevel) $GR->query("update {$dbFIX}board_list set comment_write_level = '$modifyCommentWriteLevel' where group_no = '$modifyGroup'");
if($sameModifyDownLevel) $GR->query("update {$dbFIX}board_list set down_level = '$modifyDownLevel' where group_no = '$modifyGroup'");
if($sameModifyDownPoint) $GR->query("update {$dbFIX}board_list set down_point = '$modifyDownPoint' where group_no = '$modifyGroup'");
if($sameModifyCommentPageNum) $GR->query("update {$dbFIX}board_list set comment_page_num = '$modifyCommentPageNum' where group_no = '$modifyGroup'");
if($sameModifyCommentPagePerList) $GR->query("update {$dbFIX}board_list set comment_page_per_list = '$modifyCommentPagePerList' where group_no = '$modifyGroup'");
if($sameModifyNumFile) $GR->query("update {$dbFIX}board_list set num_file = '$modifyNumFile' where group_no = '$modifyGroup'");
if($sameModifyIsRss) $GR->query("update {$dbFIX}board_list set is_rss = '$modifyIsRss' where group_no = '$modifyGroup'");
if($sameModifyIsHtml) $GR->query("update {$dbFIX}board_list set is_html = '$modifyIsHtml' where group_no = '$modifyGroup'");
if($sameModifyIsEditor) $GR->query("update {$dbFIX}board_list set is_editor = '$modifyIsEditor' where group_no = '$modifyGroup'");
if($sameModifyCutSubject) $GR->query("update {$dbFIX}board_list set cut_subject = '$modifyCutSubject' where group_no = '$modifyGroup'");
if($sameModifyIsFull) $GR->query("update {$dbFIX}board_list set is_full = '$modifyIsFull' where group_no = '$modifyGroup'");
if($sameModifyIsList) $GR->query("update {$dbFIX}board_list set is_list = '$modifyIsList' where group_no = '$modifyGroup'");
if($sameModifyCommentSort) $GR->query("update {$dbFIX}board_list set comment_sort = '$modifyCommentSort' where group_no = '$modifyGroup'");
if($sameModifyIsCoEditor) $GR->query("update {$dbFIX}board_list set is_comment_editor = '$modifyIsCoEditor' where group_no = '$modifyGroup'");
if($sameModifyIsHistory) $GR->query("update {$dbFIX}board_list set is_history = '$modifyIsHistory' where group_no = '$modifyGroup'");
if($sameModifyIsEnglish) $GR->query("update {$dbFIX}board_list set is_English = '$modifyIsEnglish' where group_no = '$modifyGroup'");
if($sameModifyFixTime) $GR->query("update {$dbFIX}board_list set fix_time = '$modifyFixTime' where group_no = '$modifyGroup'");

// 정렬옵션
if(array_key_exists('sortList', $_GET) && $sortList == 'desc') $sortBy = 'asc'; else $sortBy = 'desc';

// 게시판 추가하기
if(array_key_exists('isAddBoard', $_POST) && $_POST['isAddBoard']) {
	$resultAlready = $GR->getArray("select no from {$dbFIX}board_list where id = '$addBoardId'");
	if($resultAlready['no']) $GR->error('게시판 ID 값이 중복됩니다. 게시판 ID 를 다른 이름으로 해주세요.', 0, 'admin_board.php?page='.$page);

	$sqlAddBoard = "create table {$dbFIX}bbs_{$addBoardId} (
		no int(11) not null auto_increment,
		member_key int(11) not null default '0',
		name varchar(20) not null default '',
		password varchar(50) not null default '',
		email varchar(255) default '',
		homepage varchar(255) default '',
		ip varchar(20) not null default '',
		signdate int(11) not null default '0',
		hit int(11) not null default '0',
		good int(11) not null default '0',
		bad int(11) not null default '0',
		comment_count int(11) not null default '0',
		is_notice tinyint(4) default '0',
		is_secret tinyint(4) default '0',
		is_grcode tinyint(4) default '0',
		category varchar(50) default '',
		subject varchar(255) not null default '',
		content text,
		link1 varchar(255),
		link2 varchar(255),	
		tag varchar(255),
		primary key(no),
		key member_key(member_key),
		key hit(hit),
		key signdate(signdate),
		key good(good),
		key bad(bad),
		key is_notice(is_notice))";
	$GR->query($sqlAddBoard);

	$sqlAddComment = "create table {$dbFIX}comment_{$addBoardId} (
		no int(11) not null auto_increment,
		board_no int(11) not null default '0',
		family_no int(11) not null default '0',
		thread tinyint(4) not null default '0',
		member_key int(11) not null default '0',
		is_grcode tinyint(4) not null default '0',
		name varchar(20) not null default '',
		password varchar(50) not null default '',
		email varchar(255) default '',
		homepage varchar(255) default '',
		ip varchar(20) not null default '',
		signdate int(11) not null default '0',
		good int(11) not null default '0',
		bad int(11) not null default '0',
		subject varchar(255) not null default '',
		content text,
		is_secret tinyint(4) not null default '0',
		order_key varchar(50) not null default '',
		primary key(no),
		key board_no(board_no),
		key family_no(family_no),
		key thread(thread),
		key member_key(member_key),
		key order_key(order_key))";
	$GR->query($sqlAddComment);

	$makeTime = $GR->grTime();
	$sqlInsertBoard = "insert into {$dbFIX}board_list
		set id = '$addBoardId',
			name = '$addBoardName',
			head_file = '$addHeadFile',
			foot_file = '$addFootFile',
			head_form = '$addHeadForm',
			foot_form = '$addFootForm',
			category = '$addCategory',
			make_time = '$makeTime',
			page_num = '$addPageNum',
			page_per_list = '$addPagePerList',
			enter_level = '$addEnterLevel',
			view_level = '$addViewLevel',
			write_level = '$addWriteLevel',
			comment_write_level = '$addCommentWriteLevel',
			down_level = '$addDownLevel',
			down_point = '$addDownPoint',
			master = '$addMaster',
			theme = '$addTheme',
			comment_page_num = '$addCommentPageNum',
			comment_page_per_list = '$addCommentPagePerList',
			num_file = '$addNumFile',
			cut_subject = '$addCutSubject',
			is_full = '$addIsFull',
			is_rss = '$addIsRss',
			is_html = '$addIsHtml',
			is_editor = '$addIsEditor',
			group_no = '$addGroup',
			is_list = '$addIsList',
			comment_sort = '$addCommentSort',
			is_comment_editor = '$addIsCoEditor',
			fix_time = '$addFixTime',
			is_history = '$addIsHistory',
			is_english = '$addIsEnglish'";
			
	$GR->query($sqlInsertBoard);

	// 모든 게시판에 동일설정 반영처리
	if($sameAddHeadFile) $GR->query("update {$dbFIX}board_list set head_file = '$addHeadFile' where group_no = '$addGroup'");
	if($sameAddFootFile) $GR->query("update {$dbFIX}board_list set foot_file = '$addFootFile' where group_no = '$addGroup'");
	if($sameAddHeadForm) $GR->query("update {$dbFIX}board_list set head_form = '$addHeadForm' where group_no = '$addGroup'");
	if($sameAddFootForm) $GR->query("update {$dbFIX}board_list set foot_form = '$addFootForm' where group_no = '$addGroup'");
	if($sameAddCategory) $GR->query("update {$dbFIX}board_list set category = '$addCategory' where group_no = '$addGroup'");
	if($sameAddMaster) $GR->query("update {$dbFIX}board_list set master = '$addMaster' where group_no = '$addGroup'");
	if($sameAddTheme) $GR->query("update {$dbFIX}board_list set theme = '$addTheme' where group_no = '$addGroup'");
	if($sameAddPageNum) $GR->query("update {$dbFIX}board_list set page_num = '$addPageNum' where group_no = '$addGroup'");
	if($sameAddPagePerList) $GR->query("update {$dbFIX}board_list set page_per_list = '$addPagePerList' where group_no = '$addGroup'");
	if($sameAddEnterLevel) $GR->query("update {$dbFIX}board_list set enter_level = '$addEnterLevel' where group_no = '$addGroup'");
	if($sameAddViewLevel) $GR->query("update {$dbFIX}board_list set view_level = '$addViewLevel' where group_no = '$addGroup'");
	if($sameAddWriteLevel) $GR->query("update {$dbFIX}board_list set write_level = '$addWriteLevel' where group_no = '$addGroup'");
	if($sameAddCommentWriteLevel) $GR->query("update {$dbFIX}board_list set comment_write_level = '$addCommentWriteLevel' where group_no = '$addGroup'");
	if($sameAddCommentPageNum) $GR->query("update {$dbFIX}board_list set comment_page_num = '$addCommentPageNum' where group_no = '$addGroup'");
	if($sameAddCommentPagePerList) $GR->query("update {$dbFIX}board_list set comment_page_per_list = '$addCommentPagePerList' where group_no = '$addGroup'");
	if($sameAddNumFile) $GR->query("update {$dbFIX}board_list set num_file = '$addNumFile' where group_no = '$addGroup'");
	if($sameAddCutSubject) $GR->query("update {$dbFIX}board_list set cut_subject = '$addCutSubject' where group_no = '$addGroup'");
	if($sameAddIsFull) $GR->query("update {$dbFIX}board_list set is_rss = '$addIsRss' where group_no = '$addGroup'");
	if($sameAddIsFull) $GR->query("update {$dbFIX}board_list set is_full = '$addIsFull' where group_no = '$addGroup'");
	if($sameAddIsHtml) $GR->query("update {$dbFIX}board_list set is_html = '$addIsHtml' where group_no = '$addGroup'");
	if($sameAddIsEditor) $GR->query("update {$dbFIX}board_list set is_editor = '$addIsEditor' where group_no = '$addGroup'");
	if($sameAddIsList) $GR->query("update {$dbFIX}board_list set is_list = '$addIsList' where group_no = '$addGroup'");
	if($sameAddCommentSort) $GR->query("update {$dbFIX}board_list set comment_sort = '$addCommentSort' where group_no = '$addGroup'");
	if($sameAddIsCoEditor) $GR->query("update {$dbFIX}board_list set is_comment_editor = '$addIsCoEditor' where group_no = '$addGroup'");
	if($sameAddFixTime) $GR->query("update {$dbFIX}board_list set fix_time = '$addFixTime' where group_no = '$addGroup'");
	if($sameAddDownLevel) $GR->query("update {$dbFIX}board_list set down_level = '$addDownLevel' where group_no = '$addGroup'");
	if($sameAddDownPoint) $GR->query("update {$dbFIX}board_list set down_point = '$addDownPoint' where group_no = '$addGroup'");
	if($sameAddIsHistory) $GR->query("update {$dbFIX}board_list set is_history = '$addIsHistory' where group_no = '$addGroup'");
	if($sameAddIsEnglish) $GR->query("update {$dbFIX}board_list set is_english = '$addIsEnglish' where group_no = '$addGroup'");
	$GR->error('게시판을 성공적으로 추가했습니다.', 0, 'admin_board.php?boardID='.$addBoardId.'&page='.$page);
}

// 게시판 설정 수정하기
if(array_key_exists('isModifyBoard', $_POST) && $_POST['isModifyBoard']) {
	if(array_key_exists('renewalCategory', $_POST) && $_POST['renewalCategory']) {
		$originalCategory = $_POST['originalCategory'];
		$renewalCategory = $_POST['renewalCategory'];
		$getCategoryFull = $GR->getArray("select category from {$dbFIX}board_list where id = '$boardID'");
		if( preg_match('/'.$renewalCategory.'/i', $getCategoryFull[0]) ) {
			$GR->error('이미 '.$renewalCategory.' 가 카테고리(분류)에 존재합니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
		}
		$modifyCategory = str_replace($originalCategory, $renewalCategory, $getCategoryFull[0]);
		$GR->query('update '.$dbFIX.'bbs_'.$boardID." set category = '$renewalCategory' where category = '$originalCategory'");
	}

	$sqlUpdateBoard = "update {$dbFIX}board_list
		set name = '$modifyBoardName',
			head_file = '$modifyHeadFile',
			foot_file = '$modifyFootFile',
			head_form = '$modifyHeadForm',
			foot_form = '$modifyFootForm',
			category = '$modifyCategory',
			page_num = '$modifyPageNum',
			page_per_list = '$modifyPagePerList',
			enter_level = '$modifyEnterLevel',
			view_level = '$modifyViewLevel',
			write_level = '$modifyWriteLevel',
			comment_write_level = '$modifyCommentWriteLevel',
			down_level = '$modifyDownLevel',
			down_point = '$modifyDownPoint',
			master = '$modifyMaster',
			theme = '$modifyTheme',
			comment_page_num = '$modifyCommentPageNum',
			comment_page_per_list = '$modifyCommentPagePerList',
			num_file = '$modifyNumFile',
			cut_subject = '$modifyCutSubject',
			is_full = '$modifyIsFull',
			is_rss = '$modifyIsRss',
			is_html = '$modifyIsHtml',
			is_editor = '$modifyIsEditor',
			group_no = '$modifyGroup',
			is_list = '$modifyIsList',
			comment_sort = '$modifyCommentSort',
			is_comment_editor = '$modifyIsCoEditor',
			fix_time = '$modifyFixTime',
			is_history = '$modifyIsHistory',
			is_English = '$modifyIsEnglish'
		where no = '$targetBoardNo'";
	$GR->query($sqlUpdateBoard);
	$GR->error('게시판 설정을 성공적으로 수정했습니다.', 0, 'admin_board.php?boardID='.$boardID.'&page='.$page);
}

// 게시판 삭제하기
if(array_key_exists('deleteBoardNo', $_GET) && $_GET['deleteBoardNo'] && $_GET['deleteBoardId']) {
	$deleteBoardNo = $_GET['deleteBoardNo'];
	$deleteBoardId = $_GET['deleteBoardId'];
	$GR->query('delete from '.$dbFIX.'board_list where no = '.$deleteBoardNo);
	$GR->query('drop table '.$dbFIX.'bbs_'.$deleteBoardId);
	$GR->query('drop table '.$dbFIX.'comment_'.$deleteBoardId);
	$getDeleteFile = $GR->query("select * from {$dbFIX}pds_save where id = '$deleteBoardId'");
	while($files = $GR->fetch($getDeleteFile)) {
		if($files['file_route1']) @unlink($files['file_route1']);
		if($files['file_route2']) @unlink($files['file_route2']);
		if($files['file_route3']) @unlink($files['file_route3']);
		if($files['file_route4']) @unlink($files['file_route4']);
		if($files['file_route5']) @unlink($files['file_route5']);
		if($files['file_route6']) @unlink($files['file_route6']);
		if($files['file_route7']) @unlink($files['file_route7']);
		if($files['file_route8']) @unlink($files['file_route8']);
		if($files['file_route9']) @unlink($files['file_route9']);
		if($files['file_route10']) @unlink($files['file_route10']);
		$GR->query('delete from '.$dbFIX.'pds_list where type = 0 and uid = '.$files['no']);
	}
	$getExtendFiles = $GR->query('select file_route from '.$dbFIX.'pds_extend where id = \''.$deleteBoardId.'\'');
	while($extFiles = $GR->fetch($getExtendFiles)) {
		@unlink($extFiles['file_route']);
		$GR->query('delete from '.$dbFIX.'pds_list where type = 1 and uid = '.$extFiles['no']);
	}
	$GR->query('delete from '.$dbFIX.'pds_save where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'pds_extend where id = '.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'time_bomb where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'total_article where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'total_comment where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'tag_list where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'scrap_book where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'report where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'poll_subject where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'poll_option where id = \''.$deleteBoardId.'\'');
	$GR->query('delete from '.$dbFIX.'poll_article_option where id = \''.$deleteBoardId.'\'');
	$GR->error('게시판을 성공적으로 삭제했습니다.', 0, 'admin_board.php?page='.$page);
}

// 페이지관련 처리
if(!$page) $page = 1;
$fromRecord = ($page - 1) * $viewRows;

// 상단내용 & 하단내용 기본설정
$headContent = '<!doctype html>'."\n".'<html>'."\n".
'<head><meta charset="utf-8" />'."\n".
'<title>GR Board</title><link rel="stylesheet" href="[theme]/style.css" type="text/css" title="style" />'."\n".
'</head><body><section><div style="text-align: center"><div style="margin: auto; width: 650px">'."\n";
$footContent = '</div></div></section></body></html>';

// 문서설정
$title = 'GR Board Admin Page ( Board )';
$htmlHeadAdd = '<script src="js/jquery.js"></script>'."\n".
'<script src="admin/admin_board.js"></script>';
include 'html_head.php';
?>