<?php

class MOVIE_CHAPTERS extends MOVIETRACKER {

	protected $chapterId = 0;
	protected $movieId = 0;
	protected $startHour = 0;
	protected $startMinute = 0;
	protected $startSecond = 0;
	protected $startMicro = '0000000';
	protected $description = '';
	private $prefilled = false;

	public function __construct($chapterId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		if (!empty($chapterId) && is_numeric($chapterId) && $chapterId > 0) {
			$statement = $this->dbConnection->prepare("select
			chapter_id,
			movie_id,
			start_hour,
			start_minute,
			start_second,
			start_micro,
			description
			from movie_chapters where chapter_id = :chapterId");
			$statement->bindParam(':chapterId', $chapterId, PDO::PARAM_INT);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if (is_object($statement) && get_class($statement) == 'PDOStatement') {
				while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
					$this->set_chapterId($row->chapter_id);
					$this->set_movieId($row->movie_id);
					$this->set_startHour($row->start_hour);
					$this->set_startMinute($row->start_minute);
					$this->set_startSecond($row->start_second);
					$this->set_startMicro($row->start_micro);
					$this->set_description($row->description);
				}
			}
		}
	}


	public function update_chapter() {
		$statement = $this->dbConnection->prepare("update movie_chapters set
		movie_id = :movieId,
		start_hour = :startHour,
		start_minute = :startMinute,
		start_second = :startSecond,
		start_micro = :startMicro,
		description = :description
		where chapter_id = :chapterId");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':startHour', $this->startHour, PDO::PARAM_INT);
		$statement->bindParam(':startMinute', $this->startMinute, PDO::PARAM_INT);
		$statement->bindParam(':startSecond', $this->startSecond, PDO::PARAM_INT);
		$statement->bindParam(':startMicro', $this->startMicro, PDO::PARAM_STR, 7);
		$statement->bindParam(':description', $this->description, PDO::PARAM_STR, strlen($this->description));
		$statement->bindParam(':chapterId', $this->chapterId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
	}


	public function create_chapter() {
		$statement = $this->dbConnection->prepare("insert into movie_chapters (
		movie_id,
		start_hour,
		start_minute,
		start_second,
		start_micro,
		description
		) values (
		:movieId,
		:startHour,
		:startMinute,
		:startSecond,
		:startMicro,
		:description)");
		$statement->bindParam(':movieId', $this->movieId, PDO::PARAM_INT);
		$statement->bindParam(':startHour', $this->startHour, PDO::PARAM_INT);
		$statement->bindParam(':startMinute', $this->startMinute, PDO::PARAM_INT);
		$statement->bindParam(':startSecond', $this->startSecond, PDO::PARAM_INT);
		$statement->bindParam(':startMicro', $this->startMicro, PDO::PARAM_STR, 7);
		$statement->bindParam(':description', $this->description, PDO::PARAM_STR, strlen($this->description));
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$chapterId = $this->dbConnection->lastInsertId();
			$this->set_chapterId($chapterId);
			$this->addToLog('EDIT', "Created Movie Chapter Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function delete_chapter() {
		$statement = $this->dbConnection->prepare("delete from movie_chapters where chapter_id = :chapterId");
		$statement->bindParam(':chapterId', $this->chapterId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->pushToLog('EDIT', "Deleted Movie Chapter Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function fetch_chapterDataForMovie($movieId) {
		$numOfRows = 0;
		$this->set_movieId($movieId);
		$collection = array();

		$statement = $this->dbConnection->prepare("select
		chapter_id,
		movie_id,
		start_hour,
		start_minute,
		start_second,
		start_micro,
		description
		from movie_chapters where movie_id = :movieId
		order by start_hour, start_minute, start_second");
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
				$numOfRows++;
				$collection[] = $row;
			}
		}
		// If there is not chapter data, create 3 entries per Actor.
		if ($numOfRows === 0 && $this->prefilled === false) {
			$collection = $this->prefill_chapters($movieId);
		}
		return $collection;
	}


	private function prefill_chapters($movieId) {
		$this->prefilled = true;
		$startHour = 0;
		$collection = array();
		$statement = $this->dbConnection->prepare("select
		a.actor_name
		from
			actor as a,
			movie_actor_xref as ax
		where
			ax.movie_id = :movieId
			and a.actor_id = ax.actor_id
		order by 1");
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
				$chapterObj1 = new MOVIE_CHAPTERS();
				$chapterObj1->set_movieId($movieId);
				$chapterObj1->set_startHour($startHour);
				$chapterObj1->set_description($row->actor_name);
				$chapterObj1->create_chapter();

				$tmpObj1 = new stdClass();
				$tmpObj1->chapter_id = $chapterObj1->get_chapterId();
				$tmpObj1->movie_id = $movieId;
				$tmpObj1->start_hour = $chapterObj1->get_startHour();
				$tmpObj1->start_minute = $chapterObj1->get_startMinute();
				$tmpObj1->start_second = $chapterObj1->get_startSecond();
				$tmpObj1->start_micro = $chapterObj1->get_startMicro();
				$tmpObj1->description = $chapterObj1->get_description();
				$collection[] = $tmpObj1;
				$startHour++;
				/*
				$chapterObj2 = new MOVIE_CHAPTERS();
				$chapterObj2->set_movieId($movieId);
				$chapterObj2->set_startHour($startHour);
				$chapterObj2->set_description($row->actor_name);
				$chapterObj2->create_chapter();

				$tmpObj2 = new stdClass();
				$tmpObj2->chapter_id = $chapterObj2->get_chapterId();
				$tmpObj2->movie_id = $movieId;
				$tmpObj2->start_hour = $chapterObj2->get_startHour();
				$tmpObj2->start_minute = $chapterObj2->get_startMinute();
				$tmpObj2->start_second = $chapterObj2->get_startSecond();
				$tmpObj2->start_micro = $chapterObj2->get_startMicro();
				$tmpObj2->description = $chapterObj2->get_description();
				$collection[] = $tmpObj2;
				$startHour++;
				*/
				$chapterObj3 = new MOVIE_CHAPTERS();
				$chapterObj3->set_movieId($movieId);
				$chapterObj3->set_startHour($startHour);
				$chapterObj3->set_description($row->actor_name);
				$chapterObj3->create_chapter();

				$tmpObj3 = new stdClass();
				$tmpObj3->chapter_id = $chapterObj3->get_chapterId();
				$tmpObj3->movie_id = $movieId;
				$tmpObj3->start_hour = $chapterObj3->get_startHour();
				$tmpObj3->start_minute = $chapterObj3->get_startMinute();
				$tmpObj3->start_second = $chapterObj3->get_startSecond();
				$tmpObj3->start_micro = $chapterObj3->get_startMicro();
				$tmpObj3->description = $chapterObj3->get_description();
				$collection[] = $tmpObj3;
				$startHour++;
			}
		}
		return $collection;
	}


	public function fetch_chapterMetaForMovie($movieId) {
		$this->set_movieId($movieId);
		$rs = "00:00:00.0000000\tBeginning\n";
		$statement = $this->dbConnection->prepare("select
		CONCAT(LPAD(start_hour, 2, '0'), ':', LPAD(start_minute, 2, '0'), ':', LPAD(start_second, 2, '0'), '.', RPAD(start_micro, 7, '0')) as time_mark,
		description
		from movie_chapters
		where movie_id = :movieId
		order by start_hour, start_minute, start_second");
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
				$rs .= $row->time_mark."\t".$row->description."\n";
			}
		}
		return $rs;
	}


	public function fetch_chaptersAsJsonData($movieId) {
		$chapters = array();
		$statement = $this->dbConnection->prepare("select
		concat(((start_hour*60*60)+(start_minute*60)+start_second),'.',start_micro) as chap_sec,
		description
		from movie_chapters
		where movie_id = :movieId
		order by start_hour, start_minute, start_second, start_micro");
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
				$chapters[] = $row;
			}
		}
		return json_encode($chapters);
	}


	public function set_chapterId($inVal) { $this->chapterId = intval($inVal); }
	public function get_chapterId() { return intval($this->chapterId); }

	public function set_movieId($inVal) {
		$this->movieId = intval($inVal);
		$this->logSettings['movieId'] = $this->movieId;
		MOVIETRACKER::setCurrentMovieId($this->movieId);
	}
	public function get_movieId() { return intval($this->movieId); }

	public function set_startHour($inVal) { $this->startHour = intval($inVal); }
	public function get_startHour() { return intval($this->startHour); }

	public function set_startMinute($inVal) { $this->startMinute = intval($inVal); }
	public function get_startMinute() { return intval($this->startMinute); }

	public function set_startSecond($inVal) { $this->startSecond = intval($inVal); }
	public function get_startSecond() { return intval($this->startSecond); }

	public function set_startMicro($inVal) { $this->startMicro = str_pad(substr($inVal, 0, 7), 7, '0', STR_PAD_RIGHT); }
	public function get_startMicro() { return str_pad(substr($this->startMicro, 0, 7), 7, '0', STR_PAD_RIGHT); }

	public function set_description($inVal) { $this->description = trim($inVal); }
	public function get_description() { return $this->description; }



	public function __set($inName, $inVal) {
		if ($inName == 'chapterId') {
			$this->chapterId = intval($inVal);
			$this->logSettings['movieId'] = $this->movieId;
			MOVIETRACKER::setCurrentMovieId($this->movieId);
		}
		elseif ($inName == 'movieId') { $this->movieId = intval($inVal); }
		elseif ($inName == 'startHour') { $this->startHour = intval($inVal); }
		elseif ($inName == 'startMinute') { $this->startMinute = intval($inVal); }
		elseif ($inName == 'startSecond') { $this->startSecond = intval($inVal); }
		elseif ($inName == 'startMicro') { $this->startMicro = str_pad(substr($inVal, 0, 7), 7, '0', STR_PAD_RIGHT); }
		elseif ($inName == 'description') { $this->description = trim($inVal); }

	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'chapterId') { $outVal = (integer) intval($this->chapterId); }
		elseif ($inName == 'movieId') { $outVal = (integer) intval($this->movieId); }
		elseif ($inName == 'startHour') { $outVal = (integer) intval($this->startHour); }
		elseif ($inName == 'startMinute') { $outVal = (integer) intval($this->startMinute); }
		elseif ($inName == 'startSecond') { $outVal = (integer) intval($this->startSecond); }
		elseif ($inName == 'startMicro') { $outVal = (string) str_pad(substr($this->startMicro, 0, 7), 7, '0', STR_PAD_RIGHT); }
		elseif ($inName == 'description') { $outVal = (string) $this->description; }

		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'chapterId') { $outVal = (intval($this->chapterId) > 0) ? true : false; }
		elseif ($inName == 'movieId') { $outVal = (intval($this->movieId) > 0) ? true : false; }
		elseif ($inName == 'startHour') { $outVal = (intval($this->startHour) > 0) ? true : false; }
		elseif ($inName == 'startMinute') { $outVal = (intval($this->startMinute) > 0) ? true : false; }
		elseif ($inName == 'startSecond') { $outVal = (intval($this->startSecond) > 0) ? true : false; }
		elseif ($inName == 'startMicro') { $outVal = (strlen($this->startMicro) == 0) ? true : false; }
		elseif ($inName == 'description') { $outVal = (trim($this->description) !== '') ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'chapterId') { $this->chapterId = 0; }
		elseif ($inName == 'movieId') { $this->movieId = 0; }
		elseif ($inName == 'startHour') { $this->startHour = 0; }
		elseif ($inName == 'startMinute') { $this->startMinute = 0; }
		elseif ($inName == 'startSecond') { $this->startSecond = 0; }
		elseif ($inName == 'startMicro') { $this->startMicro = '0000000'; }
		elseif ($inName == 'description') { $this->description = ''; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>