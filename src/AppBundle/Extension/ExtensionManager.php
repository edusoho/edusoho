<?php

namespace AppBundle\Extension;

class ExtensionManager
{
    protected $extensions = [];

    protected $questionTypes = [];

    protected $payments = [];

    protected $activities = [];

    protected $callbacks = [];

    protected $taskToolbars = [];

    protected $courseTypes = [];

    protected $wechatTemplates = [];

    protected $newcomerTasks = [];

    protected $favoriteTypes = [];

    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        $this->questionTypes = array_merge($this->questionTypes, $extension->getQuestionTypes());
        $this->payments = array_merge($this->payments, $extension->getPayments());
        $this->activities = array_merge($this->activities, $extension->getActivities());
        $this->taskToolbars = array_merge($this->taskToolbars, $extension->getTaskToolbars());
        $this->callbacks = array_merge($this->callbacks, $extension->getCallbacks());
        $this->courseTypes = array_merge($this->courseTypes, $extension->getCourseTypes());
        $this->wechatTemplates = array_merge($this->wechatTemplates, $extension->getWeChatTemplates());
        $this->newcomerTasks = array_merge($this->newcomerTasks, $extension->getNewcomerTasks());
        $this->favoriteTypes = array_column($this->favoriteTypes, $extension->getFavoriteTypes());
    }

    public function getQuestionTypes()
    {
        return $this->questionTypes;
    }

    public function getPayments()
    {
        return $this->payments;
    }

    public function getActivities()
    {
        return $this->activities;
    }

    public function getCallbacks()
    {
        return $this->callbacks;
    }

    public function getTaskToolbars()
    {
        return $this->taskToolbars;
    }

    public function getCourseTypes()
    {
        return $this->courseTypes;
    }

    public function getWeChatTemplates()
    {
        return $this->wechatTemplates;
    }

    public function getNewcomerTasks()
    {
        return $this->newcomerTasks;
    }

    public function getFavoriteTypes()
    {
        return $this->favoriteTypes;
    }
}
