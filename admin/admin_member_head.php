<?php
// 기본 클래스를 부른다 @sirini
$preRoute = './';
require $preRoute.'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 관리자인지 확인한다. @sirini
if($_SESSION['no'] != 1) $GR->error('관리자 화면은 관리자만 접근할 수 있습니다.', 1, 'CLOSE');

// 변수처리 GET, POST to Global @sirini
if($_GET['memberID']) $memberID = $_GET['memberID'];
if($_GET['sortList']) $sortList = $_GET['sortList'];
if($_GET['pointSort']) $pointSort = 1;
if($_GET['page']) $page = $_GET['page'];
if($_GET['selectGroup']) $selectGroup = $_GET['selectGroup'];
if($_GET['changeLevel']) $changeLevel = $_GET['changeLevel'];
@extract($_POST);

if($modifyJumin) $modifyJumin = md5(trim($modifyJumin));
if($modifyPassword) $modifyPassword = trim($modifyPassword);
if($modifyBlock_decontrol) $modifyBlock_decontrol = trim($block_decontrol);
if($modifyNickname) $modifyNickname = trim($modifyNickname);
if($modifyRealname) $modifyRealname = trim($modifyRealname);
if($modifyMail) $modifyMail = trim($modifyMail);
if($modifyHomepage) $modifyHomepage = trim($modifyHomepage);
//if($modifySelfInfo) $modifySelfInfo = str_replace('  ', '', strip_tags(m.ysql_real_escape_string(trim($modifySelfInfo))));
if($modifySelfInfo) $modifySelfInfo = str_replace('  ', '', strip_tags(trim($modifySelfInfo)));
if(!$viewRows) $viewRows = 10;
if($modifyPassword) $password = "password = md5('$modifyPassword'),";

// 정렬옵션 @sirini
if(isset($sortList) && $sortList == 'desc') $sortBy = 'asc'; else $sortBy = 'desc';

// 페이지관련 처리 @sirini
if(!$page or $page < 0) $page = 1;
$fromRecord = ($page - 1) * $viewRows;

// 선택된 멤버들 레벨변경 @sirini
if($changeLevel && $_GET['clm'][0]) {
	for($i=0; $i<count($_GET['clm']); $i++) {
		$GR->query('update '.$dbFIX.'member_list set level = '.$changeLevel.' where no = '.$_GET['clm'][$i].' limit 1');
	}
	$GR->error('선택된 멤버들의 레벨을 모두 '.$changeLevel.' 로 수정하였습니다.', 0, 'admin_member.php');
}

// 선택된 멤버들 삭제 @sirini
if($_GET['dcm'][0]) {
	for($d=0; $d<count($_GET['dcm']); $d++) {
		$getMember = $GR->getArray('select id, photo, nametag, icon from '.$dbFIX.'member_list where no = \''.$_GET['dcm'][$d].'\' limit 1');
		$GR->query('delete from '.$dbFIX.'member_list where no = '.$_GET['dcm'][$d].' limit 1');
		if(file_exists($getMember['photo']))
			@unlink($getMember['photo']);
		if(file_exists($getMember['nametag']))
			@unlink($getMember['nametag']);
		if(file_exists($getMember['icon']))
			@unlink($getMember['icon']);
		if(file_exists('passwd/'.$getMember['id'].'.php'))
			@unlink('passwd/'.$getMember['id'].'.php');
	}
	$GR->error('선택된 멤버들을 모두 삭제하였습니다.', 0, 'admin_member.php');
}

// 멤버 정보수정 처리 @sirini
if(array_key_exists('isModifyMember', $_POST) && $_POST['isModifyMember']) {
	if($deleteNameTag) {
		$delete1 = $GR->getArray('select nametag from '.$dbFIX.'member_list where no = '.$targetMemberNo);
		@unlink($delete1['nametag']);
		$GR->query("update {$dbFIX}member_list set nametag = '' where no = '$targetMemberNo'");
	}
	if($deletePhoto) {
		$delete2 = $GR->getArray("select photo from {$dbFIX}member_list where no = '$targetMemberNo'");
		@unlink($delete2['photo']);
		$GR->query("update {$dbFIX}member_list set photo = '' where no = '$targetMemberNo'");
	}
	if($deleteIcon) {
		$delete3 = $GR->getArray("select icon from {$dbFIX}member_list where no = '$targetMemberNo'");
		@unlink($delete3['icon']);
		$GR->query("update {$dbFIX}member_list set icon = '' where no = '$targetMemberNo'");
	}
	$getOldFile = $GR->getArray("select photo, nametag, icon from {$dbFIX}member_list where no = '$targetMemberNo'");
	
	// 사진 등록하기 @sirini
	if(array_key_exists('photo', $_FILES) && $_FILES['photo']) {
		$filename1 = $_FILES['photo']['name'];
		$filetype1 = $_FILES['photo']['type'];
		$filesize1 = $_FILES['photo']['size'];
		$filetmpname1 = $_FILES['photo']['tmp_name'];

		if($filesize1 > 0) {
			if(!preg_match('/\.(png|jpg|gif)$/i', $filename1)) {
				$GR->error('사진이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'admin_member.php');
			}

			$checkSize1 = @getimagesize($filename1);
			if($checkSize1[0] > 200 or $checkSize1[1] > 200) {
				$GR->error('사진이 200 x 200 이상입니다. 줄여서 업로드 해 주세요.', 0, 'admin_member.php');
			}
			if(!is_dir('member')) {
				@mkdir("member", 0705);
				@chmod('member', 0707); 
			}			
			if(!is_uploaded_file($filetmpname1)) {
				$GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'admin_member.php');
			}
			$filetmpname1 = str_replace('\\\\', '\\', $filetmpname1);

			if(preg_match('|\.png|i', $filename1)) $filename1 = 'grboard_photo_'.$targetMemberNo.'.png';
			elseif(preg_match('|\.jpg|i', $filename1)) $filename1 = 'grboard_photo_'.$targetMemberNo.'.jpg';
			else $filename1 = "grboard_photo_".$targetMemberNo.".gif";
			
			if(file_exists('member/'.$filename1)) @unlink('member/'.$filename1);
			$saveFile1 = 'member/'.$filename1;
			if(!move_uploaded_file($filetmpname1, $saveFile1)) {
				$GR->error('파일을 업로드 하지 못했습니다.', 0, 'admin_member.php');
			}
		} else $saveFile1 = $getOldFile['photo'];
	}

	// 네임택 등록하기 @sirini
	if(array_key_exists('nametag', $_FILES) && $_FILES['nametag']) {
		$filename2 = $_FILES['nametag']['name'];
		$filetype2 = $_FILES['nametag']['type'];
		$filesize2 = $_FILES['nametag']['size'];
		$filetmpname2 = $_FILES['nametag']['tmp_name'];

		if($filesize2 > 0) {
			if(!preg_match('/\.(png|jpg|gif)$/i', $filename2)) {
				$GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'admin_member.php');
			}

			$checkSize2 = @getimagesize($filename2);
			if($checkSize2[0] > 80 or $checkSize2[1] > 20) {
				$GR->error('그림이 80 x 20 이상입니다. 줄여서 업로드 해 주세요.', 0, 'admin_member.php');
			}
			if(!is_dir('member')) {
				@mkdir('member', 0705);
				@chmod('member', 0707);
			}
			if(!is_uploaded_file($filetmpname2)) {
				$GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'admin_member.php');
			}
			$filetmpname2 = str_replace('\\\\', '\\', $filetmpname2);
			
			if(preg_match('|\.png|i', $filename2)) $filename2 = 'grboard_nametag_'.$targetMemberNo.'.png';
			elseif(preg_match('|\.jpg|i', $filename2)) $filename2 = 'grboard_nametag_'.$targetMemberNo.'.jpg';
			else $filename2 = 'grboard_nametag_'.$targetMemberNo.'.gif';
			
			if(file_exists('member/'.$filename2)) @unlink('member/'.$filename2);
			$saveFile2 = 'member/'.$filename2;
			
			if(!move_uploaded_file($filetmpname2, $saveFile2)) {
				$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'admin_member.php');
			}
		} else $saveFile2 = $getOldFile['nametag'];
	}

	// 아이콘 등록하기 @sirini
	if(array_key_exists('icon', $_FILES) && $_FILES['icon']) {
		$filename3 = $_FILES['icon']['name'];
		$filetype3 = $_FILES['icon']['type'];
		$filesize3 = $_FILES['icon']['size'];
		$filetmpname3 = $_FILES['icon']['tmp_name'];

		if($filesize3 > 0) {
			if(!preg_match('/\.(png|jpg|gif)$/i', $filename3)) {
				$GR->error('그림이 아닙니다. 지원 확장자 : .png, .jpg, .gif', 0, 'admin_member.php');
			}

			$checkSize3 = @getimagesize($filename3);
			if($checkSize3[0] > 16 or $checkSize3[1] > 16) {
				$GR->error('그림이 16 x 16 이상입니다. 줄여서 업로드 해 주세요.', 0, 'admin_member.php');
			}

			if(!is_dir('icon')) {
				@mkdir('icon', 0705);
				@chmod('icon', 0707);
			}
			if(!is_uploaded_file($filetmpname3)) {
				$GR->error('정상적으로 파일을 업로드 해 주세요.', 0, 'admin_member.php');
			}
			$filetmpname3 = str_replace('\\\\', '\\', $filetmpname3);
			
			if(preg_match('|\.png|i', $filename3)) $filename3 = 'grboard_icon_'.$targetMemberNo.'.png';
			elseif(preg_match('|\.jpg|i', $filename3)) $filename3 = 'grboard_icon_'.$targetMemberNo.'.jpg';
			else $filename3 = 'grboard_icon_'.$targetMemberNo.'.gif';
			
			if(file_exists('icon/'.$filename3)) @unlink('icon/'.$filename3);
			$saveFile3 = 'icon/'.$filename3;
			if(!move_uploaded_file($filetmpname3, $saveFile3)) {
				$GR->error('파일을 업로드 하지 못했습니다. 파일용량이 너무 크지는 않은지 확인해 보세요.', 0, 'admin_member.php');
			}
		} else $saveFile3 = $getOldFile['icon'];
	}

	$sqlUpdateMember = "update {$dbFIX}member_list
		set $password 
			nickname = '$modifyNickname',
			realname = '$modifyRealname',
			email = '$modifyMail',
			homepage = '$modifyHomepage',
			level = '$modifyLevel',
			point = '$modifyPoint',
			self_info = '$modifySelfInfo',
			photo = '$saveFile1',
			nametag = '$saveFile2',
			jumin = '$modifyJumin',
			group_no = '$modifyGroup',
			icon = '$saveFile3',
			blocks = '$block_decontrol'
		where no = '$targetMemberNo'";
	$GR->query($sqlUpdateMember);
	$GR->error('멤버 정보를 성공적으로 수정했습니다.', 0, 'admin_member.php?memberID='.$modifyID);
}

// 멤버 추가 @sirini
if(array_key_exists('isAddMember', $_POST) && $_POST['isAddMember']) {
	if(!$addID) $GR->error('등록할 멤버의 고유 ID를 입력해 주세요.', 0, 'admin_member.php');
	if(!$addPassword) $GR->error('등록할 멤버의 비밀번호를 지정해 주세요.', 0, 'admin_member.php');
	if(!$addNickname) $GR->error('등록할 멤버의 닉네임(별명)을 지정해 주세요.', 0, 'admin_member.php');
	if(!$addRealname) $GR->error('등록할 멤버의 실명을 지정해 주세요.', 0, 'admin_member.php');
	$isUniq = $GR->getArray("select no from {$dbFIX}member_list where id = '$addID'");
	if($isUniq[0]) $GR->error('이미 등록된 ID 입니다. 다른 아이디로 등록해 주세요.', 0, 'admin_member.php');
	$addTime = $GR->grTime();
	$sqlAddMember = "insert into {$dbFIX}member_list
	set no = '',
	id = '$addID',
	password = md5('$addPassword'),
	nickname = '$addNickname',
	realname = '$addRealname',
	email = '',
	homepage = '',
	make_time = '$addTime',
	level = '2',
	point = '0',
	self_info = '',
	photo = '',
	nametag = '',
	jumin = '',
	group_no = '$addGroup',
	icon = ''";
	$GR->query($sqlAddMember);
	$GR->error('멤버를 성공적으로 추가했습니다.', 0, 'admin_member.php');
}

// 멤버 삭제 처리 @sirini
if(array_key_exists('deleteMemberNo', $_GET) && $_GET['deleteMemberNo'] && 
	array_key_exists('deleteMemberId', $_GET) && $_GET['deleteMemberId']) {
	$deleteMemberNo = $_GET['deleteMemberNo'];
	$deleteMemberId = $_GET['deleteMemberId'];
	if($deleteMemberNo != 1) {
		$getMember = $GR->getArray('select photo, nametag, icon from '.$dbFIX.'member_list where no = \''.
			$deleteMemberNo.'\' and id = \''.$deleteMemberId.'\'');
		$GR->query("delete from {$dbFIX}member_list where no = '$deleteMemberNo'");
		if(file_exists($getMember['photo']))
			@unlink($getMember['photo']);
		if(file_exists($getMember['nametag']))
			@unlink($getMember['nametag']);
		if(file_exists($getMember['icon']))
			@unlink($getMember['icon']);
		if(file_exists('passwd/'.$deleteMemberId.'.php'))
			@unlink('passwd/'.$deleteMemberId.'.php');
		$GR->error($deleteMemberId.' 를 삭제하는데 성공했습니다.', 0, 'admin_member.php');
	} else $GR->error('관리자는 삭제할 수 없습니다.', 0, 'admin_member.php');
}

// 문서설정 @sirini
$title = 'GR Board Admin Page ( Member )';
include 'html_head.php';
?>