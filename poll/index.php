<?php
// 기본 클래스를 부른다
$preRoute = '../';
include $preRoute.'class/common.php';
$GR = new COMMON;
$GR->dbConn();
$p = (int)$_GET['p'];

// XSS 방지
if($_GET['deleteComment'] && !(int)$_GET['deleteComment']) exit;
$deleteComment = (int)$_GET['deleteComment'];
$addOption = (int)$_GET['addOption'];

// 투표 반영
if($_GET['addVote']) {
	if($_SESSION['alreadyPoll']) $GR->error('이미 투표 하셨습니다.', 0, './?p='.$p);
	@mysql_query("update {$dbFIX}poll_option set vote = vote + 1 where no = '".$addOption."'");
	$_SESSION['alreadyPoll'] = true;
	$GR->move('./?p='.$p);
}

// 의견 삭제
if($deleteComment) {
	$member_no = @mysql_query("select member_no from {$dbFIX}poll_comment where poll_no = '$p'");	
	if( $member_no == $_SESSION['no'] || $_SESSION['no'] == '1' ) {
		@mysql_query("delete from {$dbFIX}poll_comment where no = '".$deleteComment."'");
		$GR->error('댓글을 삭제 하였습니다.', 0, './?p='.$p);
	}
	else $GR->error('본인이 작성한 댓글만 삭제할 수 있습니다.', 0, './?p='.$p);
}

// 의견 넣기
if($_POST['content']) {
	@extract($_POST);
	$sql = "insert into {$dbFIX}poll_comment set no = '', poll_no = '$p', member_no = '".$_SESSION['no']."', ".
		"comment = '".htmlspecialchars(trim(stripslashes($content)))."', signdate = '".time()."'";
	@mysql_query($sql);
	$GR->error('작성 완료 되었습니다.', 0, './?p='.$p);
}

// 설문 가져오기
$getPollSubject = mysql_fetch_array(mysql_query("select * from {$dbFIX}poll_subject where no = '$p'"));
$getPollComment = @mysql_query("select * from {$dbFIX}poll_comment where poll_no = '$p'");
$getPollOptions = @mysql_query("select * from {$dbFIX}poll_option where poll_no = '$p' order by no asc");
$getSumVoted = @mysql_fetch_array(mysql_query("select sum(vote) from {$dbFIX}poll_option where poll_no = '$p'"));
$getTotalComment = @mysql_fetch_array(mysql_query("select count(*) from {$dbFIX}poll_comment where poll_no = '$p'"));
if(!$getSumVoted[0]) $getSumVoted[0] = 1;

// 문서설정
$title = 'GR Board Poll Page';
include $preRoute . 'html_head.php';
?>
<body>
<!-- 중앙배열 -->
<div id="installBox">

	<!-- 폭 설정 -->
	<div id="joinBox">

		<!-- 타이틀 -->
		<div class="bigTitle">Poll</div>

		<!-- 정보 입력받기 -->
		<fieldset>
			<legend><img src="../image/icon/poll_icon.gif" alt="" /> <?php echo stripslashes($getPollSubject['subject']); ?> &nbsp;&nbsp;(댓글: <?php echo $getTotalComment[0]; ?>)</legend>

			<!-- 위아래 공백 -->
			<div class="vSpace"></div>
			<ol>
			<?php
			while($options = mysql_fetch_array($getPollOptions)) { 
				$ratio = floor(($options['vote'] / $getSumVoted[0]) * 100);
				$originRatio = $ratio;
				if($ratio < 2) $ratio = 1;
				else $ratio -= 2;
			?>
			<li>
					<div><?php echo stripslashes($options['title']); ?></div>
					<div style="width: <?php echo $ratio; ?>%; height: 20px" class="bar" title="<?php echo $options['vote']; ?> (<?php echo $originRatio; ?>%)"></div>
			</li>
			<?php } ?>
			</ol>
		</fieldset><!--# 정보 입력받기 -->

		<?php if(!$getPollSubject['id']) { ?>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

		<!-- 정보 입력받기 -->
		<form id="addComment" method="post" onsubmit="return valueCheck();" action="<?php echo $_SERVER['PHP_SELF']; ?>?p=<?php echo $p; ?>">
		<fieldset>
			<legend><img src="../image/icon/poll_comments.gif" alt="" /> 기타 의견을 작성해 주세요</legend>
			<div style="padding: 10px; text-align: center">
			<?php if($_SESSION['no']) { ?>
			<input type="text" name="content" class="i" /> <input type="submit" value="확인" class="submit" />
			<?php } else { ?>로그인 후 의견을 작성하실 수 있습니다.<?php } ?>
			</div>
		</fieldset>
		</form>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<?php
		// 댓글 불러오기
		while($co = mysql_fetch_array($getPollComment)) { 
			$name = @mysql_fetch_array(mysql_query("select nickname, nametag from {$dbFIX}member_list where no = '".$co['member_no']."'"));
			if($name['nametag']) $showName = '<img src="../'.$name['nametag'].'" alt="'.$name['nickname'].'" />';
			else $showName = $name['nickname'];
		?>
		<div class="tableListLine">
			<div class="tableLeft" title="<?php echo date('Y.m.d H:i:s', $co['signdate']); ?>"><?php echo stripslashes($showName); ?></div>
			<div class="tableRight"><?php echo stripslashes($co['comment']); ?> 
			<?php if($_SESSION['no'] == 1) { ?><a href="#" onclick="deleteComment(<?php echo $co['no']; ?>);"><img src="../image/icon/poll_co_del.gif" alt="삭제" /></a><?php } ?></div>
			<div class="clear"></div>
		</div>
		<?php } ?>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

		<!-- 이전 설문 조사 가져오기 -->
		<div style="text-align: center"><select name="oldPoll" onchange="location.href='./?p='+this.value;">
		<?php
		$getOldPoll = @mysql_query("select * from {$dbFIX}poll_subject order by no desc");
		while($old = mysql_fetch_array($getOldPoll)) { ?>
		<option value="<?php echo $old['no']; ?>"<?php echo ($old['no'] == $p)?' selected="selected"':''; ?>><?php echo stripslashes($old['subject']).' ('.date('Y.m.d', $old['signdate']).')'; ?></option>
		<?php } ?>
		</select></div>

		<?php } ?>

	</div><!--# 폭 설정 -->

</div><!--# 중앙배열 -->

<script type="text/javascript">//<![CDATA[
function valueCheck()
{
	t = document.forms['addComment'];
	if(!t.elements['content'].value) {
		alert('기타 의견을 입력해 주세요. 250자 이내로 작성해 주시면 됩니다.');
		t.elements['content'].focus();
		return false;
	}
	return true;
}
function deleteComment(no)
{
	if(confirm('이 댓글을 삭제하시겠습니까?')) {
		location.href='./?p=<?php echo $p; ?>&deleteComment='+no;
	}
}
//]]></script>
</body>
</html>
