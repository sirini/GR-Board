<?php 
include 'admin/admin_head.php'; 
include 'admin/admin_left_menu.php';
?>
		<!-- 현재 페이지 도움말 -->
		<div id="grboardHelpBox">
			GR Board 관리자 페이지 첫화면에 오셨습니다. GR Board 의 상태, 각종 필터링, 통계 등을 보실 수 있습니다. <a href="#" title="도움말을 더 봅니다" id="helpAdminBtn">...도움말보기 <img src="image/admin/btn_help_more.gif" alt="더 보기" /></a>
			<div id="helpBox" style="display: none">
			아래의 "<strong>보안 정보</strong>" 항목을 유심히 살펴봐 주십시오. GR Board 가 스스로 진단한 결과를 보여줍니다.<br />
			만약 욕설이나 비속어등을 차단할 필요가 있을 경우 아래의 "<strong>작성금지단어</strong>" 를 확인해 보십시오.<br />
			특정 IP를 사용하는 방문객을 거부하고 싶을 땐 "<strong>접근금지IP</strong>"에 IP를 써 주시면 됩니다.<br />
			GR Board 를 오래 운영하셨고 GR Board 가 사용중인 DB 테이블을 다시 최적화 시켜 주고 싶으시다면<br />
			"<strong>총 게시판 수</strong>" 항목 우측에 있는 [모든 게시판 오류수정/최적화] 를 클릭하여 테이블을 재정비 하실 수 있습니다.<br />
			그룹, 게시판, 멤버 등의 각 항목별 관리는 좌측 메뉴판에 버튼이 배치되어 있습니다.<br />
			"<strong>관리메뉴</strong>" 를 활용하여 각 메뉴별로 GR Board 를 원하시는대로 설정해 보시기 바랍니다.<br />
			<br />
			<strong>※ GR보드 성능향상 팁!</strong><br />
			<ol>
				<li>[모든 게시판 오류수정/최적화] 기능을 주기적으로 사용해 보세요.</li>
				<li>기록된 오류들은 확인 후 주기적으로 [모두삭제] 해 보세요.</li>
				<li>서버의 PHP 와 MySQL 버젼이 5 이상이면 더 빨라 집니다.</li>
				<li>GR보드는 가급적 최신버젼을 사용해 보세요.</li>
				<li>스팸성 트랙백/회원들은 주기적으로 정리해 보세요.</li>
				<li>이미지 썸네일 캐쉬파일들을 주기적으로 정리해 보세요.</li>
			</ol>
			</div>
		</div><!--# 현재 페이지 도움말 -->
		
		<div id="grboardAdminTab">
			
			<ul>
				<li><a href="#admStatTable">현재상황</a></li>
				<li><a href="#admLatestArticle">최근 게시물/댓글 기록</a></li>
				<li><a href="#admSubPageTable">서브 페이지 테마 설정</a></li>
				<li><a href="#admFilterTable">작성 금지단어 설정</a></li>
				<li><a href="#admErrorTable">오류 기록</a></li>
				<li><a href="#admLoginLogList">최근 로그인 기록</a></li>
			</ul>
			
			<!-- 현재상황 -->
			<div class="mvBack" id="admStatTable">
					
				<div class="tableListLine">
					<div class="tableLeft" title="GR Core 와 연동되어 있는지 확인합니다. GR Board 설치 후, GR Core 를 설치시 GR Board 상대경로를 지정해주었다면 연동이 됩니다.">GR Core 연동</div>
					<div class="tableRight"><?php echo (file_exists('core.php'))?
					'<span class="goodStatus">GR Core 가 사용가능 합니다. GR Shop 등의 GR시리즈들과 연동이 가능합니다.</span>'
					:'GR Core 와 연동되어 있지 않습니다. GR시리즈들과 연동되지 않습니다.'; ?></div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="사용중인 GR보드 버젼을 확인합니다. 가급적 최신버젼을 사용해 주세요.">GR Board 버젼</div>
					<div class="tableRight"><?php echo $GR->grInfo('all'); ?></div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="서버 환경을 조사합니다. PHP와 MySQL이 최신 안정버젼으로 업데이트 되었는지 확인해 보세요.">서버 정보</div>
					<div class="tableRight">
						<?php echo 'OS: ' . PHP_OS .
						', PHP: ' . PHP_VERSION . 
						', MySQL: ' . mysql_get_server_info() .
						' (MySQLi: '.((function_exists('mysqli_connect_errno'))?' <span class="goodStatus" title="GR Core 와 연동이 가능합니다.">가능</span>':'<span class="badStatus" title="GR Core 와 연동할 수 없어 GR보드 v1.9 이후 버젼부터는 사용하실 수 없는 환경입니다. 서버 관리자에게 PHP5 MySQLi 확장을 열어달라고 요청해 보세요.">불가</span>').')';
						?>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="GR보드가 주요 보안 설정을 자동으로 검사합니다. 우측에 나타난 보안 취약점을 주의해 주세요.">보안 정보</div>
					<div class="tableRight">
						<?php
						if(!ini_get('magic_quotes_gpc'))
							echo '<div title="GR보드가 이제 스스로 해당 문제점에 대해 미리 방어 합니다.">'.
							'PHP magic_quotes_gpc 옵션이 Off 입니다. SQL Injection 취약점이 발생할 수 있습니다.</div>';
						else $safe = 1;
						if(ini_get('register_globals'))
							echo '<div title="GR보드가 보다 깊은 값 검사를 통해 문제점을 미리 방어합니다.">'.
							'PHP register_globals 옵션이 On 입니다. 확인되지 않은 출신의 변수를 통해 공격받을 수 있습니다.</div>';
						else $safe++;
						if($safe == 2) echo '<span class="goodStatus">서버 옵션 중 보안 취약점은 발견되지 않았습니다.</span>';
						?>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="총 생성된 게시판 수를 확인합니다. 게시판이 너무 많으면 서버에 전체적으로 무리가 가게 됩니다.">총 게시판 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalBoardNum[0]; ?></span> 개
						<a href="#" onclick="repairDB();" title="모든 게시판 및 GR보드 관련 Table 들을 최적화 합니다.">[모든 게시판 오류수정/최적화]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="등록된 멤버수를 확인합니다.">멤버 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalMemberNum[0]; ?></span> 명
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="기본 업로드 기능으로 올려진 첨부파일 수를 확인 합니다.">기본 업로드 파일</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalPdsNum[0]; ?></span> 개
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="추가/멀티 업로드 기능으로 올려진 첨부파일 수를 확인 합니다.">추가/멀티업로드 파일</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalPdsExtendNum[0]; ?></span> 개
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="기록된 오류가 몇 개인지 확인합니다. 주기적으로 비워주시는 것이 좋습니다.">기록된 오류 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalErrorNum[0]; ?></span> 개 
						<a href="#" onclick="errorLogDelete();" title="보고된 오류 기록들을 모두 삭제합니다.">[모두삭제]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="보관중인 쪽지 수를 확인합니다. DB공간 절약을 위해서는 주기적으로 비우시는 것이 좋습니다.">보관중인 쪽지 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalMemoNum[0]; ?></span> 개
						<a href="#" onclick="memoDelete();" title="일주일 이상 지난 쪽지들은 모두 삭제합니다.">[오래된 쪽지삭제]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="현재 로그인중인 사용자들을 위해 GR보드가 생성한 세션 수를 확인합니다.">사용중인 세션</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalSession; ?></span> 개
						<a href="#" onclick="sessionDelete();" title="현재 로그인된 모든 사용자 정보를 강제로 로그아웃 시킵니다. (관리자 포함)">[모두삭제]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="보관중인 내 알림들의 갯수를 확인합니다. 주기적으로 초기화 해주시는 게 서버에 좋습니다.">내 알림 갯수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalNotiNum[0]; ?></span> 개
						<a href="#" onclick="notiDelete();" title="내 알림들을 모두 삭제합니다.">[모두삭제]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="설문조사에 달린 총 댓글 수를 확인합니다.">설문조사 댓글 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalPollCommentNum[0]; ?></span> 개
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="멤버들의 로그인 기록이 얼만큼 쌓였는지 확인합니다. 주기적으로 기록을 삭제하시면 DB 공간을 더 효율적으로 쓸 수 있습니다.">로그인 기록 수</div>
					<div class="tableRight">
						<span class="miniBold"><?php echo $totalLoginLogNum[0]; ?></span> 개
						<a href="#" onclick="loginLogDelete();" title="지금까지 보관중이던 로그인 시간 기록 정보를 모두 삭제합니다. DB공간을 더 효율적으로 활용할 수 있습니다.">[모두삭제]</a>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="GR보드에 주소 재작성 기능 (아파치 웹서버 전용) 사용 여부와 기능 선택이 나타납니다. 서버 부하를 줄이기 위해선 사용하지 않는 것이 좋습니다.">주소 재작성</div>
					<div class="tableRight">
						<?php
						if(file_exists('./.htaccess')) echo '<a href="admin.php?rewrite=off" title="클릭하시면 주소 재작성기를 쓰지 않습니다.">[mod_rewrite 사용중지]</a> Apache 웹서버의 mod_rewrite 모듈을 이용한 주소 재작성 기능을 <span style="color: green"><strong>사용중</strong></span>입니다. ';
						else echo '<a href="admin.php?rewrite=on" title="클릭하시면 주소 재작성기를 사용하여 단축된 URL 기능을 지원합니다.">[mod_rewrite 사용하기]</a> 기존 주소체계를 사용하고 있습니다.';
						if(!is_writable('./.htaccess') && !is_writable('./no.use.htaccess')) echo '<br /><span class="badStatus">※ GR보드 폴더 내 <strong>.htaccess</strong> (혹은) <strong>no.use.htaccess</strong> 파일의 퍼미션(권한)이 707이 아닙니다.</span>';
						?>
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="썸네일용으로 생성되는 임시 이미지 파일들을 모두 정리하여 서버 공간을 효율적으로 활용합니다.">이미지 썸네일</div>
					<div class="tableRight"><a href="#" onclick="cacheImgDelete();" title="여기를 클릭하시면 썸네일용 임시 이미지들을 모두 제거합니다.">[캐쉬 파일 정리하기]</a> 불필요하게 저장된 임시 이미지 파일들을 정리합니다.<?php 
					if(!is_writable('phpThumb/')) echo '<br /><span class="badStatus">! GR보드 내 phpThumb 디렉토리의 퍼미션(접근 권한)이 707 이 아닙니다. FTP 프로그램을 통해 707로 변경해 주세요!</span>'; 
					if(!is_writable('phpThumb/cache/')) echo '<br /><span class="badStatus">! GR보드 내 phpThumb/cache 디렉토리의 퍼미션(접근 권한)이 707 이 아닙니다. FTP 프로그램을 통해 707로 변경해 주세요!</span>';
					?></div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="신고 목록들을 모두 삭제합니다. 신고된 게시물들을 모두 확인하신 후에 진행해 주세요.">신고 목록 삭제</div>
					<div class="tableRight"><a href="#" onclick="reportListDelete();" title="여기를 클릭하시면 신고 목록들을 모두 제거합니다.">[신고 목록 제거하기]</a> 지금까지 들어온 신고 목록들을 제거합니다.</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="통합 최근게시물/댓글에 최근 등록된 1,000 개의 글을 조사하여 이미 삭제된 게시물을 가리키는 목록을 발견하면 목록만 제거합니다.">최근 게시물/댓글 정리</div>
					<div class="tableRight"><a href="#" onclick="confirmTotalLatest();" title="여기를 클릭하시면 통합 최근게시물/댓글에 최근 등록된 1,000 개의 글을 조사하여 이미 삭제된 게시물을 가리키는 목록을 발견하면 목록만 제거합니다. (원래 게시물/댓글은 이미 삭제된 상태에만 해당합니다.)">[통합 최근 게시물/댓글 정리하기]</a> 삭제된 게시물/댓글을 가리키는 최근 통합 목록들만 정리합니다.</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" style="height: 45px" title="서버의 시간과 관리자님 컴퓨터의 시간을 비교하여, 차가 심할 경우 관리자님 컴퓨터 시간을 따르도록 GR보드에게 지시합니다.">시간 비교</div>
					<div class="tableRight">
						<div title="새로고침을 했을 때의 서버 시간과 컴퓨터 시간입니다."><em>서버: <?php echo date('Y-n-j H:i:s'); ?> / 관리자님: <span id="clientTime"></span></em>
						&nbsp;&nbsp;<span class="goodStatus">(GR보드: <?php echo date('Y-n-j H:i:s', $GR->grTime()); ?>)</span></div>
						<div><a href="#" onclick="timeSync(<?php echo date('U'); ?>);" title="여기를 클릭하시면 현재 관리자님 컴퓨터 시간을 따르도록 GR보드를 설정합니다.">[내 컴퓨터 시간으로 동기화]</a> GR보드가 관리자님 컴퓨터 시간을 따르도록 조정합니다.</div>
					</div>
					<div class="clear"></div>
				</div>
			
			</div><!--# 현재상황 -->
			
			<!-- 최근 게시물/댓글 기록 -->
			<div class="mvBack" id="admLatestArticle">
	
				<div class="halfBox">
				<div class="mv" title="아래에 GR보드에서 작성된 최신 글들이 통합되어 출력됩니다.">통합 최근 게시물</div>
					<ul>
						<?php
						$getTA = $GR->query('select * from '.$dbFIX.'total_article order by no desc limit 20');
						while($ta = $GR->fetch($getTA)) { ?>
						<li><a href="board.php?id=<?php echo $ta['id']; ?>&amp;articleNo=<?php echo $ta['article_num']; ?>"><?php echo strip_tags($ta['subject']); ?></a> 
						&nbsp; <span class="smallEng" title="<?php echo date('Y/m/d H:i:s', $ta['signdate']); ?>">(<?php echo date('m.d', $ta['signdate']); ?>)</span></li>
						<?php } ?>
					</ul>
				</div>
	
				<div class="halfBox">
				<div class="mv" title="아래에 GR보드에서 작성된 최신 댓글(코멘트)들이 통합되어 출력됩니다.">통합 최근 댓글</div>
					<ul>
						<?php
						$getTC = $GR->query('select * from '.$dbFIX.'total_comment order by no desc limit 20');
						while($tc = $GR->fetch($getTC)) { ?>
						<li><a href="board.php?id=<?php echo $tc['id']; ?>&amp;articleNo=<?php echo $tc['article_num']; ?>"><?php echo strip_tags($tc['subject']); ?></a> 
						&nbsp; <span class="smallEng" title="<?php echo date('Y/m/d H:i:s', $tc['signdate']); ?>">(<?php echo date('m.d', $tc['signdate']); ?>)</span></li>
						<?php } ?>
					</ul>
				</div>
	
				<div class="clear"></div>
			</div><!--# 최근 게시물/댓글 기록 -->
	
			<!-- 서브 페이지들 테마 설정 -->
			<div class="mvBack" id="admSubPageTable">
			<form id="subPageTheme" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<div><input type="hidden" name="subPageConfirm" value="1" /></div>
				
				<div class="tableListLine">
					<div class="tableLeft" title="GR보드 자체 내의 로그인 테마를 선택합니다.">로그인</div>
					<div class="tableRight">
					<select name="loginTheme">
					<?php
					$getOutloginSkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'outlogin_skin\' limit 1');
					$getOutloginDir = @opendir('admin/theme/outlogin/');
					while($outDir = @readdir($getOutloginDir)) { 
						if($outDir == '.' || $outDir == '..') continue;
					?>
						<option value="<?php echo $outDir; ?>"<?php echo ($getOutloginSkin['var']==$outDir)?' selected="selected"':''; ?>><?php echo $outDir; ?></option>
					<?php } ?>
					</select>
					게시판을 통해 로그인을 할 경우, 게시판 상/하단의 내용을 포함하여 출력합니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="[팝업창] GR보드 자체 내의 쪽지함 테마를 선택합니다.">쪽지함</div>
					<div class="tableRight">
					<select name="memoTheme">
					<?php
					$getMemoSkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'memo_skin\' limit 1');
					$getMemoDir = @opendir('admin/theme/memo/');
					while($memoDir = @readdir($getMemoDir)) { 
						if($memoDir == '.' || $memoDir == '..') continue;
					?>
						<option value="<?php echo $memoDir; ?>"<?php echo ($getMemoSkin['var']==$memoDir)?' selected="selected"':''; ?>><?php echo $memoDir; ?></option>
					<?php } ?>
					</select>
					쪽지함은 새 창으로 띄워집니다. 게시판 상/하단 디자인과 연동되지 않습니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="GR보드 자체 내의 멤버가입 테마를 선택합니다.">멤버가입</div>
					<div class="tableRight">
					<select name="joinTheme">
					<?php
					$getJoinSkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'join_skin\' limit 1');
					$getJoinDir = @opendir('admin/theme/join/');
					while($joinDir = @readdir($getJoinDir)) { 
						if($joinDir == '.' || $joinDir == '..') continue;
					?>
						<option value="<?php echo $joinDir; ?>"<?php echo ($getJoinSkin['var']==$joinDir)?' selected="selected"':''; ?>><?php echo $joinDir; ?></option>
					<?php } ?>
					</select>
					게시판을 통해 멤버가입을 할 경우, 게시판 상/하단의 내용을 포함하여 출력합니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="[팝업창] GR보드 자체 내의 신고함 테마를 선택합니다.">신고함</div>
					<div class="tableRight">
					<select name="reportTheme">
					<?php
					$getReportSkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'report_skin\' limit 1');
					$getReportDir = @opendir('admin/theme/report/');
					while($reportDir = @readdir($getReportDir)) { 
						if($reportDir == '.' || $reportDir == '..') continue;
					?>
						<option value="<?php echo $reportDir; ?>"<?php echo ($getReportSkin['var']==$reportDir)?' selected="selected"':''; ?>><?php echo $reportDir; ?></option>
					<?php } ?>
					</select>
					신고함은 새 창으로 띄워집니다. 게시판 상/하단 디자인과 연동되지 않습니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="[레이어 팝업창] 메모(쪽지)가 왔을 때 알려주는 메시지 상자 테마를 선택합니다.">쪽지 알림상자</div>
					<div class="tableRight">
					<select name="notifyTheme">
					<?php
					$getNotifySkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'notify_skin\' limit 1');
					$getNotifyDir = @opendir('admin/theme/memo_notify/');
					while($notifyDir = @readdir($getNotifyDir)) { 
						if($notifyDir == '.' || $notifyDir == '..') continue;
					?>
						<option value="<?php echo $notifyDir; ?>"<?php echo ($getNotifySkin['var']==$notifyDir)?' selected="selected"':''; ?>><?php echo $notifyDir; ?></option>
					<?php } ?>
					</select>
					쪽지 알림상자는 레이어 팝업 형식으로 띄워집니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="tableListLine">
					<div class="tableLeft" title="GR보드 자체 내의 멤버가입 테마를 선택합니다.">가입 정보확인</div>
					<div class="tableRight">
					<select name="infoTheme">
					<?php
					$getInfoSkin = $GR->getArray('select var from '.$dbFIX.'layout_config where opt = \'info_skin\' limit 1');
					$getInfoDir = @opendir('admin/theme/info/');
					while($infoDir = @readdir($getInfoDir)) { 
						if($infoDir == '.' || $infoDir == '..') continue;
					?>
						<option value="<?php echo $infoDir; ?>"<?php echo ($getInfoSkin['var']==$infoDir)?' selected="selected"':''; ?>><?php echo $infoDir; ?></option>
					<?php } ?>
					</select>
					게시판을 통해 정보확인을 할 경우, 게시판 상/하단의 내용을 포함하여 출력합니다.
					</div>
					<div class="clear"></div>
				</div>
	
				<div class="submitBox">
					<input type="submit" value="서브 페이지 테마 업데이트" title="GR보드내 보여지는 서브 페이지들의 테마를 변경합니다." />
				</div>
			
			</form>
			</div><!--# 서브 페이지들 테마 설정 -->
		
			<!-- 필터링 -->
			<div class="mvBack" id="admFilterTable">
				
				<form id="filter" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<div><input type="hidden" name="modifyFilter" value="1" /></div>
				<div style="text-align:center;">
					<textarea name="filterForm" class="filterForm" rows="5" cols="50"><?php @readfile('filter.txt'); ?></textarea>
				</div>
				<?php if(!is_writable('filter.txt')) { ?><span class="badStatus">※ GR Board 디렉토리 안에 있는 filter.txt 파일의 퍼미션(권한)을 707로 해주세요!</span><br /><?php } ?>
				<div class="caution">- 금지단어는 공백없이 (<strong>,</strong>) 콤마로 구분합니다.</div>
				
				<div class="submitBox">
					<input type="submit" value="금지어 필터 업데이트" title="금지단어 필터를 수정합니다" />
				</div>
				</form>
			</div><!--# 필터링 -->
		
			<!-- 오류기록 -->
			<div class="mvBack" id="admErrorTable">
	
				<?php
				$fromRecord = ($page - 1) * 10;
				$totalError = $GR->getArray('select count(*) from '.$dbFIX.'error_save');
				$totalCount = $totalError[0];
				$totalPage = ceil($totalCount / 10);
	
				// 오류목록을 가져와서 10개씩 뿌림
				$getError = $GR->query("select * from {$dbFIX}error_save order by no desc limit {$fromRecord}, 10");
				while($error = $GR->fetch($getError))
				{
					?>
				<div class="tableListLine">
					<div class="tableLeft"><?php echo date("Y.m.d", $error['msg_time']); ?></div>
					<div class="tableRight"><?php echo strip_tags($error['error_msg']); ?></div>
					<div class="clear"></div>
				</div>
					<?php
				} # while
	
				// 오류기록이 많이 쌓여 있을 땐 페이징
				if($totalCount > 10) { ?>
				<div class="paging"><?php echo $GR->getPaging(10, $page, $totalPage, 'admin.php?page=', 0, 0, '', '', '', '#admErrorTable'); ?></div>
				<?php } # paging ?>
			
			</div><!--# 오류기록 -->
		
			<!-- 최근 로그인 기록 -->
			<div class="mvBack" id="admLoginLogList">
	
				<div class="halfBox">
					<ul>
				<?php
				// 시작레코드열 구함
				if(array_key_exists('loginlogPage', $_GET)) $loginlogPage = $_GET['loginlogPage'];
				if(!$loginlogPage) $loginlogPage = 1;
				$loginDiv = 30;
				$loginlogFromRecord = ($loginlogPage - 1) * $loginDiv;
				$totalloginlogNum = $GR->getArray('select count(*) from '.$dbFIX.'login_log');
				$loginlogTotalPage = ceil($totalloginlogNum[0] / $loginDiv);
	
				// 최근 로그인한 기록들 열람
				$getLoginLog = $GR->query('select * from '.$dbFIX.'login_log order by no desc limit '.$loginlogFromRecord.', '.$loginDiv);
				$loopLogin = $loginlogFromRecord+1;
				$chkLoop = 0;
				while($loginlog = $GR->fetch($getLoginLog))
				{
					$m = $GR->getArray('select id, level, point, nickname, nametag, icon, lastlogin from '.$dbFIX.'member_list where no = '.$loginlog['member_key']);
					$name = strip_tags($m['nickname']);
					if($m['nametag']) $name = '<img src="'.$m['nametag'].'" alt="'.$m['nickname'].'" />';
					if($m['icon']) $name = '<img src="'.$m['icon'].'" alt="" /> '.$name;
					echo '<li title="IP: '.$loginlog['ip'].'">'.$loopLogin.'. '.$name.' <span class="caution">(level: '.$m['level'].' / point: '.$m['point'].' / time: '.date('m.d H:i:s', $loginlog['signdate']).')</span></li>'; 
					$loopLogin++;
					$chkLoop++;
				}
				echo '</ul>';
	
				// 로그인 멤버 페이징
				if($totalloginlogNum[0] > $loginDiv) {
				?>
				<div class="vSpace"></div>
				<div class="paging"><?php echo $GR->getPaging(10, $loginlogPage, $loginlogTotalPage, 'admin.php?loginlogPage=', 0, 0, '', '', '', '#admLoginLogList'); ?></div>
				<?php } # paging ?>
			
			</div>
	
			</div>
	
			<div class="clear"></div>
	
		</div>
		
	</div>

</div>

</body>
</html>
