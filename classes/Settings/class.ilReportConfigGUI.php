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
		global $tpl, $ilCtrl, $ilTabs;
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gTabs = $ilTabs;

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

		$formvalues = $this->readDB();

		$repfield = new ilReportFieldDefinitionGUI('name', 'var1');
		$repfield2 = new ilReportFieldDefinitionGUI('name2', 'var2');

		$form->addItem($repfield);
		$form->addItem($repfield2);

		$form->setValuesByArray($formvalues);

		$form->addCommandButton(self::CMD_ADDITEM, "(save and) add item");
		$form->addCommandButton(self::CMD_STORE, "save");
		$form->setFormAction($this->gCtrl->getFormAction($this));

		$this->gTpl->setContent($form->getHtml());

	}


	private function readDB() {

		return array(
			'var1' => array('title'=>'sometitle', 'type'=>'learningprogress', 'ref_id'=>12),
			'var2' => array('title'=>'sometitle', 'type'=>'memberbelow', 'ref_id'=>33),

		);

	}

	public function cfgStore() {

		$post = $_POST;
		$post_extract = $this->extractRFPostValues($post);

		$this->gTpl->setContent(print_r($post_extract,1));
	}


	private function extractRFPostValues($post) {
		$ret = array();
		foreach ($post as $key => $value) {
			if(substr($key, -3) === '_rf') {
				$len = strlen($key);
				$var = substr($key, 0, $len - 9);
				$suffix = substr($key, -8, 5);
				if(! array_key_exists($var, $ret))  {
					$ret[$var] = array();
				}
				$ret[$var][$suffix] = $value;
			}
		}
		return $ret;

	}


}