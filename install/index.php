<?php
// 기본 클래스를 불러온다. @sirini
$preRoute = '../';
require $preRoute . 'class/common.php';
$GR = new COMMON;

// 이미 설치되어 있다면 (db_info.php 파일이 있다면) 에러발생 @sirini
if(file_exists( $preRoute . 'db_info.php')) $GR->error('이미 GR Board 가 설치되어 있습니다.');

// 비정상적으로 GR Board 가 삭제되었던 경우 세션부분 처리 @sirini
$_SESSION = array();
@session_destroy();

// 문서설정 @sirini
$title = 'GR Board Install Page';
include $preRoute . 'html_head.php';
?>
<body>
<!-- 인스톨 처리 시작 -->
<div id="installBox">
	
	<!-- 폭 설정 -->
	<div class="sizeFix">
		
		<!-- 타이틀 -->
		<div class="bigTitle"><img src="<?php echo $preRoute; ?>image/install/head_mark.gif" alt="" /> GR Board Installation</div>

		<!-- 관리메뉴 -->
		<div id="admMenuTable">
			<div style="padding: 10px">설치순서</div>
			<div class="menu">1. 라이센스 동의 후 DB정보 입력 (현재)</div>
			<div class="menu">2. DB Table 세팅 후 관리자 정보 입력</div>
			<div class="menu">3. 로그인 후 관리자 페이지로 이동</div>
		</div>

		<!-- 우측 몸통 부분 -->
		<div id="admBody">

			<!-- 라이센스 보기 박스 -->
			<div class="mvBack" id="checkMe">
				<div class="mv">GR Board 설치전 확인</div>
				<div style="padding: 5px; line-height: 160%">
				<strong>프로그램 명칭</strong>: GR Board<br />
				<strong>프로그램 버젼</strong>: <?php echo $GR->grInfo('all'); ?><br />
				<strong>제작자</strong>: 박 희 근 (SIRINI)<br />
				<strong>라이센스</strong>: <a href="http://www.gnu.org/licenses/gpl-2.0.html" onclick="window.open(this.href, '_blank'); return false;">GPL (General Public License)</a> (← 클릭 시 라이센스 확인)<br />
				<strong>Powered by GR Series</strong>: 바른 설계, 빠른 실행!  <span style="color: #32739b">우리가 함께 만들어가고 있습니다!</span>
				</div>
			</div>

			<div class="vSpace"></div>

			<!-- 현재 환경체크 박스 -->
			<div class="mvBack" id="checkEnv">
				<div class="mv">현재 환경</div>
				<div style="padding: 5px">
				<?php
				// 퍼미션이 조절되지 않았다면 경고처리
				if(!is_writable('../')) { ?>
				<span class="badStatus">※ 경고 : 퍼미션이 맞지 않습니다. GR Board 폴더의 퍼미션을 707 혹은 777 로 변경하세요!</span><br />
				<?php }
				// 이미 설치된 적이 있다면 경고처리
				if(is_dir('data') || is_dir('session')) echo '<span class="badStatus">※ 경고 : 이전에 GR Board 가 설치된 상태로 보입니다. 재설치시 기존의 DB 는 모두 삭제됩니다!</span><br />';
				?>
				서버 OS : <?php echo PHP_OS; ?><br />
				PHP 버젼 : <?php echo PHP_VERSION; ?> (PHP 4.3 이상 권장)<br />
				서버 소프트웨어 : <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br />
				도메인 : <?php echo $_SERVER['HTTP_HOST']; ?><br />
				주요 PHP 설정 : 
				register_globals =
				<?php 
					if(ini_get('register_globals')) echo '<span class="badStatus" title="경고: 느슨한 서버 보안 설정">On</span>';
					else echo '<span class="goodStatus">Off</span>';
				?>, 
				post_max_size = <?php echo ini_get('post_max_size'); ?>, 
				allow_url_fopen = 
				<?php 
					if(ini_get('allow_url_fopen')) echo '<span class="badStatus" title="경고: 느슨한 서버 보안 설정">On</span>';
					else echo '<span class="goodStatus">Off</span>';	
				?>
				</div>
			</div>

			<div class="vSpace"></div>

			<!-- DB 정보 입력받기 -->
			<div class="mvBack" id="enterDBInfo">
				<div class="mv">DB정보 입력</div>
				<form id="installFirst" action="install_ok.php" onsubmit="return checkValue();" method="post">			
					<div style="padding: 5px"><a href="http://www.gnu.org/licenses/gpl.html" 
					onclick="window.open(this.href, '_blank');return false;" onfocus="this.blur()" 
					title="클릭하시면 라이센스 전문을 보실 수 있습니다">GPL</a> 에 동의하고 
					라이센스에서 제시한 사항을 법적으로 책임질 수 있다면 설치를 계속 하십시오.<br />
					<span style="color: #888">(※ 테이블구분자는 GR보드를 중복 설치할 경우 테이블을 구분해주는 역할을 합니다. 처음 설치시 기본값 권장.)</span></div>
					<div style="padding: 5px">호스트네임: <input type="text" name="hostName" class="input" value="localhost" size="10" />
					DB아이디: <input type="text" name="userId" class="input" size="7" />
					DB패스워드: <input type="password" name="password" class="input" size="7" />
					DB이름: <input type="text" name="dbName" class="input" size="7" />
					테이블구분자: <input type="text" name="dbFIX" class="input" value="gr_" size="7" /></div>
					<div class="admSubmitBox"><input type="image" src="<?php echo $preRoute; ?>image/admin/install_ok.gif" title="설치를 계속합니다" class="btn" /></div>
				</form>
			</div>

			<div class="vSpace"></div>

			<!-- 설치 도움말 -->
			<div class="mvBack" id="installHelp">
				<div class="mv">설치 도움말</div>
				<div style="padding: 5px">
					<span id="showHelpBtn" style="cursor: pointer">도움말 보기 ▼</span> 
					<span style="color: #aaa">|</span>
					<span id="hideHelpBtn" style="cursor: pointer">도움말 닫기 △</span>			
					<div id="helpMe" style="display: none">
					GR보드는 다른 게시판들과 같은 설치형 게시판 입니다.<br />
					설치를 위해서는 MySQL DB 접속 정보를 알고 있어야 합니다.<br />
					만약 타 게시판을 사용해 보셨다면 입력하는 정보가 동일함을 아시게 될 겁니다.<br />
					<br />
					<strong>1. 제로보드 사용자</strong><br />
					<br />
					<img src="<?php echo $preRoute; ?>image/install/ex_zeroboard.gif" alt="zeroboard user" /><br />
					<br />
					위 그림에서 보이는 4가지 입력항목을 보시면 순서대로<br />
					<strong>호스트네임</strong>(Host Name), <strong>DB아이디</strong>(SQL User ID), <strong>DB패스워드</strong>(Password), <strong>DB이름</strong>(DB Name)<br />
					으로 되어 있는 걸 보실 수 있습니다.<br />
					제로보드 설치 때와 마찬가지로 순서대로 입력하시면 됩니다.<br />
					<br />
					<strong>2. 그누보드 사용자</strong><br />
					<br />
					<img src="<?php echo $preRoute; ?>image/install/ex_gnuboard.gif" alt="gnuboard user" /><br />
					<br />
					그누보드4 설치 화면의 왼편에 보이는 입력항목 4개<br />
					(Host, User, Password, DB) 를 각각<br />
					<strong>호스트네임, DB아이디, DB패스워드, DB이름</strong> 에 입력해 주시면 됩니다.
					</div>
				</div>
			</div>

		</div>

		<div class="clear"></div>
	
	</div>

</div>

<script src="<?php echo $preRoute; ?>js/install_check.js" type="text/javascript"></script>

</body>
</html>