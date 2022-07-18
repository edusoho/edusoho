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
            $submitSetting = array_merge($defaultSetting, $this->filterReviewSetting($request->request->all()));
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_REVIEW, $submitSetting);

            $this->syncReviewSetting($submitSetting);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/review.html.twig', [
            'reviewSetting' => $reviewSetting,
        ]);
    }

    protected function syncReviewSetting($reviewSetting)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $goodsSetting = $this->getSettingService()->get('goods_setting', []);
        $openCourseSetting = $this->getSettingService()->get('openCourse', []);
        $articleSetting = $this->getSettingService()->get('article', []);
        $courseSetting['show_review'] = (!empty($reviewSetting['enable_review']) && !empty($reviewSetting['enable_course_review'])) ? '1' : '0';
        $classroomSetting['show_review'] = (!empty($reviewSetting['enable_review']) && !empty($reviewSetting['enable_classroom_review'])) ? '1' : '0';
        $goodsSetting['show_review'] = (!empty($courseSetting['show_review']) || !empty($classroomSetting['show_review'])) ? '1' : '0';
        $openCourseSetting['show_comment'] = (!empty($reviewSetting['enable_review']) && !empty($reviewSetting['enable_open_course_review'])) ? '1' : '0';
        $articleSetting['show_comment'] = (!empty($reviewSetting['enable_review']) && !empty($reviewSetting['enable_article_review'])) ? '1' : '0';
        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('classroom', $classroomSetting);
        $this->getSettingService()->set('goods_setting', $goodsSetting);
        $this->getSettingService()->set('openCourse', $openCourseSetting);
        $this->getSettingService()->set('article', $articleSetting);
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
            $submitSetting = array_merge($defaultSetting, $this->filterNoteSetting($request->request->all()));
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_NOTE, $submitSetting);
            $this->syncNoteSetting($submitSetting);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/note.html.twig', [
            'noteSetting' => $noteSetting,
        ]);
    }

    protected function syncNoteSetting($noteSetting)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $courseSetting['show_note'] = (!empty($noteSetting['enable_note']) && !empty($noteSetting['enable_course_note'])) ? 1 : 0;
        $classroomSetting['show_note'] = (!empty($noteSetting['enable_note']) && !empty($noteSetting['enable_classroom_note'])) ? 1 : 0;
        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('classroom', $classroomSetting);
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
            $submitSetting = array_merge($defaultSetting, $this->filterThreadSetting($request->request->all()));
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_THREAD, $submitSetting);
            $this->syncThreadSetting($submitSetting);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/thread.html.twig', [
            'threadSetting' => $threadSetting,
        ]);
    }

    protected function syncThreadSetting($threadSetting)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $courseSetting['show_question'] = (!empty($threadSetting['enable_thread']) && !empty($threadSetting['enable_course_question'])) ? '1' : '0';
        $courseSetting['show_discussion'] = (!empty($threadSetting['enable_thread']) && !empty($threadSetting['enable_course_thread'])) ? '1' : '0';
        $classroomSetting['show_thread'] = (!empty($threadSetting['enable_thread']) &&
            (!empty($threadSetting['enable_classroom_question']) || !empty($threadSetting['enable_classroom_thread']))) ? '1' : '0';
        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('classroom', $classroomSetting);
    }

    public function privateMessageAction(Request $request)
    {
        $defaultSetting = [
            'enable_private_message' => '1',
            'student_to_student' => '1',
            'student_to_teacher' => '1',
            'teacher_to_student' => '1',
        ];
        $privateMessageSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_PRIVATE_MESSAGE, []));
        if ('POST' === $request->getMethod()) {
            $submitSetting = array_merge($defaultSetting, $this->filterPrivateMessageSetting($request->request->all()));
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_PRIVATE_MESSAGE, $submitSetting);
            $this->syncPrivateMessageSetting($submitSetting);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/private-message.html.twig', [
            'privateMessageSetting' => $privateMessageSetting,
        ]);
    }

    public function contentAuditAction(Request $request)
    {
        $defaultSetting = [
            'mode' => 'audit_after',
            'enable_auto_audit' => '1',
            "enable_anti_brush_captcha" => 1,
        ];

        $contentAuditSetting = array_merge($defaultSetting, $this->getSettingService()->get(SettingNames::UGC_USER_CONTENT_CONTROL_CONTENT_AUDIT, []));

        if ('POST' === $request->getMethod()) {
            $contentAuditSetting = array_merge($contentAuditSetting, $this->filterContentAuditSetting($request->request->all()));
            $this->getSettingService()->set(SettingNames::UGC_USER_CONTENT_CONTROL_CONTENT_AUDIT, $contentAuditSetting);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/user-content-control/content-audit.html.twig', [
            'contentAuditSetting' => $contentAuditSetting,
        ]);
    }

    protected function syncPrivateMessageSetting($privateMessageSetting)
    {
        $messageSetting = $this->getSettingService()->get('message', []);
        $messageSetting['showable'] = !empty($privateMessageSetting['enable_private_message']) ? '1' : '0';
        $messageSetting['studentToStudent'] = (!empty($privateMessageSetting['enable_private_message']) && !empty($privateMessageSetting['student_to_student'])) ? '1' : '0';
        $messageSetting['studentToTeacher'] = (!empty($privateMessageSetting['enable_private_message']) && !empty($privateMessageSetting['student_to_teacher'])) ? '1' : '0';
        $messageSetting['teacherToStudent'] = (!empty($privateMessageSetting['enable_private_message']) && !empty($privateMessageSetting['teacher_to_student'])) ? '1' : '0';
        $this->getSettingService()->set('message', $messageSetting);
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
            'student_to_student',
            'student_to_teacher',
            'teacher_to_student',
        ]);
        foreach (['student_to_student', 'student_to_teacher', 'teacher_to_student'] as $key) {
            $submitSetting[$key] = empty($submitSetting[$key]) ? '0' : '1';
        }

        return $submitSetting;
    }

    protected function filterContentAuditSetting($submitSetting)
    {
        $submitSetting = ArrayToolkit::parts($submitSetting, [
            'mode',
            'enable_auto_audit',
            'enable_anti_brush_captcha',
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
