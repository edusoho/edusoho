<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class UpgradeTestpaperCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-testpaper-item')
            ->setDescription('22.1.3大数据升级');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sectionItemCount = $this->getAssessmentSectionItemDao()->count([]);
        $page = $sectionItemCount / 5000;
        for ($i = 1; $i <= $page; ++$i) {
            $result = $this->processAssessmentSectionItem($i);
            if ('finish' == $result) {
                return;
            }
        }
        $questionCount = $this->getQuestionDao()->count([]);
        $page = $questionCount / 3000;
        for ($i = 1; $i <= $page; ++$i) {
            $result = $this->processAssessmentQuestion($i);
            if ('finish' == $result) {
                return;
            }
        }
        $taskCount = $this->getTaskDao()->count([]);
        $page = $taskCount / 1000;
        for ($i = 1; $i <= $page; ++$i) {
            $result = $this->processHomeworkActivity($i);
            if ('finish' == $result) {
                return;
            }
        }
    }

    public function processAssessmentSectionItem($page)
    {
        $sectionItems = $this->getAssessmentSectionItemDao()->search([], ['id' => 'ASC'], ($page - 1) * 5000, 5000, ['id', 'score_rule']);

        if (empty($sectionItems)) {
            return 'finish';
        }
        $update = [];
        foreach ($sectionItems as $sectionItem) {
            $rules = $sectionItem['score_rule'];
            foreach ($rules as &$scoreRule) {
                if (empty($scoreRule['rule'])) {
                    continue;
                }
                $questionRules = \AppBundle\Common\ArrayToolkit::index($scoreRule['rule'], 'name');
                $allRight = $questionRules['all_right'];
                if (!empty($questionRules['part_right'])) {
                    $questionRules['part_right'] = [
                        'name' => 'part_right',
                        'score' => $allRight['score'],
                        'score_rule' => [
                            'score' => $allRight['score'],
                            'scoreType' => 'question',
                            'otherScore' => $questionRules['part_right']['score'],
                        ],
                    ];
                } else {
                    $questionRules['part_right'] = [
                        'name' => 'part_right',
                        'score' => $allRight['score'],
                        'score_rule' => [
                            'score' => $allRight['score'],
                            'scoreType' => 'question',
                            'otherScore' => $allRight['score'],
                        ],
                    ];
                }
                $scoreRule['rule'] = array_values($questionRules);
            }
            $update[$sectionItem['id']] = ['score_rule' => $rules];
        }

        if (!empty($update)) {
            $this->getAssessmentSectionItemDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        unset($update);
        unset($sectionItems);
        var_dump('assess'.$page);

        return $page + 1;
    }

    public function processAssessmentQuestion($page)
    {
        $sectionQuestions = $this->getQuestionDao()->search([], ['created_time' => 'ASC'], ($page - 1) * 3000, 3000);
        if (empty($sectionQuestions)) {
            return 'finish';
        }
        $update = [];
        foreach ($sectionQuestions as $sectionQuestion) {
            if (!empty($sectionQuestion['score_rule'])) {
                continue;
            }
            $update[$sectionQuestion['id']] = [
                'score_rule' => [
                    'score' => $sectionQuestion['score'],
                    'scoreType' => 'question',
                    'otherScore' => 'text' == $sectionQuestion['answer_mode'] ? $sectionQuestion['score'] : 0,
                ], ];
        }
        if (!empty($update)) {
            $this->getQuestionDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        unset($update);
        unset($sectionQuestions);
        var_dump('question'.$page);

        return $page + 1;
    }

    public function processHomeworkActivity($page)
    {
        $tasks = $this->getTaskDao()->search(['type' => 'homework'], ['createdTime' => 'ASC'], ($page - 1) * 1000, 1000);
        if (empty($tasks)) {
            return 'finish';
        }
        $activityIds = array_column($tasks, 'activityId');
        $activities = $this->getActivityDao()->findByIds($activityIds);
        $activities = array_column($activities, null, 'id');
        $update = [];
        foreach ($tasks as $task) {
            if (empty($activities[$task['activityId']])) {
                continue;
            }
            $activity = $activities[$task['activityId']];
            $update[$activity['mediaId']] = [
                'has_published' => $task['copyId'] > 0 ? 2 : ('create' == $task['status'] ? 0 : 1),
            ];
        }
        if (!empty($update)) {
            $this->getHomeworkActivityDao()->batchUpdate(array_keys($update), $update, 'id');
        }

        return $page + 1;
    }

    /**
     * @return HomeworkActivityDao
     */
    protected function getHomeworkActivityDao()
    {
        return ServiceKernel::instance()->createDao('Activity:HomeworkActivityDao');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return ServiceKernel::instance()->createDao('Task:TaskDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return ServiceKernel::instance()->createDao('Activity:ActivityDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return ServiceKernel::instance()->createDao('ItemBank:Item:QuestionDao');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return ServiceKernel::instance()->createDao('ItemBank:Item:ItemDao');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return ServiceKernel::instance()->createDao('ItemBank:Assessment:AssessmentSectionItemDao');
    }
}
