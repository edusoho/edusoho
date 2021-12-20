<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;

class MeJoinedFilter extends Filter
{
    protected $publicFields = [
        'id', 'subtitle', 'courseSet', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'compulsoryTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime', 'enableFinish',
        'buyExpiryTime', 'access', 'isAudioOn', 'hasCertificate', 'goodsId', 'specsId', 'spec', 'hitNum', 'classroom', 'assistants', 'assistant', 'lastLearnTime', 'meJoinedType',
        'seq', 'categoryId', 'title', 'isOptional', 'startTime', 'endTime', 'mode',
        'number', 'type', 'mediaSource', 'length', 'activity', 'course',
        'headTeacher', 'auditorNum', 'courseNum', 'threadNum', 'noteNum', 'postNum', 'service', 'recommended',
        'recommendedSeq', 'maxRate', 'showable', 'expiryValue',
        'productId', 'exerciseId', 'questionBankId', 'doneQuestionNum', 'rightQuestionNum', 'masteryRate', 'completionRate', 'itemBankExercise',
    ];

    protected function publicFields(&$data)
    {
        $this->transformCover($data['cover']);
    }

    private function transformCover(&$cover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], 'item_bank_exercise.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], 'item_bank_exercise.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], 'item_bank_exercise.png');
    }
}
