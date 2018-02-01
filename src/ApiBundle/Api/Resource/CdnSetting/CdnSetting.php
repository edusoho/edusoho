<?php

namespace ApiBundle\Api\Resource\CdnSetting;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

class CdnSetting extends AbstractResource
{
    /**
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $data = $request->request->all();
        $fileSystem = new Filesystem();
        $path = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../web/files/test/test.txt';
        if (!file_exists(dirname($path))) {
            $fileSystem->mkdir(dirname($path));
        }
        file_put_contents($path, $request->headers);
        $cdn = array(
            'enabled' => $request->headers,
            'defaultUrl' => $request->headers->get('Authorization'),
            'userUrl' => $data['user_url'],
            'contentUrl' => $data['content_url'],
        );

        // $this->getSettingService()->set('cdn', $cdn);
        return array('code' => 'success', 'msg' => "设置cdn, enabled:{$cdn['enabled']}, defaultUrl:{$cdn['defaultUrl']},userUrl:{$cdn['userUrl']}, contentUrl:{$cdn['contentUrl']}");
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
