<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankAssessmentExerciseRecordFilter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankChapterExerciseRecordFilter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseModuleFilter;

class MeFootprintFilter extends Filter
{
    protected $publicFields = ['id', 'userId', 'targetType', 'targetId', 'event', 'date', 'target', 'createdTime', 'updatedTime'];

    protected $filterMethods = [
        'task' => 'filterTaskFootprint',
        'item_bank_assessment_exercise' => 'filterItemBankAssessmentExerciseFootprint',
        'item_bank_chapter_exercise' => 'filterItemBankChapterExerciseFootprint',
    ];

    protected function publicFields(&$footprint)
    {
        if (empty($footprint['target'])) {
            return $footprint;
        }

        if (isset($this->filterMethods[$footprint['targetType']])) {
            $method = $this->filterMethods[$footprint['targetType']];
            $footprint = $this->$method($footprint);
        }
    }

    protected function filterItemBankAssessmentExerciseFootprint($footprint)
    {
        if (!empty($footprint['target'])) {
            if (!empty($footprint['target']['assessment'])) {
                $assessmentFilter = new AssessmentFilter();
                $assessmentFilter->setMode(Filter::SIMPLE_MODE);
                $assessmentFilter->filter($footprint['target']['assessment']);
            }

            if (!empty($footprint['target']['module'])) {
                $itemBankExerciseModuleFilter = new ItemBankExerciseModuleFilter();
                $itemBankExerciseModuleFilter->setMode(Filter::SIMPLE_MODE);
                $itemBankExerciseModuleFilter->filter($footprint['target']['module']);
            }

            if (!empty($footprint['target']['exercise'])) {
                $itemBankExerciseFilter = new ItemBankExerciseFilter();
                $itemBankExerciseFilter->setMode(Filter::SIMPLE_MODE);
                $itemBankExerciseFilter->filter($footprint['target']['exercise']);
            }

            if (!empty($footprint['target']['answerRecord'])) {
                $itemBankAssessmentExerciseRecordFilter = new ItemBankAssessmentExerciseRecordFilter();
                $itemBankAssessmentExerciseRecordFilter->filter($footprint['target']['answerRecord']);
            }
        }

        return $footprint;
    }

    protected function filterItemBankChapterExerciseFootprint($footprint)
    {
        if (!empty($footprint['target'])) {
            if (!empty($footprint['target']['assessment'])) {
                $assessmentFilter = new AssessmentFilter();
                $assessmentFilter->setMode(Filter::SIMPLE_MODE);
                $assessmentFilter->filter($footprint['target']['assessment']);
            }

            if (!empty($footprint['target']['module'])) {
                $itemBankExerciseModuleFilter = new ItemBankExerciseModuleFilter();
                $itemBankExerciseModuleFilter->setMode(Filter::SIMPLE_MODE);
                $itemBankExerciseModuleFilter->filter($footprint['target']['module']);
            }

            if (!empty($footprint['target']['exercise'])) {
                $itemBankExerciseFilter = new ItemBankExerciseFilter();
                $itemBankExerciseFilter->setMode(Filter::SIMPLE_MODE);
                $itemBankExerciseFilter->filter($footprint['target']['exercise']);
            }

            if (!empty($footprint['target']['answerRecord'])) {
                $itemBankChapterExerciseRecordFilter = new ItemBankChapterExerciseRecordFilter();
                $itemBankChapterExerciseRecordFilter->filter($footprint['target']['answerRecord']);
            }
        }

        return $footprint;
    }

    protected function filterTaskFootprint($footprint)
    {
        if (empty($footprint)) {
            return [];
        }

        if (empty($footprint['target'])) {
            return $footprint;
        }

        $courseItemFilter = new CourseItemFilter();
        $courseItemFilter->setMode(Filter::SIMPLE_MODE);

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::SIMPLE_MODE);

        $activityFilter = new ActivityFilter();
        $activityFilter->setMode(Filter::SIMPLE_MODE);

        $course = empty($footprint['target']['course']) ? null : $footprint['target']['course'];
        $classroom = empty($footprint['target']['classroom']) ? null : $footprint['target']['classroom'];
        $activity = empty($footprint['target']['activity']) ? null : $footprint['target']['activity'];

        $courseFilter->filter($course);
        $activityFilter->filter($activity);

        $courseItemFilter->filter($footprint['target']);

        $footprint['target']['course'] = $course;
        $footprint['target']['classroom'] = $classroom;
        $footprint['target']['activity'] = $activity;

        return $footprint;
    }
}
