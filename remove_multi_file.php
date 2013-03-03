<?php
// 멀티업로드로 올린 파일을 글작성 도중에 다시 제거할 경우 실행
if(!$_POST['id'] || !$_POST['filename']) exit();
$_POST['id'] = str_replace(array('../', '.php'), '', $_POST['id']);
$_POST['filename'] = str_replace(array('../', '.php'), '', $_POST['filename']);
@unlink('data/'.$_POST['id'].'/'.$_POST['filename']);

$readTmpList = @file('data/tmpfile.'.$_SERVER['REMOTE_ADDR']);

foreach ($readTmpList as $k => $v) {
	//같은 이름,다른 내용의 이미지파일을 업로드 했을 경우 정확히 걸러내기 위해서 explode두번 사용.
	$v_ex=explode('__GRBOARD__',$v);
	$v_ex_ex=end(explode('/',$v_ex[0]));
    if($v_ex_ex==$_POST['filename']){
    	unset($readTmpList[$k]);
    	break;
    }
}
$newTmp=implode('',$readTmpList);
$f = @fopen('data/tmpfile.'.$_SERVER['REMOTE_ADDR'], 'w');
@fwrite($f, $newTmp);
@fclose($f);
?>
{"result":"OK"}