<?php
// 클래스 초기화 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();
include 'class/blog.php';
$BLOG = new BLOG;

// 허용한 태그를 제외한 나머지 태그 제거 함수 @sirini
function strip_tags2($text, $tags) {
	$allowedTags = explode(',', $tags);
	preg_match_all('!<\s*(/)?\s*([a-zA-Z]+)[^>]*>!', $text, $allTags);
	array_shift($allTags);
	$slashes = $allTags[0];
	$allTags = $allTags[1];
	foreach ($allTags as $i => $tag) {
		if (in_array($tag, $allowedTags)) continue;
		$text = preg_replace('!<(\s*'.$slashes[$i].'\s*'.$tag.'[^>]*)>!', '&lt;$1&gt;', $text);
	}
	return $text;
}

//찾는 문자열을 한 번만 바꾸는 함수, @pico
function str_replace_once($haystack, $needle , $replace, $pos){
    $pos = strpos($haystack, $needle, $pos);
    if ($pos === false) { //찾는 데이터가 없으면
        return array($haystack, $pos); //지금까지 문자열과 현재검색위치 반환
    }
    //찾는 데이터가 있으면 치환한 문자열과 현재검색위치 반환. 현재위치부터 검색 시작.
    return array(substr_replace($haystack, $replace, $pos, strlen($needle)), $pos);
}

// 불법적인 글쓰기는 아닌가 체크 @sirini
if(!preg_match('|'.$_SERVER['HTTP_HOST'].'|i', $_SERVER['HTTP_REFERER'])) 
	$GR->error('정상적인 방법으로 게시물을 작성해 주세요.', 1);

// 변수 처리 1 - 비회원 / 회원 @sirini
$ip = $_SERVER['REMOTE_ADDR'];
if($_POST['id']) $id = $_POST['id']; else $id = $_GET['id'];
if($_POST['articleNo']) $articleNo = $_POST['articleNo'];
if($_POST['mode']) $mode = $_POST['mode'];
if($_POST['page']) $page = $_POST['page'];
if($_POST['is_secret']) $isSecret = $_POST['is_secret'];
if($_POST['is_notice']) $isNotice = $_POST['is_notice'];
if($_POST['is_grcode']) $isGrcode = $_POST['is_grcode'];
if($_POST['password']) $password = $_POST['password'];
if($_POST['name']) $name = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['name']))));
if($_POST['category']) $category = $_POST['category'];
if($_POST['subject']) $subject = $_POST['subject'];
if($_POST['content']) $content = $_POST['content'];
if($_POST['email']) $email = $_POST['email'];
if($_POST['homepage']) $homepage = htmlspecialchars(trim($_POST['homepage']));
if($_POST['link1']) $link1 = htmlspecialchars(trim($_POST['link1']));
if($_POST['link2']) $link2 = htmlspecialchars(trim($_POST['link2']));
if($_POST['is_alert']) $isAlert = $_POST['is_alert'];
if($_POST['is_timebomb']) $isTimeBomb = $_POST['is_timebomb'];
if($_POST['bombTime']) $bombTime = $_POST['bombTime'];
if($_POST['bombTerm']) $bombTerm = $_POST['bombTerm'];
if($_POST['tag']) $tag = htmlspecialchars(str_replace(' ', '', trim($_POST['tag'])));
if($_POST['deleteExtendPds']) $deleteExtendPds = $_POST['deleteExtendPds'];
if($_POST['option_reply_open']) $optionReplyOpen = $_POST['option_reply_open'];
if($_POST['option_reply_notify']) $optionReplyNotify = $_POST['option_reply_notify'];
if($_POST['clickCategory']) $clickCategory = $_POST['clickCategory']; // 카테고리 선택 후, 글쓰기 할때 자동선택 설정 @PicoZ, @이동규
$grboard = str_replace('/'.end(explode('/', $_SERVER['REQUEST_URI'])), '', $_SERVER['REQUEST_URI']);

// 게시판 설정 가져오기 @sirini
$tmpFetchBoard = $GR->getArray("select * from {$dbFIX}board_list where id = '$id'");

// 테마(스킨)에 있는 글쓰기 처리부터 인클루드 @sirini
@include 'theme/'.$tmpFetchBoard['theme'].'/theme_write_ok.php';
if(!$addExtendFieldQuery) $addExtendFieldQuery = '';

// 글쓴이 권한값 가져오기 @sirini
if($_SESSION['no']) {
	$sessionNo = $_SESSION['no'];
	$getMemberInfo = $GR->query("select id, nickname, password, email, homepage, level from {$dbFIX}member_list where no = '$sessionNo'");
	$tmpFetch = $GR->fetch($getMemberInfo) or $GR->error('기본정보를 가져와서 처리하는 도중 문제가 발생했습니다.');
	$writerLevel = $tmpFetch['level'];
	if($_SESSION['no'] == 1) $isAdmin = 1; else $isAdmin = 0;
	$isMember = 1;
	$isMaster = false;
	$isGroupAdmin = false;
	$getMasters = $GR->getArray('select master, group_no from '.$dbFIX.'board_list where id = \''.$id.'\'');

	// 게시판 관리자 @sirini
	if($getMasters[0]) {
		$masterArr = explode('|', $getMasters[0]);
		$masterNum = count($masterArr);
		for($m=0; $m<$masterNum; $m++) {
			if($_SESSION['mId'] && ($_SESSION['mId'] == $masterArr[$m])) {
				$isAdmin = 1;
				$isMaster = 1;
				break;
			}
		}
	}

	// 그룹 관리자 @sirini
	if($getMasters[1]) {
		$getGroupMaster = $GR->getArray('select master from '.$dbFIX.'group_list where no = '.$getMasters[1]);
		$groupMaster = explode('|', $getGroupMaster[0]);
		$cntResult = count($groupMaster);
		for($g=0; $g<$cntResult; $g++) {
			if($_SESSION['mId'] && ($_SESSION['mId'] == $groupMaster[$g])) {
				$isAdmin = 1;
				$isGroupAdmin = 1;
				break;
			}
		}
	}

	$name = $tmpFetch['nickname'];
	$password = $tmpFetch['password'];
	$email = $tmpFetch['email'];
	$homepage = $tmpFetch['homepage'];
}
// 비회원 글쓰기시 처리 @sirini
else {
	$sessionNo = 0;
	$tPass = $GR->getArray("select password('$password')");
	$password = $tPass[0];
	$writerLevel = 1;
	$isAdmin = 0;
	$isMember = 0;
	$isMaster = 0;
	
	if(!$name) $GR->error('이름을 입력해 주세요', 0, 'HISTORY_BACK');
	if(!$password) $GR->error('비밀번호를 입력해 주세요', 0, 'HISTORY_BACK');
	if(!$subject) $GR->error('제목을 입력해 주세요', 0, 'HISTORY_BACK');
	if(!$content) $GR->error('내용을 입력해 주세요', 0, 'HISTORY_BACK');
	if(!$_SESSION['antiSpam'] || !$_POST['antispam'] || $_SESSION['antiSpam'] != $_POST['antispam']) {
		$GR->error('자동입력방지 답이 올바르지 않습니다', 0, 'HISTORY_BACK');
	}
}

// 본문 변형 처리
//<p></p>는 지우고 <p style></p>는 남기기 위한 코드 --- <p style...> </p>를 <x style...> </x>로 바꿈 @pico
$returned[0] = $content; $returned[1] = 0; //변수초기화
while(true){
	$returned = str_replace_once($returned[0], "<p style=", "<x style=", $returned[1]);
	if($returned[1] === false) break; //찾는 내용이 없으면 루프 탈출
	//<p style>이 있으면 </p>도 같이 치환
	$returned = str_replace_once($returned[0], "</p>", "</x>", $returned[1]);
}
$content = $returned[0];
//---<p style...> 끝
$content = str_replace(array('<img id="player-box-', '<img id=\"player-box-'), '<div id="player-layout"></div><img title="플레이어" id="player-box-', $content);
$content = str_replace('<p>', '', str_replace('</p>', '', str_replace('<p>&nbsp;</p>', '', $content)));
$content = preg_replace('/<p(.*?)>/i', '', $content);
$content = str_replace(array("<x style=", "</x>"), array("<p style=", "</p>"), $content); //x를 p로 바꿈
$content = str_replace(array("<ul>\r\n", "<ol>\r\n", "</ul>\r\n", "</ol>\r\n", "</li>\r\n", "<br />\r\n"), array('<ul>', '<ol>', '</ul>', '</ol>', '</li>', '<br />'), $content);
$content = str_replace(array("<ul>\n", "<ol>\n", "</ul>\n", "</ol>\n", "</li>\n", "<br />\n"), array('<ul>', '<ol>', '</ul>', '</ol>', '</li>', '<br />'), $content);
$content = str_replace('src=\"http://'.$_SERVER['HTTP_HOST'].$grboard.'/', 'src=\"', $content);
$content = str_replace('src="http://'.$_SERVER['HTTP_HOST'].$grboard.'/', 'src="', $content);

// 변수 처리 2 - 관리자, 마스터 / 멤버, 비회원 @sirini
if($isAdmin || $isMaster) {
	$subject = trim($subject);
	$content = trim($content);
	$content = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $content);
} else {
	$allowTags = $GR->getArray('select is_html from '.$dbFIX.'board_list where id = \''.$id.'\'');
	$subject = $GR->escape(trim(htmlspecialchars($GR->unescape($subject))));
	$content = trim($GR->unescape($content));
	$content = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $content);
	$content = $GR->escape(strip_tags2($content, $allowTags['is_html'])); //윗윗줄에서 unescape했으므로 다시 escape.
	$filterText = @file_get_contents('filter.txt');
	$filterArray = explode(',', $filterText);
	$filterNum = count($filterArray);
	for($tf=0; $tf<$filterNum; $tf++) {
		if(preg_match('|'.$filterArray[$tf].'|i', $subject)) $GR->error('글제목에 필터링 대상 단어가 있습니다 : '.$filterArray[$tf], 1, 'HISTORY_BACK');
		if(preg_match('|'.$filterArray[$tf].'|i', $content)) $GR->error('글내용에 필터링 대상 단어가 있습니다 : '.$filterArray[$tf], 1, 'HISTORY_BACK');
	}
	  // 영어로만 입력된글 차단
	  if($tmpFetchBoard['is_english'] > 0) {
      if(!preg_match('/[\x{1100}-\x{11ff}\x{3130}-\x{318f}\x{ac00}-\x{d7af}]+/u', $content)) $GR->error('스팸성 게시물로 의심되어 차단되었습니다.', 1, 'HISTORY_BACK');
	  }
  }

// 파일 저장하는 경로 생성
$saveFileDir = 'data/'.$id;
$saveDirY = date('Y');
$saveDirM = date('m');
$saveDirD = date('d');
if(!is_dir($saveFileDir)) {
	@mkdir($saveFileDir, 0705);
	@chmod($saveFileDir, 0707);
}
if(!is_dir($saveFileDir . '/' . $saveDirY)) {
	@mkdir($saveFileDir . '/' . $saveDirY, 0705);
	@chmod($saveFileDir . '/' . $saveDirY, 0707);
}
if(!is_dir($saveFileDir . '/' . $saveDirY . '/' . $saveDirM)) {
	@mkdir($saveFileDir . '/' . $saveDirY . '/' . $saveDirM, 0705);
	@chmod($saveFileDir . '/' . $saveDirY . '/' . $saveDirM, 0707);
}
if(!is_dir($saveFileDir . '/' . $saveDirY . '/' . $saveDirM . '/' . $saveDirD)) {
	@mkdir($saveFileDir . '/' . $saveDirY . '/' . $saveDirM . '/' . $saveDirD, 0705);
	@chmod($saveFileDir . '/' . $saveDirY . '/' . $saveDirM . '/' . $saveDirD, 0707);
}
$saveFileDir = $saveFileDir . '/' . $saveDirY . '/' . $saveDirM . '/' . $saveDirD;

// 현재 게시판의 접근권한을 확인한다. @sirini
$isWriteOk = $GR->getArray("select write_level from {$dbFIX}board_list where id = '$id'");
if(!$isAdmin && !$isMaster && ($writerLevel < $isWriteOk['write_level'])) $GR->error('글쓰기 권한이 없습니다.', 0, 'HISTORY_BACK');

// 파일 업로드 처리 (추가 업로드 포함) @sirini
$fCount = 0;
$feCnt = 0;
$saveFile = array();
$saveExtendFile = array();
$fnameSave = array();
$fnameExtend = array();
$fnameTemp = '';
$isImageFile = false;

// 파일 첨부하기
foreach($_FILES as $fKey => $fValue) {
	$filename = strtolower($fValue['name']);
	$filetype = $fValue['type'];
	$filesize = $fValue['size'];
	$filetmpname = $fValue['tmp_name'];
	if(strpos('fileExtend', $fKey) === true) $isExtendFile = true; 
	else $isExtendFile = false;
	if($filesize > 0) {
		if(!is_uploaded_file($filetmpname)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'HISTORY_BACK');
		if(preg_match('/\.(inc|phtm|htm|shtm|ztx|php|dot|asp|cgi|pl|js|sql|sh|py|htaccess|jsp)$/i', $filename)) {
			$GR->error('HTML, Server side script 관련 파일은 업로드 하실 수 없습니다.', 1, 'HISTORY_BACK');
		}
		$filetmpname = str_replace('\\\\', '\\', $filetmpname);
		$filename = str_replace(' ', '_', $filename);
		$fnameTemp = $filename;
		if(!preg_match('/\.(jpg|jpeg|bmp|gif|png)$/i', $filename)) {
			$filename = md5($GR->grTime().'GRBOARD'.$filename);
			$isImageFile = false;
		} else {
			$ext = end(explode('.', $filename));
			$filename = md5($filename).'.'.$ext;
			$isImageFile = true;
		}
		if(file_exists($saveFileDir.'/'.$filename)) $savePos = $saveFileDir.'/'.substr(md5($GR->grTime()), -3).'_'.$filename;
		else $savePos = $saveFileDir.'/'.$filename;
		if($isExtendFile) {
			$saveExtendFile[$feCnt] = $savePos;
			if(!$isImageFile) $fnameExtend[$feCnt] = $saveFileDir.'/'.$fnameTemp;
		} else {
			$saveFile[$fCount] = $savePos;
			if(!$isImageFile) $fnameSave[$fCount] = $saveFileDir.'/'.$fnameTemp;
		}
		if(!move_uploaded_file($filetmpname, $savePos)) $GR->error('파일을 업로드 하지 못했습니다. 파일용량을 확인해 보세요.', 0, 'HISTORY_BACK');
		$isUploadEnd = 1;	
		if($isExtendFile) $feCnt++; else $fCount++;

	}
}

// 통합 태그 처리 @sirini
if($tag) {
	$arrTags = @explode(',', $tag);
	$arrTagNum = @count($arrTags);
	for($ti=0; $ti<$arrTagNum; $ti++) {
		if(!$arrTags[$ti]) continue;
		$isExistTag = $GR->getArray('select no from '.$dbFIX.'tag_list where id = \''.$id.'\' and tag = \''.$arrTags[$ti].'\' limit 1');
		if($isExistTag['no']) $GR->query('update '.$dbFIX.'tag_list set count = count + 1 where no = '.$isExistTag['no']);
		else $GR->query('insert into '.$dbFIX."tag_list set no = '', id = '$id', tag = '".$arrTags[$ti]."', count = 0");
	}
}

// 수정글일 경우 먼저 처리 @sirini
if(isset($mode) && $articleNo) {
	for($df=1; $df<11; $df++) {
		if(array_key_exists('delete'.$df, $_POST) && $_POST['delete'.$df]) { 
			@unlink($_POST['delete'.$df]); 
			$GR->query("update {$dbFIX}pds_save set file_route{$df} = '' where id = '$id' and article_num = '$articleNo'"); 
			$getPdsSave = $GR->getArray('select no from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = '.$articleNo);
			$GR->query('delete from '.$dbFIX.'pds_list where type = 0 and uid = '.$getPdsSave['no'].' and idx = '.($df-1).' limit 1');
		}
	}

	if(!$isAdmin && !$isMaster) {
		$getOldPassword = $GR->query("select member_key, password from {$dbFIX}bbs_{$id} where no = '$articleNo'") or 
			$GR->error("기존 게시물의 암호정보를 가져오는데 실패했습니다.", 0, 'HISTORY_BACK');
		$oldPassword = $GR->fetch($getOldPassword);
		if(($oldPassword['member_key'] != $_SESSION['no']) && ($oldPassword['password'] != $password)) $GR->error('비밀번호가 맞지 않습니다.', 0, 'HISTORY_BACK');
	}

	$old = $GR->getArray("select member_key, name, email, homepage, ip, signdate, bad from {$dbFIX}bbs_{$id} where no = '$articleNo'");

	// 글 수정시 이름, 홈페이지, 이메일 수정되게 하기 @좋아
	if(!$isMember || ($sessionNo == $old['member_key'])){
		$oldName = $name;
		$oldEmail = $email;
		$oldHomepage = $homepage;
		$oldIp = $ip;
	} else {
		$oldName = $old['name'];
		$oldEmail = $old['email'];
		$oldHomepage = $old['homepage'];
		$oldIp = $old['ip'];
	}
	$oldSigndate = $old['signdate'];
	if($old['bad'] > -1000) $bad = ($isAlert) ? -99 : 0; else $bad = -1001;
	if($tmpFetchBoard['is_history'] > 0) $content .= '<br /><span class="modifyTime">modified at '.date('Y.m.d H:i:s', $GR->grTime()).' by '.(($isAdmin)?'moderator':$name).'</span>';;
	$sqlUpdateQue = "update {$dbFIX}bbs_{$id}
		set name = '$oldName',
		email = '$oldEmail',
		homepage = '$oldHomepage',
		ip = '$oldIp',
		signdate = '$oldSigndate',
		bad = '$bad',
		is_notice = '$isNotice',
		is_secret = '$isSecret',
		is_grcode = '$isGrcode',
		category = '$category',
		subject = '$subject',
		content = '$content',
		link1 = '$link1',
		link2 = '$link2',
		tag = '$tag'
		$addExtendFieldQuery
		where no = '$articleNo'";
	$GR->query($sqlUpdateQue);
	$getArticleOption = $GR->getArray("select no from {$dbFIX}article_option where id = '$id' and article_num = '$articleNo'");
	if(!$getArticleOption) $GR->query("insert into {$dbFIX}article_option set no = '', article_num = '$articleNo', id = '$id', reply_open = '$optionReplyOpen', reply_notify = '$optionReplyNotify'");
	else $GR->query("update {$dbFIX}article_option set reply_open = '$optionReplyOpen', reply_notify = '$optionReplyNotify' where id = '$id' and article_num = '$articleNo'");
	$isAlreadyFiles = $GR->getArray("select * from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");
	if($isAlreadyFiles['no']) $isUploaded = 1; else $isUploaded = 0;
	if($isUploadEnd) {

		// 업로드 수정글일 때 @sirini
		if($isUploaded) {
			$sqlUploadUpdate = 'update '.$dbFIX.'pds_save set ';
			$loopUp = 1;
			for($t=1; $t<10; $t++) {
				if(!$_FILES['file'.$t]['name']) $loopUp++;
				else break;
			}
			for($tmp=0; $tmp<10; $tmp++) {
				if($saveFile[$tmp]) $sqlUploadUpdate .= 'file_route'.($tmp+$loopUp)." = '".$saveFile[$tmp]."',";
			}
			$sqlUploadUpdate = substr($sqlUploadUpdate, 0, -1);
			$sqlUploadUpdate .= " where id = '$id' and article_num = '$articleNo'";
			$GR->query($sqlUploadUpdate);

			$getPdsSave = $GR->getArray('select no from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = '.$articleNo);
			for($m=0; $m<10; $m++) {
				if($fnameSave[$m]) {
					$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 0, uid = '.$getPdsSave['no'].', idx = '.($m+$loopUp-1).', name = \''.$fnameSave[$m].'\'');
				}
			}

		// 신규 업로드일 때 @sirini
		} else {
			$sqlUploadInsert = "insert into {$dbFIX}pds_save
				set no = '', id = '$id', article_num = '$articleNo',
					file_route1 = '$saveFile[0]',
					file_route2 = '$saveFile[1]',
					file_route3 = '$saveFile[2]',
					file_route4 = '$saveFile[3]',
					file_route5 = '$saveFile[4]',
					file_route6 = '$saveFile[5]',
					file_route7 = '$saveFile[6]',
					file_route8 = '$saveFile[7]',
					file_route9 = '$saveFile[8]',
					file_route10 = '$saveFile[9]', hit = '0'";
			$GR->query($sqlUploadInsert);
			
			$upInsertID = $GR->getInsertId();
			for($i=0; $i<10; $i++) {
				if($fnameSave[$i]) {
					$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 0, uid = '.$upInsertID.', idx = '.$i.', name = \''.$fnameSave[$i].'\'');
				}
			}
		}
	}
}
// 신규 게시물일 경우 처리 @sirini
else {
	$getLastArticle = $GR->query("select ip, signdate from {$dbFIX}bbs_{$id} order by no desc limit 1");
	$lastArticle = $GR->fetch($getLastArticle);
	if($lastArticle['ip'] && !$isAdmin && !$isMaster && ($GR->grTime()-30 < $lastArticle['signdate']) && ($ip == $lastArticle['ip'])) 
		$GR->error('너무 빠른 시간에 게시물을 연속해서 올리실 수 없습니다.', 1, 'HISTORY_BACK'); 
	$thisTime = $GR->grTime();
	$bad = ($isAlert) ? -99 : 0;
	$sqlInsertQue = "insert into {$dbFIX}bbs_{$id}
		set no = '',
			member_key = '$sessionNo',
			name = '$name',
			password = '$password',
			email = '$email',
			homepage = '$homepage',
			ip = '$ip',
			signdate = '$thisTime',
			hit = '0',
			good = '0',
			bad = '$bad',
			comment_count = '0',
			is_notice = '$isNotice',
			is_secret = '$isSecret',
			is_grcode = '$isGrcode',
			category = '$category',
			subject = '$subject',
			content = '$content',
			link1 = '$link1',
			link2 = '$link2',
			tag = '$tag'
			$addExtendFieldQuery";
	$GR->query($sqlInsertQue);
	$insertNo = $GR->getInsertId();
	if($optionReplyOpen || $optionReplyNotify) {
		$GR->query("insert into {$dbFIX}article_option set no = '', article_num = '$insertNo', id = '$id', reply_open = '$optionReplyOpen', reply_notify = '$optionReplyNotify'");
	}
	if($insertNo) {
		$sqlTotalQue = "insert into {$dbFIX}total_article
		set no = '',
			subject = '$subject',
			id = '$id',
			article_num = '$insertNo',
			signdate = '$thisTime',
			is_secret = '$isSecret'";
		$GR->query($sqlTotalQue);	
	}

	if($isUploadEnd && $insertNo) {
		$sqlUploadInsert = "insert into {$dbFIX}pds_save
			set no = '',
				id = '$id',
				article_num = '$insertNo',
				file_route1 = '$saveFile[0]',
				file_route2 = '$saveFile[1]',
				file_route3 = '$saveFile[2]',
				file_route4 = '$saveFile[3]',
				file_route5 = '$saveFile[4]',
				file_route6 = '$saveFile[5]',
				file_route7 = '$saveFile[6]',
				file_route8 = '$saveFile[7]',
				file_route9 = '$saveFile[8]',
				file_route10 = '$saveFile[9]',
				hit = '0'";
		$GR->query($sqlUploadInsert);

		$upInsertID = $GR->getInsertId();
		for($i=0; $i<10; $i++) {
			if($fnameSave[$i]) {
				$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 0, uid = '.$upInsertID.', idx = '.$i.', name = \''.$fnameSave[$i].'\'');
			}
		}
	}

	if($isMember) {
		$GR->query("update {$dbFIX}member_list set point = point + 2 where no = '$sessionNo'");
	}

}

// 삭제 체크된 것들 먼저 처리 @sirini
if(is_array($deleteExtendPds)) {
	$delExtCnt = @count($deleteExtendPds);
	for($de=0; $de<$delExtCnt; $de++) {
		$getDeleteTarget = $GR->getArray('select file_route from '.$dbFIX.'pds_extend where no = '.$deleteExtendPds[$de]);
		$GR->query('delete from '.$dbFIX.'pds_extend where no = '.$deleteExtendPds[$de].' limit 1');
		@unlink($getDeleteTarget['file_route']);
		$GR->query('delete from '.$dbFIX.'pds_list where type = 1 and uid = '.$deleteExtendPds[$de].' limit 1');
	}
}

// 추가 업로드된 파일 DB저장 @sirini
if($isExtendFile) {
	$feCnt++;
	$setArticleNum = ($articleNo) ? $articleNo : $insertNo;
	for($eu=0; $eu<$feCnt; $eu++) {
		if($saveExtendFile[$eu]) {
			$GR->query('insert into '.$dbFIX."pds_extend set no = '', id = '$id', article_num = '$setArticleNum', file_route = '".$saveExtendFile[$eu]."'");
			$getExtendInsertID = $GR->getInsertId();
			if($fnameExtend[$eu]) {
				$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 1, uid = '.$getExtendInsertID.', idx = 0, name = \''.$fnameExtend[$eu].'\'');
			}
		}
	}
}

// 멀티업로드 (swfupload) 파일 발견시 처리 @sirini
$tmp = 'data/tmpfile.'.$ip;
$isImageFile = false;
if(file_exists($tmp)) {
	$multi = @file_get_contents($tmp);
	$listArr = @explode("\n", $multi);
	$listCnt = @count($listArr)-1;
	$setArticleNum = ($articleNo) ? $articleNo : $insertNo;
	for($m=0; $m<$listCnt; $m++) {
		$lsArr = @explode('__GRBOARD__', $listArr[$m]);
		if(!preg_match('/\.(jpg|jpeg|bmp|gif|png)$/i', $lsArr[1])) {
			$swFilename = end(explode('/', $lsArr[0]));
			$newFilename = 'data/'.$id.'/'.md5($GR->grTime().'GRBOARD'.$swFilename);
			@rename($lsArr[0], $newFilename);
			$isImageFile = false;
		} else {
			$newFilename = $lsArr[0];
			$isImageFile = true;
		}
		$GR->query('insert into '.$dbFIX."pds_extend set no = '', id = '$id', article_num = '$setArticleNum', file_route = '".$newFilename."'");
		$getExistInsertID = $GR->getInsertId();
		if(!$isImageFile) $GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 1, uid = '.$getExistInsertID.', idx = 0, name = \''.$lsArr[1].'\'');
	}
	@unlink($tmp);
}

// 자동폭파 사용시 세팅 @sirini
if($tmpFetchBoard['is_bomb'] && $isTimeBomb) {
	$bombSetTime = $GR->grTime() + ($bombTime * $bombTerm);
	$GR->query("insert into {$dbFIX}time_bomb set no = '', id = '$id', article_num = '".(($insertNo)?$insertNo:$articleNo)."', set_time = '$bombSetTime'");
}

// 신고된 게시물을 관리자가 수정했다면 @sirini
$isReported = $_POST['isReported'];
if ($isReported && !(int)$isReported) exit; // XSS 방지
if($isReported && ($_SESSION['no'] == 1)) {
	$GR->query("update {$dbFIX}report set status = 1 where no = ".$isReported);	
	$GR->error('신고된 게시물을 수정하였습니다.', 0, 'CLOSE');
}

// 글쓰기 하단 스킨 인클루드. @sirini
@include 'theme/'.$tmpFetchBoard['theme'].'/theme_write_ok_foot.php';

// 글쓰기를 완료하고 나서 목록보기로 페이지 이동
// 카테고리 선택 후, 자동선택 설정 @PicoZ, @이동규
if($mode) $GR->move('board.php?id='.$id.'&articleNo='.$articleNo.'&page='.$page.'&clickCategory='.$clickCategory);
else $GR->move('board.php?id='.$id.'&articleNo='.$insertNo.'&page='.$page.'&clickCategory='.$clickCategory);
?>
