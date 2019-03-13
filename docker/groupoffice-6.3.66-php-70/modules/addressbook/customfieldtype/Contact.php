<?php

namespace GO\Addressbook\Customfieldtype;


class Contact extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'Contact';
	}
	
	public static function getModelName() {
		return 'GO\Addressbook\Model\Contact';
	}
	
	public function includeInSearches() {
		return true;
	}

	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$html="";
		if(!empty($attributes[$key])) {
			$id = $this->getId($attributes[$key]);
			if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport && !empty($id)){
				$name = htmlspecialchars($this->getName($attributes[$key]), ENT_COMPAT, 'UTF-8');
				$html='<a href="#contact/'.
					$id.'" title="'.$name.'">'.
						$name.'</a>';
			}else
			{
				$html=$this->getName($attributes[$key]);
			}
		}
		return $html;
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		
		if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
			return parent::formatFormOutput($key, $attributes, $model);
		}else
		{
			return $this->getName($attributes[$key]);
		}		
	}	
}
