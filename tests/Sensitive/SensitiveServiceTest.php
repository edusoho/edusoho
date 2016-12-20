<?php

namespace SensitiveWord\Service\Tests;

use Biz\BaseTestCase;;


class  SensitiveServiceTest extends BaseTestCase{


	public function testscanText(){
		$str2 = "ｈｔｔｐ：／／ＪＢ５１．ｎｅｔ／　－　脚本之家";
		$msg = $this->getSensitiveService()->scanText($str2);
	}

	private function getSensitiveService()
	{
		return $this->getServiceKernel()->createService('Sensitive:Sensitive.SensitiveService');
	}
}