<?php

/**
 * Interface for DB handle of additional setting values
 */
interface DB {
	/**
	 * Install tables and standard contents and types.
	 */
	public function install();

	/**
	 * Update settings of an existing repo object.
	 *
	 * @param	<PLUGINNAME>		$settings
	 */
	public function update(<PLUGINNAME> $settings);

	/**
	 * Create a new settings object for <PLUGINNAME> object.
	 *
	 * @param	int		$obj_id
	 * @param	int		$study_content
	 * @param	int		$study_type
	 * @param	int		$credit_points
	 *
	 * @return \CaT\Plugins\<PLUGINNAME>\Settings\<PLUGINNAME>
	 */
	public function create($obj_id, /*additonal setting values*/);

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