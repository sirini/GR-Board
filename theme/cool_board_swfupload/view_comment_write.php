<?php if(!defined('__GRBOARD__')) exit(); ?>

<div id="layerCoWrite">

<form id="commentWrite" onsubmit="return valueCheck(<?php echo $isMember; ?>);" method="post" action="<?php echo $grboard; ?>/comment_write_ok.php">
<div><input type="hidden" name="articleNo" value="<?php echo $articleNo; ?>" />
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<input type="hidden" name="page" value="<?php echo $page; ?>" />
<input type="hidden" name="modifyTarget" value="<?php echo $modifyTarget; ?>" />
<input type="hidden" name="commentPage" value="<?php echo $commentPage; ?>" />
<input type="hidden" name="replyTarget" value="<?php echo $replyTarget; ?>" />
<input type="hidden" name="useCoEditor" value="<?php echo (($tmpFetchBoard['is_comment_editor'])?1:0); ?>" />
<input type="hidden" name="clickCategory" value="<?php echo $clickCategory; ?>" /></div>

<table rules="none" summary="GR Board Write Comment" cellpadding="0" cellspacing="0" border="0" class="commentWriteBox">
<caption></caption>
<colgroup>
	<col style="width:120px" />
	<col />
</colgroup>
<tbody>

<?php if(!$isMember): ?>

<tr>
	<td class="cWriteLeft">이름</td>
	<td class="cWriteRight"><input type="text" name="name" class="miniInput" maxlength="20" value="<?php echo $comment['name']; ?>" /></td>
</tr>
<tr>
	<td class="cWriteLeft">비밀번호</td>
	<td class="cWriteRight"><input type="password" name="password" maxlength="40" class="miniInput" /></td>
</tr>
<tr>
	<td class="cWriteLeft">이메일</td>
	<td class="cWriteRight"><input type="text" name="email" class="miniInput" maxlength="250" value="<?php echo $comment['email']; ?>" /></td>
</tr>
<tr>
	<td class="cWriteLeft">홈페이지</td>
	<td class="cWriteRight"><input type="text" name="homepage" maxlength="250" class="miniInput" value="<?php echo $comment['homepage']; ?>" /></td>
</tr>
<tr>
	<td class="cWriteLeft">자동등록방지</td>
	<td class="cWriteRight"><input type="text" name="antispam" class="input" style="width: 100px" /> (<strong><?php echo $antiSpam0.$antiSpam3.$antiSpam1; ?>=?</strong> 의 답을 입력해 주세요.)</td>
</tr>

<?php endif; ?>

<tr>
	<td class="cWriteLeft">
	
	<?php if($_SESSION['no']): ?>
		<input type="checkbox" id="is_secret" name="is_secret" value="1" <?php echo (($comment['is_secret'])?'checked="checked"':''); ?> />
		<label for="is_secret" title="게시물 작성자만 볼 수 있도록 비밀 댓글을 작성합니다." style="cursor: help">비밀글</label>
	<?php endif; ?>
	
	</td>
	
	<td class="cWriteRight">
		<div style="width:79%; float: left">
			<div id="editableCoBox">
				<textarea name="content" class="commentTextarea" style="height: 150px"><?php echo $comment['content']; ?></textarea>
			</div>
			
			<?php if(!$tmpFetchBoard['is_comment_editor']): ?>
				<div id="grcodeButton" style="display:<?php echo (($comment['is_grcode'])?'':'none'); ?>">
				<span class="hand" onclick="quickTag('[b]', '[/b]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_bold.gif" alt="굵게" title="드래그한 글자 굵게" /></span>
				<span class="hand" onclick="quickTag('[i]', '[/i]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_i.gif" alt="기울게" title="드래그한 글자 기울게" /></span>
				<span class="hand" onclick="quickTag('[img]', '[/img]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_img.gif" alt="그림넣기" title="드래그한 URI주소가 그림주소이며 출력하기" /></span>
				<span class="hand" onclick="quickTag('[big]', '[/big]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_big.gif" alt="글자크게" title="드래그한 글자 크게" /></span>
				<span class="hand" onclick="quickTag('[color:blue:]', '[/color]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_color.gif" alt="색상" title="드래그한 글자 색깔을 blue 로 하기 (red, green, #2e4f4f 등 가능)" /></span>
				<span class="hand" onclick="quickTag('[div]', '[/div]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_div.gif" alt="문단꾸미기" title="드래그한 문단을 박스모양 안에 담기" /></span>
				<span class="hand" onclick="quickTag('[u]', '[/u]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_underline.gif" alt="밑줄" title="드래그한 글자에 밑줄치기" /></span>
				<span class="hand" onclick="quickTag('[s]', '[/s]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_midline.gif" alt="취소선" title="드래그한 글자에 취소선 긋기" /></span>
				<span class="hand" onclick="quickTag('[quote]', '[/quote]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_quote.gif" alt="인용" title="드래그한 문단을 인용 표시하기" /></span>
				<span class="hand" onclick="quickTag('[url]', '[/url]')"><img src="<?php echo $grboard.'/'.$theme; ?>/image/icon_url.gif" alt="링크" title="드래그한 문장에 링크걸기" /></span>
				</div>
				
				<!-- 이모티콘 입력 (Emoticon by phpBB - http://phpbb.com) -->
				<div id="emoticon" style="display: none">
					<span class="hand" onclick="emoticon(' :D ');"><img src="image/emoticon/icon_biggrin.gif" alt="행복해" title="행복해" /></span>
					<span class="hand" onclick="emoticon(' :) ');"><img src="image/emoticon/icon_smile.gif" alt="미소" title="미소" /></span>
					<span class="hand" onclick="emoticon(' :( ');"><img src="image/emoticon/icon_sad.gif" alt="슬퍼요" title="슬퍼요" /></span>
					<span class="hand" onclick="emoticon(' :o ');"><img src="image/emoticon/icon_surprised.gif" alt="놀람" title="놀람" /></span>
					<span class="hand" onclick="emoticon(' :shock: ');"><img src="image/emoticon/icon_eek.gif" alt="쇼크" title="쇼크" /></span>
					<span class="hand" onclick="emoticon(' :? ');"><img src="image/emoticon/icon_confused.gif" alt="혼란" title="혼란" /></span>
					<span class="hand" onclick="emoticon(' 8) ');"><img src="image/emoticon/icon_cool.gif" alt="시원함" title="시원함" /></span>
					<span class="hand" onclick="emoticon(' :lol: ');"><img src="image/emoticon/icon_lol.gif" alt="웃음" title="웃음" /></span>
					<span class="hand" onclick="emoticon(' :x ');"><img src="image/emoticon/icon_mad.gif" alt="미친" title="미친" /></span>
					<span class="hand" onclick="emoticon(' :P ');"><img src="image/emoticon/icon_razz.gif" alt="냉소" title="냉소" /></span>
					<span class="hand" onclick="emoticon(' :oops: ');"><img src="image/emoticon/icon_redface.gif" alt="당황" title="당황" /></span>
					<span class="hand" onclick="emoticon(' :cry: ');"><img src="image/emoticon/icon_cry.gif" alt="울음" title="울음" /></span>
					<span class="hand" onclick="emoticon(' :evil: ');"><img src="image/emoticon/icon_evil.gif" alt="사악함" title="사악함" /></span>
					<span class="hand" onclick="emoticon(' :twisted: ');"><img src="image/emoticon/icon_twisted.gif" alt="비틀어진 사악함" title="비틀어진 사악함" /></span>
					<span class="hand" onclick="emoticon(' :roll: ');"><img src="image/emoticon/icon_rolleyes.gif" alt="눈굴림" title="눈굴림" /></span>
					<span class="hand" onclick="emoticon(' :wink: ');"><img src="image/emoticon/icon_wink.gif" alt="윙크" title="윙크" /></span>
					<span class="hand" onclick="emoticon(' :!: ');"><img src="image/emoticon/icon_exclaim.gif" alt="느낌표" title="느낌표" /></span>
					<span class="hand" onclick="emoticon(' :?: ');"><img src="image/emoticon/icon_question.gif" alt="물음표" title="물음표" /></span>
					<span class="hand" onclick="emoticon(' :idea: ');"><img src="image/emoticon/icon_idea.gif" alt="아이디어" title="아이디어" /></span>
					<span class="hand" onclick="emoticon(' :arrow: ');"><img src="image/emoticon/icon_arrow.gif" alt="화살표" title="화살표" /></span>
					<span class="hand" onclick="emoticon(' :| ');"><img src="image/emoticon/icon_neutral.gif" alt="무표정" title="무표정" /></span>
					<span class="hand" onclick="emoticon(' :mrgreen: ');"><img src="image/emoticon/icon_mrgreen.gif" alt="초록 아저씨" title="초록 아저씨" /></span>
				</div>
			<?php endif; ?>
		</div>
		
		<div style="width: 19%; float: left">
			<input type="image" src="<?php echo $grboard.'/'.$theme; ?>/image/comment_write_ok.gif" onmouseover="this.src='<?php echo $grboard.'/'.$theme; ?>/image/comment_write_ok_rollover.gif'" onmouseout="this.src='<?php echo $grboard.'/'.$theme; ?>/image/comment_write_ok.gif'" title="댓글을 작성완료 합니다" />
		</div>
		
	</td>
	
</tr>
</tbody>
</table>

</form>
</div>

<?php if($tmpFetchBoard['is_comment_editor']): ?>
<script src="<?php echo $grboard; ?>/tiny_mce/tiny_mce.js"></script>
<script>
	tinyMCE.init({
	language: "ko",
	mode : "textareas",
	content_css : "<?php echo $grboard.'/'.$theme; ?>/edit.css",
	theme : "advanced",
	plugins : "emotions,inlinepopups,media",
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,forecolor,|,emotions,image,media,bullist,numlist,link,code",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_buttons4 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	media_use_script : true,
	paste_strip_class_attributes : "all",
	paste_remove_spans : false,
	paste_remove_styles : false,
	forced_root_block : false,
	force_br_newlines : true,
	force_p_newlines : false,
	convert_urls : false
	});
</script>
<?php endif; ?>
