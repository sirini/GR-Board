<?php
// 기본 클래스 호출
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 보안강화
if(isset($_GET['id'])) $id = $_GET['id']; else $id = $_POST['id'];

// 로그인 되었을 때
if($_SESSION['no']) {
	$sessionNo = (int)$_SESSION['no'];
	$tmpFetch = $GR->getArray('select level from '.$dbFIX.'member_list where no = \''.$sessionNo.'\'');
	$visitorLevel = $tmpFetch['level'];
	if($_SESSION['no'] == 1) $isAdmin = 1; else $isAdmin = 0;
	$isMember = 1;
	$getMasters = $GR->getArray('select master, group_no from '.$dbFIX.'board_list where id = \''.$id.'\'');

	// 게시판 관리자
	if($getMasters[0]) {
		$masterArr = explode('|', $getMasters[0]);
		$masterNum = count($masterArr);
		for($m=0; $m<$masterNum; $m++) {
			if($_SESSION['mId'] && $_SESSION['mId'] == $masterArr[$m]) {
				$isAdmin = 1; break;
			}
		}
	}

	// 그룹 관리자
	if($getMasters[1]) {
		$getGroupMaster = $GR->getArray('select master from '.$dbFIX.'group_list where no = '.$getMasters[1]);
		$groupMaster = explode('|', $getGroupMaster[0]);
		$cntResult = count($groupMaster);
		for($g=0; $g<$cntResult; $g++) {
			if($_SESSION['mId'] && $_SESSION['mId'] == $groupMaster[$g]) {
				$isAdmin = 1; break;
			}
		}
	}

// 비회원일 때
} else { 
	$sessionNo = 0; $visitorLevel = 1; $isAdmin = 0; $isMember = 0;
}

// 접근제한이 있으면 제한. 2 이상은 모두 회원전용. (1 = 손님가능)
$tmpFetchBoard = $GR->getArray('select * from '.$dbFIX.'board_list where id = \''.$id.'\'');
if(!$tmpFetchBoard['no']) $GR->error('게시판이 생성되지 않았습니다.');
if(!$isAdmin && !$isMaster && ($tmpFetchBoard['enter_level'] > $visitorLevel)) $GR->error('접근하실 수 있는 권한이 없습니다.');

// 변수 처리
if($_GET['articleNo']) $articleNo = (int)$_GET['articleNo'];
if($_GET['page']) $page = (int)$_GET['page'];
if($_GET['alreadyEnterPassword']) $alreadyEnterPassword = $_GET['alreadyEnterPassword'];
if($_GET['replyTarget']) $replyTarget = (int)$_GET['replyTarget'];
if($_GET['modifyTarget']) $modifyTarget = (int)$_GET['modifyTarget'];
if($_GET['good']) $good = (int)$_GET['good'];
if($_GET['bad']) $bad = (int)$_GET['bad'];	
if($_GET['voteCommentNo']) $voteCommentNo = (int)$_GET['voteCommentNo'];
if($_GET['division']) $division = $_GET['division']; else $division = $_POST['division'];
$division = (int) $division;
if($_GET['originDivision']) $originDivision = $_GET['originDivision']; else $originDivision = $_POST['originDivision'];
$originDivision = (int) $originDivision;
if($_GET['clickCategory']) $clickCategory = urldecode($_GET['clickCategory']);
if($_GET['searchOption']) $searchOption = $_GET['searchOption']; elseif($_POST['searchOption']) $searchOption = $_POST['searchOption'];
if($_GET['sortList']) $sortList = $_GET['sortList']; else $sortList = 'no';
if($_GET['sortBy']) $sortBy = $_GET['sortBy']; else $sortBy = 'desc';
if($_GET['searchText']) $searchText = urldecode(htmlspecialchars($_GET['searchText']));
elseif($_POST['searchText']) {
	$searchText = urldecode(htmlspecialchars($_POST['searchText']));
	$searchText = str_replace("'","''",$_POST['searchText']);
}
if($tmpFetchBoard['view_level'] != 1 && $tmpFetchBoard['view_level'] > $_SESSION['level']){unset($searchOption); unset($searchText);} //차후 권한 세분화 필요 by pico
$grboard = str_replace('/'.end(explode('/', $_SERVER['REQUEST_URI'])), '', $_SERVER['REQUEST_URI']);

// 테마에 사용될 기본변수 정리
$cutingSubject = $tmpFetchBoard['cut_subject'];
$tmpThemeName = $tmpFetchBoard['theme'];
$theme = 'theme/'.$tmpThemeName;
if($tmpFetchBoard['category']) $isCategory = 1; else $isCategory = 0;
$isRSS = 1;
if(!$tmpFetchBoard['is_rss'] || (($tmpFetchBoard['view_level'] > $visitorLevel) && ($_SESSION['no'] != 1))) $isRSS = 0;

// 상단파일 불러오기 전 확장파일 인클루드
$extendDir = @dir('extend');
if($extendDir){
	while (false !== ($entry = $extendDir->read())) {
	   if(end(explode('.',$entry)) == 'php') include 'extend/'.$entry;
	}
	$extendDir->close();
}

// 상단에 불러올 파일과 내용을 처리
if($tmpFetchBoard['head_file']) include $tmpFetchBoard['head_file'];
echo str_replace('[theme]', $grboard.'/theme/'.$tmpThemeName, $tmpFetchBoard['head_form']);

// 목록 혹은 내용 보기
if(empty($articleNo)) include 'list.php';
else {
	include 'view.php';
	if($tmpFetchBoard['is_list']) include 'list.php';
}

// 하단에 불러올 파일과 내용을 처리한다.
echo $tmpFetchBoard['foot_form'];
if($tmpFetchBoard['foot_file']) include $tmpFetchBoard['foot_file'];
?>
