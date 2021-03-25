<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Biz\System\SettingNames;
use Symfony\Component\HttpFoundation\Request;

class UserContentCtrlController extends BaseController
{
    public function reviewAction(Request $request)
    {
        $defaultSetting = [
            'enable_review' => '1',
            'enable_course_review' => '1',
            'enable_classroom_review' => '1',
            'enable_question_bank_review' => '1',
            'enable_open_course_review' => '1',
            'enable_article_review' => '1',
        ];
        $reviewSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_REVIEW, []));
        if ('POST' === $request->getMethod()) {
            $submitSetting = $this->filterReviewSetting($request->request->all());

            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_REVIEW, array_merge($defaultSetting, $submitSetting));

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/review.html.twig', [
            'reviewSetting' => $reviewSetting,
        ]);
    }

    public function noteAction(Request $request)
    {
        $defaultSetting = [
            'enable_note' => '1',
            'enable_course_note' => '1',
            'enable_classroom_note' => '1',
        ];
        $noteSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_NOTE, []));

        if ('POST' === $request->getMethod()) {
            $submitSetting = $this->filterNoteSetting($request->request->all());
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_NOTE, array_merge($defaultSetting, $submitSetting));

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/note.html.twig', [
            'noteSetting' => $noteSetting,
        ]);
    }

    public function threadAction(Request $request)
    {
        $defaultSetting = [
            'enable_thread' => '1',
            'enable_course_question' => '1',
            'enable_classroom_question' => '1',
            'enable_course_thread' => '1',
            'enable_classroom_thread' => '1',
            'enable_group_thread' => '1',
        ];
        $threadSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_THREAD, []));
        if ('POST' === $request->getMethod()) {
            $submitSetting = $this->filterThreadSetting($request->request->all());
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_THREAD, array_merge($defaultSetting, $submitSetting));

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/thread.html.twig', [
            'threadSetting' => $threadSetting,
        ]);
    }

    public function privateMessageAction(Request $request)
    {
        $defaultSetting = [
            'enable_private_message' => '1',
            'sending_rules' => [],
        ];
        $privateMessageSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_PRIVATE_MESSAGE, []));
        if ('POST' === $request->getMethod()) {
            $submitSetting = $this->filterPrivateMessageSetting($request->request->all());
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_PRIVATE_MESSAGE, array_merge($defaultSetting, $submitSetting));

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/private-message.html.twig', [
            'privateMessageSetting' => $privateMessageSetting,
        ]);
    }

    protected function filterReviewSetting($submitSetting)
    {
        $submitSetting = ArrayToolkit::parts($submitSetting, [
            'enable_review',
            'enable_course_review',
            'enable_classroom_review',
            'enable_question_bank_review',
            'enable_open_course_review',
            'enable_article_review',
        ]);

        return $submitSetting;
    }

    protected function filterNoteSetting($submitSetting)
    {
        $submitSetting = ArrayToolkit::parts($submitSetting, [
            'enable_note',
            'enable_course_note',
            'enable_classroom_note',
        ]);

        return $submitSetting;
    }

    protected function filterThreadSetting($submitSetting)
    {
        $submitSetting = ArrayToolkit::parts($submitSetting, [
            'enable_thread',
            'enable_course_question',
            'enable_classroom_question',
            'enable_course_thread',
            'enable_classroom_thread',
            'enable_group_thread',
        ]);

        return $submitSetting;
    }

    protected function filterPrivateMessageSetting($submitSetting)
    {
        $submitSetting = ArrayToolkit::parts($submitSetting, [
            'enable_private_message',
            'sending_rules',
        ]);

        return $submitSetting;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
