<?php
namespace Topxia\Service\Util\Impl\Tests;

use Topxia\Service\Util\SystemUtilService;
use Topxia\Service\Common\BaseTestCase;

class SystemUtilServiceImplTest extends BaseTestCase
{


	public function testRemoveUnusedUploadFiles()
	{	
		$conn = mysql_connect("localhost","root","root");
		if($conn){
			echo '链接';
		}else{
			echo '失败';
		}
		
		 mysql_select_db("edusoho-test");

		$sql = "INSERT INTO `upload_files` (`hashId`,`targetId`, `targetType`,`filename`,`ext`,`size`,`storage`,`usedCount`)
		 VALUES ('courselesson/1/20151030034018-4rnonr.mp4','1', 'courselesson','1.mp4','mp4','0','local','0')";
		
		$test_query = mysql_query($sql , $conn);
		if($test_query){
			echo '插入成功';
		};

		$test = $this->getSystemUtilService()->removeUnusedUploadFiles();

		 $this->assertEquals(1,$test);
		
	}


	protected function getSystemUtilService()
	{
	return $this->getServiceKernel()->createService('Util.SystemUtilService');
	}

  	

}