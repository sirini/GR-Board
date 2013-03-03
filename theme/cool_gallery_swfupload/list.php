<?php
if(!defined('__GRBOARD__')) exit();
if($articleNo) echo '<div style="height: 30px"></div>';
include_once 'genxPhpThumb/ThumbLib.inc.php';
include_once $theme.'/lib/list_lib.php';
?>

<form id="list" method="post" action="<?php echo $grboard; ?>/list_adjust.php">
<div>
	<input type="hidden" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="articleNo" value="<?php echo $articleNo; ?>" />
</div>

<table id="grListTable" rules="none" summary="GR Board Article List" cellpadding="0" cellspacing="0" border="0">
<?php if($setting['show_header']): ?>
<thead> 
<tr>
	<th class="titleBar" colspan="<?php echo $setting['column_count']; ?>">

	<?php if($isCategory): ?>
			<select name="chooseCategory" onchange="setCategory(this.value)">
				<option value="">선택하세요</option>
				<?php showCategoryComboBox(); ?>
			</select>
	<?php endif; ?>

	정렬: 
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=subject&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">&middot; 제목 </a>
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=name&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">&middot; 이름 </a>
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=signdate&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">&middot; 날짜 </a>
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;sortList=hit&amp;sortBy=<?php echo ($sortBy=='desc')?'asc':'desc'; ?>&amp;page=<?php echo $page; ?>">&middot; 보기 </a>

</tr>
</thead>
<?php endif; ?>

<tbody>

<?php while($notice = setNoticeData($GR->fetch($getNotice))): /* 공지글 반복 시작 */ ?>

<tr class="hover">
	<td class="list" colspan="<?php echo $setting['column_count']; ?>">
	<?php if($isCategory): echo $notice['category'] . ' | '; endif; ?>
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $notice['no']; ?>&amp;page=<?php echo $page; ?>"><strong><?php echo $notice['subject']; ?></strong></a>
	</td>
</tr>

<?php endwhile; /* 공지글 반복 종료 */ ?>


<?php $loop=0; while($list = setArticleData($GR->fetch($getList), $setting['thumb_width_size'])): /* 일반글 반복 시작 */ ?>

<?php $loop++; if($loop == 1): ?><tr><?php endif; ?>
	
	<td>
		<p class="thumbImage">
			<a href="<?php echo $list['link']; ?>&amp;page=<?php echo $page; ?>">
				<img src="<?php echo $list['thumb']; ?>" alt="미리보기" style="width: <?php echo $setting['thumb_width_size']; ?>px" />
			</a>
		</p>
		<p class="thumbInfo"><?php if($isAdmin): ?><input type="checkbox" name="box[]" value="<?php echo $list['no']; ?>" /><?php endif; ?>			
		<a href="<?php echo $list['link']; ?>&amp;page=<?php echo $page; ?>"><?php echo $list['subject']; ?></a></p>
		<p class="thumbInfo">	
			<span onclick="getMember(<?php echo (($list['member_key'])?$list['member_key']:0).','.(($_SESSION['no'])?$_SESSION['no']:0); ?>, event);">
			<?php echo $list['name']; ?>
			</span>
		</p>
		<p class="thumbInfo">
			<span class="comment">댓글(<?php echo $list['comment_count']; ?>)</span>
			<span class="comment">, 추천(<?php echo $list['good']; ?>)</span>
		</p>
	</td>
	
<?php if($loop % $setting['column_count'] == 0): ?></tr><tr><?php endif; ?>

<?php endwhile; /* 일반글 반복 종료 */ ?>
	
<?php 
$remainLoop = $setting['column_count'] - ($loop % $setting['column_count']);
for($p=0; $p<$remainLoop; $p++): ?>
	<td></td>		
<?php endfor;
if($remainLoop) echo '</tr>';	
?>
</tbody>
</table>
</form>