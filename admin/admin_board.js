// DOM 아이디로 객체 반환
function _(id) {
	return document.getElementById(id);
}

// 검색할 게시판명을 입력했는지 확인하기
function isSearchValue() {
	if(!document.forms["searchBoard"].elements["searchBoardList"].value) {
		alert('검색할 게시판명을 입력하세요');
		return false;
	}
	return true;
}

// 삭제할건지 물어보기
function deleteBoard() {
	if(confirm('정말로 GR Board 를 삭제하시겠습니까?\n\n'+
		'GR Board 가 사용하고 있는 테이블과 DB 접속정보 모두 삭제됩니다.\n\n'+
		'완전한 삭제를 위해서는 ftp 로 접속 후 첨부파일을 포함하여 모두 삭제하세요.'+
		'\n\n삭제 후 설치 페이지로 이동합니다.')) {
		location.href='admin_uninstall.php';
	}
}

// 선택한 id 의 게시판 테이블과 코멘트 테이블을 삭제할 것인지 물어보기
function deleteTable(no, id) {
	if(confirm('정말로 '+id+' 게시판을 삭제하시겠습니까?\n\n작성된 게시물과 코멘트들이 모두 삭제됩니다.\n\n'+
		'만약을 대비해서 DB 를 백업해 두시길 바랍니다.\n\n계속 진행하시겠습니까?')) {
		location.href='admin_board.php?deleteBoardNo='+no+'&deleteBoardId='+id;
	}
}

// 게시판 추가시 필수 입력폼이 작성되어 있는지 체크
function isAddValue(f) {
	if(!f.elements["addBoardId"].value) {
		alert('게시판 ID를 작성하지 않으셨습니다.\n\n게시판 ID는 영문소문자와 숫자, 언더바로 이루어집니다.');
		return false;
	}
	if(!f.elements["addPageNum"].value) {
		alert('한 페이지에 표시될 게시물 숫자를 입력하지 않으셨습니다.\n\n한 페이지에 표시될 범위는 1 개 부터 999 개까지 입니다.');
		return false;
	}
	if(!f.elements["addPagePerList"].value) {
		alert('페이지 표시 숫자를 입력하지 않으셨습니다.\n\n페이지 표시 범위는 1개 부터 999 개까지 입니다.');
		return false;
	}
	return true;
}

// 게시판 수정시 필수 입력폼이 작성되어 있는지 체크
function isModifyValue(f) {
	if(!f.elements["modifyBoardId"].value) {
		alert('게시판 ID를 작성하지 않으셨습니다.\n\n게시판 ID는 영문소문자와 숫자, 언더바로 이루어집니다.');
		return false;
	}
	if(!f.elements["modifyPageNum"].value) {
		alert('한 페이지에 표시될 게시물 숫자를 입력하지 않으셨습니다.\n\n한 페이지에 표시될 범위는 1 개 부터 999 개까지 입니다.');
		return false;
	}
	if(!f.elements["modifyPagePerList"].value) {
		alert('페이지 표시 숫자를 입력하지 않으셨습니다.\n\n페이지 표시 범위는 1개 부터 999 개까지 입니다.');
		return false;
	}
	return true;
}

// DB 파일을 백업할 것인지 물어보기
function dbSaveOk() {
	if(confirm('GR Board 가 사용중인 DB 를 백업하시겠습니까?\n\n'+
		'예(Yes)를 누르시면 백업파일을 받으실 수 있으며\n\n'+
		'아니오(No)를 누르시면 현재 사용중인 DB 상태를 확인할 수 있습니다.\n\n'+
		'※ GR 보드 뿐만 아니라 사용하고 계시는 모든 DB 를 백업하시려면\n'+
		'이용중인 호스팅회사에서 제공하는 전용 매니져 프로그램이나,\n'+
		'phpMyAdmin 을 이용하시길 바랍니다.\n\n'+
		'(phpMyAdmin 은 대표적인 MySQL DB 서버 관리 도구이며,\n'+
		'http://phpmyadmin.net 에서 배포하고 있는 오픈소스 프로그램입니다.\n'+
		'시리니넷에서도 받으실 수 있습니다.)')) {
		location.href='admin_backup.php?db_save_ok=1';
	} else {
		location.href='admin_backup.php?v=2';
	}
}

// 기존 카테고리를 선택했을 때
function setNewCategory(v) {
	document.forms["modifyBoard"].elements["renewalCategory"].value = v;
}

// 문자열 치환
function str_replace(str1, str2, str3) {
	var r = new RegExp(str1, 'g');
	return str3.replace(r, str2);
}

// 도움말 토글
function toggleHelp(id) {
	t = document.getElementById(id);
	if(t.style.display == '') Effect.Fade(id);
	else Effect.Appear(id);
}

// 레이아웃용으로 head / foot 세팅
function setForLayout(t, target) {
	_(target).value = t.value;
}

// 추가한 확장필드 제거하기
function isDeleteExtend(name, id) {
	if(confirm('정말로 추가된 확장필드 '+name+' 를 제거하시겠습니까?\n\n'+
		'필드가 삭제되면 그 필드에 저장된 데이터들은 모두 삭제됩니다.')) {
		location.href='admin_board.php?boardID='+id+'&delExtName='+name;
	}
}

// 필드 추가하기
function addNewExtend(id) {
	if(confirm('이 게시판에서 추가로 사용할 확장필드를 생성하시겠습니까?\n\n'+
		'이 게시판에서 사용되는 스킨(테마)가 확장필드를 활용해야 실제 사용이 됩니다.\n\n'+
		'또한 데이터의 보존이나 호환성을 기본적으로 보장하지 않습니다.')) {
		var t = document.forms['modifyBoard'];
		var name = t.elements['extendName'].value;
		var type = t.elements['extendType'].value;
		if(!name) {
			alert('필드명을 입력하지 않으셨습니다.');
			t.elements['extendName'].focus();
			return;
		}
		if(!type) {
			alert('추가할 필드의 타입을 선택하지 않으셨습니다.');
			t.elements['extendType'].focus();
			return;
		}
		location.href='admin_board.php?boardID='+id+'&addExtName='+name+'&addExtType='+type;
	}
}

// ID 로 관리자 지정할 시 ID 찾는 거 돕기
function findID(id) {
	var lastID = id.split('|');
	var id = lastID[lastID.length-1];

	$.ajax({
		type: 'POST',
		url: 'admin/admin_find_id.php',
		data: 'id='+id,
		dataType: 'xml',
		success: function(xml) {
			var result = '<ol>';

			$(xml).find('lists').find('ids').each(function(idx) {
				result += '<li title="관리자로 임명할 사람의 ID 정보와, 닉네임/본명을 알아봅니다."><strong>'+$(this).text()+'</strong>' +
					'(닉네임: '+$(this).attr('name')+' / 본명: '+$(this).attr('real')+')</li>';
			});

			result += '</ol>';
			
			$('#findIDResult').fadeIn().html(result);
		}
	});
}

// 테마 미리보기 기능
function themePreview(t) {
	$('#themePreview').html('<a href="theme_preview.php?src=theme/'+t+'/theme_preview.jpg" onclick="window.open(this.href, \'previewOpen\', \'menubar=no,statusbar=no,toolbar=no,width=800,height=600,scrollbars=yes\'); return false" title="클릭하시면 스크린샷을 크게 봅니다.">'+
		'<img src="phpThumb/phpThumb.php?src=../theme/'+t+'/theme_preview.jpg&amp;w=100&amp;h=80&amp;q=90" alt="테마 미리보기" /></a>');
}

// 특정 카테고리 수정시
function modifyCate(str) {
	$('#modifyCateForm').fadeIn();
	$('#modifyCateBtn').attr('value', '으로 변경합니다.');
	document.forms['modifyBoard'].elements['modifyCateOriginal'].value = str;
}

// 특정 카테고리 삭제시
function deleteCate(str, first) {
	if(confirm('정말로 '+str+' 분류를 삭제하시겠습니까?')) {
		var move = prompt(str+' 분류에 속했던 게시물들을 어디로 옮기시겠습니까? (기본: 첫번째 분류)', first);
		var f = document.forms['modifyBoard'];

		if(!move && first) {
			alert('옮기고 싶은 분류명을 적어주세요. 기존에 존재하는 분류명이면 됩니다.\n\n다시 삭제를 시도해 주세요.');
			return false;
		}
		if(!move && !first) {
			if(confirm('마지막으로 남아있던 분류입니다.\n\n삭제하시면 자동으로 카테고리 기능을 사용하지 않습니다.\n\n계속 진행하시겠습니까?')) {
				location.href='admin_board.php?boardID='+f.elements['boardID'].value+'&deleteCateAll=disableCategory';
			} else return false;
		}
		f.elements['modifyCateOriginal'].value = str;
		f.elements['createCategory'].value = move;
		f.elements['deleteCate'].value = str;
		f.submit();
	}
}

// 카테고리 전부 삭제 및 기능 사용 OFF
function deleteCateAll() {
	if(confirm('정말로 모든 분류를 삭제하고 카테고리 기능을 그만 사용하시겠습니까?\n\n분류가 삭제된다고 해서 게시물이 삭제되지는 않습니다.')) {
		location.href='admin_board.php?boardID='+document.forms['modifyBoard'].elements['boardID'].value+'&deleteCateAll=disableCategory';
	}
}

// 페이지 로드 후 실행
$(function(){

	// onsubmit 이벤트 잡아채기
	document.forms['searchBoard'].onsubmit = isSearchValue;
	
	// btnOver , btnOut 이벤트 잡아채기
	var inputBtn = document.getElementsByTagName('input');
	for(i=0; i<inputBtn.length; i++) {
		if(inputBtn[i].getAttribute('class') == 'btn') {
			inputBtn[i].onmouseover = function() { this.setAttribute('src', str_replace('.gif', '_over.gif', this.getAttribute('src'))); }
			inputBtn[i].onmouseout = function() { this.setAttribute('src', str_replace('_over.gif', '.gif', this.getAttribute('src'))); }
		}
	}
	
	// 추가 필드 도움말 토글
	$('#helpAddFieldBtn').toggle(
		function(){ $('#helpExtend').fadeIn(); },
		function(){ $('#helpExtend').fadeOut(); }
	);

	// 상단 도움말 토글
	$('#helpBoardBtn').toggle(
		function(){ $('#helpBox').fadeIn(); },
		function(){ $('#helpBox').fadeOut(); }
	);

});