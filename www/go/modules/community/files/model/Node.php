<?php
namespace go\modules\community\files\model;

use go\core\acl\model;
use go\core\db\Query;

class Node extends model\AclEntity {

	//use \go\core\orm\CustomFieldsTrait;
	use \go\core\orm\SearchableTrait;
	
	public $name;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $createdAt;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $modifiedAt;
	public $ownedBy;
	public $modifiedBy;
	public $isDirectory;
	public $bytesSize;
	public $contentType;
	
	public $comments;
	public $bookmarked;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $touchedAt;
	public $storageId;
	public $parentId;
	
	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('files_nodeview', 'node');
	}
	
	public function getLocation() {
		return '/biem/'.$this->name;
	}

	protected function getSearchDescription() {
		return $this->createdAt->format(\GO()->getUser()->date_format);
	}

	protected function getSearchName() {
		return $this->name;
	}
	
	/**
	 * Tells if this folder has children.
	 * 
	 * @return boolean
	 */
	public function getHasChildren(){
		$hasChild = self::find()->where(['parentId'=>$this->id,'isDirectory'=>true])->single();
		return $hasChild?true:false;
	}
	
	public static function filter(Query $query, array $filter) {
		
		// Add where usergroup is the personal group of the user
		if(isset($filter['isHome'])){
			$homeDir = \GO()->getUser()->storage->getRootFolder();
						
			if(!empty($filter['isHome'])){
				// We are querying the "home dir" of the current user
				$query->andWhere(['parentId' => $homeDir->id]);
			} else {
				// We are querying the "shared with me" dir of the current user
				$query->andWhere('parentId','!=',$homeDir->id);
				$query->andWhere('id','!=',0);
				$query->andWhere('storageId','!=',$homeDir->storageId);
			}
		}
		
		$filterableProperties = ['parentId', 'isDirectory'];
		foreach($filterableProperties as $prop) {
			if(isset($filter[$prop])) {
				$query->andWhere([$prop => $filter[$prop]]);
			}
		}
		return parent::filter($query, $filter);		
	}
	
	public function toArray($properties = array()) {
		$result = parent::toArray($properties);
		$unset = ($result['isDirectory']) ?
			['metaData', 'mimeType', 'byteSize', 'bloId', 'versions'] :
			['items', 'subscribed', 'canAddItems'];
		foreach($unset as $key) { 
			unset($result[$key]); 
		}
		return $result;
	}

}
