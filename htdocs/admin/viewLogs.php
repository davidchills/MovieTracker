<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
$logObj = new LOGGER_REPORT();
$movieObj = new MOVIE();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>View Logs</title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>
<style type="text/css">
td.padTopBottom {
	padding-top: 5px;
	padding-bottom: 5px;
	white-space: nowrap;
}
td.eventDescription {
	border: 1px solid black;
	font-weight: normal;
	padding: 0px;
	width: 735px;
}
td.eventDescription div {
	height: auto;
	width: 735px;
	margin: 0;
	border: 0;
	padding: 3px;
	clip: rect(0px, 735px, 1000px, 0px);
	overflow: auto;
}
td.eventDescriptionWarning {
	font-weight: bold;
	text-align: center;
	color: red;
}
#resultsTbody td.subHeader {
	border: solid black 1px;
	padding: 3px;
	white-space: nowrap;
	font-weight: bold;
	overflow: hidden;
}
#gridHeaderRow { border: 1px solid #0785C8; }
input[type="text"], input[type="search"], input[type="url"], textarea, select {
	background-color: #EAF4FD;
	background-image: linear-gradient(#FFFFFF 0%, #EAF4FD 100%);
	border: 1px inset #AAAAAA;
	cursor: text;
	padding: 0 5px;
	position: relative;
}
input[type="text"]:focus, input[type="search"]:focus, input[type="url"]:focus, textarea:focus, select:focus {
	background-color: #FEFCEA;
	background-image: linear-gradient(#FEFCEA 0%, #F5F78C 100%);
	background: #FEFCEA;
	background: -moz-linear-gradient(top, #FEFCEA 0%, #F5F78C 100%);
	background: -webkit-linear-gradient(top, #FEFCEA 0%,#F5F78C 100%);
	background: linear-gradient(to bottom, #FEFCEA 0%,#F5F78C 100%);
}
</style>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>
<script type="text/javascript">
var ReportLogger = {
	fetchTimeOut: null,
	logEvents: [],
	logNameArray: ['MOVIE','ACTOR'],
	logLevelArray: ['0'],
	loggedByArray: ['ALL'],
	javascriptLogging: 'OFF',
	reverseSort: 'Y',
	lastSortValue: 'eventDate',
	reqParam: {
		className: 'loggerReportClass',
		methodCall: 'fetchLogs',
		logName: JSON.stringify(['MOVIE','ACTOR']),
		logLevel: JSON.stringify(['0']),
		loggedBy: JSON.stringify(['ALL']),
		startDate: '2016-01-01',
		endDate: '2016-01-02',
		movieId: 'ALL',
		logNameExclude: 'N',
		logLevelExclude: 'N',
		loggedByExclude: 'N'
	},

	setLogging: function(logValue) {
		'use strict';
		ReportLogger.javascriptLogging = (logValue === 'ON') ? 'ON' : 'OFF';
		Logger.setLogging(ReportLogger.javascriptLogging);
	},

	getReportFromOnload: function() {
		'use strict';
		ReportLogger.reqParam.startDate = $D('startDate').value;
		ReportLogger.reqParam.endDate = $D('endDate').value;
		ReportLogger.realGetReport();
	},

	set_movieId: function() {
		'use strict';
		var currObj = jQuery(this),
			movieId = currObj.val();

		ReportLogger.reqParam.movieId = movieId;
		ReportLogger.realGetReport();
	},

	set_startDate: function() {
		'use strict';
		var currObj = jQuery(this),
			startDate = currObj.val();

		ReportLogger.reqParam.startDate = startDate;
		ReportLogger.getReport();
	},

	set_endDate: function() {
		'use strict';
		var currObj = jQuery(this),
			endDate = currObj.val();

		ReportLogger.reqParam.endDate = endDate;
		ReportLogger.getReport();
	},

	set_logNameExclude: function() {
		'use strict';
		var currObj = jQuery(this),
			isChecked = currObj.prop("checked"),
			logNameObj = $D('logName');

		if (isChecked === true) {
			logNameObj.options[0].selected = false;
			logNameObj.options[0].disabled = true;
			ReportLogger.reqParam.logNameExclude = 'Y';
			ReportLogger.set_logName();
		}
		else {
			logNameObj.options[0].disabled = false;
			ReportLogger.reqParam.logNameExclude = 'N';
			ReportLogger.set_logName();
		}
	},

	set_logLevelExclude: function() {
		'use strict';
		var currObj = jQuery(this),
			isChecked = currObj.prop("checked"),
			logLevelObj = $D('logLevel');

		if (isChecked === true) {
			logLevelObj.options[0].selected = false;
			logLevelObj.options[0].disabled = true;
			ReportLogger.reqParam.logLevelExclude = 'Y';
			ReportLogger.set_logLevel();
		}
		else {
			logLevelObj.options[0].disabled = false;
			ReportLogger.reqParam.logLevelExclude = 'N';
			ReportLogger.set_logLevel();
		}
	},

	set_loggedByExclude: function() {
		'use strict';
		var currObj = jQuery(this),
			isChecked = currObj.prop("checked"),
			loggedByObj = $D('loggedBy');

		if (isChecked === true) {
			loggedByObj.options[0].selected = false;
			loggedByObj.options[0].disabled = true;
			ReportLogger.reqParam.loggedByExclude = 'Y';
			ReportLogger.set_loggedBy();
		}
		else {
			loggedByObj.options[0].disabled = false;
			ReportLogger.reqParam.loggedByExclude = 'N';
			ReportLogger.set_loggedBy();
		}
	},

	set_logName: function() {
		'use strict';
		var i = 0,
			logNameObj = $D('logName'),
			logNameExcludeObj = $D('logNameExclude'),
			selectedOptions = jQuery(logNameObj).val();

		ReportLogger.logNameArray = selectedOptions;
		// If 'ALL' is selected, uncheck everything else
		if (logNameObj.options[0].selected === true || selectedOptions.length === 0) {
			for (i = 0; i < logNameObj.options.length; i += 1) { logNameObj.options[i].selected = false; }
			ReportLogger.logNameArray = ['ALL'];
			ReportLogger.reqParam.logName = JSON.stringify(['ALL']);
			logNameObj.value = 'ALL';
			logNameExcludeObj.checked = false;
			logNameExcludeObj.disabled = true;
			ReportLogger.reqParam.logNameExclude = 'N';
			ReportLogger.getReport();
		}
		else {
			logNameExcludeObj.disabled = false;
			ReportLogger.logNameArray = selectedOptions;
			ReportLogger.reqParam.logName = JSON.stringify(selectedOptions);
			ReportLogger.reqParam.logNameExclude = (logNameExcludeObj.checked === true) ? 'Y' : 'N';
			ReportLogger.getReport();
		}
	},

	set_logLevel: function() {
		'use strict';
		var i = 0,
			logLevelObj = $D('logLevel'),
			logLevelExcludeObj = $D('logLevelExclude'),
			selectedOptions = jQuery(logLevelObj).val();

		ReportLogger.logLevelArray = selectedOptions;
		// If 'ALL' is selected, uncheck everything else
		if (logLevelObj.options[0].selected === true || selectedOptions.length === 0) {
			for (i = 0; i < logLevelObj.options.length; i += 1) { logLevelObj.options[i].selected = false; }
			ReportLogger.logLevelArray = ['0'];
			ReportLogger.reqParam.logLevel = JSON.stringify(['0']);
			logLevelObj.value = '0';
			logLevelExcludeObj.checked = false;
			logLevelExcludeObj.disabled = true;
			ReportLogger.reqParam.logLevelExclude = 'N';
			ReportLogger.getReport();
		}
		else {
			logLevelExcludeObj.disabled = false;
			ReportLogger.logLevelArray = selectedOptions;
			ReportLogger.reqParam.logLevel = JSON.stringify(selectedOptions);
			ReportLogger.reqParam.logLevelExclude = (logLevelExcludeObj.checked === true) ? 'Y' : 'N';
			ReportLogger.getReport();
		}
	},

	set_loggedBy: function() {
		'use strict';
		var i = 0,
			loggedByObj = $D('loggedBy'),
			loggedByExcludeObj = $D('loggedByExclude'),
			selectedOptions = jQuery(loggedByObj).val();

		ReportLogger.loggedByArray = selectedOptions;
		if (loggedByObj.options[0].selected === true || selectedOptions.length === 0) {
			for (i = 0; i < loggedByObj.options.length; i += 1) { loggedByObj.options[i].selected = false; }
			ReportLogger.loggedByArray = ['ALL'];
			ReportLogger.reqParam.loggedBy = JSON.stringify(['ALL']);
			loggedByObj.value = 'ALL';
			loggedByExcludeObj.checked = false;
			loggedByExcludeObj.disabled = true;
			ReportLogger.reqParam.loggedByExclude = 'N';
			ReportLogger.getReport();
		}
		else {
			loggedByExcludeObj.disabled = false;
			ReportLogger.loggedByArray = selectedOptions;
			ReportLogger.reqParam.loggedBy = JSON.stringify(selectedOptions);
			ReportLogger.reqParam.loggedByExclude = (loggedByExcludeObj.checked === true) ? 'Y' : 'N';
			ReportLogger.getReport();
		}
	},

	getReport: function() {
		'use strict';
		if (ReportLogger.fetchTimeOut !== null) {
			clearTimeout(ReportLogger.fetchTimeOut);
			ReportLogger.fetchTimeOut = null;
		}
		ReportLogger.fetchTimeOut = setTimeout(ReportLogger.realGetReport, 2500);
	},

	realGetReport: function() {
		'use strict';
		if (ReportLogger.fetchTimeOut !== null) {
			clearTimeout(ReportLogger.fetchTimeOut);
			ReportLogger.fetchTimeOut = null;
		}

		if (ReportLogger.logNameArray.length === 0) { return; }
		else if (ReportLogger.logLevelArray.length === 0) { return; }
		else if (ReportLogger.loggedByArray.length === 0) { return; }
		else if (ReportLogger.reqParam.startDate === '') { return; }
		else if (ReportLogger.reqParam.endDate === '') { return; }

		dhf.util.displayWaitDiv('Loading.....', 'eventsHeader');

		jQuery.getJSON('/ajaxHandlers/requestHandler.php', ReportLogger.reqParam, function(data) {

			setTimeout(function () { dhf.util.removeWaitDiv(); }, 1500);
			ReportLogger.logEvents = data;
			if (ReportLogger.logEvents.length > 0) {
				dhf.util.sortArray(ReportLogger.logEvents, ReportLogger.lastSortValue, ReportLogger.reverseSort);
				setTimeout(function () { dhf.util.removeWaitDiv(); }, 1500);
			}
			setTimeout(function () { dhf.util.removeWaitDiv(); }, 1500);
			ReportLogger.makeGridTable();
		});
	},

	checkReverseSort: function(sortField) {
		'use strict';
		if (sortField === ReportLogger.lastSortValue) { ReportLogger.reverseSort = (ReportLogger.reverseSort === 'N') ? 'Y' : 'N'; }
		else {
			ReportLogger.reverseSort = 'N';
			ReportLogger.lastSortValue = sortField;
		}
	},

	sortBy: function() {
		'use strict';
		var currObj = jQuery(this),
			sortField = currObj.attr("data-sortby");

		ReportLogger.checkReverseSort(sortField);

		if (ReportLogger.logEvents.length > 0) {
			dhf.util.displayWaitDiv('Sorting.....', 'eventsHeader');
			dhf.util.sortArray(ReportLogger.logEvents, ReportLogger.lastSortValue, ReportLogger.reverseSort);
			setTimeout(function () { dhf.util.removeWaitDiv(); }, 1500);
		}
		setTimeout(function () { dhf.util.removeWaitDiv(); }, 1500);
		ReportLogger.makeGridTable();
	},

	makeGridTable: function() {
		'use strict';
		var i = 0,
			rowContent,
			rowsRetrieved = ReportLogger.logEvents.length,
			displayRowCount = (rowsRetrieved <= 50) ? rowsRetrieved : 50,
			rowColorClass = dhf.altRowColor();

		if ($D('resultsBody')) { jQuery("#resultsBody").remove(); }

		if (rowsRetrieved < 1) {
			ReportLogger.makeGridTableNoResults();
			return;
		}
		dhf.makeTable({ "oid": 'resultsBody', "oWidth": '735', "ocn": 'dhf-grid-results', "parentId": 'eventsData', "tBodyId": 'resultsTbody' });

		ReportLogger.makeGridTableHeaderRow();

		if (rowsRetrieved > 50) {
			dhf.makeTableRow({
				"parentId": 'resultsTbody',
				"ocn": rowColorClass,
				"iObjs": [{
					"iFunctName": 'makeTableCell',
					"iParams": {
						"ocn": 'eventDescriptionWarning',
						"cSpan": '5',
						"iObjs": [{ "iFunctName": 'makeDiv', "iParams": { "iHTML": 'Too many results returned ('+rowsRetrieved+'). The display was limited to 50.<br />Try to narrow your criteria.' } }]
					}
				}]
			});
		}

		dhf.setDefaults('defaultTableCell', { "ocn": 'subHeader' });

		for (i = 0; i < displayRowCount; i += 1) {
			rowContent = ReportLogger.logEvents[i];
			rowColorClass = dhf.altRowColor();

			dhf.makeTableRow({
				"parentId": 'resultsTbody',
				"ocn": rowColorClass,
				"iObjs": [
					{ "iFunctName": 'makeTableCell', "iParams": { "oWidth": '191', "iHTML": rowContent.logName } },
					{ "iFunctName": 'makeTableCell', "iParams": { "oWidth": '210', "iHTML": rowContent.eventDate } },
					{ "iFunctName": 'makeTableCell', "iParams": { "oWidth": '92', "iHTML": rowContent.logLevel } },
					{ "iFunctName": 'makeTableCell', "iParams": { "oWidth": '115', "iHTML": rowContent.loggedBy } },
					{ "iFunctName": 'makeTableCell', "iParams": { "oWidth": '93', "iHTML": rowContent.movieId } }
				]
			});

			dhf.makeTableRow({
				"parentId": 'resultsTbody',
				"ocn": rowColorClass,
				"iObjs": [{
					"iFunctName": 'makeTableCell',
					"iParams": {
						"ocn": 'eventDescription',
						"cSpan": '5',
						"iObjs": [{ "iFunctName": 'makeDiv', "iParams": { "iHTML": rowContent.logDescription } }]
					}
				}]
			});
		}

		dhf.setDefaults('defaultTableCell', { "ocn": '' });

	},

	makeGridTableHeaderRow: function() {
		'use strict';
		var sortClass = (ReportLogger.reverseSort === 'Y') ? 'reverseSorted' : 'sorted';

		dhf.setDefaults('defaultTableCell', { "nWrap": 'nowrap', "ocn": 'jsLink' });
		dhf.makeTableHeader({
			"oid": 'resultsThead',
			"parentId": 'resultsBody',
			"iObjs": [
				{ "iFunctName": 'makeTableRow',
					"iParams": {
						"oid": 'gridHeaderRow',
						"parentId": 'resultsThead',
						"ocn": 'dhf-grid-header-row',
						"iObjs": [
							{ "iFunctName": 'makeTableCell', "iParams": { "oid": 'logNameHeader', "oWidth": '191', "iHTML": 'Log Name', "oAttr": { "oName": "data-sortby", "oValue": 'logName' } } },
							{ "iFunctName": 'makeTableCell', "iParams": { "oid": 'eventDateHeader', "oWidth": '210', "iHTML": 'Event Date', "oAttr": { "oName": "data-sortby", "oValue": 'eventDate' } } },
							{ "iFunctName": 'makeTableCell', "iParams": { "oid": 'logLevelHeader', "oWidth": '92', "iHTML": 'Log Level', "oAttr": { "oName": "data-sortby", "oValue": 'logLevel' } } },
							{ "iFunctName": 'makeTableCell', "iParams": { "oid": 'loggedByHeader', "oWidth": '115', "iHTML": 'Logged By', "oAttr": { "oName": "data-sortby", "oValue": 'loggedBy' } } },
							{ "iFunctName": 'makeTableCell', "iParams": { "oid": 'movieIdHeader', "oWidth": '93', "iHTML": 'Movie Id', "oAttr": { "oName": "data-sortby", "oValue": 'movieId' } } }
						]
					}
				}
			]
		});
		dhf.setDefaults('defaultTableCell', { "nWrap": '', "ocn": '' });

		jQuery("#gridHeaderRow > td").removeClass('sorted');
		jQuery("#"+ReportLogger.lastSortValue+"Header").addClass(sortClass);
	},

	makeGridTableNoResults: function() {
		'use strict';
		if ($D('resultsBody')) { $D('eventsData').removeChild($D('resultsBody')); }
		dhf.makeTable({ "oid": 'resultsBody', "ocn": 'dhf-grid-results', "parentId": 'eventsData', "tBodyId": 'resultsTbody' });
		dhf.makeTableRow({
			"parentId": 'resultsTbody',
			"ocn": 'dhf-noResults',
			"iObjs": [{ "iFunctName": 'makeTableCell', "iParams": { "oAlign": 'center', "nWrap": 'nowrap', "ocn": 'eventDescription', "iHTML": 'No Results Found' } }]
		});
	}

};

// Adding an error handler for the getJSON call
jQuery(document).ajaxError(function() {
	dhf.util.removeWaitDiv();
	if ($D('resultsBody')) { jQuery("#resultsBody").remove(); }
	dhf.makeTable({ "oid": 'resultsBody', "ocn": 'dhf-grid-results', "parentId": 'eventsData', "tBodyId": 'resultsTbody' });
	dhf.makeTableRow({
		"parentId": 'resultsTbody',
		"ocn": 'row_color2',
		"iObjs": [{ "iFunctName": 'makeTableCell', "iParams": { "oAlign": 'center', "nWrap": 'nowrap', "ocn": 'eventDescription', "iHTML": 'Triggered ajaxError handler.' } }]
	});
});

jQuery(document).ready(function() {
	// Setup the date picker default values
	jQuery.datepicker.setDefaults({
		dateFormat: 'yy-mm-dd',
		showOn: 'both',
		buttonText: 'Choose Date',
		buttonImage: '/images/Calendar_scheduleHS.png',
		buttonImageOnly: true,
		mandatory: true,
		minDate: new Date(2015, 1, 1),
		maxDate: '+1d',
		changeYear: true,
		changeMonth: true,
		monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
	});
	if ($D('logName').value === '') { $D('logName').selectedIndex = 0; }
	if ($D('logLevel').value === '') { $D('logLevel').selectedIndex = 0; }
	if ($D('loggedBy').value === '') { $D('loggedBy').selectedIndex = 0; }
	jQuery("#startDate").datepicker();
	jQuery("#endDate").datepicker();
	jQuery("#startDate").on("change", ReportLogger.set_startDate);
	jQuery("#endDate").on("change", ReportLogger.set_endDate);
	jQuery("#movieId").on("change", ReportLogger.set_movieId);
	jQuery('#logNameExclude').on("click", ReportLogger.set_logNameExclude);
	jQuery('#logName').on("change", ReportLogger.set_logName);
	jQuery('#logLevelExclude').on("click", ReportLogger.set_logLevelExclude);
	jQuery('#logLevel').on("change", ReportLogger.set_logLevel);
	jQuery('#loggedByExclude').on("click", ReportLogger.set_loggedByExclude);
	jQuery('#loggedBy').on("change", ReportLogger.set_loggedBy);
	jQuery(document).on("click", "td[data-sortby]", ReportLogger.sortBy);
	ReportLogger.getReportFromOnload();
});
</script>
</head>
<body>
<div id="container">
	<div id="wrap">
	<?php include_once($_SERVER["DOCUMENT_ROOT"].'/inc/tabNav.php'); ?>
	<div id="dhf-waitDivHolder"></div>
	<fieldset class="adminEditBlock">
	<legend>View Logs</legend>
	<form id="editAction" name="editAction" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table border="0" cellspacing="0" cellpadding="0" width="735" align="center" class="logReportResults">
		<tbody>

		<!-- Date Range Fields -->
		<tr>
			<td colspan="3" width="735" height="30" valign="middle">
			<table border="0" cellspacing="0" cellpadding="0" width="735" align="center">
				<tbody>
				<tr>
					<td align="left" width="320"><span  class="bold">Start Date</span> <input type="text" id="startDate" name="startDate" size="11" maxlength="10" value="<?php print date('Y-m-d', (time() - 86400)); ?>" readonly="readonly" /></td>
					<td align="left" width="415"><span  class="bold">End Date</span> <input type="text" id="endDate" name="endDate" size="11" maxlength="10" value="<?php print date('Y-m-d'); ?>" readonly="readonly" /></td>
				</tr>
				</tbody>
			</table>
			</td>
		</tr>

		<!-- Movie Select Menu -->
		<tr>
			<td colspan="3" width="735" height="30" valign="middle" class="padTopBottom">
			<span  class="bold">Movie:</span>
			<select id="movieId" name="movieId" size="1" style="width: 530px;">
			<option value="ALL">ALL</option>
			<option value="0">Events Not Related to a Movie</option>
			<?php print $movieObj->build_movieSelectList(); ?>
			</select>
			</td>
		</tr>

		<!-- Filter Choices -->
		<tr>
			<!-- Filter Log Name -->
			<td width="265" height="120" valign="middle" class="padTopBottom">
			<span  class="bold">Log Name</span> (exclude <input name="logNameExclude" id="logNameExclude" type="checkbox" value="Y" />)<br />
			<select id="logName" name="logName[]" size="7" style="width: 220px;" multiple="multiple">
			<?php print $logObj->build_log_name_select_menu((isset($_REQUEST['logName']) && is_array($_REQUEST['logName'])) ? $_REQUEST['logName'] : array('ACTOR','MOVIE')); ?>
			</select>
			</td>

			<!-- Filter Log Level -->
			<td width="235" height="120" valign="middle" class="padTopBottom">
			<span  class="bold">Log Level</span> (exclude <input name="logLevelExclude" id="logLevelExclude" type="checkbox" value="Y" disabled="disabled" />)<br />
			<select id="logLevel" name="logLevel[]" size="7" style="width: 180px;" multiple="multiple">
			<?php print $logObj->build_log_level_select_menu((isset($_REQUEST['logLevel']) && is_array($_REQUEST['logLevel'])) ? $_REQUEST['logLevel'] : array(0)); ?>
			</select>
			</td>

			<!-- Filter Logged By -->
			<td width="235" height="120" valign="middle" class="padTopBottom">
			<span  class="bold">Logged By</span> (exclude <input name="loggedByExclude" id="loggedByExclude" type="checkbox" value="Y" disabled="disabled" />)<br />
			<select id="loggedBy" name="loggedBy[]" size="7" style="width: 180px;" multiple="multiple">
			<?php print $logObj->build_logged_by_select_menu((isset($_REQUEST['loggedBy']) && is_array($_REQUEST['loggedBy'])) ? $_REQUEST['loggedBy'] : array('ALL')); ?>
			</select>
			</td>
		</tr>

		<!-- Simple Header -->
		<tr><td colspan="3" width="735" align="left" id="eventsHeader" class="padTopBottom"><span class="bold">Log Events</span></td></tr>

		<!-- Target for results data -->
		<tr><td colspan="3" width="735" id="eventsData" align="left">No data</td></tr>

		</tbody>
	</table>
	</form>
	</fieldset>
	<br clear="all" />
	</div>
</div>
</body>
</html>