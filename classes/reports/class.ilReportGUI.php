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
		//property-label, input name
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
		$titles = $this->getRow();
		$rowdata = $this->getRowData();
		$this->gTpl->setContent($this->htmlTable($titles, $rowdata));
		$this->exportXLS($titles, $rowdata);
	}




	private function htmlTable(array $titles, array $rowdata) {
		$buf = '<table border=1>';
		$buf .= '<tr><th>';
		$buf .= join('</th><th>', array_values($titles));
		$buf .= '</th></tr>';

		foreach ($rowdata as $row) {
			$buf .= '<tr><td>';
			$buf .= join('</td><td>', $row);
			$buf .= '</td></tr>';
		}
		$buf .= '</table>';
		return $buf;
	}


	private function getExportFilename() {
		return 'report.xls';
	}

	private function exportXLS(array $titles, array $rowdata) {

		require_once "Services/Excel/classes/class.ilExcelUtils.php";
		require_once "Services/Excel/classes/class.ilExcelWriterAdapter.php";

		$fname = $this->getExportFilename();

		ob_clean();
		$adapter = new ilExcelWriterAdapter($fname, true);
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		//available formats within the sheet
		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();

		//init cols and write titles
		$colcount = 0;
		foreach ($titles as $colkey=>$title) {
			$worksheet->setColumn($colcount, $colcount, 30); //width
			$worksheet->writeString(0, $colcount, $title, $format_bold);
			$colcount++;
		}

		//write data-rows
		$rowcount = 1;
		foreach ($rowdata as $entry) {
			$colcount = 0;
			foreach ($titles as $colkey=>$title) {
				$v = $entry[$colkey];
				$worksheet->write($rowcount, $colcount, $v, $format_wrap);
				$colcount++;
			}
			$rowcount++;
		}

		$workbook->close();
	}











	private function getRow() {

		return array(
			'login' => 'AN-Nr.',
			'adress1' => 'Anrede',
			'adress2' => 'Anrede2',
			'firstname' => 'Vorname',
			'lastname' => 'Nachname',
			'email' => 'Mail',
			'x1' => 'PF8 in ProofCenter eingetragen',
			'x2' => 'Gesprächsergebnisse Anmerkungen',
			'x3' => 'Erinnerung',
			'f2fsem' => 'NBA Anmeldung Präsenzseminar',
			'x5' => 'NBA Angemeldet am',
			'x6' => 'Starterpaket an AN',
			'phase1' => 'SLP1',
			'phase2' => 'SLP2',
			'f2f' => 'Präsenzseminar',
			'test' => 'Online-Test',
			'center' => 'Übungscenter',
		);
	}


	private function getRowData() {
		$ret = array();
		$settings = array(
			//internal_name, ref_id
			'phase1' => 75,
			'phase2' => 117,
			'test' => 133,
			'center' => 138,
			'f2f_std' => 166,
			'f2f_children' => 166,
		);

		$users = $this->getAllUsers();

		foreach ($users as $user) {
			//userdata
			$l = $this->getRow();
			$l['x1'] = '';
			$l['x2'] = '';
			$l['x3'] = '';
			$l['x4'] = '';
			$l['x5'] = '';
			$l['x6'] = '';

			foreach(array_keys($user) as $k) {
				if(in_array($k, array_keys($l))) {
					$l[$k] = $user[$k];
				}
			}
			$l['adress1'] = ($user['gender'] === 'm') ? 'Herr' : 'Frau';
			$l['adress2'] = ($user['gender'] === 'm') ? 'Sehr geehrter Herr' : 'Sehr geehrte Frau';

			//learningprogress
			$l['phase1'] = $this->getLearningProgressInObject($settings['phase1'], $user['usr_id']);
			$l['phase2'] = $this->getLearningProgressInObject($settings['phase2'], $user['usr_id']);
			$l['test'] = $this->getLearningProgressInObject($settings['test'], $user['usr_id']);
			$l['center'] = $this->getLearningProgressInObject($settings['center'], $user['usr_id']);

			//get f2fsem of user
			$m1 = $this->getMembershipsOfUsersBelowRef($settings['f2f_std'], $user['usr_id']);
			$m2 = $this->getMembershipsOfUsersBelowRef($settings['f2f_children'], $user['usr_id']);
			$f2fref = -1;
			if(count($m1) > 0) {
				$f2fref = $m1[0]['t_ref_id'];
			} else {
				if(count($m2) > 0) {
					$f2fref = $m2[0]['t_ref_id'];
				}
			}
			if($f2fref > -1) {
				$l['f2fsem'] = $this->getTitleOfObject($f2fref);
				$l['f2f'] = $this->getLearningProgressInObject($f2fref, $user['usr_id']);
			} else {
				$l['f2fsem'] = '-';
				$l['f2f'] = '-';
			}
			array_push($ret, $l);
		}
		return $ret;
	}


	/**
	* get all users
	*/
	private function getAllUsers() {
		global $ilDB;
		$ret = array();

		$sql = "SELECT usr_id, login, gender, firstname, lastname, email "
			." FROM usr_data"
			." INNER JOIN object_data ON usr_data.usr_id = object_data.obj_id"
			." WHERE object_data.type = 'usr'"
			." AND object_data.obj_id NOT IN(6, 13)";
		$res = $ilDB->query($sql);
		while ($rec = $ilDB->fetchAssoc($res)){
			array_push($ret, $rec);
		}
		return $ret;
	}
	/**
	* get all memberships of user in courses below the ref
	*/
	private function getMembershipsOfUsersBelowRef($ref_id, $usr_id) {
		global $ilDB;
		global $tree;
		$children = $tree->getChilds($ref_id, "title");
		$valid_roles = array();
		foreach ($children as $child) {
			$rol = 'il_crs_member_' . $child['ref_id'];
			array_push($valid_roles, $rol);
		}

		$ret = array();

		$sql = "SELECT ua.usr_id, od.title FROM rbac_ua ua"
			." JOIN rbac_fa fa ON fa.rol_id = ua.rol_id"
			." JOIN object_data od ON od.obj_id = ua.rol_id"
			." WHERE od.title IN ('"
			.join("', '", $valid_roles)
			."')"
			." AND ua.usr_id = " . $usr_id
			;

		$res = $ilDB->query($sql);
		while ($rec = $ilDB->fetchAssoc($res)){
			$t_ref_id = str_replace('il_crs_member_', '', $rec['title']);
			$rec['t_ref_id'] = $t_ref_id;
			array_push($ret, $rec);
		}
		return $ret;
	}

	/**
	* get learning progresses for all users in object
	*/
	private function getLearningProgressInObject($ref_id, $usr_id) {
		require_once('./Services/Tracking/classes/class.ilLPStatus.php');
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$status = \ilLPStatus::_lookupStatus($obj_id, $usr_id, false);

		switch ($status) {
			case \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
				return 'unknown';
			case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return 'incomplete';
			case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return 'passed';
			case \ilLPStatus::LP_STATUS_FAILED_NUM:
				return 'failed';
		}
	}

	private function getTitleOfObject($ref_id) {
		global $ilDB;
		$sql = "SELECT od.title FROM object_data AS od"
		." INNER JOIN object_reference AS oref ON od.obj_id = oref.obj_id"
		." WHERE oref.ref_id = " . $ref_id;
		$res = $ilDB->query($sql);
		$rec = $ilDB->fetchAssoc($res);
		return $rec['title'];
	}





}