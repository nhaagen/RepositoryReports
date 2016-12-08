<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("class.ilReportFieldDefinitionGUI.php");

use CaT\Plugins\RepositoryReports\Settings;
/**
 *
 * @ilCtrl_Calls ilReportConfigGUI: ilFormPropertyDispatchGUI
 */

class ilReportConfigGUI {

	const CMD_CONFIG = "configureReport";
	const CMD_STORE = "cfgStore";
	const CMD_DELETEITEM = "cfgDelItem";
	const CMD_ADDITEM = "cfgAddItem";

	const CMD_STANDARD = "configureReport";

	/**
	 * @var \Closure
	 */
	protected $txt;

	/**
	 * @var \ilObjRepositoryReportsGUI
	 */
	protected $parent;


	public function __construct($parent, \Closure $txt) {
		global $tpl, $ilCtrl, $ilTabs, $ilDB;
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gTabs = $ilTabs;

		$this->gDB = $ilDB;

		$this->parent = $parent;
		$this->actions = $actions;
		$this->txt = $txt;

	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();

		switch ($cmd) {
			case self::CMD_STORE:
			case self::CMD_ADDITEM:
			case self::CMD_DELETEITEM:
				$this->$cmd();
				break;

			case self::CMD_CONFIG:
			default:
				$cmd = self::CMD_STANDARD;
				$this->$cmd();
		}
	}


	public function configureReport() {
		$form = new \ilPropertyFormGUI();
		$formvalues = array();

		$settings = $this->readDB();
		foreach ($settings as $setting) {

			$repfield = new Settings\ilReportFieldDefinitionGUI(
				$setting->id(), //name
				$setting->id() //postvar
			);
			$form->addItem($repfield);

			$formvalues[$setting->id()] = array(
				'title' => $setting->title(),
				'type' => $setting->type(),
				'ref_id' => $setting->ref_id(),
			);
		}

		$form->setValuesByArray($formvalues);

		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->addCommandButton(self::CMD_DELETEITEM, "delete items");
		$form->addCommandButton(self::CMD_ADDITEM, "(save and) add item");
		$form->addCommandButton(self::CMD_STORE, "save");

		$this->gTpl->setContent($form->getHtml());

	}


	public function cfgStore() {
		$post = $_POST;
		$settings = $this->extractRFPostValues($post);
		$this->storeToDB($settings);

		$cmd = self::CMD_CONFIG;
		$this->$cmd();
	}

	public function cfgAddItem() {
		$post = $_POST;
		$settings = $this->extractRFPostValues($post);

		//get next field id
		$db = new Settings\ilDB($this->gDB);
		$nu_id = $db->getNextFieldFor($this->parent->getObjId());

		//add a blank setting
		$setting = new Settings\RepositoryReportsSetting (
			$nu_id,
			'-',
			'blank',
			''
		);
		array_push($settings, $setting);
		$this->storeToDB($settings);

		$cmd = self::CMD_CONFIG;
		$this->$cmd();
	}

	public function cfgDelItem() {
		$post = $_POST;
		$mark = array();

		foreach ($post as $key => $value) {
			if(substr($key, -7) === '_delete') {
				$len = strlen($key);
				$var = substr($key, 0, $len - 7);
				array_push($mark, $var);
			}
		}

		$this->deleteFromDB($mark);

		$cmd = self::CMD_CONFIG;
		$this->$cmd();
	}


	/**
	* @return array <Settings\RepositoryReportsSetting>
	*/
	private function readDB() {
		$db = new Settings\ilDB($this->gDB);
		$values = $db->selectFor($this->parent->getObjId());
		if($values === false) {
			$values = array();
		}
		return $values;
	}

	/**
	* @param $settings array <Settings\RepositoryReportsSetting>
	*/
	private function storeToDB($settings) {
		$db = new Settings\ilDB($this->gDB);
		$db->update($this->parent->getObjId(), $settings);
	}


	private function deleteFromDB($field_ids) {
		$db = new Settings\ilDB($this->gDB);
		$db->deleteFor($this->parent->getObjId(), $field_ids);
	}


	/**
	* @return array <Settings\RepositoryReportsSetting>
	*/
	private function extractRFPostValues($post) {
		$ret = array();
		$values = array();
		foreach ($post as $key => $value) {
			if(substr($key, -3) === '_rf') {
				$len = strlen($key);
				$var = substr($key, 0, $len - 9);
				$suffix = substr($key, -8, 5);
				if(! array_key_exists($var, $values))  {
					$values[$var] = array();
				}
				$values[$var][$suffix] = $value;
			}
		}

		foreach ($values as $id => $data) {
			$setting = new Settings\RepositoryReportsSetting (
					$id,
					$data['title'],
					$data['ftype'],
					$data['refid']
				);
			array_push($ret, $setting);
		}
		return $ret;
	}


}