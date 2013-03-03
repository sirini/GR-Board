<div class="geekParentBox">
	<h3><a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>"><?php echo $latestTitle; ?></a></h3>
	<div class="geekChildBox">
	<?php
	// 게시물 루프
	while($latest = $GR->fetch($getData))
	{
		$title = cutString(stripslashes($latest['subject']), $cutSize);
		?>
	<div class="latestSubject"><span class="latestName"><?php echo $latest['name']; ?> |</span>&nbsp;
	<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $latest['no']; ?>"><?php echo $title; ?></a>	
	<?php if($latest['comment_count']) { ?><span class="latestCommentNum"><?php echo $latest['comment_count']; ?></span><?php } ?>
	</div>
		<?php
	} # while
	?>
	</div>
</div>