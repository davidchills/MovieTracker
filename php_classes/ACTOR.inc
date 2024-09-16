<?php

class ACTOR extends MOVIETRACKER {

	protected $actorId = 0;
	protected $actorName = '';
	protected $actorScore = 0;
	protected $actorUrl = '';
	protected $actorComments = '';
	protected $isActive = 'Y';
	protected $hasPicture = 'N';
	protected $privateNotes = '';
	private $actorScoreChoices = array('0','1','2','3','4','5','6','7','8','9','10');

	/**
	 * Constructor
	 * @param int $actorId
	 * @return void
	 */
	public function __construct($actorId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString','hasPicture','actorScoreChoices'));
		if (is_numeric($actorId)) {
			$statement = $this->dbConnection->prepare("select * from (
			select
			a.actor_id,
			a.actor_name,
			a.actor_score,
			a.actor_url,
			a.actor_comments,
			a.is_active,
			a.private_notes,
			(case
				when (select count(*) from actor_picture as ap where ap.actor_id = a.actor_id) = 1
				then 'Y'
				else 'N'
			end) as has_picture
			from actor as a
			where a.actor_id = :actorId
			) as boq");
			$statement->bindParam(':actorId', $actorId, PDO::PARAM_INT);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if (is_object($statement) && get_class($statement) == 'PDOStatement') {
				while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
					$this->set_actorId($row->actor_id);
					$this->set_actorName($row->actor_name);
					$this->set_actorScore($row->actor_score);
					$this->set_actorUrl($row->actor_url);
					$this->set_actorComments($row->actor_comments);
					$this->set_isActive($row->is_active);
					$this->set_hasPicture($row->has_picture);
					$this->set_privateNotes($row->private_notes);
				}
			}
		}
	}


	/**
	 * Saves the object back to the database
	 * @return void
	 */
	public function save_actor() {

		$this->compare_old_new_objects($this->actorId);
		$this->errors = array();
		$statement = $this->dbConnection->prepare("update actor set
		actor_name = :actorName,
		actor_score = :actorScore,
		actor_url = :actorUrl,
		actor_comments = :actorComments,
		is_active = :isActive,
		private_notes = :privateNotes
		where actor_id = :actorId");
		$statement->bindParam(':actorName', $this->actorName, PDO::PARAM_STR, strlen($this->actorName));
		$statement->bindParam(':actorScore', $this->actorScore, PDO::PARAM_INT);
		$statement->bindParam(':actorUrl', $this->actorUrl, PDO::PARAM_STR, strlen($this->actorUrl));
		$statement->bindParam(':actorComments', $this->actorComments, PDO::PARAM_STR, strlen($this->actorComments));
		$statement->bindParam(':isActive', $this->isActive);
		$statement->bindParam(':privateNotes', $this->privateNotes, PDO::PARAM_STR, strlen($this->privateNotes));
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
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
			//$this->addToLog('EDIT', "Actor \"".$this->actorName." (".$this->actorId.")\" Updated:\n".$this->oldNewDiffString);
		}
		return $this->actorId;
	}


	/**
	 * Inserts the object into the database as a new entry
	 * @return void
	 */
	public function create_actor() {

		$tmpActorId = $this->test_forExistingActor();
		if ($tmpActorId > 0) {
			$originalActor = $this->logableObj($this);
			self::__construct($tmpActorId);
			$this->addToLog('EDIT', "Found Existing Actor Obj:\n".print_r($this->logableObj($this), true));
			$this->actorScore = $originalActor->actorScore;
			$this->actorUrl = $originalActor->actorUrl;
			$this->actorComments = $originalActor->actorComments;
			$this->isActive = 'Y';
			$this->privateNotes = $originalActor->privateNotes;
			$this->save_actor();
		}
		else {
			$statement = $this->dbConnection->prepare("insert into actor (
			actor_name,
			actor_score,
			actor_url,
			actor_comments,
			is_active,
			added_date,
			private_notes
			) values (
			:actorName,
			:actorScore,
			:actorUrl,
			:actorComments,
			:isActive,
			now(),
			:privateNotes)");
			$statement->bindParam(':actorName', $this->actorName, PDO::PARAM_STR, strlen($this->actorName));
			$statement->bindParam(':actorScore', $this->actorScore, PDO::PARAM_INT);
			$statement->bindParam(':actorUrl', $this->actorUrl, PDO::PARAM_STR, strlen($this->actorUrl));
			$statement->bindParam(':actorComments', $this->actorComments, PDO::PARAM_STR, strlen($this->actorComments));
			$statement->bindParam(':isActive', $this->isActive);
			$statement->bindParam(':privateNotes', $this->privateNotes, PDO::PARAM_STR, strlen($this->privateNotes));
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if ($statement->rowCount() == 1) {
				$actorId = $this->dbConnection->lastInsertId();
				$this->set_actorId($actorId);
				$this->addToLog('EDIT', "Created Actor Obj:\n".print_r($this->logableObj($this), true));
			}
		}
	}


	private function test_forExistingActor() {
		$actorId = 0;
		$statement = $this->dbConnection->prepare("select actor_id from actor where actor_name = :actorName");
		$statement->bindParam(':actorName', $this->actorName, PDO::PARAM_STR, strlen($this->actorName));
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {	$actorId = $row->actor_id; }
		}
		return $actorId;
	}


	public function delete_actor() {

		$pictureObj = new ACTOR_PICTURE($this->actorId);
		$pictureObj->delete_picture();

		$statement = $this->dbConnection->prepare("delete from actor where actor_id = :actorId");
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Deleted Actor Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function fetch_allActors() {

		$collection = array();

		$statement = $this->dbConnection->query("select * from actor where is_active = 'Y' order by actor_name");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}


	public function fetchActorsWithId() {

		$collection = array();

		$statement = $this->dbConnection->query("select actor_id, actor_name from actor where is_active = 'Y' order by actor_name");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}


	public function fetch_moviesWithActor($actorId) {
		$collection = array();
		$statement = $this->dbConnection->prepare("select
		m.movie_id,
		m.title,
		m.movie_score,
		m.studio,
		p.poster_name,
		p.poster_width,
		p.poster_height
		from
			movie as m,
			movie_actor_xref as x
			left outer join movie_poster as p on x.movie_id = p.movie_id
		where
		x.actor_id = :actorId
		and x.movie_id = m.movie_id
		and m.is_active = 'Y'
		order by m.sort_title, m.title");
		$statement->bindParam(':actorId', $actorId, PDO::PARAM_INT);
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


	public function add_actorToMovie($movieId, $actorId) {
		self::__construct($actorId);
		$this->logSettings['movieId'] = $movieId;
		MOVIETRACKER::setCurrentMovieId($movieId);
		$statement = $this->dbConnection->prepare("insert into movie_actor_xref (movie_id, actor_id) values (:movieId, :actorId)");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		$statement->bindParam(':actorId', $actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Added Actor ".$actorId." to Movie ".$movieId.":\n".print_r($this->logableObj($this), true));
		}
	}


	public function remove_actorFromMovie($movieId, $actorId) {
		self::__construct($actorId);
		$this->logSettings['movieId'] = $movieId;
		MOVIETRACKER::setCurrentMovieId($movieId);
		$statement = $this->dbConnection->prepare("delete from movie_actor_xref where movie_id = :movieId and actor_id = :actorId");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		$statement->bindParam(':actorId', $actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Removed Actor ".$actorId." from Movie ".$movieId.":\n".print_r($this->logableObj($this), true));
		}
	}


	public function fetch_actorCollection() {
		$collection = array();
		$statement = $this->dbConnection->query("select * from (
		select
		a.actor_id,
		a.actor_name,
		a.actor_score,
		a.is_active,
		(case
			when (select count(*) from actor_picture as ap where ap.actor_id = a.actor_id) = 1
			then 'Y'
			else 'N'
		end) as has_picture,
		(select count(*) from movie_actor_xref where actor_id = a.actor_id) as number_of_movies
		from actor as a
		) as boq
		where is_active = 'Y'
		order by actor_name");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}


	public function make_actorScoreChoiceMenu() {
		$rs = '';
		foreach ($this->actorScoreChoices as $actorScore) {
			$selected = ($actorScore == $this->actorScore) ? " selected='selected'" : "";
			$rs .= "<option value='".$actorScore."'".$selected.">".$actorScore."</option>\n";
		}
		return $rs;
	}


	public function set_actorId($inVal) { $this->actorId = intval($inVal); }
	public function get_actorId() { return intval($this->actorId); }

	public function set_actorName($inVal) { $this->actorName = trim($inVal); }
	public function get_actorName() { return $this->actorName; }

	public function set_actorScore($inVal) { $this->actorScore = intval($inVal); }
	public function get_actorScore() { return intval($this->actorScore); }

	public function set_actorUrl($inVal) { $this->actorUrl = trim($inVal); }
	public function get_actorUrl() { return $this->actorUrl; }

	public function set_actorComments($inVal) { (is_null($inVal)) ? null : $this->actorComments = trim($inVal); }
	public function get_actorComments() { return $this->actorComments; }

	public function set_hasPicture($inVal) { $this->hasPicture = ($inVal == 'Y') ? 'Y' : 'N'; }
	public function get_hasPicture() { return $this->hasPicture; }

	public function set_isActive($inVal) { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
	public function get_isActive() { return $this->isActive; }

	public function set_privateNotes($inVal) { (is_null($inVal)) ? null : $this->privateNotes = trim($inVal); }
	public function get_privateNotes() { return $this->privateNotes; }


	public function __set($inName, $inVal) {
		if ($inName == 'actorId') { $this->actorId = intval($inVal); }
		elseif ($inName == 'actorName') { $this->actorName = trim($inVal); }
		elseif ($inName == 'actorScore') { $this->actorScore = intval($inVal); }
		elseif ($inName == 'actorUrl') { $this->actorUrl = trim($inVal); }
		elseif ($inName == 'actorComments') { $this->actorComments = trim($inVal); }
		elseif ($inName == 'isActive') { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
		elseif ($inName == 'hasPicture') { $this->hasPicture = ($inVal == 'Y') ? 'Y' : 'N'; }
		elseif ($inName == 'privateNotes') { $this->privateNotes = trim($inVal); }
		else { $this->$inName = $inVal; }
	}


	public function __get($inName) {

		$outVal = null;

		if ($inName == 'actorId') { $outVal = (integer) intval($this->actorId); }
		elseif ($inName == 'actorName') { $outVal = (string) trim($this->actorName); }
		elseif ($inName == 'actorScore') { $outVal = (integer) intval($this->actorScore); }
		elseif ($inName == 'actorUrl') { $outVal = (string) trim($this->actorUrl); }
		elseif ($inName == 'actorComments') { $outVal = (string) trim($this->actorComments); }
		elseif ($inName == 'isActive') { $outVal = (string) $this->isActive; }
		elseif ($inName == 'hasPicture') { $outVal = (string) $this->hasPicture; }
		elseif ($inName == 'privateNotes') { $outVal = (string) trim($this->privateNotes); }

		return $outVal;
	}


	public function __isset($inName) {

		$outVal = false;

		if ($inName == 'actorId') { $outVal = (intval($this->actorId) > -1) ? true : false; }
		elseif ($inName == 'actorName') { $outVal = (trim($this->actorName) != '') ? true : false; }
		elseif ($inName == 'actorScore') { $outVal = (intval($this->actorScore) > -1) ? true : false; }
		elseif ($inName == 'actorUrl') { $outVal = (trim($this->actorUrl) != '') ? true : false; }
		elseif ($inName == 'actorComments') { $outVal = (trim($this->actorComments) != '') ? true : false; }
		elseif ($inName == 'isActive') { $outVal = true; }
		elseif ($inName == 'hasPicture') { $outVal = ($this->hasPicture == 'Y' || $this->hasPicture == 'N') ? true : false; }
		elseif ($inName == 'privateNotes') { $outVal = (trim($this->privateNotes) != '') ? true : false; }
		return $outVal;
	}


	public function __unset($inName) {
		if ($inName == 'actorId') { $this->actorId = 0; }
		elseif ($inName == 'actorName') { $this->actorName = ''; }
		elseif ($inName == 'actorScore') { $this->actorScore = 0; }
		elseif ($inName == 'actorUrl') { $this->actorUrl = ''; }
		elseif ($inName == 'actorComments') { $this->actorComments = ''; }
		elseif ($inName == 'isActive') { $this->isActive = 'Y'; }
		elseif ($inName == 'hasPicture') { $this->hasPicture = 'N'; }
		elseif ($inName == 'privateNotes') { $this->privateNotes = ''; }
	}


	public function __destruct() { parent::__destruct(); }

}
?>