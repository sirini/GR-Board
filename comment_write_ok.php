<?php
// 기본 클래스를 부른다 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 불법적인 글쓰기는 아닌가 체크 @sirini
if(!preg_match('|'.$_SERVER['HTTP_HOST'].'|i', $_SERVER['HTTP_REFERER'])) 
	$GR->error('정상적인 방법으로 게시물을 작성해 주세요.', 1);
	
// 댓글의 답글일 경우 정렬 키 만들기 @sirini
function createOrderKey($familyNo, $parentKey) {
	global $dbFIX, $id, $GR;
	$keyWork = true;
	if($parentKey) {
		$getMaxChild = $GR->getArray('select max(order_key) from '.$dbFIX.'comment_'.$id.' where family_no = '.$familyNo.' and order_key like \''.$parentKey.'_\'');
		if(!$getMaxChild[0]) return $parentKey.'A';
	} else {
		$getMaxChild = $GR->getArray('select max(order_key) from '.$dbFIX.'comment_'.$id.' where family_no = '.$familyNo.' and thread = 1');
		if(!$getMaxChild[0]) return 'AAA';
	}
	$arr = preg_split('//', $getMaxChild[0], -1, PREG_SPLIT_NO_EMPTY);
	$arrCnt = @count($arr) - 1;
	$newArr = $arr;
	$upperUp = 0;
	for($r=$arrCnt; $r>-1; $r--) {
		$ord = ord($arr[$r]);
		if(($ord + $upperUp) < 90) {
			$newArr[$r] = chr($ord+1);
			$upperUp = 0;
			break;
		} else {
			$newArr[$r] = 'A';
			$upperUp = 1;
		}
	}
	$result = '';
	for($e=0; $e<($arrCnt+1); $e++) {
		$result .= $newArr[$e];
	}
	return $result;
}

// 변수 처리 @sirini
if($_POST['id']) $id = $_POST['id']; elseif($_GET['id']) $id = $_GET['id'];
if($_POST['page']) $page = $_POST['page']; elseif($_GET['page']) $page = $_GET['page'];
if($_POST['articleNo']) $articleNo = $_POST['articleNo']; elseif($_GET['articleNo']) $articleNo = $_GET['articleNo'];
if($_POST['modifyTarget']) $modifyTarget = $_POST['modifyTarget']; elseif($_GET['modifyTarget']) $modifyTarget = $_GET['modifyTarget'];
if($_POST['replyTarget']) $replyTarget = $_POST['replyTarget']; elseif($_GET['replyTarget']) $replyTarget = $_GET['replyTarget'];
$ip = $_SERVER['REMOTE_ADDR'];
if($_POST['is_grcode']) $isGrcode = $_POST['is_grcode'];
if($_SESSION['no']) $sessionNo = $_SESSION['no']; else $sessionNo = 0;
if($_POST['clickCategory']) $clickCategory = $_POST['clickCategory']; // 카테고리 선택 후, 자동선택 설정 @PicoZ, @이동규

// 변수 타입 / 필터링 @sirini
$page = (int)$page;
$articleNo = (int)$articleNo;
$modifyTarget = (int)$modifyTarget;
$replyTarget = (int)$replyTarget;
$isGrcode = (int)$isGrcode;

// 테마(스킨)에 있는 글쓰기 처리부터 인클루드 @sirini
$tmpFetchBoard = $GR->getArray("select * from {$dbFIX}board_list where id = '$id'");
@include 'theme/'.$tmpFetchBoard['theme'].'/theme_comment_write_ok.php';

// 글쓴이 권한값 가져오기 @sirini
if($sessionNo && !(int)$sessionNo) exit;
if($sessionNo) {
	$tmpFetch = $GR->getArray("select id, level from {$dbFIX}member_list where no = '$sessionNo'");
	$writerLevel = $tmpFetch['level'];
	if($sessionNo == 1) $isAdmin = 1; else $isAdmin = 0;
	if($_POST['is_secret']) $isSecret = 1; else $isSecret = 0;
	$isMember = 1;
	$getMasters[0] = $tmpFetchBoard['master'];
	$getMasters[1] = $tmpFetchBoard['group_no'];
	if($getMasters[0]) {
		$masterArr = explode('|', $getMasters[0]);
		$masterNum = count($masterArr);
		for($m=0; $m<$masterNum; $m++) {
			if($_SESSION['mId'] && $_SESSION['mId'] == $masterArr[$m]) {
				$isAdmin = 1;
				break;
			}
		}
	}
	if($getMasters[1]) {
		if($getMasters && !(int)$getMasters) exit;
		$getGroupMaster = $GR->getArray('select master from '.$dbFIX.'group_list where no = '.$getMasters[1]);
		$groupMaster = explode('|', $getGroupMaster[0]);
		$cntResult = count($groupMaster);
		for($g=0; $g<$cntResult; $g++) {
			if($_SESSION['mId'] && $_SESSION['mId'] == $groupMaster[$g]) {
				$isAdmin = 1;
				break;
			}
		}
	}
}
else {
	$writerLevel = 1; $isAdmin = 0; $isMember = 0; $isMaster = 0; $isSecret = 0; $sessionNo = 0;
	if(!$_SESSION['antiSpam'] || !$_POST['antispam'] || $_SESSION['antiSpam'] != $_POST['antispam'])
		$GR->error('자동입력방지 답이 올바르지 않습니다', 0, 'HISTORY_BACK');
}

// 현재 게시판의 접근권한을 확인한다. 작성자가 댓글을 허용하지 않으면 댓글을 달 수 없다. @sirini
$isWriteOk['comment_write_level'] = $tmpFetchBoard['comment_write_level'];
if(!$isAdmin && !$isMaster && ($writerLevel < $isWriteOk['comment_write_level'])) $GR->error('코멘트 작성 권한이 없습니다.', 0, 'HISTORY_BACK');
$getArticleOption = $GR->getArray("select * from {$dbFIX}article_option where id = '$id' and article_num = '$articleNo'");
if($getArticleOption['no'] && !$getArticleOption['reply_open']) $GR->error('이 게시물에는 댓글을 작성하실 수 없습니다.', 0, 'HISTORY_BACK');

// 회원의 기본 정보를 가져온다. @sirini
if($isMember) {
	$memberData = $GR->getArray("select no, nickname, password, email, homepage from {$dbFIX}member_list where no = '$sessionNo'");
	$name = $GR->escape($memberData['nickname']);
	$password = $GR->escape($memberData['password']);
	$email = $GR->escape($memberData['email']);
	$homepage = $GR->escape($memberData['homepage']);
}

// 비회원일 경우 처리 @sirini
else {
  
	if($_POST['name']) $name = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['name']))));
	if($_POST['email']) $email = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['email']))));
	if($_POST['homepage']) $homepage = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['homepage']))));
	if($_POST['password']) {
		$tmpPassword = $GR->getArray("select password('".$_POST['password']."')");
		$password = $tmpPassword[0];
	}

	// 입력폼 검사 @sirini
	if(!$name) $GR->error('이름을 입력해 주세요', 0, 'HISTORY_BACK');
	if(!$password) $GR->error('비밀번호를 입력해 주세요', 0, 'HISTORY_BACK');
}

// 제목 내용 처리 @sirini
if($_POST['content']) $content = $GR->unescape($_POST['content']);
else $GR->error('내용을 입력해 주세요', 0, 'HISTORY_BACK');
if($_POST['subject']) $subject = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['subject']))));
else $subject = $name.' 님의 댓글';
$content = $GR->escape(strip_tags($content, '<br><strong><p><a><li><span><font><i><img><u><ol><ul>')); //정밀 처리 필요
$content = str_replace(array('<p>','</p>','<p>&nbsp;</p>'), '', $content);

// 영어로만 입력된글 차단 @sirini
if($tmpFetchBoard['is_english'] && !$isMember) {
	$content = trim($content);
	if(!preg_match('/[\x{1100}-\x{11ff}\x{3130}-\x{318f}\x{ac00}-\x{d7af}]+/u', $content)) {
		$GR->error('스팸성 댓글로 의심되어 차단되었습니다.', 1, 'HISTORY_BACK');
	}
}

// 코멘트 수정일 때 @sirini
if($modifyTarget && !$replyTarget) {
	$key = $GR->getArray("select member_key, password from {$dbFIX}comment_{$id} where no = '$modifyTarget'");
	if($key['member_key']) {
		if(!$isAdmin && !$isMaster && $sessionNo != $key['member_key']) $GR->error('자신이 작성한 코멘트만 수정할 수 있습니다.', 0, 'HISTORY_BACK');
	} else {
		if(!$isAdmin && !$isMaster && $password != $key['password']) $GR->error('비밀번호가 다릅니다.', 0, 'HISTORY_BACK');
	}
	if($tmpFetchBoard['is_history'] > 0) $content .= '<br /><span class="modifyTime">modified at '.date('Y.m.d H:i:s', $GR->grTime()).' by '.(($isAdmin)?'moderator':$name).'</span>';
	if($isMember) {
		$sqlCommentUpdate = "update {$dbFIX}comment_{$id}
			set is_grcode = '$isGrcode', 
			subject = '$subject',
			content = '$content',
			is_secret = '$isSecret'
			where no = '$modifyTarget'";
		$GR->query($sqlCommentUpdate);
	} else {
		$sqlCommentUpdate = "update {$dbFIX}comment_{$id}
			set is_grcode = '$isGrcode', 
			name = '$name',
			email = '$email',
			homepage = '$homepage',
			subject = '$subject',
			content = '$content'
			where no = '$modifyTarget'";
		$GR->query($sqlCommentUpdate);
	}
}
// 코멘트 댓글달 때 @sirini
elseif($replyTarget && !$modifyTarget)
{
	$originComment = $GR->getArray("select no, family_no, thread, order_key from {$dbFIX}comment_{$id} where no = '$replyTarget'");
	$familyNo = $originComment['family_no'];
	$parentKey = $originComment['order_key'];
	$orderKey = createOrderKey($familyNo, $parentKey);
	if(!$originComment['no']) 	$GR->error('답변글을 달 대상 코멘트가 없습니다.', 0, 'HISTORY_BACK');
	$newThread = $originComment['thread'] + 1;
	$thisTime = $GR->grTime();
	$sqlReplyQue = "insert into {$dbFIX}comment_{$id}
		set no = '',
		board_no = '$articleNo',
		family_no = '$familyNo',
		thread = '$newThread',
		member_key = '$sessionNo',
		is_grcode = '$isGrcode',
		name = '$name',
		password = '$password',
		email = '$email',
		homepage = '$homepage',
		ip = '$ip',
		signdate = '$thisTime',
		good = '0',
		bad = '0',
		subject = '$subject',
		content = '$content',
		is_secret = '$isSecret',
		order_key = '$orderKey'";
	$GR->query($sqlReplyQue);
	$insertNo = $GR->getInsertId();
	$GR->query("update {$dbFIX}bbs_{$id} set comment_count = comment_count + 1 where no = '$articleNo'");
	if($isMember) $GR->query("update {$dbFIX}member_list set point = point + 1 where no = '$sessionNo'");

	$totalSubject = $GR->cutString($content, 300);
	$sqlTotalCommentQue = "insert into {$dbFIX}total_comment
		set no = '',
			subject = '$totalSubject',
			id = '$id',
			article_num = '$articleNo',
			comment_num = '$insertNo',
			signdate = '$thisTime',
			is_secret = '$isSecret'";
	$GR->query($sqlTotalCommentQue);

	//  활동 알림판에 기록해두기
	if($originWriter['member_key'] && $originWriter['member_key'] != $sessionNo) {
		$GR->query("insert into {$dbFIX}notification set no = '', to_key = '".$originWriter['member_key']."', from_key = '".$sessionNo."', " .
			"act = '2', bbs_id = '$id', bbs_no = '$articleNo', is_checked = '0'");
	}

// 코멘트 신규 추가일때 @sirini
} else {
	$thisTime = $GR->grTime();
	$sqlNewQue = "insert into {$dbFIX}comment_{$id}
		set no = '',
		board_no = '$articleNo',
		family_no = '0',
		thread = '0',
		member_key = '$sessionNo',
		is_grcode = '$isGrcode',
		name = '$name',
		password = '$password',
		email = '$email',
		homepage = '$homepage',
		ip = '$ip',
		signdate = '$thisTime',
		good = '0',
		bad = '0',
		subject = '$subject',
		content = '$content',
		is_secret = '$isSecret',
		order_key = ''";
	$GR->query($sqlNewQue);
	$familyNo = $GR->getInsertId();
	$GR->query("update {$dbFIX}comment_{$id} set family_no = '$familyNo' where no = '$familyNo'");
	$GR->query("update {$dbFIX}bbs_{$id} set comment_count = comment_count + 1 where no = '$articleNo'");
	if($isMember) $GR->query("update {$dbFIX}member_list set point = point + 1 where no = '$sessionNo'");

	$totalSubject = $GR->cutString($content, 300);
	$sqlTotalCommentQue = "insert into {$dbFIX}total_comment
		set no = '',
			subject = '$totalSubject',
			id = '$id',
			article_num = '$articleNo',
			comment_num = '$familyNo',
			signdate = '$thisTime',
			is_secret = '$isSecret'";
	$GR->query($sqlTotalCommentQue);

	//  활동 알림판에 기록해두기
	if($originWriter['member_key'] && $originWriter['member_key'] != $sessionNo) {
		$GR->query("insert into {$dbFIX}notification set no = '', to_key = '".$originWriter['member_key']."', from_key = '".$sessionNo."', " .
			"act = '1', bbs_id = '$id', bbs_no = '$articleNo', is_checked = '0'");
	}
}

// 완료 후 댓글창 쿠키 삭제. @sirini
if($_COOKIE['pointer'][0]) setcookie('pointer[0]','',time()-3600,'/');
if($_COOKIE['pointer'][1]) setcookie('pointer[1]','',time()-3600,'/');

// 글쓰기 하단 스킨 인클루드 @sirini
@include 'theme/'.$tmpFetchBoard['theme'].'/theme_write_ok_foot.php';

// 완료 처리 @sirini
// 카테고리 선택 후, 자동선택 설정 @PicoZ, @이동규
$GR->move('board.php?id='.$id.'&articleNo='.$articleNo.'&commentPage='.$_POST['commentPage'].'&page='.$page.'&clickCategory='.$clickCategory);
?>
