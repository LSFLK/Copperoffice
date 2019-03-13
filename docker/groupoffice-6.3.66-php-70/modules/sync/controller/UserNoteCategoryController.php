<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 *
 * @author WilmarVB <wilmar@intermesh.nl>
 */


namespace GO\Sync\Controller;


class UserNoteCategoryController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String 
	 */
	public function modelName() {
		return 'GO\Notes\Model\Category';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Sync\Model\UserNoteCategory';
	}
	
	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'category_id';
	}	
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm) {
		$cm->formatColumn('default_category', 'isset($model->default_category) ? intval($model->default_category) : 0');
		$cm->formatColumn('permission_level', '$model->permissionLevel');
		return parent::formatColumns($cm);
	}
}
