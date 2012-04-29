<?php
/****************
 * 기본 멤버 정보확인 테마
 ****************
- 이 파일은 grboard/info.php 위치에서 불려집니다.
- GR보드 기본 멤버 정보확인 테마로서, 상/하단에 게시판 상/하단 HTML 코드(파일)가 그대로 나옵니다.
- 스타일시트는 이 파일과 동일한 위치의 style.css 파일 내용이 반영됩니다.
 */
if(!defined('__GRBOARD__')) exit();

?>

<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->
<!-- MemberInfo -->
<div id="memberInfo">
	<div class="header">
		<h1>My information - 회원정보 관리</h1>
	</div>
<form id="info" onsubmit="return valueOk(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
	<div class="contents">
		<div><input type="hidden" name="modifyMemberInfo" value="1" />
		<input type="hidden" name="targetMemberNo" value="<?php echo $member['no']; ?>" />
		<input type="hidden" name="isEnableJumin" value="<?php echo $enableJumin; ?>" />
		<input type="hidden" name="boardId" value="<?php echo $boardId; ?>" /></div>
		<noscript>
	  <div id="noscript">
	    <p>Javascript를 지원하지 않는 브라우저 입니다.</p>
	    <p>회원정보 수정과 탈퇴가 불가능할 수 있습니다.</p>
	  </div>
	</noscript>
<!-- 기본정보 -->
		<div class="common">
			<h2>기본정보</h2>
			<p class="notice"><span class="mark">(필수)</span>표시는 필수 입력 사항입니다.</p>
			<table class="input_table" width="100%" cellspacing="0" border="0" summary="기본정보 입력필드">
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> ID(아이디)</th>
					<td><?php echo $member['id']; ?></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 비밀번호</th>
					<td>
						<input type="password" name="password" class="input" /> <p class="info">입력 시 새 비밀번호로 수정됩니다.</p>
						<p>보다 안전한 개인정보 관리를 위해, 주기적으로 비밀번호를 변경해 주세요.</p>
					</td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 이름(실명)</th>
					<td><input type="text" id="realname" name="realname" class="input" value="<?php echo $member['realname']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 닉네임(별명)</th>
					<td><input type="text" id="nickname" name="nickname" class="input" value="<?php echo $member['nickname']; ?>" style="float: left;" />  <a href="#noscript" class="nickname_check" onclick="alreadyNickCheck(); return false;" title="입력하신 닉네임이 이미 등록되어 있는지 확인합니다.">[닉네임 중복확인]</a></td>
				</tr>
<?php /* 주민등록번호 사용시 */
if($enableJumin) { ?>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> 주민등록번호</th>
					<td>
						<?php /* 주민등록번호가 입력되지 않은 경우 */
						if($enableJumin & $member['jumin'] == NULL) { ?>
						<script type="text/javascript">
              alert("주민등록번호 입력이 되어있지 않습니다. \n주민등록번호를 입력해주세요. ");
              window.onload = function(){
                var form = document.memberInfo;
                form.jumin.focus();
              } 
            </script>
						<input type="password" id="jumin" name="jumin" value="<?php echo $member['jumin']; ?>" class="input" /> <p class="info">하이픈 '-' 없이 입력하세요.</p>
						<?php /* 주민등록번호가 입력된 경우 */
						} else { ?>
						<p class="jumin_ok" style="padding-left: 10px;">주민등록번호 입력 확인되었습니다.</p>
						<?php } ?>
					</td>
				</tr>
<?php } ?>
				<tr>
					<th scope="row" class="mark"><span class="mark">(필수)</span> Email</th>
					<td><input type="text" id="email" name="email" class="input" value="<?php echo $member['email']; ?>" style="width: 380px;" /></td>
				</tr>
				<tr>
					<th scope="row">레벨</th>
					<td><?php echo $member['level']; ?></td>
				</tr>
				<tr>
					<th scope="row">포인트</th>
					<td><?php echo $member['point']; ?></td>
				</tr>
				<tr>
					<th scope="row">등록일</th>
					<td><?php echo date('Y년m월d일,  H시i분s초', $member['make_time']); ?>에 등록되셨습니다.</td>
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
				</tr>
<?php } if($enableIcon) { ?>
				<tr>
					<th scope="row">아이콘</th>
					<td>
					  <input type="file" name="icon" class="input" /> <p class="info">16x16 이하의 이미지파일만 등록해주세요.</p>
				    <?php /* 아이콘이 등록된 경우 */
				    if($member['icon']) { ?>
				    <div class="info">
				      <img src="<?php echo $member['icon']; ?>" alt="<?php echo $member['realname']; ?>님이 등록한 아이콘" /> <input type="checkbox" id="deleteIcon" name="deleteIcon" value="1" /> <label for="deleteIcon">아이콘을 삭제합니다.</label>
				    </div>
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
				      </div>
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
					<td><textarea name="self_info" class="textarea" rows="5" cols="60" style="width: 380px;"><?php echo htmlspecialchars($member['self_info']); ?></textarea> <p class="info">HTML 태그는 &lt;img&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;br /&gt; 만 사용 가능합니다.</p></td>
				</tr>
			</table>
	  </div>
<!-- 최근 로그인 기록 -->
	  <div class="login">
	    <h2>최근 로그인 기록</h2>
	    <div>
	      <ol>
	      <?php 
				  $getLatestLogin = @mysql_query('select * from '.$dbFIX.'login_log where member_key = '.$member['no'].' order by no desc limit 10');
				  while($loginList = @mysql_fetch_array($getLatestLogin)) {
					  echo '<li>'.date('Y/m/d H:i:s', $loginList['signdate']).' (IP: '.$loginList['ip'].', Referer: '.$loginList['ref'].')</li>';
				  } ?>
			  </ol>
			  <p class="info"><strong><?php echo $member['realname']; ?></strong>님의 최근 로그인 기록을 확인하실 수 있습니다.</p>
			  <p class="info">기록조회는 <strong>최대 10개</strong>까지이며, 만약 자신이 위 시간에 로그인 한 적이 없다면, 해킹을 의심해볼 수 있습니다.</p>
			  <p class="info">이 경우, 비밀번호를 변경함으로써 대책을 마련할 수 있습니다.</p>
			</div>
	  </div>
	  <!-- Button -->
    <ul class="button">
      <li class="delete"><a href="#noscript" onclick="javascript:outMe(); return false;">탈퇴</a></li>
      <li class="ok"><input type="image" src="./admin/theme/info/new_default/images/ok.gif" title="정보를 수정합니다." /></li> 
      <li class="cancel">
      <?php if(!$boardId) { ?>
      <a href="#noscript" onclick="window.close(); return false;"><span>취소</span></a><?php } else { ?>
      <a href="#noscript" onclick="history.go(1); return false;"><span>취소</span></a><?php } ?>
      </li>
    </ul>
</div>
<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->
