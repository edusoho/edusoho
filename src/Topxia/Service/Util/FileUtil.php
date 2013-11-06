<?php

namespace Topxia\Service\Util;
use Symfony\Component\Filesystem\Filesystem;

class FileUtil
{
	
	// TODO 这个函数只能测试文件夹存在与否，不能测试文件存在与否
	public static function is_writable($path) 
	{
	    $path .= DIRECTORY_SEPARATOR.uniqid(mt_rand()).'.tmp';
	    $fileSystem = new FileSystem();
	    $rm = $fileSystem->exists($path);
	    $f = fopen($path, 'a');
	    if ($f===false)
	        return false;
	    fclose($f);
	    if (!$rm)
	        $fileSystem->remove($path);
	    return true;
	}
	
	public static function emptyDir($dirPath,$includeDir=false){
		$fileSystem = new FileSystem();
		if(!$fileSystem->exists($dirPath)) return ;
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    		$path->isFile() ? $fileSystem->remove($path->getPathname()) : rmdir($path->getPathname());
		}
		if($includeDir){
			rmdir($dirPath);
		}
	}

 	public static function deepCopy($src,$dest,array $patternMatch=null)
 	{
 		$fileSystem = new FileSystem();
		if(!$fileSystem->exists($src)) return ;
		if(!$fileSystem->exists($dest)){
			$fileSystem->mkdir($dest,0777) ;
		}
		$match = false;
		if(!empty($patternMatch) && count($patternMatch) == 2){
			$match = true;
		}
		$fileCount = 0;

		foreach(new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
			 \RecursiveIteratorIterator::SELF_FIRST ) as $path) {
			
			if($match && $patternMatch[0]->$patternMatch[1]($path->getPathname())){
					continue;
			}

			$relativeFile = str_replace($src,'',$path->getPathname());

			$destFile = $dest.$relativeFile;	
			if($path->isDir() ){
				if(!$fileSystem->exists($destFile))
					$fileSystem->mkdir($destFile,0777);
			}else{
				if(strpos( $path->getFilename(), ".") ===0 ){
					continue;
				}
				$fileSystem->copy($path->getPathname(),$destFile,true);
				$fileCount ++;
			}
		}
		return $fileCount;
 	}	

}
