<?php
namespace Topxia\WebBundle\DataDict;

use Topxia\WebBundle\DataDict\DataDictInterface;
use Topxia\Service\Common\ServiceKernel;

class MemberLevelDisct  implements DataDictInterface{
	public function getDict()
	{
		return array(
			'level_p'=>$this->getServiceKernel()->trans('普通会员'),
			'level_g'=>$this->getServiceKernel()->trans('金牌会员'),
		);
	}

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}

?>