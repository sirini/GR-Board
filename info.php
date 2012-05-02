<?php
// 기본 클래스를 부른다 @sirini
include_once 'class/common.php';
$GR = new COMMON;
include 'config_member.php';

// 로그인 상태가 아니면 에러 @sirini
if(!$_SESSION['no']) $GR->error('로그인 상태가 아닙니다. 로그인을 해 주세요.');
$GR->dbConn();

// 탈퇴를 실행해도 한 번 더 물어본다 @sirini
if($_GET['outMe'])
{
	$title = 'GR Board Withdraw Page';
	include 'html_head.php';
	?>
	<body>
	<div id="msgBox">
	
		<div id="inputPass" class="mvLoginBack">
			<div class="mv">탈퇴 최종확인</div>
			<form id="checkOutMe" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<div><input type="hidden" name="isSure" value="YES" /></div>
						
			<div class="tableListLine">
				<div class="tableRight">정말로 탈퇴 하시겠습니까?</div>
				<div class="clear"></div>
			</div>
	
			<div class="submitBox">
				<input type="submit" value="탈퇴하기" />
				<input type="button" value="취소" onclick="history.back();" />
			</div>
	
		</form>
		</div>
	
	</div>
	
	</body>
	</html>
	<?php
	exit();
}

// 탈퇴를 진심으로 실행했다면 처리한다. @sirini
if( $_POST['isSure'] == 'YES' ) {
	if($_SESSION['no'] == 1) $GR->error('관리자 자신은 탈퇴 할 수 없습니다.', 0, 'info.php');
	$getMemberInfo = $GR->getArray('select photo, nametag, icon from '.$dbFIX.'member_list where no = \''.$_SESSION['no'].'\'');
	$GR->query('delete from '.$dbFIX.'member_list where no = '.$_SESSION['no']);
	@unlink($getMemberInfo['photo']);
	@unlink($getMemberInfo['nametag']);
	@unlink($getMemberInfo['icon']);
	echo '<!doctype html><html><head><title>감사합니다</title><link rel="stylesheet" href="style.css" type="text/css" title="style" />'.
	'<meta charset="utf-8" /><script> alert(\'정상적으로 등록정보가 삭제되었습니다.\'); self.close(); </script></head><body></body></html>';
	$_SESSION = array();
	exit();
}

// 멤버정보가 수정되었다면 수정처리한다. @sirini
if($_POST['modifyMemberInfo']) {
	$nickname = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['nickname']))));
	$email = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['email']))));
	$getMemberInfo = $GR->getArray('select nickname,email from '.$dbFIX.'member_list where no = \''.$_SESSION['no'].'\'');
	
	// 닉네임은 중복되면 안된다! @sirini
	if($nickname != $getMemberInfo['nickname'])
	{
		$getExistNick = $GR->getArray('select no from '.$dbFIX.'member_list where nickname = \''.$nickname.'\'');
		if($getExistNick['no']) $GR->error('닉네임이 중복됩니다.', 0, 'HISTORY_BACK');
	}
	
	// 이메일은 중복되면 안된다! @sirini
	if($email != $getMemberInfo['email'])
	{
		$getExistEmail = $GR->getArray('select no from '.$dbFIX.'member_list where email = \''.$email.'\'');
		if($getExistEmail['no']) $GR->error('이메일 주소가 이미 등록되어 있습니다.', 0, 'HISTORY_BACK');
	}
	
	$targetMemberNo = $_POST['targetMemberNo'];

	if($_POST['deleteNameTag'])
	{
		$delete1 = $GR->getArray('select nametag from '.$dbFIX.'member_list where no = '.$targetMemberNo);
		@unlink($delete1['nametag']);
		$GR->query("update {$dbFIX}member_list set nametag = '' where no = '$targetMemberNo'");
	}
	if($_POST['deletePhoto'])
	{
		$delete2 = $GR->getArray('select photo from '.$dbFIX.'member_list where no = '.$targetMemberNo);
		@unlink($delete2['photo']);		
		$GR->query("update {$dbFIX}member_list set photo = '' where no = '$targetMemberNo'");
	}
	if($_POST['deleteIcon'])
	{
		$delete3 = $GR->getArray('select icon from '.$dbFIX.'member_list where no = '.$targetMemberNo);
		@unlink($delete3['icon']);		
		$GR->query("update {$dbFIX}member_list set icon = '' where no = '$targetMemberNo'");
	}
	$getOldFile = $GR->getArray('select photo, nametag, icon from '.$dbFIX.'member_list where no = '.$targetMemberNo);

	// 사진 처리 @sirini
	if($_FILES['photo'])
	{
		$filename1 = $_FILES['photo']['name'];
		$filetype1 = $_FILES['photo']['type'];
		$filesize1 = $_FILES['photo']['size'];
		$filetmpname1 = $_FILES['photo']['tmp_name'];

		if($filesize1 > 0)
		{
			$checkSize1 = @getimagesize($filetmpname1);
			if(!$checkSize1 || ($checkSize1[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
			switch($checkSize1[2]) {
				case '1': $extension1 = 'gif'; break;
				case '2': $extension1 = 'jpg'; break;
				case '3': $extension1 = 'png'; break;
			}

			if(($checkSize1[0] > 200) or ($checkSize1[1] > 200)) 
				$GR->error('사진이 200 x 200 이상입니다. 줄여서 업로드 해 주세요.', 0, 'info.php');

			if(!is_dir('member')) { @mkdir('member', 0705); @chmod('member', 0707); }			
			if(!is_uploaded_file($filetmpname1)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'info.php');

			$filetmpname1 = str_replace('\\\\', '\\', $filetmpname1);
			$filename1 = 'grboard_photo_'.$targetMemberNo.'.'.$extension1;

			if(file_exists('member/'.$filename1)) @unlink('member/'.$filename1);

			$saveFile1 = 'member/'.$filename1;
			if(!move_uploaded_file($filetmpname1, $saveFile1)) $GR->error('파일을 업로드 하지 못했습니다.', 0, 'info.php');
		} else $saveFile1 = $getOldFile['photo'];
	}

	// 네임택 처리 @sirini
	if($_FILES['nametag']) {
		$filename2 = $_FILES['nametag']['name'];
		$filetype2 = $_FILES['nametag']['type'];
		$filesize2 = $_FILES['nametag']['size'];
		$filetmpname2 = $_FILES['nametag']['tmp_name'];

		if($filesize2 > 0)
		{
			$checkSize2 = @getimagesize($filetmpname2);
			if(!$checkSize2 || ($checkSize2[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
			switch($checkSize2[2]) {
				case '1': $extension2 = 'gif'; break;
				case '2': $extension2 = 'jpg'; break;
				case '3': $extension2 = 'png'; break;
			}

			if(($checkSize2[0] > 80) or ($checkSize2[1] > 20)) 
				$GR->error('그림이 80 x 20 이상입니다. 줄여서 업로드 해 주세요.', 0, 'info.php');

			if(!is_dir('member')) { @mkdir('member', 0705); @chmod('member', 0707); }			
			if(!is_uploaded_file($filetmpname2)) $GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'info.php');

			$filetmpname2 = str_replace('\\\\', '\\', $filetmpname2);
			$filename2 = 'grboard_nametag_'.$targetMemberNo.'.'.$extension2;

			if(file_exists('member/'.$filename2)) @unlink('member/'.$filename2);

			$saveFile2 = 'member/'.$filename2;
			if(!move_uploaded_file($filetmpname2, $saveFile2))
				$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'info.php');
		} else $saveFile2 = $getOldFile['nametag'];
	}

	// 아이콘 업로드 처리 @sirini
	if($_FILES['icon'])
	{
		$filename3 = $_FILES['icon']['name'];
		$filetype3 = $_FILES['icon']['type'];
		$filesize3 = $_FILES['icon']['size'];
		$filetmpname3 = $_FILES['icon']['tmp_name'];

		if($filesize3 > 0)
		{
			$checkSize3 = @getimagesize($filetmpname3);
			if(!$checkSize3 || ($checkSize3[2] > 3)) $GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'HISTORY_BACK');
			switch($checkSize3[2]) {
				case '1': $extension3 = 'gif'; break;
				case '2': $extension3 = 'jpg'; break;
				case '3': $extension3 = 'png'; break;
			}

			if($checkSize3[0] > 16 or $checkSize3[1] > 16) 
				$GR->error('그림이 16 x 16 이상입니다. 줄여서 업로드 해 주세요.', 0, 'HISTORY_BACK');

			if(!is_dir('icon'))	{
				@mkdir('icon', 0705);
				@chmod('icon', 0707);
			}
			if(!is_uploaded_file($filetmpname3)) 
				$GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'HISTORY_BACK');

			$filetmpname3 = str_replace('\\\\', '\\', $filetmpname3);
			$filename3 = 'grboard_icon_'.$targetMemberNo.'.'.$extension3;

			if(file_exists('icon/'.$filename3)) @unlink('icon/'.$filename3);

			$saveFile3 = 'icon/'.$filename3;
			if(!move_uploaded_file($filetmpname3, $saveFile3)) 
				$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'HISTORY_BACK');
		} else $saveFile3 = $getOldFile['icon'];
	}

	$realname = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['realname']))));
	$homepage = $GR->escape(htmlspecialchars(trim($GR->unescape($_POST['homepage']))));
//	$selfInfo = $GR->escape(strip_tags(nl2br(trim($GR->unescape($_POST['self_info']))),'<img><p><strong><br>'));
        $selfInfo = $GR->escape(nl2br(htmlspecialchars(trim($GR->unescape($_POST['self_info']))))); //수정 필요!!! pico
	if($_POST['password']) $password = trim($_POST['password']);

	// 홈페이지에 http:// 빠져 있으면 넣어주기 @sirini
	if($homepage && !preg_match('/^(http)/i', $homepage)) $homepage = 'http://'.$homepage;
	
	// 주민등록 처리 @sirini
	if($enableJumin) {
		$_POST['jumin'] = trim($_POST['jumin']);
		$savedJumin = @end($GR->getArray("select jumin from {$dbFIX}member_list where no = ".$_SESSION['no']));
		if($_POST['jumin'] == $savedJumin) $jumin = $savedJumin;
		else $jumin = md5($_POST['jumin']);
	} else $jumin = '';

	if($_SESSION['no']) $sessionNo = $_SESSION['no']; else $sessionNo = 0;

	$sqlUpdate = 'update '.$dbFIX.'member_list set ';
	if($_POST['password']) { 
		$sqlUpdate .= "password = password('$password'),";
		// 비밀번호 변경시, 회원 Email로 통보 @이동규
		$PasswordEmail = $GR->getArray("select no, id, realname, email from {$dbFIX}member_list where no = ".$_SESSION['no']);
		$adminMailAddress = @end($GR->getArray('select email from '.$dbFIX.'member_list where no = 1'));
		$mailHeader  = 'MIME-Version: 1.0' . "\r\n";
		$mailHeader .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$mailHeader .= 'To: '.$PasswordEmail['id'].' <'.$PasswordEmail['email'].'>' . "\r\n";
		$mailHeader .= 'From: '.$_SERVER['HTTP_HOST'].' <'.$adminMailAddress['email'].'>' . "\r\n";
		$mailHeader .= 'Content-Transfer-Encoding: base64' . "\r\n";
		$mailSubject = '[http://'.$_SERVER['HTTP_HOST'].'] 회원정보 변경 알림 메일입니다. (GR Board Member Information Change)';
		$mailSubject = '=?utf-8?B?'.base64_encode($mailSubject).'?='; //제목 깨지는거 방지
		$mailMsg = '안녕하세요 '.$_SERVER['HTTP_HOST'].' 입니다.<br />'.
			'회원님 아이디('.$PasswordEmail['id'].')의 비밀번호가 변경되었습니다.<br /><br /><div style="border: #aaa 1px dotted; padding: 10px; background-color: #fafafa">'.
			date("Y년m월d일 A h시i분s초").'에 <strong>'.$_SERVER['REMOTE_ADDR'].'</strong>에 의한 비밀번호 변경</div>'.
		'<br /><br />※ 만약, 본인에 의해 변경한것이 아니라면 해킹/도용 가능성이 큽니다.<br />이 경우, 관리자에게 신속하게 알려주시어 2차 피혜를 막기 바랍니다.<br />'.
			'감사합니다.<br /><br /><span style="color: #999">Powered by <a href="http://sirini.net" style="color: #999">GR Board</a> '.$GR->grInfo().'</span>';
		$mailMsg = chunk_split(base64_encode($mailMsg));  //base64인코딩 

		// 메일 보내기 실패시 에러메시지가 출력됨. @sirini
		mail($PasswordEmail['email'], $mailSubject, $mailMsg, $mailHeader);
	}
	$sqlUpdate .= "nickname = '$nickname',
		realname = '$realname',
		email = '$email',
		homepage = '$homepage',
		self_info = '$selfInfo',
		photo = '$saveFile1',
		nametag = '$saveFile2',
		jumin = '$jumin',
		icon = '$saveFile3'
		where no = '$sessionNo'";
	$GR->query($sqlUpdate);
	$GR->error('수정을 완료했습니다.', 0, ($_POST['boardId'])?'board.php?id='.$_POST['boardId']:'CLOSE');
}

// 회원의 정보를 가져온다. @sirini
$member = $GR->getArray('select * from '.$dbFIX.'member_list where no = '.$_SESSION['no']);

// 게시판상에서 정보 확인일 경우 변수로 저장 @sirini
if(isset($_GET['infoInBoard'])) $infoInBoard = 1; else $infoInBoard = 0;
if(isset($_GET['boardId'])) $boardId = $_GET['boardId']; else $boardId = '';
if(isset($_GET['fromPage'])) $fromPage = $_GET['fromPage']; else $fromPage = '';

// 필요한 변수정의 @sirini
$getInformation = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'info_skin\' limit 1');
if(!$getInformation['var']) $getInformation['var'] = 'default';
$setup = $GR->getArray("select head_file, head_form, foot_form, foot_file, theme from {$dbFIX}board_list where id = '$boardId'");
$theme = 'theme/'.$setup['theme'];
$grboard = str_replace('/info.php', '', $_SERVER['SCRIPT_NAME']);

// 상단 설정, 이동지점 @sirini
if(!empty($boardId) && ($setup['head_file'] or $setup['head_form'])) {

	if($setup['head_file']) {
		ob_start();
		include $setup['head_file'];
		$content = ob_get_contents();
		ob_clean();
		echo str_replace('</head>', '<link rel="stylesheet" href="'.$grboard.'/admin/theme/info/'.$getInformation['var'].'/style.css" type="text/css" title="style" /></head>', $content);
	}
	if($setup['head_form']) {
		$setup['head_form'] = str_replace('[theme]', $grboard.'/'.$theme, $setup['head_form']);
		$setup['head_form'] = str_replace('</head>', '<style type="text/css"> @import  url('.$grboard.'/admin/theme/info/'.$getInformation['var'].'/style.css); </style></head>', $setup['head_form']);
		echo $setup['head_form'];
	}

} else {
	$title = 'GR Board My Information Page';
	include 'html_head.php';
	echo '<script type="text/javascript" src="js/info_check.js"></script><body>';
}

// 회원 가입 테마 부르기 @sirini
include 'admin/theme/info/'.$getInformation['var'].'/info.php';

// 하단 설정 @sirini
if($boardId) {
	if($setup['foot_form']) echo $setup['foot_form'];
	if($setup['foot_file']) include $setup['foot_file'];
}
else { ?></body></html><?php } ?>
