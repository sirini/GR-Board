<?php
// 기본 클래스를 부른다 @sirini
include 'class/common.php';
$GR = new COMMON;
$GR->dbConn();

// 관리자가 아니면 볼 수 없다. @sirini
if(!$_SESSION['no']) exit();
$id = $_POST['id'];
if($_SESSION['no'] == 1) { $isAdmin = 1; } else { $isAdmin = 0; }
$getMasters = $GR->getArray('select master, group_no from '.$dbFIX.'board_list where id = \''.$id.'\'');

// 게시판 관리자 @sirini
if($getMasters[0]) {
	$masterArr = explode('|', $getMasters[0]);
	$masterNum = count($masterArr);
	for($m=0; $m<$masterNum; $m++) {
		if($_SESSION['mId'] && ($_SESSION['mId'] == $masterArr[$m])) {
			$isAdmin = 1;
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
			break;
		}
	}
}
if(!$isAdmin) $GR->error('관리자만이 볼 수 있습니다.', 1, 'board.php?id='.$id);

// 변수 처리 @sirini
$maxDefaultField = 22;
if(array_key_exists('exec', $_POST)) $exec = $_POST['exec'];
if($exec) {
	$selectArticle = $_POST['selectArticle'];
} else {
	$box = $_POST['box'];
	$selectArticle = "";
	$boxSize = count($box);
	for($tb=0; $tb<$boxSize; $tb++) $selectArticle .= $box[$tb].';';
}
$tempArray = explode(';', $selectArticle); //tempArray[] : 선택한 게시물의 no 값 배열
array_pop($tempArray); //세미콜론 뒤 NULL 제거
$countTmpArr = count($tempArray);

$grboard = str_replace('/list_adjust.php', '', $_SERVER['PHP_SELF']);
$pathArr = @explode('/', $grboard);
if(count($pathArr) > 1) $grboard = '/'.$pathArr[1];

#### 실행 버튼을 누른 경우 (HTML 로드하기 전) ####

// 게시물들 삭제
if($exec == 'delete') {
	for($g=0; $g<$countTmpArr; $g++) {
		$articleNo = $tempArray[$g];
		$deleteQuery = array("delete from {$dbFIX}bbs_{$id} where no = '$articleNo';"
							,"delete from {$dbFIX}comment_{$id} where board_no = '$articleNo';"
							,"delete from {$dbFIX}total_article where id = '$id' and article_num = '$articleNo';"
							,"delete from {$dbFIX}article_option where id = '$id' and article_num = '$articleNo';"
							,"delete from {$dbFIX}total_comment where id = '$id' and article_num = '$articleNo';"
							,"delete from {$dbFIX}time_bomb where id = '$id' and article_num = '$articleNo';"
		);
		for($h=0;$h<count($deleteQuery);$h++){
			$GR->query($deleteQuery[$h]);
		}
		
		$files = $GR->getArray("select * from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");
		if($files['no']) {
			for($f=1; $f<11; $f++) {
				$fileRoute = 'file_route'.$f;
				if($files[$fileRoute]) @unlink($files[$fileRoute]); 
			}
		} # if
		$getExtendFiles = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
		
		$deleteQuery = array();
		while($extFiles = $GR->fetch($getExtendFiles)) {
			@unlink($extFiles['file_route']);
			array_push($deleteQuery, "delete from {$dbFIX}pds_list where type = 1 and uid = '{$extFiles['no']}'");
		}
		array_push($deleteQuery, "delete from {$dbFIX}pds_list where type = 0 and uid = '{$files['no']}';"
								,"delete from {$dbFIX}pds_extend where id = '$id' and article_num = '$articleNo';"
								,"delete from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo';"
								,"delete from {$dbFIX}total_article where id = '$id' and article_num = '$articleNo';"
								,"delete from {$dbFIX}total_comment where id = '$id' and article_num = '$articleNo';"
		);
		for($h=0;$h<count($deleteQuery);$h++){
			$GR->query($deleteQuery[$h]);
		}
		
	} # for

	$GR->error('선택된 게시물을 모두 삭제했습니다.', 0, 'board.php?id='.$id);
} # delete

// 게시물들 이동
if($exec == 'move' && array_key_exists('moveAction', $_POST)) { //이동시작 버튼 눌렀으면
	$moveTarget = $_POST['moveTarget'];
	$moveTargetID = str_replace($dbFIX.'bbs_', '', $moveTarget);
	sort($tempArray, SORT_NUMERIC);

	for($g=0; $g<$countTmpArr; $g++) {
		$articleNo = $tempArray[$g];
		$moveData = $GR->assoc($GR->query("select * from {$dbFIX}bbs_{$id} where no = '$articleNo'"));
		unset($moveData['no']); //auto_increment 적용해서 번호 새로 부여
		$insertArticleQue = "insert into {$moveTarget} set no = ''";
		foreach ($moveData as $key => $value) {
			$value = $GR->escape($value); //GPC아님 지우면 안 됨
			$insertArticleQue .= ", $key = '$value'";
		}
		
		$addExtendField = '';
		$getExtendField = $GR->query('select * from '.$dbFIX.'bbs_'.$id.' where no = '.$articleNo);
		$numField = $GR->getNumFields($getExtendField);
		$targetExtendCheck = $GR->getNumFields($GR->query('select * from '.$moveTarget.' limit 1'));
		if(($numField > $maxDefaultField) && ($numField == $targetExtendCheck)) {
			for($f=$maxDefaultField; $f<$numField; $f++) {
				$fields = $GR->getFetchFields($getExtendField, $f);
				$addExtendField .= ', '.$fields->name.' = \''.$moveData[$fields->name].'\'';
			}
		}
		
		$insertArticleQue .= $addExtendField;
		
		$GR->query($insertArticleQue);
		$insertNo = $GR->getInsertId();
		$deleteQuery = "delete from {$dbFIX}bbs_{$id} where no = '$articleNo'";
		$GR->query($deleteQuery);

		$getOldComment = $GR->query("select * from {$dbFIX}comment_{$id} where board_no = '$articleNo'");
		while($moveComment = $GR->assoc($getOldComment)) {
			unset($moveComment['no']); //auto_increment 적용해서 번호 새로 부여
			$moveComment['board_no'] = $insertNo; //새 부모글 번호
			$insertArticleQue = "insert into {$dbFIX}comment_{$moveTargetID} set no =''";
			foreach ($moveComment as $key => $value) {
				$value = $GR->escape($value); //GPC아님 지우면 안 됨
				$insertArticleQue .= ", $key = '$value'";
			}
			$GR->query($insertArticleQue);
			$deleteQuery = "delete from {$dbFIX}comment_{$id} where no = '$mvNo'";
			$GR->query($deleteQuery);
		}

		$moveFile = $GR->getArray('select * from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = '.$articleNo);

		$mvFileRoute1 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route1']);
		$mvFileRoute2 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route2']);
		$mvFileRoute3 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route3']);
		$mvFileRoute4 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route4']);
		$mvFileRoute5 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route5']);
		$mvFileRoute6 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route6']);
		$mvFileRoute7 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route7']);
		$mvFileRoute8 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route8']);
		$mvFileRoute9 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route9']);
		$mvFileRoute10 = str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $moveFile['file_route10']);
		
		if(!is_dir('data/'.$moveTargetID)) {
			@mkdir('data/'.$moveTargetID, 0705);
			@chmod('data/'.$moveTargetID, 0707);
		}

		$mvFileRoute = array();
		for($mf=1; $mf<11; $mf++) {
			$fileRoute = 'file_route'.$mf;
			if($moveFile[$fileRoute]) {
				$filename = end(explode('/', $moveFile[$fileRoute]));
				$afterMoveRoute = 'data/'.$moveTargetID.'/'.$filename;
				if(file_exists($afterMoveRoute)) $afterMoveRoute = 'data/'.$moveTargetID.'/'.substr(md5($GR->grTime()), -3).'_'.$filename;
				@copy($moveFile[$fileRoute], $afterMoveRoute);
				@unlink($moveFile[$fileRoute]);
				$mvFileRoute[$mf] = $afterMoveRoute;
			}
		}

		$GR->query('update '.$dbFIX.'pds_save set id = \''.$moveTargetID.'\', article_num = '.$insertNo.
			", file_route1 = '$mvFileRoute[1]', file_route2 = '$mvFileRoute[2]', file_route3 = '$mvFileRoute[3]'".
			", file_route4 = '$mvFileRoute[4]', file_route5 = '$mvFileRoute[5]', file_route6 = '$mvFileRoute[6]'".
			", file_route7 = '$mvFileRoute[7]', file_route8 = '$mvFileRoute[8]', file_route9 = '$mvFileRoute[9]'".
			", file_route10 = '$mvFileRoute[10]' where no = ".$moveFile['no'].' limit 1');
		
		$getReals = $GR->query('select * from '.$dbFIX.'pds_list where type = 0 and uid = '.$moveFile['no']);
		while($rf = $GR->fetch($getReals)) {
			$GR->query('update '.$dbFIX.'pds_list set name = \''.str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $rf['name']).'\' where no = '.$rf['no']);
		}

		$getExtendFiles = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
		while($extFiles = $GR->fetch($getExtendFiles)) {
			$fileName = end(explode('/', $extFiles['file_route']));
			if(file_exists('data/'.$moveTargetID.'/'.$fileName)) $mvPath = 'data/'.$moveTargetID.'/'.substr(md5($GR->grTime()), -3).$fileName;
			else $mvPath = 'data/'.$moveTargetID.'/'.$fileName;
			@copy($extFiles['file_route'], $mvPath);
			@unlink($extFiles['file_route']);
			$GR->query("update {$dbFIX}pds_extend set id = '$moveTargetID', article_num = '$insertNo', file_route = '$mvPath' where no = ".$extFiles['no']);
			
			$getRealName = $GR->getArray('select no, name from '.$dbFIX.'pds_list where type = 1 and uid = '.$extFiles['no'].' limit 1');
			$GR->query('update '.$dbFIX.'pds_list set name = \''.str_replace('/'.$id.'/', '/'.$moveTargetID.'/', $getRealName['name']).'\' where no = '.$getRealName['no'].' limit 1');
		}
		$GR->query("update {$dbFIX}total_article set id = '$moveTargetID', article_num = '$insertNo' where id = '$id' and article_num = '$articleNo'");
		$GR->query("update {$dbFIX}total_comment set id = '$moveTargetID', article_num = '$insertNo' where id = '$id' and article_num = '$articleNo'");
		$GR->query("update {$dbFIX}time_bomb set id = '$moveTargetID', article_num = '$insertNo' where id = '$id' and article_num = '$articleNo'");
	}
	$GR->error('게시물을 성공적으로 이동했습니다.', 0, 'board.php?id='.$id);
} #move

if($exec == 'copy' && array_key_exists('copyAction', $_POST)) { //복사시작 버튼 눌렀으면
	$copyTarget = $_POST['copyTarget'];
	$copyTargetID = str_replace($dbFIX.'bbs_', '', $copyTarget);
	sort($tempArray, SORT_NUMERIC);

	for($g=0; $g<$countTmpArr; $g++) {
		$articleNo = $tempArray[$g];
		$copyData = $GR->assoc($GR->query("select * from {$dbFIX}bbs_{$id} where no = '$articleNo'"));
		unset($copyData['no']); //auto_increment 적용해서 번호 새로 부여
		$insertArticleQue = "insert into {$copyTarget} set no = ''";
		foreach ($copyData as $key => $value) {
			$value = $GR->escape($value); //GPC아님 지우면 안 됨
			$insertArticleQue .= ", $key = '$value'";
		}

		$addExtendField = '';
		$getExtendField = $GR->query('select * from '.$dbFIX.'bbs_'.$id.' where no = '.$articleNo);
		$numField = $GR->getNumFields($getExtendField);
		$targetExtendCheck = $GR->getNumFields($GR->query('select * from '.$copyTarget.' limit 1'));
		if(($numField > $maxDefaultField) && ($numField == $targetExtendCheck)) {
			for($f=$maxDefaultField; $f<$numField; $f++) {
				$fields = $GR->getFetchFields($getExtendField, $f);
				$addExtendField .= ', '.$fields->name.' = \''.$copyData[$fields->name].'\'';
			}
		}
		$insertArticleQue .= $addExtendField;
		$GR->query($insertArticleQue); 
		$insertNo = $GR->getInsertId();

		$getOldComment = $GR->query("select * from {$dbFIX}comment_{$id} where board_no = '$articleNo'");
		while($copyComment = $GR->assoc($getOldComment)) {
			$insertArticleQue = "insert into {$dbFIX}comment_{$copyTargetID} set no =''";
			unset($copyComment['no']); //새 부모글 번호
			$copyComment['board_no'] = $insertNo; //새 부모글 번호
			foreach ($copyComment as $key => $value) {
				$value = $GR->escape($value); //GPC아님 지우면 안 됨
				$insertArticleQue .= ", $key = '$value'";
			}
			$GR->query($insertArticleQue); 
		}

		$copyFile = $GR->getArray("select * from {$dbFIX}pds_save where id = '$id' and article_num = '$articleNo'");
		
		if(!is_dir('data/'.$copyTargetID)) {
			@mkdir('data/'.$copyTargetID, 0705);
			@chmod('data/'.$copyTargetID, 0707);
		}

		$targetPath = array();
		for($cf=1; $cf<11; $cf++) {
			$fileRoute = 'file_route'.$cf;
			if($copyFile[$fileRoute]) {
				$filename = end(explode('/', $copyFile[$fileRoute]));
				if(file_exists('data/'.$copyTargetID.'/'.$filename)) {
					$targetPath[$cf] = 'data/'.$copyTargetID.'/'.substr(md5($GR->grTime()), -3).'_'.$filename;
				} else {
					$targetPath[$cf] = 'data/'.$copyTargetID.'/'.$filename;
				}
				@copy($copyFile[$fileRoute], $targetPath[$cf]);
			}
		}
		$cpHit = $copyFile['hit'];

		if($copyFile[0]) {
			$insertPdsQue = "insert into {$dbFIX}pds_save
				set no = '',
				id = '$copyTargetID',
				article_num = '$insertNo',
				file_route1 = '$targetPath[1]',
				file_route2 = '$targetPath[2]',
				file_route3 = '$targetPath[3]',
				file_route4 = '$targetPath[4]',
				file_route5 = '$targetPath[5]',
				file_route6 = '$targetPath[6]',
				file_route7 = '$targetPath[7]',
				file_route8 = '$targetPath[8]',
				file_route9 = '$targetPath[9]',
				file_route10 = '$targetPath[10]',
				hit = '$cpHit'";
			$GR->query($insertPdsQue);
			$copyInsertID = $GR->getInsertId();
			
			$getRealNames = $GR->query('select * from '.$dbFIX.'pds_list where type = 0 and uid = '.$copyFile['no']);
			while($reals = $GR->fetch($getRealNames)) {
				$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 0, uid = '.$copyInsertID.', idx = '.$reals['idx'].', name = \''.str_replace('/'.$id.'/', '/'.$copyTargetID.'/', $reals['name']).'\'');
			}
		}

		$getExtendFiles = $GR->query('select no, file_route from '.$dbFIX.'pds_extend where id = \''.$id.'\' and article_num = '.$articleNo);
		while($extFiles = $GR->fetch($getExtendFiles)) {
			$targetFile = end(explode('/', $extFiles['file_route']));
			if(file_exists('data/'.$copyTargetID.'/'.$targetFile)) $randExtPds = substr(md5($GR->grTime()), -3).'_';
			else $randExtPds = '';
			$copyExtendRoute = 'data/'.$copyTargetID.'/'.$randExtPds.$targetFile;
			@copy($extFiles['file_route'], $copyExtendRoute);
			$GR->query('insert into '.$dbFIX."pds_extend set no = '', id = '$copyTargetID', article_num = '$insertNo', file_route = '$copyExtendRoute'");

			$getExtendInsertID = $GR->getInsertId();
			$getRealName = $GR->getArray('select name from '.$dbFIX.'pds_list where type = 1 and uid = '.$extFiles['no'].' limit 1');
			$GR->query('insert into '.$dbFIX.'pds_list set no = \'\', type = 1, uid = '.$getExtendInsertID.', idx = 0, name = \''.str_replace('/'.$id.'/', '/'.$copyTargetID.'/', $getRealName['name']).'\'');
		}

		$GR->query("insert into {$dbFIX}total_article set no = '', subject = '$copySubject', id = '$copyTargetID', article_num = '$insertNo', signdate = '$copySigndate', is_secret = '$copyIsSecret'");

		$getSetTime = $GR->getArray('select set_time from '.$dbFIX."time_bomb where id = '$id' and article_num = '$articleNo'");
		if($getSetTime['set_time'] > 0) {
			$GR->query("insert into {$dbFIX}time_bomb set no = '', id = '$copyTargetID', article_num = '$insertNo', set_time = '".$getSetTime['set_time']."'");
		}
	} # for				
	$GR->error('게시물을 성공적으로 복사했습니다.', 0, 'board.php?id='.$id);
} #copy

//카테고리 변경
if($exec == 'category' && array_key_exists('categoryAction', $_POST)) {
	$categoryTarget = $_POST['categoryTarget'];
	if($categoryTarget == "category_delete") { $categoryTarget = ""; }
	sort($tempArray, SORT_NUMERIC);
	for($g=0; $g<$countTmpArr; $g++) {
		$articleNo = $tempArray[$g];
		$GR->query("update {$dbFIX}bbs_{$id} set category = '$categoryTarget' where no = '$articleNo'");
	} # for
$GR->error('해당 게시물의 카테고리를 성공적으로 변경했습니다.', 0, 'board.php?id='.$id);
} #category

#### 여기부터는 실행버튼 안 누른 경우 HTML 표시하는 부분 ####

// 문서설정
$title = 'GR Board Article Adjust Page';
include 'html_head.php';
?>
<body>
<!-- 중앙배열 -->
<div id="installBox">

	<!-- 폭 설정 -->
	<div class="sizeFix">

		<!-- 타이틀 -->
		<div class="bigTitle">Article adjust</div>

		<!-- 게시물관리 보기 박스 -->
		<div id="admMenuTable">
			<div style="padding: 10px">선택한 게시물 관리</div>
			<form id="adjustMenu" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<input type="hidden" name="id" value="<?php echo $id;?>" />
				<input type="hidden" name="selectArticle" value="<?php echo $selectArticle;?>" />
				<input type="hidden" name="exec" value="" />
				<div class="menu"><a href="javascript:execAdjust('delete');" onfocus="this.blur()" title="선택한 게시물들을 삭제합니다"><img src="image/admin/arrow.gif" alt="" /> 삭제</a></div>
				<div class="menu"><a href="javascript:execAdjust('move');" title="선택한 게시물들을 이동합니다"><img src="image/admin/arrow.gif" alt="" /> 이동</a></div>
				<div class="menu"><a href="javascript:execAdjust('copy');" title="선택한 게시물들을 복사합니다"><img src="image/admin/arrow.gif" alt="" /> 복사</a></div>
				<div class="menu"><a href="javascript:execAdjust('category');" title="선택한 게시물들의 카테고리를 변경합니다."><img src="image/admin/arrow.gif" alt="" /> 카테고리 변경</a></div>
			</form>
		</div><!--# 게시물관리 보기 박스 -->

		<!-- 우측 몸통 부분 -->
		<div id="admBody">

		<div class="mvBack" id="admSelectedList">
			<div class="mv">선택된 게시물 목록</div>
			<ul>
			<?php
				for($g=0; $g<$countTmpArr; $g++){
					$articleNo = $tempArray[$g];
					$articleSubject = $GR->getArray("select subject from {$dbFIX}bbs_{$id} where no = '$articleNo'");
					echo '<li>'.strip_tags($articleSubject[0]).'</li>';
				}
			?>
			</ul>
		</div>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

		<?php if(!isset($exec)){?><a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>
		<?php
		}#if(!isset($exec))

		// 게시물들 이동
		if($exec == 'move') {	?>
		<form name="moveArticle" onsubmit="return moveOk();" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="exec" value="move" />
		<input type="hidden" name="moveAction" value="1" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="selectArticle" value="<?php echo $selectArticle; ?>" />
		<div class="mvBack">
			<div class="mv">이동할 게시판 선택</div>
			<div style="text-align:center;padding:10px;">
				<select name="moveTarget">
				<option value="">이동할 게시판을 선택하세요</option>
				<?php
				include 'db_info.php'; //GPC안 쓰므로 예외적으로 db_info.php 직접 호출 허용
				$getTableList = $GR->query("show table status from {$dbName} like '{$dbFIX}bbs%'");
				while($tables = $GR->fetch($getTableList)) { ?>
					<option value="<?php echo $tables['Name']; ?>"><?php echo str_replace($dbFIX.'bbs_', '', $tables['Name']); ?></option>
					<?php
				} # while
				?>
				</select>
				<input type="submit" value="이동 시작" class="submit" /> <input type="button" value="뒤로 가기" class="submit" onclick="location.href='<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>';" />
			</div>
		</div>
		</form>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>

		<?php
		} # move
		
		// 게시물 복사
		if($exec == 'copy') { ?>
		<form name="copyArticle" onsubmit="return copyOk();" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="exec" value="copy" />
		<input type="hidden" name="copyAction" value="1" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="selectArticle" value="<?php echo $selectArticle; ?>" />
		<div class="mvBack">
			<div class="mv">복사할 게시판 선택</div>
			<div style="text-align: center; padding: 10px">
				<select name="copyTarget">
				<option value="">복사할 게시판을 선택하세요</option>
				<?php
				// 게시판 목록 받아오기
				include "db_info.php"; //GPC안 쓰므로 예외적으로 db_info.php 직접 호출 허용
				$getTableList = $GR->query("show table status from {$dbName} like '{$dbFIX}bbs%'");
				while($tables = $GR->fetch($getTableList)) { ?>
					<option value="<?php echo $tables['Name']; ?>"><?php echo str_replace($dbFIX.'bbs_', "", $tables['Name']); ?></option>
					<?php
				} # while
				?>
				</select>
				<input type="submit" value="복사 시작" class="submit" /> <input type="button" value="뒤로 가기" class="submit" onclick="location.href='<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>';" />
			</div>
		</div>
		</form>

		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>

		<?php
		} # copy
		// 카테고리 변경
		if($exec == 'category') {		?>
		<form name="categoryArticle" onsubmit="return categoryOk();" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="exec" value="category" />
		<input type="hidden" name="categoryAction" value="1" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="selectArticle" value="<?php echo $selectArticle; ?>" />
		<div class="mvBack">
			<div class="mv">카테고리 선택</div>
			<div style="text-align: center; padding: 10px">
				<select name="categoryTarget">
				<option value="">변경할 카테고리명을 선택하세요</option>
				<?php
				// 게시판 카테고리 목록 받아오기
				include "db_info.php"; //GPC안 쓰므로 예외적으로 db_info.php 직접 호출 허용
				// 셀렉트박스형 카테고리 선택
				$categories = $GR->getArray('select category from '.$dbFIX.'board_list where id = \''.$id.'\'');
				$categoryArray = @explode('|', $categories['category']);
				$countCategory = @count($categoryArray);
				for($ca=0; $ca<$countCategory; $ca++) { ?>
					<option value="<?php echo strip_tags($categoryArray[$ca]); ?>"><?php echo strip_tags($categoryArray[$ca]); ?></option>
				<?php } # for ?> 
				<option value="category_delete">카테고리 제거</option>
				</select>
				<input type="submit" value="변경 시작" class="submit" /> <input type="button" value="뒤로 가기" class="submit" onclick="location.href='<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>';" />
			</div>
		</div>
		</form>

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>
		<div class="vSpace"></div>
		<div class="vSpace"></div>

		<a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>" title="게시판으로 돌아갑니다." style="font-weight:bold;">[게시판으로 돌아가기]</a>

		<?php
		} # category
		?>
		</div><!--# 우측 몸통 부분 -->
		<div class="clear"></div>

	</div><!--# 폭 설정 -->

</div><!--# 중앙배열 -->

<script>
function moveOk() {
	if(!document.forms["moveArticle"].elements["moveTarget"].value) {
		alert('이동할 게시판을 선택하세요.');
		return false;
	}
	return true;
}

function copyOk() {
	if(!document.forms["copyArticle"].elements["copyTarget"].value) {
		alert('복사할 게시판을 선택하세요.');
		return false;
	}
	return true;
}

function categoryOk() {
	if(!document.forms["categoryArticle"].elements["categoryTarget"].value) {
		alert('변경할 카테고리를 선택하세요.');
		return false;
	}
	return true;
}

function execAdjust(exec)
{
	document.forms["adjustMenu"].exec.value = exec;
	if(exec == 'delete'){
		if(confirm('정말로 선택한 게시물들을 삭제하시겠습니까?\n\n게시물과 연관된 첨부파일, 코멘트 모두 삭제됩니다.')){
			document.forms["adjustMenu"].submit();
		}
		else{
			//확인 안 누르면 Do Nothing
		}
	}
	else{
		document.forms["adjustMenu"].submit();
	}
}

// 마우스 온 이벤트
function Over(t) {
	t.style.backgroundColor='#ececec';
}

// 마우스 아웃 이벤트
function Out(t) {
	t.style.backgroundColor='';
}
</script>

</body>
</html>
