<?php
require_once $grboard . '/genxPhpThumb/ThumbLib.inc.php';
$width = 200;
?>
<div class="subGalleryTitle"><?php echo $latestTitle; ?></div>

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
		$thumb->adaptiveResize($width, $width)->save($genFile);
	}
	//$exif = exif_read_data($photo['file_route1']);
	?>
	<div class="subPhotoList"><a href="<?php echo $grboard; ?>/board.php?id=<?php echo $id; ?>&amp;articleNo=<?php echo $latest['no']; ?>"><img src="<?php echo $genFile; ?>" alt="갤러리 미리보기" /><br /><?php echo $title; ?></a></div>
	<?php
} # while
?>

<div class="latestClear"></div>
