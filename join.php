<?php
// 기본 클래스를 부른다 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 멤버관리옵션 부르기 @sirini
include 'config_member.php';

// 로그인 상태면 에러 @sirini
if($_SESSION['no']) $GR->error('이미 로그인 상태입니다. 중복 가입을 하실 수 없습니다.');

// 게시판상에서 가입시도일 경우 변수로 저장 @sirini
if(isset($_GET['joinInBoard'])) $joinInBoard = 1; else $joinInBoard = 0;
if(isset($_GET['boardId'])) $boardId = $_GET['boardId']; else $boardId = '';
if(isset($_GET['fromPage'])) $fromPage = $_GET['fromPage']; else $fromPage = '';

// 필요한 변수정의 @sirini
$getJoinus = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'join_skin\' limit 1');
if(!$getJoinus['var']) $getJoinus['var'] = 'default';
$setup = $GR->getArray("select head_file, head_form, foot_form, foot_file, theme from {$dbFIX}board_list where id = '$boardId'");
$theme = 'theme/'.$setup['theme'];
$grboard = str_replace('/join.php', '', $_SERVER['SCRIPT_NAME']);
$_time = time();
$antiSpamKey = substr(md5('grboardAntiSpamJoin'.$_time), -4);

// 상단 설정, 이동지점 @sirini
if(!empty($boardId) && ($setup['head_file'] or $setup['head_form'])) {
	if($setup['head_file']) {
		ob_start();
		include $setup['head_file'];
		$content = ob_get_contents();
		ob_clean();
		echo str_replace('</head>', '<link rel="stylesheet" href="'.$grboard.'/admin/theme/join/'.$getJoinus['var'].'/style.css" type="text/css" title="style" /></head>', $content);
	}
	if($setup['head_form']) {
		$setup['head_form'] = str_replace('[theme]', $grboard.'/'.$theme, $setup['head_form']);
		$setup['head_form'] = str_replace('</head>', '<style type="text/css"> @import url('.$grboard.'/admin/theme/join/'.$getJoinus['var'].'/style.css); </style></head>', $setup['head_form']);
		echo $setup['head_form'];
	}

// 상/하단 페이지가 없을 때 @sirini
} else {
	$title = 'GR Board Join Page';
	include 'html_head.php';
}

// 회원 가입 테마 부르기 @sirini
include 'admin/theme/join/'.$getJoinus['var'].'/join.php';

// 하단 설정 @sirini
if($boardId) {
	if($setup['foot_form']) echo stripslashes($setup['foot_form']);
	if($setup['foot_file']) include $setup['foot_file'];
}
else { ?></body></html><?php } ?>
