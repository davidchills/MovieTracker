<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['movieId']) ) {
	$movieObj = new MOVIE($_GET['movieId']);
	$chapterObj = new MOVIE_CHAPTERS();
	$chapterObj->set_movieId($_GET['movieId']);
	$chaptersCollection = $chapterObj->fetch_chapterDataForMovie($_GET['movieId']);
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['chapterPostAction'] == 'Update This Chapter' && isset($_POST['chapterId']) && $_POST['chapterId'] > 0) {
		$chapterObj = new MOVIE_CHAPTERS($_POST['chapterId']);
		$chapterObj->set_movieId($_POST['movieId']);
		$chapterObj->set_sortTitle($_POST['startHour']);
		$chapterObj->set_studio($_POST['startMinute']);
		$chapterObj->set_collection($_POST['startSecond']);
		$chapterObj->set_rating($_POST['startMicro']);
		$chapterObj->set_format($_POST['description']);
		$chapterObj->update_chapter();
		$movieId = $chapterObj->get_movieId();
		header("Location: /editChapters.php?movieId=".$movieId);
	}
	elseif ($_POST['chapterPostAction'] == 'Add New Chapter') {
		$chapterObj = new MOVIE_CHAPTERS();
		$chapterObj->set_movieId($_POST['movieId']);
		$chapterObj->set_sortTitle($_POST['startHour']);
		$chapterObj->set_studio($_POST['startMinute']);
		$chapterObj->set_collection($_POST['startSecond']);
		$chapterObj->set_rating($_POST['startMicro']);
		$chapterObj->set_format($_POST['description']);
		$chapterObj->create_chapter();
		$movieId = $chapterObj->get_movieId();
		header("Location: /editMovie.php?movieId=".$movieId);
	}
	elseif ($_POST['chapterPostAction'] == 'Delete Chapter' && isset($_POST['chapterId']) && $_POST['chapterId'] > 0) {
		$chapterObj = new MOVIE_CHAPTERS($_POST['chapterId']);
		$chapterObj->delete_chapter();
		header("Location: /closeWindow.php");
	}
}
else {
	$movieObj = new MOVIE();
	$chapterObj = new MOVIE_CHAPTERS();
	$chaptersCollection = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo ($movieObj->title != '') ? $movieObj->title : ''; ?>: Edit Chapters</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base','chosen')); ?>
<style type="text/css">
#editChapterFormTable {
	border: solid black 1px;
	width: 855px;
}
#editChapterFormTable > tbody > tr > td, #editChapterFormTable > tfoot > tr > td {
	border: solid black 1px;
	padding: 3px 3px;
}
input[type="text"], input[type="search"], input[type="url"], input[type="number"] {
	height: 20px;
	font-size: smaller;
}
input[type="text"], input[type="search"], input[type="url"], input[type="number"], textarea {
	background-color: rgba(177, 217, 249, 0.25);
	border: 1px inset #AAAAAA;
	cursor: text;
	padding: 0 5px;
	position: relative;
}
input[type="text"]:focus, input[type="search"]:focus, input[type="url"]:focus, input[type="number"]:focus, textarea:focus {
	background-color: rgba(243, 244, 178, 0.25);
}
input[type="number"] {
	width: 50px;
}
input[type="text"].startHour {
	width: 50px;
}
input[type="text"].startMinute {
	width: 50px;
}
input[type="text"].startSecond {
	width: 50px;
}
input[type="text"].startMicro {
	width: 70px;
}
input[type="text"].chapDesc {
	width: 230px;
}
button {
	width: 120px;
	height: 20px;
	text-align: center;
	vertical-align: middle;
	cursor: pointer;
	border-radius: 6px;
	border: 1px outset #AAAAAA;
	margin: 5px 8px;
}
button:hover { border: 1px #B4B4B4 inset; }
button.deleteButton { background-color: rgba(255, 0, 48, 0.25); }
button.updateButton { background-color: rgba(191, 255, 190, 0.25); }
#addNewChapter:disabled, button.deleteButton:disabled, button.updateButton:disabled {
	background-color: rgba(175, 175, 175, 0.5);
	color: silver;
}
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined','chosen')); ?>
<script type="text/javascript">
var editChapter = {

	chapterCollection: [],

	init: function () {
		'use strict';
		var requestParams = { "className": "movieChapterClass", "methodCall": "fetchChapterDataForMovie", "movieId": $D('movieId').value };

		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			editChapter.chapterCollection = returnData;
			editChapter.drawChapters();
		});
	},

	updateChapter: function () {
		'use strict';
		var currObj = jQuery(this),
			chapterId = currObj.attr("data-updatechapterid"),
			startHour = jQuery("input[data-starthourid"+chapterId+"]").val(),
			startminute = jQuery("input[data-startminuteid"+chapterId+"]").val(),
			startSecond = jQuery("input[data-startsecondid"+chapterId+"]").val(),
			startMicro = jQuery("input[data-startmicroid"+chapterId+"]").val(),
			description = jQuery("input[data-descriptionid"+chapterId+"]").val(),
			movieId = $D('movieId').value,
			requestParams = {
				"className": "movieChapterClass",
				"methodCall": "updateChapter",
				"chapterId": chapterId,
				"movieId": movieId,
				"startHour": startHour,
				"startminute": startminute,
				"startSecond": startSecond,
				"startMicro": startMicro,
				"description": description
			};

		jQuery("button[data-updatechapterid], button[data-deletechapterid], #addNewChapter").prop("disabled", true);
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			editChapter.chapterCollection = returnData;
			editChapter.drawChapters();
		});
	},

	addNewChapter: function () {
		'use strict';
		var currObj = jQuery(this),
			requestParams = {
				"className": "movieChapterClass",
				"methodCall": "createChapter",
				"movieId": $D('movieId').value,
				"startHour": $D('newStartHour').value,
				"startminute": $D('newStartMinute').value,
				"startSecond": $D('newStartSecond').value,
				"startMicro": $D('newStartMicro').value,
				"description": $D('newDescription').value
			};

		if (requestParams.description !== '') {
			jQuery("button[data-updatechapterid], button[data-deletechapterid], #addNewChapter").prop("disabled", true);
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
				$D('newStartHour').value = 0;
				$D('newStartMinute').value = 0;
				$D('newStartSecond').value = 0;
				$D('newStartMicro').value = '0000000';
				$D('newDescription').value = '';
				editChapter.chapterCollection = returnData;
				editChapter.drawChapters();
			});
		}
	},

	deleteChapter: function () {
		'use strict';
		var currObj = jQuery(this),
			chapterId = currObj.attr("data-deletechapterid"),
			requestParams = {
				"className": "movieChapterClass",
				"methodCall": "deleteChapter",
				"chapterId": chapterId,
				"movieId": $D('movieId').value
			};

		jQuery("button[data-updatechapterid], button[data-deletechapterid], #addNewChapter").prop("disabled", true);
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			editChapter.chapterCollection = returnData;
			editChapter.drawChapters();
		});
	},

	drawChapters: function () {
		'use strict';
		var i = 0,
			chapterData,
			hourCell,
			minuteCell,
			secondCell,
			microCell,
			descCell,
			updateCell,
			deleteCell,
			collectionLength = editChapter.chapterCollection.length;

		if ($D('chapterDataBody')) { $D("editChapterFormTable").removeChild($D("chapterDataBody")); }

		dhf.make_tBody({
			"oid": 'chapterDataBody',
			"parentId": 'editChapterFormTable'
		});

		dhf.setDefaults('defaultTableCell', { "nWrap": "nowrap", "ocn": "gridDataCell" });

		for (i = 0; i < collectionLength; i += 1) {
			chapterData = editChapter.chapterCollection[i];

			hourCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeInputNumber', "iParams": {
						"oName": 'startHour',
						"ocn": 'startHour',
						"oPattern": '[0-9]{1,2}',
						"oMin": '0',
						"oMax": '20',
						"oStep": '1',
						"oRequired": 'required',
						"oValue": chapterData.start_hour,
						"oAttr": [{ "oName": 'data-starthourid'+chapterData.chapter_id, "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			minuteCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeInputNumber', "iParams": {
						"oName": 'startMinute',
						"ocn": 'startMinute',
						"oPattern": '[0-9]{1,2}',
						"oMin": '0',
						"oMax": '59',
						"oStep": '1',
						"oRequired": 'required',
						"oValue": chapterData.start_minute,
						"oAttr": [{ "oName": 'data-startminuteid'+chapterData.chapter_id, "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			secondCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeInputNumber', "iParams": {
						"oName": 'startSecond',
						"ocn": 'startSecond',
						"oPattern": '[0-9]{1,2}',
						"oMin": '0',
						"oMax": '59',
						"oStep": '1',
						"oRequired": 'required',
						"oValue": chapterData.start_second,
						"oAttr": [{ "oName": 'data-startsecondid'+chapterData.chapter_id, "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			microCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeInputText', "iParams": {
						"oName": 'startMicro',
						"ocn": 'startMicro',
						"oPattern": '[0-9]{1,7}',
						"oRequired": 'required',
						"oValue": chapterData.start_micro.toString(),
						"oAttr": [{ "oName": 'data-startmicroid'+chapterData.chapter_id, "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			descCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeInputText', "iParams": {
						"oName": 'description',
						"oRequired": 'required',
						"ocn": 'chapDesc',
						"oValue": chapterData.description,
						"oAttr": [{ "oName": 'data-descriptionid'+chapterData.chapter_id, "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			updateCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeButtonButton', "iParams": {
						"iHTML": 'Update Chapter',
						"ocn": "updateButton",
						"oAttr": [{ "oName": 'data-updatechapterid', "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			deleteCell = { "iFunctName": "makeTableCell", "iParams": {
				"iObjs": [
					{ "iFunctName": 'makeButtonButton', "iParams": {
						"iHTML": 'Delete Chapter',
						"ocn": "deleteButton",
						"oAttr": [{ "oName": 'data-deletechapterid', "oValue": chapterData.chapter_id }]
					} }
				]
			} };

			dhf.makeTableRow({
				"parentId": 'chapterDataBody',
				"iObjs": [ hourCell, minuteCell, secondCell, microCell, descCell, updateCell, deleteCell ]
			});
		}
		dhf.setDefaults('defaultTableCell', { "ocn": "" });
		$D('newDescription').focus();
		jQuery("button[data-updatechapterid], button[data-deletechapterid], #addNewChapter").prop("disabled", false);
	}
};

jQuery(document).ready(function() {
	if ($D('movieId').value > 0) {
		editChapter.init();
		jQuery(document).on("click", "button[data-updatechapterid]", editChapter.updateChapter);
		jQuery(document).on("click", "button[data-deletechapterid]", editChapter.deleteChapter);
		jQuery(document).on("click", "#addNewChapter", editChapter.addNewChapter);
		jQuery(document).on("focus, click", "input", function() { this.select(); });
		$D('newDescription').focus();
	}
});
</script>
</head>
<body>
<div id="container">
	<div id="wrap">
		<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
		<div id="dhf-waitDivHolder"></div>
		<fieldset class="adminEditBlock">
		<legend>Edit/Add Chapters</legend>
		<h2><?= $movieObj->title; ?>
		<form id="editChapterForm" name="editChapterForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" accept-charset="utf-8" method="post" autocomplete="off">
		<input type="hidden" id="chapterPostAction" name="chapterPostAction" value="" />
		<input type="hidden" id="movieId" name="movieId" value="<?php print $_GET['movieId']; ?>" />
		<table id="editChapterFormTable" class="dhf-grid-results">
			<thead>
				<tr class="dhf-grid-header-row">
					<td>Hour</td>
					<td>Minute</td>
					<td>Second</td>
					<td>Micro</td>
					<td colspan="3">Description</td>
				</tr>
			</thead>
			<tbody id="chapterDataBody">
			</tbody>
			<tfoot>
				<tr>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left"><input type="number" class="startHour" name="newStartHour" id="newStartHour" value="0" min="0" max="3" step="1" required="required" pattern="[0-9]{1,2}" autocomplete="off"></td>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left"><input type="number" class="startMinute" name="newStartMinute" id="newStartMinute" value="0" min="0" max="59" step="1" required="required" pattern="[0-9]{1,2}" autocomplete="off"></td>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left"><input type="number" class="startSecond" name="newStartSecond" id="newStartSecond" value="0" min="0" max="59" step="1" required="required" pattern="[0-9]{1,2}" autocomplete="off"></td>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left"><input type="text" class="startMicro" name="newStartMicro" id="newStartMicro" value="0000000" required="required" pattern="[0-9]{1,7}" autocomplete="off"></td>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left"><input type="text" class="chapDesc" name="newDescription" id="newDescription" value="" required="required" autocomplete="off"></td>
					<td class="gridDataCell" valign="middle" nowrap="nowrap" align="left" colspan="3"><button class="updateButton" type="button" id="addNewChapter" value="Add New Chapter">Add New Chapter</button></td>
				</tr>
			</tfoot>
		</table>
		</form>
		</fieldset>

		<br /><br /><br />
	</div>
</div>
</body>
</html>
