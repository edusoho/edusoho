<?php

namespace AppBundle\Extension;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Extension extends BizAware implements ExtensionInterface
{
    public function getQuestionTypes()
    {
        return [];
    }

    public function getPayments()
    {
        return [];
    }

    public function getActivities()
    {
        return [];
    }

    public function getCallbacks()
    {
        return [];
    }

    public function getTaskToolbars()
    {
        return [];
    }

    public function getCourseTypes()
    {
        return [];
    }

    public function getTaskTypes()
    {
        return [];
    }

    public function getWeChatTemplates()
    {
        return [];
    }

    public function getMessageSubscribeTemplates()
    {
        return [];
    }

    public function getNewComerTasks()
    {
        return [];
    }

    public function getFavoriteTypes()
    {
        return [];
    }

    public function getReportSources()
    {
        return [];
    }

    public function getContentAuditSources()
    {
        return [];
    }
}
