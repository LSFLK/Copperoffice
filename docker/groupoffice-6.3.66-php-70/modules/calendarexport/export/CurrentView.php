<?php
namespace GO\Calendarexport\Export;

use GO\Base\Model\AbstractExport;

class CurrentView extends AbstractExport {
	
	/**
	 * This is a grid export that needs the key for the statement that is saved in the session
	 * 
	 * @var StringHelper 
	 */
	public $queryKey = 'events';
	
	/**
	 * Which views are supported by this export
	 *  
	 * @return array
	 */
	public function getSupportedViews(){
		return array(
				AbstractExport::VIEW_CSV,
				AbstractExport::VIEW_PDF,
				AbstractExport::VIEW_XLS,
				AbstractExport::VIEW_HTML
		);
	}

	
}
