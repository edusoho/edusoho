<?php

namespace ApiBundle\Api\Resource\ItemDetail;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\Service\ResourceFacadeService;
use Biz\Course\MemberException;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;

class ItemDetail extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $req = $request->query->all();
        $user = $this->getCurrentUser();

        switch ($req['type']) {
            case 'course':
                if (!$this->getCourseMemberService()->isCourseMember($req['id'], $user['id'])) {
                    throw MemberException::NOTFOUND_MEMBER();
                }
                break;
            case 'classroom':
                if (!$this->getClassroomService()->getClassroomMember($req['id'], $user['id'])) {
                    throw MemberException::NOTFOUND_MEMBER();
                }
                break;
            case 'exercise':
                if (!$this->getExerciseMemberService()->isExerciseMember($req['id'], $user['id'])) {
                    throw ItemBankExerciseMemberException::NOTFOUND_MEMBER();
                }
                break;
            default:
                throw MemberException::NOTFOUND_MEMBER();
                break;
        }

        $attachment = $this->getAttachmentService()->getAttachmentByGlobalId($req['globalId']);
        if (empty($attachment)) {
            return ['result' => false, 'msg' => '文件不存在', 'data' => []];
        }

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
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
