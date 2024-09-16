<script type="text/javascript">
jQuery(document).ready(function() {
	var navTabIndex = 0;

	if (location.pathname.indexOf('/index.php') > -1) { navTabIndex = 0; }
	else if (location.pathname.indexOf('/actorList.php') > -1) { navTabIndex = 0; }
	else if (location.pathname.indexOf('/editMovie.php') > -1) { navTabIndex = 1; }
	else if (location.pathname.indexOf('/editActor.php') > -1) { navTabIndex = 1; }
	else if (location.pathname.indexOf('/editChapters.php') > -1) { navTabIndex = 1; }
	else if (location.pathname.indexOf('/admin/') > -1) { navTabIndex = 2; }
	else { navTabIndex = 0; }
	jQuery("#navigationTabsContainer").tabs({ active: navTabIndex });

	jQuery("navLinkSelected").removeClass("navLinkSelected");
	if (location.pathname.indexOf('index.php') > -1) { jQuery("#movieListPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/actorList.php') > -1) { jQuery("#actorListPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/editMovie.php') > -1) { jQuery("#movieEditPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/editActor.php') > -1) { jQuery("#actorEditPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/editChapters.php') > -1) { jQuery("#chapterEditPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/users/logout.php') > -1) { jQuery("#logoutPage").addClass("navLinkSelected"); }
	else if (location.pathname.indexOf('/admin/viewLogs.php') > -1) { jQuery("#adminViewLogs").addClass("navLinkSelected"); }
	else { jQuery("#movieListPage").addClass("navLinkSelected"); }
});
</script>

<div id="navigationTabsContainer" class="ui-tabs">
	<ul>
		<li><a href="#navTabsMain">Movies/Actors</a></li>
		<li><a href="#navTabsEdits">Add &amp; Edit</a></li>
		<li><a href="#navTabsAdmin">Admin</a></li>
	</ul>

	<div id="navTabsMain" style="display: none;">
	<a id="movieListPage" href="/index.php" target="_blank">Movie List</a> |
	<a id="actorListPage" href="/actorList.php" target="_blank">Actor List</a> |
	<a id="logoutPage" href="/users/logout.php">Logout</a>
	</div>

	<div id="navTabsEdits" style="display: none;">
	<a id="movieEditPage" href="/editMovie.php?movieId=0" target="_blank">Add or Edit Movie</a> |
	<a id="actorEditPage" href="/editActor.php?actorId=0" target="_blank">Add or Edit Actor</a> |
	<a id="chapterEditPage" href="/editChapters.php?movieId=0" target="_blank">Add or Edit Chapters</a> |
	<a id="genreEditPage" href="/">Edit Genre (SOON)</a> |
	<a id="ratingEditPage" href="/">Edit Rating (SOON)</a>
	</div>

	<div id="navTabsAdmin" style="display: none;">
	<a id="adminViewLogs" href="/admin/viewLogs.php" target="_blank">View Logs</a>
	</div>
</div>
