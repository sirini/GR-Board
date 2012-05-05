<?php
// 기본 클래스 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 관리자인지 확인한다. @sirini
if($_SESSION['no'] != 1) $GR->error('관리자 화면은 관리자만 접근할 수 있습니다.', 1, 'CLOSE');

// 설문 항목 삭제 @sirini
if($_GET['deleteOption']) {
	$GR->query("delete from {$dbFIX}poll_option where no = '".$_GET['deleteOption']."'");
	$GR->move('admin_poll.php?pollNum='.$_GET['go']);
}

// 설문 삭제하기 @sirini
if($_GET['deletePoll']) {
	$GR->query("delete from {$dbFIX}poll_subject where no = '".$_GET['deletePoll']."'");
	$GR->query("delete from {$dbFIX}poll_option where poll_no = '".$_GET['deletePoll']."'");
	$GR->query("delete from {$dbFIX}poll_comment where poll_no = '".$_GET['deletePoll']."'");
	$GR->error('설문을 삭제하였습니다.', 0, 'admin_poll.php');
}

// 설문 추가하기 @sirini
if($_POST['pollSubject']) {
	@extract($_POST);
	//$pollSubject = m.ysql_real_escape_string($pollSubject);
	$countPollOption = @count($pollOption);
	if($modifyPoll) {
		$GR->query("update {$dbFIX}poll_subject set subject = '$pollSubject' where no = '$modifyPoll'");
		for($i=0; $i<$countPollOption; $i++) {
			if($pollOption[$i]) {
				//$pollOption[$i] = m.ysql_real_escape_string($pollOption[$i]);
				if($modifyOption[$i]) $GR->query("update {$dbFIX}poll_option set title = '".$pollOption[$i]."' where no = '".$modifyOption[$i]."'");
				else $GR->query("insert into {$dbFIX}poll_option set no = '', poll_no = '$modifyPoll', title = '".$pollOption[$i]."', vote = '0', id = ''");
			}
		}
		$GR->error('설문을 수정하였습니다.', 0, 'admin_poll.php?pollNum='.$modifyPoll);
	} else {
		$GR->query("insert into {$dbFIX}poll_subject set no = '', subject = '$pollSubject', signdate = '".time()."', comment_num = '0', id = ''");
		$insertNo = @mysql_insert_id();
		for($i=0; $i<$countPollOption; $i++) {
			if($pollOption[$i]) {
				//$pollOption[$i] = m.ysql_real_escape_string($pollOption[$i]);
				$GR->query("insert {$dbFIX}poll_option set no = '', poll_no = '$insertNo', title = '".$pollOption[$i]."', vote = '0', id = ''");
			}
		}
		$GR->error('설문을 추가하였습니다.', 0, 'admin_poll.php?pollNum='.$insertNo);
	}
}

// 페이지관련 처리 @sirini
$viewRows = 10;
if(!$_GET['page'] || $page < 0) $page = 1; else $page = $_GET['page'];
$fromRecord = ($page - 1) * $viewRows;
$totalCount = $GR->getArray("select count(*) from {$dbFIX}poll_subject");
$totalPage = ceil($totalCount[0] / $viewRows);

// 설문조사 목록/항목 가져오기 @sirini
$resultQue = $GR->query("select * from {$dbFIX}poll_subject order by no desc limit $fromRecord, $viewRows");
if($_GET['pollNum']) {
	$pollNum = $_GET['pollNum'];
	$poll = $GR->getArray("select * from {$dbFIX}poll_subject where no = '$pollNum'");
}

// 문서설정 @sirini
$title = 'GR Board Admin Page ( Poll )';
include 'html_head.php';
include 'admin/admin_left_menu.php';
?>
		<!-- 현재 페이지 도움말 -->
		<div id="grboardHelpBox">
			설문조사를 GR Board 를 통해서 하실 수 있도록 도와주는 페이지 입니다. <a href="#" title="도움말을 더 봅니다" id="helpPollBtn">...도움말보기 <img src="image/admin/btn_help_more.gif" alt="더 보기" /></a>
			<div id="helpBox" style="display: none">
			GR Board 에서는 페이지에 최근 게시물과 비슷한 방식으로 설문조사를 넣을 수 있도록 지원하고 있습니다.<br />
			아래 "<strong>설문추가하기</strong>" 패널을 주목해 주십시오.<br />
			원하는 주제를 정해 설문주제 항목에 작성하고, 각 항목들을 작성하신 후 "<strong>설문추가</strong>" 를 누르시면 됩니다.<br />
			이미 올려진 설문을 수정하기 위해서는 설문조사에서 해당 설문주제를 클릭하시면 수정하실 수 있습니다.<br />
			페이지에 최근게시물처럼 최근설문조사를 추가하실 경우 아래의 코드를 입력해 주시길 바랍니다.<br />
			<br />
			&lt;?php poll('sirini_poll'); ?&gt;<br />
			위에서 '<strong>sirini_poll</strong>' 은 최근설문조사 테마(스킨) 이름 입니다. /latest/ 폴더 아래에 있습니다.<br />
			위의 코드를 작성하신 페이지 안에 적당한 위치에 삽입하시면 최근에 작성된 설문조사가 올려지게 됩니다.<br />
			최근설문조사 테마의 스타일시트를 해당 페이지에서 부르시고자 할 경우에는 &lt;head&gt; ~ &lt;/head&gt; 사이에 아래의 코드를<br />
			입력하시면 됩니다. (이는 최근게시물에서도 동일한 방식으로 적용 가능 합니다.)<br />
			<br />
			&lt;style type="text/css"&gt;<br />
			//&lt;![CDATA[<br />
			@import url(&lt;?php echo $grboard; ?&gt;/latest/sirini_poll/style.css);<br />
			//]]&gt;<br />
			&lt;/style&gt;<br />
			위에서 @import url(); 부분이 외부의 스타일시트(CSS)를 불러온다는 구문입니다.<br />
			$grboard 는 최근게시물이나 최근설문조사를 페이지에서 사용하고자 할 때 제일 먼저 선언되어야 할 부분입니다.
			</div>
		</div><!--# 현재 페이지 도움말 -->

		<!-- 설문조사 목록 -->
		<div class="mvBack" id="admPoll">
			<div class="mv">설문조사</div>

			<table rules="none" summary="GR Board Group List" cellpadding="0" cellspacing="0" border="0" style="width: 100%">
			<caption></caption>
			<colgroup>
			<col />
			<col style="width: 80px" />
			<col style="width: 80px" />
			<col style="width: 80px" />
			<col style="width: 70px" />
			</colgroup>
			<thead>
			<tr>
				<th class="titleBar">설문주제</th>
				<th class="titleBar">총투표수</th>
				<th class="titleBar">총댓글수</th>
				<th class="titleBar">실시일</th>
				<th class="titleBar">삭제</th>
			</tr>
			</thead>
			<tbody>
			<?php
			while($data = $GR->fetch($resultQue)) { 
				$getTotalVote = $GR->getArray("select sum(vote) from {$dbFIX}poll_option where poll_no = '".$data['no']."'");
				$getTotalComment = $GR->getArray("select count(*) from {$dbFIX}poll_comment where poll_no = '".$data['no']."'");
			?>
			<tr>
				<td>&nbsp;&nbsp; <?php if($data['id']) echo '<a href="board.php?id='.$data['id'].'" title="이 게시판에서 설문이 등록되었습니다.">['.$data['id'].']</a> '; ?>
				<a href="admin_poll.php?pollNum=<?php echo $data['no']; ?>" title="이 설문을 수정합니다."><?php echo strip_tags($data['subject']); ?></a></td>
				<td class="boardList"><?php echo $getTotalVote[0]; ?></td>
				<td class="boardList"><?php echo $getTotalComment[0]; ?></td>
				<td class="boardList"><?php echo date('Y.m.d', $data['signdate']); ?></td>
				<td class="boardList"><a href="#" onclick="deletePoll(<?php echo $data['no']; ?>);"><img src="image/admin/admin_delete_poll.gif" alt="설문삭제" /></a></td>
			</tr>
			<?php } 
			$printPage = $GR->getPaging($viewRows, $page, $totalPage, 'admin_poll.php?page=');
			if($printPage) {
			?>
			<tr>
				<td colspan="5" class="paging"><?php echo $printPage; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			</table>

		</div><!--# 설문조사 목록 -->

		<div class="vSpace"></div>

		<!-- 설문추가하기 -->
		<form id="pollAdd" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<div><input type="hidden" name="modifyPoll" value="<?php echo $poll['no']; ?>" /></div>
		<div class="mvBack" id="admAddPoll">
			<div class="mv">설문<?php echo ($poll['no'])?'수정':'추가'; ?>하기</div>

			<div class="tableListLine">
				<div class="tableLeft" title="설문주제를 입력합니다.">설문주제</div>
				<div class="tableRight">
					<input type="text" name="pollSubject" class="input" style="width: 350px" value="<?php echo $poll['subject']; ?>" />
					<?php if($poll['no']) { ?><a href="poll/?p=<?php echo $poll['no']; ?>" onclick="window.open(this.href, 'poll', 'width=550,height=600,menubar=no,scrollbars=yes'); return false"><img src="image/admin/admin_poll_preview.gif" alt="설문보기" /></a><?php } ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="설문항목을 추가합니다.">설문항목
				<a href="#" onclick="addOption();"><img src="image/admin/admin_poll_add.gif" alt="항목늘리기: 공백으로 두신 부분은 자동 제외되어 반영 됩니다." /></a></div>
				<div id="inputFields" class="tableRight">
				<?php
				if($poll['no']) {
					$getPollOption = $GR->query("select * from {$dbFIX}poll_option where poll_no = '".$poll['no']."'");
					$o = 0;
					while($pollOptions = $GR->fetch($getPollOption)) { 
						if($poll['no']) { ?><input type="hidden" name="modifyOption[]" value="<?php echo $pollOptions['no']; ?>" /><?php } ?>
						항목 #<?php echo $o+1; ?>: <input type="text" name="pollOption[]" value="<?php echo $pollOptions['title']; ?>" title="필요 없으신 항목은 비워 두시면 됩니다." /> <a href="#" onclick="deleteOption(<?php echo $pollOptions['no'].', '.$poll['no']; ?>);"><img src="image/admin/admin_poll_sub.gif" alt="항목 삭제" /></a><br />
				<?php $o++;	} $maxInput = $o;
				} else {
					$pollOptionNum = $GR->getArray("select count(*) from {$dbFIX}poll_option where poll_no = '".$poll['no']."'");
					if(!$pollOptionNum[0]) $maxInput = 5; else $maxInput = $pollOptionNum[0];
					for($o=0; $o<$maxInput; $o++) { ?>
						항목 #<?php echo $o+1; ?>: <input type="text" name="pollOption[]" value="" title="필요 없으신 항목은 비워 두시면 됩니다." /><br />
					<?php
					}
				}
				?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="submitBox">
					<input type="submit" value="설문<?php echo ($poll['no'])?'수정':'추가'; ?>" />
				</div>
			</div>
		</div><!--# 설문추가하기 -->
		</form>
		
		</div><!--# 우측 몸통 부분 -->

		<div class="clear"></div>

	</div><!--# 폭 설정 -->	

</div><!--# 가운데 정렬 -->

<script src="js/jquery.js"></script>
<script type="text/javascript">
//<![CDATA[
var TOTAL_INPUT = <?php echo $maxInput; ?>;
//]]>
</script>
<script src="admin/admin_poll.js"></script>

</body>
</html>
