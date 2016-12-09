<?php
use CaT\Plugins\RepositoryReports;
require_once(__DIR__."/autoload.php");
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Object of the plugin
 */
class ilObjRepositoryReports extends ilObjectPlugin implements RepositoryReports\ObjRepositoryReports {
	const PLUGIN_TYPE = "xrep";

	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType(self::PLUGIN_TYPE);
	}

	/**
	 * Gets called if the object get be updated
	 * Update additoinal setting values
	 */
	public function doUpdate() {

	}

	/**
	 * Gets called after object creation to read further information
	 */
	public function doRead() {

	}

	/**
	 * Gets called if the object should be deleted.
	 * Delete additional settings
	 */
	public function doDelete() {

	}

	/**
	 * Gets called if the object get be coppied.
	 * Copy additional settings to new object
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null) {

	}
}