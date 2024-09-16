<?php

class MOVIE_GENRE_XREF extends MOVIETRACKER {

	protected $movieId = 0;
	protected $genreId = 0;


	public function __construct($movieId = null, $genreId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		$this->set_movieId($movieId);
		$this->set_genreId($genreId);
	}



	public function create_xref() {

		$statement = $this->dbConnection->prepare("insert into movie_genre_xref (
		movie_id,
		genre_id
		) values (
		:movieId,
		:genreId)");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':genreId', $this->genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Created Movie Genre Xref:\n".print_r($this->logableObj($this), true));
		}
	}


	public function delete_xref() {
		$statement = $this->dbConnection->prepare("delete from movie_genre_xref where movie_id = :movieId and genre_id = :genreId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':genreId', $this->genreId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->addToLog('EDIT', "Deleted Movie Genre Xref:\n".print_r($this->logableObj($this), true));
		}
	}

	public function fetch_genresInMovie($movieId) {
		$collection = array();
		$statement = $this->dbConnection->prepare("select genre_id from movie_genre_xref where movie_id = :movieId");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) { $collection[] = $row->genre_id; }
		}
		return $collection;
	}


	public function delete_genresInMovie($movieId = '') {
		if ($movieId == '') { $movieId = $this->movieId; }
		$statement = $this->dbConnection->prepare("delete from movie_genre_xref where movie_id = :movieId");
		$statement->bindParam(':movieId', $movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
	}


	public function add_genresToMovie($movieId, $genreCollection) {

		$statement = $this->dbConnection->prepare("insert into movie_genre_xref (
		movie_id,
		genre_id
		) values (
		:movieId,
		:genreId)");
		foreach ($genreCollection as $genreId) {
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
				//$this->addToLog('EDIT', "Created Movie Genre Xref:\n".print_r($this->logableObj($this), true));
			}
		}
	}


	public function set_movieId($inVal) {
		$this->movieId = intval($inVal);
		$this->logSettings['movieId'] = $this->movieId;
		MOVIETRACKER::setCurrentMovieId($this->movieId);
	}
	public function get_movieId() { return intval($this->movieId); }

	public function set_genreId($inVal) { $this->genreId = intval($inVal); }
	public function get_genreId() { return intval($this->genreId); }


	public function __set($inName, $inVal) {
		if ($inName == 'movieId') {
			$this->movieId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'genreId') { $this->genreId = intval($inVal); }
		else { $this->$inName = $inVal; }
	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'genreId') { $outVal = (integer) intval($this->genreId); }
		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'movieId') { $outVal = (intval($this->movieId) > 0) ? true : false; }
		elseif ($inName == 'genreId') { $outVal = (intval($this->genreId) > 0) ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'genreId') { $this->genreId = 0; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>