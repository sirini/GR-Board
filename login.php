<?php
// 기본 클래스를 불러온다. @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 만약 이미 로긴되어 있다면 경고페이지로 이동한다. @sirini
if($_SESSION['no'] == 1) $GR->error('이미 로그인 되어 있습니다. 관리자 화면으로 갑니다.', 0, 'admin.php');
elseif($_SESSION['no'] > 1) $GR->error('이미 로그인 되어 있습니다. 첫페이지로 갑니다.', 0, $_SERVER['HTTP_HOST']);

// 자동 로그인을 사용중이라면 바로 패스 @sirini
if($_COOKIE['auto_login']) {
	$_time = $GR->grTime();
	$GR->query("update {$dbFIX}member_list set lastlogin = '$_time' where no = '".$_COOKIE['auto_login']."' limit 1");
	$GR->query("insert into {$dbFIX}login_log set no = '', member_key = '".$_COOKIE['auto_login']."', signdate = '$_time'");
	$_SESSION['no'] = $_COOKIE['auto_login'];
	if($_GET['boardID']) $GR->move('board.php?id='.$_GET['boardID']);
	elseif($_GET['fromPage']) $GR->move($_GET['fromPage']);
}

// 변수 처리 @sirini
$boardID = $_GET['boardID'];
if($_SERVER['HTTP_REFERER'] && !strstr($_SERVER['HTTP_REFERER'],'login_ok.php')) $fromPage = $_SERVER['HTTP_REFERER']; else $fromPage = $_GET['fromPage'];
if($_GET['adminGo']) $goAdminPage = 1; else $goAdminPage = 0;
$setup = $GR->getArray("select head_file, head_form, foot_form, foot_file, theme from {$dbFIX}board_list where id = '$boardID'");
$theme = 'theme/'.$setup['theme'];
$grboard = str_replace('/'.end(explode('/', $_SERVER['REQUEST_URI'])), '', $_SERVER['REQUEST_URI']);
$getOutlogin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'outlogin_skin\' limit 1');
if(!$getOutlogin['var']) $getOutlogin['var'] = 'default';

// 상단 설정, 이동지점 @sirini
if(!empty($boardID) && ($setup['head_file'] || $setup['head_form'])) {
	if($setup['head_file']) {
		ob_start();
		include $setup['head_file'];
		$content = ob_get_contents();
		ob_clean();
		echo str_replace('</head>', '<link rel="stylesheet" href="'.$grboard.'/admin/theme/outlogin/'.$getOutlogin['var'].'/style.css" type="text/css" title="style" /></head>', $content);
	}
	if($setup['head_form']) {
		$setup['head_form'] = str_replace('[theme]', $grboard.'/'.$theme, $setup['head_form']);
		$setup['head_form'] = str_replace('</head>', '<link rel="stylesheet" href="'.$grboard.'/admin/theme/outlogin/'.$getOutlogin['var'].'/style.css" type="text/css" title="style" /></head>', $setup['head_form']);
		echo $setup['head_form'];
	}

// 상/하단이 없을 시 @sirini
} else {
	$title = 'GR Board Login Page';
	include 'html_head.php';
}

// 로그인 테마 부르기 @sirini
include 'admin/theme/outlogin/'.$getOutlogin['var'].'/login.php';
?>

<script src="<?php echo $grboard; ?>/js/login.js"></script>

<?php
// 하단 설정 @sirini
if($boardID || $boardId) {
	if($setup['foot_form']) echo $setup['foot_form'];
	if($setup['foot_file']) include $setup['foot_file'];
}
else { ?></body></html><?php } ?>
