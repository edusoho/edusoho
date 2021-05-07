<?php

namespace AppBundle\Extension;

interface ExtensionInterface
{
    public function getQuestionTypes();

    public function getPayments();

    public function getActivities();

    public function getCallbacks();

    public function getTaskToolbars();

    public function getCourseTypes();

    public function getWeChatTemplates();

    public function getMessageSubscribeTemplates();

    public function getFavoriteTypes();

    public function getReportSources();

    public function getContentAuditSources();
}
