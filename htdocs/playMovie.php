<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['movieId'])) {
	$movieObj = new MOVIE($_GET['movieId']);
	$moviePath = $movieObj->moviePath;
	$hardPath = '/Volumes/Movies/'.$movieObj->directoryPath;
	// If there is an optimized version, use that instead of the original
	$releaseYear = ' ('.substr($movieObj->releaseDate, 0, 4).')';
	if (file_exists($hardPath.'/Versions/Optimized for TV/'.$movieObj->cleanTitle.$releaseYear.'.mp4')) {
		$hardPath .= $movieObj->cleanTitle.$releaseDate;
		$moviePath .= '/Versions/Optimized for TV/'.rawurlencode($movieObj->cleanTitle).$releaseYear.'.mp4';
	}
	else {
		if ($movieObj->collection != '') {
			$moviePath .= $movieObj->collection.'/';
			$hardPath .= $movieObj->collection.'/';
		}
		$hardPath .= $movieObj->cleanTitle;
		if (file_exists($hardPath.'.mp4')) { $moviePath .= rawurlencode($movieObj->cleanTitle).'.mp4'; }
		elseif (file_exists($hardPath.'.m4v')) { $moviePath .= rawurlencode($movieObj->cleanTitle).'.m4v'; }
		elseif (file_exists($hardPath.'.mov')) { $moviePath .= rawurlencode($movieObj->cleanTitle).'.mov'; }
		else { $error = 'Movie Not Found'; }
	}
	$posterObj = new MOVIE_POSTER($movieObj->movieId);
	$posterPath = '/posters/'.rawurlencode($posterObj->posterName);
	$chapterObj = new MOVIE_CHAPTERS();
	$chapterCollection = json_decode($chapterObj->fetch_chaptersAsJsonData($movieObj->movieId));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Play Movie: <?php print $movieObj->title; ?></title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>
<style type="text/css">
video {
	border: 0;
	vertical-align: center;
	display: block;
	margin: 8px auto;
	width: 95%;
}
h1 { text-align: center; }
table.chapterData {
	width: 90%;
	margin: 15px auto 0px auto;
}
table.chapterData td {
	text-align: center;
	font-size: 11px;
}
table.playButtons {
	margin: 5px auto;
}
span[data-chapterstart] {
	cursor: pointer;
	color: blue;
}
table.chapterData td.small {
	width: 8px;
	text-align: center;
	color: black;
	cursor: text;
}
button {
	width: 110px;
	height: 20px;
	text-align: center;
	vertical-align: middle;
	cursor: pointer;
	border-radius: 6px;
	border: 1px outset #AAAAAA;
	margin: 5px 8px;
}
button[data-jumpback] { width: 50px; }
button[data-jumpforward] { width: 50px; }
button:hover { border: 1px #B4B4B4 inset; }
button.backOrSlow { background: linear-gradient(rgba(255,255,255,1.0) 0%, rgba(170,207,253,0.63) 100%); }
button.fowardOrFast { background: linear-gradient(rgba(255,255,255,1.0) 0%, rgba(191,255,190,0.63) 100%); }
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>
<script type="text/javascript">
var movieController = {

	movieObj: null,
	startTime: 0,
	endTime: 0,
	playSpeed: 1,

	init: function () {
		'use strict';
		movieController.movieObj = $D('movieObj');
		movieController.movieObj.preload = true;
		movieController.movieObj.pause();
		movieController.movieObj.volume = 0;
		movieController.movieObj.muted = true;
		movieController.startTime = 0;
		movieController.endTime = 0;
		return;
	},

	set_movieLength: function () {
		'use strict';
		if (movieController.endTime === 0) {
			movieController.startTime = movieController.movieObj.seekable.start(0);
			movieController.endTime = movieController.movieObj.seekable.end(0);
		}
	},

	playMovie: function () {
		if (movieController.movieObj.readyState === 4) {
			movieController.movieObj.play();
			movieController.set_movieLength();
		}
		else { setTimeout(function() {
			movieController.movieObj.play();
			movieController.set_movieLength();
		}, 1500); }
	},

	playChapter: function () {
		'use strict';
		var currObj = jQuery(this),
			chapStart = currObj.attr("data-chapterstart"),
			chapDesc = currObj.text();

		movieController.movieObj.pause();
		movieController.movieObj.currentTime = chapStart;
		movieController.movieObj.muted = false;
		movieController.playMovie();
		return;
	},

	playFaster: function () {
		'use strict';
		var fasterButton = $D('fasterbutton'),
			slowerButton = $D('slowerbutton');

		if (movieController.playSpeed < 5) {
			if (movieController.playSpeed === 1) { movieController.playSpeed = 2; }
			else { movieController.playSpeed += 1; }
			movieController.movieObj.playbackRate = movieController.playSpeed;
			fasterButton.innerHTML = "Faster ("+movieController.playSpeed+"x)";
			slowerButton.innerHTML = "Slower ("+movieController.playSpeed+"x)";
		}

		if (movieController.playSpeed === 5) {
			fasterButton.disabled = true;
			slowerButton.disabled = false;
		}
		else {
			fasterButton.disabled = false;
			slowerButton.disabled = false;
		}
		return;
	},

	playSlower: function () {
		'use strict';
		var fasterButton = $D('fasterbutton'),
			slowerButton = $D('slowerbutton');

		if (movieController.playSpeed > 1) {
			if (movieController.playSpeed === 2) { movieController.playSpeed = 1; }
			else { movieController.playSpeed -= 1; }
			movieController.movieObj.playbackRate = movieController.playSpeed;
			fasterButton.innerHTML = "Faster ("+movieController.playSpeed+"x)";
			slowerButton.innerHTML = "Slower ("+movieController.playSpeed+"x)";
		}

		if (movieController.playSpeed === 1) {
			fasterButton.disabled = false;
			slowerButton.disabled = true;
		}
		else {
			fasterButton.disabled = false;
			slowerButton.disabled = false;
		}
		return;
	},

	jumpForward: function () {
		'use strict';
		var currObj = jQuery(this),
			interval = parseFloat(currObj.attr("data-jumpforward")),
			currentPlayPoint = 0,
			newPlayPoint = 0;

		movieController.set_movieLength();
		movieController.movieObj.pause();
		currentPlayPoint = movieController.movieObj.currentTime;
		newPlayPoint = parseFloat(currentPlayPoint + interval);
		if (movieController.endTime > newPlayPoint) {
			movieController.movieObj.currentTime = newPlayPoint;
			movieController.playMovie();
		}
		else { movieController.playMovie(); }
	},

	jumpBack: function () {
		'use strict';
		var currObj = jQuery(this),
			interval = parseFloat(currObj.attr("data-jumpback")),
			currentPlayPoint = 0,
			newPlayPoint = 0;

		movieController.set_movieLength();
		movieController.movieObj.pause();
		currentPlayPoint = movieController.movieObj.currentTime;
		newPlayPoint = parseFloat(currentPlayPoint - interval);
		console.log("Interval: "+interval);
		console.log("New Play Point: "+newPlayPoint);
		if (newPlayPoint > 0) {
			movieController.movieObj.currentTime = newPlayPoint;
			movieController.playMovie();
		}
		else { movieController.playMovie(); }
	}
};

jQuery(document).ready(function() {

	movieController.init();
	jQuery(document).on("click", "span[data-chapterstart]", movieController.playChapter);
	jQuery(document).on("click", "button[data-playfaster]", movieController.playFaster);
	jQuery(document).on("click", "button[data-playslower]", movieController.playSlower);
	jQuery(document).on("click", "button[data-jumpback]", movieController.jumpBack);
	jQuery(document).on("click", "button[data-jumpforward]", movieController.jumpForward);

});
</script>
</head>
<body>
<?php if (isset($error) && $error == 'Movie Not Found') { ?>
<h1>Movie (<?php print $movieObj->title; ?>) Not Found at <?php print $hardPath."<br><br>".$moviePath.$movieObj->cleanTitle;?></h1>
<?php } else { ?>
<fieldset class="adminEditBlock">
<legend><h1><a href="/editMovie.php?movieId=<?=$movieObj->movieId; ?>" target="_blank"><?php print $movieObj->title.' ('.$movieObj->formatName.')'; ?></a></h1></legend>
<video id="movieObj" src="<?php print $moviePath; ?>" type="video/mp4" preload="auto" muted controls poster="<?=$posterPath; ?>">
  Your browser does not support the <code>video</code> element.
</video>
<table class="chapterData">
	<tbody>
		<tr>
			<?php
			$numOfChapters = count($chapterCollection);
			foreach ($chapterCollection as $chapterData) {
				$numOfChapters -= 1;
				$chapterHours = 0;
				$chapterSeconds = round($chapterData->chap_sec);
				$chapterMinutes = round($chapterSeconds/60);
				if ($chapterMinutes > 120) {
					$chapterHours = 2;
					$chapterMinutes = round($chapterMinutes - 120);
					$chapterSeconds = round($chapterSeconds - 7200);
				}
				elseif ($chapterMinutes > 59) {
					$chapterHours = 1;
					$chapterMinutes = round($chapterMinutes - 60);
					$chapterSeconds = round($chapterSeconds - 3600);
				}
				if ($chapterSeconds > 59) { $chapterSeconds = round($chapterSeconds - ($chapterMinutes * 60)); }
				if ($chapterSeconds < 0) {
					$chapterMinutes -= 1;
					$chapterSeconds += 60;
				}
				elseif ($chapterSeconds > 59) { $chapterSeconds = round($chapterSeconds - ($chapterMinutes * 60)); }
				print '<td><span data-chapterstart="'.$chapterData->chap_sec.'">'.$chapterData->description.'</span><br>'.$chapterHours.':'.str_pad($chapterMinutes, 2, '0', STR_PAD_LEFT).':'.str_pad($chapterSeconds, 2, '0', STR_PAD_LEFT).'</td>';
				if ($numOfChapters > 0) { print '<td class="small">-</td>'; }
			}
			?>
		</tr>
	</tbody>
</table>
<br>
<table class="playButtons">
	<tbody>
		<tr>
			<td>
				<table style="margin-right: 25px;">
					<tbody>
						<tr>
							<td><button class="backOrSlow" id="slowerbutton" type="button" data-playslower="1">Slower (1x)</button></td>
							<td><button class="fowardOrFast" id="fasterbutton" type="button" data-playfaster="1">Faster (1x)</button></td>
						</tr>
					</tbody>
				</table>
			</td>

			<td>
				<table style="margin-left: 25px; margin-right: 25px;" title="Jump Backward">
					<tbody>
						<tr>
							<td><button class="backOrSlow" type="button" data-jumpback="15.0">&lt;&nbsp;15</button></td>
							<td><button class="backOrSlow" type="button" data-jumpback="60.0">&lt;&nbsp;60</button></td>
							<td><button class="backOrSlow" type="button" data-jumpback="120.0">&lt;&nbsp;120</button></td>
							<td><button class="backOrSlow" type="button" data-jumpback="300.0">&lt;&nbsp;300</button></td>
						</tr>
					</tbody>
				</table>
			</td>

			<td>
				<table style="margin-left: 25px;" title="Jump Forward">
					<tbody>
						<tr>
							<td><button class="fowardOrFast" type="button" data-jumpforward="15.0">15&nbsp;&gt;</button></td>
							<td><button class="fowardOrFast" type="button" data-jumpforward="60.0">60&nbsp;&gt;</button></td>
							<td><button class="fowardOrFast" type="button" data-jumpforward="120.0">120&nbsp;&gt;</button></td>
							<td><button class="fowardOrFast" type="button" data-jumpforward="300.0">300&nbsp;&gt;</button></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</fieldset>
<?php } print urldecode($moviePath); ?>
</body>
</html>
