<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

class ilReportGUI {

	const CMD_EXECREPORT = "executeReport";
	const CMD_STANDARD = "selectReport";

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
			case self::CMD_EXECREPORT:
				//which report?
				$selected_report = @$_POST['report'];
				if($selected_report) {
					$this->$cmd($selected_report);
				}

				break;

			case 'reportGUI':
			default:
				$cmd = self::CMD_STANDARD;
				$this->$cmd();

		}


	}


	private function selectReport() {
		$form = new \ilPropertyFormGUI();
		//$form->addCommandButton(self::CMD_EXECREPORT, $this->txt("generate_report"));
		$form->addItem($this->reportSelectionInput());
		$form->addCommandButton(self::CMD_EXECREPORT, "generate_report");
		$form->setFormAction($this->gCtrl->getFormAction($this));

		$this->gTpl->setContent($form->getHtml());
	}


	private function reportSelectionInput() {

		//$si = new \ilSelectInputGUI($this->txt("possible_packages"), ilActions::F_PACKAGE);
		$options = $this->availableReports();
		//label, name
		$si = new \ilSelectInputGUI("possible reports", 'report');
		$si->setOptions($options);
		return $si;
	}


	private function availableReports() {
		return array(
			'dummyreport' => 'Dummy',
			'dummyreport2' => 'Dummy2'
		);
	}

	private function executeReport($report) {

		$this->gTpl->setContent($report);

	}

	private function exportXLS(array $titles, array $rowdata) {

	}

}