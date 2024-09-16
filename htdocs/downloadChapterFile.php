<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();

if (isset($_GET['movieId']) && is_numeric($_GET['movieId'])) {
	$movieObj = new MOVIE($_GET['movieId']);
	$chapterObj = new MOVIE_CHAPTERS();
	$chapterData = $chapterObj->fetch_chapterMetaForMovie($_GET['movieId']);
	$title = $movieObj->cleanTitle;
	$fileName = $title.'.Chapters.txt';
	$filePath = $_SERVER["DOCUMENT_ROOT"].'/chapter_files/'.$fileName;
	if (file_exists($filePath)) { unlink($filePath); }
	$fileHandle = fopen($filePath, 'wb');
	fwrite($fileHandle, $chapterData);
	fclose($fileHandle);
	$fileSize = filesize($filePath);
	$fileContents = file_get_contents($filePath);
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-Type: text/plain");
	header("Content-Type: application/force-download; name=filename");
	header("Content-disposition: attachment; filename=\"".$fileName."\"");
	echo $fileContents;
	if (file_exists($filePath)) { unlink($filePath); }
}
?>