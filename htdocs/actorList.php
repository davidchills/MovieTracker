<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
$actorObj = new ACTOR();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Actor List</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>
<style type="text/css">
#dhf-resultsTableContainer {
	height: 760px;
	overflow: auto;
	display: block;
}
#filterBlock {
	width: 95%;
	margin-left: 5%;
}
#searchActor {
	width: 225px;
}
button {
	min-width: 150px;
	cursor: pointer;
	margin-left: 3px;
	margin-right: 3px;
}
input[type="text"], input[type="search"] {
	height: 25px;
}
#actorPicturePreview {
	border: 0;
	margin: 10px;
}
#actorPicturePreviewDiv {
	position: absolute;
	top: 200px;
	left: 200px;
	background-color: white;
	border: solid black 1px;
	text-align: center;
	vertical-align: middle;
}
#actorPicturePreviewClose {
	cursor: pointer;
	color: navy;
	display: block;
	font-size: 16px;
	font-weight: bold;
	text-align: center;
	margin: 5px auto;
}
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>
<script type="text/javascript">
var MOVIETRACKER = {

	delayTimeId: null,
	indexActorCollection: [],
	indexActorCollectionFiltered: [],
	lastSortValue: 'actor_name',
	reverseSort: 'N',
	selectedRows: [],

	actorIndexinit: function () {
		'use strict';
		jQuery.when(MOVIETRACKER.fetch_actorCollectionIndex()).then(function () {
			MOVIETRACKER.filterBySearchActorIndex();
		});
	},

	refreshCollectionActorIndex: function () {
		'use strict';
		MOVIETRACKER.selectedRows = [];
		jQuery(".selectedRow").each(function() { MOVIETRACKER.selectedRows.push(this.id); });
		jQuery.when(MOVIETRACKER.fetch_actorCollectionIndex()).then(function () {
			var i = 0,
				localRows = MOVIETRACKER.selectedRows,
				selectedRowsLength = localRows.length;

			if ((jQuery("td.red")).length == 1) { jQuery("td.red").trigger("click"); }
			else { MOVIETRACKER.filterBySearchActorIndex(); }
			for (i = 0; i < selectedRowsLength; i += 1) {
				jQuery("#"+localRows[i]).addClass('selectedRow');
			}
		});
	},

	fetch_actorCollectionIndex: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "fetchActorCollection" },
			dfWait;

		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(collectionData) {
			MOVIETRACKER.indexActorCollection = collectionData;
		});
		return dfWait.promise();
	},

	searchFilterDelayActorIndex: function () {
		'use strict';
		clearTimeout(MOVIETRACKER.delayTimeId);
		MOVIETRACKER.delayTimeId = setTimeout(function() { MOVIETRACKER.filterBySearchActorIndex(); }, 750);
	},

	filterBySearchActorIndex: function () {
		'use strict';
		var i = 0,
			actorData = null,
			collectionLength = MOVIETRACKER.indexActorCollection.length,
			searchTextPattern = new RegExp($D('searchActor').value, "i");

		MOVIETRACKER.indexActorCollectionFiltered = [];

		if ($D('searchActor').value === '') { MOVIETRACKER.indexActorCollectionFiltered =  MOVIETRACKER.indexActorCollection; }
		else {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = MOVIETRACKER.indexActorCollection[i];
				if (actorData.actor_name.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexActorCollectionFiltered.push(actorData);
				}
			}
		}
		if (MOVIETRACKER.indexActorCollectionFiltered.length > 0) {
			MOVIETRACKER.indexActorCollectionFiltered = dhf.util.sortArray(MOVIETRACKER.indexActorCollectionFiltered, MOVIETRACKER.lastSortValue, MOVIETRACKER.reverseSort);
		}
		MOVIETRACKER.makeGridTableActorIndex();
	},

	checkReverseSort: function (sortField) {
		'use strict';
		if (sortField === MOVIETRACKER.lastSortValue) { MOVIETRACKER.reverseSort = (MOVIETRACKER.reverseSort === 'N') ? 'Y' : 'N'; }
		else {
			MOVIETRACKER.reverseSort = 'N';
			MOVIETRACKER.lastSortValue = sortField;
		}
	},

	sortByActorIndex: function () {
		'use strict';
		var sortField = jQuery(this).attr("data-sortby");
		dhf.util.displayWaitDiv('Sorting.....', 'dhf-waitDivHolder');
		MOVIETRACKER.checkReverseSort(sortField);
		MOVIETRACKER.filterBySearchActorIndex();
	},

	makeGridTableActorIndex: function () {
		'use strict';
		var i = 0,
			x = 0,
			tmpRowId,
			rowContent,
			hasPictureCell,
			rowCellCollection = [],
			displayRowCount = MOVIETRACKER.indexActorCollectionFiltered.length;

		if ($D('resultsBody')) { jQuery("#resultsBody").remove(); }

		if (displayRowCount < 1) {
			MOVIETRACKER.makeGridTableNoResults();
			return;
		}

		dhf.makeTable({ "oid": "resultsBody", "ocn": "dhf-grid-results", "parentId": "dhf-resultsTableContainer", "tBodyId": "resultsTbody" });
		MOVIETRACKER.makeGridTableHeaderRowActorIndex();
		dhf.setDefaults('defaultTableCell', { "nWrap": "nowrap", "ocn": "gridDataCell" });
		dhf.rowColor.increment = 1;

		for (i = 0; i < displayRowCount; i += 1) {
			rowCellCollection = [];
			rowContent = MOVIETRACKER.indexActorCollectionFiltered[i];
			tmpRowId = 'dataRow'+rowContent.actor_id;

			if (rowContent.has_picture === 'N') { hasPictureCell = { "iFunctName": "makeTableCell", "iParams": { "iHTML": '&nbsp;' } }; }
			else {
				hasPictureCell = { "iFunctName": "makeTableCell", "iParams": {
					"oAlign": 'center',
					"vAlign": 'middle',
					"iObjs": [
						{ "iFunctName": 'makeImage', "iParams": { "oSrc": '/images/previewImg.png', "oWidth": '16', "oHeight": '16', "ocn": 'cursor-pointer', "oAttr": { "oName": "data-actorimageid", "oValue": rowContent.actor_id } } }
					]
				} };
			}

			rowCellCollection.push(dhf.makeIobjsCell(i+1));
			rowCellCollection.push(dhf.makeIobjsCell(dhf.util.getActorHtml(rowContent.actor_id, rowContent.actor_name)));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.number_of_movies));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.actor_score));
			rowCellCollection.push(hasPictureCell);
			dhf.makeTableRow({
				"oid": tmpRowId,
				"parentId": "resultsTbody",
				"altColor": "Y",
				"iObjs": rowCellCollection
			});

		}
		dhf.setDefaults('defaultTableCell', { "ocn": "" });
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 300);
	},

	makeGridTableHeaderRowActorIndex: function () {
		'use strict';
		var sortClass = (MOVIETRACKER.reverseSort === 'Y') ? 'reverseSorted' : 'sorted',
			headerCellCollection = [];

		dhf.setDefaults('defaultTableCell', { "nWrap": 'nowrap', "ocn": 'jsLink' });

		headerCellCollection.push({ "iFunctName": "makeTableCell", "iParams": { "iHTML": '&nbsp;', "ocn": "" } });
		headerCellCollection.push(dhf.makeSortHeaderCell("Actor Name", "actor_name"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Movies", "number_of_movies"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Score", "actor_score"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Has Picture", "has_picture"));

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
		jQuery("#"+MOVIETRACKER.lastSortValue+"Header").addClass(sortClass);
	},

	makeGridTableNoResults: function () {
		'use strict';
		dhf.makeTable({ "oid": "resultsBody", "ocn": "dhf-grid-results", "parentId": "dhf-resultsTableContainer", "tBodyId": "resultsTbody" });
		dhf.makeTableRow({
			"parentId": "resultsTbody",
			"ocn": "dhf-noResults",
			"iObjs": [{ "iFunctName": "makeTableCell", "iParams": { "oAlign": "center", "ocn": "dhf-noResults", "iHTML": "No Results Found" } }]
		});
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 10);
	},

	colorRow: function () { jQuery(this).toggleClass('selectedRow'); },

	clearMovieFilterActorIndex: function () {
		'use strict';
		$D('searchActor').value = '';
		$D('searchActor').focus();
		MOVIETRACKER.filterBySearchActorIndex();
	},

	got_addNewActorPage: function () {
		'use strict';
		var day = new Date(),
			id = day.getTime(),
			newPageName = 'page'+id,
			newWindow = window.open('/editActor.php?actorId=0', newPageName);

		newWindow.focus();
	},

	showActorPicture: function () {
		'use strict';
		var currObj = jQuery(this),
			currObjPosition = currObj.position(),
			actorId = currObj.attr("data-actorimageid"),
			requestParams = { "className": "actorPictureClass", "methodCall": "fetchPictureObj", "actorId": actorId };


		if ($D('actorPicturePreviewDiv')) { document.body.removeChild($D("actorPicturePreviewDiv")); }
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(pictureData) {
			var divObj,
				imgObj;

			imgObj = document.createElement("img");
			imgObj.setAttribute('id', 'actorPicturePreview');
			imgObj.setAttribute('src', '/actor_pictures/'+pictureData.pictureName);
			imgObj.setAttribute('width', pictureData.pictureWidth);
			imgObj.setAttribute('height', pictureData.pictureHeight);

			divObj = document.createElement("div");
			divObj.setAttribute('id', 'actorPicturePreviewDiv');
			divObj.style.width = (pictureData.pictureWidth+20)+'px';
			divObj.style.height = (pictureData.pictureHeight+20)+'px';
			divObj.style.top = (currObjPosition.top-220)+'px';
			divObj.style.left = (currObjPosition.left-300)+'px';
			divObj.appendChild(imgObj);

			document.body.appendChild(divObj);
		});
	},

	removeActorPicture: function () {
		if ($D('actorPicturePreviewDiv')) { document.body.removeChild($D("actorPicturePreviewDiv")); }
	}
};

jQuery(document).ready(function() {
	MOVIETRACKER.actorIndexinit();
	jQuery("#searchActor").keyup(MOVIETRACKER.searchFilterDelayActorIndex);
	jQuery("#doSearch").button().click(MOVIETRACKER.filterBySearchActorIndex);
	jQuery("#clearSearch").button().click(MOVIETRACKER.clearMovieFilterActorIndex);
	jQuery("#addNewActor").button().click(MOVIETRACKER.got_addNewActorPage);
	jQuery(document).on("click", "td[data-sortby]", MOVIETRACKER.sortByActorIndex);
	jQuery(document).on("click", "#resultsTbody tr", MOVIETRACKER.colorRow);
	jQuery(document).on("mouseover", "img[data-actorimageid]", MOVIETRACKER.showActorPicture);
	jQuery(document).on("mouseout", "img[data-actorimageid]", MOVIETRACKER.removeActorPicture);
	jQuery("#searchActor").click(function() { this.select(); });
});

</script>
</head>
<body>
<div id="container">
	<div id="wrap">
		<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
		<div id="dhf-waitDivHolder"></div>

		<p id="filterBlock">
		<input type="text" id="searchActor" placeholder="Search Actor Names" class="plex-corner-all" />
		<button type="button" id="doSearch" value="Filter Actors">Filter Actors</button>
		<button type="button" id="clearSearch" value="Clear Filter">Clear Filter</button>
		<button type="button" id="addNewActor" value="Add New Actor">Add New Actor</button>
		</p>

		<div id="dhf-resultsTableContainer"></div>

	</div>
</div>

</body>
</html>