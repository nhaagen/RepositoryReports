<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("class.ilReportFieldDefinitionGUI.php");

use CaT\Plugins\RepositoryReports\Settings;

/**
 *
 * @ilCtrl_Calls ilReportConfigGUI: ilFormPropertyDispatchGUI
 */

class ilReportConfigGUI {

	const CMD_EDITPROPS = "editProperties";
	const CMD_SAVEPROPS = "saveProperties";

	const CMD_CONFIG = "configureReport";
	const CMD_STORE = "cfgStore";
	const CMD_DELETEITEM = "cfgDelItem";
	const CMD_ADDITEM = "cfgAddItem";

	const CMD_STANDARD = "editProperties";

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

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	protected function txt($code) {
		assert('is_string($code)');
		$txt = $this->txt;
		return $txt($code);
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		$this->setSubTabs();

		switch ($cmd) {
			case self::CMD_EDITPROPS:
			case self::CMD_SAVEPROPS:

			case self::CMD_CONFIG:
			case self::CMD_STORE:
			case self::CMD_ADDITEM:
			case self::CMD_DELETEITEM:

				$this->$cmd();
				break;

			default:
				$cmd = self::CMD_STANDARD;
				$this->$cmd();
		}
	}


	protected function setSubTabs() {
		$this->gTabs->addSubTab(self::CMD_EDITPROPS, $this->txt('settings'), $this->gCtrl->getLinkTarget($this->parent, self::CMD_EDITPROPS));
		$this->gTabs->addSubTab(self::CMD_CONFIG, $this->txt('report_config'), $this->gCtrl->getLinkTarget($this->parent, self::CMD_CONFIG));
	}


	protected function editProperties($form = null) {
		$this->gTabs->setSubTabActive(self::CMD_EDITPROPS);
		if($form === null) {
			$form = $this->initForm();
			$object = $this->parent->object;
			$values = array(
				'title' => $object->getTitle(),
				'description' => $object->getDescription()
			);
			$form->setValuesByArray($values);
		}

		$form->addCommandButton(self::CMD_SAVEPROPS, $this->txt("save"));
		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->setTitle($this->txt('obj_edit_settings'));

		$this->gTpl->setContent($form->getHtml());
	}

	protected function saveProperties() {
		$post = $_POST;
		$form = $this->initForm();
		if(!$form->checkInput()) {
			$form->setValuesByPost();
			\ilUtil::sendFailure($this->txt("not_saved"), true);
			$this->editProperties($form);
			return;
		}

		$obj= $this->parent->object;
		$obj->setTitle($post['title']);
		$obj->setDescription($post['description']);
		$obj->update();
		$this->gCtrl->redirect($this, self::CMD_EDITPROPS);
	}


	protected function initForm() {
		$form = new \ilPropertyFormGUI();

		$ti = new \ilTextInputGUI($this->txt('obj_title'), 'title');
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new \ilTextAreaInputGUI($this->txt('obj_description'), 'description');
		$form->addItem($ta);

		return $form;
	}




	public function configureReport() {
		$this->gTabs->setSubTabActive(self::CMD_CONFIG);
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
				'value' => $setting->value(),
			);
		}

		$form->setValuesByArray($formvalues);

		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->addCommandButton(self::CMD_DELETEITEM, "delete items");
		$form->addCommandButton(self::CMD_ADDITEM, "(save and) add item");
		$form->addCommandButton(self::CMD_STORE, "save");
		$form->setTitle($this->txt('obj_config_report'));
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
					$data['value']
				);
			array_push($ret, $setting);
		}
		return $ret;
	}


}