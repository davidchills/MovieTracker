<?php

class GENRE extends MOVIETRACKER {

	protected $genreId = 0;
	protected $genreName = '';
	protected $sortOrder = 0;
	protected $isActive = 'Y';

	/**
	 * Constructor
	 * @param int $actorId
	 * @return void
	 */
	public function __construct($genreId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		if (is_numeric($genreId)) {
			$statement = $this->dbConnection->prepare("select
			genre_id,
			genre_name,
			sort_order,
			is_active
			from movie_genre where genre_id = :genreId");
			$statement->bindParam(':genreId', $genreId, PDO::PARAM_INT);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if (is_object($statement) && get_class($statement) == 'PDOStatement') {
				while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
					$this->set_genreId($row->genre_id);
					$this->set_genreName($row->genre_name);
					$this->set_sortOrder($row->sort_order);
					$this->set_isActive($row->is_active);
				}
			}
		}
	}


	/**
	 * Saves the object back to the database
	 * @return void
	 */
	public function save_genre() {

		$this->compare_old_new_objects($this->genreId);

		$statement = $this->dbConnection->prepare("update movie_genre set
		genre_name = :genreName,
		sort_order = :sortOrder,
		is_active = :isActive
		where genre_id = :genreId");
		$statement->bindParam(':genreName', $this->genreName, PDO::PARAM_STR, strlen($this->genreName));
		$statement->bindParam(':sortOrder', $this->sortOrder, PDO::PARAM_INT);
		$statement->bindParam(':isActive', $this->isActive);
		$statement->bindParam(':genreId', $this->genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1 && $this->oldNewDiffString != '') {
			$this->addToLog('EDIT', "Genre \"".$this->genreName." (".$this->genreId.")\" Updated:\n".$this->oldNewDiffString);
		}
	}


	/**
	 * Inserts the object into the database as a new entry
	 * @return void
	 */
	public function create_genre() {

		$tmpActorId = $this->test_forExistingGenre();
		if ($tmpActorId > 0) {
			self::__construct($tmpActorId);
			$this->addToLog('EDIT', "Found Existing Genre Obj:\n".print_r($this->logableObj($this), true));
		}
		else {
			$statement = $this->dbConnection->prepare("insert into movie_genre (
			genre_name,
			sort_order,
			is_active
			) values (
			:genreName,
			:sortOrder,
			:isActive)");
			$statement->bindParam(':genreName', $this->genreName, PDO::PARAM_STR, strlen($this->genreName));
			$statement->bindParam(':sortOrder', $this->sortOrder, PDO::PARAM_INT);
			$statement->bindParam(':isActive', $this->isActive);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if ($statement->rowCount() == 1) {
				$actorId = $this->dbConnection->lastInsertId();
				$this->set_genreId($genreId);
				$this->addToLog('EDIT', "Created Genre Obj:\n".print_r($this->logableObj($this), true));
			}
		}
	}


	private function test_forExistingGenre() {
		$genreId = 0;
		$statement = $this->dbConnection->prepare("select genre_id from movie_genre where genre_name = :genreName");
		$statement->bindParam(':genreName', $this->genreName, PDO::PARAM_STR, strlen($this->genreName));
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {	$genreId = $row->genre_id; }
		}
		return $genreId;
	}


	public function delete_genre() {
		$statement = $this->dbConnection->prepare("delete from movie_genre where genre_id = :genreId");
		$statement->bindParam(':genreId', $this->genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Deleted Genre Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function fetch_allGenres() {

		$collection = array();

		$statement = $this->dbConnection->query("select * from movie_genre order by genre_name");
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$collection[] = $row;
			}
		}
		return $collection;
	}


	public function add_genreToMovie($movieId, $genreId) {
		$statement = $this->dbConnection->prepare("insert into movie_genre_xref (movie_id, genre_id) values (:movieId, :genreId)");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		$statement->bindParam(':genreId', $genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Added Genre ".$genreId." to Movie ".$movieId.":\n".print_r($this->logableObj($this), true));
		}
	}


	public function remove_genreFromMovie($movieId, $genreId) {
		$statement = $this->dbConnection->prepare("delete from movie_genre_xref where movie_id = :movieId and genre_id = :genreId");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		$statement->bindParam(':genreId', $genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Removed Genre ".$genreId." from Movie ".$movieId.":\n".print_r($this->logableObj($this), true));
		}
	}


	public function set_genreId($inVal) { $this->genreId = intval($inVal); }
	public function get_genreId() { return intval($this->genreId); }

	public function set_genreName($inVal) { $this->genreName = trim($inVal); }
	public function get_genreName() { return $this->genreName; }

	public function set_sortOrder($inVal) { $this->sortOrder = intval($inVal); }
	public function get_sortOrder() { return intval($this->sortOrder); }

	public function set_isActive($inVal) { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
	public function get_isActive() { return $this->isActive; }


	public function __set($inName, $inVal) {
		if ($inName == 'genreId') { $this->genreId = intval($inVal); }
		elseif ($inName == 'genreName') { $this->genreName = trim($inVal); }
		elseif ($inName == 'sortOrder') { $this->sortOrder = intval($inVal); }
		elseif ($inName == 'isActive') { $this->isActive = ($inVal == 'N') ? 'N' : 'Y'; }
		else { $this->$inName = $inVal; }
	}


	public function __get($inName) {

		$outVal = null;

		if ($inName == 'genreId') { $outVal = (integer) intval($this->genreId); }
		elseif ($inName == 'genreName') { $outVal = (string) trim($this->genreName); }
		elseif ($inName == 'sortOrder') { $outVal = (integer) intval($this->sortOrder); }
		elseif ($inName == 'isActive') { $outVal = (string) $this->isActive; }
		return $outVal;
	}


	public function __isset($inName) {

		$outVal = false;

		if ($inName == 'genreId') { $outVal = (intval($this->genreId) > -1) ? true : false; }
		elseif ($inName == 'genreName') { $outVal = (trim($this->genreName) != '') ? true : false; }
		elseif ($inName == 'sortOrder') { $outVal = (intval($this->sortOrder) > -1) ? true : false; }
		elseif ($inName == 'isActive') { $outVal = true; }
		return $outVal;
	}


	public function __unset($inName) {
		if ($inName == 'genreId') { $this->genreId = 0; }
		elseif ($inName == 'genreName') { $this->genreName = ''; }
		elseif ($inName == 'sortOrder') { $this->sortOrder = 0; }
		elseif ($inName == 'isActive') { $this->isActive = 'Y'; }
	}


	public function __destruct() { parent::__destruct(); }

}
?>