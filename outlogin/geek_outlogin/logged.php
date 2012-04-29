<?php
// 쪽지알림 레이어 표시여부
$msg_poupop = "1"; // 설정할경우 1, 설정하지 않을경우 빈값
if(!$_SESSION['no']) die("로그인이 필요합니다.");

// 이동할 주소 정하기
if($_SERVER['HTTPS'] != 'on'){$protocol = "http://";} else{$protocol = "https://";}
$boardTargetID = $_GET['id'];
if($boardTargetID) $move = 'board.php?id='.$boardTargetID;
else $move = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
?>
<div id="grLoginForm">
	<div class="loginLeft">ID</div>
	<div class="loginRight"><?php echo $login['id']; ?>
	&nbsp;&nbsp;<a href="<?php echo $grboard; ?>/view_memo.php" onclick="window.open(this.href, '_blank', 'width=600,height=650,menubar=no,scrollbars=yes'); return false" title="클릭하시면 쪽지함을 열어봅니다."><img src="<?php echo $grboard; ?>/outlogin/<?php echo $theme; ?>/note.png" alt="m" /></a>
	</div>
	<div class="loginClear"></div>
	<div class="loginLeft">Nick</div>
	<div class="loginRight"><?php echo $login['nickname']; ?>
	<?php if($_SESSION['no'] == 1) { ?>&nbsp;&nbsp;<a href="<?php echo $grboard; ?>/admin.php" style="color: #ff0000; text-decoration: none">admin</a><?php } ?>
	</div>
	<div class="loginClear"></div>
	<div class="loginAlign">
	<a href="#" onclick="window.open('<?php echo $grboard; ?>/info.php','my_info','width=650,height=600,menubar=no,scrollbars=yes');"><img src="<?php echo $grboard; ?>/outlogin/<?php echo $theme; ?>/modify.gif" alt="정보수정" /></a>
	<a href="#" onclick="location.href='<?php echo $grboard; ?>/logout.php?page=<?php echo $move; ?>'"><img src="<?php echo $grboard; ?>/outlogin/<?php echo $theme; ?>/logout.gif" alt="로그아웃" /></a>
	</div>
</div>