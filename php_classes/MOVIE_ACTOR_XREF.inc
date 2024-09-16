<?php

class MOVIE_ACTOR_XREF extends MOVIETRACKER {

	protected $movieId = 0;
	protected $actorId = 0;


	public function __construct($movieId = null, $actorId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		$this->set_movieId($movieId);
		$this->set_actorId($actorId);
	}



	public function create_xref() {

		$statement = $this->dbConnection->prepare("insert into movie_actor_xref (
		movie_id,
		actor_id
		) values (
		:movieId,
		:actorId)");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Created Movie Actor Xref:\n".print_r($this->logableObj($this), true));
		}
	}


	public function delete_xref() {
		$statement = $this->dbConnection->prepare("delete from movie_actor_xref where movie_id = :movieId and actor_id = :actorId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Deleted Movie Actor Xref:\n".print_r($this->logableObj($this), true));
		}
	}

	public function fetch_actorsInMovie($movieId) {
		$collection = array();
		$statement = $this->dbConnection->prepare("select actor_id from movie_actor_xref where movie_id = :movieId");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) { $collection[] = $row->actor_id; }
		}
		return $collection;
	}


	public function delete_actorsInMovie($movieId) {
		$statement = $this->dbConnection->prepare("delete from movie_actor_xref where movie_id = :movieId");

		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		return $collection;
	}


	public function add_actorsToMovie($movieId, $actorCollection) {

		$statement = $this->dbConnection->prepare("insert into movie_actor_xref (
		movie_id,
		actor_id
		) values (
		:movieId,
		:actorId)");
		foreach ($actorCollection as $actorId) {
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
				//$this->addToLog('EDIT', "Created Movie Actor Xref:\n".print_r($this->logableObj($this), true));
			}
		}
	}


	public function set_movieId($inVal) {
		$this->movieId = intval($inVal);
		$this->logSettings['movieId'] = $this->movieId;
		MOVIETRACKER::setCurrentMovieId($this->movieId);
	}
	public function get_movieId() { return intval($this->movieId); }

	public function set_actorId($inVal) { $this->actorId = intval($inVal); }
	public function get_actorId() { return intval($this->actorId); }





	public function __set($inName, $inVal) {
		if ($inName == 'movieId') {
			$this->movieId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'actorId') { $this->actorId = intval($inVal); }
		else { $this->$inName = $inVal; }
	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'actorId') { $outVal = (integer) intval($this->actorId); }
		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'movieId') { $outVal = (intval($this->movieId) > 0) ? true : false; }
		elseif ($inName == 'actorId') { $outVal = (intval($this->actorId) > 0) ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'actorId') { $this->actorId = 0; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>