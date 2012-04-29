<?php
/***************
 * 기본 쪽지함 테마
 ***************
- 이 파일은 grboard/view_memo.php 위치에서 불려집니다.
- 스타일시트는 이 파일과 동일한 위치의 style.css 파일 내용이 반영됩니다.
 */
if(!defined('__GRBOARD__')) exit();

$viewList = 10; # 한 화면에 (기본) 10 개씩 보기
?>

<body>
<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->
<!-- MemberInfo -->
<div id="memobox">
	<div class="header">
		<h1>Memo box - 쪽지보관함</h1>
	</div>
	<div class="contents">
  <noscript>
	  <div id="noscript">
	    <p>Javascript를 지원하지 않는 브라우저 입니다.</p>
	    <p>회원정보 수정과 탈퇴가 불가능할 수 있습니다.</p>
	  </div>
	</noscript>
<?php
  // 선택한 게시물이 있을 경우 내용보기
	if($action && $viewNo) 
	{
		$getMemo = @mysql_query('select * from '.$dbFIX.'memo_save where (member_key = '.$sessionNo.' or sender_key = '.$sessionNo.') and no = '.$viewNo) or 
		  $GR->error('선택한 쪽지 내용을 가져오지 못했습니다.', 0, 'view_memo.php');
		$view = @mysql_fetch_array($getMemo);
		$senderKey = $view['sender_key'];
		$sender = @mysql_fetch_array(mysql_query("select realname, nickname from {$dbFIX}member_list where no = '$senderKey'"));
		if($action != 2 && !$view['is_view']) @mysql_query("update {$dbFIX}memo_save set is_view = '1' where no = '$viewNo'");
		?>
		<div class="common">
		  <h2 class="nametac4">쪽지 읽기</h2>
		  <ul class="memo_menu">
		    <li><a href="view_memo.php?action=1" <?php echo ($action==1)?' style="font-weight: bold"':''; ?> title="받은 쪽지">받은 쪽지</a></li>
		    <li><a href="view_memo.php?action=2" <?php echo ($action==2)?' style="font-weight: bold"':''; ?> title="보낸 쪽지">보낸 쪽지</a></li>
		    <li><a href="view_memo.php?action=3" <?php echo ($action==3)?' style="font-weight: bold"':''; ?> title="안 읽은 쪽지">안 읽은 쪽지</a></li>
		  </ul>
		  <table class="memotable_read" width="100%" cellspacing="0" border="0" summary="쪽지내용">
		    <caption>쪽지읽기</caption>
		    <colgroup>
		      <col width="130px" />
		      <col />
		      <col width="130px" />
		      <col />
		    </colgroup>
		    <thead>
		      <tr class="subject">
		        <th>제목</th>
		        <td colspan="3"><?php echo strip_tags($view['subject']); ?></td>
		      </tr>
		      <tr class="sender_info">
          	<th class="sender">보낸사람</td>
          	<td><?php echo ($sender['realname'])?$sender['nickname'].' ('.$sender['realname'].')':'(탈퇴한 회원)'; ?></td>
          	<th class="sendtime">보낸시간</td>
          	<td><?php echo date("Y.m.d h:m", $view['signdate']); ?></td>
          </tr>
		    </thead>
		    <tbody>
		      <tr>
		        <th>내용</th>
		        <td class="content" colspan="3">
		          <?php echo nl2br($view['content']); ?>
		        </td>
		      <tr>
		    </tbody>
		  </table>
		  <ul class="memoread">
		    <li><a href="view_memo.php?action=<?php echo $action; ?>" title="쪽지함 목록보기로 돌아갑니다">목록보기</a></li>
		    <li><a href="send_memo.php?target=<?php echo $view['sender_key']; ?>" title="답장을 씁니다">답장쓰기</a></li>
		    <li><a href="#" onclick="deleteMemo(<?php echo $viewNo; ?>);" title="현재 보고 계신 쪽지를 삭제합니다.">쪽지삭제</a></li>
		  </ul>
		</div>
<?php }
  // 본인이 보낸 쪽지 목록들 보기
	elseif($action == 2) { ?>
	  <div class="common">
	    <h2 class="nametac2">보낸 쪽지</h2>
	    <ul class="memo_menu">
		    <li><a href="view_memo.php?action=1" <?php echo ($action==1)?' style="font-weight: bold"':''; ?> title="받은 쪽지">받은 쪽지</a></li>
		    <li><a href="view_memo.php?action=2" <?php echo ($action==2)?' style="font-weight: bold"':''; ?> title="보낸 쪽지">보낸 쪽지</a></li>
		    <li><a href="view_memo.php?action=3" <?php echo ($action==3)?' style="font-weight: bold"':''; ?> title="안 읽은 쪽지">안 읽은 쪽지</a></li>
		  </ul>
	    <table class="memotable" width="100%" cellspacing="0" border="0" summary="보낸쪽지함">
        <thead>
	        <tr class="titlebar">
	          <th class="checkbox"><a href="#" onclick="selectAll();" title="전체선택"><img src="./admin/theme/memo/new_default/images/mark.gif" align="전체선택" title="전체선택" /></a></th>
	          <th class="check"><img src="./admin/theme/memo/new_default/images/read_no.gif" align="수신여부" title="수신여부" /></th>
  	        <th class="sender">받는사람</th>
  	        <th class="title">제목</th>
  	        <th class="date">날짜</th>
  	        <th class="delete">삭제</th>
  	      </tr>
  	    </thead>
  	    <tbody>
  	    <?php
				// 본인이 보낸 쪽지내용들 가져오기
				$getMemoList = @mysql_query("select no, member_key, subject, signdate, is_view from {$dbFIX}memo_save where ".
					"sender_key = '$sessionNo' order by no desc limit {$fromRecord}, {$viewList}");
				while($memo = mysql_fetch_array($getMemoList)) {
					$receiver = @mysql_fetch_array(mysql_query("select nickname from {$dbFIX}member_list where no = '".$memo['member_key']."'"));
				?>
  	      <tr class="listbar">
  	        <td class="checkbox"><input type="checkbox" name="delTargets[]" value="<?php echo $memo['no']; ?>" disabled="disabled" /></td>
  	        <td class="check">
  	          <?php if($memo['is_view']) { ?><img src="./admin/theme/memo/new_default/images/read.gif" align="읽음" title="읽음" /> <?php } else { ?><img src="./admin/theme/memo/new_default/images/read_no.gif" align="읽지않음" title="읽지않음" /><?php } ?>
  	        </td>
  	        <td class="sender"><?php echo $receiver['nickname']; ?></td>
  	        <td class="title"><a href="view_memo.php?viewMemoNo=<?php echo $memo['no']; ?>&action=<?php echo $action; ?>" title="<?php echo strip_tags($memo['subject']); ?>" <?php if(!$memo['is_view']) echo 'class="no_read"'; ?>>
						<?php echo strip_tags($memo['subject']); ?></a></td>
  	        <td class="date"><?php echo date("Y.m.d", $memo['signdate']); ?></td>
  	        <td class="delete"><a href="#" onclick="deleteMemo(<?php echo $memo['no']; ?>);" title="이 쪽지를 삭제합니다"><img src="./admin/theme/memo/new_default/images/delete.png" align="삭제" /></a></td>
  	      </tr>
  	    <?php	} # while ?>
  	    </tbody>
  	    <tfoot>
  	    <?php
				// 페이징 처리
				$totalResult=@mysql_fetch_array(mysql_query('select count(*) as no from '.$dbFIX.'memo_save where sender_key = '.$sessionNo));
				$totalCount=$totalResult['no'];

				$totalPage = ceil($totalCount / 10);
				if($totalCount > 10)
				{
					$printPage = $GR->getPaging(10, $page, $totalPage, 'view_memo.php?action='.$action.'&amp;page=');
				?>
				<tr class="pagelist">
					<td colspan="6"><?php echo $printPage;?></td>
				</tr>
				<?php } # 페이징 ?>
  	    </tfoot>
  	  </table>
  	</div>
  	
<?php
  // 안 읽은 쪽지 보기
	} elseif($action == 3) { ?>
	  <div class="common">
	    <h2 class="nametac3">안 읽은 쪽지</h2>
	    <ul class="memo_menu">
		    <li><a href="view_memo.php?action=1" <?php echo ($action==1)?' style="font-weight: bold"':''; ?> title="받은 쪽지">받은 쪽지</a></li>
		    <li><a href="view_memo.php?action=2" <?php echo ($action==2)?' style="font-weight: bold"':''; ?> title="보낸 쪽지">보낸 쪽지</a></li>
		    <li><a href="view_memo.php?action=3" <?php echo ($action==3)?' style="font-weight: bold"':''; ?> title="안 읽은 쪽지">안 읽은 쪽지</a></li>
		  </ul>
	    <table class="memotable" width="100%" cellspacing="0" border="0" summary="받은쪽지함 - 읽지않은 쪽지">
        <thead>
	        <tr class="titlebar">
	          <th class="checkbox"><a href="#" onclick="selectAll();" title="전체선택"><img src="./admin/theme/memo/new_default/images/mark.gif" align="전체선택" title="전체선택" /></a></th>
	          <th class="check"><img src="./admin/theme/memo/new_default/images/read_no.gif" align="읽음여부" title="읽음여부" /></th>
  	        <th class="sender">보낸사람</th>
  	        <th class="title">제목</th>
  	        <th class="date">날짜</th>
  	        <th class="delete">삭제</th>
  	      </tr>
  	    </thead>
  	    <tbody>
  	    <?php
				// 쪽지내용들 가져오기
				$getMemoList = @mysql_query("select no, sender_key, subject, signdate, is_view from {$dbFIX}memo_save where ".
					"member_key = '$sessionNo' and is_view = 0 order by no desc limit {$fromRecord}, {$viewList}");
				while($memo = mysql_fetch_array($getMemoList)) {
					$getSender = @mysql_fetch_array(mysql_query("select nickname from {$dbFIX}member_list where no = '".$memo['sender_key']."'"));
				?>
  	      <tr class="listbar">
  	        <td class="checkbox"><input type="checkbox" name="delTargets[]" value="<?php echo $memo['no']; ?>" disabled="disabled" /></td>
  	        <td class="check">
  	          <?php if($memo['is_view']) { ?><img src="./admin/theme/memo/new_default/images/read.gif" align="읽음" title="읽음" /> <?php } else { ?><img src="./admin/theme/memo/new_default/images/read_no.gif" align="읽지않음" title="읽지않음" /><?php } ?>
  	        </td>
  	        <td class="sender"><?php echo $getSender['nickname']; ?></td>
  	        <td class="title"><a href="view_memo.php?viewMemoNo=<?php echo $memo['no']; ?>&action=<?php echo $action; ?>" title="<?php echo strip_tags($memo['subject']); ?>" <?php if(!$memo['is_view']) echo 'class="no_read"'; ?>>
						<?php echo strip_tags($memo['subject']); ?></a></td>
  	        <td class="date"><?php echo date("Y.m.d", $memo['signdate']); ?></td>
  	        <td class="delete"><a href="#" onclick="deleteMemo(<?php echo $memo['no']; ?>);" title="이 쪽지를 삭제합니다"><img src="./admin/theme/memo/new_default/images/delete.png" align="삭제" /></a></td>
  	      </tr>
  	    <?php	} # while ?>
  	    </tbody>
  	    <tfoot>
  	    <?php

				// 페이징 처리
				$totalResult=@mysql_fetch_array(mysql_query('select count(*) as no from '.$dbFIX.'memo_save where member_key = '.$sessionNo.' and is_view = 0'));
				$totalCount=$totalResult['no'];
				$totalPage = ceil($totalCount / 10);
				if($totalCount > 10)
				{
					$printPage = $GR->getPaging(10, $page, $totalPage, 'view_memo.php?page=');
				?>
				<tr class="pagelist">
					<td colspan="6"><?php echo $printPage;?></td>
				</tr>
				<?php } # 페이징 ?>
  	    </tfoot>
  	  </table>
	  </div>
<?php
  // 선택한 게시물이 없을 경우 목록보기
	} else { ?>
	  <div class="common">
	  <h2 class="nametac1">받은 쪽지</h2>
	  <ul class="memo_menu">
		    <li><a href="view_memo.php?action=1" <?php echo ($action==1)?' style="font-weight: bold"':''; ?> title="받은 쪽지">받은 쪽지</a></li>
		    <li><a href="view_memo.php?action=2" <?php echo ($action==2)?' style="font-weight: bold"':''; ?> title="보낸 쪽지">보낸 쪽지</a></li>
		    <li><a href="view_memo.php?action=3" <?php echo ($action==3)?' style="font-weight: bold"':''; ?> title="안 읽은 쪽지">안 읽은 쪽지</a></li>
		    <li><a href="#" onclick="adjustMemo();" style="color: red" title="선택하신 쪽지들을 모두 삭제합니다.">선택한 쪽지삭제</a></li>
		  </ul>
	  <form id="list" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	    <table class="memotable" width="100%" cellspacing="0" border="0" summary="받은쪽지함">
        <thead>
	        <tr class="titlebar">
	          <th class="checkbox"><a href="#" onclick="selectAll();" title="전체선택"><img src="./admin/theme/memo/new_default/images/mark.gif" align="전체선택" title="전체선택" /></a></th>
	          <th class="check"><img src="./admin/theme/memo/new_default/images/read_no.gif" align="읽음여부" title="읽음여부" /></th>
  	        <th class="sender">보낸사람</th>
  	        <th class="title">제목</th>
  	        <th class="date">날짜</th>
  	        <th class="delete">삭제</th>
  	      </tr>
  	    </thead>
  	    <tbody>
  	    <?php
				// 쪽지내용들 가져오기
				$getMemoList = @mysql_query("select no, sender_key, subject, signdate, is_view from {$dbFIX}memo_save where ".
					"member_key = '$sessionNo' order by no desc limit {$fromRecord}, {$viewList}");
				while($memo = mysql_fetch_array($getMemoList)) {
					$getSender = @mysql_fetch_array(mysql_query("select nickname from {$dbFIX}member_list where no = '".$memo['sender_key']."'"));
				?>
  	      <tr class="listbar">
  	        <td class="checkbox"><input type="checkbox" name="delTargets[]" value="<?php echo $memo['no']; ?>" /></td>
  	        <td class="check">
  	          <?php if($memo['is_view']) { ?><img src="./admin/theme/memo/new_default/images/read.gif" align="읽음" title="읽음" /> <?php } else { ?><img src="./admin/theme/memo/new_default/images/read_no.gif" align="읽지않음" title="읽지않음" /><?php } ?>
  	        </td>
  	        <td class="sender"><?php echo $getSender['nickname']; ?></td>
  	        <td class="title"><a href="view_memo.php?viewMemoNo=<?php echo $memo['no']; ?>&action=<?php echo $action; ?>" title="<?php echo strip_tags($memo['subject']); ?>" <?php if(!$memo['is_view']) echo 'class="no_read"'; ?>>
						<?php echo strip_tags($memo['subject']); ?></a></td>
  	        <td class="date"><?php echo date("Y.m.d", $memo['signdate']); ?></td>
  	        <td class="delete"><a href="#" onclick="deleteMemo(<?php echo $memo['no']; ?>);" title="이 쪽지를 삭제합니다"><img src="./admin/theme/memo/new_default/images/delete.png" align="삭제" /></a></td>
  	      </tr>
  	    <?php	} # while ?>
  	    </tbody>
  	    <tfoot>
  	    <?php

				// 페이징 처리
				$totalResult=@mysql_fetch_array(mysql_query('select count(*) as no from '.$dbFIX.'memo_save where member_key = '.$sessionNo));
				$totalCount=$totalResult['no'];

				$totalPage = ceil($totalCount / 10);
				if($totalCount > 10)
				{
					$printPage = $GR->getPaging(10, $page, $totalPage, 'view_memo.php?page=');
				?>
				<tr class="pagelist">
					<td colspan="6"><?php echo $printPage;?></td>
				</tr>
				<?php } # 페이징 ?>
  	    </tfoot>
  	  </table>
  	</form>
	  </div>
<?php } # 목록보기 ?>
</div>

