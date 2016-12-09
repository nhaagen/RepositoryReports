<?php
namespace CaT\Plugins\RepositoryReports\Reports;
/**
 * Interface for data-retrieval
 */
interface ReportData {

	/**
	* get all columns with title for report
	*
	* @return array	<string, string> field_id => title
	*/
	public function getRow();

	/**
	* get row-data for a distinct user
	*
	* @return array <string, mixed>	field_id => value
	*/
	public function getRowData();

}