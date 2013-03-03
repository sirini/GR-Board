<?php
// 글보기시 특정 기능 ON / OFF 설정 //
$setting['enable_facebook'] = 0; # 페이스북에 글 보내기 기능 사용=1, 미사용=0
$setting['enable_twitter'] = 0; # 트위터에 글 소개하기 기능 사용=1, 미사용=0
$setting['enable_added_photo_direct_show'] = 0; # 추가 업로드한 사진들을 본문에 바로 보여주기 사용=1, 미사용=0
$setting['thumb_width_size'] = 550; // 글보기에서 썸네일 폭 지정, 0 이면 사용 안함 (px 단위)
$setting['thumb_width_bottom'] = 50; // 글보기 하단에 추가 첨부한 사진들 사이즈 지정 (px 단위)
// 글보기시 특정 기능 ON / OFF 설정 끝 //

// 썸네일 이미지 만들기
function makeThumbImage($path, $width=0)
{
	global $theme, $setting;
	if(!file_exists($path)) return '';
	if(!$width) $width = $setting['thumb_width_size'];
	$filename = end(explode('/', $path));
	$filetype = strtolower(end(explode('.', $filename)));
	$genFile = 'data/__thumbs__/'.date('Y/m/d').'/'.$width.'_'.$filename;
	if(file_exists($genFile)) return $genFile;
	
	if(!is_dir('data/__thumbs__/'.date('Y'))) @mkdir('data/__thumbs__/'.date('Y'), 0707);
	if(!is_dir('data/__thumbs__/'.date('Y/m'))) @mkdir('data/__thumbs__/'.date('Y/m'), 0707);
	if(!is_dir('data/__thumbs__/'.date('Y/m/d'))) @mkdir('data/__thumbs__/'.date('Y/m/d'), 0707);
	
	$thumb = PhpThumbFactory::create($path);
	$thumb->resize($width, $width)->save($genFile);
	
	if($filetype == 'jpg' || $filetype == 'jpeg') {
		$image = imagecreatefromjpeg($genFile);
		$matrix = array( array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1) );
        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0; 
        imageconvolution($image, $matrix, $divisor, $offset);
		imagejpeg($image, $genFile, 100);	
	}
	return $genFile;
}

// 본문에 추가 첨부된 파일을 보여주기
function showAddedPhoto()
{
	global $dbFIX, $GR, $id, $articleNo;
	$getExtendFile = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
	while($extendFile = $GR->fetch($getExtendFile)) { 
		$extendFileName = end(explode('/', $extendFile['file_route']));
		$ft = strtolower(end(explode('.', $extendFileName)));
		if($ft == 'jpg' || $ft == 'gif' || $ft == 'png' || $ft == 'bmp') {
			$thumb = makeThumbImage($extendFile['file_route']);
			if($thumb) echo '<div class="addedPhotoThumb"><a href="'.$extendFile['file_route'].'" onclick="return hs.expand(this)" title="클릭하시면 사진을 크게 봅니다"><img src="'.$thumb.'" alt="미리보기" /></a></div>';
		}
	}
}

// 첨부파일이 그림일 경우 처리하는 함수
function showImg($filename, $f=1)
{
	global $id, $theme, $grboard, $articleNo, $dbFIX, $GR;
	$getPdsSave = $GR->getArray('select no, file_route'.$f.' from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = '.$articleNo.' limit 1');
	$path = $getPdsSave['file_route'.$f];
	$ft = strtolower(end(explode('.', $filename)));
	if($ft == 'jpg' || $ft == 'gif' || $ft == 'png' || $ft == 'bmp') {
		return '<span><a href="'.$path.'" onclick="return hs.expand(this)" title="클릭하시면 사진을 크게 봅니다"><img src="'.$path.'" width="550px" alt="그림보기" /></a></span>';
	}
	else return '<div class="attachedList"><strong class="attached">[첨부 파일받기]</strong></div>';
}

// 멤버일 경우 등록된 사진과 자기소개 출력
function showMemberInfo($mem=0)
{
	if(!$mem) return;
	global $dbFIX, $GR;
	$result = '<div id="viewMemInfo">';
	$m = $GR->getArray("select photo, self_info from {$dbFIX}member_list where no = '$mem'");
	if($m['photo']) $result .= '<div id="myPhoto"><img src="'.$m['photo'].'" alt="사진" title="" /></div>';
	else $result .= '<div id="myPhoto">&nbsp;</div>';
	if($m['self_info']) $result .= '<div id="myComment">'.$m['self_info'].'</div>';
	else $result .= '<div id="myComment">소개글이 없습니다.</div>';
	$result .= '<div class="clear"></div></div>';
	return $result;
}

// 이름 출력 부분에 네임택이나 아이콘 출력 기능 추가
function showName($no, $name)
{
	$result = $name;
	global $dbFIX, $GR;
	$listtag = $GR->getArray("select nametag, icon from {$dbFIX}member_list where no = '".$no."'");
	if($listtag['nametag']) $result = '<img src="'.$listtag['nametag'].'" alt="" />';
	else $result = '<strong>'.$result.'</strong>';
	if($listtag['icon']) $result = '<img src="'.$listtag['icon'].'" alt="" /> '.$result;
	return $result;
}

// 지정된 너비 이상의 이미지는 본문 보기시 자동 리사이즈
function autoImgResize($maxWidth, $content)
{
	$content = str_replace(array('class="multi-preview" src="', '" alt="미리보기"'), array('class="multi-preview" src="phpThumb/phpThumb.php?src=../',
		'&amp;w='.$maxWidth.'&amp;q=100&amp;fltr[]=usm|99|0.5|3" alt="미리보기"'), $content);
	return $content;
}

// 추가 첨부된 파일 목록 출력
function showAddedFileList()
{
	global $dbFIX, $GR, $id, $articleNo, $setting;
	$extendLoop = 1;
	$getExtendFile = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
	while($extendFile = $GR->fetch($getExtendFile)) { 
		$extendFileName = end(explode('/', $extendFile['file_route']));
		$ft = strtolower(end(explode('.', $extendFileName)));
		if($ft == 'jpg' || $ft == 'gif' || $ft == 'png' || $ft == 'bmp') {
			$thumb = makeThumbImage($extendFile['file_route'], $setting['thumb_width_bottom']);
			if($thumb) echo '<a href="'.$extendFile['file_route'].'" onclick="return hs.expand(this)" title="클릭하시면 사진을 크게 봅니다."><img class="addedPhotoBottom" src="'.$thumb.'" alt="미리보기" /></a>';
		}
		else echo '<a href="'.$grboard.'/download.php?id='.$id.'&amp;articleNo='.$articleNo.'&amp;extNo='.$extNo.'">'.$extendFileName.'</a> &nbsp;';
		$extendLoop++; 
	}
}

// 댓글 보기 전 전처리
function setViewData($comment)
{
	// 내용이 없으면 종료
	if( !$comment ) return;
	
	global $GR, $dbFIX, $grboard;
	
	// 댓글이 블라인드 상태일 때
	if($comment['bad'] < -1000) {
		$blindMsg = '<div class="smallEng" title="댓글은 삭제되지 않았으나, 블라인드 해제가 되지 않으면 댓글 내용을 볼 수 없습니다.">'.
			'<strong>[!]</strong> 댓글이 관리자에 의해 블라인드 처리 되었습니다.'.(($isAdmin)?' (관리자는 댓글 내용이 보입니다.)':'').'</div>';
		if($isAdmin) $comment['content'] = $blindMsg.$comment['content'];
		else $comment['content'] = $blindMsg;
		$comment['subject'] = '── 관리자에 의해 블라인드 되었습니다 ──';
	}

	// 변수 처리
	$comment['name'] = strip_tags($comment['name']);
	$comment['subject'] = htmlspecialchars($comment['subject']);
	$comment['content'] = nl2br($comment['content']);
	$comment['signdate'] = date("Y.m.d H:i:s", $comment['signdate']);
	$comment['homepage'] = htmlspecialchars($comment['homepage']);
	$comment['email'] = htmlspecialchars($comment['email']);
	
	// 홈페이지
	if($comment['homepage']) $comment['homepage'] = '<a href="'.$comment['homepage'].'" class="commentBtn" title="'.$comment['name'].' 님의 홈으로 갑니다." onclick="window.open(this.href, \'_blank\'); return false;">[H]</a>';
	else $comment['homepage'] = "";
	
	// 이메일
	if($comment['email']) $comment['email'] = '<a href="mailto:'.$comment['email'].'" class="commentBtn" title="'.$comment['name'].' 님에게 메일을 보냅니다.">[E]</a>';
	else $comment['email'] = "";

	// 이름 대신 닉콘
	if($comment['member_key']) {
		$listtag = $GR->getArray("select nametag, icon from {$dbFIX}member_list where no = '".$comment['member_key']."'");
		if($listtag['nametag']) $comment['name'] = '<img src="'.$grboard.'/'.$listtag['nametag'].'" alt="'.$comment['name'].'" title="" /> ';
		if($listtag['icon']) $comment['name'] = '<img src="'.$grboard.'/'.$listtag['icon'].'" alt="" /> '.$comment['name'];
	}

	// 비밀 코멘트 시 처리
	if($comment['is_secret']) {
		if(!$_SESSION['no'] || ($comment['member_key'] != $_SESSION['no']) && ($view['member_key'] != $_SESSION['no']) && ($_SESSION['no'] != 1)) {
			$comment['subject'] = '비밀 댓글 입니다.';			
			$comment['content'] = '<span class="secretComment">비밀 댓글 입니다.</span>';
		}
	}
	return $comment;
}
?>
