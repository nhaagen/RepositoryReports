<?php

namespace CaT\Plugins\RepositoryReports\Settings;
/**
 * This is the object for additional settings.
 */
class RepositoryReportsSetting {

	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string "blank"||"memberbelow"||"learningprogress"
	 */
	private $type;

	/**
	 * @var integer
	 */
	private $ref_id;


	private static $VALID_TYPES = array(
		'blank', //allways empty string
		'memberbelow', //title of course below ref_id the user is member of
		'learningprogress', //lp of user in ref_id
		'lpmembership' //give fieldId instead of object.ref_id;
					   //do memberbelow-lookup and return progress for result and user
	);


	public function __construct($id, $title, $type, $ref_id) {
		assert('is_int($id)');
		assert('is_string($title)');
		assert('is_string($type)');
		//assert('(is_int($ref_id) || $ref_id=='')';

		if(! $this->isValidType($type)) {
			throw new \InvalidArgumentException('not a valid type: ' .$type);
		}

		/*
		if(trim($id) === '') {
			throw new \InvalidArgumentException('internal name must not be empty string');
		}
		*/

		$this->id = $id;
		$this->title = $title;
		$this->type = $type;
		$this->ref_id = $ref_id;
	}

	/**
	 * validate type; must be "int", "ref" of "txt"
	 *
	 * @return boolean
	 */
	public function isValidType($type) {
		return in_array($type, self::$VALID_TYPES);
	}

	public function validTypes() {
		return self::$VALID_TYPES;
	}



	/**
	 * get setting values as array
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'id' => $this->id,
			'title' => $this->title,
			'type' => $this->type,
			'ref_id' => $this->ref_id
		);
	}


	public function id() {
		return $this->id;
	}
	public function title() {
		return $this->title;
	}
	public function ref_id() {
		return $this->ref_id;
	}

	/**
	 * get type
	 *
	 * @return string
	 */
	public function type() {
		return $this->type;
	}

}