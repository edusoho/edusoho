<?php
namespace Topxia\WebBundle\DataDict;

use Topxia\WebBundle\DataDict\DataDictInterface;

class MemberLevelDisct  implements DataDictInterface{
	public function getDict()
	{
		return array(
			'level_p'=>'普通会员',
			'level_g'=>'金牌会员',
		);
	}

}

?>