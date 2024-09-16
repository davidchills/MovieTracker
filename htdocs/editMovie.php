<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['movieId']) ) {
	$movieObj = new MOVIE($_GET['movieId']);
	if ($movieObj->movieId == 0 && !empty($_GET['titleValue'])) {
		$movieObj->title = $_GET['titleValue'];
		$movieObj->sortTitle = $_GET['titleValue'];
	}
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$movieId = 0;
	$posterSize = '';
	$backgroundSize = '';
	$posterWidth = 0;
	$backgroundWidth = 0;
	$posterHeight = 0;
	$backgroundHeight = 0;
	$posterScale = 1;
	$backgroundScale = 1;
	$uniquePosterName = '';
	$uniqueBackgroundName = '';
	$hasPosterUpload = false;
	$hasBackgoundUpload = false;

	if (is_uploaded_file($_FILES['coverPosterUpload']['tmp_name'])) {
		$posterFileName = $_FILES['coverPosterUpload']['name'];
		preg_match("/.*(\.\w+)$/", $_FILES['coverPosterUpload']['name'], $matches);
		$uniquePosterName = md5(time().$posterFileName).$matches[1];
		move_uploaded_file($_FILES['coverPosterUpload']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/posters/".$uniquePosterName);
		$posterSize = getimagesize($_SERVER["DOCUMENT_ROOT"]."/posters/".$uniquePosterName);
		$posterWidth = $posterSize[0];
		$posterHeight = $posterSize[1];

		if ($posterWidth > 200) {
			$posterScale = 200/$posterWidth;
			$posterHeight *= $posterScale;
			$posterWidth = 200;
		}
		if ($posterHeight > 266) {
			$posterScale *= 266/$posterHeight;
			$posterWidth *= 266/$posterHeight;
			$posterHeight = 266;
		}
		$hasPosterUpload = true;
	}

	if (is_uploaded_file($_FILES['backgroundUpload']['tmp_name'])) {
		$backgroundFileName = $_FILES['backgroundUpload']['name'];
		preg_match("/.*(\.\w+)$/", $_FILES['backgroundUpload']['name'], $matches);
		$uniqueBackgroundName = md5(time().$backgroundFileName).$matches[1];
		move_uploaded_file($_FILES['backgroundUpload']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/backgrounds/".$uniqueBackgroundName);
		$backgroundSize = getimagesize($_SERVER["DOCUMENT_ROOT"]."/backgrounds/".$uniqueBackgroundName);
		$backgroundWidth = $backgroundSize[0];
		$backgroundHeight = $backgroundSize[1];
		$hasBackgoundUpload = true;
	}

	if ($_POST['moviePostAction'] == 'Update This Movie' && isset($_POST['movieId']) && $_POST['movieId'] > 0) {
		$movieObj = new MOVIE($_POST['movieId']);
		$movieObj->set_title($_POST['title']);
		$movieObj->set_sortTitle($_POST['sortTitle']);
		$movieObj->set_studio($_POST['studio']);
		$movieObj->set_collection($_POST['collection']);
		$movieObj->set_rating($_POST['rating']);
		$movieObj->set_format($_POST['format']);
		$movieObj->set_releaseDate($_POST['releaseDate']);
		$movieObj->set_descShort($_POST['descShort']);
		$movieObj->set_descLong($_POST['descLong']);
		$movieObj->set_movieScore($_POST['movieScore']);
		$movieObj->set_isActive($_POST['isActive']);
		$movieObj->set_originalUrl($_POST['originalUrl']);
		$movieObj->set_privateNotes($_POST['privateNotes']);
		$movieObj->save_movie();
		$movieId = $movieObj->get_movieId();
		if ($hasPosterUpload == true) {
			$posterObj = new MOVIE_POSTER($movieId);
			$posterObj->delete_poster();
			$posterObj->set_posterName($uniquePosterName);
			$posterObj->set_posterWidth($posterWidth);
			$posterObj->set_posterHeight($posterHeight);
			$posterObj->create_poster();
		}
		if ($hasBackgoundUpload == true) {
			$backgroundObj = new MOVIE_BACKGROUND($movieId);
			$backgroundObj->delete_background();
			$backgroundObj->set_backgroundName($uniqueBackgroundName);
			$backgroundObj->set_backgroundWidth($backgroundWidth);
			$backgroundObj->set_backgroundHeight($backgroundHeight);
			$backgroundObj->create_background();
		}
		header("Location: /editMovie.php?action=update&movieId=".$movieId);
	}

	elseif ($_POST['moviePostAction'] == 'Add New Movie') {
		$movieObj = new MOVIE();
		$movieObj->set_title($_POST['title']);
		$movieObj->set_sortTitle($_POST['sortTitle']);
		$movieObj->set_studio($_POST['studio']);
		$movieObj->set_collection($_POST['collection']);
		$movieObj->set_rating($_POST['rating']);
		$movieObj->set_format($_POST['format']);
		$movieObj->set_releaseDate($_POST['releaseDate']);
		$movieObj->set_descShort($_POST['descShort']);
		$movieObj->set_descLong($_POST['descLong']);
		$movieObj->set_movieScore($_POST['movieScore']);
		$movieObj->set_isActive($_POST['isActive']);
		$movieObj->set_originalUrl($_POST['originalUrl']);
		$movieObj->set_privateNotes($_POST['privateNotes']);
		$movieObj->create_movie();
		$movieId = $movieObj->get_movieId();

		if (isset($_POST['genre']) && is_array($_POST['genre'])) {
			$movieGenreObj = new MOVIE_GENRE_XREF($movieId, null);
			$movieGenreObj->delete_genresInMovie();
			foreach ($_POST['genre'] as $genreId) {
				$movieGenreObj->set_genreId($genreId);
				$movieGenreObj->create_xref();
			}
		}

		if ($hasPosterUpload == true) {
			$posterObj = new MOVIE_POSTER($movieId);
			$posterObj->delete_poster();
			$posterObj->set_posterName($uniquePosterName);
			$posterObj->set_posterWidth($posterWidth);
			$posterObj->set_posterHeight($posterHeight);
			$posterObj->create_poster();
		}
		if ($hasBackgoundUpload == true) {
			$backgroundObj = new MOVIE_BACKGROUND($movieId);
			$backgroundObj->delete_background();
			$backgroundObj->set_backgroundName($uniqueBackgroundName);
			$backgroundObj->set_backgroundWidth($backgroundWidth);
			$backgroundObj->set_backgroundHeight($backgroundHeight);
			$backgroundObj->create_background();
		}
		header("Location: /editMovie.php?action=new&movieId=".$movieId);
	}
}
else { $movieObj = new MOVIE(); }

if ($movieObj->movieId > 0) {
	$posterObj = new MOVIE_POSTER($movieObj->movieId);
	$backgroundObj = new MOVIE_BACKGROUND($movieObj->movieId);
	if (!empty($posterObj->posterName)) {
		$posterPath = '/posters/'.rawurlencode($posterObj->posterName);
		$posterWidth = $posterObj->posterWidth;
		$posterHeight = $posterObj->posterHeight;
	}
	else {
		$posterPath = '';
		$posterWidth = 0;
		$posterHeight = 0;
	}

	if (!empty($backgroundObj->backgroundName)) {
		$backgroundPath = '/backgrounds/'.rawurlencode($backgroundObj->backgroundName);
		$backgroundWidth = $backgroundObj->backgroundWidth;
		$backgroundHeight = $backgroundObj->backgroundHeight;
	}
	else {
		$backgroundPath = '';
		$backgroundWidth = 0;
		$backgroundHeight = 0;
	}
}
else {
	$posterPath = '';
	$posterWidth = 0;
	$posterHeight = 0;
	$backgroundPath = '';
	$backgroundWidth = 0;
	$backgroundHeight = 0;
}
$actorObj = new ACTOR();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo ($movieObj->title != '') ? $movieObj->title : ''; ?>: Edit Movie</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base','chosen')); ?>
<style type="text/css">
#editMovieFormTable {
	border: solid black 1px;
	min-width: 845px;
	max-width: 1045px;
}
#editMovieFormTable > tbody > tr > td {
	border: solid black 1px;
	padding: 5px 3px;
}
#actorChoicesTable {
	width: 845px;
	margin: 10px 0px 20px 1px;
}
#actorChoicesTableBodyId > tr > td {
	width: 180px;
	height: 20px;
	text-align: left;
	vertical-align: middle;
	border: solid black 1px;
}
#movieId {
	width: 28px;
	padding: 1px;
	background-color: rgba(189, 189, 189, 0.55);
	float: left;
	text-align: center;
	vertical-align: middle;
}
#genreCell{ vertical-align: middle; }
#movieScore { width: 70px; }
#isActive { width: 145px; }
#releaseDate { width: 75px; }
#genre {
	width: 690px;
	text-align: left;
}
#originalUrl { width: 690px; }
#posterCell {
	width: 200px;
	text-align: center;
	vertical-align: middle;
}
td.alignRight {
	padding-right: 5px;
}
td.colWidth1 { width: 380px; }
td.colWidth2 { width: 240px; }
input.inputWidth1 { width: 230px; }
select.selectWidth1 { width: 140px; }
#coverPosterUpload { background-color: #EAF4FD; }
#editMovieFormTable > tbody > tr > #actorNameString {
	padding-left: 8px;
	font-weight: normal;
	font-size: smaller;
	cursor: pointer;
	color: #323CB6;
	background-color: white;
}
#searchActor {
	width: 230px;
	height: 25px;
	line-height: 25px;
	font-size: 14px;
	border: 1px inset #AAAAAA;
	cursor: text;
	padding: 0 5px;
	position: relative;
	vertical-align: middle;
}
textarea {
	width: 550px;
	min-height: 55px;
	max-height: 85px;
	font-size: smaller;
}
textarea[name="descShort"] {
	width: 550px;
	min-height: 20px;
	max-height: 30px;
	font-size: inherit;
	background-color: white;
}
#privateNotes { width: 800px; }
textarea { background-color: white; }
img {
	margin-left: auto;
	border: 0;
}
button.width170 {
	min-width: 135px;
	max-width: 155px;
	cursor: pointer;
	margin: 3px 5px;
}
label {
	display: inline;
	line-height: 15px;
	font-weight: normal;
}
.red { color: red; }
#clearMovieId {
	text-align: left;
	vertical-align: middle;
	margin-left: 0px;
	margin-right: 5px;
	cursor: pointer;
	float: left;
}
#actionButtonRow {
	text-align: center;
	vertical-align: middle;
	padding: 5px;
}
input[type="text"], input[type="search"], input[type="url"] {
	height: 20px;
	background-color: white;
}
.closeLink {
	font-size: larger;
	font-weight: bold;
	margin: 15px;
}
#exportDataDiv {
	position: absolute;
	top: 180px;
	left: 90px;
	min-width: 500px;
	max-width: 810px;
	min-height: 310px;
	max-height: 800px;
	padding: 10px;
	border: solid black 1px;
	background-color: white;
	text-align: left;
	vertical-align: top;
	color: black;
	font-size: 12px;
	z-index: 10;
}
span.exportKey {
	font-weight: bold;
	margin-right: 10px;
	display: inline-block;
	width: 120px;
	text-align: right;
}
span.exportData {
	display: inline-block;
	margin-left: 10px;
}
div.exportData {
	max-width: 560px;
	margin-left: 40px;
	margin-right: 60px;
	margin-top: -10px;
}
p.exportData {
	margin-left: 40px;
}
p.symlink.closeLink {
	text-align: center;
}
button.changeMade {
	color: #E16550;
	border: solid #E16550 1px;
}
<?php if ($backgroundPath !== '') { ?>
#main {
	background-image: url(<?=$backgroundPath;?>);
	background-repeat: no-repeat;
	background-position: center top;
}
<?php } ?>
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined','chosen')); ?>

<script type="text/javascript">
const editMovie = {

	selectedActors: [],
	selectedActorNames: [],
	allActorChoices: [],
	filteredActors: [],
	delayTimeId: null,
	movieId: 0,
	title: '',
	sortTitle: '',
	cleanTitle: '',
	studio: 1,
	studioString: '',
	rating: 6,
	ratingString: '',
	collection: '',
	resolution: 4,
	resolutionString: 'HD 1080p',
	movieScore: 0,
	isActive: 'Y',
	isActiveString: 'Movie is Active',
	releaseDate: '',
	shortDescription: '',
	longDescription: '',
	genres: [],
	genreString: '',
	originalUrl: '',
	privateNotes: '',
	hasChapters: 'N',

	init: function () {
		'use strict';
		let getVars = dhf.util.urlGetVars();
		jQuery.when(editMovie.fetchActorsWithId()).then(function () {
			if (getVars.action === 'new' || getVars.action === 'update') { editMovie.refreshParent(); }
			if (getVars.titleValue) { editMovie.autoSetCollection(); }
			if ($D("movieId").value > 0) {
				editMovie.fetchSelectedActors();
				editMovie.setPageTitle();
			}
			editMovie.refreshVars();
		});
	},

	refreshVars: function () {
		'use strict';
		if ($D('movieId')) {
			editMovie.movieId = $D('movieId').value;
			editMovie.title = $D('title').value;
			editMovie.sortTitle = $D('sortTitle').value;
			editMovie.cleanTitle = $D('cleanTitle').value;
			editMovie.studio = $D('studio').value;
			editMovie.studioString = editMovie.fetch_selectMenuText('studio');
			editMovie.rating = $D('rating').value;
			editMovie.ratingString = editMovie.fetch_selectMenuText('rating');
			editMovie.collection = $D('collection').value;
			editMovie.resolution = $D('format').value;
			editMovie.resolutionString = editMovie.fetch_selectMenuText('format');
			editMovie.movieScore = $D('movieScore').value;
			editMovie.isActive = $D('isActive').value;
			editMovie.isActiveString = editMovie.fetch_selectMenuText('isActive');
			editMovie.releaseDate = $D('releaseDate').value;
			editMovie.shortDescription = $D('descShort').value;
			editMovie.longDescription = $D('descLong').value;
			editMovie.originalUrl = $D('originalUrl').value;
			editMovie.privateNotes = $D('privateNotes').value;
			editMovie.hasChapters = $D('hasChapters').value;
		}
		else {
			editMovie.movieId = '';
			editMovie.title = '';
			editMovie.sortTitle = '';
			editMovie.cleanTitle = '';
			editMovie.studio = 1;
			editMovie.studioString = '';
			editMovie.rating = 6;
			editMovie.ratingString = '';
			editMovie.collection = '';
			editMovie.resolution = 4;
			editMovie.resolutionString = 'HD 1080p';
			editMovie.movieScore = 0;
			editMovie.isActive = 'Y';
			editMovie.isActiveString = 'Movie is Active';
			editMovie.releaseDate = '';
			editMovie.shortDescription = '';
			editMovie.longDescription = '';
			editMovie.originalUrl = '';
			editMovie.privateNotes = '';
			editMovie.hasChapters = 'N';
		}
	},

	refreshParent: function () {
		'use strict';
		if (window.opener !== null && window.opener.document.title == 'Movie List') {
			window.opener.gridControl.refreshCollection();
		}
	},

	buildActorNameString: function () {
		'use strict';
		let i = 0,
			actorData = null,
			selectedActorNames = [],
			actorChoiceLength = editMovie.allActorChoices.length;

		for (i = 0; i < actorChoiceLength; i += 1) {
			actorData = editMovie.allActorChoices[i];
			if (jQuery.inArray(actorData.actor_id, editMovie.selectedActors) > -1) {
				selectedActorNames.push('<span data-actorid="'+actorData.actor_id+'">'+actorData.actor_name+'</span>');
			}
		}
		if (selectedActorNames.length >= 1) {
			jQuery("#actorNameString").html(dhf.trim(selectedActorNames.join(', '))).prop('title','Click on Actor Name to go to that page');
		}
	},

	searchFilterDelay: function () {
		'use strict';
		clearTimeout(editMovie.delayTimeId);
		editMovie.delayTimeId = setTimeout(function() { editMovie.buildActorChoiceTable(); }, 750);
	},

	filterBySearch: function () {
		'use strict';
		let i = 0,
			actorData = null,
			collectionLength = editMovie.allActorChoices.length,
			searchTextPattern = new RegExp($D('searchActor').value, "i");

		editMovie.filteredActors = [];

		if ($D('searchActor').value === '') {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = editMovie.allActorChoices[i];
				if (jQuery.inArray(actorData.actor_id, editMovie.selectedActors) > -1) {
					editMovie.filteredActors.push(actorData);
				}
			}
		}
		else {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = editMovie.allActorChoices[i];

				if (actorData.actor_name.search(searchTextPattern) > -1) {
					editMovie.filteredActors.push(actorData);
				}
				else if (jQuery.inArray(actorData.actor_id, editMovie.selectedActors) > -1) {
					editMovie.filteredActors.push(actorData);
				}
			}
		}
	},

	fetchActorsWithId: function () {
		'use strict';
		let requestParams = { "className": "actorClass", "methodCall": "fetchActorsWithId" },
			dfWait;

		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
			editMovie.allActorChoices = actorData;
		});
		return dfWait;
	},

	fetchSelectedActors: function () {
		'use strict';
		let movieId = $D("movieId").value,
			requestParams = { "className": "movieClass", "methodCall": "fetchActorsInMovie", "movieId": movieId };

		if (movieId > 0) {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {

				var i = 0,
					actorDataLength = actorData.length;

				for (i = 0; i < actorDataLength; i += 1) {
					editMovie.selectedActors.push(actorData[i].actor_id);
				}
				editMovie.buildActorChoiceTable();
			});
		}
		else {
			editMovie.selectedActors = [];
			editMovie.buildActorChoiceTable();
		}
	},

	addNewActor: function () {
		'use strict';
		let actorName = dhf.trim($D('searchActor').value),
			movieId = $D("movieId").value,
			requestParams = { "className": "actorClass", "methodCall": "createActor", "actorName": actorName, "movieId": movieId };

		if (parseInt($D('movieId').value, 10) > 0 && actorName !== '') {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				$D('searchActor').value = '';
				$D('searchActor').focus();
				editMovie.allActorChoices = actorData.allActors;
				editMovie.selectedActors = actorData.selectedActors;
				editMovie.buildActorChoiceTable();

				if (window.opener !== null) {
					window.opener.gridControl.refreshCollection();
				}
			});
		}
	},

	toggleSelectedActor: function () {
		'use strict';
		let actorObj = jQuery(this),
			actorId = actorObj.val(),
			isChecked = (actorObj.prop("checked")) ? 'Y' : 'N',
			movieId = $D("movieId").value,
			i = 0,
			cleanArray = [],
			requestParams = { "className": "actorClass", "methodCall": "addActorToMovie", "actorId": actorId, "movieId": movieId };

		if (parseInt($D('movieId').value, 10) > 0) {
			if (isChecked === 'Y') {
				requestParams.methodCall = 'addActorToMovie';
				editMovie.selectedActors.push(actorId);
			}
			else {
				requestParams.methodCall = 'removeActorFromMovie';
				for (i = 0; i < editMovie.selectedActors.length; i += 1) {
					if (editMovie.selectedActors[i] !== actorId) {
						cleanArray.push(editMovie.selectedActors[i]);
					}
				}
				editMovie.selectedActors = cleanArray;
			}
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				$D('searchActor').value = '';
				$D('searchActor').focus();
				editMovie.buildActorChoiceTable();
			});
		}
	},

	buildActorChoiceTable: function () {
		'use strict';
		let i = 0,
			rowCounter = 0,
			columnCounter = 0,
			rowIncrement = 0,
			columnIncrement = 0,
			currentRowId = 'actorRow'+rowIncrement,
			currentColumnId = currentRowId+'Column'+columnIncrement,
			actorData = null,
			actorIsSelected = 'N',
			labelClass= '',
			collectionLength = 0;

		editMovie.filterBySearch();
		collectionLength = editMovie.filteredActors.length;
		editMovie.selectedActorNames = [];

		if ($D('actorChoicesTable')) { $D("actorForm").removeChild($D("actorChoicesTable")); }

		if (collectionLength < 1) {
			editMovie.makeGridTableNoResults();
			return;
		}

		if (!$D('actorChoicesTableBodyId')) {
			dhf.makeTable({
				"oid": 'actorChoicesTable',
				"tBodyId": 'actorChoicesTableBodyId',
				"oAlign": 'left',
				"parentId": 'actorForm'
			});
		}

		dhf.makeTableRow({ "oid": currentRowId, "ocn": 'noColor', "parentId": 'actorChoicesTableBodyId' });
		for (i = 0; i < collectionLength; i += 1) {
			actorData = editMovie.filteredActors[i];
			if (jQuery.inArray(actorData.actor_id, editMovie.selectedActors) > -1) {
				actorIsSelected = 'Y';
				labelClass = 'red';
			}
			else {
				actorIsSelected = 'N';
				labelClass = '';
			}
			dhf.makeTableCell({
				"parentId": currentRowId,
				"oAlign": 'left',
				"vAlign": 'middle',
				"iObjs": [
					{ "iFunctName": 'makeInputCheckbox', "iParams": { "oid": 'actorChoice'+currentColumnId, "oName": 'actorChoice', "ov": actorData.actor_id, "os": actorIsSelected } },
					{ "iFunctName": 'makeInputLabel', "iParams": {
						"oFor": 'actorChoice'+currentColumnId,
						"iHTML": actorData.actor_name,
						"ocn": labelClass
					} }
				]
			});
			if (columnIncrement == 4) {
				rowIncrement += 1;
				columnIncrement = 0;
				currentRowId = 'actorRow'+rowIncrement;
				currentColumnId = currentRowId+'Column'+columnIncrement;
				dhf.makeTableRow({ "oid": currentRowId, "ocn": 'noColor', "parentId": 'actorChoicesTableBodyId' });
			}
			else {
				columnIncrement += 1;
				currentColumnId = currentRowId+'Column'+columnIncrement;
			}
		}
		editMovie.buildActorNameString();
	},

	// If there is no data in the JSON array, create one row stating so.
	makeGridTableNoResults: function () {
		'use strict';
		dhf.makeTable({ "oid": "actorChoicesTable", "ocn": "dhf-grid-results", "parentId": "actorForm", "tBodyId": "actorChoicesTableBodyId" });
		dhf.makeTableRow({
			"parentId": "actorChoicesTableBodyId",
			"ocn": "dhf-noResults",
			"iObjs": [{ "iFunctName": "makeTableCell", "iParams": { "oAlign": "center", "ocn": "dhf-noResults", "iHTML": "No Results Found" } }]
		});
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 10);
	},

	clearForm: function () {
		'use strict';
		$D('movieId').value = '';
		$D('title').value = '';
		$D('sortTitle').value = '';
		$D('studio').value = '1';
		$D('collection').value = '';
		$D('rating').value = '6';
		$D('format').value = '4';
		jQuery('#genre').val('3').trigger("chosen:updated");
		$D('isActive').value = 'Y';
		$D('releaseDate').value = '';
		$D('movieScore').value = '0';
		$D('descShort').value = '';
		$D('descLong').value = '';
		$D('originalUrl').value = '';
		$D('actorNameString').innerHTML = '';
		$D('privateNotes').value = '';
		editMovie.selectedActors = [];
		editMovie.buildActorChoiceTable();
		editMovie.refreshVars();
	},

	clearMovieId: function () {
		'use strict';
		$D('actorNameString').innerHTML = '';
		editMovie.selectedActors = [];
		editMovie.buildActorChoiceTable();
		$D('movieId').value = '';
		$D('title').value = '';
		$D('sortTitle').value = '';
		$D('descLong').value = '';
		$D('privateNotes').value = '';
		$D('title').focus();
		editMovie.refreshVars();
	},

	clearActorFilter: function () {
		'use strict';
		$D('searchActor').value = '';
		editMovie.buildActorChoiceTable();
	},

	setPageTitle: function () {
		if ($D('title').value !== '') { document.title = $D('title').value+': Edit Movie'; }
	},

	areYouSure: function () {
		'use strict';
		let movieId = $D('movieId').value,
			buttonData = [
				{ "text": "Yes Delete", "click": function() { jQuery(this).dialog("close"); editMovie.deleteMovie(); } },
				{ "text": "Don't Delete", "click": function() { jQuery(this).dialog("close"); } }
			];

		if (movieId > 0) {
			dhf.popup.make({ "modal": true, "title": 'Confirm Delete Movie', "buttons": buttonData });
			dhf.popup.html('<p class="warningMessage">Are you sure you want to delete this movie?<br />This cannot be undone!</p>');
			dhf.popup.open();
		}
	},

	deleteMovie: function () {
		'use strict';
		let requestParams = { "className": "movieClass", "methodCall": "deleteMovie", "movieId": $D('movieId').value };
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			if (window.opener !== null) { window.opener.gridControl.refreshCollection(); }
			window.close();
		});
	},

	validateMovie: function () {
		'use strict';
		const movieIdObj = document.getElementById('movieId'),
			titleObj = document.getElementById('title'),
			sortTitleObj = document.getElementById('sortTitle'),
			collectionObj = document.getElementById('collection'),
			releaseDateObj = document.getElementById('releaseDate'),
			descShortObj = document.getElementById('descShort'),
			descLongObj = document.getElementById('descLong'),
			originalUrlObj = document.getElementById('originalUrl'),
			coverPosterUploadObj = document.getElementById('coverPosterUpload');

		if (titleObj.value === '') {
			titleObj.classList.add('invalid');
			document.querySelector('label[for=title]').classList.add('error');
			dhf.errorHandler.add("No Title Found");
		}
		else {
			titleObj.classList.remove('invalid');
			document.querySelector('label[for=title]').classList.remove('error')		
		}

		if (sortTitleObj.value === '') {
			sortTitleObj.classList.add('invalid');
			document.querySelector('label[for=sortTitle]').classList.add('error');
			dhf.errorHandler.add("No Sort Title Found");
		}
		else {
			sortTitleObj.classList.remove('invalid');
			document.querySelector('label[for=sortTitle]').classList.remove('error');
		}
		
		if (collectionObj.value === '') {
			collectionObj.classList.add('invalid');
			document.querySelector('label[for=collection]').classList.add('error');
			dhf.errorHandler.add("No Collection Found");
		}
		else {
			collectionObj.classList.remove('invalid');
			document.querySelector('label[for=collection]').classList.remove('error');
		}		

		if (releaseDateObj.value === '') {
			releaseDateObj.classList.add('invalid');
			document.querySelector('label[for=releaseDate]').classList.add('error');
			dhf.errorHandler.add("No Release Date Found");
		}
		else {
			releaseDateObj.classList.remove('invalid');
			document.querySelector('label[for=releaseDate]').classList.remove('error');
		}

		if (descShortObj.value === '') {
			descShortObj.classList.add('invalid');
			document.querySelector('label[for=descShort]').classList.add('error');
			dhf.errorHandler.add("No Short Description Found");
		}
		else {
			descShortObj.classList.remove('invalid');
			document.querySelector('label[for=descShort]').classList.remove('error');
		}

		if (descLongObj.value === '') {
			descLongObj.classList.add('invalid');
			document.querySelector('label[for=descLong]').classList.add('error');
			dhf.errorHandler.add("No Long Description Found");
		}
		else {
			descLongObj.classList.remove('invalid');
			document.querySelector('label[for=descLong]').classList.remove('error');
		}

		if (originalUrlObj.value === '') {
			originalUrlObj.classList.add('invalid');
			document.querySelector('label[for=originalUrl]').classList.add('error');
			dhf.errorHandler.add("No Original URL Found");
		}
		else {
			originalUrlObj.classList.remove('invalid');
			document.querySelector('label[for=originalUrl]').classList.remove('error');
		}

		if (movieIdObj.value === '' && coverPosterUploadObj.value === '') {
			coverPosterUploadObj.classList.add('invalid');
			document.querySelector('label[for=coverPosterUpload]').classList.add('error');
			dhf.errorHandler.add("No Poster Selected");
		}
		else {
			coverPosterUploadObj.classList.remove('invalid');
			document.querySelector('label[for=coverPosterUpload]').classList.remove('error');
		}

		if (parseInt(movieIdObj.value, 10) > 0) {
			$D('moviePostAction').value = "Update This Movie";
		}
		else {
			$D('moviePostAction').value = "Add New Movie";
		}

		if (!dhf.errorHandler.display()) { $D('editMovieForm').submit(); }
	},

	goToActorProfile: function () {
		'use strict';
		let currObj = jQuery(this),
			actorId = currObj.attr("data-actorid"),
			url = '/editActor.php?actorId='+actorId,
			newWindow = '';

		newWindow = window.open(url, 'editActor'+actorId);
		newWindow.focus();
	},

	playSelectedMovie: function () {
		'use strict';
		let currObj = jQuery(this),
			movieId = currObj.attr("data-playmovieid"),
			url = '/playMovie.php?movieId='+movieId,
			newWindow = '';

		newWindow = window.open(url, 'playMovie'+movieId);
		newWindow.focus();
	},

	build_genreString: function () {
		'use strict';
		let genreCollection = $D("genre").options,
			genreLength = genreCollection.length,
			cleanCollection = [],
			genreParts = [],
			optionText = '',
			genreString = '',
			i = 0;

		for (i = 0; i < genreLength; i += 1) {
			if (genreCollection[i].selected === true) {
				optionText = genreCollection[i].text;
				if (optionText.search(/:/) > -1) {
					genreParts = optionText.split(":");
					optionText = genreParts[1];
				}
				if (optionText !== 'Has Chapters' && optionText !== '') {
					cleanCollection.push(optionText);
				}
			}
		}
		return cleanCollection.join(', ');
	},

	toggle_selectedGenre: function () {
		'use strict';
		let movieId = $D("movieId").value,
			selectedGenres = jQuery("#genre").val(),
			requestParams = { "className": "movieGenreXrefClass", "methodCall": "updateChoices", "movieId": movieId, "selectedGenres": JSON.stringify(selectedGenres) };

		if (parseInt($D('movieId').value, 10) > 0) {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				editMovie.refreshParent();
			});
		}
	},

	updateMovie: function () {
		'use strict';
		let movieId = $D("movieId").value,
			title = $D('title').value,
			sortTitle = $D('sortTitle').value,
			studio = $D('studio').value,
			collection = $D('collection').value,
			rating = $D('rating').value,
			format = $D('format').value,
			releaseDate = $D('releaseDate').value,
			descShort = $D('descShort').value,
			descLong = $D('descLong').value,
			movieScore = $D('movieScore').value,
			originalUrl = $D('originalUrl').value,
			isActive = $D('isActive').value,
			privateNotes = $D('privateNotes').value,
			requestParams = {
				"className": "movieClass",
				"methodCall": "updateMovie",
				"movieId": movieId,
				"title": title,
				"sortTitle": sortTitle,
				"studio": studio,
				"collection": collection,
				"rating": rating,
				"format": format,
				"releaseDate": releaseDate,
				"descShort": descShort,
				"descLong": descLong,
				"movieScore": movieScore,
				"originalUrl": originalUrl,
				"isActive": isActive,
				"privateNotes": privateNotes
			};

		if (parseInt($D('movieId').value, 10) > 0) {
			editMovie.refreshVars();
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(responseData) {
				if (responseData.errorMsg && responseData.errorMsg == 'BAD') { console.log("Error Saving Data"); }
				else {
					$D('cleanTitle').value = responseData.cleanTitle;
					$D('hasChapters').value = responseData.hasChapters;
					editMovie.refreshParent();
				}
			});
		}
	},

	build_actorString: function () {
		let i = 0,
			actorData = null,
			selectedActorNames = [],
			actorString  = '',
			actorChoiceLength = editMovie.allActorChoices.length;

		for (i = 0; i < actorChoiceLength; i += 1) {
			actorData = editMovie.allActorChoices[i];
			if (jQuery.inArray(actorData.actor_id, editMovie.selectedActors) > -1) {
				selectedActorNames.push(actorData.actor_name);
			}
		}
		return dhf.trim(selectedActorNames.join(', '));
	},

	fetch_selectMenuText: function (menuId) {
		'use strict';
		var myOpts,
			selectedLabel = '',
			i = 0;

		myOpts = $D(menuId).options;
		for (i = 0; i < myOpts.length; i += 1) {
			if (myOpts[i].selected === true) {
				selectedLabel = myOpts[i].text;
			}
		}
		return selectedLabel;
	},

	showExportData: function () {
		'use strict';
		let currObj = jQuery(this),
			exportString = '<p>',
			movieId = $D("movieId").value,
			movieTitle = $D("title").value,
			tmpDate,
			divObj;

		if ($D('exportDataDiv')) {
			document.body.removeChild($D("exportDataDiv"));
			return;
		}
		else {
			tmpDate = jQuery.datepicker.parseDate('yy-mm-dd', $D('releaseDate').value);
			tmpDate = jQuery.datepicker.formatDate('m/d/yy', tmpDate);
			exportString += '<span class="exportKey">Title:</span><span class="exportData">'+movieTitle+'</span><br>';
			exportString += '<span class="exportKey">Sort Title:</span><span class="exportData">'+$D("sortTitle").value+'</span><br>';
			exportString += '<span class="exportKey">Actors:</span><span class="exportData">'+editMovie.build_actorString()+'</span><br><br>';
			exportString += '<span class="exportKey">Studio:</span><span class="exportData">'+editMovie.fetch_selectMenuText('studio')+'</span><br>';
			exportString += '<span class="exportKey">Rating:</span><span class="exportData">'+editMovie.fetch_selectMenuText('rating')+'</span><br>';
			exportString += '<span class="exportKey">Genre:</span><span class="exportData">'+editMovie.build_genreString()+'</span><br>';
			exportString += '<span class="exportKey">Resolution:</span><span class="exportData">'+editMovie.fetch_selectMenuText('format')+'</span><br>';
			exportString += '<span class="exportKey">Score:</span><span class="exportData">'+editMovie.fetch_selectMenuText('movieScore')+'</span><br>';
			exportString += '<span class="exportKey">Release Date:</span><span class="exportData">'+tmpDate+'</span><br>';
			exportString += '<span class="exportKey">Short Desc:</span><span class="exportData">'+$D("descShort").value+'</span><br><br>';
			exportString += '<span class="exportKey">Description:</span>';
			exportString += '<div class="exportData">'+$D("descLong").value+'</div><br>';
			exportString += '</p>';

			exportString += '<p class="exportData"><a href="/editChapters.php?movieId='+movieId+'" target="_blank">Edit Chapter Data</a></p>';
			if (editMovie.hasChapters === 'Y') {
				exportString += '<p class="exportData">Download Chapter File: <a href="/downloadChapterFile.php?movieId='+movieId+'">'+movieTitle+'.Chapters.txt</a></p>';
			}
			exportString += '<p class="symlink closeLink">Close</p>';
			divObj = document.createElement("div");
			divObj.setAttribute('id', 'exportDataDiv');
			divObj.innerHTML = exportString;
			document.body.appendChild(divObj);
		}
	},

	openChapterEdit: function () {
		'use strict';
		let currObj = jQuery(this),
			movieId = $D("movieId").value,
			url = '/editChapters.php?movieId='+movieId,
			newWindow = '';
		if (parseInt(movieId, 10) > 0) {
			newWindow = window.open(url, 'editChapters'+movieId);
			newWindow.focus();
		}
		return;
	},

	autoSetCollection: function () {
		'use strict';
		let titleObj = jQuery("#title"),
			titleValue = titleObj.val(),
			collectionObj = jQuery("#collection"),
			collectionValue = collectionObj.val(),
			studioObj = jQuery("#studio"),
			studioValue = parseInt(studioObj.val(), 10);

		if (collectionValue === '' && titleValue !== '' && studioValue === 4) {
			if (jQuery('#genre').val() === null) { jQuery('#genre').val(['3']).trigger("chosen:updated"); }
		}
	}
};

jQuery(document).ready(function() {
	jQuery.datepicker.setDefaults({
		dateFormat: 'yy-mm-dd',
		showOn: 'both',
		buttonText: 'Choose Date',
		buttonImage: '/images/Calendar_scheduleHS.png',
		buttonImageOnly: true,
		mandatory: true,
		minDate: new Date(2010, 1, 1),
		maxDate: '+1m',
		changeYear: true,
		changeMonth: true,
		hideIfNoPrevNext: true,
		monthNamesShort: ['Jan (1)', 'Feb (2)', 'Mar (3)', 'Apr (4)', 'May (5)', 'Jun (6)', 'Jul (7)', 'Aug (8)', 'Sep (9)', 'Oct (10)', 'Nov (11)', 'Dec (12)']
	});

	jQuery("#releaseDate").datepicker();
	jQuery("#buttonClearForm").button().click(editMovie.clearForm);
	jQuery("#addNewActorButton").button().click(editMovie.addNewActor);
	jQuery("#buttonAddUpdateMovie").button().click(editMovie.validateMovie);
	jQuery("#buttonDeleteMovie").button().click(editMovie.areYouSure);
	jQuery("#buttonExportMetaData").button().click(editMovie.showExportData);
	jQuery("#buttonEditChapters").button().click(editMovie.openChapterEdit);
	jQuery("#searchActor").keyup(editMovie.searchFilterDelay);
	jQuery("#doSearch").button().click(editMovie.buildActorChoiceTable);
	jQuery("#clearSearch").button().click(editMovie.clearActorFilter);
	jQuery("#title").change(editMovie.setPageTitle);
	jQuery("#clearMovieId").click(editMovie.clearMovieId);
	jQuery(document).on("click", ":checkbox", editMovie.toggleSelectedActor);
	jQuery(document).on("click", "span[data-actorid]", editMovie.goToActorProfile);
	jQuery("#genre").chosen().change(editMovie.toggle_selectedGenre);
	jQuery(document).on("click", "img[data-playmovieid]", editMovie.playSelectedMovie);
	jQuery(document).on("click", ".closeLink", function () { document.body.removeChild($D("exportDataDiv")); });
	jQuery(document).on("focus, click", "input, textarea", function() { this.select(); });
	editMovie.init();

	jQuery("#editMovieForm").change(function() { jQuery("#buttonAddUpdateMovie").addClass("changeMade"); });
});

</script>
</head>
<body>
<div id="container">
	<div id="wrap">
		<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
		<div id="dhf-waitDivHolder"></div>
		<fieldset id="main" class="adminEditBlock">
		<legend>Edit/Add Movie</legend>
		<form id="editMovieForm" name="editMovieForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" accept-charset="utf-8" method="post" autocomplete="off">
		<input type="hidden" id="moviePostAction" name="moviePostAction" value="" />
		<input type="hidden" id="cleanTitle" name="cleanTitle" value="<?php print $movieObj->cleanTitle; ?>" />
		<input type="hidden" id="hasChapters" name="hasChapters" value="<?php print $movieObj->hasChapters; ?>" />
		<table id="editMovieFormTable">
			<tbody>
				<tr>
					<td id="titleCell" colspan="2" class="alignRight colWidth1">
						<img src="/images/X.png" width="20" height="20" title="Clear Movie Id" id="clearMovieId" />
						<input name="movieId" id="movieId" type="text" title="Movie ID" value="<?php print ($movieObj->movieId > 0) ? $movieObj->movieId : ''; ?>" />
						<label for="title">Title</label>
						<input class="inputWidth1" name="title" id="title" type="text" title="Movie Title" required="required" placeholder="Movie Title" value="<?php print $movieObj->title; ?>" />
					</td>

					<td id="studioCell" class="alignRight colWidth2">
						<label for="studio">Studio</label>
						<select class="selectWidth1" name="studio" id="studio" title="Movie Studio">
						<?php print $movieObj->make_studioChoiceMenu($movieObj->studio); ?>
						</select>
					</td>

					<td id="posterCell" rowspan="6">
					<?php if ($posterPath != '') { ?>
					<img title="Play Movie" src="<?php print $posterPath; ?>" width="<?php print $posterWidth; ?>" height="<?php print $posterHeight; ?>" data-playmovieid="<?php print $movieObj->movieId; ?>" class="cursor-pointer" />
					<?php } else { ?>&nbsp;<?php } ?>
					</td>
				</tr>

				<tr>
					<td id="sortTitleCell" colspan="2" class="alignRight colWidth1">
						<label for="sortTitle">Sort Title</label>
						<input class="inputWidth1" name="sortTitle" id="sortTitle" type="text" title="Movie Sort Title" required="required" placeholder="Movie Sort Title" value="<?php print $movieObj->sortTitle; ?>" />
					</td>

					<td id="ratingCell" class="alignRight colWidth2">
						<label for="rating">Rating</label>
						<select class="selectWidth1" name="rating" id="rating" title="Movie Rating">
						<?php print $movieObj->make_ratingChoiceMenu(); ?>
						</select>
					</td>
				</tr>

				<tr>
					<td id="collectionCell" colspan="2" class="alignRight colWidth1">
						<label for="collection">Collection</label>
						<input class="inputWidth1" name="collection" id="collection" type="text" title="Collection Name" required="required" placeholder="Collection Name" value="<?php print $movieObj->collection; ?>" />
					</td>

					<td id="formatCell" class="alignRight  colWidth2">
						<label for="format">Resolution</label>
						<select class="selectWidth1" name="format" id="format" title="Movie Resolution">
						<?php print $movieObj->make_formatChoiceMenu(); ?>
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="3">
					<table style="width: 100%;">
						<tbody>
							<tr>
								<td id="movieScoreCell" class="alignRight">
									<label for="movieScore">Score</label>
									<select name="movieScore" id="movieScore" title="Movie Rating">
									<?php print $movieObj->make_movieScoreChoiceMenu(); ?>
									</select>
								</td>

								<td id="isActiveCell" class="alignRight">
									<select name="isActive" id="isActive" title="Is Movie Active">
									<?php print $movieObj->build_select_menu(array('Y' => 'Movie is Active', 'N' => 'Movie is NOT Active'), $movieObj->isActive); ?>
									</select>
								</td>

								<td id="releaseDateCell" class="alignRight">
									<label for="releaseDate">Release Date</label>
									<input name="releaseDate" id="releaseDate" type="text" title="Release Date" required="required" placeholder="1970-01-01" value="<?php print $movieObj->releaseDate; ?>" />
								</td>
							</tr>
						</tbody>
					</table>
					</td>
				</tr>

				<tr>
					<td colspan="3">
					<label for="descShort">Short Description</label><br />
					<textarea name="descShort" id="descShort" title="Short Description" required="required" placeholder="Short Description"><?php print $movieObj->descShort; ?></textarea>
					</td>
				</tr>

				<tr>
					<td colspan="3">
					<label for="descLong">Long Description</label><br />
					<textarea name="descLong" id="descLong" title="Long Description" required="required" placeholder="Long Description"><?php print $movieObj->descLong; ?></textarea>
					</td>
				</tr>

				<tr>
					<td id="genreCell" colspan="4" class="alignRight">
					<label for="genre">Genre:</label>
					<select name="genre[]" id="genre" title="Movie Genre" data-placeholder="Choose 1 or more Genres" class="chosen-ltr" multiple="multiple">
					<?php print $movieObj->make_genreChoiceMenu(); ?>
					</select>
					</td>
				</tr>

				<tr>
					<td colspan="4" class="alignRight">
					<label for="originalUrl">Original URL:</label>
					<input name="originalUrl" id="originalUrl" type="url" title="Original URL" required="required" placeholder="www.domain.com" value="<?php print $movieObj->originalUrl; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<label for="coverPosterUpload">Poster Upload:</label>
					<input type="file" id="coverPosterUpload" name="coverPosterUpload" />
					</td>
					<td colspan="3" id="actorNameString"></td>
				</tr>

				<tr>
					<td colspan="4">
					<label for="backgroundUpload">Background Upload:</label>
					<input type="file" id="backgroundUpload" name="backgroundUpload" />
					</td>
				</tr>

				<tr>
					<td id="actionButtonRow" colspan="4">
						<button type="button" id="buttonAddUpdateMovie" value="Add/Update Movie" class="width170">Add/Update Movie</button>
						<button type="button" id="buttonClearForm" value="Clear Form" class="width170">Clear Form</button>
						<button type="button" id="buttonDeleteMovie" value="Delete This Movie" class="width170">Delete This Movie</button>
						<button type="button" id="buttonExportMetaData" value="Export Meta Data" class="width170">Export Meta Data</button>
						<button type="button" id="buttonEditChapters" value="Edit Chapter Data" class="width170">Edit Chapter Data</button>
					</td>
				</tr>

				<?php if ($_SESSION[$_SERVER['VHOST']]['user_login']['userId'] == 1) { ?>
				<tr>
					<td colspan="4">
					<label for="privateNotes">Private Notes</label><br />
					<textarea name="privateNotes" id="privateNotes" title="Private Notes" placeholder="Private Notes"><?php print $movieObj->privateNotes; ?></textarea>
					</td>
				</tr>
			</tbody>
			</table>
			<?php } else { ?>
			</tbody>
			</table>
			<input name="privateNotes" id="privateNotes" type="hidden" value="<?php print $movieObj->privateNotes; ?>" />
			<?php } ?>
		</form>
		</fieldset>

		<fieldset class="adminEditBlock">
		<legend>Filter Actors</legend>
		<form id="actorForm" name="actorForm">
		<p class="buttonBlock">
		<input type="text" id="searchActor" placeholder="Search Actors Name" class="plex-corner-all" />
		<button type="button" id="doSearch" value="Filter Actors" class="width170">Filter Actors</button>
		<button type="button" id="clearSearch" value="Clear Filter" class="width170">Clear Filter</button>
		<button type="button" id="addNewActorButton" value="Add New Actor" class="width170">Add New Actor</button>
		</p>
		</form>
		<br />

		</fieldset>

		<br /><br /><br />
	</div>
</div>
</body>
</html>
