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

		$form->addCommandButton(self::CMD_ADDITEM, "(save and) add item");
		$form->addCommandButton(self::CMD_STORE, "save");
		$form->setFormAction($this->gCtrl->getFormAction($this));

		$this->gTpl->setContent($form->getHtml());

	}


	private function readDB() {
		$db = new Settings\ilDB($this->gDB);
		$values = $db->selectFor($this->parent->getObjId());
		return $values;
	}

	/**
	* @param $settings array <Settings\RepositoryReportsSetting>
	*/
	private function storeToDB($settings) {
		$db = new Settings\ilDB($this->gDB);
		$db->update($this->parent->getObjId(), $settings);

	}



	public function cfgStore() {
		$post = $_POST;
		$settings = $this->extractRFPostValues($post);
		$this->storeToDB($settings);

		$cmd = self::CMD_CONFIG;
		$this->$cmd();
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