<?php

namespace Biz\Marker\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Marker\Dao\QuestionMarkerResultDao;
use Biz\Marker\QuestionMarkerException;
use Biz\Marker\Service\MarkerService;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Biz\Marker\Service\ReportService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function analysisQuestionMarker($courseId, $taskId, $questionMarkerId)
    {
        $this->preCheck($courseId, $taskId);
        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerId);
        if (empty($questionMarker)) {
            $this->createNewException(QuestionMarkerException::NOTFOUND_QUESTION_MARKER());
        }

        $item = $this->getItemService()->getItemWithQuestions($questionMarker['questionId'], true);
        if (empty($item) || empty($item['questions'])) {
            $this->createNewException(QuestionMarkerException::NOTFOUND_QUESTION_MARKER());
        }

        $analysis = [];
        $analysis['item'] = $item;
        $analysis['question'] = current($item['questions']);

        $results = $this->getQuestionMarkerResultService()->findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId);

        $analysis['count'] = count($results);

        //根据不同的题目类型，调用不同的解析逻辑，
        $method = $this->getMethodName($item['type']);
        $analysis['metaStats'] = call_user_func_array([$this, $method], [$item, $results]);

        return $analysis;
    }

    public function statTaskQuestionMarker($courseId, $taskId)
    {
        $this->preCheck($courseId, $taskId);
        $stats = [
            'courseId' => $courseId,
            'taskId' => $taskId,
            'tasks' => [],
            'questionMarkers' => [],
            'totalUserNum' => 0,
            'totalAnswerNum' => 0,
        ];
        $this->buildTasks($stats);
        $this->buildQuestionMarkers($stats);
        $this->buildUserNum($stats);
        $this->buildAnswerNum($stats);

        return $stats;
    }

    protected function preCheck($courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if ($task = $this->getTaskService()->getTask($taskId)) {
            if (empty($task)) {
                $this->createNewException(TaskException::NOTFOUND_TASK());
            }
        }
    }

    protected function buildTasks(&$stats)
    {
        $conditions = [
            'type' => 'video',
            'courseId' => $stats['courseId'],
            'mediaSource' => 'self',
        ];
        $tasks = $this->getTaskService()->searchTasks($conditions, ['seq' => 'ASC'], 0, PHP_INT_MAX);

        $stats['tasks'] = ArrayToolkit::thin($tasks, ['id', 'title']);
    }

    protected function buildQuestionMarkers(&$stats)
    {
        $stats['questionMarkers'] = [];
        if (empty($stats['taskId']) && empty($stats['tasks'])) {
            return;
        }

        $taskId = $stats['taskId'];
        if (empty($stats['taskId'])) {
            $taskId = $stats['tasks'][0]['id'];
            $stats['taskId'] = $taskId;
        }

        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $markers = $this->getMarkerService()->findMarkersByMediaId($activity['ext']['mediaId']);
        $questionMarkers = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds(ArrayToolkit::column($markers, 'id'));
        $questionMarkers = $this->setQuestionMarkerStem($questionMarkers);

        $stats['questionMarkers'] = $this->sortAndMerge($markers, $questionMarkers);
    }

    protected function setQuestionMarkerStem($questionMarkers)
    {
        $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($questionMarkers, 'questionId'), true);
        foreach ($questionMarkers as $key => &$questionMarker) {
            if (!empty($items[$questionMarker['questionId']]['questions'])) {
                $item = $items[$questionMarker['questionId']];
                $question = reset($items[$questionMarker['questionId']]['questions']);
                $questionMarker['stem'] = ('fill' == $item['type']) ? preg_replace('/\[\[]]/', '____', $question['stem']) : $question['stem'];
            } else {
                unset($questionMarkers[$key]);
            }
        }

        return $questionMarkers;
    }

    protected function buildUserNum(&$stats)
    {
        foreach ($stats['questionMarkers'] as &$questionMarker) {
            $questionMarker['userNum'] = $this->getQuestionMarkerResultDao()->countDistinctUserIdByQuestionMarkerIdAndTaskId($questionMarker['id'], $stats['taskId']);
        }

        $stats['totalUserNum'] = $this->getQuestionMarkerResultDao()->countDistinctUserIdByTaskId($stats['taskId']);
    }

    protected function buildAnswerNum(&$stats)
    {
        $totalAnswerNum = 0;
        foreach ($stats['questionMarkers'] as &$questionMarker) {
            $questionMarker['answerNum'] = $this->getQuestionMarkerResultDao()->count(['questionMarkerId' => $questionMarker['id'], 'taskId' => $stats['taskId']]);
            $questionMarker['rightNum'] = $this->getQuestionMarkerResultDao()->count(['questionMarkerId' => $questionMarker['id'], 'taskId' => $stats['taskId'], 'status' => 'right']);
            $totalAnswerNum += $questionMarker['answerNum'];
            $questionMarker['pct'] = empty($questionMarker['answerNum']) ? 0 :
                floor($questionMarker['rightNum'] / $questionMarker['answerNum'] * 100);
        }

        $stats['totalAnswerNum'] = $totalAnswerNum;
    }

    protected function getMethodName($type)
    {
        $replacedType = str_replace('_', ' ', $type);
        $replacedType = ucwords($replacedType);
        $replacedType = str_replace(' ', '', $replacedType);

        return 'analysis'.$replacedType;
    }

    protected function analysisSingleChoice($item, $results)
    {
        $choices = array_shift($item['questions'])['response_points'];
        $stats = $this->generateStats($choices, $results);
        $count = count($results);

        if ($count > 0) {
            $this->largestRemainderMethod($stats, $count);
        }

        return $stats;
    }

    protected function analysisUncertainChoice($item, $results)
    {
        return $this->analysisChoice($item, $results);
    }

    protected function analysisChoice($item, $results)
    {
        $choices = array_shift($item['questions'])['response_points'];
        $stats = $this->generateStats($choices, $results);
        $this->appendPct($stats, count($results));

        return $stats;
    }

    protected function analysisFill($item, $results)
    {
        $questionAnswers = array_shift($item['questions'])['answer'];
        $stats = array_fill_keys(array_keys($questionAnswers), ['answerNum' => 0, 'pct' => 0]);
        foreach ($results as $result) {
            $userAnswers = $result['answer'];
            $this->countFillRightAnswer($questionAnswers, $userAnswers, $stats);
        }

        $this->appendPct($stats, count($results));

        return $stats;
    }

    protected function analysisDetermine($item, $results)
    {
        $determine = array_shift($item['questions'])['response_points'];
        $stats = $this->generateStats($determine, $results);
        $count = count($results);

        if ($count > 0) {
            $this->largestRemainderMethod($stats, $count);
        }

        return [$stats['T'], $stats['F']];
    }

    protected function countFillRightAnswer($questionAnswers, $userAnswers, &$context)
    {
        foreach ($questionAnswers as $index => $rightAnswer) {
            $expectAnswer = [];
            $rightAnswer = explode('|', $rightAnswer);
            foreach ($rightAnswer as $value) {
                $value = trim($value);
                $value = preg_replace("/([\x20\s\t]){2,}/", ' ', $value);
                $expectAnswer[] = $value;
            }

            $actualAnswer = trim($userAnswers[$index]);
            $actualAnswer = preg_replace("/([\x20\s\t]){2,}/", ' ', $actualAnswer);
            if (in_array($actualAnswer, $expectAnswer)) {
                ++$context[$index]['answerNum'];
            }
        }
    }

    private function sortAndMerge($markers, $questionMarkers)
    {
        $markers = ArrayToolkit::index($markers, 'id');
        uasort($markers, function ($marker1, $marker2) {
            return $marker1['second'] > $marker2['second'];
        });

        $place = array_fill_keys(array_keys($markers), []);
        foreach ($questionMarkers as &$questionMarker) {
            $markerId = $questionMarker['markerId'];
            $questionMarker['markTime'] = $markers[$markerId]['second'];
            $place[$markerId][] = $questionMarker;
        }

        $result = [];
        foreach ($place as $qMarkers) {
            $result = array_merge($result, $qMarkers);
        }

        return $result;
    }

    private function generateStats($options, $results)
    {
        $stats = [];
        foreach ($options as $option) {
            $option = array_shift($option);
            $stats[$option['val']] = ['answerNum' => 0, 'pct' => 0];
        }

        foreach ($results as $result) {
            foreach ($result['answer'] as $index) {
                if (isset($stats[$index])) {
                    ++$stats[$index]['answerNum'];
                }
            }
        }

        return $stats;
    }

    private function appendPct(&$metaStats, $count)
    {
        if ($count > 0) {
            array_walk($metaStats, function (&$item) use ($count) {
                $item['pct'] = floor($item['answerNum'] / $count * 100);
            });
        }
    }

    private function largestRemainderMethod(&$stats, $totalCount)
    {
        //注释掉最大余数算法，使用简单的向下取整处理
        $totalPct = 0;
        foreach ($stats as $key => &$stat) {
            $totalPct += floor($stat['pct'] = $stat['answerNum'] / $totalCount * 100);
        }

        uasort($stats, function ($item1, $item2) {
            return $item1['pct'] - floor($item1['pct']) < $item2['pct'] - floor($item2['pct']);
        });

        foreach ($stats as &$stat) {
            $stat['pct'] = ($totalPct++ < 100) ? ceil($stat['pct']) : floor($stat['pct']);
        }

        ksort($stats);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    /**
     * @return QuestionMarkerResultService
     */
    protected function getQuestionMarkerResultService()
    {
        return $this->createService('Marker:QuestionMarkerResultService');
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker:QuestionMarkerService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return QuestionMarkerResultDao
     */
    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker:QuestionMarkerResultDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }
}
