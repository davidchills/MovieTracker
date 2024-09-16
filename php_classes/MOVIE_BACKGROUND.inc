<?php

class MOVIE_BACKGROUND extends MOVIETRACKER {

	protected $movieId = 0;
	protected $backgroundName = '';
	protected $backgroundWidth = 0;
	protected $backgroundHeight = 0;

	public function __construct($movieId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		if (is_numeric($movieId) && $movieId > 0) {
			$this->set_movieId($movieId);
			$statement = $this->dbConnection->prepare("select
			movie_id,
			background_name,
			background_width,
			background_height
			from movie_background where movie_id = :movieId");
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
					$this->set_backgroundName($row->background_name);
					$this->set_backgroundWidth($row->background_width);
					$this->set_backgroundHeight($row->background_height);
				}
			}
		}
	}


	public function save_background() {
		$this->create_background();
	}


	private function check_forExistingBackground() {
		$kount = 0;
		$statement = $this->dbConnection->prepare("select count(*) as kount from movie_background where movie_id = :movieId");
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


	public function create_background() {
		$statement = $this->dbConnection->prepare("insert into movie_background (
		movie_id,
		background_name,
		background_width,
		background_height
		) values (
		:movieId,
		:backgroundName,
		:backgroundWidth,
		:backgroundHeight)");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':backgroundName', $this->backgroundName, PDO::PARAM_STR, strlen($this->backgroundName));
		$statement->bindParam(':backgroundWidth', $this->backgroundWidth, PDO::PARAM_INT);
		$statement->bindParam(':backgroundHeight', $this->backgroundHeight, PDO::PARAM_INT);
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


	public function delete_background() {
		if ($this->backgroundName != '') {
			if (file_exists($_SERVER["DOCUMENT_ROOT"].'/backgrounds/'.rawurlencode($this->backgroundName))) {
				unlink($_SERVER["DOCUMENT_ROOT"].'/backgrounds/'.rawurlencode($this->backgroundName));
			}
		}
		$statement = $this->dbConnection->prepare("delete from movie_background where movie_id = :movieId");
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

	public function set_backgroundName($inVal) { $this->backgroundName = trim($inVal); }
	public function get_backgroundName() { return $this->backgroundName; }

	public function set_backgroundWidth($inVal) { $this->backgroundWidth = intval($inVal); }
	public function get_backgroundWidth() { return intval($this->backgroundWidth); }

	public function set_backgroundHeight($inVal) { $this->backgroundHeight = intval($inVal); }
	public function get_backgroundHeight() { return intval($this->backgroundHeight); }



	public function __set($inName, $inVal) {
		if ($inName == 'movieId') {
			$this->movieId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'backgroundName') { $this->backgroundName = trim($inVal); }
		elseif ($inName == 'backgroundWidth') { $this->backgroundWidth = intval($inVal); }
		elseif ($inName == 'backgroundHeight') { $this->backgroundHeight = intval($inVal); }
	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'backgroundName') { $outVal = (string) $this->backgroundName; }
		elseif ($inName == 'backgroundWidth') { $outVal = (integer) intval($this->backgroundWidth); }
		elseif ($inName == 'backgroundHeight') { $outVal = (integer) intval($this->backgroundHeight); }
		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'movieId') { $outVal = (intval($this->movieId) > 0) ? true : false; }
		elseif ($inName == 'backgroundName') { $outVal = (trim($this->backgroundName) !== '') ? true : false; }
		elseif ($inName == 'backgroundWidth') { $outVal = (intval($this->backgroundWidth) > 0) ? true : false; }
		elseif ($inName == 'backgroundHeight') { $outVal = (intval($this->backgroundHeight) > 0) ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'backgroundName') { $this->backgroundName = ''; }
		elseif ($inName == 'backgroundWidth') { $this->backgroundWidth = 0; }
		elseif ($inName == 'backgroundHeight') { $this->backgroundHeight = 0; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>