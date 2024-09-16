<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
class AJAX_REQUESTS extends MOVIETRACKER {

	protected $reqVars;
	public $outputHeaders = array();
	public $outPutResults;

	public function __construct($request_array) {

		$this->reqVars = $request_array;

		if (isset($this->reqVars['className']) && isset($this->reqVars['methodCall'])) {

			switch($this->reqVars['className']) {

				case 'movieClass':
					$this->movieClass();
					break;

				case 'moviePosterClass':
					$this->moviePosterClass();
					break;

				case 'actorClass':
					$this->actorClass();
					break;

				case 'actorPictureClass':
					$this->actorPictureClass();
					break;

				case 'loggerReportClass':
					$this->loggerReportClass();
					break;

				case 'movieGenreXrefClass':
					$this->movieGenreXrefClass();
					break;

				case 'movieChapterClass':
					$this->movieChapterClass();
					break;

				default:
					return;
					break;

			}
		}
	}

	// movieClass
	private function movieClass() {
		if ($this->reqVars['methodCall'] == 'fetchActorsInMovie') { $this->movieClass_fetchActorsInMovie(); }
		elseif ($this->reqVars['methodCall'] == 'fetchAllMovies') { $this->movieClass_fetchAllMovies(); }
		elseif ($this->reqVars['methodCall'] == 'fetchMovieCollection') { $this->movieClass_fetchMovieCollection(); }
		elseif ($this->reqVars['methodCall'] == 'updateMovie') { $this->movieClass_updateMovie(); }
		elseif ($this->reqVars['methodCall'] == 'deleteMovie') { $this->movieClass_deleteMovie(); }
	}
	private function movieClass_fetchActorsInMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$movieObj = new MOVIE($this->reqVars['movieId']);
		$collection = $movieObj->fetch_actorsInMovie($this->reqVars['movieId']);
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function movieClass_fetchAllMovies() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$movieObj = new MOVIE();
		$collection = $movieObj->fetch_allMovies();
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function movieClass_fetchMovieCollection() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$movieObj = new MOVIE();
		$collection = $movieObj->fetch_movieCollection();
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function movieClass_updateMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$movieObj = new MOVIE($this->reqVars['movieId']);
		if (isset($this->reqVars['title'])) { $movieObj->set_title($this->reqVars['title']); }
		if (isset($this->reqVars['sortTitle'])) { $movieObj->set_sortTitle($this->reqVars['sortTitle']); }
		if (isset($this->reqVars['studio'])) { $movieObj->set_studio($this->reqVars['studio']); }
		if (isset($this->reqVars['collection'])) { $movieObj->set_collection($this->reqVars['collection']); }
		if (isset($this->reqVars['rating'])) { $movieObj->set_rating($this->reqVars['rating']); }
		if (isset($this->reqVars['format'])) { $movieObj->set_format($this->reqVars['format']); }
		if (isset($this->reqVars['releaseDate'])) { $movieObj->set_releaseDate($this->reqVars['releaseDate']); }
		if (isset($this->reqVars['descShort'])) { $movieObj->set_descShort($this->reqVars['descShort']); }
		if (isset($this->reqVars['descLong'])) { $movieObj->set_descLong($this->reqVars['descLong']); }
		if (isset($this->reqVars['movieScore'])) { $movieObj->set_movieScore($this->reqVars['movieScore']); }
		if (isset($this->reqVars['originalUrl'])) { $movieObj->set_originalUrl($this->reqVars['originalUrl']); }
		if (isset($this->reqVars['isActive'])) { $movieObj->set_isActive($this->reqVars['isActive']); }
		if (isset($this->reqVars['privateNotes'])) { $movieObj->set_privateNotes($this->reqVars['privateNotes']); }
		$movieObj->save_movie();

		if (count($movieObj->errors) > 0) {
			$tmpObj = new stdClass();
			$tmpObj->response = "BAD";
			$this->outPutResults = json_encode($tmpObj);
		}
		else {
			$this->outPutResults = json_encode($movieObj->logableObj($movieObj));
		}
	}
	private function movieClass_deleteMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$movieObj = new MOVIE($this->reqVars['movieId']);
		$movieObj->delete_movie();
		$tmpObj = new stdClass();
		$tmpObj->response = "OK";
		$this->outPutResults = json_encode($tmpObj);
	}


	// moviePosterClass
	private function moviePosterClass() {
		if ($this->reqVars['methodCall'] == 'fetchPosterObj') { $this->moviePosterClass_fetchPosterObj(); }
	}
	private function moviePosterClass_fetchPosterObj() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$posterObj = new MOVIE_POSTER($this->reqVars['movieId']);
		$this->outPutResults = utf8_encode(json_encode($posterObj->logableObj($posterObj)));
	}


	// actorClass
	private function actorClass() {
		if ($this->reqVars['methodCall'] == 'fetchAllActors') { $this->actorClass_fetchAllActors(); }
		elseif ($this->reqVars['methodCall'] == 'fetchActorsWithId') { $this->actorClass_fetchActorsWithId(); }
		elseif ($this->reqVars['methodCall'] == 'fetchActorCollection') { $this->actorClass_fetchActorCollection(); }
		elseif ($this->reqVars['methodCall'] == 'addActorToMovie') { $this->actorClass_addActorToMovie(); }
		elseif ($this->reqVars['methodCall'] == 'removeActorFromMovie') { $this->actorClass_removeActorFromMovie(); }
		elseif ($this->reqVars['methodCall'] == 'createActor') { $this->actorClass_createActor(); }
		elseif ($this->reqVars['methodCall'] == 'fetchMoviesWithActor') { $this->actorClass_fetchMoviesWithActor(); }
		elseif ($this->reqVars['methodCall'] == 'updateActor') { $this->actorClass_updateActor(); }
		elseif ($this->reqVars['methodCall'] == 'deleteActor') { $this->actorClass_deleteActor(); }
	}
	private function actorClass_fetchAllActors() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$collection = $actorObj->fetch_allActors();
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function actorClass_fetchActorsWithId() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$collection = $actorObj->fetchActorsWithId();
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function actorClass_fetchActorCollection() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$collection = $actorObj->fetch_actorCollection();
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function actorClass_addActorToMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$actorObj->add_actorToMovie($this->reqVars['movieId'], $this->reqVars['actorId']);
		$tmpObj = new stdClass();
		$tmpObj->response = "OK";
		$this->outPutResults = utf8_encode(json_encode($tmpObj));
	}
	private function actorClass_removeActorFromMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$actorObj->remove_actorFromMovie($this->reqVars['movieId'], $this->reqVars['actorId']);
		$tmpObj = new stdClass();
		$tmpObj->response = "OK";
		$this->outPutResults = utf8_encode(json_encode($tmpObj));
	}
	private function actorClass_createActor() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR();
		$actorObj->set_actorName($this->reqVars['actorName']);
		$actorObj->create_actor();

		$tmpObj = new stdClass();
		$tmpObj->newId = $actorObj->get_actorId();
		$tmpObj->allActors = $actorObj->fetch_allActors();
		$tmpObj->selectedActors = array();

		$actorObj->add_actorToMovie($this->reqVars['movieId'], $actorObj->actorId);

		$movieObj = new MOVIE($this->reqVars['movieId']);
		$tmpCollection = $movieObj->fetch_actorsInMovie($this->reqVars['movieId']);
		foreach ($tmpCollection as $tmpActor) {
			$tmpObj->selectedActors[] = $tmpActor->actor_id;
		}
		$this->outPutResults = utf8_encode(json_encode($tmpObj));
	}
	private function actorClass_fetchMoviesWithActor() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR($this->reqVars['actorId']);
		$collection = $actorObj->fetch_moviesWithActor($this->reqVars['actorId']);
		$this->outPutResults = utf8_encode(json_encode($collection));
	}
	private function actorClass_updateActor() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR($this->reqVars['actorId']);
		if (isset($this->reqVars['actorName'])) { $actorObj->set_actorName($this->reqVars['actorName']); }
		if (isset($this->reqVars['actorUrl'])) { $actorObj->set_actorUrl($this->reqVars['actorUrl']); }
		if (isset($this->reqVars['actorScore'])) { $actorObj->set_actorScore($this->reqVars['actorScore']); }
		if (isset($this->reqVars['isActive'])) { $actorObj->set_isActive($this->reqVars['isActive']); }
		if (isset($this->reqVars['actorComments'])) { $actorObj->set_actorComments($this->reqVars['actorComments']); }
		if (isset($this->reqVars['privateNotes'])) { $actorObj->set_privateNotes($this->reqVars['privateNotes']); }
		$actorObj->save_actor();
		$tmpObj = new stdClass();
		if (count($actorObj->errors) > 0) { $tmpObj->response = "BAD"; }
		else { $tmpObj->response = "OK"; }
		$this->outPutResults = json_encode($tmpObj);
	}
	private function actorClass_deleteActor() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$actorObj = new ACTOR($this->reqVars['actorId']);
		$actorObj->delete_actor();
		$tmpObj = new stdClass();
		$tmpObj->response = "OK";
		$this->outPutResults = json_encode($tmpObj);
	}


	// actorClass
	private function actorPictureClass() {
		if ($this->reqVars['methodCall'] == 'fetchPictureObj') { $this->actorPictureClass_fetchPictureObj(); }
	}
	private function actorPictureClass_fetchPictureObj() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$this->outputHeaders[] = 'Cache-Control: no-cache';
		$pictureObj = new ACTOR_PICTURE($this->reqVars['actorId']);
		$this->outPutResults = utf8_encode(json_encode($pictureObj->logableObj($pictureObj)));
	}


	// actorClass
	private function loggerReportClass() { if ($this->reqVars['methodCall'] == 'fetchLogs') { $this->loggerReportClass_fetchLogs(); } }
	private function loggerReportClass_fetchLogs() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$logReportObj = new LOGGER_REPORT();
		$this->outPutResults = utf8_encode(json_encode($logReportObj->get_log_entries($this->reqVars)));
	}


	// actorClass
	private function movieGenreXrefClass() { if ($this->reqVars['methodCall'] == 'updateChoices') { $this->movieGenreXrefClass_updateChoices(); } }
	private function movieGenreXrefClass_updateChoices() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$movieGenreObj = new MOVIE_GENRE_XREF($this->reqVars['movieId'], null);
		$movieGenreObj->delete_genresInMovie();
		$selectedGenres = json_decode($this->reqVars['selectedGenres']);
		foreach ($selectedGenres as $genreId) {
			$movieGenreObj->set_genreId($genreId);
			$movieGenreObj->create_xref();
		}
		$tmpObj = new stdClass();
		$tmpObj->response = "OK";
		$this->outPutResults = json_encode($tmpObj);
	}


	private function movieChapterClass() {
		if ($this->reqVars['methodCall'] == 'updateChapter') { $this->movieChapterClass_updateChapter(); }
		elseif ($this->reqVars['methodCall'] == 'createChapter') { $this->movieChapterClass_createChapter(); }
		elseif ($this->reqVars['methodCall'] == 'deleteChapter') { $this->movieChapterClass_deleteChapter(); }
		elseif ($this->reqVars['methodCall'] == 'fetchChapterDataForMovie') { $this->movieChapterClass_fetchChapterDataForMovie(); }
	}
	private function movieChapterClass_updateChapter() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$chapterObj = new MOVIE_CHAPTERS($this->reqVars['chapterId']);
		$chapterObj->set_movieId($this->reqVars['movieId']);
		$chapterObj->set_startHour($this->reqVars['startHour']);
		$chapterObj->set_startMinute($this->reqVars['startminute']);
		$chapterObj->set_startSecond($this->reqVars['startSecond']);
		$chapterObj->set_startMicro($this->reqVars['startMicro']);
		$chapterObj->set_description($this->reqVars['description']);
		$chapterObj->update_chapter();
		$this->outPutResults = json_encode($chapterObj->fetch_chapterDataForMovie($this->reqVars['movieId']));
	}
	private function movieChapterClass_createChapter() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$chapterObj = new MOVIE_CHAPTERS();
		$chapterObj->set_movieId($this->reqVars['movieId']);
		$chapterObj->set_startHour($this->reqVars['startHour']);
		$chapterObj->set_startMinute($this->reqVars['startminute']);
		$chapterObj->set_startSecond($this->reqVars['startSecond']);
		$chapterObj->set_startMicro($this->reqVars['startMicro']);
		$chapterObj->set_description($this->reqVars['description']);
		$chapterObj->create_chapter();
		$this->outPutResults = json_encode($chapterObj->fetch_chapterDataForMovie($this->reqVars['movieId']));
	}
	private function movieChapterClass_deleteChapter() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$chapterObj = new MOVIE_CHAPTERS($this->reqVars['chapterId']);
		$chapterObj->delete_chapter();
		$this->outPutResults = json_encode($chapterObj->fetch_chapterDataForMovie($this->reqVars['movieId']));
	}
	private function movieChapterClass_fetchChapterDataForMovie() {
		$this->outputHeaders[] = 'Content-type: application/json; charset=UTF-8';
		$chapterObj = new MOVIE_CHAPTERS();
		$collection = $chapterObj->fetch_chapterDataForMovie($this->reqVars['movieId']);
		$this->outPutResults = json_encode($collection);
	}
}

if ($_SERVER['REQUEST_METHOD'] == "POST") { $genObj = new AJAX_REQUESTS($_POST); }
else { $genObj = new AJAX_REQUESTS($_GET); }
if (count($genObj->outputHeaders) > 0) { foreach ($genObj->outputHeaders as $header) { header($header); } }
if ($genObj->outPutResults != '') { print trim($genObj->outPutResults); }
?>