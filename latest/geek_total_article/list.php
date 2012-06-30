<div class="geekTotalParentBox">
	<?php
	// 게시물 루프
	while($latest = mysql_fetch_array($getData))
	{
		$title = htmlspecialchars(cutString(stripslashes($latest['subject']), $cutSize));
		$bbsName = get_bbs_name($latest['id']);
		?>
	<div class="geekTotalSubject">
	<span class="date"><?php echo date('m.d', $latest['signdate']); ?> |</span> &nbsp;
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $latest['id']; ?>&amp;articleNo=<?php echo $latest['article_num']; ?>">
	<?php if($latest['is_secret']) { ?><img src="<?php echo $path; ?>/secret.gif" alt="비밀글" style="vertical-align: middle" /> <?php } ?>
	<?php echo $title; ?></a>
	</div>
		<?php
	} # while
	?>
	
</div>