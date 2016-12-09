<?php
namespace CaT\Plugins\RepositoryReports\Reports;
use CaT\Plugins\RepositoryReports\Settings;
/**
 * collect data for reports
 */
class ilReportData implements ReportData {

	/**
	 * @var gDB \ilDB
	 */
	private $gDB;

	/**
	 * @var settings array <Settings\RepositoryReportsSetting>
	 */
	private $settings;


	public function __construct($db, $settings) {
		$this->gDB = $db;
		$this->settings = $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function getRow() {
		$ret = array();
		foreach ($this->settings as $setting) {
			$ret[$setting->id()] = $setting->title();
		}
		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function getRowData() {
		$ret = array();
		$users = $this->getAllUsers();
		foreach ($users as $user) {
			$row = $this->getRow();
			$usr_id = $user['usr_id'];

			foreach ($this->settings as $setting) {
				switch ($setting->type()) {
					case 'fix':
						$row[$setting->id()] = $setting->value();
						break;
					case 'memberbelow':
						$row[$setting->id()] = $this->rowValueMemberBelow($setting, $usr_id);
						break;
					case 'learningprogress':
						$row[$setting->id()] = $this->rowValueLP($setting, $usr_id);
						break;
					case 'lpmembership':
						$row[$setting->id()] = $this->rowValueLPMember($setting, $usr_id);
						break;
					case 'userfield':
						//TODO: add check
						$row[$setting->id()] = $user[$setting->value()];
						break;
				}

			}
			array_push($ret, $row);
		}
		return $ret;
	}

	private function rowValueMemberBelow(Settings\RepositoryReportsSetting $setting, $usr_id) {
		$crss = $this->getMembershipsOfUsersBelowRef($setting->value(), $usr_id);
		if(count($crss) > 0) {
			return $this->getTitleOfObject($crss[0]['t_ref_id']);
		}
		return '';
	}

	private function rowValueLP(Settings\RepositoryReportsSetting $setting, $usr_id) {
		return $this->getLearningProgressInObject($setting->value(), $usr_id);
	}

	private function rowValueLPMember(Settings\RepositoryReportsSetting $setting, $usr_id) {
		//get according setting
		$dependency_setting = false;
		foreach ($this->settings as $s) {
			if($s->id() == $setting->value()) {
				$dependency_setting = $s;
			}
		}
		if($dependency_setting === false) {
			return '#misconfigured#';
		}
		//is member?
		$crss = $this->getMembershipsOfUsersBelowRef($dependency_setting->value(), $usr_id);
		if(count($crss) == 0) {
			return '-';
		}
		return $this->getLearningProgressInObject($crss[0]['t_ref_id'], $usr_id);
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