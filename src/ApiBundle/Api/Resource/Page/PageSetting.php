<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ApiBundle\Api\Exception\ErrorCode;

class PageSetting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $mode = $request->query->get('mode', 'published');
        $method = 'get'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $portal)
    {
        $type = $request->query->get('type');
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $mode = $request->query->get('mode', 'draft');
        $content = $request->request->all();
        $method = 'add'.ucfirst($type);

        return $this->$method($portal, $mode, $content);
    }

    protected function addDiscovery($portal, $mode = 'draft', $content = array())
    {
        return $this->getSettingService()->set("{$portal}-{$mode}-discovery", $content);
    }

    protected function addCourseCondition($portal, $mode = 'draft', $content = array())
    {
        return $this->getSettingService()->set("{$portal}-{$mode}-courseCondition", $content);
    }

    protected function getDiscovery($portal, $mode = 'published')
    {
        $user = $this->getCurrentUser();
        if ('draft' == $mode && !$user->isAdmin()) {
            throw new AccessDeniedHttpException();
        }

        return $this->getSettingService()->get("{$portal}-{$mode}-discovery", array());
    }

    protected function getCourseCondition($portal, $mode = 'published')
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        return array(
            'title' => '所有课程',
            array(
                'type' => 'category',
                'moduleType' => 'tree',
                'text' => '分类',
                'data' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            ),
            array(
                'type' => 'courseType',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'normal',
                        'text' => '课程',
                    ),
                    array(
                        'type' => 'live',
                        'text' => '直播',
                    ),
                ),
            ),
            array(
                'type' => 'sort',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'recommendedSeq',
                        'text' => '推荐',
                    ),
                    array(
                        'type' => '-studentNum',
                        'text' => '热门',
                    ),
                    array(
                        'type' => '-createdTime',
                        'text' => '最新',
                    ),
                ),
            ),
        );
    }

    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }

    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
