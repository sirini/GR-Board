		<form name="copyArticle" onsubmit="return copyOk();" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php if(!$_SESSION['no']) die('Permission Denied.'); ?>
		
		<input type="hidden" name="exec" value="copy" />
		<input type="hidden" name="copyAction" value="1" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="selectArticle" value="<?php echo $selectArticle; ?>" />
		<div class="mvBack">
			<div class="mv">복사할 게시판 선택</div>
			<div style="text-align: center; padding: 10px">
				<select name="copyTarget">
				<option value="">복사할 게시판을 선택하세요</option>
				<?php
				// 게시판 목록 받아오기
				include "db_info.php"; //GPC안 쓰므로 예외적으로 db_info.php 직접 호출 허용
				$getTableList = $GR->query("show table status from {$dbName} like '{$dbFIX}bbs%'");
				while($tables = $GR->fetch($getTableList)) { ?>
					<option value="<?php echo $tables['Name']; ?>"><?php echo str_replace($dbFIX.'bbs_', "", $tables['Name']); ?></option>
					<?php
				} # while
				?>
				</select>
				<input type="submit" value="복사 시작" class="submit" /> <input type="button" value="뒤로 가기" class="submit" onclick="location.href='<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>';" />
			</div>
		</div>
		</form>

		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>