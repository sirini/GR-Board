<?php
/****************
 * 기본 멤버가입 테마
 ****************
- 이 파일은 grboard/join.php 위치에서 불려집니다.
- GR보드 기본 멤버가입 테마로서, 상/하단에 게시판 상/하단 HTML 코드(파일)가 그대로 나옵니다.
- 스타일시트는 이 파일과 동일한 위치의 style.css 파일 내용이 반영됩니다.
- 회원가입을 받지 않는 상태일경우, join_cancel_msg.txt 파일이 가입약관 부분에 읽혀지고 정보 입력은 받지 않습니다.
 */
if(!defined('__GRBOARD__')) exit();

// 게시판에서 불려지지 않았을 때
if(!$boardId) echo '<body>';

// 기본 처리
$joinTitle = '가입약관';
$joinText = @file_get_contents('join.txt');

// 회원가입을 받지 않을 때 처리
if(!$enableJoin) {
	$joinTitle = '안내 메시지';
	$joinText = @file_get_contents('join_cancel_msg.txt');
}
?>
<script type="text/javascript" src="./js/jquery.js"></script> 
<script type="text/javascript" src="./js/jquery.pstrength-min.1.2.js"></script> 
<script type="text/javascript">
$(function() {
$('.password').pstrength();
});
</script>
<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->
<!-- memberJoin -->
<div id="memberJoin">
	<div class="header">
		<h1>Register - 회원가입</h1>
	</div>
	<div class="contents">
	  <noscript>
	    <div id="noscript">
	      <p>Javascript를 지원하지 않는 브라우저 입니다.</p>
	      <p>회원가입이 불가능할 수 있습니다.</p>
	    </div>
	  </noscript>
<?php if(!$enableJoin) { ?>
	  <!-- 회원가입 거부 -->
		<div class="info">
		  <h2>안내</h2>
		  <div class="agreement_contents">
		    <?php echo $joinText; ?>
		  </div>
	  </div>
<?php 
} else { 
if($joinText) { ?>
	  <!-- 회원약관 확인 -->
		<div class="agreement">
		  <h2>회원약관 확인</h2>
		  <p class="join_help">회원가입을 원하실 경우, 아래의 '회원약관'을 반드시 읽고 진행해주세요.</p>
		  <div class="agreement_contents">
		    <?php echo $joinText; ?>
		  </div>
	  </div>
<?php } ?>

<!-- Form -->
<form id="join" method="post" onsubmit="return isValueForm(this);" action="join_ok.php" enctype="multipart/form-data">
<div><input type="hidden" name="joinInBoard" value="<?php echo $joinInBoard; ?>" />
<input type="hidden" name="boardId" value="<?php echo $boardId; ?>" />
<input type="hidden" name="fromPage" value="<?php echo $fromPage; ?>" />
<input type="hidden" name="enableJumin" value="<?php echo $enableJumin; ?>" />
<input type="hidden" name="time" value="<?php echo $_time; ?>" /></div>

	  <!-- 기본정보 -->
	  <div class="common">
			<h2>기본정보</h2>
			<p class="notice"><span class="mark">(필수)</span>표시는 필수 입력 사항입니다.</p>
			<table class="input_table" width="100%" cellspacing="0" border="0" summary="기본정보 입력필드">
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> ID(아이디)</th>
					<td><input type="text" name="id" class="input" style="float: left;" /> <a href="#noscript" class="id_check" onclick="alreadyIdCheck(); return false;" title="입력하신 ID 가 이미 등록되어 있는지 확인합니다.">[ID 중복확인]</a></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 비밀번호</th>
					<td><input type="password" name="password" class="input password" onblur="checkPassLength();" /> <p class="info">영문(소/대문자), 특수문자, 숫자를 혼합하여 6자 이상 입력하세요.</p></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 비밀번호 확인</th>
					<td><input type="password" name="passwordCheck" class="input" /></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 이름(실명)</th>
					<td><input type="text" name="realname" class="input" /></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 닉네임(별명)</th>
					<td><input type="text" name="nickname" class="input" style="float: left;" /> <a href="#noscript" class="nickname_check" onclick="alreadyNickCheck(); return false;" title="입력하신 닉네임이 이미 등록되어 있는지 확인합니다.">[닉네임 중복확인]</a></td>
				</tr>
<?php if($enableJumin) { ?>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 주민등록번호</th>
					<td><input type="password" name="jumin" class="input" />
					  <p class="info">하이픈 '-' 없이 입력하세요.</p>
					  <p class="info">주민등록번호는 비밀번호를 분실한 경우에만 쓰이며,<br />
                            해독이 불가능하도록 암호화 하여 DB에 저장되므로 관리자도 알 수 없습니다.<br />
                            예) 44fcff3a3412e2f612
            </p>
          </td>
				</tr>
<?php } ?>
        <tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> Email</th>
					<td><input type="text" name="email" class="input" style="width: 380px;" /></td>
				</tr>
			</table>
	  </div>
	  
	  <!-- 추가정보 -->
	  <div class="add">
			<h2>추가정보</h2>
			<table class="input_table" width="100%" cellspacing="0" border="0" summary="추가정보 입력필드">
<?php if($enableNameTag) { ?>
			  <tr>
					<th scope="row">네임택</th>
					<td>
					  <input type="file" name="nametag" class="input" /> <p class="info">80x20 이하의 이미지파일만 등록해주세요.</p>
				    <?php /* 네임택이 등록된 경우 */
				     if($member['nametag']) { ?>
				    <div class="info">
				      <img src="<?php echo $member['nametag']; ?>" alt="<?php echo $member['realname']; ?>님이 등록한 네임택" /> <input type="checkbox" id="deleteNameTag" name="deleteNameTag" value="1" /> <label for="deleteNameTag">네임택을 삭제합니다.</label>
				    </div>
				    <?php } ?>
					</td>
<?php } if($enableIcon) { ?>
				</tr>
				<tr>
					<th scope="row">아이콘</th>
					<td>
					  <input type="file" name="icon" class="input" /> <p class="info">16x16 이하의 이미지파일만 등록해주세요.</p>
				    <?php /* 아이콘이 등록된 경우 */
				    if($member['icon']) { ?>
				    <div class="info">
				      <img src="<?php echo $member['icon']; ?>" alt="<?php echo $member['realname']; ?>님이 등록한 아이콘" /> <input type="checkbox" id="deleteIcon" name="deleteIcon" value="1" /> <label for="deleteIcon">아이콘을 삭제합니다.</label>
				    <?php } ?>
					</td>
				</tr>
<?php } if($enablePhoto) { ?>
				<tr>
					<th scope="row">사진</th>
					<td>
					  <input type="file" name="photo" class="input" /> <p class="info">200x200 이하의 이미지파일만 등록해주세요.</p>
				    <?php if($member['photo']) { ?>
				      <div class="info">
				      <img src="<?php echo $member['photo']; ?>" alt="<?php echo $member['realname']; ?>님이 등록한 사진" class="photo" /> <input type="checkbox" id="deletePhoto" name="deletePhoto" value="1" /> <label for="deletePhoto">사진을 삭제합니다.</label>
				    <?php } ?>
					</td>
				</tr>
<?php } ?>
				<tr>
					<th scope="row">홈페이지</th>
					<td><input type="text" name="homepage" class="input" value="<?php echo $member['homepage']; ?>" style="width: 380px;" /></td>
				</tr>
				<tr>
					<th scope="row">자기소개</th>
					<td><textarea name="self_info" class="textarea" rows="5" cols="60" style="width: 380px;"></textarea> <p class="info">HTML 태그는 &lt;img&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;/ br&gt; 만 사용 가능합니다.</p></td>
				</tr>
			</table>
	  </div>
	  
	  <!-- 자동등록방지 코드 -->
	  <div class="join_code">
	    <h2>자동등록방지 코드</h2>
	    <table class="input_table" width="100%" cellspacing="0" border="0" summary="자동등록방지 코드 입력필드">
	      <tr>
					<th scope="row"><span class="antiCode"><?php echo $antiSpamKey; ?></span></th>
					<td><input type="text" name="antiSpam" class="input" /></td>
				</tr>
	    </table>
	  </div>
	  
	  <!-- 안내문구 -->
	  <div class="join_ask">
	    <p>회원약관을 꼼꼼히 읽어보시고, 필수입력항목에 정상적으로 입력하셨나요?</p>
      <p>회원가입을 하시면 <strong>회원약관에 동의하는 것으로 간주</strong>됩니다.</p>
	  </div>

	  <!-- Button -->
    <ul class="button">
      <li class="ok"><input type="image" src="./admin/theme/join/new_default/images/submit.gif" title="약관에 동의하였으며 회원가입을 완료합니다." /></li> 
    </ul>
<!-- #Form -->
</form>
<?php } ?>
<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->


<script type="text/javascript" src="js/join_check.js"></script>
</div>
