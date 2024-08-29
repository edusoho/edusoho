<?php

namespace ApiBundle\Api\Resource\Watermark;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class Watermark extends AbstractResource
{
    public function get(ApiRequest $request, $scene)
    {
        if ('task' == $scene) {
            return $this->getTaskWatermark();
        }

        return [];
    }

    private function getTaskWatermark()
    {
        $courseSetting = $this->getSettingService()->get('course');
        if (empty($courseSetting['task_page_watermark_enable']) || empty($courseSetting['task_page_watermark_setting']['fields'])) {
            return [];
        }
        $watermarkSetting = $courseSetting['task_page_watermark_setting'];
        $user = $this->getUserService()->getUserAndProfile($this->getCurrentUser()->getId());
        $text = [];
        foreach ($watermarkSetting['fields'] as $field) {
            if ('truename' == $field) {
                $text[] = $user['truename'];
            }
            if ('nickname' == $field) {
                $text[] = $user['nickname'];
            }
            if ('mobile' == $field) {
                $text[] = $user['verifiedMobile'];
            }
            if ('custom' == $field) {
                $text[] = $watermarkSetting['custom_text'];
            }
        }

        return [
            'text' => implode('/', array_filter($text)),
            'color' => $watermarkSetting['color'],
            'alpha' => intval($watermarkSetting['alpha']) / 100,
        ];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
