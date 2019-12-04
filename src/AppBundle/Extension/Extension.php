<?php

namespace AppBundle\Extension;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Extension extends BizAware implements ExtensionInterface
{
    public function getQuestionTypes()
    {
        return array();
    }

    public function getPayments()
    {
        return array();
    }

    public function getActivities()
    {
        return array();
    }

    public function getCallbacks()
    {
        return array();
    }

    public function getTaskToolbars()
    {
        return array();
    }

    public function getCourseTypes()
    {
        return array();
    }

    public function getWeChatTemplates()
    {
        return array();
    }

    public function getNewComerTasks()
    {
        return array();
    }
}
