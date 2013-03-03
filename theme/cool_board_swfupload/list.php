<?php
if(!defined('__GRBOARD__')) exit();
if($articleNo) echo '<div style="height: 30px"></div>'; 
include_once $theme.'/lib/list_lib.php';
?>

<form id="list" method="post" action="<?php echo $grboard; ?>/list_adjust.php">
<div>
	<input type="hidden" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="articleNo" value="<?php echo $articleNo; ?>" />
</div>

<table id="grListTable" rules="none" summary="GR Board Article List" cellpadding="0" cellspacing="0" border="0">
<caption></caption>
<colgroup>
	<col style="width: 40px" />
	<?php if($isAdmin): ?><col style="width: 40px" /><?php endif; ?>
	<?php if($isCategory): ?><col style="width: 100px" /><?php endif; ?>
	<col />
	<col style="width: 100px" />
	<col style="width: 50px" />
	<col style="width: 40px" />
</colgroup>

<thead> 
<tr>
	<th class="titleBar">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=no&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">번호</a>
	</th>
	
	<?php if($isAdmin): ?><th class="titleBar"><a href="#" onclick="selectAll();" class="listTopBox">담기</a></th><?php endif; ?>
	
	<?php if($isCategory): ?>
		<th class="titleBar">
			<select name="chooseCategory" onchange="setCategory(this.value)">
				<option value="">선택하세요</option>
				<?php showCategoryComboBox(); ?>
			</select>
		</th>
	<?php endif; ?>

	<th class="titleBar">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=subject&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">제목</a>
	</th>	
	
	<th class="titleBar">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=name&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">이름</a>
	</th>
	
	<th class="titleBar">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=signdate&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">날짜</a>
	</th>
	
	<th class="titleBar">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=hit&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">보기</a>
	</th>

</tr>
</thead>

<tbody>

<?php while($notice = setNoticeData($GR->fetch($getNotice))): /* 공지글 반복 시작 */ ?>

<tr class="hover">
	<td class="no"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_notice.gif" alt="Notice" /></td>
	<?php if($isAdmin): ?><td class="name"><input type="checkbox" name="box[]" value="<?php echo $notice['no']; ?>" /></td><?php endif; ?>
	<?php if($isCategory): ?><td class="category"><?php echo $notice['category']; ?></td><?php endif; ?>
	<td class="list" colspan="4">
		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $notice['no']; ?>&amp;page=<?php echo $page; ?>"><strong><?php echo $notice['subject']; ?></strong></a>
	</td>
</tr>

<?php endwhile; /* 공지글 반복 종료 */ ?>


<?php while($list = setArticleData($GR->fetch($getList))): /* 일반글 반복 시작 */ ?>

<tr class="hover">
	<td class="no"><?php echo $number; ?></td>
	
	<?php if($isAdmin): ?><td class="no"><input type="checkbox" name="box[]" value="<?php echo $list['no']; ?>" /></td><?php endif; ?>
	
	<?php if($isCategory): ?>
		<td class="category">
			<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;clickCategory=<?php echo urlencode($list['category']); ?>&amp;page=<?php echo $page; ?>"><?php echo $list['category']; ?></a>
		</td>
	<?php endif; ?>
	
	<td class="list">
		<?php echo $list['icon']; ?>
		<a href="<?php echo $list['link']; ?>&amp;page=<?php echo $page; ?>"><?php echo $list['subject']; ?></a>

		<?php if($list['comment_count']): ?>&nbsp; <span class="comment<?php echo $list['strongComment'];?>">(<?php echo $list['comment_count']; ?>)</span><?php endif; ?>
		<?php if($list['hit'] > 300): ?> <img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_hot_article.gif" alt="" /><?php endif; ?>
	</td>

	<td class="name">
		<span onclick="getMember(<?php echo (($list['member_key'])?$list['member_key']:0).','.(($_SESSION['no'])?$_SESSION['no']:0); ?>, event);">
			<?php echo $list['name']; ?>
		</span>
	</td>
	
	<td class="date"><?php echo date('m.d', $list['signdate']); ?></td>
	
	<td class="no"><?php echo $list['hit']; ?></td>
</tr>

<?php $number--; endwhile; /* 일반글 반복 종료 */ ?>

</tbody>
</table>
</form>