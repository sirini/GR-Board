<?php
require_once $grboard . '/genxPhpThumb/ThumbLib.inc.php';
$width = 800;
if(!is_dir($grboard . '/data/__thumbs__/'.date('Y'))) @mkdir($grboard . '/data/__thumbs__/'.date('Y'), 0707);
if(!is_dir($grboard . '/data/__thumbs__/'.date('Y/m'))) @mkdir($grboard . '/data/__thumbs__/'.date('Y/m'), 0707);
if(!is_dir($grboard . '/data/__thumbs__/'.date('Y/m/d'))) @mkdir($grboard . '/data/__thumbs__/'.date('Y/m/d'), 0707);
?>

<ul class="galleryList">
<?php
// 게시물 루프
while($latest = $GR->fetch($getData))
{
	$title = cutString(stripslashes($latest['subject']), $cutSize);
	$photo = $GR->getArray('select file_route1 from '.$dbFIX.'pds_save where id = \''.$id.'\' and article_num = ' . $latest['no']);
	if(preg_match('/[가-힣]/i', $photo['file_route1'])) continue;
	$filetype = strtolower(end(explode('.', $photo['file_route1'])));
	$filename = md5(end(explode('/', $photo['file_route1'])));
	$genFile = $grboard . '/data/__thumbs__/'.date('Y/m/d').'/'.$width.'_'.$filename.'.'.$filetype;
	if(!file_exists($genFile)) {
		$thumb = PhpThumbFactory::create($grboard . '/' . $photo['file_route1']);
		$thumb->resize($width, $width)->save($genFile);
	}
	//$exif = @exif_read_data($photo['file_route1']);
	?>
	<li class="subWeeklyPhotoList"><div><a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $latest['no']; ?>"><img src="<?php echo $genFile; ?>" alt="갤러리 미리보기" /><br /><?php echo $title; ?></a></div>
		<!-- <div class="exifInfo"><?php echo $exif['Model']; ?></div> --></li>
	<?php
} # while
?>
</ul>
<div class="latestClear"></div>
