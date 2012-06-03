<?php
// 기본 클래스를 불러온다. @sirini
include 'class/common.php';
$GR = new COMMON;

// DB 에 연결한다. @sirini
$GR->dbConn();

// 스팸등록이면 알짤없이 튕겨버린다. @sirini
if(!$_POST['antiSpam'] || ($_POST['antiSpam'] != substr(md5('grboardAntiSpamJoin'.$_POST['time']), -4)))
	$GR->error('자동등록방지용 4자리 코드가 올바르지 않습니다. 다시 입력해 주세요.', 0, 'HISTORY_BACK');

// 주민등록번호 검사 @PHP스쿨 곤님
function resnoCheck($resno) 
{
	// 형태 검사: 총 13자리의 숫자, 7번째는 1..4의 값을 가짐
	if (!preg_match('|^[[:digit:]]{6}[1-4][[:digit:]]{6}$|i', $resno)) return false;

	// 날짜 유효성 검사
	$birthYear = ('2' >= $resno[6]) ? '19' : '20';
	$birthYear += substr($resno, 0, 2);
	$birthMonth = substr($resno, 2, 2);
	$birthDate = substr($resno, 4, 2);
	if (!checkdate($birthMonth, $birthDate, $birthYear)) return false;

	// Checksum 코드의 유효성 검사
	for ($i = 0; $i < 13; $i++) $buf[$i] = (int) $resno[$i];
	$multipliers = array(2,3,4,5,6,7,8,9,2,3,4,5);
	for ($i = $sum = 0; $i < 12; $i++) $sum += ($buf[$i] *= $multipliers[$i]);
	if ((11 - ($sum % 11)) % 10 != $buf[12]) return false;

	return true;
}

// 주민등록번호 검사 @sirini
if($_POST['jumin'] && !resnoCheck($_POST['jumin'])) {
	$GR->error('주민등록번호가 유효하지 않습니다. 다시 입력해 주세요.', 0, 'HISTORY_BACK');
}

// 넘어온 값을 처리한다. (보안패치 @KISA)
if($_POST['joinInBoard']) $joinInBoard = trim($_POST['joinInBoard']);
if($_POST['boardId']) $boardId = trim($_POST['boardId']);
if($_POST['fromPage'])	$fromPage = trim($_POST['fromPage']);
if($_POST['id']) {
	$id = str_replace(' ', '', trim($_POST['id']));
	$id = str_replace('<',' ', trim($_POST['id']));
}
if($_POST['password']) $password = str_replace(' ', '', trim($_POST['password']));
if($_POST['jumin']) $jumin = md5(trim($_POST['jumin']));
else $jumin = '';
if($_POST['nickname']) $nickname = str_replace('  ', '', strip_tags(trim($_POST['nickname'])));
if($_POST['realname']) $realname = str_replace('  ', '', strip_tags(trim($_POST['realname'])));
if($_POST['email']) $email = str_replace('  ', '', strip_tags(trim($_POST['email'])));
else $email = '';
if($_POST['homepage']) $homepage = str_replace('  ', '', strip_tags(trim($_POST['homepage'])));
else $homepage = '';
if($_POST['self_info']) $self_info = str_replace('  ', '', strip_tags(trim($_POST['self_info']))); //!!!확인필요 더블스페이스 왜 없애는가
else $self_info = '';

// 이메일은 중복되면 안된다! @sirini
$getExistEmail = $GR->getArray('select no from '.$dbFIX.'member_list where email = \''.$email.'\'');
if($getExistEmail['no']) $GR->error('이메일 주소가 이미 등록되어 있습니다.', 0, 'HISTORY_BACK');

// 닉네임은 중복되면 안된다! @sirini
$getExistNick = $GR->getArray('select no from '.$dbFIX.'member_list where nickname = \''.$nickname.'\'');
if($getExistNick['no']) $GR->error('닉네임이 중복됩니다.', 0, 'HISTORY_BACK');

// 넘겨진 id 값이 유효한 형태인지 확인한다. @sirini
$alreadyId = $GR->getArray("select id from {$dbFIX}member_list where id = '$id'");
if(!isset($id) || ($alreadyId['id'] != '')) {
	$GR->error('ID 값이 유효하지 못합니다.<br />ID 는 공백없이, 영어로 3자 이상 45자 이하여야 하며'.
		'<br />이미 등록된 ID 가 아니어야 합니다.<br /><br />'.(($alreadyId['id'])?'[!] 아이디가 중복 되었습니다!':''), 0, 'HISTORY_BACK');
}

// 사진 업로드 처리 @sirini
$md5Time = substr(md5(time()), -5);
if(isset($_FILES['photo'])) {
	$filename1 = $_FILES['photo']['name'];
	$filetype1 = $_FILES['photo']['type'];
	$filesize1 = $_FILES['photo']['size'];
	$filetmpname1 = $_FILES['photo']['tmp_name'];

	if($filesize1 > 0) {
		$filename1 = strtolower($filename1);

		$checkSize1 = @getimagesize($filetmpname1);
		if(!$checkSize1 || ($checkSize1[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
		switch($checkSize1[2]) {
			case '1': $extension1 = 'gif'; break;
			case '2': $extension1 = 'jpg'; break;
			case '3': $extension1 = 'png'; break;
		}

		if($checkSize1[0] > 200 || $checkSize1[1] > 200) $GR->error('사진이 200 x 200 이상입니다. 줄여서 업로드 해 주세요.', 0, 'HISTORY_BACK');

		if(!is_dir('member')) { 
			@mkdir("member", 0705); 
			@chmod('member', 0707); 
		}			
		if(!is_uploaded_file($filetmpname1)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'HISTORY_BACK');

		$filetmpname1 = str_replace('\\\\', '\\', $filetmpname1);
		$filename1 = 'grboard_photo_'.$id.'_'.$md5Time.'.'.$extension1;
		if(file_exists('member/'.$filename1)) @unlink('member/'.$filename1);
		$saveFile1 = 'member/'.$filename1;
		if(!move_uploaded_file($filetmpname1, $saveFile1)) $GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'HISTORY_BACK');
	} else $saveFile1 = '';
}

// 네임택 업로드 처리 @sirini
if(isset($_FILES['nametag'])) {
	$filename2 = $_FILES['nametag']['name'];
	$filetype2 = $_FILES['nametag']['type'];
	$filesize2 = $_FILES['nametag']['size'];
	$filetmpname2 = $_FILES['nametag']['tmp_name'];

	if($filesize2 > 0) {
		$filename2 = strtolower($filename2);

		$checkSize2 = @getimagesize($filetmpname2);
		if(!$checkSize2 || ($checkSize2[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
		switch($checkSize2[2]) {
			case '1': $extension2 = 'gif'; break;
			case '2': $extension2 = 'jpg'; break;
			case '3': $extension2 = 'png'; break;
		}

		if($checkSize2[0] > 80 || $checkSize2[1] > 20) $GR->error('그림이 80 x 20 이상입니다. 줄여서 업로드 해 주세요.', 0, 'HISTORY_BACK');
		if(!is_dir('member')) { 
			@mkdir('member', 0705);
			@chmod('member', 0707); 
		}			
		if(!is_uploaded_file($filetmpname2)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'HISTORY_BACK');
		$filetmpname2 = str_replace('\\\\', '\\', $filetmpname2);
		$filename2 = 'grboard_nametag_'.$id.'_'.$md5Time.'.'.$extension2;
		if(file_exists('member/'.$filename2)) @unlink('member/'.$filename2);
		$saveFile2 = 'member/'.$filename2;
		if(!move_uploaded_file($filetmpname2, $saveFile2)) 
			$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'HISTORY_BACK');
	} else $saveFile2 = '';
}

// 아이콘 업로드 처리 @sirini
if(isset($_FILES['icon'])) {
	$filename3 = $_FILES['icon']['name'];
	$filetype3 = $_FILES['icon']['type'];
	$filesize3 = $_FILES['icon']['size'];
	$filetmpname3 = $_FILES['icon']['tmp_name'];

	if($filesize3 > 0) {
		$filename3 = strtolower($filename3);

		$checkSize3 = @getimagesize($filetmpname3);
		if(!$checkSize3 || ($checkSize3[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
		switch($checkSize3[2]) {
			case '1': $extension3 = 'gif'; break;
			case '2': $extension3 = 'jpg'; break;
			case '3': $extension3 = 'png'; break;
		}

		if($checkSize3[0] > 16 || $checkSize3[1] > 16) {
			$GR->error('그림이 16 x 16 이상입니다. 줄여서 업로드 해 주세요.', 0, 'HISTORY_BACK');
		}
		if(!is_dir('icon')) { 
			@mkdir('icon', 0705);
			@chmod('icon', 0707); 
		}			
		if(!is_uploaded_file($filetmpname3)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'HISTORY_BACK');
		$filetmpname3 = str_replace('\\\\', '\\', $filetmpname3);
		$filename3 = 'grboard_icon_'.$id.'_'.$md5Time.'.'.$extension3;
		if(file_exists('icon/'.$filename3)) @unlink('icon/'.$filename3);
		$saveFile3 = 'icon/'.$filename3;
		if(!move_uploaded_file($filetmpname3, $saveFile3)) 
			$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'HISTORY_BACK');
	} else $saveFile3 = '';
}

// 여기까지 왔다면 DB 에 등록한다. @sirini
$registerTime = $GR->grTime();
$sqlInsertNewMember = "insert into {$dbFIX}member_list
	set no = '',
	id = '$id',
	password = md5('$password'),
	nickname = '$nickname',
	realname = '$realname',
	email = '$email',
	homepage = '$homepage',
	make_time = '$registerTime',
	level = '2',
	point = '0',
	self_info = '$self_info',
	photo = '$saveFile1',
	nametag = '$saveFile2',
	jumin = '$jumin',
	group_no = '1',
	icon = '$saveFile3',
	lastlogin = '0'";
$GR->query($sqlInsertNewMember);
$insertMemberNo = $GR->getInsertId();

// 파일명 변경 & db업데이트 @sirini
if($saveFile1){
	$saveFile1New = 'member/grboard_photo_'.$insertMemberNo.'.'.$extension1;
	@rename($saveFile1,$saveFile1New);
	$GR->query('update '.$dbFIX.'member_list set photo = \''.$saveFile1New.'\' where no = \''.$insertMemberNo.'\'');
}
if($saveFile2){
	$saveFile2New = 'member/grboard_nametag_'.$insertMemberNo.'.'.$extension2;
	@rename($saveFile2,$saveFile2New);
	$GR->query('update '.$dbFIX.'member_list set nametag = \''.$saveFile2New.'\' where no = \''.$insertMemberNo.'\'');
}
if($saveFile3){
	$saveFile3New = 'icon/grboard_icon_'.$insertMemberNo.'.'.$extension3;
	@rename($saveFile3,$saveFile3New);
	$GR->query('update '.$dbFIX.'member_list set icon = \''.$saveFile3New.'\' where no = \''.$insertMemberNo.'\'');
}

// 회원가입 확장필드용 불러오기 (확장필드가 필요한 스킨의 경우) @sirini
$getJoinus = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'join_skin\' limit 1');
@include 'admin/theme/join/'.$getJoinus['var'].'/theme_join_ok.php';

// 문서설정 @sirini
$title = 'GR Board Join Complete Page';
include 'html_head.php';
?>
<body>

<!-- 여기까지 왔다면 등록을 완료했다는 뜻이다. -->
<script>
<?php if($joinInBoard) { ?>
alert('등록을 완료했습니다. 게시판 상단 로그인 버튼을 클릭하세요.\n\n이 창은 닫아집니다.');
location.href='board.php?id=<?php echo $boardId; ?>';
<?php } else { 
	if($fromPage == "outlogin") { ?>
		alert('등록을 완료했습니다. 메인화면에서 로그인 해 주세요. 이 창은 닫힙니다.');
		self.close();
	<?php } else { ?>
		alert('등록을 완료했습니다. 관리자 화면으로 갑니다.');
		location.href='admin.php';
	<?php } 
} ?>
</script>

<noscript>
<?php if($joinInBoard) { ?>
	<p>등록을 완료했습니다. <a href="board.php?id=<?php echo $boardId; ?>">이전 게시판으로 이동하기</a></p>
<?php } else if($fromPage == "outlogin") { ?>
	<p>등록을 완료했습니다. 메인화면에서 로그인 해 주세요.</p>
<?php } else { ?>
  <p>등록을 완료했습니다.</p>
<?php } ?>
</noscript>

</body>
</html>
