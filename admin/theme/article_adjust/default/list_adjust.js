function moveOk() {
	if(!document.forms["moveArticle"].elements["moveTarget"].value) {
		alert('이동할 게시판을 선택하세요.');
		return false;
	}
	return true;
}

function copyOk() {
	if(!document.forms["copyArticle"].elements["copyTarget"].value) {
		alert('복사할 게시판을 선택하세요.');
		return false;
	}
	return true;
}

function categoryOk() {
	if(!document.forms["categoryArticle"].elements["categoryTarget"].value) {
		alert('변경할 카테고리를 선택하세요.');
		return false;
	}
	return true;
}

function execAdjust(exec)
{
	document.forms["adjustMenu"].exec.value = exec;
	if(exec == 'delete'){
		if(confirm('정말로 선택한 게시물들을 삭제하시겠습니까?\n\n게시물과 연관된 첨부파일, 코멘트 모두 삭제됩니다.')){
			document.forms["adjustMenu"].submit();
		}
		else{
			//확인 안 누르면 Do Nothing
		}
	}
	else{
		document.forms["adjustMenu"].submit();
	}
}

// 마우스 온 이벤트
function Over(t) {
	t.style.backgroundColor='#ececec';
}

// 마우스 아웃 이벤트
function Out(t) {
	t.style.backgroundColor='';
}