<?php 
include 'admin/admin_member_head.php'; 
include 'admin/admin_left_menu.php';
?>
		<!-- 현재 페이지 도움말 -->
		<div id="grboardHelpBox">
			GR Board 에 등록된 멤버(회원)들을 관리하는 화면 입니다. <a href="#" title="도움말을 더 봅니다" id="helpMemberBtn">...도움말보기 <img src="image/admin/btn_help_more.gif" alt="더 보기" /></a>
			<div id="helpBox" style="display: none">
			사이트의 멤버(회원)들을 관리하는 페이지 입니다.<br />
			"<strong>멤버 목록</strong>" 패널에서는 등록되어 있는 회원의 목록과 선택된 멤버의 삭제 등을 할 수 있습니다.<br />
			관리자가 직접 멤버를 빠르게 등록하길 원하시면 "<strong>빠른 멤버 등록</strong>" 패널의 내용을 작성하여 등록하시면 됩니다.<br />
			"<strong>공통멤버관리</strong>" 패널을 통해 멤버 등록 시 허용여부를 정할 항목들을 설정할 수 있으며<br />
			특히 주민등록번호를 등록 시 필요정보로 받게 되면 비밀번호찾기 기능을 사용할 수 있게 됩니다.<br />
			<br />
			<strong>※ 직접 수정하실 수 있는 부분들 소개</strong><br />
			<br />
			GR Board 폴더 안에 보시면 join.txt 라는 파일과 join_cancel_msg.txt 라는 파일이 보이실 겁니다.<br />
			이 두 개의 텍스트 파일은 각각 아래와 같은 용도를 가지고 있습니다.<br />
			<br />
			<strong>join.txt</strong> : 멤버등록을 허용할 경우 가입약관 부분에 출력될 문서입니다. HTML 태그를 사용하실 수 도 있습니다.<br />
			<strong>join_cancel_msg.txt</strong> : 멤버등록을 허용하지 않을 경우, 안내 메시지에 출력될 문서입니다. HTML 태그를 사용하실 수도 있습니다.<br />
			<br />
			위 두개의 파일들을 FTP 프로그램으로 내려 받으셔서 본인의 웹사이트에 맞게 수정하신 후,<br />
			다시 동일한 위치에 덮어씌우시면 안내문구가 변경되어 출력됩니다.<br />
			join.txt 에는 일반적으로 환영 문구나 가입시 혜택, 주의사항등을 안내해 주시면 좋으며<br />
			join_cancel_msg.txt 에는 현재 멤버등록을 받고 있지 않은 이유나 혹은 공지 게시물 링크를 안내해 주시면 좋습니다.<br />  
			</div>
		</div><!--# 현재 페이지 도움말 -->

		<!-- 멤버 목록 -->
		<div class="mvBack" id="admMemberList">
			<div class="mv">멤버 목록</div>
			<?php			
			// 검색할 멤버이름이 있다면 
			$que = "select no, id, nickname, realname, make_time, level, point, group_no from {$dbFIX}member_list";
			if(isset($searchMemberList) && !isset($sortList)) {
				if(!$searchMemberOption) $searchMemberOption = 'id';
				$que .= " where {$searchMemberOption} like '%{$searchMemberList}%' order by no desc limit {$fromRecord}, {$viewRows}";
				$forPageQue = "select no from {$dbFIX}member_list where {$searchMemberOption} like '%{$searchMemberList}%'";
				$totalAddQue = " where {$searchMemberOption} like '%{$searchMemberList}%'";
			}
			// 정렬옵션이 있다면
			elseif($_GET['sortList']) {
				if($pointSort) $sortTarget = 'point'; else $sortTarget = 'id';
				$que .= " order by {$sortTarget} {$sortBy} limit {$fromRecord}, {$viewRows}";
				$forPageQue = "select id from {$dbFIX}member_list";
				$totalAddQue = '';
			}
			// 그룹을 선택했다면
			elseif($selectGroup) {
				$que .= ' where group_no = '.$_GET['selectGroup'].' order by no desc limit '.$fromRecord.', '.$viewRows;
				$totalAddQue = ' where group_no = '.$_GET['selectGroup'];
			}
			// 아무것도 없다면 기본 부름
			else {
				$que .= " order by no desc limit {$fromRecord}, {$viewRows}";
				$forPageQue = 'select id from '.$dbFIX.'member_list';
				$totalAddQue = '';
			}
			// 쿼리실행
			$resultQue = $GR->query($que);
			
			// 페이징 처리를 위해 결과셋별로 총 목록 수 저장
			$totalResult = $GR->getArray('select count(*) as id from '.$dbFIX.'member_list'.$totalAddQue);
			$totalCount = $totalResult['id'];
			?>
			<div style="padding: 5px">
				<a href="admin_member.php?sortList=<?php echo $sortBy; ?>&amp;pointSort=1" title="멤버 포인트를 기준으로 순차/역순 정렬합니다">&middot; 포인트순 정렬</a> &nbsp;&nbsp;
				<a href="admin_member.php?sortList=<?php echo $sortBy; ?>" title="멤버 ID 를 알파벳대로 순차/역순 정렬합니다">&middot; 알파벳순 정렬</a>
			</div>
			<form id="searchMember" onsubmit="return isSearchValue();" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<table rules="none" summary="GR Board Member List" cellpadding="0" cellspacing="0" border="0" style="width: 100%">
			<caption></caption>
			<colgroup>
			<col style="width: 40px" />
			<col style="width: 40px" />
			<col style="width: 140px" />
			<col style="width: 80px" />
			<col style="width: 100px" />
			<col style="width: 60px" />
			<col style="width: 60px" />
			<col style="width: 50px" />
			<col style="width: 40px" />
			</colgroup>
			<thead>
			<tr>
				<th class="titleBar"><a href="#" onclick="selectAll();">선택</a></th>
				<th class="titleBar">번호</th>
				<th class="titleBar">실명(닉네임)</th>
				<th class="titleBar">아이디</th>
				<th class="titleBar">소속그룹</th>
				<th class="titleBar">레벨(포인트)</th>
				<th class="titleBar">생성일</th>
				<th class="titleBar">쪽지</th>
				<th class="titleBar">삭제</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$totalMember = ceil($totalCount - (($page -1) * $viewRows));
			while($data = $GR->fetch($resultQue)) { 
				$groupName = $GR->getArray('select name from '.$dbFIX.'member_group where no = '.$data['group_no']);
			?>
			<tr>
				<td class="boardList"><input type="checkbox" name="checkMember[]" value="<?php echo $data['no']; ?>" /></td>
				<td class="boardList"><?php echo $totalMember; ?></td>
				<td style="font-size: 9pt">
				<a href="admin_member.php?memberID=<?php echo $data['id']; ?>&amp;page=<?php echo $page; ?>" title="클릭하시면 멤버정보 수정 패널이 열립니다"><?php echo $data['realname'].'('.$data['nickname'].')'; ?></a></td>
				<td class="boardList"><?php echo $data['id']; ?></td>
				<td class="boardList"><?php echo $groupName['name']; ?></td>
				<td class="boardList"><?php echo $data['level'].'('.$data['point'].')'; ?></td>
				<td class="boardList"><?php echo date('Y.m.d', $data['make_time']); ?></td>
				<td class="boardList"><a href="send_memo.php?target=<?php echo $data['no']; ?>" title="해당 회원에게 쪽지를 발송합니다." onclick="window.open('send_memo.php?target=<?php echo $data['no']; ?>', 'sendMemo', 'width=650,height=600,menubar=no,scrollbars=yes'); return false;">보내기</a></td>
				<td class="boardList"><a href="#" onclick="deleteMember(<?php echo $data['no']; ?>, '<?php echo $data['id']; ?>');" title="선택한 멤버를 삭제합니다."><img src="image/admin/admin_user_delete.gif" alt="멤버 삭제" /></a></td>
			</tr>
				<?php
					$totalMember--;
			} # while
			$addPageQue = '';
			if($sortList) $addPageQue = '&amp;sortList='.$sortList;
			if($pointSort) $addPageQue .= '&amp;pointSort='.$pointSort;
			if($selectGroup) $addPageQue .= '&amp;selectGroup='.$selectGroup;
			$addPageQue .= '&amp;page=';
			$totalPage = ceil($totalCount / $viewRows);				
			$printPage = $GR->getPaging($viewRows, $page, $totalPage, 'admin_member.php?x=y'.$addPageQue);
			if($printPage) {
			?>
			<tr>
				<td colspan="9" class="paging"><?php echo $printPage; ?></td>
			</tr>
			<?php } # 페이징 표시 ?>
			<tr>
				<td colspan="9" class="admBoardBottom">
				
				<div class="submitBox">
					<select name="selectGroup" onchange="if(this.value) location.href='admin_member.php?selectGroup='+this.value;">
					<option value="">그룹 선택</option>
					<?php
					$getGroups = $GR->query('select no, name from '.$dbFIX.'member_group');
					while($groups = $GR->fetch($getGroups)) { ?>
						<option value="<?php echo $groups['no']; ?>"
							<?php echo (($groups['no']==$memberData['group_no'] || $groups['no']==$selectGroup)?' selected="selected"':''); ?>>
							<?php echo $groups['name']; ?>
						</option>
					<?php } ?>
					</select>
					<select name="searchMemberOption">
						<option value="">멤버 검색조건 선택</option>
						<option value="id">아이디</option>
						<option value="nickname">닉네임</option>
						<option value="realname">실명</option>
						<option value="email">이메일</option>
						<option value="homepage">홈페이지</option>
						<option value="level">레벨</option>
					</select>
					<input type="text" name="searchMemberList" class="input" title="검색조건에 맞는 값을 입력하세요" value="<?php if(isset($searchBoardList)) echo $searchBoardList; ?>" />
					을 <input type="text" name="viewRows" class="input" title="검색결과를 몇개씩 보실 것인지 입력하세요" value="<?php echo $viewRows; ?>" size="3" maxlength="3" /> 명씩
					<input type="submit" value="검색" title="검색 합니다" />
				</div>

				<div id="admMemberWork">
				<ul>
					<li>선택된 멤버들의 레벨을 <select name="levels" onchange="changeLevel(this.value);">
					<option value="">선택하세요</option>
					<?php for($v=1; $v<100; $v++) { ?>
						<option value="<?php echo $v; ?>"><?php echo $v; ?></option>
					<?php } ?>
					</select> 로 변경 <span style="color: #999">(선택하시면 해당 레벨로 변경됩니다.)</span></li>
					<li><a href="#" onclick="deleteCheckMember();">선택된 멤버들을 모두 삭제</a></li>
				</ul>
				</div>
				</td>
			</tr>
			</tbody>
			</table>
			</form>
		</div><!--# 멤버 목록 -->

		<div class="vSpace"></div>
		<?php		
		// 선택한 멤버 ID값이 있을 경우
		if(isset($memberID)) {
			$memberData = $GR->getArray("select * from {$dbFIX}member_list where id = '$memberID'");
		?>
		<!-- 멤버정보 수정 -->
		<div class="mvBack" id="admModifyMember">
			<div class="mv">멤버정보수정</div>
			<form id="modifyMember" onsubmit="return isModifyValue();" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
			<div><input type="hidden" name="isModifyMember" value="1" />
			<input type="hidden" name="targetMemberNo" value="<?php echo $memberData['no']; ?>" /></div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="멤버의 ID 는 변경할 수 없습니다.">멤버 ID</div>
				<div class="tableRight">
					<input type="text" name="modifyID" class="input" value="<?php echo $memberID; ?>" readonly="readonly" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버가 속할 그룹을 설정합니다.">멤버 그룹설정</div>
				<div class="tableRight">
					<select name="modifyGroup">
					<?php
					$getGroups = $GR->query('select no, name from '.$dbFIX.'member_group');
					while($groups = $GR->fetch($getGroups)) { ?>
						<option value="<?php echo $groups['no']; ?>"
							<?php echo (($groups['no']==$memberData['group_no'] || $groups['no']==$selectGroup)?' selected="selected"':''); ?>>
							<?php echo $groups['name']; ?>
						</option>
					<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="암호는 DB에 암호화되어 저장됩니다.">비밀번호</div>
				<div class="tableRight">
					<input type="password" name="modifyPassword" class="input" /> (※ 경고 : 수정 시 덮어씌워짐)
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="해당 해원의 로그인 실패차단여부의 확인과 차단을 해제할 수 있습니다.">로그인 차단여부</div>
				<div class="tableRight">
					<?php if(!$memberData['blocks'] == 0) { ?><strong class="badStatus">로그인 실패허용 횟수 초과</strong> <input type="checkbox" id="modifyBlock_decontrol" name="modifyBlock_decontrol" value="1" /> <label for="modifyBlock_decontrol">로그인 차단을 해제합니다.</label><?php } else { ?>
					<span class="goodStatus">차단되지 않았습니다.</span><?php } ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버의 주민등록번호는 암호화되어 저장됩니다.">주민등록번호</div>
				<div class="tableRight">
					<input type="text" name="modifyJumin" class="input" /> (※ 경고 : 수정 시 덮어씌워짐)
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="닉네임(별명)은 중복될 수 있습니다.">닉네임</div>
				<div class="tableRight">
					<input type="text" name="modifyNickname" class="input" value="<?php echo $memberData['nickname']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이름(실명)은 중복될 수 있습니다.">실명</div>
				<div class="tableRight">
					<input type="text" name="modifyRealname" class="input" value="<?php echo $memberData['realname']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">이메일</div>
				<div class="tableRight">
					<input type="text" name="modifyMail" class="input" value="<?php echo $memberData['email']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">홈페이지</div>
				<div class="tableRight">
					<input type="text" name="modifyHomepage" class="input" value="<?php echo $memberData['homepage']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="레벨은 일정포인트가 쌓일 때마다 자동으로 증가합니다. 레벨이 높을수록 좋으며 한계치는 없습니다.">레벨</div>
				<div class="tableRight">
				<select name="modifyLevel">
				<?php for($t=1; $t<100; $t++) { ?>
					<option value="<?php echo $t; ?>" <?php echo (($memberData['level']==$t)?'selected':''); ?>><?php echo $t; ?></option>
				<?php } ?>
				</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="포인트는 게시물 작성 = 1, 코멘트 작성 = 1 입니다.">포인트</div>
				<div class="tableRight">
					<input type="text" name="modifyPoint" class="input" value="<?php echo $memberData['point']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="자기소개는 250자 이하로 작성되어야 하며, HTML 태그가 허용되지 않습니다." style="height:70px;">자기소개</div>
				<div class="tableRight">
					<textarea name="modifySelfInfo" class="textarea" rows="3" cols="90"><?php echo strip_tags(nl2br($memberData['self_info'])); ?></textarea>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="사진은 크기 200 x 200 이하여야 합니다.">사진</div>
				<div class="tableRight">
					<input type="file" name="photo" class="input" /> (200 x 200 이하)
					<?php if($memberData['photo']) { ?>
					<div>
						<img src="<?php echo $memberData['photo']; ?>" border="0" alt="사진" title="등록된 사진" /> 
						<input type="checkbox" name="deletePhoto" value="1" /> 등록된 사진을 삭제합니다.
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이름(네임택) 앞에 자그만하게 출력되는 그림으로, 16 x 16 크기가 제일 좋습니다.">아이콘</div>
				<div class="tableRight"><input type="file" name="icon" class="input" /> (16 x 16 이하)
				<?php if($memberData['icon']) { ?>
				<div>
					<img src="<?php echo $memberData['icon']; ?>" border="0" alt="아이콘" title="등록된 아이콘" />
					<input type="checkbox" name="deleteIcon" value="1" /> 등록된 아이콘을 삭제합니다.
				</div>
				<?php } ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="네임택(이름대신 출력되는 그림으로된 이름)은 80 x 20 이하여야 합니다.">네임택</div>
				<div class="tableRight"><input type="file" name="nametag" class="input" /> (80 x 20 이하)
				<?php if($memberData['nametag']) { ?>
				<div>
					<img src="<?php echo $memberData['nametag']; ?>" border="0" alt="네임택" title="등록된 그림이름" />
					<input type="checkbox" name="deleteNameTag" value="1" /> 등록된 네임택을 삭제합니다.
				</div>
				<?php } ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="submitBox">
					<input type="submit" value="멤버 정보 수정" title="멤버 정보를 수정 합니다" />
				</div>
			</div>
			</form>
		</div><!--# 멤버정보 수정 -->
		<?php
		} # 수정화면 끝


		// 멤버 수정이 아닐 경우 일반 멤버관리화면 출력
		else { ?>
		<!-- 빠른멤버등록 -->
		<div class="mvBack" id="admAddMember">
			<div class="mv">빠른 멤버 등록</div>
			<form id="addMember" onsubmit="return isAddValue();" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
			<div><input type="hidden" name="isAddMember" value="1" /></div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="멤버의 ID 는 영문으로 작성해주세요.">멤버 ID</div>
				<div class="tableRight"><input type="text" name="addID" class="input" /> 	
				<a href="#" onclick="alreadyIdCheck();" title="입력하신 ID 가 이미 등록되어 있는지 확인합니다.">[ID 중복확인]</a></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버가 속할 그룹을 설정합니다.">멤버 그룹설정</div>
				<div class="tableRight">
					<select name="addGroup">
					<?php
					$getGroups = $GR->query('select no, name from '.$dbFIX.'member_group');
					while($groups = $GR->fetch($getGroups)) { ?>
					<option value="<?php echo $groups['no']; ?>"><?php echo $groups['name']; ?></option>
					<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="암호는 DB에 암호화되어 저장됩니다.">비밀번호</div>
				<div class="tableRight"><input type="password" name="addPassword" class="input" /></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="닉네임(별명)은 중복될 수 있습니다.">닉네임</div>
				<div class="tableRight"><input type="text" name="addNickname" class="input" /></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이름(실명)은 중복될 수 있습니다.">실명</div>
				<div class="tableRight"><input type="text" name="addRealname" class="input" /></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="submitBox">
					<input type="submit" value="멤버 추가 완료" title="멤버를 새로 추가 합니다" />
				</div>
			</div>
			</form>
		</div><!--# 빠른멤버등록 -->

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

		<?php
		  // POST 처리
		    @extract($_POST);
		    //$enableBlockNum = m.ysql_real_escape_string($enableBlockNum);
			// 공통 멤버 관리 옵션
			if($commonModify) {
				$config = '<?php'."\n";
				$config.= '$enableJoin = '.$enableJoin.';'."\n";
				$config.= '$enableNameTag = '.$enableNameTag.';'."\n";
				$config.= '$enablePhoto = '.$enablePhoto.';'."\n";
				$config.= '$enableJumin = '.$enableJumin.';'."\n";
				$config.= '$enableIcon = '.$enableIcon.';'."\n";
				$config.= '$enableBlock = '.$enableBlock.';'."\n";
				$config.= '$enableBlockNum = '.$enableBlockNum.';'."\n";
				$config.= '?>';
				$fp = @fopen('config_member.php', 'w');
				@fwrite($fp, $config);
				@fclose($fp);
			}
			include 'config_member.php';
		?>
		<!-- 멤버관리옵션 -->
		<div class="mvBack" id="admMemberRule">
			<div class="mv">공통멤버관리</div>
			<form id="commonMemberAdmin" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<div><input type="hidden" name="commonModify" value="1" /></div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="멤버등록을 허용할지 불허할지 결정합니다. GR보드 폴더 안에 있는 join.txt 와 join_cancel_msg.txt 파일도 확인해 보세요.">멤버등록</div>
				<div class="tableRight">
					<input type="radio" name="enableJoin" id="eJoin" value="1" <?php if($enableJoin) echo 'checked="checked"'; ?> /> <label for="eJoin">허용</label> 
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enableJoin" id="disJoin" value="0" <?php if(!$enableJoin) echo 'checked="checked"'; ?> /> <label for="disJoin">거부</label>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버들이 자신만의 이미지 닉네임을 쓸 수 있도록 할지 아니게 할 지 결정합니다.">네임택</div>
				<div class="tableRight">
					<input type="radio" name="enableNameTag" id="eNameTag" value="1" <?php if($enableNameTag) echo 'checked="checked"'; ?> /> <label for="eNameTag">허용</label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enableNameTag" id="disNameTag" value="0" <?php if(!$enableNameTag) echo 'checked="checked"'; ?> /> <label for="disNameTag">거부</label>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버들이 자신만의 사진을 올릴 수 있도록 할지 아니게 할 지 결정합니다.">멤버사진</div>
				<div class="tableRight">
					<input type="radio" name="enablePhoto" id="ePhoto" value="1" <?php if($enablePhoto) echo 'checked="checked"'; ?> /> <label for="ePhoto">허용</label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enablePhoto" id="disPhoto" value="0" <?php if(!$enablePhoto) echo 'checked="checked"'; ?> /> <label for="disPhoto">거부</label>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버들에게 자신의 이름 앞에 아이콘을 넣을 수 있도록 할 것인지 결정합니다.">아이콘</div>
				<div class="tableRight">
					<input type="radio" name="enableIcon" id="eIcon" value="1" <?php if($enableIcon) echo 'checked="checked"'; ?> /> <label for="eIcon">허용</label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enableIcon" id="disIcon" value="0" <?php if(!$enableIcon) echo 'checked="checked"'; ?> /> <label for="disIcon">거부</label>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="멤버들에게 반드시 주민등록번호를 입력 하도록 합니다.">주민등록번호</div>
				<div class="tableRight">
					<input type="radio" name="enableJumin" id="eJumin" value="1" <?php if($enableJumin) echo 'checked="checked"'; ?> /> <label for="eJumin">허용</label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enableJumin" id="disJumin" value="0" <?php if(!$enableJumin) echo 'checked="checked"'; ?> /> <label for="disJumin">거부</label>
				</div>
				<div class="clear"></div>
			</div>
			
			
			<div class="tableListLine">
				<div class="tableLeft" title="일정횟수 이상 로그인 실패시, 로그인을 제한합니다.">로그인 실패 제한</div>
				<div class="tableRight">
					<input type="radio" name="enableBlock" id="eblock" value="1" <?php if($enableBlock) echo 'checked="checked"'; ?> /> <label for="eBlock">허용</label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="enableBlock" id="disblock" value="0" <?php if(!$enableBlock) echo 'checked="checked"'; ?> /> <label for="disBlock">거부</label>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="로그인 실패 허용횟수를 정합니다.">로그인 실패 제한 횟수</div>
				<div class="tableRight">
          <input type="text" id="enableBlockNum" name="enableBlockNum" class="input" value="<?php echo $enableBlockNum; if(!$enableBlockNum) echo '5'; ?>" style="width: 10px;" /> 회 까지 허용합니다.
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="submitBox">
					
					<?php if(!is_writable('config_member.php')) { ?><span class="badStatus">※ GR Board 디렉토리 안에 있는 config_member.php 파일의 퍼미션(권한)을 707로 해주세요!</span><?php } ?>

					<input type="submit" value="공통 설정 수정" title="공통 설정을 수정 합니다" />
				</div>
			</div>
			</form>
		</div><!--# 멤버관리옵션 -->
		<?php
		} # 멤버관리화면 끝
		?>
		</div><!--# 우측 몸통 부분 -->

		<div class="clear"></div>

		</div><!--# 폭 설정 -->

	</div><!--# 가운데 정렬 -->

<script src="js/jquery.js"></script>
<script src="admin/admin_member.js"></script>

</body>
</html>
