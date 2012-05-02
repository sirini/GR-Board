<?php
// 관리자인지 확인한다.
if($_SESSION['no'] != 1) { 
	header('HTTP/1.1 406 Not Acceptable');
	exit('관리자만 접근가능합니다.'); 
}

if(!defined('__GRBOARD__')) exit();

			// 선택한 게시판의 정보를 가져온다.
			$modifyData = $GR->getArray("select * from {$dbFIX}board_list where id = '$boardID'");
			?>
		<div class="mvBack" id="admModifyBoard">
			<div class="mv">게시판 수정</div>
			<form id="modifyBoard" onsubmit="return isModifyValue(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<div><input type="hidden" name="isModifyBoard" value="1" />
			<input type="hidden" name="targetBoardNo" value="<?php echo $modifyData['no']; ?>" />
			<input type="hidden" name="boardID" value="<?php echo $boardID; ?>" />
			<input type="hidden" name="categoryAll" value="<?php echo $modifyData['category']; ?>" />
			<input type="hidden" name="page" value="<?php echo $page; ?>" />
			<input type="hidden" name="deleteCate" value="" /></div>
			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판이 속할 그룹을 지정합니다.">그룹지정</div>
				<div class="tableRight">
					<select name="modifyGroup">
					<?php
					$getGroup = $GR->query('select no, name from '.$dbFIX.'group_list');
					while($group = $GR->fetch($getGroup)) { ?>
						<option value="<?php echo $group['no']; ?>" <?php echo ($group['no']==$modifyData['group_no'])?'selected="selected"':''; ?>><?php echo $group['name']; ?></option>
					<?php } ?>
					</select> (아래 '모두 변경' 클릭 시 지정된 그룹 내 각 게시판 설정을 동일하게 변경 합니다.)
				</div>
				<div class="clear"></div>
			</div>
			<div class="tableListLine">
				<div class="tableLeft" title="테마(스킨) 으로 여러가지 용도의 활용이 가능합니다.">테마(스킨) 선택</div>
				<div class="tableRight">
				<select name="modifyTheme" onchange="themePreview(this.value);">
				<?php
				$themeListArr = array();
				$openThemeDir = @opendir('theme');
				while($themeList = @readdir($openThemeDir)) {
					if($themeList != '.' && $themeList != '..') $themeListArr[] = $themeList;
				}
				@closedir($openThemeDir);
				$cntTheme = @count($themeListArr);
				sort($themeListArr);
				for($th=0; $th<$cntTheme; $th++) {
					echo '<option value="'.$themeListArr[$th].'"'.(($modifyData['theme']==$themeListArr[$th])?' selected="selected"':'').'>'.$themeListArr[$th].'</option>';
				}
				?>
				</select>
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다."><input type="checkbox" name="sameModifyTheme" value="1" /> 모두 변경</span>
				<div id="themePreview"></div>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="한번 작성된 게시판 ID 는 수정하실 수 없습니다.">게시판 ID</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyBoardId" maxlength="48" value="<?php echo $modifyData['id']; ?>" readonly="readonly" />
					<a href="board.php?id=<?php echo $modifyData['id']; ?>">[미리보기]</a>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="게시판 이름을 적어주세요. (예: 자유게시판)">게시판 이름</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyBoardName" maxlength="50" value="<?php echo $modifyData['name']; ?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="목록에서 게시물 내용을 포함하여 모든 정보를 가져옵니다. 방명록 형태로 사용할 때 체크하세요.">방명록 형태</div>
				<div class="tableRight">
					<input type="checkbox" name="modifyIsFull" value="1" <?php if($modifyData['is_full']) { echo 'checked="checked"'; } ?> />  
					목록에서 게시물 내용까지 모두 가져옵니다. (<strong>속도저하</strong> / 방명록형태일 때 체크)
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsFull" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="체크하시면 게시물 내용을 볼 때 하단에 게시물 목록을 함께 출력합니다.">게시물 목록보기</div>
				<div class="tableRight">
					<input type="checkbox" name="modifyIsList" value="1" <?php if($modifyData['is_list']) { echo 'checked="checked"'; } ?> />  
					게시물을 볼 때 하단에 게시물 목록을 같이 볼지 설정합니다.
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsList" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="제목을 일정 글자수 이하로 줄일 수 있습니다.">제목 자르기</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="3" name="modifyCutSubject" value="<?php echo $modifyData['cut_subject']; ?>" />  
					글 목록에서 제목을 일정 길이로 자릅니다. (0 : 사용안함)
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyCutSubject" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
				
			<div class="tableListLine">
				<div class="tableLeft" title="한페이지에 출력할 게시물 개수를 입력하세요. (1 ~ 999)">페이지당 목록수</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="3" name="modifyPageNum" value="<?php echo $modifyData['page_num']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyPageNum" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="한페이지에 출력할 페이지 묶음수를 입력하세요. (1 ~ 999)">페이지 표시 수</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="3" name="modifyPagePerList" value="<?php echo $modifyData['page_per_list']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyPagePerList" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="코멘트 볼 때 한페이지에 출력할 코멘트 개수를 입력하세요. (1 ~ 999)">코멘트 목록 수</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="3" name="modifyCommentPageNum" value="<?php echo $modifyData['comment_page_num']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyCommentPageNum" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="코멘트 볼 때 한페이지에 출력할 코멘트 페이지 묶음수를 입력하세요. (1 ~ 999)">코멘트 표시 수</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="3" name="modifyCommentPagePerList" value="<?php echo $modifyData['comment_page_per_list']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyCommentPagePerList" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="게시물을 특정 시간 이후로 삭제가 불가능하도록 정합니다.">게시물 삭제제한</div>
				<div class="tableRight">
					<input type="text" class="input" size="3" maxlength="2" name="modifyFixTime" value="<?php echo $modifyData['fix_time']; ?>" />
					(기본 0: 사용하지 않음 / 1~99시간 사이에서 정하여 넣으면 됩니다.)
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyFixTime" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 달리는 코멘트들의 정렬방식을 정합니다.">코멘트 정렬 방식</div>
				<div class="tableRight">
					<input type="radio" value="1" name="modifyCommentSort" <?php echo (($modifyData['comment_sort'])?'checked="checked"':''); ?> /> 
					먼저 작성된 것이 제일 위로 
					<input type="radio" value="0" name="modifyCommentSort" <?php echo ((!$modifyData['comment_sort'])?'checked="checked"':''); ?> /> 
					마지막에 작성된 것이 제일 위로 <span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyCommentSort" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 올려진 글들을 RSS로 외부출력 허용할 것인지 정합니다.">RSS 외부출력</div>
				<div class="tableRight">
					<input type="checkbox" value="1" name="modifyIsRss" <?php echo (($modifyData['is_rss'])?'checked="checked"':''); ?> /> 
					체크하시면 이 게시판 글들을 RSS 로 외부에 보냅니다. <span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsRss" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="일반 사용자가 html 을 사용할 수 있도록 할 것인지, 어떤 태그를 허용할 건지 정합니다.">HTML 허용</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyIsHtml" maxlength="200" style="width: 180px" value="<?php echo $modifyData['is_html']; ?>" />
					비어있을 경우 html 태그를 모두 허용하지 않습니다.
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsHtml" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="글 작성시 쉽게 본문을 편집할 수 있도록 합니다.">웹에디터 사용</div>
				<div class="tableRight">
					<input type="checkbox" value="1" name="modifyIsEditor" <?php echo (($modifyData['is_editor'])?'checked="checked"':''); ?> />
					체크하지 않을 경우 웹에디터 대신 GR코드를 사용 합니다.
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsEditor" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="댓글 작성시 간단한 편집을 할 수 있도록 합니다.">댓글 웹에디터</div>
				<div class="tableRight">
					<input type="checkbox" value="1" name="modifyIsCoEditor" <?php echo (($modifyData['is_comment_editor'])?'checked="checked"':''); ?> />
					체크할 경우 댓글(코멘트)을 달 때 간단한 위지윅 에디터를 사용하도록 합니다.
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsCoEditor" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="게시판 출력 이전에 출력할 파일의 위치를 적어 넣습니다. 제일 처음 출력됩니다.">상단 파일</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyHeadFile" maxlength="250" value="<?php echo $modifyData['head_file']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyHeadFile" value="1" /> 모두 변경</span>
					<input type="checkbox" name="forLayoutHead" value="layout/<?php echo $layout['var']; ?>/head.board.php" id="forHead" onfocus="setForLayout(this, 'modifyHeadFile');" /><label for="forHead">레이아웃용으로 설정</label>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="게시판 출력 이전에 출력할 내용을 적습니다. 상단파일 다음으로 출력됩니다." style="height: 150px">상단 내용</div>
				<div class="tableRight">
					<textarea name="modifyHeadForm" class="textarea" rows="7" cols="90"><?php echo str_replace('&', '&amp;', $modifyData['head_form']); ?></textarea>
					<div><span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyHeadForm" value="1" /> 모두 변경</span></div>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="게시판을 부른 후 출력할 내용을 적습니다. 게시판 출력 이후에 바로 출력됩니다." style="height: 150px">하단 내용</div>
				<div class="tableRight">
					<textarea name="modifyFootForm" class="textarea" rows="7" cols="90"><?php echo str_replace('&', '&amp;', $modifyData['foot_form']); ?></textarea>
					<div><span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyFootForm" value="1" /> 모두 변경</span></div>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="게시판을 부른 후 출력할 파일의 위치를 적어 넣습니다. 제일 마지막에 출력됩니다.">하단 파일</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyFootFile" maxlength="250" value="<?php echo $modifyData['foot_file']; ?>" /> 
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyFootFile" value="1" /> 모두 변경</span>
					<input type="checkbox" name="forLayoutFoot" value="layout/<?php echo $layout['var']; ?>/foot.board.php" id="forFoot" onfocus="setForLayout(this, 'modifyFootFile');" /><label for="forFoot">레이아웃용으로 설정</label>
				</div>
				<div class="clear"></div>
			</div>

			<div id="categoryForm" class="tableListLine">
				<div class="tableLeft" title="카테고리를 새로이 추가 (혹은 수정) 합니다. 카테고리(분류)가 너무 많으면 뒤에 생긴 것들이 짤립니다. 적당한 갯수로 분류를 조절해 주세요.">카테고리</div>
				<div class="tableRight">
					<div><?php
					if($modifyData['category']) {
						echo '<ul class="cate"><li><span onclick="deleteCateAll();" title="이 곳을 클릭하시면 카테고리를 한 번에 모두 삭제하며, 카테고리 기능을 사용하지 않습니다." style="color: #e24646">모두 삭제</span></li>';
						$cateArr = @explode('|', $modifyData['category']);
						$cateCnt = @count($cateArr);
						for($c=0; $c<$cateCnt; $c++) {
							echo '<li><span onclick="modifyCate(\''.$cateArr[$c].'\');" title="이 카테고리명을 수정하려면 이 곳을 클릭하세요.">'.strip_tags($cateArr[$c]).'</span> '.
							'&nbsp;<span onclick="deleteCate(\''.$cateArr[$c].'\', \''.(($cateArr[1])?$cateArr[0]:'').'\');" title="이 곳을 클릭하시면 이 카테고리를 삭제합니다."><img src="image/admin/admin_poll_sub.gif" alt="삭제" /></span></li>';
						}
						echo '</ul>';
					}
					?>
					<span id="modifyCateForm" style="display: none"><input type="text" class="input" name="modifyCateOriginal" size="15" value="" title="여기를 빈 칸으로 두시면 기존 카테고리를 수정하지 않고, 오른쪽에 지정한 이름으로 새로운 카테고리를 추가합니다." /> (을)를 </span>
					<input type="text" class="input" name="createCategory" size="15" title="이 곳에 새로 추가할 카테고리명을 입력해 주세요." value="" /> <input type="submit" id="modifyCateBtn" value="추가하기" title="카테고리를 새로 추가합니다." class="submit" /></div>

					<div class="expertBox"><strong>고급수정:</strong> <input type="text" class="input" name="expertCreateCategory" size="45" title="이 곳에서 이미 생성된 카테고리의 정렬 순서를 바로 변경하거나, 새로이 추가 혹은 삭제 처리를 하실 수 있습니다. GR보드의 카테고리 생성에 능숙하신 분들을 위한 기능입니다." value="<?php echo $modifyData['category']; ?>" /> <input type="submit" id="modifyExpertCateBtn" value="수정하기" title="새 카테고리 추가, 제거 혹은 순서변경을 완료하셨다면 클릭해 주세요~" class="submit" /></div>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="목록을 볼 수 있는 대상을 제한할 수 있습니다. 선택한 숫자 이상의 레벨만 사용가능 합니다.">목록보기 제한</div>
				<div class="tableRight">
				<select name="modifyEnterLevel">
				<?php for($i=1; $i<100; $i++) { ?>
					<option value="<?php echo $i; ?>" <?php echo (($modifyData['enter_level']==$i)?'selected="selected"':''); ?>><?php echo $i; ?></option>
				<?php } ?>
				</select>
				(<strong>1</strong> 이면 비회원도 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyEnterLevel" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			<div>

			<div class="tableListLine">
				<div class="tableLeft" title="글 내용을 볼 수 있는 대상을 제한할 수 있습니다. 선택한 숫자 이상의 레벨만 사용가능 합니다.">글내용보기 제한</div>
				<div class="tableRight">
				<select name="modifyViewLevel">
				<?php for($ii=1; $ii<100; $ii++) { ?>
					<option value="<?php echo $ii; ?>" <?php echo (($modifyData['view_level']==$ii)?'selected="selected"':''); ?>><?php echo $ii; ?></option>
				<?php } ?>
				</select>
				(<strong>1</strong> 이면 비회원도 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyViewLevel" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="글쓰기 가능한 대상을 제한할 수 있습니다. 선택한 숫자 이상의 레벨만 사용가능 합니다.">글쓰기 제한</div>
				<div class="tableRight">
				<select name="modifyWriteLevel">
				<?php for($iii=1; $iii<100; $iii++) { ?>
					<option value="<?php echo $iii; ?>" <?php echo (($modifyData['write_level']==$iii)?'selected="selected"':''); ?>><?php echo $iii; ?></option>
				<?php } ?>
				</select>
				(<strong>1</strong> 이면 비회원도 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyWriteLevel" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="코멘트 쓰기가능한 대상을 제한할 수 있습니다. 선택한 숫자 이상의 레벨만 사용가능 합니다.">코멘트쓰기 제한</div>
				<div class="tableRight">
				<select name="modifyCommentWriteLevel">
				<?php for($iiii=1; $iiii<100; $iiii++) { ?>
					<option value="<?php echo $iiii; ?>" <?php echo (($modifyData['comment_write_level']==$iiii)?'selected="selected"':''); ?>><?php echo $iiii; ?></option>
				<?php } ?>
				</select>
				(<strong>1</strong> 이면 비회원도 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyCommentWriteLevel" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="첨부파일 다운로드를 특정 레벨 이상일 때만 가능하도록 할 수 있습니다. 선택한 숫자 이상의 레벨만 이 게시판에서 첨부파일을 받을 수 있습니다.">다운로드 제한</div>
				<div class="tableRight">
				<select name="modifyDownLevel">
				<?php for($d=1; $d<100; $d++) { ?>
					<option value="<?php echo $d; ?>" <?php echo (($modifyData['down_level']==$d)?'selected="selected"':''); ?>><?php echo $d; ?></option>
				<?php } ?>
				</select>
				(<strong>1</strong> 이면 비회원도 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyDownLevel" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="첨부파일을 받을 때 받는 사용자의 포인트를 차감하고, 게시물 작성자에게 그 만큼 채워줄 수 있습니다. 비회원은 이 경우 파일을 받을 수 없습니다.">받기 포인트</div>
				<div class="tableRight">
				<select name="modifyDownPoint">
				<?php for($p=0; $p<100; $p++) { ?>
					<option value="<?php echo $p; ?>" <?php echo (($modifyData['down_point']==$p)?'selected="selected"':''); ?>><?php echo $p; ?></option>
				<?php } ?>
				</select>
				(<strong>0</strong> 이면 비회원도 파일 받을 수 있음)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyDownPoint" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판을 관리할 관리자 ID 를 적습니다. (여러명일 경우 | (Shift + ￦) 로 구분)">관리자 ID</div>
				<div class="tableRight">
					<input type="text" class="input" name="modifyMaster" onkeydown="findID(this.value);" maxlength="255" value="<?php echo $modifyData['master']; ?>" />
					(예: heegeun|sirini|honggildong)
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyMaster" value="1" /> 모두 변경</span>
					<div id="findIDResult" style="display: none"></div>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 첨부할 수 있는 필드 수를 정합니다.">파일첨부</div>
				<div class="tableRight">
				<select name="modifyNumFile">
				<option value="0" <?php echo (($modifyData['num_file']==0)?'selected="selected"':''); ?>>0</option>
				<?php for($ft=1; $ft<11; $ft++) { ?>
					<option value="<?php echo $ft; ?>" <?php echo (($modifyData['num_file']==$ft)?'selected':''); ?>><?php echo $ft; ?></option>
				<?php } ?>
				</select> 개 (한번에 10개까지 업로드 가능)
				<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
				<input type="checkbox" name="sameModifyNumFile" value="1" /> 모두 변경</span>
				</div>
			</div>
			<div class="clear"></div>
			
			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 등록된 게시물과 댓글이 수정될 시, 게시물과 댓글에 수정여부를 기록할지 설정합니다.">댓글/게시물 수정기록</div>
				<div class="tableRight">
					<input type="checkbox" value="1" name="modifyIsHistory" <?php echo (($modifyData['is_history'])?'checked="checked"':''); ?> />
					체크하시면, 게시물과 댓글의 수정여부를 기록합니다.
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsHistory" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>
			
		  <div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 영문만입력된 게시물/댓글이 등록시, 차단할지 허용할지를 설정합니다.">영문 게시물/댓글 차단</div>
				<div class="tableRight">
					<input type="checkbox" value="1" name="modifyIsEnglish" <?php echo (($modifyData['is_english'])?'checked="checked"':''); ?> />
					체크하시면, 게시물과 댓글에 영문으로만 된 글이<br /> 작성될경우 차단합니다. (비회원 글쓰기에만 적용)
					<span class="sameBox" title="이 설정을 이 게시판이 속한 그룹 내에 소속된 게시판에 한해서 동일하게 적용합니다.">
					<input type="checkbox" name="sameModifyIsEnglish" value="1" /> 모두 변경</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="추가로 생성한 확장 필드들의 목록을 보고, (필요시) 삭제합니다.">추가된 필드들</div>
				<div class="tableRight">
				
				<div class="extendFieldsManage">
				<table rules="none" summary="GR Board Extend Field List" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed">
				<caption></caption>
				<thead> 
				<tr>
					<th>필드명</th>
					<th>타입</th>
					<th title="삭제시 확장 필드에 저장된 데이터들도 함께 제거됩니다.">삭제</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$getExtendField = $GR->query('select * from '.$dbFIX.'bbs_'.$modifyData['id'].' limit 1');
				$numField = @mysql_num_fields($getExtendField);
				for($f=0; $f<$numField; $f++) {
					if($f < $maxDefaultField) continue;
					$fields = @mysql_fetch_field($getExtendField, $f);
					?>
					<tr>
						<td><strong><?php echo $fields->name; ?></strong></td>
						<td><?php 
						if($fields->type == 'int') echo '<span title="0 부터 9 사이의 숫자만 입력 가능한 추가 필드입니다. 11자리 숫자까지 입력할 수 있습니다.">숫자입력칸</span>';
						elseif($fields->type == 'string') echo '<span title="문자와 숫자, 특수기호등을 포함하여 255자까지 입력 가능한 추가 필드입니다.">250자 문자열 입력칸</span>';
						elseif($fields->type == 'blob') echo '<span title="입력 가능한 모든 문자,숫자,기호 등을 최대 6만 5천자까지 입력할 수 있는 추가 필드입니다.">아주 많은 문자열 입력칸</span>';
						?></td>
						<td><img src="image/admin/admin_delete.gif" alt="삭제" title="삭제시 확장 필드에 저장된 데이터들도 함께 제거됩니다." onclick="isDeleteExtend('<?php echo $fields->name; ?>', '<?php echo $modifyData['id']; ?>');" /></td>
					</tr>
					<?php
				}
				?>
				</tbody>
				</table>
				</div>

				</div>
				<div class="clear"></div>
			</div>

			<div class="tableListLine">
				<div class="tableLeft" title="이 게시판에 한해서, 추가로 사용할 확장 필드를 생성합니다.">필드 추가하기</div>
				<div class="tableRight">

				<div style="cursor: help" id="helpAddFieldBtn" title="클릭하시면 관련 도움말을 아래에 펼칩니다. 다시 클릭하시면 닫습니다.">※ 필드 추가하기와 관련된 도움말을 펼쳐 봅니다.</a></div>
				<div id="helpExtend" class="grayBox" style="margin: 10px 0 10px 0; display: none"><div>
				<strong>※ 필드 추가하기란?</strong><br />
				<br />
				GR보드에서 게시판을 생성하면 기본적으로 생기는 기본 필드들(no, member_key ...)외에,<br />
				추가로 스킨(테마)에서 활용할 수 있는 추가 필드들을 생성할 수 있도록 합니다.<br />
				단, 지금 수정하고 계시는 게시판에만 필드를 추가하실 수 있고,<br />
				스킨에서 추가된 필드들을 활용하지 않으면 필드를 추가해도 활용하실 수 없습니다.<br />
				(스킨을 직접 수정하고픈 분들은 iround_minishop_expand_in178 기본스킨을 확인해 보세요.)<br />
				<br />
				구인/구직, 미니샵, 신청게시판 등 입력해야 할 데이터들이 다양한 경우에<br />
				해당 기능을 하는 스킨을 사용하고, 스킨 제작자가 설명하는 내용대로 필드명과 타입을 선택하여<br />
				하나씩 추가하신 후 게시판을 활용하시면 됩니다.<br />
				<br />
				<strong>※ 주의하실 점</strong><br />
				<br />
				<u>[!] 우선, "<strong>방명록 형태</strong>" 로 체크하시는 것 잊지 마세요!</u><br />
				<br />
				추가된 필드들은 GR보드에서 직접적으로 관리되지 않는 필드입니다.<br />
				직접적인 관리가 되지 않는다는 것은 곧 데이터의 안정성 및 호환성을 보장하지 못한다는 뜻입니다.<br />
				사용자분들이 추가하신 필드로 인해 발생되는 문제는 GR보드에서 해결을 할 수 없습니다.<br />
				때문에, 꼭 필요한 게시판에 한해서, 추가 필드 생성을 요구하는 스킨을 사용할 때만<br />
				활용해 주시길 바랍니다.<br />
				<br />
				스킨 제작자가 제시하는 설명을 꼼꼼히 확인하시고 필드명과 타입을 작성/선택해 주세요.<br />
				추가된 필드는 (기본적으로) 해당 필드를 활용하는 특정 스킨에서만 활용이 됩니다.<br />
				또한 추가된 필드에 저장된 데이터들은 해당 필드가 삭제될 경우 모두 삭제되므로 주의해 주세요.<br />
				필드가 많이 추가될수록, "아주 긴 문자열 입력가능" 한 필드가 많아질수록<br />
				이 게시판은 더 느려지며 DB서버 부하는 더 커지게 됩니다.<br />
				<br />
				※ 스킨 디렉토리 내 <strong>field.extend.ini</strong> 파일이 있으면 클릭 한 번으로 모든 필드들을 추가할 수 있습니다.
				</div></div>

				<div class="extendFieldsManage">
				<table rules="none" summary="GR Board Extend Field List" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed">
				<caption></caption>
				<thead> 
				<tr>
					<th title="추가할 필드명을 입력해 주세요. 추가된 필드는 스킨(테마)에서 활용되지 않으면 사용되지 않습니다.">추가할 필드명</th>
					<th title="3가지 중 하나를 선택하시면 됩니다. 숫자만 입력가능, 짧은 문자열 입력가능, 아주 긴 문자열 입력가능">타입</th>
					<th title="추가를 원하시면 추가해 주세요. 추가된 필드를 스킨(테마)에서 활용해야 실제로 데이터 입/출력이 됩니다.">삭제</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><strong>ext_</strong><input type="text" name="extendName" class="input" /></td>
					<td>
						<select name="extendType">
							<option value="">추가할 필드 타입을 선택해 주세요</option>
							<option value="int" title="0부터 9가지의 11자리 숫자까지 입력가능하도록 합니다.">숫자만 입력가능</option>
							<option value="varchar" title="문자, 숫자, 기호 등을 포함한 문자열을 255자까지 입력가능하도록 합니다.">짧은 문자열 입력가능</option>
							<option value="text" title="문자, 숫자, 기호 등을 포함한 아주 긴 문자열을 최대 6만 5천자까지 입력가능하도록 합니다.">아주 긴 문자열 입력가능</option>
						</select>
						</select>
					</td>
					<td><img src="image/admin/btn_add.gif" alt="추가하기" onclick="addNewExtend('<?php echo $modifyData['id']; ?>');" title="필드를 추가합니다." /></td>
				</tr>
				<?php if(file_exists('theme/'.$modifyData['theme'].'/field.extend.ini')) { ?>
				<tr>
					<td colspan="3">
						<a href="admin_board.php?boardID=<?php echo $boardID; ?>&amp;fieldAutoExtend=1&amp;selectTheme=<?php echo $modifyData['theme']; ?>" title="이 스킨은 추가로 필요한 필드들을 자동으로 만들 수 있는 field.extend.ini 파일을 가지고 있습니다. 이를 이용해서 한 번에 모든 필드를 생성합니다.">[자동으로 모든 필드 추가하기]</a>
					</td>
				</tr>
				<?php } ?>
				</tbody>
				</table>
				</div>
				
				</div>
				<div class="clear"></div>
			</div>

			<div class="submitBox">
				<input type="submit" value="게시판 수정" title="게시판 설정을 수정 합니다" />
			</div>
			</form>
		</div>
