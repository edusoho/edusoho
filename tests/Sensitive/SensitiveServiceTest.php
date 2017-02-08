<?php

namespace Tests\Sensitive;

use Biz\BaseTestCase;;


class  SensitiveServiceTest extends BaseTestCase{


	public function testscanText(){
		$str2 = "ｈｔｔｐ：／／ＪＢ５１．ｎｅｔ／　－　脚本之家";
		$msg = $this->getSensitiveService()->scanText($str2);
	}

	private function getSensitiveService()
	{
		return $this->createService('Sensitive:SensitiveService');
	}
}