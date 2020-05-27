<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Service\UploadFileService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class CourseSetQuestionSync extends AbstractEntitySync
{
    /**
     * 复制链说明：
     * CourseSet
     * - Question
     *   - Attachment 问题附件.
     * 由于exercise类型任务的题目列表是使用时自动创建的，因此需要把题目事先复制过去.
     *
     * @param
     */
    protected function syncEntity($source, $config = [])
    {
        $newCourse = $config['newCourse'];

        return $this->doSyncQuestions($newCourse, $source);
    }

    /**
     * @param $newCourse
     * @param $sourceCourse
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function doSyncQuestions($newCourse, $sourceCourse)
    {
        $this->syncParentQuestions($newCourse, $sourceCourse);
        $this->syncChildrenQuestions($newCourse, $sourceCourse);

        /*
        $syncIds = array_merge(
            ArrayToolkit::column($sourceCourse['childrenQuestions'], 'id'),
            ArrayToolkit::column($sourceCourse['parentQuestions'], 'id')
        );
        $questions = $this->getQuestionService()->findQuestionsBySyncIds($syncIds);

        $questions = ArrayToolkit::index($questions, 'syncId');
        $this->syncAttachments($questions, $newCourse, $sourceCourse);
        **/
        return [];
    }

    /**
     * @param $newCourse
     * @param $sourceCourse
     * 1. 一一同步每个题目
     * 2. 每个题目创建一个Item、一个Question、一个sync关系
     */
    protected function syncParentQuestions($newCourse, $sourceCourse)
    {
        $parentQuestions = $sourceCourse['parentQuestions'];

        if (empty($parentQuestions)) {
            return;
        }
        $s2b2cConfig = $this->getS2B2CConfig();
        $items = [];
        $bizQuestions = [];
        foreach ($parentQuestions as $question) {
            $newItem = $this->processFields($newCourse, $question);
            $items[] = $newItem = $this->getItemDao()->create($newItem);
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'item',
                'localResourceId' => $newItem['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
            if ('material' == $question['type']) {
                continue;
            }
            $newQuestion = $this->converBizQuestion($question, $newItem['id']);
            $bizQuestion = $this->getBizQuestionDao()->create($newQuestion);
            //子题依然采用远程的questionId 做关联
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'question',
                'localResourceId' => $bizQuestion['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
        }
    }

    /**
     * @param $newCourse
     * @param $sourceCourse
     * 1. 准备好父级问题和子问题
     * 2. 获取父级问题的同步关系
     * 3. 同步每个子问题，仅创建Question，Sync关系
     */
    protected function syncChildrenQuestions($newCourse, $sourceCourse)
    {
        $childrenQuestions = $sourceCourse['childrenQuestions'];
        $parentQuestions = $sourceCourse['parentQuestions'];
        if (empty($childrenQuestions)) {
            return;
        }
        $s2b2cConfig = $this->getS2B2CConfig();
        $resourceSyncs = ArrayToolkit::index(
            $this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
                $s2b2cConfig['supplierId'],
                ArrayToolkit::column($parentQuestions, 'id'),
                'item'
            ),
            'remoteResourceId'
        );

        foreach ($childrenQuestions as $question) {
            $parentQuestionSync = $resourceSyncs[$question['parentId']];
            $newQuestion = $this->converBizQuestion($question, $parentQuestionSync['localResourceId']);
            $newQuestion = $this->getBizQuestionDao()->create($newQuestion);
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'question',
                'localResourceId' => $newQuestion['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
        }
    }

    private function syncAttachments($questionMaps, $newCourse, $sourceCourse)
    {
        $attachments = $sourceCourse['questionAttachments'];
        if (empty($attachments)) {
            return;
        }
        $files = $this->getUploadFileService()->searchFiles(['targetId' => $newCourse['courseSetId']], [], 0, PHP_INT_MAX);
        $files = ArrayToolkit::index($files, 'syncId');

        $newAttachments = [];
        foreach ($attachments as $attachment) {
            $newTargetId = empty($questionMaps[$attachment['targetId']]) ? 0 : $questionMaps[$attachment['targetId']]['id'];
            $newAttachment = [
                'syncId' => $attachment['id'],
                'type' => 'attachment',
                'fileId' => empty($files[$attachment['fileId']]) ? 0 : $files[$attachment['fileId']]['id'],
                'targetType' => $attachment['targetType'],
                'targetId' => $newTargetId,
            ];

            $newAttachments[] = $newAttachment;
        }

        $this->getUploadFileService()->batchCreateUseFiles($newAttachments);
    }

    protected function getFields()
    {
        return [
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'difficulty',
            'subCount',
            'bankId',
        ];
    }

    private function processFields($newCourse, $question)
    {
        $newQuestion['bank_id'] = $question['bankId'];
        $newQuestion['type'] = $question['type'];
        $newQuestion['material'] = $question['stem'];
        $newQuestion['analysis'] = $question['analysis'];
        $newQuestion['category_id'] = $question['categoryId'];
        $newQuestion['difficulty'] = $question['difficulty'];
        $newQuestion['question_num'] = $question['subCount'];
        $newQuestion['created_user_id'] = $this->biz['user']['id'];
        $newQuestion['updated_user_id'] = $this->biz['user']['id'];

        return $newQuestion;
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $newCourse = $config['newCourse'];
        $this->updateParentQuestions($newCourse, $source);
        $this->updateChildrenQuestions($newCourse, $source);
        /*
        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourse['courseSetId']);
        $questions = ArrayToolkit::index($questions, 'syncId');

        $this->updateAttachments($questions, $newCourse, $source);
        **/
        return [];
    }

    /**
     * @param $newCourse
     * @param $sourceCourse
     * 1. 检查已经存在的和关联关系$existSyncResources
     * 2. 如果远程没有parentQuestions,则删除本地本课程所有的题目
     * 3. 更新已存在的题目，ES测需要更新Item和Question
     * 4. 业务屏蔽；比对和远程题目的差别，删除远程已经不存在的题目, 无论是原始B还是迁移后，都无法获取删除的题目（老的courseSetId 不存在，新的已经将题目和课程脱钩）
     */
    protected function updateParentQuestions($newCourse, $sourceCourse)
    {
        $parentQuestions = $sourceCourse['parentQuestions'];
        $s2b2cConfig = $this->getS2B2CConfig();
        $existSyncResources = $this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
            $s2b2cConfig['supplierId'],
            ArrayToolkit::column($parentQuestions, 'id'),
            'item'
        );
        if (empty($parentQuestions)) {
            foreach ($existSyncResources as $existSyncResource) {
                $this->getItemService()->deleteItem($existSyncResource['localResourceId']);
            }

            return;
        }

        $existSyncResources = ArrayToolkit::index($existSyncResources, 'remoteResourceId');
        foreach ($parentQuestions as $question) {
            $newItem = $this->processFields($newCourse, $question);
            if (!empty($existSyncResources[$question['id']])) {
                $newItem = $this->getItemDao()->update($existSyncResources[$question['id']]['localResourceId'], $newItem);
//                $this->getBizQuestionDao()->batchDelete(['item_id' => $existSyncResources[$question['id']]['localResourceId']]);
                //如果是材料题，则忽略
                if ('material' == $question['type']) {
                    continue;
                }
                $bizQuestion = $this->getBizQuestionDao()->findByItemId($newItem['id']);
                $newQuestion = $this->converBizQuestion($question, $newItem['id']);
                if (!empty($bizQuestion)) {
                    $this->getBizQuestionDao()->update($bizQuestion[0]['id'], $newQuestion);
                }
                continue;
            }
            $newItem = $this->getItemDao()->create($newItem);
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'item',
                'localResourceId' => $newItem['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
            if ('material' == $question['type']) {
                continue;
            }
            $newQuestion = $this->converBizQuestion($question, $newItem['id']);
            $bizQuestion = $this->getBizQuestionDao()->create($newQuestion);
            //子题依然采用远程的questionId 做关联
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'question',
                'localResourceId' => $bizQuestion['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
        }

//        $needDeleteParentQuestionSyncIds = array_values(array_diff(array_keys($existSyncResources), ArrayToolKit::column($parentQuestions, 'id')));
//        if (!empty($existParentQuestions) && !empty($needDeleteParentQuestionSyncIds)) {
//            foreach ($needDeleteParentQuestionSyncIds as $needDeleteParentQuestionSyncId) {
//                $resourceSync = $existSyncResources[$needDeleteParentQuestionSyncId];
//                $this->getQuestionDao()->delete($resourceSync['localResourceId']);
//                $this->getResourceSyncService()->deleteSync($resourceSync['id']);
//            }
//        }
    }

    /**
     * @param $newCourse
     * @param $sourceCourse
     * 1. 检查已经存在的和关联关系$subQuestionResourceSyncs|$questionResourceSyncs
     * 2. 如果远程没有parentQuestions,则删除本地本课程所有的题目
     * 3. 更新已存在的题目，ES测需要更新Question
     * 4. 业务屏蔽；比对和远程题目的差别，删除远程已经不存在的题目, 无论是原始B还是迁移后，都无法获取删除的题目（老的courseSetId 不存在，新的已经将题目和课程脱钩）
     */
    protected function updateChildrenQuestions($newCourse, $sourceCourse)
    {
        $s2b2cConfig = $this->getS2B2CConfig();
        $childrenQuestions = $sourceCourse['childrenQuestions'];
        $parentQuestions = $sourceCourse['parentQuestions'];
        $subQuestionResourceSyncs = ArrayToolkit::index($this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
            $s2b2cConfig['supplierId'],
            ArrayToolkit::column($childrenQuestions, 'id'),
            'question'
        ), 'remoteResourceId');
        $questionResourceSyncs = ArrayToolkit::index($this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
            $s2b2cConfig['supplierId'],
            ArrayToolkit::column($parentQuestions, 'id'),
            'item'
        ), 'remoteResourceId');
        if (empty($childrenQuestions)) {
            foreach ($childrenQuestions as $childrenQuestion) {
                $resourceSync = $subQuestionResourceSyncs[$childrenQuestion['id']];
                $this->getBizQuestionDao()->delete($childrenQuestion['localResourceId']);
                $this->getResourceSyncService()->deleteSync($resourceSync['id']);
            }

            return;
        }
        foreach ($childrenQuestions as $question) {
            $parentQuestionSync = $questionResourceSyncs[$question['parentId']];
            $subQuestionSync = empty($subQuestionResourceSyncs[$question['id']]) ? [] : $subQuestionResourceSyncs[$question['id']];
            $newQuestion = $this->converBizQuestion($question, $parentQuestionSync['localResourceId']);
            if (!empty($subQuestionSync)) {
                //如果存在，仅更新
                $this->getBizQuestionDao()->update($subQuestionSync['localResourceId'], $newQuestion);
                continue;
            }
            $newQuestion = $this->getBizQuestionDao()->create($newQuestion);
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'question',
                'localResourceId' => $newQuestion['id'],
                'remoteResourceId' => $question['id'],
                'syncTime' => time(),
            ]);
        }

//        $needDeleteChildrenQuestionSyncIds = array_values(array_diff(array_keys($existChildrenQuestions), ArrayToolKit::column($childrenQuestions, 'id')));
//        if (!empty($existChildrenQuestions) && !empty($needDeleteChildrenQuestionSyncIds)) {
//            $needDeleteChildrenQuestions = $this->getQuestionDao()->search(['parentIdGT' => 0, 'courseSetId' => $newCourse['courseSetId'], 'syncIds' => $needDeleteChildrenQuestionSyncIds], [], 0, PHP_INT_MAX);
//            foreach ($needDeleteChildrenQuestions as $needDeleteChildrenQuestion) {
//                $this->getQuestionDao()->delete($needDeleteChildrenQuestion['id']);
//            }
//        }
    }

    /**
     * @param $questionMaps
     * @param $newCourse
     * @param $sourceCourse
     * 附件存在形式不一样，不支持
     */
    private function updateAttachments($questionMaps, $newCourse, $sourceCourse)
    {
        return;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    /**
     * @return FileUsedDao
     */
    protected function getFileUsedDao()
    {
        return $this->biz->dao('File:FileUsedDao');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Dao\QuestionDao
     */
    protected function getBizQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    protected function converBizQuestion($question, $itemId)
    {
        $english = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        switch ($question['type']) {
            case 'choice':
                $answerMode = 'choice';

                $answer = [];
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = [];
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = [
                        'checkbox' => ['val' => $english[$key], 'text' => $text],
                    ];
                }
                break;

            case 'essay':
                $answerMode = 'rich_text';
                $answer = $question['answer'];
                $responsePoints = [
                    ['rich_text' => []],
                ];
                break;

            case 'determine':
                $answerMode = 'true_false';
                $answer = '1' == $question['answer'][0] ? ['T'] : ['F'];
                $responsePoints = [
                    ['radio' => ['val' => 'T', 'text' => '正确']],
                    ['radio' => ['val' => 'F', 'text' => '错误']],
                ];
                break;

            case 'fill':
                $answerMode = 'text';
                $answer = [];
                $responsePoints = [];
                $question['stem'] = preg_replace('/\[\[.+?\]\]/', '[[]]', $question['stem']);
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = implode($questionAnswer, '|');
                    $responsePoints[] = ['text' => []];
                }
                break;

            case 'uncertain_choice':
                $answerMode = 'uncertain_choice';

                $answer = [];
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = [];
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = [
                        'checkbox' => ['val' => $english[$key], 'text' => $text],
                    ];
                }
                break;

            case 'single_choice':
                $answerMode = 'single_choice';

                $answer = [];
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = [];
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = [
                        'radio' => ['val' => $english[$key], 'text' => $text],
                    ];
                }

                break;

            default:
                $answerMode = '';
                $answer = [];
                $responsePoints = [];
                break;
        }

        $bizQuestion = [
//            'id' => $question['id'],
            'item_id' => $itemId,
            'stem' => $question['stem'],
            'seq' => 0 == $question['parentId'] ? 1 : 0,
            'score' => $question['score'],
            'answer_mode' => $answerMode,
            'response_points' => $responsePoints,
            'answer' => $answer,
            'analysis' => $question['analysis'],
            'created_user_id' => $question['createdUserId'],
            'updated_user_id' => $question['updatedUserId'],
            'updated_time' => $question['updatedTime'],
            'created_time' => $question['createdTime'],
        ];

        return $bizQuestion;
    }
}
