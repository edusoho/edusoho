<?php

namespace ApiBundle\Api\Resource\ItemDetail;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\Service\ResourceFacadeService;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class ItemDetail extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $req = $request->query->all();
        $user = $this->getCurrentUser();

        $attachment = $this->getAttachmentService()->getAttachmentByGlobalId($req['globalId']);
        if (empty($attachment)) {
            return ['result' => false, 'msg' => '文件不存在', 'data' => []];
        }

//        $question = $this->getQuestionDao()->get($attachment['target_id']);
//        $item = $this->getItemService()->getItem($question['item_id']);
//        $itemBank = $this->getItemBankService()->getItemBank($item['bank_id']);
//        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($itemBank['id']);
//        if (!$this->getQuestionBankService()->canManageBank($questionBank['id']) && !$this->getExerciseMemberService()->isExerciseMember($req['id'], $user['id'])) {
//        }

        $file = $this->getCloudFileService()->getByGlobalId($req['globalId']);
        if (empty($file)) {
            return ['result' => false, 'msg' => '文件不存在', 'data' => []];
        }

        if (in_array($file['type'], ['video', 'ppt', 'document', 'audio'])) {
            return $this->globalPlayer($file);
        }

        return ['result' => false, 'msg' => '无法预览该类文件', 'data' => []];
    }

    protected function globalPlayer($file)
    {
        $user = $this->getCurrentUser();
        $player = $this->getResourceFacadeService()->getPlayerContext($file);

        return ['result' => true, 'msg' => '', 'data' => [
            'token' => $player['token'],
            'resNo' => $file['globalId'],
            'user' => ['id' => $user['id'], 'name' => $user['nickname']],
            'type' => $file['type'],
        ]];
    }

    /**
     * @return ResourceFacadeService
     */
    protected function getResourceFacadeService()
    {
        return $this->service('CloudPlatform:ResourceFacadeService');
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->service('ItemBank:Item:AttachmentService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->service('CloudFile:CloudFileService');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->service('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }
}
