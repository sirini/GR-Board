<?php
// PHP설정값, 헤더, 세션 설정
$_GET['preRoute'] = $_POST['preRoute'] = $_REQUEST['preRoute'] = false;
if(!$headerGiven){ //별도 지정된 헤더가 없으면 기본헤더
	@header('Pragma: no-cache');
	@header('Content-Type: text/html; charset=utf-8');
}
elseif($headerGiven == 'N'){ //헤더를 그 파일에서 자체적으로 뿌리고자 하는 경우
	//Do Nothing
}
else { //별도 지정된 헤더가 있으면 그것 뿌림. 예: search_helper.php
	@header($headerGiven);
}
@session_save_path($preRoute.'session');
@session_start();
define('__GRBOARD__', true);

//---SQL injection 방지 보안 코드 시작
//참고 : http://kldp.org/node/90787
//참고 : drupal 소스코드 ( common.inc )
//참고 : 클래스 내부에 정의되어 있으면 PHP 버젼에 따라 찾지 못하는 문제 있어서 밖으로 빼둠 @sirini
function _fix_gpc_magic(&$item) {
	global $preRoute;
	require $preRoute.'db_info.php';

	if (is_array($item)) {
		array_walk($item, '_fix_gpc_magic');
	}
	else {
		if(ini_get('magic_quotes_gpc')){ //매직 ON이면 strip+escape
			$item = mysql_real_escape_string(stripslashes($item));
		}
		else{ //매직 OFF이면 strip안 하고 escape만
			$item = mysql_real_escape_string($item);
		}
	}
}
function _fix_gpc_magic_files(&$item, $key) {
	global $preRoute;
	require $preRoute.'db_info.php';

	if ($key != 'tmp_name') {
		if (is_array($item)) {
			array_walk($item, '_fix_gpc_magic_files');
		}
		else {
			if(ini_get('magic_quotes_gpc')){ //매직 ON이면 strip+escape
				$item = mysql_real_escape_string(stripslashes($item)); //!!!확인필요 - 파일업로드 시험 검토 필요!!!
			}
			else{ //매직 OFF이면 strip안 하고 escape만
				$item = mysql_real_escape_string($item); //!!!확인필요 - 파일업로드 시험 검토 필요!!!
			}
		}
	}
}
//---SQL injection 방지 보안 코드 끝

// GR보드 공통 클래스 @sirini
class COMMON {
	var $grTime;
	var $hostName;
	var $dbName;

	// 프로그램 정보 @sirini
	function grInfo($str='all') {
		if( $str == 'all' ) $info = '봉고 (v1.9.3 BETA)';
		elseif( $str == 'version' ) $info = '1.9.3';
		elseif( $str == 'status' ) $info = 'BETA';
		return $info;
	}

	// DB 접속 함수 @sirini
	function dbConn() {
		global $preRoute;
		require $preRoute.'db_info.php';
		$GLOBALS['dbFIX'] = $dbFIX;
		$this->hostName = $hostName;
		$this->dbName = $dbName;
		$this->grTime = $timeDiff;
		$this->fix_gpc_magic(); //gpc_magic_quotes를 무효화하는 함수 호출
	}

	// DB 조작 관련 래핑 메소드 모음 (DB에러 출력안함)
	function query($sql) { return @mysql_query($sql); }
	function fetch($que) { return @mysql_fetch_array($que); }
	function assoc($que) { return @mysql_fetch_assoc($que); }
	function escape($str) { return @mysql_real_escape_string($str); }
	function unescape($str) { return current(@mysql_fetch_row(mysql_query("select '{$str}' as text"))); }
	function getArray($sql) { return @mysql_fetch_array(mysql_query($sql)); }
	function getInsertId() { return @mysql_insert_id(); }
	function getNumRows($que) { return @mysql_num_rows($que); }
	function getNumFields($que) { return @mysql_num_fields($que); }
	function getFetchFields($que, $i=0) { return @mysql_fetch_fields($que, $i); }

	// 에러 처리 함수 @sirini
	function error($errorMsg, $saveError=0, $goRoute=0) {
		global $id, $dbFIX;
		$errorMsg = str_replace(array('script', '/script'), '', $errorMsg);
		$errorMsgPage = urlencode($errorMsg);
		$errorMsgDb = $this->escape($errorMsg); //GPC 값 아님 : 없애면 안 됨
		$output = '<script> ';
		if($goRoute) {
			$errorMsg = str_replace('<br />', '\\n', $errorMsg);
			if($goRoute == 'HISTORY_BACK') $output .= 'alert(\''.$errorMsg.'\'); history.back();';
			elseif($goRoute == 'CLOSE') $output .= 'alert(\''.$errorMsg.'\'); window.close();';
			else $output .= 'alert(\''.$errorMsg.'\'); location.href=\''.$goRoute.'\';';
		} else {
			$prevPage = urlencode($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
			$output .= "location.href='error.php?id={$id}&error={$errorMsgPage}&prevPage={$prevPage}';";
			$goRoute = 'error.php?id='.$id.'&error='.$errorMsgPage.'&prevPage='.$prevPage;
		}
		$output .= " </script><noscript><h1>Error :: 오류가 발생했습니다.</h1><p><strong>$errorMsg</strong></p><p><a href=\"".htmlspecialchars($goRoute, ENT_COMPAT, 'UTF-8')."\">[click to move 클릭 시 이동합니다]</a></p></noscript>";
		if($saveError) {
			$this->dbConn();
			$errorTime = $this->grTime();
			$errorMsgDb = '<span class="smallEng">['.$_SERVER['REMOTE_ADDR'].']</span> '.$errorMsgDb;
			$this->query("insert into {$dbFIX}error_save set no = '', error_msg = '$errorMsgDb', msg_time = '$errorTime'");
		}
		include 'empty_head.php';
		echo $output;
		include 'empty_foot.php';
		die();
	}

	// 페이징 처리 함수 (그누보드 4 참고 : sir.co.kr) @sirini
	function getPaging($writePages, $currentPage, $totalPage, $goUrl, $division=0, 
		$originDivision=0, $searchOption='', $searchText='', $category='', $focusID='',
		$nameLatestRange='◀ 최신 범위', $nameOldRange='과거 범위 ▶', $nameFirstPage='처음', $namePrevPage='이전', $nameNextPage='다음',
		$nameLastPage='마지막') {
		$str = '';
		if($searchOption && $searchText) $addSearchQue = '&amp;searchOption='.$searchOption.'&amp;searchText='.urlencode($searchText);
		if($category) $addSearchQue .= '&amp;clickCategory='.urlencode($category);
		$addSearchQue .= $focusID;
		if($originDivision > $division) $str .= '<a href="'.$goUrl.'1&amp;division='.($division + 1).$addSearchQue.'" class="page">'.$nameLatestRange.'</a> &nbsp;'; 
		if($currentPage > 1) $str .= '<a href="'.$goUrl.'1'.$addSearchQue.'" class="page">'.$nameFirstPage.'</a>';
		$startPage = (((int)(($currentPage - 1 ) / $writePages )) * $writePages) + 1;
		$endPage = $startPage + $writePages - 1;
		if($endPage >= $totalPage) $endPage = $totalPage;
		if($currentPage - 1 > 0) $str .= ' &nbsp;<a href="'.$goUrl.($currentPage - 1).'&amp;division='.$division.$addSearchQue.'" class="page">'.$namePrevPage.'</a>';
		if($totalPage > 1) 	{
			for($i=$startPage;$i<=$endPage;$i++) {
				if($currentPage != $i) $str .= ' &nbsp;<a href="'.$goUrl.$i.'&amp;division='.$division.$addSearchQue.'" class="page">'.$i.'</a>';
				else $str .= ' &nbsp;<strong>'.$i.'</strong> ';
			}
		}
		if($currentPage + 1 <= $totalPage) $str .= ' &nbsp;<a href="'.$goUrl.($currentPage + 1).'&amp;division='.$division.$addSearchQue.'"  class="page">'.$nameNextPage.'</a>';
		if ($currentPage < $totalPage)	 $str .= ' &nbsp;<a href="'.$goUrl.$totalPage.'&amp;division='.$division.$addSearchQue.'"  class="page">'.$nameLastPage.'</a>';		
		if($division) $str .= ' &nbsp;<a href="'.$goUrl.'1&amp;division='.($division - 1).$addSearchQue.'" class="page">'.$nameOldRange.'</a>';
		return $str;
	}

	// 문자열 자르기 @PiconZ, @이동규
	function cutString($str, $size=0) {
		if(!$size) return $str;
		$mb_cutSize = 0;
		$j = $size;
		for($i=0; ($j > 0) && ($i <= mb_strlen($str, 'UTF-8')); $i++){
			if( ord( mb_substr($str, $i, 1, 'UTF-8') ) > 127) {
				$j -= 1; $mb_cutSize += 1;
			}
			else {
				$j -= 0.5; $mb_cutSize += 1;
			}
		}
		if($j < 0 ) $mb_cutSize -= 1;
		$result = substr($str, 0, $mb_cutSize);
		preg_match('/^([\x00-\x7e]|.{3})*/', $result, $string);
		return $string[0];
	}

	// 페이지 이동 함수 @sirini
	function move($src) {
		include 'empty_head.php';
		echo '<script> location.href=\''.$src.'\'; </script>';
		echo '<noscript><p>페이지 이동이 되지 않으시다면 <a href="'.$src.'">여기를 눌러주세요.</a></p></noscript>';
		include 'empty_foot.php';
		exit();
	}

	// GR보드 기준시간 @sirini
	function grTime() {
		return (time()+$this->grTime);
	}
	
	// 클래스 밖에 정의해둔 보안용 함수들 실제 호출 @sirini
	function fix_gpc_magic() {
	  static $fixed = FALSE;
	  if(!$fixed) {
			array_walk($_GET, '_fix_gpc_magic');
			array_walk($_POST, '_fix_gpc_magic');
			array_walk($_COOKIE, '_fix_gpc_magic');
			array_walk($_REQUEST, '_fix_gpc_magic');
			array_walk($_FILES, '_fix_gpc_magic_files');
			
			if(ini_get('register_globals')) { //global 변수가 있으면 해제
				foreach($_GET as $key => $value) { unset($$key); }
				foreach($_POST as $key => $value) { unset($$key); }
				foreach($_COOKIE as $key => $value) { unset($$key); }
				foreach($_REQUEST as $key => $value) { unset($$key); }
				foreach($_FILES as $key => $value) { unset($$key); }
			}
		}
		$fixed = TRUE;
	}
	
	// MySQL 41바이트 암호화를 php로 구현 php.net
	function password($password,$option=''){
    	if($password===null)return null;
    	if(strlen($password)==0)return '';
    	$r=sha1(sha1($password,true));
    	if($option) return $r; else return '*'.strtoupper($r);
	}
	
	// MySQL 16바이트 암호화를 php로 구현 phpschool.com
	function old_password($password) {
    	$nr = 1345345333;
    	$add = 7;
    	$nr2 = 0x12345671;
    	$size = strlen($password);
    	for($i=0;$i<$size;$i++) {
    		if($password[$i] == ' ' || $password[$i] == '\t') continue; /* skipp space in password */
    		$tmp = ord($password[$i]);
    		$nr ^= ((($nr & 63)+$add)*$tmp) + ($nr << 8);
    		$nr2 += ($nr2 << 8) ^ $nr;
    		$add += $tmp;
    		}
    	$result1=$nr & ((1 << 31) -1); /* Don't use sign bit (str2int) */
    	$result2=$nr2 & ((1 << 31) -1);
    	$result = sprintf("%08x%08x",$result1,$result2);
    	return $result;
    }
}
?>
