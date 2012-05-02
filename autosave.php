<?php
// 기본 클래스를 부른다
include 'class/common.php';
$GR = new COMMON;

// 문서설정
$title = 'GR Board Join Page';
include 'html_head.php';

// 회원/비회원 구분
if($_SESSION['no']) {
	$msg = '마지막으로 임시 저장된 내용 보기';
	$GR->dbConn();
	$getLastData = $GR->getArray('select * from '.$dbFIX.'auto_save where member_key = '.$_SESSION['no']);
	$time = $getLastData['signdate'];
	$subject = $getLastData['subject'];
	$content = $getLastData['content'];
} else {
	$msg = '사용중이신 브라우저에서 1시간 이내에 자동 저장된 최근글';
	$time = $_COOKIE['grDate'];
	$subject = $_COOKIE['grSubject'];
	$content = $_COOKIE['grContent'];
}
?>
<body>
<!-- 중앙배열 -->
<div id="installBox">

	<!-- 폭 설정 -->
	<div id="joinBox">

		<!-- 타이틀 -->
		<div class="bigTitle">Auto save</div>

		<!-- 내용 보기 -->
		<fieldset>
			<legend>
			<?php echo $msg; ?>
			</legend>

			<div class="vSpace"></div>

			<div class="tableListLine">
				<div class="tableLeft">작성시각</div>
				<div class="tableRight"><?php echo date('Y년 n월 j일 H시 i분 s초', $time); ?></div>
				<div class="clear"></div>
			</div>
			<div class="tableListLine">
				<div class="tableLeft">제목</div>
				<div class="tableRight"><?php echo $subject; ?></div>
				<div class="clear"></div>
			</div>
			<div class="tableListLine">
				<div class="tableLeft">내용</div>
				<div class="tableRight"><?php echo $content; ?></div>
				<div class="clear"></div>
			</div>
		</fieldset><!--# 내용 보기 -->

		<div class="vSpace"></div>

		<div id="autosaveHelp">※ 로그인한 멤버의 경우, 글 작성시 매 30초마다 자동 저장되는 최근 글 중 마지막으로 저장된 글을 가져옵니다.
		비회원의 경우 브라우저에 1시간 이내로 저장된 최근 글을 가져옵니다. (쿠키를 지우셨을 경우, 삭제됩니다.)</div>

	</div><!--# 폭 설정 -->

</div><!--# 중앙배열 -->

</body>
</html>
