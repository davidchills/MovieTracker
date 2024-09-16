var MOVIETRACKER = {

	chapterCollection: [],

	selectedMovies: [],
	selectedActors: [],
	selectedActorNames: [],

	allActorChoices: [],
	filteredActors: [],

	delayTimeId: null,

	indexMovieCollection: [],
	indexMovieCollectionFiltered: [],

	indexActorCollection: [],
	indexActorCollectionFiltered: [],

	lastSortValue: 'sort_title',
	reverseSort: 'N',

	selectedRows: [],
	startLetters: [],

	movieId: 0,
	movieTitle: '',
	movieSortTitle: '',
	movieCleanTitle: '',
	studio: 1,
	studioString: '',
	movieRating: 6,
	movieRatingString: '',
	movieCollection: '',
	movieResolution: 4,
	movieResolutionString: 'HD 1080p',
	movieScore: 0,
	movieIsActive: 'Y',
	movieIsActiveString: 'Movie is Active',
	movieReleaseDate: '',
	movieShortDescription: '',
	movieLongDescription: '',
	movieGenres: [],
	movieGenreString: '',
	movieOriginalUrl: '',
	moviePrivateNotes: '',
	movieHasChapters: 'N',

	movieObj: null,
	startTime: 0,
	endTime: 0,
	playSpeed: 1,

	movieIndexInit: function () {
		'use strict';
		MOVIETRACKER.lastSortValue = 'sort_title';
		jQuery.when(MOVIETRACKER.fetch_movieCollection()).then(function () {
			MOVIETRACKER.draw_goToLetter();
			MOVIETRACKER.filterBySearchMovie();
		});
	},

	actorIndexInit: function () {
		'use strict';
		MOVIETRACKER.lastSortValue = 'actor_name';
		jQuery.when(MOVIETRACKER.fetch_actorCollection()).then(function () {
			MOVIETRACKER.filterBySearchActorIndex();
		});
	},

	movieInit: function () {
		'use strict';
		var getVars = dhf.util.urlGetVars();
		if (getVars.action === 'new' || getVars.action === 'update') { MOVIETRACKER.refreshParentMovie(); }
		MOVIETRACKER.refreshVarsMovie();
	},

	chapterInit: function () {
		'use strict';
		var requestParams = { "className": "movieChapterClass", "methodCall": "fetchChapterDataForMovie", "movieId": $D('movieId').value };
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			MOVIETRACKER.chapterCollection = returnData;
			MOVIETRACKER.drawChapters();
		});
	},

	actorInit: function () {
		'use strict';
		var getVars = dhf.util.urlGetVars();
		if (getVars.action === 'new' || getVars.action === 'update') {
			MOVIETRACKER.refreshParentActor();
		}
	},

	playerInit: function () {
		'use strict';
		MOVIETRACKER.movieObj = $D('movieObj');
		MOVIETRACKER.movieObj.preload = true;
		MOVIETRACKER.movieObj.pause();
		MOVIETRACKER.movieObj.volume = 0;
		MOVIETRACKER.movieObj.muted = true;
		MOVIETRACKER.startTime = 0;
		MOVIETRACKER.endTime = 0;
		return;
	},

	refreshParentMovie: function () {
		'use strict';
		if (window.opener !== null && window.opener.document.title == 'Movie Tracking: Movie List') {
			window.opener.MOVIETRACKER.refreshCollectionMovie();
		}
	},

	refreshParentActor: function () {
		'use strict';
		var getVars = dhf.util.urlGetVars();
		if (window.opener !== null && window.opener.document.title === 'Movie Tracking: Actor List') {
			window.opener.MOVIETRACKER.refreshCollectionActor();
		}
	},

	refreshVarsMovie: function () {
		'use strict';
		if ($D('movieId')) {
			MOVIETRACKER.movieId = $D('movieId').value;
			MOVIETRACKER.movieTitle = $D('title').value;
			MOVIETRACKER.movieSortTitle = $D('sortTitle').value;
			MOVIETRACKER.movieCleanTitle = $D('cleanTitle').value;
			MOVIETRACKER.studio = $D('studio').value;
			MOVIETRACKER.studioString = editMovie.fetch_selectMenuText('studio');
			MOVIETRACKER.movieRating = $D('rating').value;
			MOVIETRACKER.movieRatingString = editMovie.fetch_selectMenuText('rating');
			MOVIETRACKER.movieCollection = $D('collection').value;
			MOVIETRACKER.movieResolution = $D('format').value;
			MOVIETRACKER.movieResolutionString = editMovie.fetch_selectMenuText('format');
			MOVIETRACKER.movieScore = $D('movieScore').value;
			MOVIETRACKER.movieIsActive = $D('isActive').value;
			MOVIETRACKER.movieIsActiveString = editMovie.fetch_selectMenuText('isActive');
			MOVIETRACKER.movieReleaseDate = $D('releaseDate').value;
			MOVIETRACKER.movieShortDescription = $D('descShort').value;
			MOVIETRACKER.movieLongDescription = $D('descLong').value;
			MOVIETRACKER.movieOriginalUrl = $D('originalUrl').value;
			MOVIETRACKER.moviePrivateNotes = $D('privateNotes').value;
			MOVIETRACKER.movieHasChapters = $D('hasChapters').value;
		}
		else {
			MOVIETRACKER.movieId = '';
			MOVIETRACKER.movieTitle = '';
			MOVIETRACKER.movieSortTitle = '';
			MOVIETRACKER.movieCleanTitle = '';
			MOVIETRACKER.studio = 1;
			MOVIETRACKER.studioString = '';
			MOVIETRACKER.movieRating = 6;
			MOVIETRACKER.movieRatingString = '';
			MOVIETRACKER.movieCollection = '';
			MOVIETRACKER.movieResolution = 4;
			MOVIETRACKER.movieResolutionString = 'HD 1080p';
			MOVIETRACKER.movieScore = 0;
			MOVIETRACKER.movieIsActive = 'Y';
			MOVIETRACKER.movieIsActiveString = 'Movie is Active';
			MOVIETRACKER.movieReleaseDate = '';
			MOVIETRACKER.movieShortDescription = '';
			MOVIETRACKER.movieLongDescription = '';
			MOVIETRACKER.movieOriginalUrl = '';
			MOVIETRACKER.moviePrivateNotes = '';
			MOVIETRACKER.movieHasChapters = 'N';
		}
	},

	refreshCollectionMovie: function () {
		'use strict';
		MOVIETRACKER.selectedRows = [];
		jQuery(".selectedRow").each(function() { MOVIETRACKER.selectedRows.push(this.id); });
		jQuery.when(MOVIETRACKER.fetch_movieCollection()).then(function () {
			var i = 0,
				localRows = MOVIETRACKER.selectedRows,
				selectedRowsLength = localRows.length;

			if ((jQuery("td.red")).length == 1) { jQuery("td.red").trigger("click"); }
			else { MOVIETRACKER.filterBySearchMovie(); }
			for (i = 0; i < selectedRowsLength; i += 1) {
				jQuery("#"+localRows[i]).addClass('selectedRow');
			}
		});
	},

	refreshCollectionActor: function () {
		'use strict';
		MOVIETRACKER.selectedRows = [];
		jQuery(".selectedRow").each(function() { MOVIETRACKER.selectedRows.push(this.id); });

		jQuery.when(MOVIETRACKER.fetch_actorCollection()).then(function () {
			var i = 0,
				localRows = MOVIETRACKER.selectedRows,
				selectedRowsLength = localRows.length;

			if ((jQuery("td.red")).length == 1) { jQuery("td.red").trigger("click"); }
			else { MOVIETRACKER.filterBySearchActor(); }
			for (i = 0; i < selectedRowsLength; i += 1) {
				jQuery("#"+localRows[i]).addClass('selectedRow');
			}
		});
	},

	fetch_movieCollection: function () {
		'use strict';
		var requestParams = { "className": "movieClass", "methodCall": "fetchMovieCollection" },
			dfWait,
			movieData,
			firstLetter = '',
			collectionLength = 0,
			i = 0;

		dhf.util.displayWaitDiv('Getting Collection.....', 'dhf-waitDivHolder');

		MOVIETRACKER.indexMovieCollection = [];

		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(collectionData) {

			collectionLength = collectionData.length;
			for (i = 0; i < collectionLength; i += 1) {
				movieData = collectionData[i];
				firstLetter = movieData.sort_title.charAt(0).toUpperCase();
				if (jQuery.inArray(firstLetter, MOVIETRACKER.startLetters) === -1) {
					MOVIETRACKER.startLetters.push(firstLetter);
				}
				MOVIETRACKER.indexMovieCollection.push(movieData);
			}
		});
		return dfWait.promise();
	},

	fetch_actorCollection: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "fetchActorCollection" },
			dfWait;

		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(collectionData) {
			MOVIETRACKER.indexActorCollection = collectionData;
		});
		return dfWait.promise();
	},


	filterBySearchActor: function () {
		'use strict';
		var i = 0,
			actorData = null,
			collectionLength = MOVIETRACKER.allActorChoices.length,
			searchTextPattern = new RegExp($D('searchActor').value, "i");

		MOVIETRACKER.filteredActors = [];

		if ($D('searchActor').value == '') {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = MOVIETRACKER.allActorChoices[i];
				if (jQuery.inArray(actorData.actor_id, MOVIETRACKER.selectedActors) > -1) {
					MOVIETRACKER.filteredActors.push(actorData);
				}
			}
		}
		else {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = MOVIETRACKER.allActorChoices[i];
				if (actorData.actor_name.search(searchTextPattern) > -1) {
					MOVIETRACKER.filteredActors.push(actorData);
				}
				else if (jQuery.inArray(actorData.actor_id, MOVIETRACKER.selectedActors) > -1) {
					MOVIETRACKER.filteredActors.push(actorData);
				}
			}
		}
	},

	filterBySearchActorIndex: function () {
		'use strict';
		var i = 0,
			actorData = null,
			collectionLength = MOVIETRACKER.indexActorCollection.length,
			searchTextPattern = new RegExp($D('searchActors').value, "i");

		MOVIETRACKER.indexActorCollectionFiltered = [];

		if ($D('searchActors').value == '') { MOVIETRACKER.indexActorCollectionFiltered =  MOVIETRACKER.indexActorCollection; }
		else {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = MOVIETRACKER.indexActorCollection[i];
				if (actorData.actor_name.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexActorCollectionFiltered.push(actorData);
				}
			}
		}
		if (MOVIETRACKER.indexActorCollectionFiltered.length > 0) {
			MOVIETRACKER.indexActorCollectionFiltered = dhf.util.sortArray(MOVIETRACKER.searchedTableContent, MOVIETRACKER.lastSortValue, MOVIETRACKER.reverseSort);
		}
		MOVIETRACKER.makeGridTableActorIndex();
	},

	searchFilterDelay: function () {
		'use strict';
		clearTimeout(MOVIETRACKER.delayTimeId);
		MOVIETRACKER.delayTimeId = setTimeout(function() { MOVIETRACKER.filterBySearch(); }, 750);
	},

	searchFilterDelay: function () {
		'use strict';
		clearTimeout(MOVIETRACKER.delayTimeId);
		MOVIETRACKER.delayTimeId = setTimeout(function() { MOVIETRACKER.buildActorChoiceTable(); }, 750);
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
			MOVIETRACKER.chapterCollection = returnData;
			MOVIETRACKER.drawChapters();
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
				MOVIETRACKER.chapterCollection = returnData;
				MOVIETRACKER.drawChapters();
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
			MOVIETRACKER.chapterCollection = returnData;
			MOVIETRACKER.drawChapters();
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
			collectionLength = MOVIETRACKER.chapterCollection.length;

		if ($D('chapterDataBody')) { $D("editChapterFormTable").removeChild($D("chapterDataBody")); }

		dhf.make_tBody({
			"oid": 'chapterDataBody',
			"parentId": 'editChapterFormTable'
		});

		dhf.setDefaults('defaultTableCell', { "nWrap": "nowrap", "ocn": "gridDataCell" });

		for (i = 0; i < collectionLength; i += 1) {
			chapterData = MOVIETRACKER.chapterCollection[i];

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
	},

	fetchSelectedMovies: function () {
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
			dhf.makeTableCell({
				"parentId": currentRowId,
				"oAlign": 'center',
				"vAlign": 'top',
				"iObjs": [
					{ "iFunctName": 'makeImage', "iParams": {
						"oid": 'movieChoice'+currentColumnId,
						"oSrc": '/posters/'+movieData.poster_name,
						"oWidth": movieData.poster_width,
						"oHeight": movieData.poster_height,
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

	clearActorForm: function () {
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
		$D('actorName').focus();
	},

	setActorPageTitle: function () {
		if ($D('actorName').value !== '') { document.title = $D('actorName').value; }
	},

	areYouSure: function () {
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
			if (window.opener !== null) { window.opener.MOVIETRACKER.refreshCollectionActor(); }
			window.close();
		});
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
				else { MOVIETRACKER.refreshParentActor(); }
			});
		}
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

	buildActorNameString: function () {
		'use strict';
		var i = 0,
			actorData = null,
			selectedActorNames = [],
			actorChoiceLength = MOVIETRACKER.allActorChoices.length;

		for (i = 0; i < actorChoiceLength; i += 1) {
			actorData = MOVIETRACKER.allActorChoices[i];
			if (jQuery.inArray(actorData.actor_id, MOVIETRACKER.selectedActors) > -1) {
				selectedActorNames.push('<span data-actorid="'+actorData.actor_id+'">'+actorData.actor_name+'</span>');
			}
		}
		if (selectedActorNames.length >= 1) {
			jQuery("#actorNameString").html(dhf.trim(selectedActorNames.join(', '))).prop('title','Click on Actor Name to go to that page');
		}
	},

	fetchAllActors: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "fetchAllActors" };

		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
			MOVIETRACKER.allActorChoices = actorData;
		});
	},

	fetchSelectedActors: function () {
		'use strict';
		var movieId = $D("movieId").value,
			requestParams = { "className": "movieClass", "methodCall": "fetchActorsInMovie", "movieId": movieId };

		if (movieId > 0) {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {

				var i = 0,
					actorDataLength = actorData.length;

				for (i = 0; i < actorDataLength; i += 1) {
					MOVIETRACKER.selectedActors.push(actorData[i].actor_id);
				}
				MOVIETRACKER.buildActorChoiceTable();
			});
		}
		else {
			MOVIETRACKER.selectedActors = [];
			MOVIETRACKER.buildActorChoiceTable();
		}
	},

	addNewActor: function () {
		'use strict';
		var actorName = dhf.trim($D('searchActor').value),
			movieId = $D("movieId").value,
			requestParams = { "className": "actorClass", "methodCall": "createActor", "actorName": actorName, "movieId": movieId };

		if (parseInt($D('movieId').value, 10) > 0 && actorName !== '') {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				$D('searchActor').value = '';
				$D('searchActor').focus();
				MOVIETRACKER.allActorChoices = actorData.allActors;
				MOVIETRACKER.selectedActors = actorData.selectedActors;
				MOVIETRACKER.buildActorChoiceTable();

				if (window.opener !== null) {
					window.opener.MOVIETRACKER.refreshCollectionActor();
				}
			});
		}
	},

	toggleSelectedActor: function () {
		'use strict';
		var actorObj = jQuery(this),
			actorId = actorObj.val(),
			isChecked = (actorObj.prop("checked")) ? 'Y' : 'N',
			movieId = $D("movieId").value,
			i = 0,
			cleanArray = [],
			requestParams = { "className": "actorClass", "methodCall": "addActorToMovie", "actorId": actorId, "movieId": movieId };

		if (parseInt($D('movieId').value, 10) > 0) {
			if (isChecked === 'Y') {
				requestParams.methodCall = 'addActorToMovie';
				MOVIETRACKER.selectedActors.push(actorId);
			}
			else {
				requestParams.methodCall = 'removeActorFromMovie';
				for (i = 0; i < MOVIETRACKER.selectedActors.length; i += 1) {
					if (MOVIETRACKER.selectedActors[i] !== actorId) {
						cleanArray.push(MOVIETRACKER.selectedActors[i]);
					}
				}
				MOVIETRACKER.selectedActors = cleanArray;
			}
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				$D('searchActor').value = '';
				$D('searchActor').focus();
				MOVIETRACKER.buildActorChoiceTable();
			});
		}
	},

	buildActorChoiceTable: function () {
		'use strict';
		var i = 0,
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

		MOVIETRACKER.filterBySearch();
		collectionLength = MOVIETRACKER.filteredActors.length;
		MOVIETRACKER.selectedActorNames = [];

		if ($D('actorChoicesTable')) { $D("actorForm").removeChild($D("actorChoicesTable")); }

		if (collectionLength < 1) {
			MOVIETRACKER.makeGridTableNoResults();
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
			actorData = MOVIETRACKER.filteredActors[i];
			if (jQuery.inArray(actorData.actor_id, MOVIETRACKER.selectedActors) > -1) {
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
		MOVIETRACKER.buildActorNameString();
	},

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

	clearMovieForm: function () {
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
		MOVIETRACKER.selectedActors = [];
		MOVIETRACKER.buildActorChoiceTable();
		MOVIETRACKER.refreshVarsMovie();
	},

	clearMovieId: function () {
		'use strict';
		$D('actorNameString').innerHTML = '';
		MOVIETRACKER.selectedActors = [];
		MOVIETRACKER.buildActorChoiceTable();
		$D('movieId').value = '';
		$D('title').value = '';
		$D('sortTitle').value = '';
		$D('descLong').value = '';
		$D('privateNotes').value = '';
		$D('title').focus();
		MOVIETRACKER.refreshVarsMovie();
	},

	clearActorFilter: function () {
		'use strict';
		$D('searchActor').value = '';
		MOVIETRACKER.buildActorChoiceTable();
	},

	setMoviePageTitle: function () {
		if ($D('title').value !== '') { document.title = $D('title').value+': Edit Movie'; }
	},

	areYouSure: function () {
		'use strict';
		var movieId = $D('movieId').value,
			buttonData = [
				{ "text": "Yes Delete", "click": function() { jQuery(this).dialog("close"); MOVIETRACKER.deleteMovie() } },
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
		var requestParams = { "className": "movieClass", "methodCall": "deleteMovie", "movieId": $D('movieId').value };
		jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(returnData) {
			if (window.opener !== null) { window.opener.MOVIETRACKER.refreshCollectionMovie(); }
			window.close();
		});
	},

	validateMovie: function () {
		'use strict';
		var movieIdObj = jQuery("#movieId"),
			titleObj = jQuery("#title"),
			sortTitleObj = jQuery("#sortTitle"),
			collectionObj = jQuery("#collection"),
			releaseDateObj = jQuery("#releaseDate"),
			movieScoreObj = jQuery("#movieScore"),
			descShortObj = jQuery("#descShort"),
			descLongObj = jQuery("#descLong"),
			originalUrlObj = jQuery("#originalUrl"),
			coverPosterUploadObj = jQuery("#coverPosterUpload");

		if (titleObj.val() == '') {
			titleObj.addClass("invalid");
			jQuery("label[for=title]").addClass('error');
			dhf.errorHandler.add("No Title Found");
		}
		else {
			titleObj.removeClass("invalid");
			jQuery("label[for=title]").removeClass('error');
		}

		if (sortTitleObj.val() == '') {
			sortTitleObj.addClass("invalid");
			jQuery("label[for=sortTitle]").addClass('error');
			dhf.errorHandler.add("No Sort Title Found");
		}
		else {
			sortTitleObj.removeClass("invalid");
			jQuery("label[for=sortTitle]").removeClass('error');
		}

		if (releaseDateObj.val() == '') {
			releaseDateObj.addClass("invalid");
			jQuery("label[for=releaseDate]").addClass('error');
			dhf.errorHandler.add("No Release Date Found");
		}
		else {
			releaseDateObj.removeClass("invalid");
			jQuery("label[for=releaseDate]").removeClass('error');
		}

		if (descShortObj.val() == '') {
			descShortObj.addClass("invalid");
			jQuery("label[for=descShort]").addClass('error');
			dhf.errorHandler.add("No Short Description Found");
		}
		else {
			descShortObj.removeClass("invalid");
			jQuery("label[for=descShort]").removeClass('error');
		}

		if (descLongObj.val() == '') {
			descLongObj.addClass("invalid");
			jQuery("label[for=descLong]").addClass('error');
			dhf.errorHandler.add("No Long Description Found");
		}
		else {
			descLongObj.removeClass("invalid");
			jQuery("label[for=descLong]").removeClass('error');
		}

		if (originalUrlObj.val() == '') {
			originalUrlObj.addClass("invalid");
			jQuery("label[for=originalUrl]").addClass('error');
			dhf.errorHandler.add("No Original URL Found");
		}
		else {
			originalUrlObj.removeClass("invalid");
			jQuery("label[for=originalUrl]").removeClass('error');
		}

		if (movieIdObj.val() == '' && coverPosterUploadObj.val() == '') {
			coverPosterUploadObj.addClass("invalid");
			jQuery("label[for=coverPosterUpload]").addClass('error');
			dhf.errorHandler.add("No Poster Selected");
		}
		else {
			coverPosterUploadObj.removeClass("invalid");
			jQuery("label[for=coverPosterUpload]").removeClass('error');
		}

		if (!dhf.errorHandler.display()) { $D('editMovieForm').submit(); }
	},

	goToActorProfile: function () {
		'use strict';
		var currObj = jQuery(this),
			actorId = currObj.attr("data-actorid"),
			url = '/editActor.php?actorId='+actorId,
			newWindow = '';

		newWindow = window.open(url, 'editActor'+actorId);
		newWindow.focus();
	},

	playSelectedMovie: function () {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.attr("data-playmovieid"),
			url = '/playMovie.php?movieId='+movieId,
			newWindow = '';

		newWindow = window.open(url, 'playMovie'+movieId);
		newWindow.focus();
	},

	build_genreString: function () {
		'use strict';
		var genreCollection = $D("genre").options,
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
		var movieId = $D("movieId").value,
			selectedGenres = jQuery("#genre").val(),
			requestParams = { "className": "movieGenreXrefClass", "methodCall": "updateChoices", "movieId": movieId, "selectedGenres": JSON.stringify(selectedGenres) };

		if (parseInt($D('movieId').value, 10) > 0) {
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(actorData) {
				MOVIETRACKER.refreshParentMovie();
			});
		}
	},

	updateMovie: function () {
		'use strict';
		var movieId = $D("movieId").value,
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
			MOVIETRACKER.refreshVarsMovie();
			jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(responseData) {
				if (responseData.errorMsg && responseData.errorMsg == 'BAD') { console.log("Error Saving Data"); }
				else {
					$D('cleanTitle').value = responseData.cleanTitle;
					$D('hasChapters').value = responseData.hasChapters;
					MOVIETRACKER.refreshParentMovie();
				}
			});
		}
	},

	build_actorString: function () {
		var i = 0,
			actorData = null,
			selectedActorNames = [],
			actorString  = '',
			actorChoiceLength = MOVIETRACKER.allActorChoices.length;

		for (i = 0; i < actorChoiceLength; i += 1) {
			actorData = MOVIETRACKER.allActorChoices[i];
			if (jQuery.inArray(actorData.actor_id, MOVIETRACKER.selectedActors) > -1) {
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
			if (myOpts[i].selected == true) {
				selectedLabel = myOpts[i].text;
			}
		}
		return selectedLabel;
	},

	showExportData: function () {
		'use strict';
		var currObj = jQuery(this),
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
			exportString += '<span class="exportKey">Actors:</span><span class="exportData">'+MOVIETRACKER.build_actorString()+'</span><br><br>';
			exportString += '<span class="exportKey">Studio:</span><span class="exportData">'+MOVIETRACKER.fetch_selectMenuText('studio')+'</span><br>';
			exportString += '<span class="exportKey">Rating:</span><span class="exportData">'+MOVIETRACKER.fetch_selectMenuText('rating')+'</span><br>';
			exportString += '<span class="exportKey">Genre:</span><span class="exportData">'+MOVIETRACKER.build_genreString()+'</span><br>';
			exportString += '<span class="exportKey">Resolution:</span><span class="exportData">'+MOVIETRACKER.fetch_selectMenuText('format')+'</span><br>';
			exportString += '<span class="exportKey">Score:</span><span class="exportData">'+MOVIETRACKER.fetch_selectMenuText('movieScore')+'</span><br>';
			exportString += '<span class="exportKey">Release Date:</span><span class="exportData">'+tmpDate+'</span><br><br>';
			exportString += '<span class="exportKey">Description:</span>';
			exportString += '<div class="exportData">'+$D("descShort").value+'</div><br>';
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
		var currObj = jQuery(this),
			movieId = $D("movieId").value,
			url = '/editChapters.php?movieId='+movieId,
			newWindow = '';
		if (parseInt(movieId, 10) > 0) {
			newWindow = window.open(url, 'editChapters'+movieId);
			newWindow.focus();
		}
		return;
	},

	draw_goToLetter: function () {
		'use strict';
		var currentLetter = '',
			singleCell,
			singleLetter,
			letterCellCollection = [],
			startLetterLength = MOVIETRACKER.startLetters.length,
			i = 0;

		// Remove the existing row and start over.
		if ($D('goToLetterRow')) { jQuery("#goToLetterRow").remove(); }
		for (i = 0; i < startLetterLength; i += 1) {
			singleLetter = MOVIETRACKER.startLetters[i];
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

	goToLetter: function () {
		'use strict';
		var currObj = jQuery(this),
			currentLetter = currObj.attr("data-gotostartletter"),
			i = 0,
			movieData = null,
			collectionLength = MOVIETRACKER.indexMovieCollection.length;

		// Clear any previous searches and styles.
		$D('searchMovies').value = '';
		jQuery("td.red").removeClass("red");

		// If the clear option was clicked, cleanup and start from scratch.
		if (!currentLetter) { MOVIETRACKER.indexMovieCollectionFiltered = MOVIETRACKER.indexMovieCollection; }
		else {
			currObj.addClass("red");
			MOVIETRACKER.indexMovieCollectionFiltered = [];
			for (i = 0; i < collectionLength; i += 1) {
				movieData = MOVIETRACKER.indexMovieCollection[i];
				if (movieData.sort_title.charAt(0).toUpperCase() === currentLetter) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
			}
		}
		if (MOVIETRACKER.indexMovieCollectionFiltered.length > 0) {
			MOVIETRACKER.indexMovieCollectionFiltered = dhf.util.sortArray(MOVIETRACKER.indexMovieCollectionFiltered, MOVIETRACKER.lastSortValue, MOVIETRACKER.reverseSort);
		}
		MOVIETRACKER.makeGridTable();
	},

	filterBySearch: function () {
		'use strict';
		var i = 0,
			movieData = null,
			collectionLength = MOVIETRACKER.indexMovieCollection.length,
			searchTextPattern = new RegExp($D('searchMovies').value, "i");

		MOVIETRACKER.indexMovieCollectionFiltered = [];
		jQuery("td.red").removeClass("red");
		if ($D('searchMovies').value == '') { MOVIETRACKER.indexMovieCollectionFiltered =  MOVIETRACKER.indexMovieCollection; }
		else {
			for (i = 0; i < collectionLength; i += 1) {
				movieData = MOVIETRACKER.indexMovieCollection[i];
				if (movieData.title.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
				else if (movieData.cleanTitle.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
				else if (movieData.actors.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
				else if (movieData.genre.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
				else if (movieData.resolution.search(searchTextPattern) > -1) {
					MOVIETRACKER.indexMovieCollectionFiltered.push(movieData);
				}
			}
		}
		if (MOVIETRACKER.indexMovieCollectionFiltered.length > 0) {
			MOVIETRACKER.indexMovieCollectionFiltered = dhf.util.sortArray(MOVIETRACKER.indexMovieCollectionFiltered, MOVIETRACKER.lastSortValue, MOVIETRACKER.reverseSort);
		}
		MOVIETRACKER.makeGridTable();
	},

	checkReverseSort: function (sortField) {
		'use strict';
		if (sortField === MOVIETRACKER.lastSortValue) { MOVIETRACKER.reverseSort = (MOVIETRACKER.reverseSort === 'N') ? 'Y' : 'N'; }
		else {
			MOVIETRACKER.reverseSort = 'N';
			MOVIETRACKER.lastSortValue = sortField;
		}
	},

	sortBy: function () {
		'use strict';
		var currObj = jQuery(this),
			sortField = currObj.attr("data-sortby");

		dhf.util.displayWaitDiv('Sorting.....', 'dhf-waitDivHolder');
		MOVIETRACKER.checkReverseSort(sortField);
		MOVIETRACKER.filterBySearch();
	},

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
			displayRowCount = MOVIETRACKER.indexMovieCollectionFiltered.length;

		if ($D('resultsBody')) { jQuery("#resultsBody").remove(); }

		if (displayRowCount < 1) {
			MOVIETRACKER.makeGridTableNoResults();
			return;
		}

		dhf.makeTable({ "oid": "resultsBody", "ocn": "dhf-grid-results", "parentId": "dhf-resultsTableContainer", "tBodyId": "resultsTbody" });
		MOVIETRACKER.makeGridTableHeaderRow();
		dhf.setDefaults('defaultTableCell', { "nWrap": "nowrap", "ocn": "gridDataCell" });
		dhf.rowColor.increment = 1;

		for (i = 0; i < displayRowCount; i += 1) {
			rowCellCollection = [];
			rowContent = MOVIETRACKER.indexMovieCollectionFiltered[i];
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
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.resolution));
			rowCellCollection.push(dhf.makeIobjsCell(rowContent.release_date));
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

	makeGridTableHeaderRow: function () {
		'use strict';
		var sortClass = (MOVIETRACKER.reverseSort === 'Y') ? 'reverseSorted' : 'sorted',
			headerCellCollection = [];

		dhf.setDefaults('defaultTableCell', { "nWrap": 'nowrap', "ocn": 'jsLink' });

		headerCellCollection.push({ "iFunctName": "makeTableCell", "iParams": { "iHTML": '&nbsp;', "ocn": "" } });
		headerCellCollection.push(dhf.makeSortHeaderCell("Movie Title", "sort_title"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Actors", "actors"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Genre", "genre"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Resolution", "resolution"));
		headerCellCollection.push(dhf.makeSortHeaderCell("Release Date", "release_date"));
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
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 700);
	},

	colorRow: function (rowId) { jQuery($D(rowId)).toggleClass('selectedRow'); },

	clearMovieFilter: function () {
		'use strict';
		$D('searchMovies').value = '';
		$D('searchMovies').focus();
		MOVIETRACKER.filterBySearch();
	},

	addNewMovie: function () {
		'use strict';
		var day = new Date(),
			id = day.getTime(),
			newPageName = 'page'+id,
			newWindow = window.open('/editMovie.php?movieId=0', newPageName);

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
	},

	set_movieLength: function () {
		'use strict';
		if (MOVIETRACKER.endTime === 0) {
			MOVIETRACKER.startTime = MOVIETRACKER.movieObj.seekable.start(0);
			MOVIETRACKER.endTime = MOVIETRACKER.movieObj.seekable.end(0);
		}
	},

	playMovie: function () {
		if (MOVIETRACKER.movieObj.readyState === 4) {
			MOVIETRACKER.movieObj.play();
			MOVIETRACKER.set_movieLength();
		}
		else { setTimeout(function() {
			MOVIETRACKER.movieObj.play();
			MOVIETRACKER.set_movieLength();
		}, 1500); }
	},

	playChapter: function () {
		'use strict';
		var currObj = jQuery(this),
			chapStart = currObj.attr("data-chapterstart"),
			chapDesc = currObj.text();

		MOVIETRACKER.movieObj.pause();
		MOVIETRACKER.movieObj.currentTime = chapStart;
		MOVIETRACKER.movieObj.muted = false;
		MOVIETRACKER.playMovie();
		return;
	},

	playFaster: function () {
		'use strict';
		var fasterButton = $D('fasterbutton'),
			slowerButton = $D('slowerbutton');

		if (MOVIETRACKER.playSpeed < 5) {
			if (MOVIETRACKER.playSpeed === 1) { MOVIETRACKER.playSpeed = 2; }
			else { MOVIETRACKER.playSpeed += 1; }
			MOVIETRACKER.movieObj.playbackRate = MOVIETRACKER.playSpeed;
			fasterButton.innerHTML = "Faster ("+MOVIETRACKER.playSpeed+"x)";
			slowerButton.innerHTML = "Slower ("+MOVIETRACKER.playSpeed+"x)";
		}

		if (MOVIETRACKER.playSpeed === 5) {
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

		if (MOVIETRACKER.playSpeed > 1) {
			if (MOVIETRACKER.playSpeed === 2) { MOVIETRACKER.playSpeed = 1; }
			else { MOVIETRACKER.playSpeed -= 1; }
			MOVIETRACKER.movieObj.playbackRate = MOVIETRACKER.playSpeed;
			fasterButton.innerHTML = "Faster ("+MOVIETRACKER.playSpeed+"x)";
			slowerButton.innerHTML = "Slower ("+MOVIETRACKER.playSpeed+"x)";
		}

		if (MOVIETRACKER.playSpeed === 1) {
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

		MOVIETRACKER.set_movieLength();
		MOVIETRACKER.movieObj.pause();
		currentPlayPoint = MOVIETRACKER.movieObj.currentTime;
		newPlayPoint = parseFloat(currentPlayPoint + interval);
		if (MOVIETRACKER.endTime > newPlayPoint) {
			MOVIETRACKER.movieObj.currentTime = newPlayPoint;
			MOVIETRACKER.playMovie();
		}
		else { MOVIETRACKER.playMovie(); }
	},

	jumpBack: function () {
		'use strict';
		var currObj = jQuery(this),
			interval = parseFloat(currObj.attr("data-jumpback")),
			currentPlayPoint = 0,
			newPlayPoint = 0;

		MOVIETRACKER.set_movieLength();
		MOVIETRACKER.movieObj.pause();
		currentPlayPoint = MOVIETRACKER.movieObj.currentTime;
		newPlayPoint = parseFloat(currentPlayPoint - interval);
		console.log("Interval: "+interval);
		console.log("New Play Point: "+newPlayPoint);
		if (newPlayPoint > 0) {
			MOVIETRACKER.movieObj.currentTime = newPlayPoint;
			MOVIETRACKER.playMovie();
		}
		else { MOVIETRACKER.playMovie(); }
	}
};


/* editChapter From actorList.php */
var gridControl = {

	delayTimeId: null,
	reportTableContent: <?php print utf8_encode(json_encode($actorObj->fetch_actorCollection())); ?>,
	searchedTableContent: [],
	lastSortValue: 'actor_name',
	reverseSort: 'N',
	selectedRows: [],
	init: function () {
		'use strict';
		gridControl.searchedTableContent = gridControl.reportTableContent;
		gridControl.filterBySearch();
	},

	refreshCollection: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "fetchActorCollection" };

		jQuery(".selectedRow").each(function() { gridControl.selectedRows.push(this.id); });

		// Waits for the promise to be resolved before marking the rows selected that were selected before the refresh.
		jQuery.when(gridControl.fetch_actorCollection()).then(function () {
			var i = 0,
				localRows = gridControl.selectedRows,
				selectedRowsLength = localRows.length;

			for (i = 0; i < selectedRowsLength; i += 1) {
				jQuery("#"+localRows[i]).addClass('selectedRow');
			}
		});
	},

	fetch_actorCollection: function () {
		'use strict';
		var requestParams = { "className": "actorClass", "methodCall": "fetchActorCollection" },
			dfWait;

		dfWait = jQuery.getJSON('/ajaxHandlers/requestHandler.php', requestParams, function(collectionData) {
			gridControl.reportTableContent = collectionData;
			gridControl.init();
		});
		return dfWait.promise();
	},

	// Adds a delay in redrawing the grid to allow typing.
	searchFilterDelay: function () {
		'use strict';
		clearTimeout(gridControl.delayTimeId);
		gridControl.delayTimeId = setTimeout(function() { gridControl.filterBySearch(); }, 750);
	},

	filterBySearch: function () {
		'use strict';
		var i = 0,
			actorData = null,
			collectionLength = gridControl.reportTableContent.length,
			searchTextPattern = new RegExp($D('searchActors').value, "i");

		gridControl.searchedTableContent = [];

		if ($D('searchActors').value == '') { gridControl.searchedTableContent =  gridControl.reportTableContent; }
		else {
			for (i = 0; i < collectionLength; i += 1) {
				actorData = gridControl.reportTableContent[i];
				if (actorData.actor_name.search(searchTextPattern) > -1) {
					gridControl.searchedTableContent.push(actorData);
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
	sortBy: function (sortField) {
		'use strict';
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
			hasPictureCell,
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

	// Draws the header thead row for the table.
	makeGridTableHeaderRow: function () {
		'use strict';
		var sortClass = (gridControl.reverseSort === 'Y') ? 'reverseSorted' : 'sorted',
			headerCellCollection = [];

		dhf.setDefaults('defaultTableCell', { nWrap: 'nowrap', ocn: 'jsLink' });

		headerCellCollection.push({ "iFunctName": "makeTableCell", "iParams": { "iHTML": '&nbsp;', "ocn": "" } });
		headerCellCollection.push(dhf.makeSortHeaderCell("Actor Name", "actor_name"));
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
		setTimeout(function() { dhf.util.removeWaitDiv(); }, 10);
	},

	// Simple method to set the background color orange when a row is clicked on.
	colorRow: function (rowId) { jQuery($D(rowId)).toggleClass('selectedRow'); },

	clearMovieFilter: function () {
		'use strict';
		$D('searchActors').value = '';
		$D('searchActors').focus();
		gridControl.filterBySearch();
	},

	addNewActor: function () {
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

/* editChapter From editChapters.php */
/* editActor From editActor.php */
/* editMovie From editMovie.php */
/* gridControl From index.php */
/* movieControllerFrom playMovie */