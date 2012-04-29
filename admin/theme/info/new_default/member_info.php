<!-- 가운데 정렬 -->
<div id="memberInfo">

	<!-- 폭 설정 (기본값 사용) -->
	<div style="padding: 5px">

		<!-- 타이틀 -->
		<div class="bigTitle">Member Information</div>

		<!-- 정보 보기 박스 -->
		<fieldset>
			<legend class="legend"><?php echo $member['nickname']; ?>님의 신상명세서</legend>
			
			<?php if($_SESSION['no'] == 1) {?>
			<div class="tableListLine">
				<div class="tableLeft">ID</div>
				<div class="tableRight"><?php echo $member['id']; ?> &nbsp;
				<a href="./admin_member.php?memberID=<?php echo $member['id']; ?>&page=1#admModifyMember" onclick="window.open(this.href, '_blank'); return false;" onfocus="this.blur()" title="<?php echo $member['realname']; ?> 님의 회원정보를 관리합니다.">[회원관리]</a>
				<p>일반사용자에게 ID가 유출되지 않도록 해주세요.</p>
				</div>
				<div class="clear"></div>
			</div>
		  <?php } ?>
			
			<div class="tableListLine">
				<div class="tableLeft">닉네임</div>
				<div class="tableRight"><?php echo strip_tags($member['nickname']); ?> <a href="send_memo.php?target=<?php echo $member['no']; ?>" onfocus="this.blur()" title="<?php echo $member['realname']; ?> 님에게 쪽지를 보냅니다">[쪽지보내기]</a></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">실명</div>
				<div class="tableRight"><?php echo $member['realname']; ?></div>
				<div class="clear"></div>
			</div>
		  
			<div class="tableListLine">
				<div class="tableLeft">레벨</div>
				<div class="tableRight"><?php echo $member['level']; ?></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">포인트</div>
				<div class="tableRight"><?php echo $member['point']; ?></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">등록일</div>
				<div class="tableRight"><?php echo date("Y.m.d H:i:s", $member['make_time']); ?></div>
				<div class="clear"></div>
			</div>

			<?php if($member['nametag']) { ?>
			<div class="tableListLine">
				<div class="tableLeft">네임택</div>
				<div class="tableRight"><img src="<?php echo $member['nametag']; ?>" border="0" alt="" title="" /></div>
				<div class="clear"></div>
			</div>

			<?php } if($member['photo']) { ?>
			<div class="tableListLine">
				<div class="tableLeft">사진</div>
				<div class="tableRight"><img src="<?php echo $member['photo']; ?>" border="0" alt="" title="" /></div>
				<div class="clear"></div>
			</div>

			<?php } ?>

			<div class="tableListLine">
				<div class="tableLeft">이메일</div>
				<div class="tableRight"><?php echo hide_email($member['email']); ?></div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">홈페이지</div>
				<div class="tableRight">
					<a href="<?php echo $member['homepage']; ?>" onfocus="this.blur()" onclick="window.open(this.href, '_blank'); return false;"><?php echo $member['homepage']; ?></a>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft">자기소개</div>
				<div class="tableRight"><?php echo nl2br($member['self_info']); ?></div>
				<div class="clear"></div>
			</div>

		</fieldset><!--# 정보 보기 박스 -->

		<!-- 위아래 공백 -->
		<div class="vSpace"></div>

	</div><!--# 폭 설정 -->
</div><!--# 가운데 정렬 -->
