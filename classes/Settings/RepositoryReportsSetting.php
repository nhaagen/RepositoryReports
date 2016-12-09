<?php

namespace CaT\Plugins\RepositoryReports\Settings;

/**
 * This is the settings-object for report configuration
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
	 * @var string (see: VALID_TYPES)
	 */
	private $type;

	/**
	 * @var string
	 */
	private $value;


	private static $VALID_TYPES = array(
		'fix', //fixed value
		'userfield', //fieldname in usr_data
		'memberbelow', //title of course below ref_id the user is member of
		'learningprogress', //lp of user in ref_id
		'lpmembership' //give fieldId instead of object.ref_id;
					   //do memberbelow-lookup and return progress for result and user
	);


	public function __construct($id, $title, $type, $value) {
		assert('is_int($id)');
		assert('is_string($title)');
		assert('is_string($type)');

		if(! $this->isValidType($type)) {
			throw new \InvalidArgumentException('not a valid type: ' .$type);
		}
		$this->id = $id;
		$this->title = $title;
		$this->type = $type;
		$this->value = $value;
	}

	/**
	 * validate type
	 *
	 * @return boolean
	 */
	public function isValidType($type) {
		return in_array($type, self::$VALID_TYPES);
	}
	/**
	 * valid types
	 *
	 * @return array <string>
	 */
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
			'value' => $this->value
		);
	}

	/**
	 * get id
	 *
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * get title
	 *
	 * @return string
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * get value
	 *
	 * @return mixed
	 */
	public function value() {
		return $this->value;
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