<?php
$que = array();
// 게시판 목록저장
$que[] = "create table {$dbFIX}board_list (
	no int(11) not null auto_increment,
	id varchar(50) not null default '',
	name varchar(100) not null default '',
	head_file varchar(255) not null default '',
	foot_file varchar(255) not null default '',
	head_form text,
	foot_form text,
	category varchar(255),
	make_time int(11) not null,
	page_num tinyint(4) not null default '10',
	page_per_list tinyint(4) not null default '10',
	enter_level tinyint(4) not null default '1',
	view_level tinyint(4) not null default '1',
	write_level tinyint(4) not null default '1',
	comment_write_level tinyint(4) not null default '1',
	down_level tinyint(4) not null default '0',
	down_point tinyint(4) not null default '0',
	master varchar(255) not null default '',
	theme varchar(100) not null default '',
	comment_page_num tinyint(4) not null default '10',
	comment_page_per_list tinyint(4) not null default '5',
	num_file tinyint(4) not null default '0',
	cut_subject tinyint(4) not null default '0',
	is_full tinyint(4) not null default '0',
	is_rss tinyint(2) not null default '1',
	is_html varchar(100) not null default 'b,font,span,strong,img,a,br,p,div,hr,u,del,i,embed,object,param,s',
	is_editor tinyint(1) not null default '1',
	group_no tinyint(2) not null default '1',
	is_list tinyint(1) not null default '0',
	comment_sort tinyint(1) not null default '1',
	is_comment_editor tinyint(1) not null default '1',
	is_bomb tinyint(1) not null default '1',
	is_history tinyint(1) not null default '1',
	is_english tinyint(1) not null default '1',
	fix_time tinyint(2) not null default '0',
	primary key(no),
	key id(id)
)";

// 회원 목록저장
$que[] = "create table {$dbFIX}member_list (
	no int(11) not null auto_increment,
	id varchar(50) not null default '',
	password char(32) not null default '',
	nickname varchar(20) not null default '',
	realname varchar(15) not null default '',
	email varchar(255) not null default '',
	homepage varchar(255) not null default '',
	make_time int(11) not null,
	level tinyint(4) not null default '1',
	point int(11) not null default '0',
	self_info varchar(255) not null default '',
	photo varchar(255) not null default '',
	nametag varchar(255) not null default '',
	jumin varchar(32) not null default '',
	group_no tinyint(2) not null default '0',
	icon varchar(255) not null default '',
	lastlogin int(11) not null default '0',
	blocks int(11) not null default '0',
	primary key(no),
	key id(id)
)";

// 중앙 자료저장소 공간생성
$que[] = "create table {$dbFIX}pds_save (
	no int(11) not null auto_increment,
	id varchar(50) not null default '',
	article_num int(11) not null default '0',
	file_route1 varchar(255) not null default '',
	file_route2 varchar(255) not null default '',
	file_route3 varchar(255) not null default '',
	file_route4 varchar(255) not null default '',
	file_route5 varchar(255) not null default '',
	file_route6 varchar(255) not null default '',
	file_route7 varchar(255) not null default '',
	file_route8 varchar(255) not null default '',
	file_route9 varchar(255) not null default '',
	file_route10 varchar(255) not null default '',
	hit int(11) not null default '0',
	primary key(no),
	key id(id)
)";

// 에러기록 저장공간생성
$que[] = "create table {$dbFIX}error_save (
	no int(11) not null auto_increment,
	error_msg varchar(255) not null default '',
	msg_time int(11) not null default '0',
	primary key(no)
)";

// 쪽지 저장공간생성
$que[] = "create table {$dbFIX}memo_save (
	no int(11) not null auto_increment,
	member_key int(11) not null default '0',
	sender_key int(11) not null default '0',
	subject varchar(255) not null default '',
	content text,
	signdate int(11) not null default '0',
	is_view tinyint(4) not null default '0',
	primary key(no),
	key member_key(member_key),
	key sender_key(sender_key)
)";

// 그룹 테이블 생성
$que[] = 'create table '.$dbFIX.'group_list ( '.
	'no int(11) not null auto_increment, '.
	'name varchar(50) not null default \'normal\', '.
	'master varchar(200) not null default \'\', '.
	'make_time int(11) not null default \'0\', primary key(no), key(name))';

// 통합 게시물 테이블 생성
$que[] = 'create table '.$dbFIX.'total_article ( '.
	'no int(11) not null auto_increment, '.
	'subject varchar(255) not null default \'\', '.
	'id varchar(20) not null default \'\', '.
	'article_num int(11) not null default \'0\', '.
	'signdate int(11) not null default \'0\', '.
	'is_secret tinyint(1) not null default \'0\', '.
	'primary key(no), key(id), key(article_num))';

// 통합 코멘트 테이블 생성
$que[] = 'create table '.$dbFIX.'total_comment ( '.
	'no int(11) not null auto_increment, '.
	'subject varchar(255) not null default \'\', '.
	'id varchar(20) not null default \'\', '.
	'article_num int(11) not null default \'0\', '.
	'comment_num int(11) not null default \'0\', '.
	'signdate int(11) not null default \'0\', '.
	'is_secret tinyint(1) not null default \'0\', '.
	'primary key(no), key(article_num), key(comment_num))';

// 설문조사 제목
$que[] = 'create table '.$dbFIX.'poll_subject ( '.
	'no int(11) not null auto_increment, '.
	'subject varchar(255) not null default \'\', '.
	'signdate int(11) not null default \'0\', '.
	'comment_num int(11) not null default \'0\', '.
	'id varchar(50) not null default \'\', '.
	'primary key(no))';

// 설문조사 항목
$que[] = 'create table '.$dbFIX.'poll_option ( '.
	'no int(11) not null auto_increment, '.
	'poll_no int(11) not null default \'0\', '.
	'title varchar(255) not null default \'\', '.
	'vote int(11) not null default \'0\', '.
	'id varchar(50) not null default \'\', '.
	'primary key(no), key(poll_no), key(vote))';

// 설문조사 댓글
$que[] = 'create table '.$dbFIX.'poll_comment ( '.
	'no int(11) not null auto_increment, '.
	'poll_no int(11) not null default \'0\', '.
	'member_no int(11) not null default \'0\', '.
	'comment varchar(255) not null default \'\', '.
	'signdate int(11) not null default \'0\', '.
	'primary key(no), key(poll_no), key(member_no))';

// 자동폭파글
$que[] = 'create table '.$dbFIX.'time_bomb ( '.
	'no int(11) not null auto_increment, '.
	'id varchar(50) not null default \'\', '.
	'article_num int(11) not null default \'0\', '.
	'set_time int(11) not null default \'0\', '.
	'primary key(no))';

// 멤버 그룹
$que[] = "create table {$dbFIX}member_group (
   no int(11) not null auto_increment,
   name varchar(50) not null default '',
   make_time int(11) not null default '0',
   primary key(no))";
 
// GR보드 기본기능 테마 저장소
$que[] = "create table {$dbFIX}layout_config (
	no int(11) not null auto_increment,
	opt varchar(50) not null default '',
	var text, primary key(no), key(opt))";

// 신고 게시물
$que[] = "create table `{$dbFIX}report` ( 
	no int(11) not null auto_increment, 
	id varchar(100) not null default '', 
	article_num int(11) not null default '0', 
	reporter int(11) not null default '0', 
	reason varchar(255) not null default '', 
	status tinyint(2) not null default '0', 
	primary key(no), key(id), key(article_num), key(status))";

// 멤버 전용 게시물 임시 저장소
$que[] = "create table `{$dbFIX}auto_save` ( 
	no int(11) not null auto_increment, 
	member_key int(11) not null default '0', 
	subject varchar(255) not null default '', 
	content text, 
	signdate int(11) not null default '0',
	primary key(no), key(member_key))";

// 무한첨부용 저장소
$que[] = "create table `{$dbFIX}pds_extend` ( 
	no int(11) not null auto_increment, 
	id varchar(50) not null default '', 
	article_num int(11) not null default '0', 
	file_route varchar(255) not null default '', 
	primary key(no), key(id), key(article_num))";

// 종합 태그 저장소
$que[] = "create table `{$dbFIX}tag_list` ( 
	no int(11) not null auto_increment, 
	id varchar(50) not null default '',
	tag varchar(50) not null default '', 
	count int(11) not null default '0', 
	primary key(no), key(id))";

// 게시물별 부가 설정 저장소
$que[] = "create table `{$dbFIX}article_option` ( 
	no int(11) not null auto_increment, 
	article_num int(11) not null default '0',
	id varchar(50) not null default '', 
	reply_open tinyint(1) not null default '0', 
	reply_notify tinyint(1) not null default '0', 
	primary key(no), key(id), key(article_num))";

// 로그인 기록 저장소
$que[] = "create table `{$dbFIX}login_log` (
	no int(11) not null auto_increment, 
	member_key int(11) not null default '0', 
	signdate int(11) not null default '0', 
	ip varchar(20) not null default '',
	ref varchar(255) not null default '',
	primary key(no), key(member_key))";

// 첨부파일 원래 이름 매칭 저장소
$que[] = "create table `{$dbFIX}pds_list` ( 
	no int(11) not null auto_increment, 
	type tinyint(1) not null default '0', 
	uid int(11) not null default '0', 
	idx tinyint(2) not null default '0', 
	name varchar(255) not null default '',
	primary key(no), key(type), key(uid))";
	
// 활동 알림판 저장소
$que[] = "create table `{$dbFIX}notification` ( 
  no int(11) not null auto_increment, 
  to_key int(11) not null default '0', 
  from_key int(11) not null default '0',
  act tinyint(2) not null default '0', 
  bbs_id varchar(50) not null default '', 
  bbs_no int(11) not null default '0', 
  is_checked tinyint(1) not null default '0', 
  primary key(no), key(to_key))";

// 초기 설정값 지정
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'outlogin_skin', var = 'new_default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'info_skin', var = 'new_default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'join_skin', var = 'new_default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'memo_skin', var = 'default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'report_skin', var = 'new_default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'notify_skin', var = 'default'";
$que[] = "insert into `{$dbFIX}layout_config` set no = '', opt = 'article_adjust', var = 'default'";
$que[] = "insert into `{$dbFIX}gr_group_list` set no = '', name = 'normal', master = '', make_time = UNIX_TIMESTAMP()";
?>