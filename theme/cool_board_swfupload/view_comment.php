<?php while($comment = setViewData($GR->fetch($getComment))): ?>

<!-- 댓글 출력하기 -->
<table rules="none" summary="GR Board View Comment" cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<caption></caption>
<tbody>
<tr>
	
	<?php if($comment['thread']): ?>
		<td><?php for($tc=0; $tc<$comment['thread']; $tc++) echo '&nbsp;&nbsp;&nbsp;&nbsp;'; ?></td>
	<?php endif; ?>
	
	<td class="commentRight">
	
		<div id="read<?php echo $comment['no']; ?>" class="commentTitle">
			<span class="name" onclick="getMember(<?php echo (($comment['member_key'])?$comment['member_key']:0).','.(($_SESSION['no'])?$_SESSION['no']:0); ?>, event);">
				<?php echo $comment['name']; ?>
			</span> 
			<?php echo $comment['homepage']; ?>
			(<?php echo $comment['signdate']; ?>
			<?php if($isAdmin or $isMaster) echo '/ '.$comment['ip']; ?>) 
			<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $articleNo; ?>&amp;replyTarget=<?php echo $comment['no']; ?>&amp;commentPage=<?php echo $_GET['commentPage']; ?>&amp;page=<?php echo $page; ?>#read<?php echo $comment['no']; ?>" onclick="setPos(event);">
				<img src="<?php echo $grboard.'/'.$theme; ?>/image/comment_reply.gif" alt="답변" title="이 코멘트에 답변을 답니다" />
			</a> 
			<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $articleNo; ?>&amp;modifyTarget=<?php echo $comment['no']; ?>&amp;commentPage=<?php echo $_GET['commentPage']; ?>&amp;page=<?php echo $page; ?>#read<?php echo $comment['no']; ?>" onclick="setPos(event);">
				<img src="<?php echo $grboard.'/'.$theme; ?>/image/comment_modify.gif" alt="수정" title="이 코멘트를 수정합니다" />
			</a> 
			<a href="#" onclick="commentDeleteOk(<?php echo "'".$id."', ".$articleNo.", ".$comment['no'].", ".$page; ?>);">
				<img src="<?php echo $grboard.'/'.$theme; ?>/image/comment_delete.gif" alt="삭제" title="이 코멘트를 삭제합니다" />
			</a>
		</div>
		
		<div class="commentContent"><?php echo $comment['content']; ?></div>
		
		<div style="text-align:right;">
						
			<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $articleNo; ?>&amp;voteCommentNo=<?php echo $comment['no']; ?>&amp;good=1#read<?php echo $comment['no']; ?>" style="color:#386D9F;" title="이 코멘트가 좋습니다.">좋아요 (<?php echo $comment['good']; ?>)</a>
			
			<?php if($isAdmin && ($comment['bad'] > -1000)): ?>
				<a href="#" onclick="blindArticleOk('<?php echo $id.'\', '.$articleNo; ?>, 'comment_', '<?php echo $comment['no']; ?>');" style="color: #f3828a" title="이 댓글을 블라인드 처리 합니다. (삭제하지는 않고, 댓글 내용만 확인이 안됩니다.)">+ Blind ON</a>
			<?php endif; ?>
			
			<?php if($isAdmin && ($comment['bad'] < -1000)): ?>
				<a href="#" onclick="blindArticleNo('<?php echo $id.'\', '.$articleNo; ?>, 'comment_', '<?php echo $comment['no']; ?>');" style="color: #7390d4" title="이 댓글의 블라인드 처리를 해제합니다. (가려졌던 댓글 내용이 다시 보입니다.)">+ Blind OFF</a>
			<?php endif; ?>
		</div>
		
	</td>
</tr>
</tbody>
</table>

<?php endwhile; ?>
