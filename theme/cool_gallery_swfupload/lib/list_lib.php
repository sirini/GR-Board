<?php
// 갤러리 설정값 조정하기
$setting['show_header'] = 1; # 게시판 상단 정렬옵션, 분류표 등을 볼 것인지 설정 (기본: 보기=1)
$setting['column_count'] = 4; # 한 줄에 몇개의 사진을 보이게 할 것인지 개수 지정 (기본: 한줄에 4개씩)
$setting['thumb_width_size'] = 150; // 썸네일 폭 지정 (px 단위)
// 갤러리 설정값 조정하기 끝

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
			stripslashes($categoryArray[$ca]).'</option>';
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
	$notice['subject'] = htmlspecialchars($GR->cutString(stripslashes($notice['subject']), $cutingSubject),ENT_COMPAT, 'UTF-8');
	return $notice;
}

// 일반 게시글 데이터 가공
function setArticleData($list, $size)
{
	if(!$list['no']) return;
	global $dbFIX, $id, $GR, $cutingSubject, $searchText, $clickCategory, $grboard, $theme, $page;
	if($list['bad'] < -1000) $list['subject'] = $list['content'] = '── 관리자에 의해 블라인드 되었습니다 ──';
	if($list['member_key']) {
		$listtag = $GR->getArray("select nametag, icon from {$dbFIX}member_list where no = '".$list['member_key']."'");
		if($listtag['nametag']) $list['name'] = '<img src="'.$grboard.'/'.$listtag['nametag'].'" alt="'.$list['name'].'" title="" />';
		else $list['name'] = '<strong>'.$list['name'].'</strong>';
	}
	$list['subject'] = htmlspecialchars($GR->cutString(stripslashes($list['subject']), $cutingSubject),ENT_COMPAT, 'UTF-8'); 
	$list['link'] = $grboard.'/board.php?id='.$id.'&amp;articleNo='.(($list['board_no'])?$list['board_no']:$list['no']).'&amp;page='.$page.'&amp;searchText='.urlencode($searchText).'&amp;clickCategory='.$clickCategory;

	if($searchText) $list['subject'] = str_replace($searchText, '<span class="findMe">'.$searchText.'</span>', $list['subject']);

	if($list['is_secret']) $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/secret.gif" alt="비밀" />';
	elseif(time() < $list['signdate']+86400) $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/new.gif" alt="새글" />';
	else $list['icon'] = '<img src="'.$grboard.'/'.$theme.'/image/arrow.gif" alt="" />';
	
	$getFirstImg = $GR->getArray('select file_route1 as route from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = ' . $list['no']);
	if(!$getFirstImg['route']) $getFirstImg = $GR->getArray('select file_route as route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = ' . $list['no'] . ' order by no asc limit 1');
	$list['thumb'] = makeThumbImageList($getFirstImg['route'], $size);
	
	return $list;
}

// 썸네일 이미지 만들기
function makeThumbImageList($path, $size)
{
	global $theme;
	if(!$path || !file_exists($path)) return $theme . '/image/no_image.gif';
	$filename = end(explode('/', $path));
	$filetype = strtolower(end(explode('.', $filename)));
	$genFile = 'data/__thumbs__/'.date('Y/m/d').'/'.$size.'_'.$filename;
	if(file_exists($genFile)) return $genFile;
	
	$pre = 'data/__thumbs__';
	$y = date('Y');
	$m = date('m');
	$d = date('d');
	
	if( !is_dir($pre) ) { @mkdir($pre, 0705); @chmod($pre, 0707); }
	if( !is_dir($pre . '/' . $y) ) { @mkdir($pre . '/' . $y, 0705); @chmod($pre . '/' . $y, 0707); }
	if( !is_dir($pre . '/' . $y .'/'. $m) ) { @mkdir($pre . '/' . $y .'/'. $m, 0705); @chmod($pre . '/' . $y .'/'. $m, 0707); }
	if( !is_dir($pre . '/' . $y .'/'. $m .'/'. $d) ) { @mkdir($pre . '/' . $y .'/'. $m .'/'. $d, 0705); @chmod($pre . '/' . $y .'/'. $m .'/'. $d, 0707); }
	
	$thumb = PhpThumbFactory::create($path);
	$thumb->adaptiveResize($size, $size)->save($genFile);
	
	if($filetype == 'jpg' || $filetype == 'jpeg') {
		$image = imagecreatefromjpeg($genFile);
		$matrix = array( array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1) );
		$divisor = array_sum(array_map('array_sum', $matrix));
		$offset = 0; 
		imageconvolution($image, $matrix, $divisor, $offset);
		@unlink($genFile);
		imagejpeg($image, $genFile, 100);	
	}
	
	return $genFile;
}

?>
