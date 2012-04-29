<?php
// 기본 클래스를 불러온다. @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 넘겨받은 값을 처리한다. @sirini
$goAdminPage = $_POST['goAdminPage'];
$id = $_POST['id'];
$boardID = $_POST['boardID'];
$password = $_POST['password'];
if($_POST['fromPage']) $fromPage = urldecode($_POST['fromPage']);
if($_GET['fromPage']) $fromPage = urldecode($_GET['fromPage']);
$checkPage = end(explode('/', $fromPage));
if($fromPage && $checkPage == 'logout.php') $fromPage = '';
if($checkPage == 'login.php') $fromPage = 'http://'.$_SERVER['HTTP_HOST'];

// 해당 아이디가 로그인실패 정지되어있는지 확인 @sirini
include 'config_member.php';
$block = $GR->getArray("select no, nickname, blocks, id from {$dbFIX}member_list where id = '$id'");

if($enableBlock) {
  if($block['blocks'] >= $enableBlockNum) {
  $subject = "[알림] {$id}님의 로그인실패 허용횟수 초과.";
  $subject = $GR->escape($subject); //GPC아님 지우면 안 됨
  $content = "$id($block[nickname]) 님의 로그인실패 허용횟수가 초과되어 로그인이 차단되었습니다.\n타인에 의한 고의적인 해킹시도라면 적절한 조치를 취해주세요.\n\n<a href=\"admin_member.php?memberID=$id\" onclick=\"window.open(this.href, \'_blank\'); return false\">[로그인 차단 해제하기]</a>";
  $content = $GR->escape($content); //GPC아님 지우면 안 됨
  $GR->query("insert into {$dbFIX}memo_save set member_key = '1', sender_key = '".$block['no']."', subject = '$subject', content = '$content', signdate = '".$GR->grTime()."', is_view = '0'");
  $GR->error('로그인실패 허용횟수를 초과하셨으므로, 로그인할 수 없습니다.\n관리자에게 쪽지를 보냈으며, 제한해제를 원하신다면 관리자에게 문의하세요.', 1, 'login.php?boardID='.$boardID.'&fromPage='.urlencode($fromPage));
  }
}

// 아이디와 비밀번호가 맞다면 인증해준다. @sirini
if($id && $password) {

	$member = $GR->getArray("select level, no, id from {$dbFIX}member_list where id = '$id' and password = password('$password')");
	
	// 비회원이 작성한 글일 때 @sirini
	if(!$member['no']) {
		if($enableBlock) {
			$block['blocks']++;
			$GR->query("update {$dbFIX}member_list set blocks = '".$block['blocks']."' where id = '$id'");
	   
			// 아이디가 존재하는지 확인 @sirini
			if($id == $block['id']) { 
				$GR->error('비밀번호가 올바르지 않습니다. ('.$block['blocks'].'회 실패)', 0, 'login.php?boardID='.$boardID.'&fromPage='.urlencode($fromPage));
			} else { 
				$GR->error('아이디 혹은 비밀번호가 올바르지 않습니다.', 0, 'login.php?boardID='.$boardID.'&fromPage='.urlencode($fromPage)); 
			}
		} else { 
			$GR->error('아이디 혹은 비밀번호가 올바르지 않습니다.', 0, 'login.php?boardID='.$boardID.'&fromPage='.urlencode($fromPage)); 
		}
	}

	// 멤버가 작성한 글일 때 @sirini
	else {
		$_SESSION['no'] = $member['no'];
		$_SESSION['mId'] = $member['id'];
		$_SESSION['level'] = $member['level'];
		$_time = $GR->grTime();
		$GR->query("update {$dbFIX}member_list set lastlogin = '$_time' where no = '".$member['no']."' limit 1");
		$GR->query("insert into {$dbFIX}login_log set no = '', member_key = '".$member['no']."', signdate = '$_time', ip = '".$_SERVER['REMOTE_ADDR']."', ref = '$fromPage'");
		
		// 로그인 실패 횟수를 초기화한다. @sirini
		$GR->query("update {$dbFIX}member_list set blocks = '0' where no = '".$member['no']."'");
	}
}

// 자동 로그인 체크 시 처리 @sirini
if($_POST['auto_login']) @setcookie('auto_login', $_SESSION['no'], $GR->grTime()+2592000);

// 아이디 저장 체크 시 처리 @sirini
if($_POST['id_save']) @setcookie('id_save', $id, $GR->grTime()+2592000); else if($_COOKIE['id_save']) @setcookie('id_save', '');

// 문서설정 @sirini
$title = 'GR Board Login check';
include 'html_head.php';
?>

<body>
<?php
// 요청받은 페이지로 이동하거나, 관리자 페이지로 이동한다. @sirini
if($member['no'] == 1) {
	echo '<script>';
	if($fromPage) {
		echo "location.href='{$fromPage}';";
	} else {
		if($goAdminPage) echo "location.href='admin.php';";
		else {
			echo "if(confirm('관리자님, 접속을 환영합니다. 어디로 모실까요?\\n\\n'+".
			"'확인(OK)를 누르시면 관리화면으로 가며, 취소(Cancel)를 누르시면 게시판으로 돌아갑니다.'))".
			"{ location.href='admin.php'; } else { location.href='board.php?id={$boardID}'; }";
		}
	}
	echo '</script>';
} else {
	echo '<script>';
	if($fromPage) echo "location.href='{$fromPage}';";
	elseif($boardID) echo "location.href='board.php?id={$boardID}';";
	else echo "location.href='admin.php';";
	echo '</script>';
}
?>

<noscript>
<?php if(!$member['no'] == 1) { ?>
<h1>ADMIN Logined! :: 관리자권한으로 로그인 하셨습니다.</h1>
<p>관리자님, 접속을 환영합니다. 어디로 모실까요?</p>
<ul>
	<li><a href="admin.php">관리자페이지로 이동합니다. [Move to adminpage]</a></li>
	<li><a href="<?php echo $fromPage; ?>">이전 게시판으로 이동합니다. [Move to board]</a></li>
</ul>
<?php } else { ?>
<h1>Logined! :: 로그인 되셨습니다.</h1>
<p><a href="<?php echo $fromPage; ?>">처음 로그인하셨던 페이지로 이동하시려면 여기를 눌러주세요. [Click to Move]</a></p>
<?php } ?>
</noscript>

</body>
</html>
