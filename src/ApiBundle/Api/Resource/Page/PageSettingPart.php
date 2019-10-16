<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Annotation\Access;
use AppBundle\Common\ArrayToolkit;

class PageSettingPart extends PageSetting
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $portal)
    {
        $mode = $request->query->get('mode');
        if (!in_array($mode, array('draft', 'published'))) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $type = $request->query->get('type');
        if (!in_array($type, array('discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram', 'apps'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $content = $request->request->all();
        $method = 'add'.ucfirst($type);

        return $this->$method($portal, $mode, $content);
    }

    protected function addDiscovery($portal, $mode = 'draft', $content = array())
    {
        if (!empty($content)) {
            $setting = $this->getSettingService()->get("{$portal}_{$mode}_discovery");
            if (empty($setting)) {
                $setting = $this->getH5SettingService()->getDefaultDiscovery($portal);
            }
            $setting = ArrayToolkit::insert($setting, 1, $content);
            $this->getSettingService()->set("{$portal}_{$mode}_discovery", $setting);
        }

        return $this->getDiscovery($portal, $mode);
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    protected function getH5SettingService()
    {
        return $this->service('System:H5SettingService');
    }
}
