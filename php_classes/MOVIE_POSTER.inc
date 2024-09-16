<?php

class MOVIE_POSTER extends MOVIETRACKER {

	protected $movieId = 0;
	protected $posterName = '';
	protected $posterWidth = 0;
	protected $posterHeight = 0;

	public function __construct($movieId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		if (is_numeric($movieId) && $movieId > 0) {
			$this->set_movieId($movieId);
			$statement = $this->dbConnection->prepare("select
			movie_id,
			poster_name,
			poster_width,
			poster_height
			from movie_poster where movie_id = :movieId");
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
					$this->set_posterName($row->poster_name);
					$this->set_posterWidth($row->poster_width);
					$this->set_posterHeight($row->poster_height);
				}
			}
		}
	}


	public function save_poster() {
		$this->create_poster();
	}


	private function check_forExistingPoster() {
		$kount = 0;
		$statement = $this->dbConnection->prepare("select count(*) as kount from movie_poster where movie_id = :movieId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {	$kount = $row->kount; }
		}
		return $kount;
	}


	public function create_poster() {
		$statement = $this->dbConnection->prepare("insert into movie_poster (
		movie_id,
		poster_name,
		poster_width,
		poster_height
		) values (
		:movieId,
		:posterName,
		:posterWidth,
		:posterHeight)");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':posterName', $this->posterName, PDO::PARAM_STR, strlen($this->posterName));
		$statement->bindParam(':posterWidth', $this->posterWidth, PDO::PARAM_INT);
		$statement->bindParam(':posterHeight', $this->posterHeight, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->pushToLog('EDIT', "Created Movie Poster Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function delete_poster() {
		if ($this->posterName != '') {
			if (file_exists($_SERVER["DOCUMENT_ROOT"].'/posters/'.rawurlencode($this->posterName))) {
				unlink($_SERVER["DOCUMENT_ROOT"].'/posters/'.rawurlencode($this->posterName));
			}
		}
		$statement = $this->dbConnection->prepare("delete from movie_poster where movie_id = :movieId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->pushToLog('EDIT', "Deleted Movie Poster Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function set_movieId($inVal) {
		$this->movieId = intval($inVal);
		$this->logSettings['movieId'] = $this->movieId;
		MOVIETRACKER::setCurrentMovieId($this->movieId);
	}
	public function get_movieId() { return intval($this->movieId); }

	public function set_posterName($inVal) { $this->posterName = trim($inVal); }
	public function get_posterName() { return $this->posterName; }

	public function set_posterWidth($inVal) { $this->posterWidth = intval($inVal); }
	public function get_posterWidth() { return intval($this->posterWidth); }

	public function set_posterHeight($inVal) { $this->posterHeight = intval($inVal); }
	public function get_posterHeight() { return intval($this->posterHeight); }



	public function __set($inName, $inVal) {
		if ($inName == 'movieId') {
			$this->movieId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'posterName') { $this->posterName = trim($inVal); }
		elseif ($inName == 'posterWidth') { $this->posterWidth = intval($inVal); }
		elseif ($inName == 'posterHeight') { $this->posterHeight = intval($inVal); }
	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'posterName') { $outVal = (string) $this->posterName; }
		elseif ($inName == 'posterWidth') { $outVal = (integer) intval($this->posterWidth); }
		elseif ($inName == 'posterHeight') { $outVal = (integer) intval($this->posterHeight); }
		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'movieId') { $outVal = (intval($this->movieId) > 0) ? true : false; }
		elseif ($inName == 'posterName') { $outVal = (trim($this->posterName) !== '') ? true : false; }
		elseif ($inName == 'posterWidth') { $outVal = (intval($this->posterWidth) > 0) ? true : false; }
		elseif ($inName == 'posterHeight') { $outVal = (intval($this->posterHeight) > 0) ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'posterName') { $this->posterName = ''; }
		elseif ($inName == 'posterWidth') { $this->posterWidth = 0; }
		elseif ($inName == 'posterHeight') { $this->posterHeight = 0; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>