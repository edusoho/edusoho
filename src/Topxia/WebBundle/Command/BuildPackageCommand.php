<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;



class BuildPackageCommand extends BaseCommand
{
	private $fileSystem;

	protected function configure()
	{
		$this
            ->setName('topxia:build-package')
            ->setDescription('编制升级包')
            ->addArgument('name', InputArgument::REQUIRED, 'package name')
            ->addArgument('version', InputArgument::REQUIRED, 'which version to update')
            ->addArgument('diff_file', InputArgument::REQUIRED, 'Where is Diff file of both versions');
        ;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$output->writeln('<info>开始编制升级包</info>');
		$name = $input->getArgument('name');
		$version = $input->getArgument('version');
		$diff_file = $input->getArgument('diff_file');
		
		$path = $this->createDirectory($name,$version);

		$this->generateFiles($diff_file,$path);





		$output->writeln('<info>编制升级包完毕</info>');
	}

	private function generateFiles($diff_file,$path)
	{
		$file = @fopen($diff_file, "r") ;  
		while (!feof($file))
		{
		    $currentLine = fgets($file) ;
		    if($currentLine[0]=='M' || $currentLine[0]=='A'){
		    	echo "增加更新文件：{$currentLine}";
		    	$this->copyFileAndDir($currentLine,$path);
		    }else if($currentLine[0]=='D'){
		    	echo "增加删除文件：{$currentLine}";
		    	$this->insertDelete($currentLine,$path);
		    }else{
			    echo "无法处理该文件：{$currentLine}";
			}

		}   
	}

	private function insertDelete($line,$path)
	{
		$file = trim(substr($line,1)," ");
		$destPath = $path.'/delete';
		$f = fopen($destPath,'a+'); 
		fwrite($f,$file,strlen($file)); 
		fclose($f);
	}

	private function copyFileAndDir($line,$path)
	{
		$file = trim(substr($line,1),"\n  \t\r");
		$destPath = $path.'/source/'.$file;
		if(!file_exists(dirname($destPath))){
			mkdir(dirname($destPath), 0777, true);
		}
		copy($file, $destPath);
	}

	private function createDirectory($name,$version)
	{
		$path = 'build/'.$name.'_'.$version.'/';

		if(!file_exists($path )){
			mkdir($path,0777,true);
		}else{
			$this->emptyDir($path);
		}
		return $path;
	}
	private function emptyDir($dirPath,$includeDir=false){
		if(!$this->getFileSystem()->exists($dirPath)) return ;
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    		$path->isFile() ? $this->getFileSystem()->remove($path->getPathname()) : rmdir($path->getPathname());
		}
		if($includeDir){
			rmdir($dirPath);
		}
	}



    private function getFileSystem()
    {
    	if($this->fileSystem==null){
    		$this->fileSystem = new FileSystem();
    	}
    	return $this->fileSystem;
    }

}