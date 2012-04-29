<?php
// 기본 클래스를 부른다 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 관리자가 아니면 볼 수 없다. @sirini
if($_SESSION['no'] != 1) $GR->error('관리자만이 추적 가능 합니다.', 1);

// 필요한 변수 처리 @sirini
if($_GET['user']) $user = (int)$_GET['user']; else exit();

// 문서설정 @sirini
$title = 'GR Board Member Article Trace Page';
include 'html_head.php';
$getName = $GR->getArray('select nickname from '.$dbFIX.'member_list where no = '.$user);
?>
<body>
<!-- 중앙배열 -->
<div id="installBox">

	<!-- 폭 설정 -->
	<div class="sizeFix">

		<!-- 타이틀 -->
		<div class="bigTitle">Article trace</div>

		<!-- 게시물관리 보기 박스 -->
		<div id="admMenuTable">
			<div style="padding: 10px">도움말</div>
			<div class="comment">모든 게시판을 대상으로 "<strong><?php echo $getName[0]; ?></strong>" 이(가) 작성한 게시물을 추적 합니다.
			각각의 게시판별로 작성한 글 개수와, 최근 작성한 글 20개를 바로 보여주어 최근 글 작성
			현황을 파악하실 수 있습니다.</div>
		</div><!--# 게시물관리 보기 박스 -->

		<!-- 우측 몸통 부분 -->
		<div id="admBody">

		<!-- 게시물관리 보기 박스 -->
		<div class="mvBack" id="admAdjustMenu">
			<div class="mv">"<?php echo $getName[0]; ?>" 이(가) 작성한 게시물 조회</div>
			<div style="padding: 15px">
			<?php
			// 게시판 목록 가져와서 순회 @sirini
			$getBoards = $GR->query('select id from '.$dbFIX.'board_list');
			while($bbs = $GR->fetch($getBoards))
			{
				// 해당 게시판에서 작성한 글 개수 가져오기 @sirini
				$getArticleNum = $GR->getArray('select count(*) from '.$dbFIX.'bbs_'.$bbs['id'].' where member_key = '.$user);

				// 게시판 ID 와 작성한 글 수 출력 @sirini
				echo '<h2>'.$bbs['id'].' 게시판: 총 '.$getArticleNum[0].' 개의 글 작성</h2><ul>';

				// 각 게시판별로 회원이 작성한 글 조회 (최근 10개만) @sirini
				$getArticles = $GR->query("select no, subject, signdate from {$dbFIX}bbs_".$bbs['id']." where member_key = '$user' order by no desc limit 20");
				while($article = $GR->fetch($getArticles))
				{
					echo '<li><a href="board.php?id='.$bbs['id'].'&amp;articleNo='.$article['no'].'">'.strip_tags($article['subject']).'</a> ('.date('Y. m. d  H:i', $article['signdate']).')</li>';
				}
				echo '</ul>';
			}
			?>
			</div>
		</div><!--# 게시물관리 보기 박스 -->

		</div><!--# 우측 몸통 부분 -->
		<div class="clear"></div>
	</div><!--# 폭 설정 -->

</div><!--# 중앙배열 -->

</body>
</html>
