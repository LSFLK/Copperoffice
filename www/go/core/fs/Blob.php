<?php

namespace go\core\fs;

use go\core\db\Query;
use go\core\orm;
use go\core\util\DateTime;
use function GO;

/**
 * Blob entity
 * 
 * Group Office has a BLOB system to store files. When uploading a file a unique 
 * hash is calculated for the file to identify it. So when the same file is 
 * stored more than once in Group Office it will only be saved to disk once. You 
 * don’t have to worry about uploading or downloading the data. Because this has 
 * already been implemented for you.
 * 
 * In the database you must store the BLOB id in a BINARY (40) type column.
 * 
 * Warning
 * It’s very important that a foreign key constraint is defined for the BLOB id 
 * when it’s used in a table. Because the garbage collection mechanism uses 
 * these keys to determine if a BLOB is stale and to be cleaned up. In other 
 * words if you don’t do this your BLOB data will be removed automatically.
 * 
 * A blob can be downloaded with download.php?blob=HASH. It can be uploaded via
 * upload.php with HTTP.
 * 
 * @link https://groupoffice-developer.readthedocs.io/en/latest/blob.html
 */
class Blob extends orm\Entity {

	/**
	 * The 20 character blob hash
	 * 
	 * @var string 
	 */
	public $id;
	
	/**
	 * Content type
	 * 
	 * @var string eg. text/plain
	 */
	public $type;

	/**
	 * File name of the hash (first upload)
	 * 
	 * @var string 
	 */
	public $name;
	
	/**
	 * Blob size in bytes
	 * @var int
	 */
	public $size;
	
	/**
	 * Modified at
	 * 
	 * @var DateTime
	 */
	public $modifiedAt;
	
	/**
	 * Creation time
	 * 
	 * blob is created when uploaded for the first time
	 * 
	 * @var DateTime
	 */
	public $createdAt; 
	
	/**
	 * Blob can be deleted after this date
	 * 
	 * @var DateTime
	 */
	public $staleAt;
	
	private $tmpFile;
	private $strContent;
	

	/**
	 * Get all table columns referencing the core_blob.id column.
	 * 
	 * It uses the 'information_schema' to read all foreign key relations.
	 * So it's important that every blob is saved in a column with a 'RESTRICT'
	 * foreign key relation to core_blob.id. For example:
	 * 
	 * ```
	 * ALTER TABLE `addressbook_contact`
	 *    ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`);
	 * ```
	 * @link https://groupoffice-developer.readthedocs.io/en/latest/blob.html
	 * @return array [['table'=>'foo', 'column' => 'blobId']]
	 */
	public static function getReferences() {
		
		$refs = GO()->getCache()->get("blob-refs");
		if(!$refs) {
			$dbName = GO()->getDatabase()->getName();
			GO()->getDbConnection()->exec("USE information_schema");
			//somehow bindvalue didn't work here
			$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where constraint_schema=" . GO()->getDbConnection()->getPDO()->quote($dbName) . " and referenced_table_name='core_blob' and referenced_column_name = 'id'";
			$stmt = GO()->getDbConnection()->getPDO()->query($sql);
			$refs = $stmt->fetchAll(\PDO::FETCH_ASSOC);		
			GO()->getDbConnection()->exec("USE `" . $dbName . "`");			
			
			GO()->getCache()->set("blob-refs", $refs);			
		}		
		
		return $refs;
	}
	
	/**
	 * Check if this blob is used in a database table
	 * 
	 * It uses foreign key relations to check this.
	 * 
	 * @return boolean
	 */
	public function isUsed() {
		$refs = $this->getReferences();	
		
		$exists = false;
		foreach($refs as $ref) {
			$exists = (new Query)
							->selectSingleValue($ref['column'])
							->from($ref['table'])
							->where($ref['column'], '=', $this->id)
							->single();
			
			if($exists) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Set the blob stale if it's not used in any of the referencing tables.
	 * 
	 * @return bool true if blob is stale
	 */
	public function setStaleIfUnused() {		
		$this->staleAt = $this->isUsed() ? null : new DateTime();
		
		if(!$this->save()) {
			throw new \Exception("Couldn't save blob");
		}
		return isset($this->staleAt);
	}
	
	/**
	 * Create from temporary file.
	 * 
	 * The blob needs to be saved.
	 * 
	 * @param \go\core\fs\File $file
	 * @return \self
	 */
	public static function fromTmp(File $file) {
		$hash = bin2hex(sha1_file($file->getPath(), true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = $file->getSize();
			//$blob->staleAt = new DateTime("+1 hour");
		}
		$blob->name = $file->getName();
		$blob->tmpFile = $file->getPath();
		$blob->type = $file->getContentType();
		$blob->modifiedAt = $file->getModifiedAt();
		return $blob;
	}
	
	/**
	 * Create from string
	 * 
	 * @param string $string
	 * @return \self
	 */
	public static function fromString($string) {
		$hash = bin2hex(sha1($string, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = mb_strlen($string, '8bit');
			$blob->strContent = $string;
			//$blob->staleAt = new DateTime("+1 hour");
		}
		return $blob;
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_blob', 'b');
	}
	
	/**
	 * @return MetaData
	 */
	public function getMetaData() {
		return new MetaData($this);
	}

	protected function internalSave() {
		if (!is_dir(dirname($this->path()))) {
			mkdir(dirname($this->path()), 0775, true);
		}
		if (!file_exists($this->path())) { 
			if (!empty($this->tmpFile)) {
				rename($this->tmpFile, $this->path());
			} else if (!empty($this->strContent)) {
				file_put_contents($this->path(), $this->strContent);
			}
		}
		
		return parent::internalSave();
	}
	
	/**
	 * Checks if blob is in use. If it's used it will not delete but return true.
	 * It will remove the file on disk.
	 * 
	 * @return boolean
	 */
	protected function internalDelete() {
		
		//Check if blob is in use.
		if(!$this->deleteHard && $this->isUsed()) {
			GO()->debug("Not deleting blob because it's in use");
			return true;
		}
		
		if(parent::internalDelete()) {
			if(is_file($this->path())) {
				unlink($this->path());
			}
			return true;
		}		
	}
	
	private $deleteHard = false;
	
	/**
	 * Delete without checking isUsed()
	 * 
	 * It will throw an PDOException if you call this when it's in use.
	 * 
	 * @return true
	 */
	public function hardDelete() {
		$this->deleteHard = true;
		return $this->delete();
	}

	/**
	 * Return file system path of blob data
	 * 
	 * @return string
	 */
	public function path() {
		$dir = substr($this->id,0,2) . '/' .substr($this->id,2,2). '/';
		return GO()->getDataFolder()->getPath() . '/data/'.$dir.$this->id;
	}
	
	/**
	 * Get blob data as file system file object
	 * 
	 * @return File
	 */
	public function getFile() {
		return new File($this->path());
	}
	
	/**
	 * Get a blob URL
	 * 
	 * @param string $blobId
	 * @return string
	 */
	public static function url($blobId) {
		return GO()->getSettings()->URL . 'api/download.php?blob=' . $blobId;
	}
	
	/**
	 * Parse blob id's inserted as images in HTML content.
	 * 
	 * @param string $html
	 * @return string[] Array of blob ID's
	 */
	public static function parseFromHtml($html) {
		if(!preg_match_all('/<img .*?src=".*?blob=(.*?)".*?>/i', $html, $matches)) {
			return [];
		}
		
		return array_unique($matches[1]);
	}
	
	/**
	 * Find image tags with a blobId download URL in "src" and replace them with a 
	 * new "src" attribute.
	 * 
	 * Useful when attaching inline images for example:
	 * 
	 * ````
	 * $blobIds = \go\core\fs\Blob::parseFromHtml($body);
	 * foreach($blobIds as $blobId) {
	 * 	$blob = \go\core\fs\Blob::findById($blobId);
	 * 	
	 * 	$img = \Swift_EmbeddedFile::fromPath($blob->getFile()->getPath());
	 * 	$img->setContentType($blob->type);
	 * 	$contentId = $this->embed($img);
	 * 	$body = \go\core\fs\Blob::replaceSrcInHtml($body, $blobId, $contentId);
	 * }
	 * 
	 * @param string $html The HTML subject
	 * @param string $blobId The blob ID to find and replace
	 * @param string $newSrc The new "src" attribute for the blob
	 * @return string Replaced HTML
	 */
	public static function replaceSrcInHtml($html, $blobId, $src) {		
		return preg_replace('/(<img .*?src=").*?blob='.$blobId.'(".*?>)/i', '$1'.$src.'$2', $html);
	}
	
	/**
	 * Output for download
	 */
	public function output() {
		$this->getFile()->output(true, true, [
			'Content-Type' => $this->type, 
			"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s"),
			'Content-Disposition' => 'attachment; filename="' . $this->name . '"'
					]);
	}
}
