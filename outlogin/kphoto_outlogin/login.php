<?php
// 이동할 주소 정하기
if($_SERVER['HTTPS'] != 'on'){$protocol = "http://";} else{$protocol = "https://";}
$boardTargetID = $_GET['id'];
if($boardTargetID) $move = 'board.php?id='.$boardTargetID;
else $move = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
?>
<div id="grLoginForm">
	<form id="login" method="post" action="<?php echo $grboard; ?>/login_ok.php">
	<div><input type="hidden" name="fromPage" value="<?php echo $move; ?>" /><input type="hidden" name="boardID" value="<?php echo $boardTargetID; ?>" /></div>
	<ul>
		<li>사원번호: <input type="text" name="id" placeholder="여기에 사원 번호를 입력하세요" class="in" /></li>
		<li>비밀번호: <input type="password" name="password" placeholder="여기에 비밀번호를 입력하세요" class="in" /></li>
		<li><input type="image" src="<?php echo $grboard; ?>/outlogin/<?php echo $theme; ?>/login.gif" class="btn" /><a href="#" onclick="window.open('<?php echo $grboard; ?>/join.php?fromPage=outlogin','join','width=650,height=650,menubar=no,scrollbars=yes');"><img src="<?php echo $grboard; ?>/outlogin/<?php echo $theme; ?>/join.gif" alt="멤버등록" class="btn" /></a></li>
	</ul>
	</form>
</div>