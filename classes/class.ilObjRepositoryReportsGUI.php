<?php
/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 */
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/autoload.php");

require_once(__DIR__."/reports/class.ilReportGUI.php");

/**
* User Interface class for Jill repository object.
* ...
* @ilCtrl_isCalledBy ilObjRepositoryReportsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls      ilObjRepositoryReportsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls      ilObjRepositoryReportsGUI: ilRepositoryReportsSettingsGUI, ilPropertyFormGUI
* @ilCtrl_Calls      ilObjRepositoryReportsGUI: ilReportGUI
*
*/
class ilObjRepositoryReportsGUI extends ilObjectPluginGUI {
	const PLUGIN_TYPE = "xrep";
	const CMD_INFO = "showContent";
	const CMD_REPORTGUI = "reportGUI";


	const CMD_STANDARD = "showContent";

	/**
	 * Called after parent constructor. It's possible to define some plugin special values
	 */
	protected function afterConstructor() {
		global $tpl, $ilCtrl, $ilTabs, $ilUser, $ilToolbar, $ilAccess, $ilDB;

		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilTabs    ilTabsGUI
		 * @var $ilUser    ilObjUser
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gTabs = $ilTabs;
		$this->gUsr = $ilUser;
		$this->gToolbar = $ilToolbar;
		$this->gAccess = $ilAccess;
		$this->plugin = $this->object->plugin;

	}

	/*
	* Get type.  Same value as choosen in plugin.php
	*/
	final function getType() {
		return self::PLUGIN_TYPE;
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd) {
		switch ($cmd) {
			case self::CMD_REPORTGUI:
			case ilReportGUI::CMD_EXECREPORT:
				$this->gTabs->setTabActive(self::CMD_REPORTGUI);
				$gui = new ilReportGUI($this, $this->plugin->txtClosure());
				$this->gCtrl->forwardCommand($gui);
				//$this->gCtrl->redirectByClass("ilDummyReportGUI");

				break;

			default:
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd() {
		return "";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd() {
		return self::CMD_STANDARD;
	}


	/**
	* Set tabs
	*/
	protected function setTabs() {
		$this->gTabs->addTab(self::CMD_STANDARD, $this->txt('info'), $this->gCtrl->getLinkTarget($this, self::CMD_STANDARD));
		$this->gTabs->addTab(self::CMD_REPORTGUI, $this->txt('reports'), $this->gCtrl->getLinkTarget($this, self::CMD_REPORTGUI));

		parent::setTabs();
		return true;
	}


	/**
	* Show infoscreen
	*/
	function showContent() {
		$this->gTabs->setTabActive(self::CMD_INFO);

		include_once('./Services/InfoScreen/classes/class.ilInfoScreenGUI.php');
		$info = new ilInfoScreenGUI($this);
		$info->addObjectSections();
		$info->gui_object->object=false;
		$this->gTpl->setContent($info->getHTML());
	}



}