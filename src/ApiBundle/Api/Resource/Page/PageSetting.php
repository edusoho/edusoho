<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;

class PageSetting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode', 'published');

        if (!in_array($mode, array('draft', 'published'))) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $type = 'course' == $type ? 'courseCondition' : $type;
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $method = 'get'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

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

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $content = $request->request->all();
        $method = 'add'.ucfirst($type);

        return $this->$method($portal, $mode, $content);
    }

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function remove(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode');
        if ('draft' != $mode) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        if (!in_array($type, array('discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $method = 'remove'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    protected function addDiscovery($portal, $mode = 'draft', $content = array())
    {
        $this->getSettingService()->set("{$portal}_{$mode}_discovery", $content);

        return $this->getDiscovery($portal, $mode);
    }

    protected function removeDiscovery($portal, $mode = 'draft')
    {
        $this->getSettingService()->delete("{$portal}_{$mode}_discovery");

        return array('success' => true);
    }

    protected function getDiscovery($portal, $mode = 'published')
    {
        $user = $this->getCurrentUser();
        if ('draft' == $mode && !$user->isAdmin()) {
            throw new AccessDeniedHttpException();
        }
        $discoverySettings = $this->getH5SettingService()->getDiscovery($portal, $mode, 'setting');
        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds'));
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('courseSetId'), 'courseSet');
                foreach ($discoverySetting['data']['items'] as &$course) {
                    $courseFilter = new CourseFilter();
                    $courseFilter->setMode(Filter::PUBLIC_MODE);
                    $courseFilter->filter($course);
                }
            }
            if ('classroom_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds', 'assistantIds', 'headTeacherId'));
                foreach ($discoverySetting['data']['items'] as &$classroom) {
                    $classroomFilter = new ClassroomFilter();
                    $classroomFilter->setMode(Filter::PUBLIC_MODE);
                    $classroomFilter->filter($classroom);
                }
            }
        }

        return $discoverySettings;
    }

    protected function getCourseCondition($portal, $mode = 'published')
    {
        return $this->getH5SettingService()->getCourseCondition($portal, $mode);
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
