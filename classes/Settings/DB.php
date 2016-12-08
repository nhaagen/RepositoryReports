<?php
namespace CaT\Plugins\RepositoryReports\Settings;
/**
 * Interface for DB to handle of additional settings
 */
interface DB {
	/**
	 * Install tables
	 */
	public function install();

	/**
	 * Update settings of an existing repo object.
	 *
	 * @param	<PLUGINNAME>		$settings
	 */
	public function update($obj_id, array $settings);

	/**
	 * return <PLUGINNAME> for $obj_id
	 *
	 * @param int $obj_id
	 *
	 * @return \CaT\Plugins\<PLUGINNAME>\Settings\<PLUGINNAME>
	 */
	public function selectFor($obj_id);

	/**
	 * Delete all information of the given obj id
	 *
	 * @param 	int 	$obj_id
	 */
	public function deleteFor($obj_id);
}