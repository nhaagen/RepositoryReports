<?php
namespace CaT\Plugins\RepositoryReports\Settings;
/**
 * Interface for DB to handle of additional settings
 */
class ilDB { //implements DB {
	/**
	 * @var gDB \ilDB
	 */
	private $gDB;

	const TABLE_SETTINGS = 'xrep_local_settings';

	public function __construct($db) {
		$this->gDB = $db;
	}

	/**
	 * Install tables
	 */
	public function install() {
		$this->installReportSettingsTable();
	}

	/**
	 * Update settings of an existing repo object.
	 *
	 * @param 	int 	$obj_id
	 * @param	array 	<\Setting>
	 */
	public function update($obj_id, array $settings){

		foreach ($settings as $setting) {
			$vals = $setting->toArray();
			$query = 'REPLACE INTO '.static::TABLE_SETTINGS
				.'(obj_id, id, ' //primaries
				.'title, type, ref_id) VALUES ('
				.$this->gDB->quote($obj_id, 'integer') .', '
				.$this->gDB->quote($vals['id'], 'text') .', '
				.$this->gDB->quote($vals['title'], 'text') .', '
				.$this->gDB->quote($vals['type'], 'text') .', '
				.$this->gDB->quote($vals['ref_id'], 'integer')
				.')';

			$this->gDB->manipulate($query);
		}
	}


	/**
	 * return <PLUGINNAME> for $obj_id
	 *
	 * @param int $obj_id
	 *
	 * @return \CaT\Plugins\<PLUGINNAME>\Settings\<PLUGINNAME>
	 */
	public function selectFor($obj_id){
		assert('is_int($obj_id)');

		$query = 'SELECT * FROM '.static::TABLE_SETTINGS
			.' WHERE obj_id = ' .$this->gDB->quote($obj_id, 'integer');

		$res = $this->gDB->query($query);
		if($this->gDB->numRows($res) == 0) {
			return false;
		}
		$ret = array();
		while ($rec = $this->gDB->fetchAssoc($res)) {
			$setting = new RepositoryReportsSetting(
				$rec['id'],
				$rec['title'],
				$rec['type'],
				$rec['ref_id']
			);
			array_push($ret, $setting);
		}
		return $ret;
	}

	public function getNextFieldFor($obj_id) {
		$query = 'SELECT id FROM '.static::TABLE_SETTINGS
			.' WHERE obj_id = ' .$this->gDB->quote($obj_id, 'integer')
			.' ORDER BY id DESC LIMIT 1';

		$res = $this->gDB->query($query);
		if($this->gDB->numRows($res) == 0) {
			return 'f1';
		}
		$sid = $this->gDB->fetchAssoc($res)['id'];
		$nu =  intval(substr($sid, 1)) + 1;
		return 'f' .$nu;
	}


	/**
	 * Delete all information of the given obj id
	 *
	 * @param 	int 	$obj_id
	 */
	public function deleteFor($obj_id, $field_ids){
		$query = 'DELETE FROM '.static::TABLE_SETTINGS
			.' WHERE obj_id = ' .$this->gDB->quote($obj_id, 'integer')
			.' AND id IN (\''
			.join("', '", $field_ids)
			.'\')';

		$this->gDB->manipulate($query);
	}





	/**
	 * install the table to hold settings for obj/package
	 */
	protected function installReportSettingsTable() {
		$fields = array(
			'obj_id' => array(
				'type' => 'integer',
				'length' => 8,
				'notnull' => true
			),
			'id' => array(
				'type' => 'text',
				'length' => 64,
				'notnull' => true
			),
			'title' => array(
				'type' => 'text',
				'length' => 128,
				'notnull' => false
			),
			'type' => array( // "blank"||"memberbelow"||"learningprogress"
				'type' => 'text',
				'length' => 32,
				'notnull' => true
			),
			'ref_id' => array(
				'type' => 'integer',
				'length' => 8,
				'notnull' => false
			)
		);
		if(!$this->gDB->tableExists(static::TABLE_SETTINGS)) {
			$this->gDB->createTable(static::TABLE_SETTINGS, $fields);
			$this->gDB->addPrimaryKey(static::TABLE_SETTINGS, array('obj_id', 'id'));
		}
	}


}