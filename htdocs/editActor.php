<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['actorId']) ) {
	$actorObj = new ACTOR($_GET['actorId']);
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$actorId = 0;
	$pictureSize = '';
	$pictureWidth = 0;
	$pictureHeight = 0;
	$uniqueFileName = '';
	$hasUpload = false;
	if (is_uploaded_file($_FILES['actorPictureUpload']['tmp_name'])) {
		$fileName = $_FILES['actorPictureUpload']['name'];
		preg_match("/.*(\.\w+)$/", $_FILES['actorPictureUpload']['name'], $matches);
		$uniqueFileName = md5(time().$fileName).$matches[1];
		move_uploaded_file($_FILES['actorPictureUpload']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/actor_pictures/".$uniqueFileName);
		chmod($_SERVER["DOCUMENT_ROOT"]."/actor_pictures/".$uniqueFileName, 0777);
		$pictureSize = getimagesize($_SERVER["DOCUMENT_ROOT"]."/actor_pictures/".$uniqueFileName);
		$pictureWidth = $pictureSize[0];
		$picturerHeight = $pictureSize[1];

		if ($pictureWidth > 230) {
			$pictureScale = 230/$pictureWidth;
			$picturerHeight *= $pictureScale;
			$pictureWidth = 230;
		}
		if ($picturerHeight > 320) {
			$pictureScale *= 320/$picturerHeight;
			$pictureWidth *= 320/$picturerHeight;
			$picturerHeight = 320;
		}
		$hasUpload = true;
	}

	if ($_POST['actorPostAction'] == 'Update This Actor' && isset($_POST['actorId']) && $_POST['actorId'] > 0) {
		$actorObj = new ACTOR($_POST['actorId']);
		$actorObj->set_actorName($_POST['actorName']);
		$actorObj->set_actorScore($_POST['actorScore']);
		$actorObj->set_isActive($_POST['isActive']);
		$actorObj->set_actorUrl($_POST['actorUrl']);
		$actorObj->set_actorComments($_POST['actorComments']);
		$actorObj->set_privateNotes($_POST['privateNotes']);
		$actorObj->save_actor();
		$actorId = $actorObj->get_actorId();

		if ($hasUpload == true) {
			$pictureObj = new ACTOR_PICTURE($actorId);
			$pictureObj->delete_picture();
			$pictureObj->set_pictureName($uniqueFileName);
			$pictureObj->set_pictureWidth($pictureWidth);
			$pictureObj->set_pictureHeight($picturerHeight);
			$pictureObj->create_picture();
		}
		header("Location: /editActor.php?action=update&actorId=".$actorId);
	}

	elseif ($_POST['actorPostAction'] == 'Add New Actor') {
		$actorObj = new ACTOR();
		$actorObj->set_actorName($_POST['actorName']);
		$actorObj->set_actorScore($_POST['actorScore']);
		$actorObj->set_isActive($_POST['isActive']);
		$actorObj->set_actorUrl($_POST['actorUrl']);
		$actorObj->set_actorComments($_POST['actorComments']);
		$actorObj->set_privateNotes($_POST['privateNotes']);
		$actorObj->create_actor();
		$actorId = $actorObj->get_actorId();

		if ($hasUpload == true) {
			$pictureObj = new ACTOR_PICTURE($actorId);
			$pictureObj->delete_picture();
			$pictureObj->set_pictureName($uniqueFileName);
			$pictureObj->set_pictureWidth($pictureWidth);
			$pictureObj->set_pictureHeight($picturerHeight);
			$pictureObj->create_picture();
		}
		header("Location: /editActor.php?action=new&actorId=".$actorId);
	}

	elseif ($_POST['actorPostAction'] == 'Delete This Actor') {
		$actorObj = new ACTOR($_POST['actorId']);
		$actorObj->delete_actor();
		header("Location: /closeWindow.php");
	}
}
else { $actorObj = new ACTOR(); }

if ($actorObj->actorId > 0) {
	$pictureObj = new ACTOR_PICTURE($actorObj->actorId);
	if (!empty($pictureObj->pictureName) && file_exists($_SERVER["DOCUMENT_ROOT"]."/actor_pictures/".rawurlencode($pictureObj->pictureName))) {
		$picturePath = '/actor_pictures/'.rawurlencode($pictureObj->pictureName);
		$pictureWidth = $pictureObj->pictureWidth;
		$pictureHeight = $pictureObj->pictureHeight;
	}
	else {
		$picturePath = '';
		$pictureWidth = 0;
		$pictureHeight = 0;
	}
}
else {
	$picturePath = '';
	$pictureWidth = 0;
	$pictureHeight = 0;
}
$movieObj = new MOVIE();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Edit Actor</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>

<style type="text/css">
#editActorFormTable {
	border: solid black 1px;
	width: 730px;
	height: 357px;
	table-layout: fixed;
}
td { border: solid black 1px; }
td.colWidth1 { width: 500px; }
#movieChoicesTableBodyId > tr > td {
	text-align: center;
	vertical-align: top;
}
#actorIdCell { height: 25px; }
#actorId {
	width: 35px;
	background-color: #BDBDBD;
	background-image: linear-gradient(#eeeeee 0%, #BDBDBD 100%);
}
#actorPictureCell {
	width: 230px;
	height:320px;
	text-align: center;
	vertical-align: middle;
}
#actorProfilePicture { margin: 0px; }
#actorNameCell {
	height: 25px;
	text-align: right;
	vertical-align: middle;
	padding-right: 5px;
}
#actorName { width: 370px; }
#actorUrlCell {
	height: 25px;
	text-align: right;
	vertical-align: middle;
	padding-right: 5px;
}
#actorUrl { width: 370px; }
#actorScoreCell {
	height: 25px;
	text-align: left;
	vertical-align: middle;
}
#actorScore {
	width: 75px;
	margin-right: 40px;
}
#actorPictureUploadCell {
	height: 25px;
	text-align: left;
	vertical-align: middle;
}
#actorPictureUpload { background-color: #EAF4FD; }
#actorCommentsCell {
	height: 190px;
	text-align: left;
	vertical-align: middle;
	padding:6px;
}
#actorComments {
	width: 460px;
	height: 160px;
	margin: auto;
}
#privateNotes {
	width: 700px;
	height: 55px;
}
img {
	margin-left: auto;
	border: 0;
}
label {
	display: inline;
	line-height: 15px;
	font-weight: normal;
}
button.width170 {
	min-width: 155px;
	cursor: pointer;
	margin-left: 3px;
	margin-right: 3px;
}
.red { color: red; }
#clearActorId {
	text-align: left;
	vertical-align: middle;
	margin-left: 9px;
	margin-right: 5px;
	cursor: pointer;
}
#actionButtonRow {
	text-align: center;
	vertical-align: middle;
	padding: 5px;
}
input[type="text"], input[type="search"], input[type="url"] { height: 20px; }
fieldset.adminEditBlock {
	margin-top: 20px;
	margin-bottom: 20px;
}
</style>

<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>

<script type="text/javascript">
var MOVIETRACKER = {

	selectedMovies: [],

	editActorInit: function () {
		'use strict';
		var getVars = dhf.util.urlGetVars();
		if (getVars.action === 'new' || getVars.action === 'update') {
			MOVIETRACKER.refreshParentActorIndex();
		}
	},

	refreshParentActorIndex: function () {
		'use strict';
		var getVars = dhf.util.urlGetVars();
		if (window.opener !== null && window.opener.document.title == 'Movie Tracking: Actor List') {
			window.opener.MOVIETRACKER.refreshCollectionActorIndex();
		}
	},

	fetchSelectedMoviesEditActor: function () {
		'use strict';
		var movieListLegendObj = jQuery("#movieListLegend"),
			actorIdObj = jQuery("#actorId"),
			actorNameObj = jQuery("#actorName"),
			actorId = actorIdObj.val(),
			requestParams = { "className": "actorClass", "methodCall": "fetchMoviesWithActor", "actorId": actorId };

		if (actorId > 0) {
			movieListLegendObj.html(actorNameObj.val()+' is in these movies');
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(movieData) {
				MOVIETRACKER.selectedMovies = movieData;
				MOVIETRACKER.buildMovieChoiceTable();
			});
		}
		else {
			MOVIETRACKER.selectedMovies = [];
			MOVIETRACKER.buildMovieChoiceTable();
		}
	},

	buildMovieChoiceTable: function () {
		'use strict';
		var i = 0,
			rowCounter = 0,
			columnCounter = 0,
			rowIncrement = 0,
			columnIncrement = 0,
			currentRowId = 'actorRow'+rowIncrement,
			currentColumnId = currentRowId+'Column'+columnIncrement,
			movieData = null,
			movieIsSelected = 'N',
			labelClass= '',
			pictureScale = 0,
			posterWidth = 0,
			posterHeight = 0,
			collectionLength = MOVIETRACKER.selectedMovies.length;

		if ($D('movieChoicesTable')) { $D("movieListBlock").removeChild($D("movieChoicesTable")); }
		if (!$D('movieChoicesTableBodyId')) {
			dhf.makeTable({
				"oid": 'movieChoicesTable',
				"tBodyId": 'movieChoicesTableBodyId',
				"oAlign": 'left',
				"parentId": 'movieListBlock'
			});
		}

		dhf.makeTableRow({ "oid": currentRowId, "ocn": 'noColor', "parentId": 'movieChoicesTableBodyId' });
		for (i = 0; i < collectionLength; i += 1) {
			movieData = MOVIETRACKER.selectedMovies[i];

			posterWidth = parseFloat(movieData.poster_width);
			posterHeight = parseFloat(movieData.poster_height);

			if (posterWidth > 200) {
				pictureScale = 200/posterWidth;
				posterHeight *= pictureScale;
				posterWidth = 200;
			}
			if (posterHeight > 280) {
				pictureScale *= 280/posterHeight;
				posterWidth *= 280/posterHeight;
				posterHeight = 280;
			}

			dhf.makeTableCell({
				"parentId": currentRowId,
				"oAlign": 'center',
				"vAlign": 'top',
				"iObjs": [
					{ "iFunctName": 'makeImage', "iParams": {
						"oid": 'movieChoice'+currentColumnId,
						"oSrc": '/posters/'+movieData.poster_name,
						"oWidth": posterWidth,
						"oHeight": posterHeight,
						"oTitle": "Click to play movie in a new window",
						"ocn": "moviePoster cursor-pointer",
						"oAttr": [{ "oName": "data-playmovieid", "oValue": movieData.movie_id },{ "oName": "data-moviename", "oValue": movieData.title }]
					} },
					{ "iFunctName": 'makeParagraph', "iParams": { "iHTML": MOVIETRACKER.getMovieHtml(movieData.movie_id, movieData.title), "oTitle": "Click to edit movie in a new window" } }
				]
			});

			if (columnIncrement == 3) {
				rowIncrement += 1;
				columnIncrement = 0;
				currentRowId = 'actorRow'+rowIncrement;
				currentColumnId = currentRowId+'Column'+columnIncrement;
				dhf.makeTableRow({ "oid": currentRowId, "ocn": 'noColor', "parentId": 'movieChoicesTableBodyId' });
			}
			else {
				columnIncrement += 1;
				currentColumnId = currentRowId+'Column'+columnIncrement;
			}
		}
	},

    getMovieHtml: function (movieId, longTitle) {
        'use strict';
        var movieTitle = '',
        	shortMovieTitle = '',
        	actorName = '',
        	nameParts = [];

        nameParts = longTitle.split(" - ");
        movieTitle = nameParts[0];
        actorName = nameParts[1];

        shortMovieTitle = (movieTitle.length > 30) ? movieTitle.substring(0, 27)+'...' : movieTitle;
        if (actorName) { shortMovieTitle += '<br>'+actorName; }
        return '<a href="/editMovie.php?movieId='+movieId+'" title="'+longTitle+'" target="_blank">'+shortMovieTitle+'</a>';
    },

	clearForm: function () {
		'use strict';
		$D('actorId').value = '';
		$D('actorName').value = '';
		$D('actorUrl').value = '';
		$D('actorScore').value = '0';
		$D('actorPictureUpload').value = '';
		$D('actorProfilePicture').src = '';
		$D('privateNotes').value = '';
		MOVIETRACKER.selectedMovies = [];
		MOVIETRACKER.buildMovieChoiceTable();
		jQuery("#buttonAddUpdateActor").text("Add Actor").button();
		$D('actorName').focus();
	},

	clearActorId: function () {
		'use strict';
		$D('actorId').value = '';
		$D('actorName').value = '';
		$D('actorUrl').value = '';
		$D('actorScore').value = '0';
		$D('actorProfilePicture').src = '';
		$D('privateNotes').value = '';
		MOVIETRACKER.selectedMovies = [];
		MOVIETRACKER.buildMovieChoiceTable();
		jQuery("#buttonAddUpdateActor").text("Add Actor").button();
		$D('actorName').focus();
	},

	setPageTitleEditActor: function () {
		if ($D('actorName').value !== '') { document.title = $D('actorName').value; }
	},

	areYouSureEditActor: function () {
		'use strict';
		var actorId = $D('actorId').value,
			buttonData = [
				{ "text": "Don't Delete", "click": function() { jQuery(this).dialog("close"); } },
				{ "text": "Yes Delete", "click": function() { jQuery(this).dialog("close"); MOVIETRACKER.deleteActor() } }
			];

		if (actorId > 0) {
			dhf.popup.make({ "modal": true, "title": 'Confirm Delete Actor', "buttons": buttonData });
			dhf.popup.html('<p class="warningMessage">Are you sure you want to delete this actor?<br />This cannot be undone!</p>');
			dhf.popup.open();
		}
	},

	deleteActor: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "deleteActor", "actorId": $D('actorId').value };
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			if (window.opener !== null) { window.opener.MOVIETRACKER.refreshCollectionActorIndex(); }
			window.close();
		});
	},

	validateActor: function () {
		'use strict';
		var actorIdObj = jQuery("#actorId"),
			actorNameObj = jQuery("#actorName"),
			actorUrlObj = jQuery("#actorUrl"),
			actorScoreObj = jQuery("#actorScore"),
			actorPictureUploadObj = jQuery("#actorPictureUpload");

		if (actorNameObj.val() == '') {
			actorNameObj.addClass("invalid");
			jQuery("label[for=actorName]").addClass('error');
			dhf.errorHandler.add("No Name Entered");
		}
		else {
			actorNameObj.removeClass("invalid");
			jQuery("label[for=actorName]").removeClass('error');
		}

		if (actorUrlObj.val() == '') {
			actorUrlObj.addClass("invalid");
			jQuery("label[for=actorUrl]").addClass('error');
			dhf.errorHandler.add("No URL Entered");
		}
		else {
			actorUrlObj.removeClass("invalid");
			jQuery("label[for=actorUrl]").removeClass('error');
		}

		if (actorIdObj.val() == '' && actorPictureUploadObj.val() == '') {
			actorPictureUploadObj.addClass("invalid");
			jQuery("label[for=actorPictureUpload]").addClass('error');
			dhf.errorHandler.add("No Picure Selected");
		}
		else {
			actorPictureUploadObj.removeClass("invalid");
			jQuery("label[for=actorPictureUpload]").removeClass('error');
		}

		if ($D("actorId").value > 0) {
			$D('actorPostAction').value = "Update This Actor";
		}
		else {
			$D('actorPostAction').value = "Add New Actor";
		}

		if (!dhf.errorHandler.display()) { $D('editActorForm').submit(); }
	},

	playSelectedMovie: function () {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.attr("data-playmovieid"),
			movieName = currObj.attr("data-moviename"),
			url = '/playMovie.php?movieId='+movieId,
			newWindow = '';

		newWindow = window.open(url, 'playMovie'+movieId);
		newWindow.focus();
	},

	updateActor: function () {
		'use strict';
		var actorId = $D("actorId").value,
			actorName = $D('actorName').value,
			actorUrl = $D('actorUrl').value,
			actorScore = $D('actorScore').value,
			isActive = $D('isActive').value,
			actorComments = $D('actorComments').value,
			privateNotes = $D('privateNotes').value,

			requestParams = {
				"className": "actorClass",
				"methodCall": "updateActor",
				"actorId": actorId,
				"actorName": actorName,
				"actorUrl": actorUrl,
				"actorScore": actorScore,
				"isActive": isActive,
				"actorComments": actorComments,
				"privateNotes": privateNotes
			};

		if (parseInt($D('actorId').value, 10) > 0) {

			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(responseData) {
				if (responseData.errorMsg == 'BAD') { console.log("Error Saving Data"); }
				else { MOVIETRACKER.refreshParentActorIndex(); }
			});
		}
	}

};

jQuery(document).ready(function() {
	if ($D("actorId").value > 0) {
		MOVIETRACKER.fetchSelectedMoviesEditActor();
		MOVIETRACKER.setPageTitleEditActor();
		jQuery("#actorName, #actorUrl, #actorScore, #isActive, #actorComments, #privateNotes").change(MOVIETRACKER.updateActor);
	}
	jQuery("#buttonClearForm").button().click(MOVIETRACKER.clearForm);
	jQuery("#buttonAddUpdateActor").button().click(MOVIETRACKER.validateActor);
	jQuery("#buttonDeleteActor").button().click(MOVIETRACKER.areYouSureEditActor);
	jQuery("#actorName").change(MOVIETRACKER.setPageTitleEditActor);
	jQuery("#clearActorId").click(MOVIETRACKER.clearActorId);
	jQuery(document).on("click", "img[data-playmovieid]", MOVIETRACKER.playSelectedMovie);
	jQuery(document).on("focus, click", "input, textarea", function() { this.select(); });
	MOVIETRACKER.editActorInit();
});

</script>
</head>
<body>
<div id="container">
	<div id="wrap">
		<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
		<div id="dhf-waitDivHolder"></div>
		<fieldset class="adminEditBlock">
		<legend>Edit/Add Actor</legend>
		<form id="editActorForm" name="editActorForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" accept-charset="utf-8" method="post" autocomplete="off">
		<input type="hidden" id="actorPostAction" name="actorPostAction" value="" />
		<table id="editActorFormTable">
			<tbody>
				<tr>
					<td id="actorIdCell" class="colWidth1">
					<img src="/images/X.png" width="20" height="20" title="Clear Actor Id" id="clearActorId" />
					<input name="actorId" id="actorId" type="text" title="Actor ID" value="<?php print ($actorObj->actorId > 0) ? $actorObj->actorId : ''; ?>" />
					</td>

					<td id="actorPictureCell" rowspan="5">
					<?php if ($picturePath != '') { ?>
					<img id="actorProfilePicture" src="<?php print $picturePath; ?>" width="<?php print $pictureWidth; ?>" height="<?php print $pictureHeight; ?>" />
					<?php } else { ?>&nbsp;<?php } ?>
					</td>
				</tr>

				<tr>
					<td id="actorNameCell" class="colWidth1">
					<label for="actorName">Actor Name</label>
					<input name="actorName" id="actorName" type="text" title="Actor Name" required="required" placeholder="Actor Name" value="<?php print $actorObj->actorName; ?>" />
					</td>
				</tr>

				<tr>
					<td id="actorUrlCell" class="colWidth1">
					<label for="actorUrl">Original URL:</label>
					<input name="actorUrl" id="actorUrl" type="url" title="URL for Actor" required="required" placeholder="URL to Actor" value="<?php print $actorObj->actorUrl; ?>" />
					</td>
				</tr>

				<tr>
					<td id="actorScoreCell" class="colWidth1">
					<label for="actorScore">Actor Rating</label>
					<select name="actorScore" id="actorScore" title="Actor Rating">
					<?php print $actorObj->make_actorScoreChoiceMenu(); ?>
					</select>

					<label for="isActive">Is Actor Active</label>
					<select name="isActive" id="isActive" title="Is Actor Active">
					<?php print $actorObj->build_select_menu(array('Y' => 'Actor is Active', 'N' => 'Actor is NOT Active'), $actorObj->isActive); ?>
					</select>
					</td>
				</tr>

				<tr>
					<td id="actorCommentsCell" class="colWidth1">
					<label for="actorComments">Comments</label>
					<textarea name="actorComments" id="actorComments" placeholder="Comments to come...."><?php print $actorObj->actorComments; ?></textarea>
					</td>
				</tr>

				<tr>
					<td id="actorPictureUploadCell" colspan="2">
					<label for="actorPictureUpload">Picture Upload: </label>
					<input type="file" id="actorPictureUpload" name="actorPictureUpload" />
					</td>
				</tr>

				<tr>
					<td id="actionButtonRow" colspan="2">
					<button type="button" id="buttonAddUpdateActor" value="Add/Update Actor" class="width170">Update Actor</button>
					<button type="button" id="buttonClearForm" value="Clear Form" class="width170">Clear Form</button>
					<button type="button" id="buttonDeleteActor" value="Delete This Actor" class="width170">Delete This Actor</button>
					</td>
				</tr>

				<?php if ($_SESSION[$_SERVER['VHOST']]['user_login']['userId'] == 1) { ?>
				<tr>
					<td colspan="4">
					<label for="privateNotes">Private Notes</label><br />
					<textarea name="privateNotes" id="privateNotes" title="Private Notes" placeholder="Private Notes"><?php print $actorObj->privateNotes; ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } else { ?>
		</tbody>
		</table>
		<input name="privateNotes" id="privateNotes" type="hidden" value="<?php print $actorObj->privateNotes; ?>" />
		<?php } ?>
		</form>
		</fieldset>


		<fieldset class="adminEditBlock" id="movieListBlock">
		<legend id="movieListLegend">Movie this actor is in</legend>
		</fieldset>

	</div>
</div>
</body>
</html>
