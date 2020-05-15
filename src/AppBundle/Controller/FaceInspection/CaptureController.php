<?php

namespace AppBundle\Controller\FaceInspection;

use AppBundle\Controller\BaseController;
use Biz\FaceInspection\Common\FileToolkit;
use Biz\FaceInspection\Service\FaceInspectionService;
use Biz\System\Service\Impl\SettingServiceImpl;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Symfony\Component\HttpFoundation\Request;

class CaptureController extends BaseController
{
    public function indexAction(Request $request, $code)
    {
        $setting = $this->getSettingService()->get('cloud_facein', []);
        if (empty($setting['enabled'])) {
            $error = '监考服务未开启';
        }
        if ($setting['capture_link_code'] != $code) {
            $error = '此链接已失效，请联系管理员索取新链接';
        }

        $user = $this->getCurrentUser();
        $userFace = $this->getFaceInspectionService()->getUserFaceByUserId($user->getId());
        if ($userFace['capture_code'] == $code) {
            $error = '此链接已失效，请联系管理员索取新链接';
        }
        if (empty($error)) {
            $cloud = $this->getSettingService()->get('storage', []);
            $token = $this->getSDKFaceInspectionService()->makeToken($user['id'], $cloud['cloud_access_key'], $cloud['cloud_secret_key']);
        }

        return $this->render('face-inspection/index.html.twig', [
            'token' => empty($token) ? '' : $token,
            'user_no' => $user->getId(),
            'code' => $code,
            'error' => empty($error) ? '' : $error,
        ]);
    }

    public function uploadAction(Request $request, $code = 'face')
    {
        $setting = $this->getSettingService()->get('cloud_facein', []);
        if (empty($setting['enabled'])) {
            return $this->createJsonResponse(false);
        }
        $user = $this->getCurrentUser();
        $userFace = $this->getFaceInspectionService()->getUserFaceByUserId($user->getId());
        if (!empty($_FILES['picture'])) {
            $path = FileToolkit::saveBlobImage($_FILES['picture']);

            if (empty($userFace)) {
                $this->getFaceInspectionService()->createUserFace(['capture_code' => $code, 'user_id' => $user->getId(), 'picture' => $path]);
            } else {
                $this->getFaceInspectionService()->updateUserFace($userFace['id'], ['capture_code' => $code, 'picture' => $path]);
            }
        }

        return $this->createJsonResponse(true);
    }

    public function checkAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userFace = $this->getFaceInspectionService()->getUserFaceByUserId($user->getId());
        if (empty($userFace)) {
            return $this->createJsonResponse(false);
        }
        $path = strstr($userFace['picture'], '/facein/face_capture/');
        $url = 'https://'.$request->getHost().'/files'.$path;

        return $this->createJsonResponse($url);
    }

    public function saveAction(Request $request, $recordId)
    {
        $setting = $this->getSettingService()->get('cloud_facein', []);
        if (empty($setting['enabled'])) {
            return $this->createJsonResponse(false);
        }

        $record = $this->getAnswerRecordService()->get($recordId);
        if (empty($record)) {
            return $this->createJsonResponse(false);
        }

        if (!empty($_FILES['picture'])) {
            $data = $request->request->all();
            $userId = $this->getCurrentUser()->getId();
            $data['picture_path'] = FileToolkit::saveBlobImage($_FILES['picture'], 'face_inspection');
            $data['answer_scene_id'] = $record['answer_scene_id'];
            $data['answer_record_id'] = $recordId;
            $data['user_id'] = $userId;
            $this->getSDKFaceInspectionService()->createRecord($data);
        }

        return $this->createJsonResponse(true);
    }

    public function inspectionAction(Request $request, $answerSceneId, $answerRecordId)
    {
        if (!$this->canInspectionFace($answerSceneId, $answerRecordId)) {
            return $this->render('face-inspection/inspection.html.twig', [
                'token' => '',
                'imgUrl' => '',
                'recordId' => $answerRecordId,
            ]);
        }

        $user = $this->getCurrentUser();
        $userFace = $this->getFaceInspectionService()->getUserFaceByUserId($user['id']);
        $imgUrl = '';
        if (!empty($userFace)) {
            $path = strstr($userFace['picture'], '/facein/face_capture/');
            $imgUrl = 'https://'.$request->getHost().'/files'.$path;
        }

        $cloud = $this->getSettingService()->get('storage', []);
        $token = $this->getSDKFaceInspectionService()->makeToken($user['id'], $cloud['cloud_access_key'], $cloud['cloud_secret_key']);

        return $this->render('face-inspection/inspection.html.twig', [
            'token' => $token,
            'imgUrl' => $imgUrl,
            'recordId' => $answerRecordId,
        ]);
    }

    protected function canInspectionFace($answerSceneId, $answerRecordId)
    {
        $setting = $this->getSettingService()->get('cloud_facein', []);
        if (empty($setting['enabled'])) {
            return false;
        }
        $scene = $this->getAnswerSceneService()->get($answerSceneId);
        if (empty($scene) || 1 != $scene['enable_facein']) {
            return false;
        }
        $record = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($record)) {
            return false;
        }

        return true;
    }

    /**
     * @return SettingServiceImpl
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return FaceInspectionService
     */
    protected function getFaceInspectionService()
    {
        return $this->createService('FaceInspection:FaceInspectionService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\FaceInspection\Service\FaceInspectionService
     */
    protected function getSDKFaceInspectionService()
    {
        return $this->createService('ItemBank:FaceInspection:FaceInspectionService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }
}
