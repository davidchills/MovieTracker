<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
$movieObj = new MOVIE();
$movieObj->studio = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Movie List</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>
<style type="text/css">
#dhf-resultsTableContainer {
	height: 760px;
	overflow: auto;
	display: block;
}
#goToLetter {
	width: 95%;
	overflow: auto;
	display: block;
	margin: 3px auto;
}
td[data-gotostartletter] {
	cursor: pointer;
	text-align: left;
	vertical-align: middle;
	color: navy;
	width: 35px;
	font-weight: bold;
}
td[data-gotostartletter].red {
	color: red;
}
#filterBlock {
	width: 95%;
	margin-left: 5%;
}
#searchMovies {
	width: 225px;
	height: 25px;
	line-height: 25px;
	font-size: 14px;
	vertical-align: middle;
}
button {
	min-width: 125px;
	cursor: pointer;
	margin-left: 3px;
	margin-right: 3px;
}
input[type="text"], input[type="search"] {
	height: 25px;
}
input[type="text"], input[type="search"], textarea, #searchMovies {
	background-color: rgba(177, 217, 249, 0.25);
	border: 1px inset #AAAAAA;
	cursor: text;
	padding: 0 5px;
	position: relative;
}
input[type="text"]:focus, input[type="search"]:focus, textarea:focus, #searchMovies:focus {
	background-color: rgba(243, 244, 178, 0.25);
}
#moviePosterPreview {
	border: 0;
	margin: 10px;
}
#moviePosterPreviewDiv {
	position: absolute;
	top: 200px;
	left: 200px;
	background-color: white;
	border: solid black 1px;
	text-align: center;
	vertical-align: middle;
}
#moviePosterPreviewClose {
	cursor: pointer;
	color: navy;
	display: block;
	font-size: 16px;
	font-weight: bold;
	text-align: center;
	margin: 10px auto;
}
td.scoreCell {
	width: 55px;
}
#studio { margin-top: 8px; }
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>
<script type="text/javascript">

var gridControl = {

	delayTimeId: null,
	reportTableContent: [],
	searchedTableContent: [],
	lastSortValue: 'sort_title',
	reverseSort: 'N',
	selectedRows: [],
	startLetters: [],

	// Need to do a couple things after the page loads.
	init: function () {
		'use strict';
		jQuery.when(gridControl.fetch_movieCollection()).then(function () {
			gridControl.draw_goToLetter();
			gridControl.filterBySearch();
		});
	},

	// Loads grid data fresh from the server. Used when a child page updates a movie.
	refreshCollection: function () {
		'use strict';
		gridControl.selectedRows = [];
		jQuery(".selectedRow").each(function() { gridControl.selectedRows.push(this.id); });

		// Waits for the promise to be resolved before marking the rows selected that were selected before the refresh.
		jQuery.when(gridControl.fetch_movieCollection()).then(function () {
			var i = 0,
				localRows = gridControl.selectedRows,
				selectedRowsLength = localRows.length;

			if ((jQuery("td.red")).length == 1) { jQuery("td.red").trigger("click"); }
			else { gridControl.filterBySearch(); }
			for (i = 0; i < selectedRowsLength; i += 1) {
				jQuery("#"+localRows[i]).addClass('selectedRow');
			}
		});
	},

	// AJAX call to get the fresh movie data. Returns a promise.
	fetch_movieCollection: function () {
		'use strict';
		var requestParams = { "className": "movieClass", "methodCall": "fetchMovieCollection" },
			dfWait,
			movieData,
			firstLetter = '',
			collectionLength = 0,
			i = 0;

		dhf.util.displayWaitDiv('Getting Collection.....', 'dhf-waitDivHolder');

		// Reset the collection to empty.
		gridControl.reportTableContent = [];

		// Fetch the new data and return a promise.
		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(collectionData) {

			collectionLength = collectionData.length;
			for (i = 0; i < collectionLength; i += 1) {
				movieData = collectionData[i];
				firstLetter = movieData.sort_title.charAt(0).toUpperCase();
				if (jQuery.inArray(firstLetter, gridControl.startLetters) === -1) {
					gridControl.startLetters.push(firstLetter);
				}
				gridControl.reportTableContent.push(movieData);
			}
		});
		return dfWait.promise();
	},

	// Draw a menu of Letters the list movies with the sort title starting with that letter.
	draw_goToLetter: function () {
		'use strict';
		var currentLetter = '',
			singleCell,
			singleLetter,
			letterCellCollection = [],
			startLetterLength = gridControl.startLetters.length,
			i = 0;

		// Remove the existing row and start over.
		if ($D('goToLetterRow')) { jQuery("#goToLetterRow").remove(); }
		for (i = 0; i < startLetterLength; i += 1) {
			singleLetter = gridControl.startLetters[i];
			singleCell = { "iFunctName": "makeTableCell", "iParams": { "oAttr": { "oName": "data-gotostartletter", "oValue": singleLetter }, "iHTML": singleLetter } };
			letterCellCollection.push(singleCell);
		}
		// Add an option to clear and start from scratch.
		singleCell = { "iFunctName": "makeTableCell", "iParams": { "oAttr": { "oName": "data-gotostartletter", "oValue": "" }, "iHTML": "Clear" } };
		letterCellCollection.push(singleCell);

		dhf.makeTableRow({
			"oid": "goToLetterRow",
			"parentId": "goToLetterBody",
			"iObjs": letterCellCollection
		});
	},

	// Does the actual filtering and displaying of matched movies.
	goToLetter: function () {
		'use strict';
		var currObj = jQuery(this),
			currentLetter = currObj.attr("data-gotostartletter"),
			i = 0,
			movieData = null,
			collectionLength = gridControl.reportTableContent.length;

		// Clear any previous searches and styles.
		$D('searchMovies').value = '';
		jQuery("td.red").removeClass("red");

		// If the clear option was clicked, cleanup and start from scratch.
		if (!currentLetter) { gridControl.searchedTableContent = gridControl.reportTableContent; }
		else {
			currObj.addClass("red");
			gridControl.searchedTableContent = [];
			for (i = 0; i < collectionLength; i += 1) {
				movieData = gridControl.reportTableContent[i];
				if (movieData.sort_title.charAt(0).toUpperCase() === currentLetter) {
					gridControl.searchedTableContent.push(movieData);
				}
			}
		}
		if (gridControl.searchedTableContent.length > 0) {
			gridControl.searchedTableContent = dhf.util.sortArray(gridControl.searchedTableContent, gridControl.lastSortValue, gridControl.reverseSort);
		}
		gridControl.makeGridTable();
	},


	// Adds a delay in redrawing the grid to allow typing.
	searchFilterDelay: function () {
		'use strict';
		clearTimeout(gridControl.delayTimeId);
		gridControl.delayTimeId = setTimeout(function() { gridControl.filterBySearch(); }, 750);
	},

	// Filters out entries that match the input string.
	filterBySearch: function () {
		'use strict';
		var i = 0,
			movieData = null,
			filteredCollection = [],
			filteredCollectionLength = 0,
			studioId = parseInt($D('studio').value, 10),
			collectionLength = gridControl.reportTableContent.length,
			searchTextPattern = new RegExp($D('searchMovies').value, "i");

		gridControl.searchedTableContent = [];
		jQuery("td.red").removeClass("red");
		if (studioId == 0) { filteredCollection = gridControl.reportTableContent; }
		else {
			for (i = 0; i < collectionLength; i += 1) {
				movieData = gridControl.reportTableContent[i];
				if (parseInt(movieData.studio, 10) === studioId) { filteredCollection.push(movieData); }
			}
		}
		if ($D('searchMovies').value == '') { gridControl.searchedTableContent =  filteredCollection; }
		else {
			filteredCollectionLength = filteredCollection.length;
			for (i = 0; i < filteredCollectionLength; i += 1) {
				movieData = filteredCollection[i];
				if (movieData.title.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.cleanTitle.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.actors.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.genre.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.resolution.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.studio_name.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
				else if (movieData.studio_short_name.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(movieData);
				}
			}
		}
		if (gridControl.searchedTableContent.length > 0) {
			gridControl.searchedTableContent = dhf.util.sortArray(gridControl.searchedTableContent, gridControl.lastSortValue, gridControl.reverseSort);
		}
		gridControl.makeGridTable();
	},

	// If sorting check to see if we need to reverse it or not.
	checkReverseSort: function (sortField) {
		'use strict';
		if (sortField === gridControl.lastSortValue) { gridControl.reverseSort = (gridControl.reverseSort === 'N') ? 'Y' : 'N'; }
		else {
			gridControl.reverseSort = 'N';
			gridControl.lastSortValue = sortField;
		}
	},

	// Handler when a column header is clicked.
	sortBy: function () {
		'use strict';
		var currObj = jQuery(this),
			sortField = currObj.attr("data-sortby");

		dhf.util.displayWaitDiv('Sorting.....', 'dhf-waitDivHolder');
		gridControl.checkReverseSort(sortField);
		gridControl.filterBySearch();
	},

	// Draws the core of the table body.
	makeGridTable: function () {
		'use strict';
		var i = 0,
			x = 0,
			tmpRowId,
			rowContent,
			actorCell,
			posterCell,
			scoreCell,
			promise,
			movieActors = '',
			rowCellCollection = [],
			displayRowCount = gridControl.searchedTableContent.length;

		if ($D('resultsBody')) { jQuery("#resultsBody").remove(); }

		if (displayRowCount < 1) {
			gridControl.makeGridTableNoResults();
			return;
		}

		dhf.makeTable({ "oid": "resultsBody", "ocn": "dhf-grid-results", "parentId": "dhf-resultsTableContainer", "tBodyId": "resultsTbody" });
		gridControl.makeGridTableHeaderRow();
		dhf.setDefaults('defaultTableCell', { "nWrap": "nowrap", "ocn": "gridDataCell" });
		dhf.rowColor.increment = 1;

		for (i = 0; i < displayRowCount; i += 1) {
			rowCellCollection = [];
			rowContent = gridControl.searchedTableContent[i];
			movieActors = (rowContent.actors.length > 30) ? rowContent.actors.substring(0, 27)+'...' : rowContent.actors;
			tmpRowId = 'dataRow'+rowContent.movie_id;

			actorCell = { "iFunctName": "makeTableCell", "iParams": {
				"oid": "actorCell"+rowContent.movie_id,
				"ocn": "gridDataCell",
				"iObjs": [
					{ "iFunctName": 'makeSpan', "iParams": {
						"oid": 'actorSpan'+rowContent.movie_id,
						"iHTML": movieActors,
						"oTitle": rowContent.actors,
						"ocn": 'cursor-help'
					} }
				]
			} };

			posterCell = { "iFunctName": "makeTableCell", "iParams": {
				"oAlign": 'center',
				"vAlign": 'middle',
				"iObjs": [
					{ "iFunctName": 'makeImage', "iParams": { "oSrc": '/images/previewImg.png', "oWidth": '16', "oHeight": '16', "ocn": 'cursor-pointer', "oAttr": { "oName": "data-moviepostereid", "oValue": rowContent.movie_id } } }
				]
			} };

			scoreCell = { "iFunctName": "makeTableCell", "iParams": {
				"oid": "scoreCell"+rowContent.movie_id,
				"ocn": "gridDataCell scoreCell",
				"iObjs": [
					{ "iFunctName": 'makeSpan', "iParams": {
						"oid": 'scoreSpan'+rowContent.movie_id,
						"iHTML": rowContent.movie_score,
						"oTitle": 'click to update',
						"ocn": 'cursor-pointer',
						"oAttr": [{ "oName": "data-setmoviescoreid", "oValue": rowContent.movie_id },{ "oName": "data-setmoviescorevalue", "oValue": rowContent.movie_score }]
					} }
				]
			} };

			rowCellCollection.push(dhf.makeIobjsCell(i+1));
			rowCellCollection.push(dhf.makeIobjsCell(dhf.util.getMovieHtml(rowContent.movie_id, rowContent.title)));
			rowCellCollection.push(actorCell);
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.genre));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.studio_short_name));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.resolution));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.release_date));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.added_date));
			rowCellCollection.push(scoreCell);
			rowCellCollection.push(posterCell);

			dhf.makeTableRow({
				"oid": tmpRowId,
				"parentId": "resultsTbody",
				"altColor": "Y",
				"iObjs": rowCellCollection
			});

		}
		dhf.setDefaults('defaultTableCell', { "ocn": "" });
		setTimeout(function() {
			dhf.util.removeWaitDiv();
			jQuery("#dhf-resultsTableContainer span.cursor-help").tooltip();
		}, 700);
	},

	// Draws the header thead row for the table.
	makeGridTableHeaderRow: function () {
		'use strict';
		var sortClass = (gridControl.reverseSort === 'Y') ? 'reverseSorted' : 'sorted',
			headerCellCollection = [];

		dhf.setDefaults('defaultTableCell', { nWrap: 'nowrap', ocn: 'jsLink' });

		headerCellCollection.push({ "iFunctName": "makeTableCell", "iParams": { "iHTML": '&nbsp;', "ocn": "" } });
		headerCellCollection.push(dhf.makeSortHeaderCell("Movie Title", "sort_title"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Actors", "actors"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Genre", "genre"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Studio", "studio_name"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Resolution", "resolution"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Release Date", "release_date"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Added Date", "added_date"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Score", "movie_score"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Poster"));

		dhf.makeTableHeader({
			"oid": "resultsThead",
			"parentId": "resultsBody",
			"iObjs": [
				{ "iFunctName": "makeTableRow",
					"iParams": {
						"oid": "gridHeaderRow",
						"parentId": "resultsThead",
						"ocn": "dhf-grid-header-row",
						"iObjs": headerCellCollection
					}
				}
			]
		});

		dhf.setDefaults('defaultTableCell', { "ocn": "" });
		jQuery("#gridHeaderRow > td").removeClass('sorted');
		jQuery("#"+gridControl.lastSortValue+"Header").addClass(sortClass);
	},

	// If there is no data in the JSON array, create one row stating so.
	makeGridTableNoResults: function () {
		'use strict';
		dhf.makeTable({ "oid": "resultsBody", "ocn": "dhf-grid-results", "parentId": "dhf-resultsTableContainer", "tBodyId": "resultsTbody" });
		dhf.makeTableRow({
			"parentId": "resultsTbody",
			"ocn": "dhf-noResults",
			"iObjs": [{ "iFunctName": "makeTableCell", "iParams": { "oAlign": "center", "ocn": "dhf-noResults", "iHTML": "No Results Found" } }]
		});
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 700);
	},

	// Simple method to set the background color orange when a row is clicked on.
	colorRow: function (rowId) { jQuery($D(rowId)).toggleClass('selectedRow'); },

	clearMovieFilter: function () {
		'use strict';
		$D('studio').value = '0';
		$D('searchMovies').value = '';
		$D('searchMovies').focus();
		gridControl.filterBySearch();
	},

	addNewMovie: function () {
		'use strict';
		var day = new Date(),
			id = day.getTime(),
			newPageName = 'page'+id,
			titleValue = jQuery("#searchMovies").val(),
			newWindow = window.open('/editMovie.php?movieId=0&titleValue='+encodeURIComponent(titleValue), newPageName);

		newWindow.focus();
	},

	showMoviePoster: function () {
		'use strict';
		var currObj = jQuery(this),
			currObjPosition = currObj.position(),
			movieId = currObj.attr("data-moviepostereid"),
			requestParams = { "className": "moviePosterClass", "methodCall": "fetchPosterObj", "movieId": movieId };

		if ($D('moviePosterPreviewDiv')) { document.body.removeChild($D("moviePosterPreviewDiv")); }
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(posterData) {
			var divObj,
				imgObj;

			imgObj = document.createElement("img");
			imgObj.setAttribute('id', 'moviePosterPreview');
			imgObj.setAttribute('src', '/posters/'+posterData.posterName);
			imgObj.setAttribute('width', posterData.posterWidth);
			imgObj.setAttribute('height', posterData.posterHeight);

			divObj = document.createElement("div");
			divObj.setAttribute('id', 'moviePosterPreviewDiv');
			divObj.style.width = (posterData.posterWidth+20)+'px';
			divObj.style.height = (posterData.posterHeight+20)+'px';

			divObj.style.top = (currObjPosition.top-180)+'px';
			divObj.style.left = (currObjPosition.left-270)+'px';

			divObj.appendChild(imgObj);
			document.body.appendChild(divObj);
		});
	},

	removeMoviePoster: function () {
		if ($D('moviePosterPreviewDiv')) { document.body.removeChild($D("moviePosterPreviewDiv")); }
	},

	make_movieScoreMenu: function () {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.attr("data-setmoviescoreid"),
			currentScore = currObj.attr("data-setmoviescorevalue"),
			menuOptions = [],
			i = 0;

		if ($D('scoreSpan'+movieId)) {
			$D('scoreCell'+movieId).removeChild($D('scoreSpan'+movieId));
			for (i = 0; i <=10; i += 1) {
				if (parseInt(currentScore, 10) === i) { menuOptions.push({ "ol": i, "ov": i, "os": 'Y' }); }
				else { menuOptions.push({ "ol": i, "ov": i, "os": 'N' }); }
			}

			dhf.makeInputSelect({
				"oid": 'scoreMenu'+movieId,
				"parentId": 'scoreCell'+movieId,
				"optionsArray": menuOptions,
				"oValue": currentScore,
				"oAttr": [{ "oName": "data-setmoviescoreid", "oValue": movieId }]
			});
		}
		return false;
	},

	make_movieScoreSpan: function() {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.attr("data-setmoviescoreid"),
			currentScore = currObj.val(),
			dfWait,
			requestParams = {
				"className": "movieClass",
				"methodCall": "updateMovie",
				"movieId": movieId,
				"movieScore": currentScore
			};

		dhf.util.displayWaitDiv('Updating.....', 'dhf-waitDivHolder');
		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(responseData) {
			if (responseData.errorMsg == 'BAD') { console.log("Error Saving Data"); }
			else { /* Do Nothing */ }

			if ($D('scoreMenu'+movieId)) {
				$D('scoreCell'+movieId).removeChild($D('scoreMenu'+movieId));
				dhf.makeSpan({
					"oid": "scoreSpan"+movieId,
					"parentId": 'scoreCell'+movieId,
					"iHTML": currentScore,
					"oTitle": 'click to update',
					"ocn": 'cursor-pointer',
					"oAttr": [{ "oName": "data-setmoviescoreid", "oValue": movieId },{ "oName": "data-setmoviescorevalue", "oValue": currentScore }]
				});
			}
		});

		jQuery.when(dfWait).then(function () {
			setTimeout(function() { dhf.util.removeWaitDiv(); }, 700);
		});
		return false;
	},

	playSelectedMovie: function () {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.attr("data-moviepostereid"),
			url = '/playMovie.php?movieId='+movieId,
			newWindow = '';

		newWindow = window.open(url, 'playMovie'+movieId);
		newWindow.focus();
	}
};

jQuery(document).ready(function() {
	gridControl.init();
	jQuery("#searchMovies").keyup(gridControl.searchFilterDelay);
	jQuery("#doSearch").button().click(gridControl.filterBySearch);
	jQuery("#studio").click(gridControl.filterBySearch);
	jQuery("#clearSearch").button().click(gridControl.clearMovieFilter);
	jQuery("#addNewMovie").button().click(gridControl.addNewMovie);
	jQuery("#refreshCollection").button().click(gridControl.refreshCollection);
	jQuery(document).on("click", "td[data-sortby]", gridControl.sortBy);
	jQuery(document).on("click", "#resultsTbody tr", function () { gridControl.colorRow(this.id); });
	jQuery(document).on("mouseover", "img[data-moviepostereid]", gridControl.showMoviePoster);
	jQuery(document).on("mouseout", "img[data-moviepostereid]", gridControl.removeMoviePoster);
	jQuery(document).on("click", "td[data-gotostartletter]", gridControl.goToLetter);
	jQuery(document).on("click", "span[data-setmoviescoreid]", gridControl.make_movieScoreMenu);
	jQuery(document).on("change", "select[data-setmoviescoreid]", gridControl.make_movieScoreSpan);
	jQuery(document).on("click", "img[data-moviepostereid]", gridControl.playSelectedMovie);
	jQuery(document).on("focus, click", "input, textarea", function() { this.select(); });
	$D('searchMovies').focus();
});

</script>
</head>
<body>
<div id="container">
	<div id="wrap">
		<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
		<div id="dhf-waitDivHolder"></div>

		<p id="filterBlock">
		<input type="text" id="searchMovies" placeholder="Search Movie, Genre or Actor Name" class="plex-corner-all" />
		<button type="button" id="doSearch" value="Filter Movies">Filter Movies</button>
		<button type="button" id="clearSearch" value="Clear Filter">Clear Filter</button>
		<button type="button" id="addNewMovie" value="Add New Movie">Add New Movie</button>
		<button type="button" id="refreshCollection" value="Refresh Grid">Refresh Grid</button>
		<select class="selectWidth1" name="studio" id="studio" title="Movie Studio">
		<option value="0">ALL</option>
		<?php print $movieObj->make_studioChoiceMenu(); ?>
		</select>
		</p>

		<table id="goToLetter">
			<tbody id="goToLetterBody">
				<tr id="goToLetterRow">
					<td data-gotostartletter="A">A</td>
					<td data-gotostartletter="B">B</td>
					<td data-gotostartletter="C">C</td>
					<td data-gotostartletter="D">D</td>
					<td data-gotostartletter="E">E</td>
					<td data-gotostartletter="F">F</td>
					<td data-gotostartletter="G">G</td>
					<td data-gotostartletter="H">H</td>
					<td data-gotostartletter="I">I</td>
					<td data-gotostartletter="J">J</td>
					<td data-gotostartletter="K">K</td>
					<td data-gotostartletter="L">L</td>
					<td data-gotostartletter="M">M</td>
					<td data-gotostartletter="N">N</td>
					<td data-gotostartletter="O">O</td>
					<td data-gotostartletter="P">P</td>
					<td data-gotostartletter="Q">Q</td>
					<td data-gotostartletter="R">R</td>
					<td data-gotostartletter="S">S</td>
					<td data-gotostartletter="T">T</td>
					<td data-gotostartletter="U">U</td>
					<td data-gotostartletter="V">V</td>
					<td data-gotostartletter="W">W</td>
					<td data-gotostartletter="X">X</td>
					<td data-gotostartletter="Y">Z</td>
					<td data-gotostartletter="Z">Z</td>
				</tr>
			</tbody>
		</table>

		<div id="dhf-resultsTableContainer"></div>
	</div>
</div>
</body>
</html>