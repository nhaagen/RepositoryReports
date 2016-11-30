<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilRepositoryReportsPlugin extends ilRepositoryObjectPlugin {
	/**
	 * Get the name of the Plugin
	 *
	 * @return string
	 */
	function getPluginName() {
		return "RepositoryReports";
	}

	/**
	 * Defines custom uninstall action like delete table or something else
	 */
	protected function uninstallCustom() {
	}


	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure() {
		return function($code) {
			return $this->txt($code);
		};
	}
}