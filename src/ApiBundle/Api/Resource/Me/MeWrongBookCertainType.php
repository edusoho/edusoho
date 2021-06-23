<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBookCertainType extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookCertainTypeFilter", mode="public")
     */
    public function search(ApiRequest $request, $type)
    {
        $conditions = $request->query->all();
        $conditions['user_id'] = $this->getCurrentUser()->getId();
        $conditions['target_type'] = $type;

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $wrongBookPools = $this->service('WrongBook:WrongQuestionService')->searchWrongBookPool(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );
        $total = $this->service('WrongBook:WrongQuestionService')->countWrongBookPool($conditions);
        $default = $this->getSettingService()->get('default');
        if ('exercise' == $type) {
            $type = 'item_bank_exercise';
        } elseif ('course' == $type) {
            $type = 'courseSet';
        }

        $this->getOCUtil()->multiple($wrongBookPools, ['target_id'], $type, 'target_data');

        foreach ($wrongBookPools as &$wrongBookPool) {
            if ('courseSet' == $type) {
                if (empty($wrongBookPool['target_data']['cover']['large'])) {
                    if ('0' == $default['defaultCoursePicture']) {
                        $wrongBookPool['target_data']['cover']['middle'] = $this->getAssetUrl('/img/default/course.png');
                    } else {
                        $wrongBookPool['target_data']['cover']['middle'] = $this->getFileUrl($default['course.png']);
                    }
                } else {
                    $wrongBookPool['target_data']['cover']['middle'] = $this->getFileUrl($wrongBookPool['target_data']['cover']['middle']);
                }
            } elseif ('classroom' == $type) {
                if (empty($wrongBookPool['target_data']['middlePicture'])) {
                    $wrongBookPool['target_data']['cover']['middle'] = $this->getAssetUrl('/img/default/classroom.png');
                } else {
                    $wrongBookPool['target_data']['cover']['middle'] = $this->getFileUrl($wrongBookPool['target_data']['middlePicture']);
                }
            } else {
                if (empty($wrongBookPool['target_data']['cover'])) {
                    $wrongBookPool['target_data']['cover']['middle'] = $this->getAssetUrl('/img/default/item_bank_exercise.png');
                } else {
                    $wrongBookPool['target_data']['cover']['middle'] = $this->getFileUrl($wrongBookPool['target_data']['cover']['middle']);
                }
            }
        }

        return $this->makePagingObject($wrongBookPools, $total, $offset, $limit);
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $path = "http://{$_SERVER['HTTP_HOST']}/assets/{$path}";

        return $path;
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
