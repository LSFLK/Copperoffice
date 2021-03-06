<?php
namespace go\modules\community\addressbook;

use go\core\http\Response;
use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\UserSettings;
use go\core\model\Link;
use go\core\model\User;
							
/**						
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 * 
 * @todo 
 * filters
 * Merge
 * Batch edit
 * Export
 * Import
 * Carddav
 * Document templates
 * ActiveSync
 * Migration
 * Send newsletter
 * 
 * 
 * 
 */
class Module extends core\Module {
							
	public function getAuthor() {
		return "Intermesh BV <info@intermesh.nl>";
	}

	
	public function defineListeners() {
		parent::defineListeners();
		
		Link::on(Link::EVENT_DELETE, Contact::class, 'onLinkSaveOrDelete');
		Link::on(Link::EVENT_SAVE, Contact::class, 'onLinkSaveOrDelete');
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public function downloadVCard($contactId) {
		$contact = Contact::findById($contactId);
		
		$c = new VCard();
		
		$vcard =  $c->export($contact);		
		
		Response::get()
						->setHeader('Content-Type', 'text/vcard;charset=utf-8')
						->setHeader('Content-Disposition', 'attachment; filename="'.$contact->name.'.vcf"')
						->setHeader("Content-Length", strlen($vcard))
						->sendHeaders();
		
		echo $vcard;
	}
	
	
	public static function onMap(Mapping $mapping) {
		$mapping->addRelation('addressBookSettings', UserSettings::class, ['id' => 'userId'], false);
	}
							
}