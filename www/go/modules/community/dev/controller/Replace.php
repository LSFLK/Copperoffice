<?php
namespace go\modules\community\dev\controller;

class Replace extends \go\core\Controller {
	
//	public function ns() {
//		
//		
//		$allJSFiles = GO()->getEnvironment()->getInstallFolder()->find("/^.*\.js$/", false, true);
//		
//		
//		$folder = GO()->getEnvironment()->getInstallFolder()->getFolder("go/core/views/extjs3");
//		$modFolders = $folder->getChildren(false);
//		
//		foreach($modFolders as $f) {
//			$ns = 'go.'. $f->getName();
//			
//			foreach($f->getFiles() as $file) {
//				if($file->getExtension() !== 'js') {
//					continue;
//				}
//				
//				$oldCls  = "go.cron." .$file->getNameWithoutExtension();
//				$newCls = $ns . '.' .$file->getNameWithoutExtension();
//				
//				echo "Replace " .$oldCls . " -> " . $newCls ."\n";
//				
//				array_map(function($file) use ($oldCls, $newCls) {
//					$file->replace($oldCls, $newCls);
//				}, $allJSFiles);
//			}
//		}	
//		
//	}
}
