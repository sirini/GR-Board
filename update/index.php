<?php
// 기본 클래스를 부른다
$preRoute = '../';
require $preRoute.'class/common.php';
$GR = new COMMON;
$GR->dbConn();

if($_SESSION['no'] != 1) { 
	header("HTTP/1.0 404 Not Found"); 
	exit; 
}

if(!$dbFIX) {
	$saveDbInfo  = '<?php'."\n";
	$saveDbInfo .= '$hostName = \''.$hostName.'\';'."\n";
	$saveDbInfo .= '$userId = \''.$userId.'\';'."\n";
	$saveDbInfo .= '$password = \''.$password.'\';'."\n";
	$saveDbInfo .= '$dbName = \''.$dbName.'\';'."\n";
	$saveDbInfo .= '$dbFIX = \''.$dbFIX.'\';'."\n";
	$saveDbInfo .= '@mysql_connect($hostName, $userId, $password);'."\n";
	$saveDbInfo .= '@mysql_select_db($dbName);'."\n";
	$saveDbInfo .= '#@mysql_query(\'set names utf8\'); // 한글이 깨져보일 경우 이 줄 맨 앞에 # 을 제거'."\n";
	$saveDbInfo .= '#@mysql_query(\'set old_passwords=1\'); // 서버 이전 등으로 갑자기 로그인이 안될 때 맨 앞에 # 을 제거'."\n";
	$saveDbInfo .= '?>'."\n";
	@chmod('../db_info.php', 0707);
	@unlink('../db_info.php');
	$fileCreate = @fopen('../db_info.php', 'w') or 
		$GR->error('파일 쓰기 권한이 없습니다. 설치 디렉토리의 퍼미션(권한)을 확인하세요.<br />'.
		'설치폴더의 퍼미션은 707 을 권장합니다.', 0, 'install.php');
	@fwrite($fileCreate, $saveDbInfo);
	@fclose($fileCreate);
}

$que = array();
$que[] = "create table `{$dbFIX}member_group` ( no int(11) not null auto_increment, ".
	"name varchar(50) not null default '', make_time int(11) not null default '0', primary key(no))";
$que[] = "drop table `{$dbFIX}_member_group`";
$que[] = "alter table `{$dbFIX}member_list` add group_no tinyint(2) not null default '1'";
$que[] = "alter table `{$dbFIX}member_list` add icon varchar(255) not null default ''";
$que[] = "alter table `{$dbFIX}board_list` add fix_time tinyint(2) not null default '0'";
$que[] = "create table `{$dbFIX}layout_config` ( no int(11) not null auto_increment, opt varchar(50) not null default '', var text, ".
	"primary key(no), key(opt))";
$que[] = "alter table `{$dbFIX}poll_subject` add id varchar(50) not null default ''";
$que[] = "alter table `{$dbFIX}poll_option` add id varchar(50) not null default ''";
$que[] = "create table `{$dbFIX}report` ( no int(11) not null auto_increment, id varchar(100) not null default '', article_num int(11) not null default '0', ".
	"reporter int(11) not null default '0', reason varchar(255) not null default '', status tinyint(2) not null default '0', primary key(no), key(id), key(article_num), key(status))";
$que[] = "create table `{$dbFIX}auto_save` ( no int(11) not null auto_increment, member_key int(11) not null default '0', ".
	"subject varchar(255) not null default '', content text, signdate int(11) not null default '0', primary key(no), key(member_key))";

// after v1.7.8
$getOutlogin = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'outlogin_skin\''));
$getJoinus = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'join_skin\''));
$getMemo = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'memo_skin\''));
$getReport = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'report_skin\''));
$getInfoPage = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'info_skin\''));
if(!$getOutlogin['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'outlogin_skin', var = 'new_default'";
if(!$getJoinus['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'join_skin', var = 'default'";
if(!$getMemo['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'memo_skin', var = 'default'";
if(!$getReport['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'report_skin', var = 'default'";
if(!$getInfoPage['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'info_skin', var = 'new_default'";
$que[] = "create table `{$dbFIX}pds_extend` ( no int(11) not null auto_increment, id varchar(50) not null default '', article_num int(11) not null default '0', ".
	"file_route varchar(255) not null default '', primary key(no), key(id), key(article_num))";
$que[] = "create table `{$dbFIX}tag_list` ( no int(11) not null auto_increment, id varchar(50) not null default '', ".
	"tag varchar(50) not null default '', count int(11) not null default '0', primary key(no), key(id))";

// after v1.7.8 Plus Pack #2
$que[] = "create table `{$dbFIX}article_option` ( no int(11) not null auto_increment, article_num int(11) not null default '0', ".
	"id varchar(50) not null default '', reply_open tinyint(1) not null default '0', reply_notify tinyint(1) not null default '0', primary key(no), key(id))";
$getNotify = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'notify_skin\''));
if(!$getNotify['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'notify_skin', var = 'default'";
$que[] = "alter table `{$dbFIX}member_list` add lastlogin int(11) not null default '0'";
$que[] = "create table `{$dbFIX}login_log` ( no int(11) not null auto_increment, member_key int(11) not null default '0', ".
	"signdate int(11) not null default '0', primary key(no), key(member_key))";
$getInformation = @mysql_fetch_array(mysql_query('select var from '.$dbFIX.'layout_config where opt = \'info_skin\''));
if(!$getInformation['var']) $que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'info_skin', var = 'default'";

// after v1.8.5
$que[] = "alter table `{$dbFIX}board_list` add name varchar(100) not null default ''";
$que[] = "create table `{$dbFIX}pds_list` ( no int(11) not null auto_increment, type tinyint(1) not null default '0', ".
	"uid int(11) not null default '0', idx tinyint(2) not null default '0', ".
	"name varchar(255) not null default '', primary key(no), key(type), key(uid))";
$que[] = "alter table `{$dbFIX}login_log` add ip varchar(20) not null default ''";
$que[] = "alter table `{$dbFIX}login_log` add ref varchar(255) not null default ''";
$que[] = "alter table `{$dbFIX}board_list` add down_level tinyint(4) not null default '0'";
$que[] = "alter table `{$dbFIX}board_list` add down_point tinyint(4) not null default '0'";

// after v1.8.5 r2
$que[] = "alter table `{$dbFIX}memo_save` add index ( `sender_key` )";

// after v1.8.5 r4
$que[] = "alter table `{$dbFIX}board_list` add is_history tinyint(1) not null default '1'";
$que[] = "alter table `{$dbFIX}board_list` add is_english tinyint(1) not null default '1'";
$que[] = "alter table `{$dbFIX}member_list` add blocks int(11) not null default '0'";

// after v1.9.2
$que[] = "create table `{$dbFIX}notification` ( no int(11) not null auto_increment, to_key int(11) not null default '0', from_key int(11) not null default '0'," . 
	"act tinyint(2) not null default '0', bbs_id varchar(50) not null default '', bbs_no int(11) not null default '0', is_checked tinyint(1) not null default '0', " . 
	"primary key(no), key(to_key))";
$que[] = 'drop table '.$dbFIX.'trackback_save';
$que[] = 'drop table '.$dbFIX.'scrap_book';
$que[] = 'alter table '.$dbFIX.'board_list drop is_trackback';
$bbsList192 = @mysql_query('select id from '.$dbFIX.'board_list');
while($list192 = @mysql_fetch_array($bbsList192)) {
	$que[] = 'alter table '.$dbFIX.'bbs_'.$list192['id'].' drop trackback';
}

// after v1.9.3 R2
$que[] = "ALTER TABLE `{$dbFIX}member_list` CHANGE  `password`  `password` CHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

// run query
for($i=0; $i<count($que); $i++) @mysql_query($que[$i]);
$getBoardList = @mysql_query('select id from '.$dbFIX.'board_list');
while($bbslist = @mysql_fetch_array($getBoardList)) {
	@mysql_query("alter table `{$dbFIX}bbs_".$bbslist['id']."` add tag varchar(255) not null default ''");
	@mysql_query("alter table `{$dbFIX}comment_".$bbslist['id']."` add order_key varchar(50) not null default ''");
	
	// setup reply order key
	$getReplyKey = @mysql_fetch_array(mysql_query('select no from '.$dbFIX.'comment_'.$bbslist['id'].' where order_key != \'\''));
	if(!$getReplyKey['no']) {
		$getAllReply = @mysql_query('select no, thread from '.$dbFIX.'comment_'.$bbslist['id']);
		while($rep = @mysql_fetch_array($getAllReply)) {
			if($rep['thread']) {
				@mysql_query('update '.$dbFIX.'comment_'.$bbslist['id'].' set order_key = \''.str_repeat('A', $rep['thread']+2).'\' where no = '.$rep['no']);
			}
		}
	}
}

// setup tag list
$getOneTag = @mysql_fetch_array(mysql_query('select no from '.$dbFIX.'tag_list limit 1'));
if(!$getOneTag['no']) {
	$getBBSList = @mysql_query('select id from '.$dbFIX.'board_list');
	while($bbsList = @mysql_fetch_array($getBBSList)) {
		$getTags = @mysql_query('select tag from '.$dbFIX.'bbs_'.$bbsList['id']);
		while($tagList = @mysql_fetch_array($getTags)) {
			$arrTag = @explode(',', str_replace(' ', '', $tagList['tag']));
			for($at=0; $at<@count($arrTag); $at++) {
				if(!$arrTag[$at]) continue;
				$isExistTag = @mysql_fetch_array(mysql_query('select no from '.$dbFIX.'tag_list where id = \''.$bbsList['id'].'\' and tag = \''.$arrTag[$at].'\' limit 1'));
				if($isExistTag['no']) @mysql_query('update '.$dbFIX.'tag_list set count = count + 1 where no = '.$isExistTag['no']);
				else @mysql_query('insert into '.$dbFIX."tag_list set no = '', id = '".$bbsList['id']."', tag = '".$arrTag[$at]."', count = 0");
			}
		}
	}
}

@unlink('../.htaccess');
@unlink('../no.use.htaccess');
$grboard = str_replace('/update/index.php', '', $_SERVER['PHP_SELF']);
$str = <<<REWRITE
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {GRBOARD_PATH}
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ mod_rewrite.php/$1 [QSA]
RewriteRule ^$ - [L]
</IfModule>
REWRITE;
$fp = @fopen('../no.use.htaccess', 'w');
@fwrite($fp, str_replace('{GRBOARD_PATH}', $grboard, $str));
@fclose($fp);
@chmod('../no.use.htaccess', 0707);

if(!is_dir('../passwd'))
{
	@mkdir('../passwd', 0705);
	@chmod('../passwd', 0707);
}

// 문서설정
$title = 'GR Board Upgrade: v1.7.7 "바다표범 II" ▶▶▶ 알바트로스 (v1.9.2 BETA) 으로 업데이트 되었습니다.';
$preRoute = '..';
include '../html_head.php';
?>
<body>

<h2>GR Board 봉고 (v1.9.3 R2 BETA) 의 업데이트가 완료 되었습니다!</h2>

<strong>DB Table 업데이트 내역</strong>
<ul>
	<li><em>패스워드 저장 길이를 md5 해시길이에 맞게 수정하였습니다. (gr_member_list 의 password 컬럼)</em></li>
	<li>각 게시판마다 트랙백 주소를 받을 수 있는 컬럼 영역을 제거하였습니다. (gr_bbs_* 의 trackback 컬럼)</li>
	<li>게시판 설정에서 트랙백 받기 유무를 제거하였습니다. (gr_board_list 의 is_trackback 컬럼)</li>
	<li>트랙백을 별도로 보관하던 장소를 제거하였습니다. (gr_trackback_save)</li>
	<li>알림 내역들을 기록하는 테이블을 추가하였습니다. (gr_notification)</li>
  <li>일정 로그인 횟수이상 실패시, 로그인을 차단할 수 있습니다.</li>
  <li>영문으로만 작성된 글을 차단할 수 있습니다.</li>
  <li>글 수정시 나타나는, "moderator by" 표시여부를 설정할 수 있습니다.</li>
	<li>쪽지함의 sender_key 에도 인덱스를 걸었습니다.</li>
	<li>첨부파일 다운로드 권한 설정 및 파일 다운로드시 포인트 차감 기능 추가합니다.</li>
	<li>로그인 기록 작성시 접속 아이피와 리퍼러 정보를 추가합니다.</li>
	<li>개인 정보수정 페이지 DB 초기화가 빠진 부분 추가했습니다.</li>
	<li>게시판 이름을 추가할 수 있도록 했습니다. (gr_board_list 테이블 name 컬럼 생성)</li>
	<li>개인정보 확인 페이지를 스킨화 했습니다.</li>
	<li>보다 개선된 계층형 댓글 정렬을 위한 키 컬럼을 추가했습니다. (gr_comment_*)</li>
	<li>로그인 기록을 저장하는 테이블을 생성했습니다. (gr_login_log 테이블)</li>
	<li>마지막 로그인 시간을 기록하는 컬럼을 첨가했습니다. (member_list 테이블)</li>
	<li>쪽지 알림용 테마 변수를 추가했습니다.</li>
	<li>계층형 댓글의 버그를 보완하기 위한 추가적인 필드들을 각 댓글 테이블마다 하나씩 추가하였습니다.</li>
	<li>게시물에 댓글 허용/불허, 댓글을 쪽지로 알림 여부 등 게시물별 옵션정보를 저장하는 테이블을 추가합니다.</li>
	<li>태그들을 한 곳에 모아두는 종합 태그 저장소 테이블을 생성하고, 기존에 입력했던 태그들을 다시 정리 합니다.</li>
	<li>쪽지함, 스크랩북, 회원가입, 로그인 페이지에 스킨(테마)기능 추가 : 기본 default 테마들 사용함</li>
	<li>Apache 웹서버용 mod_rewrite 에 대응하는 .htaccess 파일을 생성하고, 기존 파일은 갱신합니다.</li>
	<li>스크랩용 저장 테이블을 생성합니다.</li>
	<li>신고 게시물 저장 테이블을 생성합니다.</li>
	<li>멤버들의 글쓰기시 임시로 데이터를 저장할 테이블을 생성합니다.</li>
</ul>

</body></html>