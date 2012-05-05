<?php
// 기본 클래스를 부른다 @sirini
include 'class/common.php';
$GR = new COMMON;

// 접근차단 IP 조회 @sirini
$getKillIPList = @file_get_contents('out_ip.txt');
$tArrIP = explode(',', $getKillIPList);
$numKillIP = count($tArrIP);
if($getKillIPList) for($tki=0; $tki<$numKillIP; $tki++) if($tArrIP[$tki] == $_SERVER['REMOTE_ADDR']) $GR->error('차단된 IP 입니다.');

$GR->dbConn();

// 변수 처리 @sirini
if(isset($_GET['id']) && $_GET['id']) $id = $_GET['id'];
if(isset($_GET['articleNo']) && $_GET['articleNo']) $articleNo = $_GET['articleNo'];
if(isset($_GET['page']) && $_GET['page']) $page = $_GET['page'];
if(isset($_GET['mode']) && $_GET['mode']) $mode = $_GET['mode'];
if(isset($_GET['alreadyEnterPassword']) && $_GET['alreadyEnterPassword']) $alreadyEnterPassword = $_GET['alreadyEnterPassword'];
if(isset($_GET['isReported'])) $isReported = $_GET['isReported'];
if(isset($_GET['clickCategory']) && $_GET['clickCategory']) $clickCategory = $_GET['clickCategory']; // 카테고리 선택 후, 글쓰기 할때 자동선택 설정 @PicoZ, @이동규
$grboard = str_replace('/'.end(explode('/', $_SERVER['REQUEST_URI'])), '', $_SERVER['REQUEST_URI']);

// 글쓴이 권한값 가져오기 @sirini
if($_SESSION['no']) {
	$tmpFetch = $GR->getArray('select id, level from '.$dbFIX.'member_list where no = '.$_SESSION['no']);
	$writerLevel = $tmpFetch['level'];
	if($_SESSION['no'] == 1) $isAdmin = 1; else $isAdmin = 0;
	$isMember = 1;

	// 게시판 마스터들은 관리자와 동등한 권한 유지 @sirini
	$getMasters = $GR->getArray('select master from '.$dbFIX.'board_list where id = \''.$id.'\'');
	if($getMasters[0]) {
		$masterArr = @explode('|', $getMasters[0]);
		$masterNum = @count($masterArr)+1;
		for($m=0; $m<$masterNum; $m++) {
			if($_SESSION['mId'] && ($_SESSION['mId'] == $masterArr[$m])) {
				$isAdmin = 1; break;
			}
		}
	}

// 비회원일 경우 처리 @sirini
} else {
	$writerLevel = 1;
	$isAdmin = 0;
	$isMember = 0;
	$isMaster = 0;

	// 스팸방지용 질문코드 (산수) @sirini
	if(!$_SESSION['no']) {
		$antiSpam0 = mt_rand(1, 9);
		$antiSpam1 = mt_rand(1, 9);
		$antiSpam2 = mt_rand(0, 1);
		if($antiSpam2) {
			$_SESSION['antiSpam'] = $antiSpam0 + $antiSpam1;
			$antiSpam3 = '+';
		}
		else {
			$_SESSION['antiSpam'] = $antiSpam0 * $antiSpam1;
			$antiSpam3 = 'x';
		}
	}
}

// 현재 게시판의 접근권한을 확인한다. @sirini
$isWriteOk = $GR->getArray("select write_level from {$dbFIX}board_list where id = '$id'");
if(!$isAdmin && !$isMaster && ($writerLevel < $isWriteOk['write_level'])) $GR->error('글쓰기 권한이 없습니다.', 0, $grboard.'/board.php?id='.$id);

// 게시판 설정 가져오기 @sirini
$tmpFetchBoard = $GR->getArray("select * from {$dbFIX}board_list where id = '$id'");

// 만약 수정글일 경우 처리 @sirini
if($mode && $articleNo) {
	$getContents = $GR->query("select * from {$dbFIX}bbs_{$id} where no = '$articleNo'");
	$modify = $GR->fetch($getContents);
	if(!$modify['no']) $GR->error('삭제된 게시물은 수정할 수 없습니다.', 0, $grboard.'/board.php?id='.$id);
	$getArticleOption = $GR->getArray("select no, reply_open, reply_notify from {$dbFIX}article_option where id = '$id' and article_num = '$articleNo'");
	$modify['option_reply_open'] = ($getArticleOption['no']) ? $getArticleOption['reply_open'] : 0;
	$modify['option_reply_notify'] = ($getArticleOption['no']) ? $getArticleOption['reply_notify'] : 0;

	// 회원이 작성한 글은 해당 회원(+관리자)만 수정가능 @sirini
	if(!$isAdmin && !$isMaster && $modify['member_key'] && ($modify['member_key'] != $_SESSION['no'])) {
		$GR->error('본인이 작성한 글만 수정할 수 있습니다.', 1, $grboard.'/board.php?id='.$id.'&articleNo='.$articleNo);
	}
	
	// 카테고리가 있을 시 셀렉트 박스 만들기 @sirini
	if($tmpFetchBoard['category']) {
		$isCategory = 1;
		$tempCategoryArray = explode('|', $tmpFetchBoard['category']);
		$countTempArray = count($tempCategoryArray);
		$category = '<select name="category">';
		for($ti=0; $ti<$countTempArray; $ti++) {
			if($tempCategoryArray[$ti] == $modify['category']) $category .= '<option value="'.$tempCategoryArray[$ti].'" selected>'.$tempCategoryArray[$ti].'</option>';
			else $category .= '<option value="'.$tempCategoryArray[$ti].'">'.$tempCategoryArray[$ti].'</option>';
		}
		$category .= '</select>';
	}
	else $isCategory = 0;

	// 비밀글일 경우 @sirini
	if(!$isAdmin && $modify['is_secret']) {
		if($alreadyEnterPassword) {
			$getOldPass = $GR->query("select password from {$dbFIX}bbs_{$id} where no = '$articleNo'"); // @좋아, @이동규
			$tFetchPass = $GR->fetch($getOldPass);
			if($alreadyEnterPassword != sha1($tFetchPass['password'])) $GR->error('입력하셨던 패스워드로 게시물에 접근하지 못했습니다.', 0, $grboard.'/board.php?id='.$id.'&page='.$page);
		}
		else $GR->move('enter_password.php?id='.$id.'&articleNo='.$articleNo.'&readyWork=write');
	}

	// 업로드한 파일 가져오기 @sirini
	$getFiles = $GR->query("select file_route1, file_route2, file_route3, file_route4, file_route5, file_route6,".
		"file_route7, file_route8, file_route9, file_route10 from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");
	$oldFile = $GR->fetch($getFiles);	
	
	$subject = htmlspecialchars($modify['subject']);
	$content = $modify['content'];	
	$content = str_replace('&amp;nbsp;', ' ', $content);

// 신규 글일 경우 카테고리 처리 @sirini
} else {

	// 카테고리가 있을 시 셀렉트 박스 만들기 @sirini
	if($tmpFetchBoard['category']) {
		$isCategory = 1;
		$tempCategoryArray = explode('|', $tmpFetchBoard['category']);
		$countTempArray = count($tempCategoryArray);
		$category = '<select name="category">';
		for($ti=0; $ti<$countTempArray; $ti++) 
			// 카테고리 선택 후, 글쓰기 할때 자동선택 설정 @컴센스, @이동규
			$category .= '<option value="'.$tempCategoryArray[$ti].'"'.
				(($tempCategoryArray[$ti] == $clickCategory)?' selected="selected"':'').'>'.$tempCategoryArray[$ti].'</option>';
		$category .= '</select>';
	}
	else $isCategory = 0;
}

// 테마에 사용될 기본변수 정리 @sirini
$theme = 'theme/'.$tmpFetchBoard['theme'];
$totalFiles = $tmpFetchBoard['num_file'];
if($tmpFetchBoard['category']) $isCategory = 1; else $isCategory = 0;

// 상단에 불러올 파일과 내용을 처리한다. @sirini
if(file_exists($tmpFetchBoard['head_file'])) include $tmpFetchBoard['head_file'];
echo str_replace('[theme]', $grboard.'/theme/'.$tmpFetchBoard['theme'], $tmpFetchBoard['head_form']);

// 테마 불러오기 @sirini
include $theme.'/write.php';

// 하단에 불러올 파일과 내용을 처리한다. @sirini
echo $tmpFetchBoard['foot_form'];
if(file_exists($tmpFetchBoard['foot_file'])) include $tmpFetchBoard['foot_file'];
?>
