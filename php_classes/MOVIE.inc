<?php

class MOVIE extends MOVIETRACKER {

	protected $movieId = 0;
	protected $title = '';
	protected $sortTitle = '';
	protected $cleanTitle = '';

	protected $studio = 4;
	protected $studioName = '';
	protected $studioShortName = '';
	protected $moviePath = '';
	protected $directoryPath = '';

	private $studioChoices = array();

	protected $collection = null;

	protected $rating = 6;
	private $ratingChoices = array();

	protected $format = 4;
	private $formatChoices = array();

	protected $formatName = 'HD 1080p';

	protected $genre = 3;
	protected $genreCollection = array();
	private $genreChoices = array();

	protected $releaseDate = '';
	protected $addedDate = '';
	protected $descShort = '';
	protected $descLong = '';
	protected $movieScore = 0;
	private $movieScoreChoices = array('0','1','2','3','4','5','6','7','8','9','10');

	protected $originalUrl = '';
	protected $isActive = 'Y';

	protected $privateNotes = '';

	protected $hasChapters = 'N';



	/**
	 * Constructor
	 * @param int $movieId
	 * @return void
	 */
	public function __construct($movieId = null) {
		parent::__construct();
		$this->releaseDate = date('Y-m-d');
		$this->add_to_excludeFromExport(array('oldNewDiffString', 'studioChoices', 'ratingChoices', 'formatChoices', 'genreChoices'));
		$this->fetch_allStudios();
		$this->fetch_allRatings();
		$this->fetch_allFormats();
		$this->fetch_allGenres();
		if (is_numeric($movieId) && $movieId > 0) {
			$statement = $this->dbConnection->prepare("select
			m.movie_id,
			m.title,
			m.sort_title,
			m.studio,
			s.studio_name,
			s.studio_short_name,
			s.movie_path,
			s.directory_path,
			m.collection,
			m.rating,
			r.rating_name,
			m.format,
			f.format_name,
			m.release_date,
			date_format(m.added_date, '%Y-%m-%d') as added_date,
			m.desc_short,
			m.desc_long,
			m.movie_score,
			m.original_url,
			m.is_active,
			m.private_notes,
			(case
				when (select count(*) from movie_chapters where movie_id = m.movie_id) > 0
				then 'Y'
				else 'N'
			end) as has_chapters
			from
				movie as m,
				movie_format as f,
				movie_studio as s,
				movie_rating as r
			where
				m.movie_id = :movieId
				and m.format = f.format_id
				and m.studio = s.studio_id
				and m.rating = r.rating_id");
			$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if (is_object($statement) && get_class($statement) == 'PDOStatement') {
				while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
					$this->set_movieId($row->movie_id);
					$this->set_title($row->title);
					$this->set_sortTitle($row->sort_title);
					$this->set_studio($row->studio);

					$this->set_studioName($row->studio_name);
					$this->set_studioShortName($row->studio_short_name);
					$this->set_moviePath($row->movie_path);
					$this->set_directoryPath($row->directory_path);

					$this->set_collection($row->collection);
					$this->set_rating($row->rating);
					$this->set_format($row->format);
					//$this->set_formatName($row->format_name);
					$this->formatName = $row->format_name;
					$this->set_releaseDate($row->release_date);
					$this->set_addedDate($row->added_date);
					$this->set_descShort($row->desc_short);
					$this->set_descLong($row->desc_long);
					$this->set_movieScore($row->movie_score);
					$this->set_originalUrl($row->original_url);
					$this->set_isActive($row->is_active);
					$this->set_privateNotes($row->private_notes);
					$this->hasChapters = $row->has_chapters;
				}
				$this->fetch_genresInMovie($this->movieId);
			}
		}
	}


	/**
	 * Saves the object back to the database
	 * @return void
	 */
	public function save_movie() {

		$this->compare_old_new_objects($this->movieId);
		$this->errors = array();
		$statement = $this->dbConnection->prepare("update movie set
		title = :title,
		sort_title = :sortTitle,
		studio = :studio,
		collection = :collection,
		rating = :rating,
		format = :format,
		release_date = :releaseDate,
		desc_short = :descShort,
		desc_long = :descLong,
		movie_score = :movieScore,
		original_url = :originalUrl,
		is_active = :isActive,
		private_notes = :privateNotes
		where movie_id = :movieId");
		$statement->bindParam(':title', $this->title, PDO::PARAM_STR, strlen($this->title));
		$statement->bindParam(':sortTitle', $this->sortTitle, PDO::PARAM_STR, strlen($this->sortTitle));
		$statement->bindParam(':studio', $this->studio, PDO::PARAM_INT);
		$statement->bindParam(':collection', $this->collection, PDO::PARAM_STR, strlen($this->collection));
		$statement->bindParam(':rating', $this->rating, PDO::PARAM_INT);
		$statement->bindParam(':format', $this->format, PDO::PARAM_INT);
		$statement->bindParam(':releaseDate', $this->releaseDate);
		$statement->bindParam(':descShort', $this->descShort, PDO::PARAM_STR, strlen($this->descShort));
		$statement->bindParam(':descLong', $this->descLong, PDO::PARAM_STR, strlen($this->descLong));
		$statement->bindParam(':movieScore', $this->movieScore, PDO::PARAM_INT);
		$statement->bindParam(':originalUrl', $this->originalUrl, PDO::PARAM_STR, strlen($this->originalUrl));
		$statement->bindParam(':isActive', $this->isActive);
		$statement->bindParam(':privateNotes', $this->privateNotes, PDO::PARAM_STR, strlen($this->privateNotes));
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$tmpObj = new stdClass();
			$tmpObj->errorMsg = $e->getMessage();
			$this->errors[] = $tmpObj;

			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1 && $this->oldNewDiffString != '') {
			$this->errors = array();
			//$this->addToLog('EDIT', "Movie \"".$this->title." (".$this->movieId.")\" Updated:\n".$this->oldNewDiffString);
		}
		return $this->movieId;
	}


	/**
	 * Inserts the object into the database as a new entry
	 * @return void
	 */
	public function create_movie() {

		$tmpMovieId = $this->test_forExistingMovie();
		if ($tmpMovieId > 0) {
			$originalMovie = $this->logableObj($this);
			self::__construct($tmpMovieId);
			$this->addToLog('EDIT', "Found Existing Movie Obj:\n".print_r($this->logableObj($this), true));
			$this->sortTitle = $originalMovie->sortTitle;
			$this->studio = $originalMovie->studio;
			$this->collection = $originalMovie->collection;
			$this->rating = $originalMovie->rating;
			$this->format = $originalMovie->format;
			$this->releaseDate = $originalMovie->releaseDate;
			$this->descShort = $originalMovie->descShort;
			$this->descLong = $originalMovie->descLong;
			$this->movieScore = $originalMovie->movieScore;
			$this->originalUrl = $originalMovie->originalUrl;
			$this->privateNotes = $originalMovie->privateNotes;

			$this->isActive = 'Y';
			if ($this->sortTitle == '') { $this->sortTitle = $this->title; }
			$this->save_movie();
		}
		else {
			if ($this->sortTitle == '') { $this->sortTitle = $this->title; }
			$statement = $this->dbConnection->prepare("insert into movie (
			title,
			sort_title,
			studio,
			collection,
			rating,
			format,
			release_date,
			desc_short,
			desc_long,
			movie_score,
			original_url,
			is_active,
			added_date,
			private_notes
			) values (
			:title,
			:sortTitle,
			:studio,
			:collection,
			:rating,
			:format,
			:releaseDate,
			:descShort,
			:descLong,
			:movieScore,
			:originalUrl,
			:isActive,
			now(),
			:privateNotes)");
			$statement->bindParam(':title', $this->title, PDO::PARAM_STR, strlen($this->title));
			$statement->bindParam(':sortTitle', $this->sortTitle, PDO::PARAM_STR, strlen($this->sortTitle));
			$statement->bindParam(':studio', $this->studio, PDO::PARAM_INT);
			$statement->bindParam(':collection', $this->collection, PDO::PARAM_STR, strlen($this->collection));
			$statement->bindParam(':rating', $this->rating, PDO::PARAM_INT);
			$statement->bindParam(':format', $this->format, PDO::PARAM_INT);
			$statement->bindParam(':releaseDate', $this->releaseDate);
			$statement->bindParam(':descShort', $this->descShort, PDO::PARAM_STR, strlen($this->descShort));
			$statement->bindParam(':descLong', $this->descLong, PDO::PARAM_STR, strlen($this->descLong));
			$statement->bindParam(':movieScore', $this->movieScore, PDO::PARAM_INT);
			$statement->bindParam(':originalUrl', $this->originalUrl, PDO::PARAM_STR, strlen($this->originalUrl));
			$statement->bindParam(':privateNotes', $this->privateNotes, PDO::PARAM_STR, strlen($this->privateNotes));
			$statement->bindParam(':isActive', $this->isActive);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if ($statement->rowCount() == 1) {
				$movieId = $this->dbConnection->lastInsertId();
				$this->set_movieId($movieId);
				$this->addToLog('EDIT', "Created Movie Obj:\n".print_r($this->logableObj($this), true));
			}
		}
		return $this->movieId;
	}

	public function delete_movie() {

		$posterObj = new MOVIE_POSTER($this->movieId);
		$posterObj->delete_poster();

		$statement = $this->dbConnection->prepare("delete from movie where movie_id = :movieId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Deleted Movie Obj:\n".print_r($this->logableObj($this), true));
		}
	}

	private function test_forExistingMovie() {
		$movieId = 0;
		$statement = $this->dbConnection->prepare("select movie_id from movie where title = :title");
		$statement->bindParam(':title', $this->title, PDO::PARAM_STR, strlen($this->title));
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {	$movieId = $row->movie_id; }
		}
		return $movieId;
	}

	public function fetch_allMovies() {

		$collection = array();

		$statement = $this->dbConnection->query("select
		t.movie_id,
		t.title,
		t.sort_title,
		t.movie_score,
		t.studio,
		t.poster_name,
		t.poster_width,
		t.poster_height,
		s.studio_name,
		s.studio_short_name
		from (
			select
			m.movie_id,
			m.title,
			m.sort_title,
			m.movie_score,
			m.studio,
			p.poster_name,
			p.poster_width,
			p.poster_height
			from movie as m
			left outer join movie_poster as p on m.movie_id = p.movie_id
			where m.is_active = 'Y'
		) as t, movie_studio as s
		where t.studio = s.studio_id
		order by t.sort_title, t.title");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}

	public function fetch_genresInMovie($movieId) {
		$this->genreCollection = array();
		$statement = $this->dbConnection->prepare("select
		a.genre_id,
		a.genre_name
		from
			movie_genre as a,
			movie_genre_xref as x
		where
			x.movie_id = :movieId
			and x.genre_id = a.genre_id
			and a.is_active = 'Y'
		order by a.genre_name");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$this->genreCollection[$row->genre_id] = $row->genre_name;
			}
		}
		return $this->genreCollection;
	}

	public function fetch_actorsInMovie($movieId) {
		$collection = array();
		$statement = $this->dbConnection->prepare("select
		a.actor_id,
		a.actor_name,
		a.actor_score
		from
			actor as a,
			movie_actor_xref as x
		where
			x.movie_id = :movieId
			and x.actor_id = a.actor_id
			and a.is_active = 'Y'
		order by a.actor_name");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}

	private function fetch_actorsInMovieAsString($movieId) {
		$actors = '';
		$statement = $this->dbConnection->prepare("select
		GROUP_CONCAT(a.actor_name order by a.actor_name SEPARATOR ', ') as actor_string
		from
			actor as a,
			movie_actor_xref as x
		where
			x.movie_id = :movieId
			and x.actor_id = a.actor_id
			and a.is_active = 'Y'");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$actors = $row->actor_string;
			}
		}
		return (string) $actors;
	}

	private function fetch_allStudios() {
		$this->studioChoices = array();
		$statement = $this->dbConnection->query("select studio_id, studio_name from movie_studio where is_active = 'Y' order by sort_order");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$this->studioChoices[$row->studio_id] = $row->studio_name;
			}
		}
	}

	private function fetch_allRatings() {
		$this->ratingChoices = array();
		$statement = $this->dbConnection->query("select rating_id, rating_name from movie_rating where is_active = 'Y' order by sort_order");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$this->ratingChoices[$row->rating_id] = $row->rating_name;
			}
		}
	}

	private function fetch_allFormats() {
		$this->formatChoices = array();
		$statement = $this->dbConnection->query("select format_id, format_name from movie_format where is_active = 'Y' order by sort_order");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$this->formatChoices[$row->format_id] = $row->format_name;
			}
		}
	}

	private function fetch_allGenres() {
		$this->genreChoices = array();
		//$statement = $this->dbConnection->query("select genre_id, genre_name from movie_genre where is_active = 'Y' order by sort_order");
		$statement = $this->dbConnection->query("select genre_id, genre_name, opt_group from movie_genre where is_active = 'Y' order by opt_group, sort_order");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$this->genreChoices[$row->genre_id] = $row;
			}
		}
	}

	public function fetch_movieCollection() {
		$collection = array();
		$statement = $this->dbConnection->query("select
		m.movie_id,
		m.title,
		m.sort_title,
		m.studio,
		s.studio_name,
		s.studio_short_name,
		m.release_date,
		date_format(m.added_date, '%Y-%m-%d') as added_date,
		m.movie_score,
		f.format_name as resolution,
		m.is_active,
		GROUP_CONCAT(g.genre_name order by g.sort_order, g.genre_name SEPARATOR ', ') as genre
		from
			movie_genre as g,
			movie_genre_xref as x,
			movie_format as f,
			movie as m,
			movie_studio as s
		where
			m.movie_id = x.movie_id
			and m.is_active = 'Y'
			and m.studio = s.studio_id
			and x.genre_id = g.genre_id
			and f.format_id = m.format
		group by m.movie_id
		order by m.sort_title, m.title");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$row->cleanTitle = preg_replace('/[^a-zA-Z0-9&\s-]/', '', $row->title);
				$row->actors = $this->fetch_actorsInMovieAsString($row->movie_id);
				//$row->actorsCollection = $this->fetch_actorsInMovie($row->movie_id);
				$collection[] = $row;
			}
		}
		return $collection;
	}

	public function fetch_movieCollection_old() {
		$collection = array();
		$statement = $this->dbConnection->query("select
		m.movie_id,
		m.title,
		m.studio,
		/*m.collection,*/
		/*m.rating,*/
		/*m.format,*/
		m.release_date,
		/*m.desc_short,*/
		/*m.desc_long,*/
		m.movie_score,
		/*m.original_url,*/
		m.is_active,
		GROUP_CONCAT(g.genre_name order by g.genre_name SEPARATOR ', ') as genre,
		p.poster_name,
		p.poster_width,
		p.poster_height
		from
			movie_genre as g,
			movie_genre_xref as x,
			movie as m

		left outer join movie_poster as p on m.movie_id = p.movie_id
		where
			m.movie_id = x.movie_id
			and m.is_active = 'Y'
			and x.genre_id = g.genre_id
		group by m.movie_id
		order by m.sort_title, m.title");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$row->actors = $this->fetch_actorsInMovieAsString($row->movie_id);
				$collection[] = $row;
			}
		}
		return $collection;
	}

	public function make_studioChoiceMenu($studioId = 0) {
		$rs = '';
		foreach ($this->studioChoices as $id => $studio) {
			$selected = ($id == $studioId) ? " selected='selected'" : "";
			$rs .= "<option value='".$id."'".$selected.">".$studio."</option>\n";
		}
		return $rs;
	}

	public function make_ratingChoiceMenu() {
		$rs = '';
		foreach ($this->ratingChoices as $id => $rating) {
			$selected = ($id == $this->rating) ? " selected='selected'" : "";
			$rs .= "<option value='".$id."'".$selected.">".$rating."</option>\n";
		}
		return $rs;
	}

	public function make_formatChoiceMenu() {
		$rs = '';
		foreach ($this->formatChoices as $id => $format) {
			$selected = ($id == $this->format) ? " selected='selected'" : "";
			$rs .= "<option value='".$id."'".$selected.">".$format."</option>\n";
		}
		return $rs;
	}

	public function make_genreChoiceMenu() {
		$rs = '';
		$rowNum = 0;
		$currentOptGroup = '';
		foreach ($this->genreChoices as $genreId => $genreObj) {
			if ($rowNum === 0) {
				$currentOptGroup = $genreObj->opt_group;
				$rs .= "<optgroup label='".$genreObj->opt_group."'>\n";
			}
			elseif ($currentOptGroup != $genreObj->opt_group) {
				$currentOptGroup = $genreObj->opt_group;
				$rs .= "</optgroup>\n";
				$rs .= "<optgroup label='".$genreObj->opt_group."'>\n";
			}
			$selected = (in_array($genreObj->genre_name, $this->genreCollection)) ? " selected='selected'" : "";
			$rs .= "<option value='".$genreId."'".$selected.">".$genreObj->genre_name."</option>\n";
			$rowNum++;
		}
		$rs .= "</optgroup>\n";
		return $rs;
	}

	public function make_movieScoreChoiceMenu() {
		$rs = '';
		foreach ($this->movieScoreChoices as $movieScore) {
			$selected = ($movieScore == $this->movieScore) ? " selected='selected'" : "";
			$rs .= "<option value='".$movieScore."'".$selected.">".$movieScore."</option>\n";
		}
		return $rs;
	}

	public function build_movieSelectList($selected = '') {
		$rs = '';
		$statement = $this->dbConnection->query("select
		movie_id,
		title
		from movie
		where is_active = 'Y'
		order by sort_title, title");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$isSelected  = ($selected == $row->movie_id) ? " selected='selected'" : "";
				$rs .= "<option value='".$row->movie_id."'>".$row->title."</option>\n";
			}
		}
		return (string) $rs;
	}


	public function set_movieId($inVal) {
		$this->movieId = intval($inVal);
		$this->logSettings['movieId'] = $this->movieId;
		MOVIETRACKER::setCurrentMovieId($this->movieId);
	}
	public function get_movieId() { return intval($this->movieId); }

	public function set_title($inVal) {
		$this->title = trim(strip_tags($inVal));
		$this->cleanTitle = preg_replace('/[^a-zA-Z0-9&\s-]/', '', $this->title);
	}
	public function get_title() { return $this->title; }
	public function get_cleanTitle() { return $this->cleanTitle; }

	public function set_sortTitle($inVal) { $this->sortTitle = trim(strip_tags($inVal)); }
	public function get_sortTitle() { return $this->sortTitle; }

	public function set_studio($inVal) { $this->studio = (array_key_exists($inVal, $this->studioChoices)) ? $inVal : 1; }
	public function get_studio() { return $this->studio; }


	public function set_studioName($inVal) { $this->studioName = $inVal; }
	public function set_studioShortName($inVal) { $this->studioShortName = $inVal; }
	public function set_moviePath($inVal) { $this->moviePath = $inVal; }
	public function set_directoryPath($inVal) { $this->directoryPath = $inVal; }


	public function set_collection($inVal) { $this->collection = trim(strip_tags($inVal)); }
	public function get_collection() { return $this->collection; }

	public function set_rating($inVal) { $this->rating = (array_key_exists($inVal, $this->ratingChoices)) ? $inVal : 6; }
	public function get_rating() { return $this->rating; }

	public function set_format($inVal) { $this->format = (array_key_exists($inVal, $this->formatChoices)) ? $inVal : 4; }
	public function get_format() { return $this->format; }

	public function set_formatName($inVal) { $this->formatName = $inValue; }
	public function get_formatName($inVal) { return $this->formatName; }

	public function set_releaseDate($inVal) { $this->releaseDate = $inVal; }
	public function get_releaseDate() { return $this->releaseDate; }

	public function set_addedDate($inVal) { $this->addedDate = $inVal; }
	public function get_addedDate() { return $this->addedDate; }

	public function set_descShort($inVal) { $this->descShort = trim(strip_tags($inVal)); }
	public function get_descShort() { return $this->descShort; }

	public function set_descLong($inVal) { $this->descLong = trim(strip_tags($inVal)); }
	public function get_descLong() { return $this->descLong; }

	public function set_movieScore($inVal) { $this->movieScore = (in_array($inVal, $this->movieScoreChoices)) ? $inVal : 0; }
	public function get_movieScore() { return intval($this->movieScore); }

	public function set_originalUrl($inVal) { $this->originalUrl = trim(strip_tags($inVal)); }
	public function get_originalUrl() { return $this->originalUrl; }

	public function set_isActive($inVal) { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
	public function get_isActive() { return $this->isActive; }

	public function set_privateNotes($inVal) { $this->privateNotes = trim(strip_tags($inVal)); }
	public function get_privateNotes() { return $this->privateNotes; }

	public function set_hasChapters($inVal) { $this->hasChapters = ($inVal == 'Y') ? 'Y' : 'N'; }
	public function get_hasChapters() { return $this->hasChapters; }


	public function __set($inName, $inVal) {
		if ($inName == 'movieId') {
			$this->movieId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'title') {
			$this->title = trim(strip_tags($inVal));
			$this->cleanTitle = preg_replace('/[^a-zA-Z0-9&\s-]/', '', $this->title);
		}
		elseif ($inName == 'sortTitle') { $this->sortTitle = trim(strip_tags($inVal)); }
		elseif ($inName == 'studio') {
			if (intval($inVal) === 0) { $this->studio = 0; }
			else { $this->studio = (in_array($inVal, $this->studioChoices)) ? $inVal : 4; }
		}
		elseif ($inName == 'collection') { $this->collection = trim(strip_tags($inVal)); }
		elseif ($inName == 'rating') { $this->rating = (array_key_exists($inVal, $this->ratingChoices)) ? $inVal : 6; }
		elseif ($inName == 'format') { $this->format = (array_key_exists($inVal, $this->formatChoices)) ? $inVal : 4; }
		elseif ($inName == 'formatName') { $this->formatName = $inVal; }
		elseif ($inName == 'releaseDate') { $this->releaseDate = $inVal; }
		elseif ($inName == 'addedDate') { $this->addedDate = $inVal; }
		elseif ($inName == 'descShort') { $this->descShort = trim(strip_tags($inVal)); }
		elseif ($inName == 'descLong') { $this->descLong = trim(strip_tags($inVal)); }
		elseif ($inName == 'movieScore') { $this->movieScore = (in_array($inVal, $this->movieScoreChoices)) ? $inVal : '0'; }
		elseif ($inName == 'originalUrl') { $this->originalUrl = trim(strip_tags($inVal)); }
		elseif ($inName == 'isActive') { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
		elseif ($inName == 'privateNotes') { $this->privateNotes = trim(strip_tags($inVal)); }
		elseif ($inName == 'hasChapters') { $this->hasChapters = ($inVal == 'Y') ? 'Y' : 'N'; }
		else { $this->$inName = $inVal; }
	}


	public function __get($inName) {

		$outVal = null;

		if ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'title') { $outVal = (string) trim($this->title); }
		elseif ($inName == 'cleanTitle') { $outVal = (string) trim($this->cleanTitle); }
		elseif ($inName == 'sortTitle') { $outVal = (string) trim($this->sortTitle); }
		elseif ($inName == 'studio') { $outVal = (integer) intval($this->studio); }

		elseif ($inName == 'studioName') { $outVal = (string) trim($this->studioName); }
		elseif ($inName == 'studioShortName') { $outVal = (string) trim($this->studioShortName); }
		elseif ($inName == 'moviePath') { $outVal = (is_null($this->moviePath)) ? null : (string) trim($this->moviePath); }
		elseif ($inName == 'directoryPath') { $outVal = (is_null($this->directoryPath)) ? null : (string) trim($this->directoryPath); }

		elseif ($inName == 'collection') { $outVal = (is_null($this->collection)) ? null : (string) trim($this->collection); }
		elseif ($inName == 'rating') { $outVal = (integer) $this->rating; }
		elseif ($inName == 'format') { $outVal = (integer) $this->format; }
		elseif ($inName == 'formatName') { $outVal = (string) $this->formatName; }
		elseif ($inName == 'releaseDate') { $outVal = (string) $this->releaseDate; }
		elseif ($inName == 'addedDate') { $outVal = (string) $this->addedDate; }
		elseif ($inName == 'descShort') { $outVal = (string) trim($this->descShort); }
		elseif ($inName == 'descLong') { $outVal = (string) trim($this->descLong); }
		elseif ($inName == 'movieScore') { $outVal = (integer) intval($this->movieScore); }
		elseif ($inName == 'originalUrl') { $outVal = (string) trim($this->originalUrl); }
		elseif ($inName == 'isActive') { $outVal = (string) $this->isActive; }
		elseif ($inName == 'privateNotes') { $outVal = (string) trim($this->privateNotes); }
		elseif ($inName == 'hasChapters') { $outVal = (string) $this->hasChapters; }
		return $outVal;
	}


	public function __isset($inName) {

		$outVal = false;

		if ($inName == 'movieId') { $outVal = (intval($this->movieId) > -1) ? true : false; }
		elseif ($inName == 'title') { $outVal = (trim($this->title) != '') ? true : false; }
		elseif ($inName == 'cleanTitle') { $outVal = (trim($this->cleanTitle) != '') ? true : false; }
		elseif ($inName == 'sortTitle') { $outVal = (trim($this->sortTitle) != '') ? true : false; }
		elseif ($inName == 'studio') { $outVal = (array_key_exists($this->studio, $this->studioChoices)) ? true : false; }

		elseif ($inName == 'studioName') { $outVal = (trim($this->studioName) != '') ? true : false; }
		elseif ($inName == 'studioShortName') { $outVal = (trim($this->studioShortName) != '') ? true : false; }
		elseif ($inName == 'moviePath') { $outVal = (trim($this->moviePath) != '') ? true : false; }
		elseif ($inName == 'directoryPath') { $outVal = (trim($this->directoryPath) != '') ? true : false; }

		elseif ($inName == 'collection') { $outVal = (trim($this->collection) != '') ? true : false; }
		elseif ($inName == 'rating') { $outVal = (array_key_exists($this->rating, $this->ratingChoices)) ? true : false; }
		elseif ($inName == 'format') { $outVal = (array_key_exists($this->format, $this->formatChoices)) ? true : false; }
		elseif ($inName == 'formatName') { $outVal = ($this->formatName != '') ? true : false; }
		elseif ($inName == 'releaseDate') { $outVal = ($this->releaseDate != '') ? true : false; }
		elseif ($inName == 'addedDate') { $outVal = ($this->addedDate != '') ? true : false; }
		elseif ($inName == 'descShort') { $outVal = (trim($this->descShort) != '') ? true : false; }
		elseif ($inName == 'descLong') { $outVal = (trim($this->descLong) != '') ? true : false; }
		elseif ($inName == 'movieScore') { $outVal = (in_array($this->movieScore, $this->movieScoreChoices)) ? true : false; }
		elseif ($inName == 'originalUrl') { $outVal = (trim($this->originalUrl) != '') ? true : false; }
		elseif ($inName == 'isActive') { $outVal = true; }
		elseif ($inName == 'privateNotes') { $outVal = (trim($this->privateNotes) != '') ? true : false; }
		elseif ($inName == 'hasChapters') { $outVal = true; }
		return $outVal;
	}


	public function __unset($inName) {
		if ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'title') { $this->title = ''; }
		elseif ($inName == 'cleanTitle') { $this->cleanTitle = ''; }
		elseif ($inName == 'sortTitle') { $this->sortTitle = ''; }
		elseif ($inName == 'studio') { $this->studio = 4; }
		elseif ($inName == 'collection') { $this->collection = null; }
		elseif ($inName == 'rating') { $this->rating = 6; }
		elseif ($inName == 'format') { $this->format = 4; }
		elseif ($inName == 'releaseDate') { $this->releaseDate = ''; }
		elseif ($inName == 'releaseDate') { $this->releaseDate = ''; }
		elseif ($inName == 'formatName') { $this->formatName = ''; }
		elseif ($inName == 'descShort') { $this->descShort = ''; }
		elseif ($inName == 'descLong') { $this->descLong = ''; }
		elseif ($inName == 'movieScore') { $this->movieScore = 0; }
		elseif ($inName == 'originalUrl') { $this->originalUrl = ''; }
		elseif ($inName == 'isActive') { $this->isActive = 'Y'; }
		elseif ($inName == 'privateNotes') { $this->privateNotes = ''; }
		elseif ($inName == 'hasChapters') { $this->isActive = 'N'; }
	}


	public function __destruct() { parent::__destruct(); }

}
?>