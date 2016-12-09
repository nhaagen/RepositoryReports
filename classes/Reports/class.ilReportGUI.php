<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
use CaT\Plugins\RepositoryReports\Reports;
use CaT\Plugins\RepositoryReports\Settings;

class ilReportGUI {

	const CMD_EXECREPORT = "executeReport";

	const CMD_REPORTHTML = "showHTMLReport";
	const CMD_REPORTXLS = "xlsReport";
	const CMD_STANDARD = "showSelection";

	const XLSFILENAME = 'report.xls';

	/**
	 * @var \Closure
	 */
	protected $txt;

	/**
	 * @var \ilObjRepositoryReportsGUI
	 */
	protected $parent;

	/**
	 * @var gDB \ilDB
	 */
	private $gDB;

	/**
	 * @var data \Reports\ReportData
	 */
	private $data;

	public function __construct($parent, \Closure $txt) {
		global $tpl, $ilCtrl, $ilTabs, $ilDB;
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gTabs = $ilTabs;
		$this->gDB = $ilDB;

		$this->parent = $parent;
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

	private function initDataAccessor() {
		$obj_id = $this->parent->getObjId();
		$settingdb = new Settings\ilDB($this->gDB);
		$settings = $settingdb->selectFor($obj_id);
		$this->data = new Reports\ilReportData($this->gDB, $settings);
	}


	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();

		switch ($cmd) {
			case self::CMD_REPORTHTML:
			case self::CMD_REPORTXLS:
				if(! $this->data) {
					$this->initDataAccessor();
				}
				$this->$cmd();
				break;

			default:
				$cmd = self::CMD_STANDARD;
				$this->$cmd();
		}
	}



	private function showSelection() {
		$form = new \ilPropertyFormGUI();
		$form->setFormAction($this->gCtrl->getFormAction($this));

		$form->setTitle($this->txt('obj_report_title'));
		$form->setDescription($this->parent->object->getDescription());

		$form->addCommandButton(self::CMD_REPORTHTML, $this->txt("show_report"));
		$form->addCommandButton(self::CMD_REPORTXLS, $this->txt("xls_report"));
		$this->gTpl->setContent($form->getHtml());
	}

	private function showHTMLReport() {
		//add back-button
		$titles = $this->data->getRow();
		$rowdata = $this->data->getRowData();
		$this->gTpl->setContent($this->htmlTable($titles, $rowdata));
	}

	private function xlsReport() {
		$titles = $this->data->getRow();
		$rowdata = $this->data->getRowData();
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
		return self::XLSFILENAME;
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

}
