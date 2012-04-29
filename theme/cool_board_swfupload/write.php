<?php
if(!defined('__GRBOARD__')) exit();

if($mode) $writeTitle = '기존의 글을 수정합니다';
else $writeTitle = '새로운 게시물을 작성합니다';

include $theme . '/head.php';
include $theme . '/lib/write_config.php';
?>

<!-- 글작성 폼 시작 (이 부분은 수정하지 마세요) -->
<form id="write" method="post" action="<?php echo $grboard; ?>/write_ok.php" onsubmit="return checkWriteValue(<?php echo $isMember; ?>);" enctype="multipart/form-data">
<div>
	<input type="hidden" name="mode" value="<?php echo $mode; ?>" />
	<input type="hidden" name="page" value="<?php echo $page; ?>" />
	<input type="hidden" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="articleNo" value="<?php echo $articleNo; ?>" />
	<input type="hidden" name="autosaveTime" value="<?php echo time(); ?>" />
	<input type="hidden" name="isReported" value="<?php echo $isReported; ?>" />
	<input type="hidden" name="clickCategory" value="<?php echo $clickCategory; ?>" />
	<?php if($setting['secret_only']): ?><input type="hidden" name="is_secret" value="1" /><?php endif; ?>
</div>

<div class="writeTitle"><?php echo $writeTitle; ?></div>

<div class="writeRight">

<ul class="noneStyle">	

	<?php if(!$setting['secret_only']): ?>
	<li><input type="checkbox" name="is_secret" value="1" <?php echo (($modify['is_secret'])?'checked="checked"':''); ?> /> 
	<span title="체크하면 비밀글로 등록되어 작성자와 이 게시판 마스터, 관리자만이 볼 수 있습니다">비밀글로 설정</span> (글 작성자와 관리자만 볼 수 있습니다.)</li>
	<?php endif; ?>

	<?php if($setting['enable_alert']): ?>
	<li><input type="checkbox" name="is_alert" value="1" <?php echo (($modify['bad'] && $modify['bad']<-10)?'checked="checked"':''); ?> onclick="useAlert();" /> 
	<span title="체크하면 글보기 시 경고문구를 먼저 보여주고 사용자가 클릭 시 본문을 보도록 합니다">경고문구 부착</span> (글 보기시 경고문구를 클릭해야 본문이 보입니다.)</li>
	<?php endif; ?>
	
	<?php if($isAdmin || $isMaster): ?>
		<li><input type="checkbox" name="is_notice" value="1" <?php echo (($modify['is_notice'])?'checked="checked"':''); ?> /> <span>공지글로 설정</span> (이 게시판 맨 윗줄에 매달아 놓습니다.)</li>
	<?php endif; ?>

	<?php if($tmpFetchBoard['is_bomb']): 
		$getBomb = $GR->getArray("select * from {$dbFIX}time_bomb where id = '$id' and article_num = '$articleNo'");
		$bombTime = date('m월 d일 H시 i분', $getBomb['set_time']);
	?>
		<li><input type="checkbox" name="is_timebomb" value="1" <?php echo (($getBomb['no'])?'checked="checked"':''); ?> onclick="useBomb();" />
		<span title="체크하면 설정한 폭파시간 이후에 읽혀질 경우 글이 자동으로 삭제 됩니다">자동폭파 설정</span> (지정된 시간이 지나면 이 글은 삭제됩니다.)</li>
		
		<div id="setBomb" style="display: none">
			<?php if($getBomb['no']): ?>
				<span style="color: red">※ 이 게시물은 <?php echo $bombTime; ?>에 폭파되도록 설정되어 있습니다.</span>
			<?php endif; ?>
			
			<?php if(!$getBomb['no']): ?>
				<input type="text" name="bombTime" value="10" /> 
				<select name="bombTerm">
					<option value="60">분</option>
					<option value="3600">시간</option>
					<option value="86400">일</option>
				</select>
				뒤에 읽혀지면 폭파됨
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if($setting['reply_open']): ?>
	<li><input type="checkbox" name="option_reply_open" value="1" <?php echo (($modify['option_reply_open'] || !$mode)?'checked="checked"':''); ?> /> 
	<span title="체크하면 이 게시물에 댓글을 허용합니다.">댓글 허용하기</span> (이 글에 댓글입력을 허용합니다.)</li>
	<?php endif; ?>
	
</ul>
</div>

<?php if($isCategory): ?>
<ul id="inputBoxs">
	<li>
		<div class="writeRight">
			<?php echo $category; ?>
		</div>
	</li>
</ul>
<?php endif; ?>

<?php if(!$isMember): ?>

<div id="normalInput">
	<div>
		<ul class="noneStyle">

			<li><span style="padding-right: 10px"><strong>이름:</strong></span> <input type="text" name="name" class="miniInput" value="<?php echo $modify['name']; ?>" /> &nbsp;&nbsp;&nbsp;&nbsp;
			<strong>비밀번호:</strong> <input type="password" class="miniInput" name="password" /></li>

			<li>이메일: <input type="text" name="email" class="miniInput" value="<?php echo $modify['email']?>" /> &nbsp;&nbsp;&nbsp;&nbsp;
			<span style="padding-right: 4px">홈페이지:</span> <input type="text" name="homepage" class="miniInput" value="<?php echo $modify['homepage']; ?>" /></li>

			<li><strong>자동입력방지:</strong> <input type="text" name="antispam" class="input" style="width: 118px" /> (<strong><?php echo $antiSpam0.$antiSpam3.$antiSpam1; ?>=?</strong> 의 답을 입력해 주세요.)</li>
		</ul>
	</div>
</div>

<?php endif; ?>


<ul class="noneStyle">

	<li><span style="padding-right: 7px"><strong>제 &nbsp; 목:</strong></span> <input type="text" name="subject" size="73" class="input" value="<?php echo $subject?>" /></li>

	<?php if($setting['enable_tag']): ?>
	<li><span style="padding-right: 5px">꼬 리 표:</span> <input type="text" name="tag" class="input" onkeydown="tagAssist(this.value, '<?php echo $id; ?>');" style="width: 350px" value="<?php echo $modify['tag']; ?>" title="태그(tag/꼬리표)를 통해 글의 핵심단어를 보여줄 수 있습니다." /> ( <strong>,</strong> 콤마로 단어 구분)
	<div id="searchTags" style="display: none"></div></li>
	<?php endif; ?>

	<?php if($setting['enable_link']): ?>
	<li><span style="padding-right: 5px">링크 # 1:</span> <input type="text" name="link1" size="73" class="input" value="<?php echo $modify['link1']; ?>" /></li>
	<li><span style="padding-right: 5px">링크 # 2:</span> <input type="text" name="link2" size="73" class="input" value="<?php echo $modify['link2']; ?>" /></li>
	<?php endif; ?>

</ul>


<div id="fileUploadField">
	<div>
		<ul class="noneStyle">

			<?php if(isset($totalFiles)): ?>

				<?php for($tmp=1; $tmp<=$totalFiles; $tmp++): ?>
					
					<li><span style="padding-right: 8px">파일 #<?php echo $tmp; ?>:</span> <input type="file" name="file<?php echo $tmp; ?>" class="input" /></li>
				
					<?php if($oldFile[$tmp-1]): ?>
					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="fileExist"><strong><?php echo end(explode('/', $oldFile[$tmp-1])); ?></strong> 은 이미 올려져 있습니다.
					<input type="checkbox" name="delete<?php echo $tmp; ?>" value="<?php echo $oldFile[$tmp-1]; ?>">삭제하기</span></li>
					<?php endif; ?>
					
				<?php endfor; ?>

				<?php
				if($mode == 'modify'): /* 추가 첨부파일이 올려져 있을 때 (글수정시) @컴센스, @이동규 */
					$getExtendPds = $GR->query("select no, file_route from ".$dbFIX."pds_extend where id = '".$id."' and article_num = ".$articleNo);
					while($extPds = $GR->fetch($getExtendPds)):
						$getPdsList = $GR->getArray('select no, name from '.$dbFIX.'pds_list where type = 1 and uid = '.$extPds['no']);
						if($getPdsList['no']) $filename = end(explode('/', $getPdsList['name']));
				?>
					<li>              
						<span class="fileExist">+ <strong><?php echo $filename;  ?></strong> 이 추가로 첨부되어 있습니다.
						<input type="checkbox" name="deleteExtendPds[]" value="<?php echo $extPds['no']; ?>">삭제하기</span>
					</li>

				<?php endwhile; endif; ?>

				<li>
					<div class="extendUploadBtn">
						<span id="swfUpBtnforGRBOARD"></span> 
						<input id="btnCancel" type="button" value="멀티업로드 취소" onclick="swfu.cancelQueue();" disabled="disabled" title="클릭하시면 멀티업로드로 업로드중이던 파일 전송을 취소합니다." />
					</div>
					<div id="extendUploads"></div>
					<div id="flashHistory" style="display: none"><div id="fsUploadProgress"></div></div>
					<div id="divStatus">0 Files Uploaded</div>
				</li>

			<?php endif; ?>

		</ul>		
	</div>	
</div>


<div id="editableBox">
	<textarea name="content" class="textarea" rows="35"><?php echo $content; ?></textarea>
</div>


<div style="padding-top: 15px">
	
	<?php if($setting['enable_dic']): ?><input type="button" class="roundBtn" id="miniDicBtn" value="미니사전" title="다음 미니사전 열기" /><?php endif; ?>
	
	<?php if($setting['enable_poll']): ?><input type="button" class="roundBtn" value="설문조사" onclick="inputPoll('<?php echo $grboard.'/'.$theme; ?>', '<?php echo $id; ?>');" title="설문조사를 작성합니다. 클릭 후 팝업창이 뜨면 그 곳에 안내된 대로 설문을 작성해서 넣어보세요." /><?php endif; ?> 
	
	<?php if($setting['enable_save']): ?>
		<input type="button" class="roundBtn" id="recoveryPostBtn" value="글 복구" title="마지막으로 저장된 글을 가져옵니다." /> 
		<input type="button" class="roundBtn" value="임시저장" onclick="autosave();" title="임시로 글제목과 내용을 저장하고, 계속해서 글을 작성합니다. (자주 눌러주세요!)" />
	<?php endif; ?> 
	
	<input type="submit" class="roundBtn" value="작성완료" accesskey="s" title="글을 작성 완료 합니다." /> 
	
	<input type="button" class="roundBtn" value="작성취소" onclick="isCancel('<?php echo $id; ?>');" title="게시물 작성을 취소합니다" />

</div>

</form>

<?php if($setting['enable_save']): ?>
<div id="writePreviewBox"><img src="<?php echo $grboard; ?>/image/icon/poll_icon.gif" alt="" /> [임시저장] 버튼을 자주 눌러주세요. 불의의 사고로 작성중인 글이 삭제되는 것을 방지합니다.</div>
<?php endif; ?>

<script>
var USE_EDITOR = false;
</script>

<?php if($tmpFetchBoard['is_editor']): ?>
<script src="<?php echo $grboard; ?>/tiny_mce/tiny_mce.js"></script>
<script>
tinyMCE.init({
	language : "ko",

	// General options
	mode : "textareas",
	theme : "advanced",
	plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

	// Theme options
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_fonts : "굴림=굴림;굴림체=굴림체;궁서=궁서;궁서체=궁서체;돋움=돋움;돋움체=돋움체;바탕=바탕;바탕체=바탕체;맑은고딕=Malgun Gothic;나눔고딕=나눔고딕;나눔명조=나눔명조;다음체=다음_Regular;Arial=Arial; Comic Sans MS='Comic Sans MS';Courier New='Courier New';Tahoma=Tahoma;Times New Roman='Times New Roman';Verdana=Verdana",

	// Example content CSS (should be your site CSS)
	content_css : "<?php echo $grboard.'/'.$theme; ?>/edit.css",

	// Drop lists for link/image/media/template dialogs
	template_external_list_url : "lists/template_list.js",
	external_link_list_url : "lists/link_list.js",
	external_image_list_url : "lists/image_list.js",
	media_external_list_url : "lists/media_list.js",

	// Style formats
	style_formats : [
		{title : 'Bold text', inline : 'strong'},
		{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
		{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
		{title : 'Example 1', inline : 'span', classes : 'example1'},
		{title : 'Example 2', inline : 'span', classes : 'example2'},
		{title : 'Table styles'},
		{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
	],

	media_use_script : true,
	paste_strip_class_attributes : "all",
	paste_remove_spans : false,
	paste_remove_styles : false,
	forced_root_block : false,
	force_br_newlines : true,
	force_p_newlines : false,
	convert_urls : false,

	// Replace values for the template plugin
	template_replace_values : {
		username : "Some User",
		staffid : "991234"
	}
});

var USE_EDITOR = true;
var GRBOARD = '<?php echo $grboard; ?>';
var THEME = '<?php echo $theme; ?>';
var BBS_ID = '<?php echo $id; ?>';
var SESS_ID = '<?php echo session_id(); ?>';
</script>
<?php endif; ?>

<script src="<?php echo $grboard; ?>/js/jquery.js"></script>
<script src="<?php echo $grboard; ?>/js/swfupload.js"></script> 
<script src="<?php echo $grboard; ?>/js/swfupload.queue.js"></script>
<script src="<?php echo $grboard; ?>/js/fileprogress.js"></script>
<script src="<?php echo $grboard; ?>/js/handlers.js"></script>
<script src="<?php echo $grboard.'/'.$theme; ?>/write.js"></script>
</div><!--# 게시판 끝 -->