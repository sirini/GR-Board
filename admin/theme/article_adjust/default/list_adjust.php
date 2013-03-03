<?php if(!$_SESSION['no']) die('Permission Denied.'); ?>

<!doctype html>
<html>
<head>
<link rel="stylesheet" href="<?php echo $theme; ?>/style.css" type="text/css" title="style" />
<meta charset="utf-8" />
<title>GR Board Article Adjust Page</title>
<script src="<?php echo $theme; ?>/list_adjust.js"></script>
</head>
<body>
<!-- 중앙배열 -->
<div id="installBox">

	<!-- 폭 설정 -->
	<div class="sizeFix">

		<!-- 타이틀 -->
		<div class="bigTitle">Article adjust</div>

		<!-- 게시물관리 보기 박스 -->
		<div id="admMenuTable">
			<div class="mv">선택한 게시물 관리</div>
			<form id="adjustMenu" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<input type="hidden" name="id" value="<?php echo $id;?>" />
				<input type="hidden" name="selectArticle" value="<?php echo $selectArticle;?>" />
				<input type="hidden" name="exec" value="" />
				<div class="menu"><a href="javascript:execAdjust('delete');" onfocus="this.blur()" title="선택한 게시물들을 삭제합니다"><img src="image/admin/arrow.gif" alt="" /> 삭제</a></div>
				<div class="menu"><a href="javascript:execAdjust('move');" title="선택한 게시물들을 이동합니다"><img src="image/admin/arrow.gif" alt="" /> 이동</a></div>
				<div class="menu"><a href="javascript:execAdjust('copy');" title="선택한 게시물들을 복사합니다"><img src="image/admin/arrow.gif" alt="" /> 복사</a></div>
				<div class="menu"><a href="javascript:execAdjust('category');" title="선택한 게시물들의 카테고리를 변경합니다."><img src="image/admin/arrow.gif" alt="" /> 카테고리 변경</a></div>
			</form>
		</div><!--# 게시물관리 보기 박스 -->

		<!-- 우측 몸통 부분 -->
		<div id="admBody">

		<div class="mvBack" id="admSelectedList">
			<div class="mv">선택된 게시물 목록</div>
			<ul>
			<?php
				for($g=0; $g<$countTmpArr; $g++){
					$articleNo = $tempArray[$g];
					$articleSubject = $GR->getArray("select subject from {$dbFIX}bbs_{$id} where no = '$articleNo'");
					echo '<li>'.strip_tags($articleSubject[0]).'</li>';
				}
			?>
			</ul>
		</div>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

		<?php if(!isset($exec)){?><a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>
		<?php
		}

		// 게시물들 이동
		if($exec == 'move') include 'move.php';
		
		// 게시물 복사
		if($exec == 'copy') include 'copy.php';

		// 카테고리 변경
		if($exec == 'category') include 'category.php';
		?>
		</div>
		<!--# 우측 몸통 부분 -->

		<div class="clear"></div>

	</div><!--# 폭 설정 -->

</div><!--# 중앙배열 -->

</body>
</html>
