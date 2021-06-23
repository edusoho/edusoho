<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class WrongBookCertainTypeFilter extends Filter
{
    protected $publicFields = [
        'id',
        'user_id',
        'item_num',
        'target_type',
        'created_time',
        'updated_time',
        'target_data',
    ];

    protected function publicFields(&$data)
    {
        if (empty($data['target_data'])) {
            return;
        }
        if ('course' == $data['target_type']) {
            $this->transformImages($data['target_data']['cover'], 'course.png');
        } elseif ('classroom' == $data['target_type']) {
            $data['target_data']['cover'] = [
                'small' => $data['target_data']['smallPicture'],
                'middle' => $data['target_data']['middlePicture'],
                'large' => $data['target_data']['largePicture'],
            ];
            $this->transformImages($data['target_data']['cover'], 'classroom.png');
        } elseif ('exercise' == $data['target_type']) {
            $this->transformImages($data['target_data']['cover'], 'item_bank_exercise.png');
        }
    }

    private function transformImages(&$images, $defaultImg = '')
    {
        $images['small'] = AssetHelper::getFurl(empty($images['small']) ? '' : $images['small'], $defaultImg);
        $images['middle'] = AssetHelper::getFurl(empty($images['middle']) ? '' : $images['middle'], $defaultImg);
        $images['large'] = AssetHelper::getFurl(empty($images['large']) ? '' : $images['large'], $defaultImg);
    }
}
