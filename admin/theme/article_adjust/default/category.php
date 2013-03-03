<?php if(!$_SESSION['no']) die('Permission Denied.'); ?>
		
		<form name="categoryArticle" onsubmit="return categoryOk();" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="exec" value="category" />
		<input type="hidden" name="categoryAction" value="1" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="selectArticle" value="<?php echo $selectArticle; ?>" />
		<div class="mvBack">
			<div class="mv">카테고리 선택</div>
			<div style="text-align: center; padding: 10px">
				<select name="categoryTarget">
				<option value="">변경할 카테고리명을 선택하세요</option>
				<?php
				// 게시판 카테고리 목록 받아오기
				include "db_info.php"; //GPC안 쓰므로 예외적으로 db_info.php 직접 호출 허용
				// 셀렉트박스형 카테고리 선택
				$categories = $GR->getArray('select category from '.$dbFIX.'board_list where id = \''.$id.'\'');
				$categoryArray = @explode('|', $categories['category']);
				$countCategory = @count($categoryArray);
				for($ca=0; $ca<$countCategory; $ca++) { ?>
					<option value="<?php echo strip_tags($categoryArray[$ca]); ?>"><?php echo strip_tags($categoryArray[$ca]); ?></option>
				<?php } # for ?> 
				<option value="category_delete">카테고리 제거</option>
				</select>
				<input type="submit" value="변경 시작" class="submit" /> <input type="button" value="뒤로 가기" class="submit" onclick="location.href='<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>';" />
			</div>
		</div>
		</form>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>