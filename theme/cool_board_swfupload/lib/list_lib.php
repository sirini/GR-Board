<?php
$strongNewComment = true; //새로운 덧글을 강조할지의 여부
$strongNewCommentTime = 604800; //새로운 덧글 판단 기준. (초)단위
// 콤보박스형태의 분류 목록 출력해주기
function showCategoryComboBox() 
{
	global $dbFIX, $id, $GR;
	$categories = $GR->getArray('select category from '.$dbFIX.'board_list where id = \''.$id.'\'');
	$categoryArray = @explode('|', $categories['category']);
	$countCategory = @count($categoryArray);
	for($ca=0; $ca<$countCategory; $ca++) {
		echo '<option value="'.urlencode($categoryArray[$ca]).'"'.
			(($categoryArray[$ca] == $clickCategory)?' selected="selected"':'').'>'.
			strip_tags($categoryArray[$ca]).'</option>';
	}
}

// 네임택 설정 @dragonkun, 및 공지 데이터 가공
function setNoticeData($notice)
{
	if(!$notice['no'] || !$notice['member_key']) {
		return;
	} else {
		global $dbFIX, $id, $GR, $cutingSubject;
		$noticeMemberKey = $notice['member_key'];
		$noticetag = $GR->getArray("select nametag, icon from {$dbFIX}member_list where no = '$noticeMemberKey'");
		if($noticetag['nametag']) $notice['name'] = '<img src="'.$noticetag['nametag'].'" alt="'.$notice['name'].'" />';
		if($noticetag['icon']) $notice['name'] = '<img src="'.$noticetag['icon'].'" alt="" /> '.$notice['name'];
	}
	$notice['subject'] = htmlspecialchars($GR->cutString($notice['subject'], $cutingSubject),ENT_COMPAT, 'UTF-8');
	return $notice;
}

// 일반 게시글 데이터 가공
function setArticleData($list)
{
	if(!$list['no']) return;
	global $dbFIX, $id, $GR, $cutingSubject, $searchText, $clickCategory, $grboard, $theme, $page, $strongNewComment, $strongNewCommentTime;
	if($list['bad'] < -1000) $list['subject'] = $list['content'] = '── 관리자에 의해 블라인드 되었습니다 ──';
	if($list['member_key']) {
		$listtag = $GR->getArray("select nametag, icon from {$dbFIX}member_list where no = '".$list['member_key']."'");
		if($listtag['nametag']) $list['name'] = '<img src="'.$grboard.'/'.$listtag['nametag'].'" alt="'.$list['name'].'" title="" />';
		else $list['name'] = '<strong>'.$list['name'].'</strong>';
		if($listtag['icon']) $list['name'] = '<img src="'.$grboard.'/'.$listtag['icon'].'" alt="" /> '.$list['name'];
	}
	$list['subject'] = $GR->cutString($list['subject'], $cutingSubject);
	$list['link'] = $grboard.'/board.php?id='.$id.'&amp;articleNo='.(($list['board_no'])?$list['board_no']:$list['no']).'&amp;page='.$page.'&amp;searchText='.urlencode($searchText).'&amp;clickCategory='.$clickCategory;

	if($searchText) $list['subject'] = str_replace($searchText, '<span class="findMe">'.$searchText.'</span>', $list['subject']);

	if($list['is_secret']) $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/secret.gif" alt="비밀" />';
	elseif(time() < $list['signdate']+86400) $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/new.gif" alt="새글" />';
	else $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/arrow.gif" alt="" />';
	if($strongNewComment){ //새 덧글 강조 by pico
		$last = $GR->getArray("select signdate from {$dbFIX}comment_{$id} where board_no='{$list['no']}' order by no desc limit 0,1");
		if(time() - $last['signdate'] < $strongNewCommentTime){$list['strongComment']="_em";}
	}
	return $list;
}
?>
