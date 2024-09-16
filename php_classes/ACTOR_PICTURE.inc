<?php

class ACTOR_PICTURE extends MOVIETRACKER {

	protected $actorId = 0;
	protected $pictureName = '';
	protected $pictureWidth = 0;
	protected $pictureHeight = 0;

	public function __construct($actorId = null) {
		parent::__construct();
		$this->set_actorId($actorId);
		$this->add_to_excludeFromExport(array('oldNewDiffString'));
		if (is_numeric($actorId) && $actorId > 0) {
			$statement = $this->dbConnection->prepare("select
			actor_id,
			picture_name,
			picture_width,
			picture_height
			from actor_picture where actor_id = :actorId");
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
					$this->set_pictureName($row->picture_name);
					$this->set_pictureWidth($row->picture_width);
					$this->set_pictureHeight($row->picture_height);
				}
			}
		}
	}

	public function save_picture() {
		$this->create_picture();
	}

	private function check_forExistingPicture() {
		$kount = 0;
		$statement = $this->dbConnection->prepare("select count(*) as kount from actor_picture where actor_id = :actorId");
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
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

	public function create_picture() {
		$statement = $this->dbConnection->prepare("insert into actor_picture (
		actor_id,
		picture_name,
		picture_width,
		picture_height
		) values (
		:actorId,
		:pictureName,
		:pictureWidth,
		:pictureHeight)");
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
		$statement->bindParam(':pictureName', $this->pictureName, PDO::PARAM_STR, strlen($this->pictureName));
		$statement->bindParam(':pictureWidth', $this->pictureWidth, PDO::PARAM_INT);
		$statement->bindParam(':pictureHeight', $this->pictureHeight, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->pushToLog('EDIT', "Created Actor Picture Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function delete_picture() {
		if ($this->pictureName != '') {
			if (file_exists($_SERVER["DOCUMENT_ROOT"].'/actor_pictures/'.rawurlencode($this->pictureName))) {
				unlink($_SERVER["DOCUMENT_ROOT"].'/actor_pictures/'.rawurlencode($this->pictureName));
			}
		}
		$statement = $this->dbConnection->prepare("delete from actor_picture where actor_id = :actorId");
		$statement->bindParam(':actorId', $this->actorId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			//$this->pushToLog('EDIT', "Deleted Actor Picture Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function set_actorId($inVal) { $this->actorId = intval($inVal); }
	public function get_actorId() { return intval($this->actorId); }

	public function set_pictureName($inVal) { $this->pictureName = trim($inVal); }
	public function get_pictureName() { return $this->pictureName; }

	public function set_pictureWidth($inVal) { $this->pictureWidth = intval($inVal); }
	public function get_pictureWidth() { return intval($this->pictureWidth); }

	public function set_pictureHeight($inVal) { $this->pictureHeight = intval($inVal); }
	public function get_pictureHeight() { return intval($this->pictureHeight); }


	public function __set($inName, $inVal) {
		if ($inName == 'actorId') { $this->actorId = intval($inVal); }
		elseif ($inName == 'pictureName') { $this->pictureName = trim($inVal); }
		elseif ($inName == 'pictureWidth') { $this->pictureWidth = intval($inVal); }
		elseif ($inName == 'pictureHeight') { $this->pictureHeight = intval($inVal); }
	}

	public function __get($inName) {
		$outVal = null;
		if ($inName == 'actorId') { $outVal = (integer) intval($this->actorId); }
		elseif ($inName == 'pictureName') { $outVal = (string) $this->pictureName; }
		elseif ($inName == 'pictureWidth') { $outVal = (integer) intval($this->pictureWidth); }
		elseif ($inName == 'pictureHeight') { $outVal = (integer) intval($this->pictureHeight); }
		return $outVal;
	}

	public function __isset($inName) {
		$outVal = false;
		if ($inName == 'actorId') { $outVal = (intval($this->actorId) > 0) ? true : false; }
		elseif ($inName == 'pictureName') { $outVal = (trim($this->pictureName) !== '') ? true : false; }
		elseif ($inName == 'pictureWidth') { $outVal = (intval($this->pictureWidth) > 0) ? true : false; }
		elseif ($inName == 'pictureHeight') { $outVal = (intval($this->pictureHeight) > 0) ? true : false; }
		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'actorId') { $this->actorId = 0; }
		elseif ($inName == 'pictureName') { $this->pictureName = ''; }
		elseif ($inName == 'pictureWidth') { $this->pictureWidth = 0; }
		elseif ($inName == 'pictureHeight') { $this->pictureHeight = 0; }
	}

	public function __destruct() { parent::__destruct(); }

}
?>