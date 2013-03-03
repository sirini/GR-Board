<?php
// swfupload 를 통한 업로드시 처리 @sirini
if (isset($_POST["PHPSESSID"])) session_id($_POST["PHPSESSID"]);

// 초기화 @sirini
$headerGiven = 'N';
include 'class/common.php';
$GR = new COMMON;

// 로그인 하지 않은 사용자는 사용 할 수 없다
if(!$_SESSION['no'])
{
	$GR->error('Permission Denied.');
}

// 에러메시지 출력 @sirini
function HandleError($message) {
	header("HTTP/1.1 500 Internal Server Error");
	die($message);
}

// 이전에 업로드하고 글작성을 완료하지 않았을 때, 버려진 파일 제거 @sirini
$tmp = 'data/tmpfile.'.$_SERVER['REMOTE_ADDR'];
if(file_exists($tmp)) {
	if(time() > 600+filemtime($tmp)) {
		$lostFiles = @file_get_contents($tmp);
		$listArr = @explode("\n", $lostFiles);
		$listNo = @count($listArr);
		for($t=0; $t<$listNo; $t++) {
			$lsArr = @explode('__GRBOARD__', $listArr[$t]);
			@unlink($lsArr[0]);
		}
		@unlink($tmp);
	}
}

// 파일 업로드 처리 (추가 업로드 포함) @sirini
$fCount = 0;
$feCnt = 0;
$saveFile = array();
$saveExtendFile = array();
$saveFileDir = 'data/'.$_POST['id'];
$saveResult = '';
while(list($fKey, $fValue) = each($_FILES)) {
	$filename = strtolower($fValue['name']);
	$filetype = $fValue['type'];
	$filesize = $fValue['size'];
	$filetmpname = $fValue['tmp_name'];

	if(preg_match('|fileExtend|i', $fKey)) $isExtendFile = true; else $isExtendFile = false;
	if($filesize > 0) {
		if(!is_dir($saveFileDir)) {
			@mkdir($saveFileDir, 0705);
			@chmod($saveFileDir, 0707);
		}
		if(!is_uploaded_file($filetmpname)) HandleError('업로드 실패. 파일 용량을 확인해 주세요.');
		if(preg_match('/\.(inc|phtm|htm|shtm|ztx|php|dot|asp|cgi|pl|js|sql|sh|py|htaccess|jsp)/i', $filename))
			HandleError('업로드 실패. HTML, Server side script 관련 파일은 업로드 하실 수 없습니다.');
		$filetmpname = str_replace('\\\\', '\\', $filetmpname);
		$filename = str_replace(' ', '_', $filename);
		$giveName = $filename;
		if(!preg_match('/\.(jpg|jpeg|bmp|gif|png|JPG|JPEG|BMP|GIF|PNG)$/i', $filename)) {
			$filename = md5($GR->grTime().'GRBOARD'.$filename);
		} else {
			$ext = end(explode('.', $filename));
			$filename = md5($filename).'.'.$ext;
			$giveName = $filename;
		}
		if(file_exists($saveFileDir.'/'.$filename)) {
			$filename = substr(md5($GR->grTime()), -5).'_'.$filename;
			$savePos = $saveFileDir.'/'.$filename;
		}
		else {
            $savePos = $saveFileDir.'/'.$filename;
        }
		if($isExtendFile) $saveExtendFile[$feCnt] = $saveFileDir.'/'.$giveName; else $saveFile[$fCount] = $saveFileDir.'/'.$giveName;
		if(!move_uploaded_file($filetmpname, $savePos)) HandleError('파일을 업로드 하지 못했습니다. 파일용량을 확인해 보세요.');
		$saveResult .= $savePos.'__GRBOARD__'.$saveFileDir.'/'.$giveName."\n";
		if($isExtendFile) $feCnt++; else $fCount++;
	}
}
if(time() > 600+@filemtime($tmp)) $tmpFS = @fopen($tmp, 'w'); else $tmpFS = @fopen($tmp, 'a');
@fwrite($tmpFS, $saveResult);
@fclose($tmpFS);
echo $filename;
?>