<?php
// 기본 클래스를 불러온다. @sirini
$preRoute = './';
require $preRoute.'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 관리자인지 확인한다. @sirini
if($_SESSION['no']) {
	if($_SESSION['no'] != 1) $GR->error('관리자 화면은 관리자만 접근할 수 있습니다.<br />'.
		'첫화면으로 가길 원하시면 <a href=http://'.$_SERVER['HTTP_HOST'].'>이 곳을 클릭하세요!</a>', 1);
} else $GR->error('로그인을 해주세요.', 0, 'login.php?adminGo=1');

// 캐쉬 이미지들 모두 정리하기 @sirini
if($_GET['cacheImgDelete']) {
	function rmrf($dir, $isRootDelete=false) {
		if(!$dh = @opendir($dir)) return;
		while (false !== ($obj = @readdir($dh))) {
			if($obj == '.' || $obj == '..') continue;
			if(!@unlink($dir . '/' . $obj)) rmrf($dir.'/'.$obj, true);
		}
		@closedir($dh);	   
		if ($isRootDelete) @rmdir($dir);	   
		return;
	}
	rmrf('phpThumb/cache/');
	$GR->error('썸네일용으로 생성된 캐쉬 이미지들을 모두 정리했습니다.', 0, 'admin.php');
}

// 페이지용 변수 처리 @sirini
if($_GET['page']) $page = $_GET['page']; else $page = 1;

// rewrite 모듈 사용여부 처리 @sirini
if($_GET['rewrite']) {
	if($_GET['rewrite'] == 'on') @rename('./no.use.htaccess', './.htaccess');
	else @rename('./.htaccess', './no.use.htaccess');
	$GR->error('주소 재작성기(mod_rewrite)를 '.$_GET['rewrite'].' 했습니다.', 0, 'admin.php');
}

// 세션을 모두 삭제한다면 처리 @sirini
if($_GET['sessionDelete']) {
	$openSessionDir = @opendir('session');
	while($deleteSession = @readdir($openSessionDir)) {
		if($deleteSession == '.' || $deleteSession == '..') continue;
		@unlink('session/'.$deleteSession);
	}
	@closedir($openSessionDir);
	$GR->error('사용중이던 모든 세션을 삭제했습니다.', 0, 'admin.php');
}

// 시간 동기화 설정 @sirini
if($_GET['timeSync']) {
	@chmod('db_info.php', 0707);
	$timeDiff = $_GET['diff'];
	$saveDbInfo  = '<?php'."\n";
	$saveDbInfo .= '$hostName = \''.$hostName.'\';'."\n";
	$saveDbInfo .= '$userId = \''.$userId.'\';'."\n";
	$saveDbInfo .= '$password = \''.$password.'\';'."\n";
	$saveDbInfo .= '$dbName = \''.$dbName.'\';'."\n";
	$saveDbInfo .= '$dbFIX = \''.$dbFIX.'\';'."\n";
	$saveDbInfo .= '$timeDiff = '.$timeDiff.';'."\n";
	$saveDbInfo .= '@mysql_connect($hostName, $userId, $password);'."\n";
	$saveDbInfo .= '@mysql_select_db($dbName);'."\n";
	$saveDbInfo .= '#@mysql_query(\'set names utf8\'); // 한글이 깨져보일 경우 이 줄 맨 앞에 # 을 제거'."\n";
	$saveDbInfo .= '?>'."\n";
	$fileCreate = fopen('db_info.php', 'w');
	fwrite($fileCreate, $saveDbInfo);
	fclose($fileCreate);
	@chmod('db_info.php', 0404);
	$GR->error('GR보드에게 새 기준시간을 알려주었습니다.', 0, 'admin.php');
}

// 필터링 단어 수정시 처리 @sirini
if($_POST['modifyFilter']) {
	$filterForm = str_replace("\n", '', trim($_POST['filterForm']));
	$openFilterFile = @fopen('filter.txt', 'w');
	@fwrite($openFilterFile, $filterForm);
	@fclose($openFilterFile);
}

// 접근금지 IP 수정시 처리 @sirini
if($_POST['modifyKillIP']) {
	$killIPForm = str_replace("\n", '', trim($_POST['killIPForm']));
	$openKillIPFile = @fopen('out_ip.txt', 'w');
	@fwrite($openKillIPFile, $killIPForm);
	@fclose($openKillIPFile);
}

// DB 에 연결한다. @sirini
$GR->dbConn();

// 신고 목록 정리하기 @sirini
if($_GET['reportListDelete']) {
	$GR->query('truncate table '.$dbFIX.'report');
	$GR->error('신고 목록들을 모두 초기화 했습니다.', 0, 'admin.php');
}

// 로그인 기록을 모두 정리한다면 처리 @sirini
if($_GET['loginLogDelete']) {
	$GR->query('truncate table '.$dbFIX.'login_log');
	$GR->error('보관중이던 로그인 시간 기록들을 모두 초기화 했습니다.', 0, 'admin.php');
}

// 서브 페이지들 테마 설정 처리 @sirini
if($_POST['subPageConfirm']) {
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['loginTheme']."' where opt = 'outlogin_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['memoTheme']."' where opt = 'memo_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['joinTheme']."' where opt = 'join_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['reportTheme']."' where opt = 'report_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['notifyTheme']."' where opt = 'notify_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['infoTheme']."' where opt = 'info_skin'");
	$GR->query('update '.$dbFIX."layout_config set var = '".$_POST['articleAdjustTheme']."' where opt = 'article_adjust'");
	
	$GR->error('서브페이지들의 테마를 설정하였습니다.', 0, 'admin.php');
}

// 오류 로그를 모두 삭제한다면 처리 @sirini
if($_GET['errorLogDelete']) {
	$GR->query('delete from '.$dbFIX.'error_save');
	$GR->error('모든 오류 정보가 삭제되었습니다', 0, 'admin.php');
}

// 쪽지함을 정리한다면 처리 @sirini
if($_GET['memoDelete']) {
	$thisTime = $GR->grTime();
	$lessThenMe = $thisTime - 604800;
	$GR->query('delete from '.$dbFIX.'memo_save where signdate < '.$lessThenMe);
	$GR->error('오래된 쪽지들을 모두 정리했습니다.', 0, 'admin.php');
}

// 내 알림을 정리한다면 처리 @sirini
if($_GET['notiDelete']) {
	$GR->query('truncate '.$dbFIX.'notification');
	$GR->error('내 알림 목록을 초기화 했습니다.', 0, 'admin.php');
}

// 사용중인 Table 의 오류들을 수정하고 최적화 한다. @sirini
if($_GET['repairDB']) {
	$getAllTable = $GR->query('select id from '.$dbFIX.'board_list');
	while($table = mysql_fetch_array($getAllTable)) {
		$repairTarget = $table['id'];
		$GR->query('repair table '.$dbFIX.'bbs_'.$repairTarget);
		$GR->query('repair table '.$dbFIX.'comment_'.$repairTarget);
		$GR->query('optimize table '.$dbFIX.'bbs_'.$repairTarget);
		$GR->query('optimize table '.$dbFIX.'comment_'.$repairTarget);
	}
	$GR->query('repair table '.$dbFIX.'board_list');
	$GR->query('repair table '.$dbFIX.'member_list');
	$GR->query('repair table '.$dbFIX.'error_save');
	$GR->query('repair table '.$dbFIX.'pds_save');
	$GR->query('repair table '.$dbFIX.'group_list');
	$GR->query('repair table '.$dbFIX.'poll_option');
	$GR->query('repair table '.$dbFIX.'poll_comment');
	$GR->query('repair table '.$dbFIX.'poll_subject');
	$GR->query('repair table '.$dbFIX.'time_bomb');
	$GR->query('repair table '.$dbFIX.'total_article');
	$GR->query('repair table '.$dbFIX.'total_comment');
	$GR->query('repair table '.$dbFIX.'member_group');
	$GR->query('repair table '.$dbFIX.'layout_config');
	$GR->query('repair table '.$dbFIX.'report');
	$GR->query('repair table '.$dbFIX.'auto_save');
	$GR->query('repair table '.$dbFIX.'pds_extend');
	$GR->query('repair table '.$dbFIX.'tag_list');
	$GR->query('repair table '.$dbFIX.'article_option');
	$GR->query('repair table '.$dbFIX.'login_log');
	$GR->query('repair table '.$dbFIX.'notification');
	$GR->query('optimize table '.$dbFIX.'board_list');
	$GR->query('optimize table '.$dbFIX.'member_list');
	$GR->query('optimize table '.$dbFIX.'error_save');
	$GR->query('optimize table '.$dbFIX.'pds_save');
	$GR->query('optimize table '.$dbFIX.'group_list');
	$GR->query('optimize table '.$dbFIX.'poll_option');
	$GR->query('optimize table '.$dbFIX.'poll_comment');
	$GR->query('optimize table '.$dbFIX.'poll_subject');
	$GR->query('optimize table '.$dbFIX.'time_bomb');
	$GR->query('optimize table '.$dbFIX.'total_article');
	$GR->query('optimize table '.$dbFIX.'total_comment');
	$GR->query('optimize table '.$dbFIX.'member_group');
	$GR->query('optimize table '.$dbFIX.'layout_config');
	$GR->query('optimize table '.$dbFIX.'report');
	$GR->query('optimize table '.$dbFIX.'auto_save');
	$GR->query('optimize table '.$dbFIX.'pds_extend');
	$GR->query('optimize table '.$dbFIX.'tag_list');
	$GR->query('optimize table '.$dbFIX.'article_option');
	$GR->query('optimize table '.$dbFIX.'login_log');
	$GR->query('optimize table '.$dbFIX.'notification');
	$GR->error('모든 테이블의 오류를 수정하고, 최적화를 실시했습니다.', 0, 'admin.php');
}

// 따로 모아진 트랙백 중 선택된 트랙백 삭제 처리 @sirini
if($_GET['deleteTrackbackNo']) {
	$GR->query('delete from '.$dbFIX.'trackback_save where no = '.$_GET['deleteTrackbackNo']);
	$GR->error('해당 트랙백을 삭제했습니다.', 0, 'admin.php');
}

// 트랙백을 모두 삭제 처리 @sirini
if($_GET['deleteTrackback']) {
	$GR->query('truncate table '.$dbFIX.'trackback_save');
	$GR->error('별도로 기록된 트랙백들을 모두 삭제했습니다.', 0, 'admin.php');
}

// 통합 최근 게시물/댓글 테이블 정리 @sirini
if($_GET['confirmTotalLatestNow']) {
	$getLatestTotalPost = $GR->query('select no, id, article_num from '.$dbFIX.'total_article order by no desc limit 1000');
	while($lps = $GR->fetch($getLatestTotalPost)) {
		$isLinkAvailable = $GR->getArray('select no from '.$dbFIX.'bbs_'.$lps['id'].' where no = '.$lps['article_num']);
		if(!$isLinkAvailable['no']) $GR->query('delete from '.$dbFIX.'total_article where no = '.$lps['no']);
	}
	$getLatestTotalReply = $GR->query('select no, id, article_num from '.$dbFIX.'total_comment order by no desc limit 1000');
	while($lcs = $GR->fetch($getLatestTotalReply)) {
		$isLinkAvailable = $GR->getArray('select no from '.$dbFIX.'comment_'.$lcs['id'].' where board_no = '.$lcs['article_num']);
		if(!$isLinkAvailable['no']) $GR->query('delete from '.$dbFIX.'total_comment where no = '.$lcs['no']);
	}
	$GR->error('통합 최근 게시물/댓글 테이블에 기록된 정보들의 유효성을 점검했습니다.', 0, 'admin.php');
}

// 각종 레코드 수를 구한다. @sirini
$totalBoardNum = $GR->getArray('select count(*) from '.$dbFIX.'board_list');
$totalMemberNum = $GR->getArray('select count(*) from '.$dbFIX.'member_list');
$totalPdsNum = $GR->getArray('select count(*) from '.$dbFIX.'pds_save');
$totalPdsExtendNum = $GR->getArray('select count(*) from '.$dbFIX.'pds_extend');
$totalErrorNum = $GR->getArray('select count(*) from '.$dbFIX.'error_save');
$totalMemoNum = $GR->getArray('select count(*) from '.$dbFIX.'memo_save');
$totalNotiNum = $GR->getArray('select count(*) from '.$dbFIX.'notification');
$totalPollCommentNum = $GR->getArray('select count(*) from '.$dbFIX.'poll_comment');
$totalLoginLogNum = $GR->getArray('select count(*) from '.$dbFIX.'login_log');

// 사용중인 세션파일을 구한다. @sirini
$sessionDirOpen = @opendir('session');
$totalSession = 0;
while($readSession = @readdir($sessionDirOpen)) {
	if($readSession == '.' || $readSession == '..') continue;
	$totalSession++;
}
@closedir($sessionDirOpen);

// 문서설정 @sirini
$title = 'GR Board Admin Page';
$htmlHeadAdd = '<script src="js/jquery.js"></script>'."\n".
'<link rel="stylesheet" href="js/jqueryui/css/smoothness/jquery-ui.custom.tab.css" type="text/css" />'."\n".
'<script src="js/jqueryui/js/jquery-ui.custom.tab.min.js"></script>'."\n".
'<script src="admin/admin.js"></script>';
include 'html_head.php';
?>