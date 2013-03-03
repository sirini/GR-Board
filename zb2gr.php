<?php
@header('Content-Type: text/html; charset=utf-8');
// 마지막 업데이트: 2012-06-10
// 제작: 박희근 (sirini@gmail.com / sirini.net)
// 참고: GR Board v1.9.3 에서 테스트 되었습니다.
// 참고: 데이터는 복사되어 변환 되므로 원본에는 아무런 영향이 가지 않습니다.
// 참고: 제로보드4 스킨으로 유명한 DQ님의 사진 스킨 시스템에도 대응해서 변환 됩니다.

// 제로보드4 의 상대적인 경로를 입력해 주세요.
// 보통 게시판 폴더 이름이 bbs 이고, GR Board 와 동등한 위치에 놓여 있을 경우
// 아래를 그대로 두시면 됩니다.
$zeroboard = "../bbs";

@set_time_limit(0);

include 'class/common.php';
include 'db_info.php';

// 변환용으로 쓸 함수
function setUTF8($str) {
	if(!$str) return '';
	//return iconv('EUC-KR', 'UTF-8', $str);
	return $str;
}

echo '<!doctype html><html><head><title>Zeroboard4 to GR Board Converter</title></head><body>';
echo '<h2>GR Board 로 오신 것을 환영합니다.</h2>';
echo '<p>이제부터 변환을 시작하겠습니다. 진행상황은 아래에 나타날 것입니다.</p>';
flush();

// 필요 폴더 생성
@mkdir('data/__thumbs__', 0707);
@mkdir('data/__thumbs__/'.date('Y'), 0707);
@mkdir('data/__thumbs__/'.date('Y/m'), 0707);
@mkdir('data/__thumbs__/'.date('Y/m/d'), 0707);

$getZero = @mysql_query("select name from zetyx_admin_table");
while($zero = @mysql_fetch_array($getZero))
{
	$id = str_replace('zetyx_board_', '', setUTF8($zero['name']));

	echo '====================================<br />';
	echo "{$id} 게시판 변환을 시작합니다. <br />";
	echo '====================================<br /><br />';
	flush();
	
	$sqlAddBoard = "create table gr_bbs_{$id} (
		no int(11) not null auto_increment,
		member_key int(11) not null default '0',
		name varchar(20) not null default '',
		password varchar(50) not null default '',
		email varchar(255) default '',
		homepage varchar(255) default '',
		ip varchar(20) not null default '',
		signdate int(11) not null default '0',
		hit int(11) not null default '0',
		good int(11) not null default '0',
		bad int(11) not null default '0',
		comment_count int(11) not null default '0',
		is_notice tinyint(4) default '0',
		is_secret tinyint(4) default '0',
		is_grcode tinyint(4) default '0',
		category varchar(50) default '',
		subject varchar(255) not null default '',
		content text,
		link1 varchar(255),
		link2 varchar(255),	
		trackback varchar(255),
		tag varchar(255),
		primary key(no),
		key member_key(member_key),
		key hit(hit),
		key signdate(signdate),
		key good(good),
		key bad(bad),
		key is_notice(is_notice)
		)";
	@mysql_query($sqlAddBoard);

	$sqlAddComment = "create table gr_comment_{$id} (
		no int(11) not null auto_increment,
		board_no int(11) not null default '0',
		family_no int(11) not null default '0',
		thread tinyint(4) not null default '0',
		member_key int(11) not null default '0',
		is_grcode tinyint(4) not null default '0',
		name varchar(20) not null default '',
		password varchar(50) not null default '',
		email varchar(255) default '',
		homepage varchar(255) default '',
		ip varchar(20) not null default '',
		signdate int(11) not null default '0',
		good int(11) not null default '0',
		bad int(11) not null default '0',
		subject varchar(255) not null default '',
		content text,
		is_secret tinyint(4) not null default '0',
		order_key varchar(50) not null default '',
		$commentExtend
		primary key(no),
		key board_no(board_no),
		key family_no(family_no),
		key thread(thread),
		key member_key(member_key)
		)";
	@mysql_query($sqlAddComment);
	
	$headContent = '<!doctype html><head><title>BBS</title><link rel="stylesheet" href="[theme]/style.css" type="text/css" title="style" />'."\n".
	'</head><body><div style="text-align:center;"><div style="margin:auto;width:650px;">'."\n";
	$footContent = '</div></div></body></html>';

	$getCategory = @mysql_query("select * from zetyx_board_category_{$id}");
	$addCategory = '';
	while($cat = @mysql_fetch_array($getCategory))
	{
		$cat['name'] = setUTF8($cat['name']);
		$addCategory .= $cat['name'].'|';
	}
	$addCategory = substr($addCategory, 0, strlen($addCategory)-1);

	$makeTime = time();
	$sqlInsertBoard = "insert into gr_board_list
		set no = '',
			id = '$id',
			head_file = '',
			foot_file = '',
			head_form = '$headContent',
			foot_form = '$footContent',
			category = '$addCategory',
			make_time = '$makeTime',
			page_num = '20',
			page_per_list = '10',
			enter_level = '1',
			view_level = '1',
			write_level = '1',
			comment_write_level = '1',
			down_level = '2',
			down_point = '2',
			master = '',
			theme = 'cool_board_swfupload',
			comment_page_num = '50',
			comment_page_per_list = '10',
			num_file = '2',
			cut_subject = '0',
			is_full = '0',
			is_rss = '0',
			is_html = 'b,font,span,strong,img,a,br,p,div,hr,u,del,i,embed,object,param,s',
			is_editor = '1',
			group_no = '1',
			is_list = '0',
			comment_sort = '1',
			is_comment_editor = '1',
			is_bomb = '1',
			is_history = '1',
			is_english = '1',
			fix_time = '0'
		";
	@mysql_query($sqlInsertBoard);

	$getAll = @mysql_query("select * from zetyx_board_{$id}");
	$i = 0;
	while($board = @mysql_fetch_array($getAll))
	{
		@extract($board);
		if($category)
		{
			$getRealCat = @mysql_fetch_array(mysql_query("select name from zetyx_board_category_{$id} where no = '$category'"));
			$category = setUTF8($getRealCat['name']);
		}
		$sqlInsertQue = "insert into gr_bbs_{$id}
			set no = '$no',
				member_key = '$ismember',
				name = '".setUTF8($name)."',
				password = '".setUTF8($password)."',
				email = '".setUTF8($email)."',
				homepage = '".setUTF8($homepage)."',
				ip = '".setUTF8($ip)."',
				signdate = '".setUTF8($reg_date)."',
				hit = '".setUTF8($hit)."',
				good = '".setUTF8($vote)."',
				bad = '0',
				comment_count = '".setUTF8($total_comment)."',
				is_notice = '0',
				is_secret = '".setUTF8($is_secret)."',
				is_grcode = '0',
				category = '$category',
				subject = '".setUTF8($subject)."',
				content = '".setUTF8($memo)."',
				link1 = '".setUTF8($sitelink1)."',
				link2 = '".setUTF8($sitelink2)."',
				trackback = '',
				tag = ''
			";
		@mysql_query($sqlInsertQue);
		
		@chmod($zeroboard.'/data', 0707);
		@chmod($zeroboard.'/data/'.$id, 0707);
		if(!is_dir('data/'.$id)) {
			@mkdir('data/'.$id, 0705);
			@chmod('data/'.$id, 0707);
		}
		if($file_name1 && !file_exists('data/'.$id.'/'.$s_file_name1)) 
			@copy($zeroboard.'/'.$file_name1, 'data/'.$id.'/'.$s_file_name1);
		if($file_name2 && !file_exists('data/'.$id.'/'.$s_file_name2)) 
			@copy($zeroboard.'/'.$file_name2, 'data/'.$id.'/'.$s_file_name2);

		$downHit = $download1 + $download2;

		$sqlUploadInsert = "insert into gr_pds_save
			set no = '',
				id = '$id',
				article_num = '".$no."',
				file_route1 = '".'data/'.$id.'/'.setUTF8($s_file_name1)."',
				file_route2 = '".'data/'.$id.'/'.setUTF8($s_file_name2)."',
				file_route3 = '',
				file_route4 = '',
				file_route5 = '',
				file_route6 = '',
				file_route7 = '',
				file_route8 = '',
				file_route9 = '',
				file_route10 = '',
				hit = '$downHit'
			";
/*
		$sqlUploadInsert = "insert into gr_pds_save
			set no = '',
				id = '$id',
				article_num = '".$no."',
				file_route1 = '".$zeroboard.'/'.$file_name1."',
				file_route2 = '".$zeroboard.'/'.$file_name2."',
				file_route3 = '',
				file_route4 = '',
				file_route5 = '',
				file_route6 = '',
				file_route7 = '',
				file_route8 = '',
				file_route9 = '',
				file_route10 = '',
				hit = '$downHit'
			";
 * 
 */
		@mysql_query($sqlUploadInsert);
		
		if($i && ($i % 500 == 0))
		{
			echo '<span style="color: green;">'."{$id} 게시판에 {$i} 개의 게시물을 변환했습니다.</span><br />";
			flush();
			sleep(2);
		}
		$i++;
	}

	$getCo = @mysql_query("select * from zetyx_board_comment_{$id}");
	$i = 0;
	while($co = @mysql_fetch_array($getCo))
	{
		@extract($co);
		$family_no = $no;
		$order_key = '';
		if($depth) {
			$family_no = $mother;
			$order_key = 'AAA';
		}
		$sqlNewQue = "insert into gr_comment_{$id}
			set no = '".setUTF8($no)."',
			board_no = '".setUTF8($parent)."',
			family_no = '".setUTF8($family_no)."',
			thread = '".setUTF8($depth)."',
			member_key = '".setUTF8($ismember)."',
			is_grcode = '0',
			name = '".setUTF8($name)."',
			password = '".setUTF8($password)."',
			email = '',
			homepage = '',
			ip = '".setUTF8($ip)."',
			signdate = '".setUTF8($reg_date)."',
			good = '0',
			bad = '0',
			subject = '.',
			content = '".setUTF8($memo)."',
			order_key = '$order_key';
		";
		@mysql_query($sqlNewQue);

		if($i && ($i % 1000 == 0))
		{
			echo '<span style="color: orange">'."{$id} 게시판 댓글을 {$i} 개 변환 했습니다.</span><br />";
			flush();
			sleep(2);
		}
		$i++;
	}

	echo '<span style="color: blue">'."{$id} 게시판을 변환 완료 했습니다.</span><br /><br />";
	flush();
	sleep(5);
}

echo '<span style="font-weight: bold">GR Board 로의 BBS 변환을 완료했습니다.</span><br />';


// DQ 님 스킨 사용자일 경우 추가 사진들 변환 처리하기
$is_dq = @mysql_fetch_array(mysql_query('select no from dq_revolution limit 1'));
if($is_dq) {
	$getDQ = @mysql_query('select * from dq_revolution');
	while($dq = @mysql_fetch_array($getDQ)) {
		@extract($dq);
		$files = explode(',', setUTF8($file_names));
		$fnames = explode(',', setUTF8($s_file_names));
		@chmod($zeroboard.'/data2', 0707);
		@chmod($zeroboard.'/data2/'.$zb_id, 0707);
		
		if(!is_dir('data/'.$zb_id)) {
			@mkdir('data/'.$zb_id, 0705);
			@chmod('data/'.$zb_id, 0707);
		}
		
		if(!is_dir('data/'.$zb_id.'/'.$zb_no)) {
			@mkdir('data/'.$zb_id.'/'.$zb_no, 0705);
			@chmod('data/'.$zb_id.'/'.$zb_no, 0707);
		}
						
		for($i=0; $i<count($files); $i++) {
			if(!$files[$i]) break;
			$file_route = 'data/'.$zb_id.'/'.$zb_no.'/'.$fnames[$i];
			if(!file_exists($file_route))
				@copy($zeroboard.'/'.$files[$i], $file_route);
			mysql_query("insert into gr_pds_extend set id = '$zb_id', article_num = '$zb_no', file_route = '$file_route'");
#			mysql_query("insert into gr_pds_extend set id = '$zb_id', article_num = '$zb_no', file_route = '".$zeroboard.'/'.$files[$i]."'");
		}
	}
}

echo '<h3>GR Board 로 회원 데이터를 이전 합니다...</h3>';
flush();

$z = 'zetyx_member_table';
$getMember = @mysql_query('select no, user_id, password, name, email, homepage, comment, point1, point2, reg_date from '.$z);

while($g = @mysql_fetch_array($getMember))
{
	if($g['no'] == 1) continue;
	$point = ($g['point1'] * 2) + $g['point2'];
	$q = "insert into gr_member_list set 
		no = '".setUTF8($g['no'])."', 
		id = '".setUTF8($g['user_id'])."', 
		password = '".setUTF8($g['password'])."', 
		nickname = '".setUTF8($g['name'])."', 
		realname = '".setUTF8($g['name'])."', 
		email = '".setUTF8($g['email'])."', 
		homepage = '".setUTF8($g['homepage'])."', 
		make_time = '".setUTF8($g['reg_date'])."', 
		level = '2', 
		point = '".setUTF8($point)."', 
		self_info = '".setUTF8($g['comment'])."', 
		photo = '', 
		nametag = ''";
	@mysql_query($q);
}

echo '<span style="font-weight: bold">GR Board 로의 회원 데이터 변환을 완료했습니다.</span><br />';
echo '<strong>변환을 완료했습니다. GR Board 의 세상에 오신 것을 환영 합니다.</strong></br /><br /><br />';
echo '<a href="http://sirini.net">시리니넷</a> 에서 GR Board 를 비롯한 GR시리즈의 최신 정보를 확인해 보세요~!</body></html>';
flush();

?>