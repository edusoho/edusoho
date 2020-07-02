<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;

class ChapterExerciseRecordServiceImpl extends BaseService implements ChapterExerciseRecordService
{
    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankChapterExerciseRecordDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankChapterExerciseRecordDao()->count($conditions);
    }

    public function create($chapterExerciseRecord)
    {
        $chapterExerciseRecord = ArrayToolkit::parts($chapterExerciseRecord, ['moduleId', 'exerciseId', 'itemCategoryId', 'userId', 'answerRecordId', 'questionNum']);

        return $this->getItemBankChapterExerciseRecordDao()->create($chapterExerciseRecord);
    }

    public function get($id)
    {
        return $this->getItemBankChapterExerciseRecordDao()->get($id);
    }

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getItemBankChapterExerciseRecordDao()->getByAnswerRecordId($answerRecordId);
    }

    public function update($id, $chapterExerciseRecord)
    {
        $chapterExerciseRecord = ArrayToolkit::parts($chapterExerciseRecord, ['status', 'doneQuestionNum', 'rightQuestionNum', 'rightRate']);

        return $this->getItemBankChapterExerciseRecordDao()->update($id, $chapterExerciseRecord);
    }

    public function getLatestRecord($moduleId, $itemCategoryId, $userId)
    {
        return $this->getItemBankChapterExerciseRecordDao()->getLatestRecord($moduleId, $itemCategoryId, $userId);
    }

    protected function getItemBankChapterExerciseRecordDao()
    {
        return $this->createDao('ItemBankExercise:ChapterExerciseRecordDao');
    }
}
