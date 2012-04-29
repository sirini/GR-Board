<?php
/****************
 * 기본 신고하기 테마
 ****************
- 이 파일은 grboard/report.php 위치에서 불려집니다.
- 스타일시트는 이 파일과 동일한 위치의 style.css 파일 내용이 반영됩니다.
 */
if(!defined('__GRBOARD__')) exit();
?>
<body>
<?php
$getPost = @mysql_fetch_array(mysql_query('select subject, content from '.$dbFIX.'bbs_'.$_GET['id'].' where no = '.$_GET['article_num']));
?>
<!-- Design BY_ STUDIO-D (www.studio-d.kr) -->
<!-- MemberInfo -->
<div id="report">
	<div class="header">
		<h1>Report - 신고하기</h1>
	</div>
	<div class="contents">
  <noscript>
	  <div id="noscript">
	    <p>Javascript를 지원하지 않는 브라우저 입니다. 지원되는 브라우저로 변경해서 다시 시도해 주세요.</p>
	  </div>
	</noscript>
	<form id="addReport" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div><input type="hidden" name="addReport" value="1" />
	<input type="hidden" name="article_num" value="<?php echo $_GET['article_num']; ?>" />
	<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" /></div>
	
	<div class="common">
		  <h2 class="nametac1">신고할 게시물</h2>
		  <table class="report_table" width="100%" cellspacing="0" border="0" summary="신고할 게시물">
		    <caption>신고할 게시물</caption>
		    <colgroup>
		      <col width="130px" />
		      <col />
		    </colgroup>
		    <thead>
		      <tr class="subject">
		        <th>게시물 제목</th>
		        <td colspan="2"><strong><?php echo strip_tags($getPost['subject']); ?></strong></td>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="subject">
		        <th>게시물 내용</th>
		        <td class="content" colspan="2">
		          <?php echo strip_tags(nl2br($getPost['content'])); ?>
		        </td>
		      <tr>
		      <tr class="subject">
		        <th>신고사유</th>
		        <td class="content" colspan="2">
		          <textarea name="reason" rows="5" cols="60" style="width: 400px;"></textarea>
		        </td>
		      <tr>
		    </tbody>
		  </table>
	</div>
   <!-- Button -->
    <ul class="button">
      <li class="ok"><input type="image" src="./admin/theme/report/new_default/images/submit.gif" title="이 게시물에 대한 신고를 완료합니다." /></li> 
    </ul>
	  </form>
</div>
