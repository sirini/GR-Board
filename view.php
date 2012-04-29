<?php
if(!defined('__GRBOARD__')) exit();

// 변수 넘겨받기
$page = (int)$_GET['page'];
if(!$page) $page = 1;
$isViewList = $tmpFetchBoard['is_list'];

// XSS 방지 @미니어스
if ($articleNo && !(int)$articleNo) exit;
if ($modifyTarget && !(int)$modifyTarget) exit;
if ($commentPage && !(int)$commentPage) exit;
if ($replyTarget && !(int)$replyTarget) exit;
if ($useCoEditor && !(int)$useCoEditor) exit;

// 게시물 블라인드 처리 @sirini
$blind = $_GET['blind'];
$tableType = $_GET['tableType'];
if(isset($_GET['tableType']) && ($_GET['tableType'] != 'bbs_' && $_GET['tableType'] != 'comment_')) exit();
$blindTarget = (int)$_GET['blindTarget'];
if ($blindTarget && !(int)$blindTarget) exit;
if($isAdmin && $blind && $tableType && $blindTarget) {
	if($blind == 'on') $GR->query('update '.$dbFIX.$tableType.$id.' set bad = -1001 where no = '.$blindTarget.' limit 1');
	else $GR->query('update '.$dbFIX.$tableType.$id.' set bad = +1001 where no = '.$blindTarget.' limit 1');
	$GR->error($id.' 게시판 '.$articleNo.'번 게시물의'.(($blindTarget!='bbs_')?' 선택된 댓글에 대한':'').' 블라인드 설정을 '.$blind.' 으로 설정했습니다.', 1, $grboard.'/board.php?id='.$id.'&articleNo='.$articleNo.'#viewComment'.$blindTarget);
}

// 게시물 보기 권한 체크 @sirini
if(!$isAdmin && ($tmpFetchBoard['view_level'] > $visitorLevel)) $GR->error('게시물을 볼 수 있는 권한이 없습니다.', 0, $grboard.'/board.php?id='.$id);

// 페이징 처리 @sirini
$commentPage = (int)$_GET['commentPage'];
if(!$commentPage) $commentPage = 1;
$commentFromRecord = ($commentPage - 1) * $tmpFetchBoard['comment_page_num'];
$getCommentTotalNum = $GR->getArray("select count(*) from {$dbFIX}comment_{$id} where board_no = '$articleNo'");
$totalCommentCount = $getCommentTotalNum[0];
$getCommentMaxNo = $GR->getArray("select max(no) from {$dbFIX}comment_{$id} where board_no = '$articleNo'");
$maxCommentNo = $getCommentMaxNo[0];
$arrangeComment = 5000;

// 게시물 가져오기 @sirini
$view = $GR->getArray("select * from {$dbFIX}bbs_{$id} where no = '$articleNo'");
if(!$view[0]) $GR->error('선택하신 게시물이 존재하지 않습니다.', 0, $grboard.'/board.php?id='.$id);

// 블라인드 상태일 때 @sirini
if($view['bad'] < -1000) {
	$blindMsg = '<div id="readAlert" title="게시물은 삭제되지 않았으나, 블라인드 해제가 되지 않으면 게시물 내용을 볼 수 없습니다.">'.
		'<strong>[!]</strong> 게시물이 관리자에 의해 블라인드 처리 되었습니다.'.(($isAdmin)?' (관리자는 글내용이 보입니다.)':'').'</div>';
	if($isAdmin) $view['content'] = $blindMsg.$view['content'];
	else $view['content'] = $blindMsg;
	$view['subject'] = '── 관리자에 의해 블라인드 되었습니다 ──';
	$view['link1'] = $view['link2'] = $view['tag'] = $view['homepage'] = '';
}

// 플래시 삽입검사
$object_view = $_GET['object_view'];
if(!$isAdmin && !$object_view) {
	$securityMsg = '<div class="flahPlayer"><p>Object(flash, media player)가 본문에서 발견되었습니다.<br />악의적으로 악성코드를 심어놓았을 경우, 사용자의 컴퓨터에 문제를 일으킬 수 있습니다.<br /><a href="board.php?id='.$id.'&amp;articleNo='.$articleNo.'&amp;page='.$page.'&amp;searchText='.$searchText.'&amp;object_view=1">계속해서 진행하시려면 여기를 눌러주세요</a></div>';
	$view['content'] = preg_replace('/<object[^>]+>(.*?<\/object>)?/is', $securityMsg, $view['content']);
	$view['content'] = preg_replace('/<embed[^>]+>(\s*<\/embed>)?/is', $securityMsg, $view['content']);
}
if($isAdmin) {
	$securityMsg = '<div class="flahPlayer"><p>Object(flash, media player)가 본문에서 발견되었습니다.<br />보안정책에 따라, 관리자는 해당 내용을 볼 수 없습니다.<br />다른아이디로 로그인해주세요.</p></div>';
	$view['content'] = preg_replace('/<object[^>]+>(.*?<\/object>)?/is', $securityMsg, $view['content']);
	$view['content'] = preg_replace('/<embed[^>]+>(\s*<\/embed>)?/is', $securityMsg, $view['content']);
}

// iframe 걸러내기
$securityMsg = '';
if($isAdmin) $securityMsg = '<p style="color: red; font-weight: bold;">이 게시물에 iframe이 삽입되어있습니다.<br />글쓴이가 악의적으로 삽입한 것인지 확인 후 조치를 취해주세요.</p>';
$view['content'] = preg_replace('/<iframe[^>]+>(\s*<\/iframe>)?/is', $securityMsg, $view['content']);

// Script 걸러내기
$securityMsg = '';
if($isAdmin) $securityMsg = '<p style="color: red; font-weight: bold;">이 게시물에 스크립트가 삽입되어있습니다.<br />글쓴이가 악의적으로 삽입한 것인지 확인 후 조치를 취해주세요.</p>';
$view['content'] = preg_replace('/<script[^>]+>(\s*<\/script>)?/is', $securityMsg, $view['content']);

// 보고 있는 사람이 게시물 작성자인지 확인한다. @sirini
if(($_SESSION['no'] && $view['member_key'] && ($_SESSION['no'] == $view['member_key'])) or 	$isAdmin) $isWriter = 1; else $isWriter = 0;

// 게시물이 비밀글일 경우는 먼저 비밀번호를 물어보는 페이지를 인클루드 한다. @sirini
if(!$isWriter && $view['is_secret']) {
	if($alreadyEnterPassword) {
		$tFetchPass = $GR->getArray("select password from {$dbFIX}bbs_{$id} where no = '$articleNo'");
		if($alreadyEnterPassword != sha1($tFetchPass['password'])) {
			$GR->error('입력하셨던 패스워드로 게시물에 접근하지 못했습니다.', 0, $grboard.'/board.php?id='.$id.'&page='.$page);
		}
	}
	else $GR->move($grboard.'/enter_password.php?id='.$id.'&page='.$page.'&articleNo='.$articleNo.'&readyWork=view&modifyTarget='.$_GET['modifyTarget']);
}

// 자동폭파글일 경우 체크 @sirini
if($tmpFetchBoard['is_bomb']) {
	$getBomb = $GR->getArray("select * from {$dbFIX}time_bomb where id = '$id' and article_num = '$articleNo'");
	if($getBomb['no']) {
		if($GR->grTime() > $getBomb['set_time']) {
			$GR->query("delete from {$dbFIX}bbs_{$id} where no = '$articleNo'");
			$GR->query("delete from {$dbFIX}comment_{$id} where board_no = '$articleNo'");
			$GR->query("delete from {$dbFIX}total_article where id = '$id' and article_num = '$articleNo'");
			$GR->query("delete from {$dbFIX}total_comment where id = '$id' and article_num = '$articleNo'");

			$bombPds = $GR->getArray("select * from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");
			if($bombPds['no']) {
				if($bombPds['file_route1']) @unlink($bombPds['file_route1']);
				if($bombPds['file_route2']) @unlink($bombPds['file_route2']);
				if($bombPds['file_route3']) @unlink($bombPds['file_route3']);
				if($bombPds['file_route4']) @unlink($bombPds['file_route4']);
				if($bombPds['file_route5']) @unlink($bombPds['file_route5']);
				if($bombPds['file_route6']) @unlink($bombPds['file_route6']);
				if($bombPds['file_route7']) @unlink($bombPds['file_route7']);
				if($bombPds['file_route8']) @unlink($bombPds['file_route8']);
				if($bombPds['file_route9']) @unlink($bombPds['file_route9']);
				if($bombPds['file_route10']) @unlink($bombPds['file_route10']);
				$GR->query('delete from '.$dbFIX.'pds_list where type = 0 and uid = '.$bombPds['no']);
			}
			$GR->query("delete from {$dbFIX}pds_save where no = '".$bombPds['no']."'");
			$GR->query("delete from {$dbFIX}time_bomb where no = '".$getBomb['no']."'");
			
			$getExtendFiles = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
			while($extFiles = $GR->fetch($getExtendFiles)) {
				@unlink($extFiles['file_route']);
				$GR->query('delete from '.$dbFIX.'pds_list where type = 1 and uid = '.$extFiles['no']);
			}
			$GR->query("delete from {$dbFIX}pds_extend where id = '$id' and article_num = '$articleNo'");
			$GR->error('글 작성자가 설정한 폭파시간을 초과하여 글이 폭파 되었습니다!', 0, 'board.php?id='.$id.'&page='.$page);
		} else {
			$view['content'] .= '<div id="bombTime" title="글쓴이가 지정한 시간 이후에 열람할 시 댓글도 함께 삭제됩니다.">'.
			'※ 이 글은 '.date('n월 j일 H시 i분', $getBomb['set_time']).' 이후 열람 시 자동으로 폭파됩니다. (현재시간: '.date('j일 G시 i분', $GR->grTime()).')</div>';
		}
	}
}

// 스팸방지용 질문코드 (산수) @sirini
if(!$_SESSION['no']) {
	$antiSpam0 = mt_rand(1, 9);
	$antiSpam1 = mt_rand(1, 9);
	$antiSpam2 = mt_rand(0, 1);
	if($antiSpam2) {
		$_SESSION['antiSpam'] = $antiSpam0 + $antiSpam1;
		$antiSpam3 = '+';
	}
	else {
		$_SESSION['antiSpam'] = $antiSpam0 * $antiSpam1;
		$antiSpam3 = 'x';
	}
}

// 제목, 내용 값 처리
$subject = $view['subject'];
$content = nl2br($view['content']);

// 내용 중 검색어는 하이라이트 @sirini
if($searchText) {
	$searchText = urldecode($searchText);
	$content = str_replace($searchText, '<span class="findMe">'.$searchText.'</span>', $content);
}

// 경고문구 부착 필요시 부착함 @sirini
if($view['bad'] < -60 && $view['bad'] > -1000) {
	$content = '<div id="readAlert" onclick="readAlert();" title="읽기를 원하지 않으실 경우 뒤로가기 를 누르시거나 백스페이스(←)를 눌러주세요.">'.
		'※ 이 글은 글쓴이가 경고문구를 부착한 글입니다. 읽기를 원하시면 클릭해 주세요.</div>'.
		'<div id="hideContent" style="display: none">'.$content.'</div>';
}

// 조회수를 올린다. (세션은 이미 시작되었음) @sirini
if(strpos($_SESSION['hit'],'gr_hit_'.$articleNo) === false) {
	$GR->query("update {$dbFIX}bbs_{$id} set hit = hit+1 where no = '$articleNo'");
	$_SESSION['hit'] = $_SESSION['hit'].',gr_hit_'.$articleNo;
}

// 게시물, 코멘트 각각 추천(good) 혹은 비추(bad) 를 눌렀을 때 해당 값을 올린다. @sirini
if($good || $bad) {
	if(!$voteCommentNo && strpos($_SESSION['vote'], 'gr_vote_'.$articleNo) === false) {
		if($good) $updateVoteQue = 'good = good+1';
		else $updateVoteQue = 'bad = bad+1';
		$GR->query("update {$dbFIX}bbs_{$id} set {$updateVoteQue} where no = '$articleNo'");
		$_SESSION['vote'] = $_SESSION['vote'].',gr_vote_'.$articleNo;
		
		// 활동 알림판에도 기록해둠
		if($view['member_key'] && $_SESSION['no'] && $view['member_key'] != $_SESSION['no']) {
			$GR->query("insert into {$dbFIX}notification set no = '', to_key = '".$view['member_key']."', from_key = '".$_SESSION['no']."', " . 
				"act = '3', bbs_id = '$id', bbs_no = '$articleNo', is_checked = '0'");
		}
	}
	if($voteCommentNo && strpos($_SESSION['voteComment'], 'gr_vote_comment_'.$voteCommentNo) === false) {
		if($good) $updateVoteCommentQue = 'good = good+1';
		else $updateVoteCommentQue = 'bad = bad+1';
		$GR->query("update {$dbFIX}comment_{$id} set {$updateVoteCommentQue} where no = '$voteCommentNo'");
		$_SESSION['voteComment'] = $_SESSION['voteComment'].',gr_vote_comment_'.$voteCommentNo;
		
		// 활동 알림판에도 기록해둠
		$coWriter = $GR->getArray("select member_key from {$dbFIX}comment_{$id} where no = '$voteCommentNo'");
		if($coWriter['member_key'] && $_SESSION['no'] && $coWriter['member_key'] != $_SESSION['no']) {
			$GR->query("insert into {$dbFIX}notification set no = '', to_key = '".$coWriter['member_key']."', from_key = '".$_SESSION['no']."', " . 
				"act = '4', bbs_id = '$id', bbs_no = '$articleNo', is_checked = '0'");
		}
	}
}

// 게시물에 연관된 업로드된 파일을 가져와서 처리한다. @sirini
$fileData = $GR->getArray('select no, file_route1, file_route2, file_route3, file_route4, file_route5, '.
	"file_route6, file_route7, file_route8, file_route9, file_route10, hit from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");

if($fileData['no'] && ($view['bad'] > -1000)) {
	$isFiles = 1;
	$downloadHit = $fileData['hit'];
	if($fileData['file_route1']) { $tFileArray1 = explode('/', $fileData['file_route1']); $files[1] = $filename1 = $tFileArray1[count($tFileArray1)-1]; }
	if($fileData['file_route2']) { $tFileArray2 = explode('/', $fileData['file_route2']); $files[2] = $filename2 = $tFileArray2[count($tFileArray2)-1]; }
	if($fileData['file_route3']) { $tFileArray3 = explode('/', $fileData['file_route3']); $files[3] = $filename3 = $tFileArray3[count($tFileArray3)-1]; }
	if($fileData['file_route4']) { $tFileArray4 = explode('/', $fileData['file_route4']); $files[4] = $filename4 = $tFileArray4[count($tFileArray4)-1]; }
	if($fileData['file_route5']) { $tFileArray5 = explode('/', $fileData['file_route5']); $files[5] = $filename5 = $tFileArray5[count($tFileArray5)-1]; }
	if($fileData['file_route6']) { $tFileArray6 = explode('/', $fileData['file_route6']); $files[6] = $filename6 = $tFileArray6[count($tFileArray6)-1]; }
	if($fileData['file_route7']) { $tFileArray7 = explode('/', $fileData['file_route7']); $files[7] = $filename7 = $tFileArray7[count($tFileArray7)-1]; }
	if($fileData['file_route8']) { $tFileArray8 = explode('/', $fileData['file_route8']); $files[8] = $filename8 = $tFileArray8[count($tFileArray8)-1]; }
	if($fileData['file_route9']) { $tFileArray9 = explode('/', $fileData['file_route9']); $files[9] = $filename9 = $tFileArray9[count($tFileArray9)-1]; }
	if($fileData['file_route10']) { $tFileArray10 = explode('/', $fileData['file_route10']); $files[10] = $filename10 = $tFileArray10[count($tFileArray10)-1]; }
}
else $isFiles = 0;

// 태그를 정제한다. @sirini
if($view['tag']) {
	$tag = '';
	$tagArray = @explode(',', $view['tag']);
	$tagCount = count($tagArray);
	for($t=0; $t<$tagCount; $t++) $tag .= '<a href="'.$grboard.'/board.php?id='.$id.'&amp;searchOption=tag&amp;searchText='.urlencode($tagArray[$t]).'">'.$tagArray[$t].'</a>, ';
	$tag = substr($tag, 0, -2);
} else $tag = '없음';

// 게시판 상단 부분과 글내용부분 불러오기 @sirini
include $theme.'/head.php';
include $theme.'/view.php';

// 범주의 크기를 구분해서 처리 @sirini
if($maxCommentNo > $arrangeComment) {
	if(!$commentDivision) {
		$commentDivision = ceil($maxCommentNo / $arrangeComment);
		$originCommentDivision = $commentDivision; 
	}
	$moreThanMeComment = ($commentDivision - 1) * $arrangeComment;
	$lessThanMeComment = $commentDivision * $arrangeComment;
	$totalPageComment = ceil($arrangeComment / $tmpFetchBoard['comment_page_num']);
}
else {
	if(!$cdivision) {
		$division = 0;
		$originDivision = 0;
	}
	$moreThanMeComment = 0;
	$lessThanMeComment = $arrangeComment;
	$totalPageComment = ceil($totalCommentCount / $tmpFetchBoard['comment_page_num']);
}

// 코멘트 하단 페이징 처리 @좋아, @이동규
$printPage = $GR->getPaging($tmpFetchBoard['comment_page_per_list'], $commentPage,
  $totalPageComment, $grboard.'/board.php?id='.$id.'&amp;articleNo='.$articleNo.
  '&amp;page='.$page.($alreadyEnterPassword?'&amp;alreadyEnterPassword='.$alreadyEnterPassword:'').'&amp;commentPage=',
  $commentDivision, $originCommentDivision, $searchOption, $searchText);
	
// 게시물에 달린 코멘트를 가져온다. @sirini
$commentPageNum = $tmpFetchBoard['comment_page_num'];
$getComment = $GR->query("select * from {$dbFIX}comment_{$id} where board_no = '$articleNo' ".
	"order by family_no ".(($tmpFetchBoard['comment_sort'])?'asc':'desc').", order_key asc limit {$commentFromRecord}, {$commentPageNum}");
if($getComment) include $theme.'/view_comment.php';

// 답변 혹은 수정 혹은 일반 코멘트 작성 폼, 게시판 하단 부분 출력 @sirini
if($isAdmin || ($tmpFetchBoard['comment_write_level'] <= $visitorLevel)) {
	if($replyTarget && !$modifyTarget) {
		$comment = $GR->getArray("select member_key, subject, content, is_secret from {$dbFIX}comment_{$id} where no = '$replyTarget'");
		if($comment['is_secret']) {
			if(($comment['member_key'] != $_SESSION['no']) && ($view['member_key'] != $_SESSION['no']) && ($_SESSION['no'] != 1)) {
				$comment['subject'] = '비밀 댓글 입니다.';
				$comment['content'] = '비밀 댓글 입니다.';
			}
		}
	}
	elseif($modifyTarget && !$replyTarget) {
		$comment = $GR->getArray('select member_key, name, email, homepage, bad, subject, content, '.
			"is_grcode, is_secret from {$dbFIX}comment_{$id} where no = '$modifyTarget'");
		if(($comment['bad'] < -1000) && !$isAdmin) {
			$comment['subject'] = '블라인드 된 글입니다.';
			$comment['content'] = '블라인드 된 글입니다.';
		} 
		if($comment['is_secret']) {
			if(($comment['member_key'] != $_SESSION['no']) && ($view['member_key'] != $_SESSION['no']) && ($_SESSION['no'] != 1)) {
				$comment['subject'] = '비밀 댓글 입니다.';
				$comment['content'] = '비밀 댓글 입니다.';
			}
		}
	}

	// 게시물 작성자가 댓글을 허용할 때만 작성폼 출력
	$getArticleOption = $GR->getArray("select no, reply_open from {$dbFIX}article_option where id = '$id' and article_num = '$articleNo'");
	if(!$getArticleOption['no'] || $getArticleOption['reply_open']) include $theme.'/view_comment_write.php';
}
// 글보기 하단 출력
include $theme.'/view_foot.php';

// 로그인 상태이면 쪽지 확인
if($sessionNo) {
	$isNewMemo = $GR->getArray('select is_view from '.$dbFIX.'memo_save where member_key = '.$sessionNo.' order by no desc limit 1');
	if($isNewMemo['is_view'] == '0') {
		$getNotify = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'notify_skin\' limit 1');
		echo '<div id="newMsgCheck">';
		include 'admin/theme/memo_notify/'.(($getNotify['var'])?$getNotify['var']:'default').'/memo_notify.php';
		echo '</div>';
	}
}
?>
