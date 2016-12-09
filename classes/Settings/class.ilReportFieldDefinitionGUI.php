<?php
namespace CaT\Plugins\RepositoryReports\Settings;
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* edit-line for report-field definition;
* inputs per row: title, type-selection and ref_id
*
* @author Nils Haagen <nils.haagen@concepts-and-training.de>
*
* @ingroup	ServicesForm
*/
class ilReportFieldDefinitionGUI extends \ilFormPropertyGUI {

	protected $value;

	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("repfield");
		$this->setValue(array(
			'title' => '',
			'type' => 'fix',
			'value' => ''
		));
	}


	private function buildSelect() {
		$options = RepositoryReportsSetting::validTypes();

		$html = '<select '
			.' name="' .$this->getFieldId() .'_ftype_rf">';

		foreach ($options as $opt) {
			$html .= '<option value="'.$opt .'"';
			if($this->getValue()['type'] === $opt) {
				$html .= 'selected';
			}
			$html .= '>';
			$html .= $opt; //translate
			$html .= '</option>';
		}
		$html .= '</select>';
		return $html;
	}


	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl){

		$inpt_chkbox = '<input type="checkbox"'
			.' name="' .$this->getFieldId() .'_delete"'
			.' />';

		$inpt_title = '<input type="text"'
			.' name="' .$this->getFieldId() .'_title_rf"'
			.' value="' .$this->getValue()['title'] .'"'

			.'/>';

		$inpt_ftype = $this->buildSelect();

		$inpt_value = '<input type="text"'
			.' name="' .$this->getFieldId() .'_value_rf"'
			.' value="' .$this->getValue()['value'] .'"'
			.'/>';

		$html = join('&nbsp;', array(
			$inpt_chkbox,
			$inpt_title,
			$inpt_ftype,
			$inpt_value
		));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* @param array $value 	array with keys: title, type, value
	*/
	public function setValue($value) {
		//check validity!
		$this->value = $value;
	}
	public function getValue() {
		return $this->value;
	}
	public function setValueByArray($values) {
		$this->setValue($values[$this->getPostVar()]);
	}


}