<?php
namespace CaT\Plugins\RepositoryReports\Settings;
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a child of repository selector in a property form.
*
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
			'type' => 'blank',
			'ref_id' => -1
		));
	}


	private function buildSelect() {
		$options = array(
			"blank",
			"memberbelow",
			"learningprogress"
		);

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
	//	$html = $this->render();

		$inpt_chkbox = '<input type="checkbox"'
			.' name="' .$this->getFieldId() .'_delete"'
			.' />';

		$inpt_title = '<input type="text"'
			.' name="' .$this->getFieldId() .'_title_rf"'
			.' value="' .$this->getValue()['title'] .'"'

			.'/>';

		$inpt_ftype = $this->buildSelect();

		$inpt_refid = '<input type="text"'
			.' name="' .$this->getFieldId() .'_refid_rf"'
			.' value="' .$this->getValue()['ref_id'] .'"'
			.'/>';

		$html = join('&nbsp;', array(
			$inpt_chkbox,
			$inpt_title,
			$inpt_ftype,
			$inpt_refid
		));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* @param array $value 	array with keys: title, type, ref_id
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