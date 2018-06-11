<?php

namespace AppBundle\Extension;

class ExtensionManager
{
    protected $extensions = array();

    protected $questionTypes = array();

    protected $payments = array();

    protected $activities = array();

    protected $callbacks = array();

    protected $taskToolbars = array();

    protected $courseTypes = array();

    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        $this->questionTypes = array_merge($this->questionTypes, $extension->getQuestionTypes());
        $this->payments = array_merge($this->payments, $extension->getPayments());
        $this->activities = array_merge($this->activities, $extension->getActivities());
        $this->taskToolbars = array_merge($this->taskToolbars, $extension->getTaskToolbars());
        $this->callbacks = array_merge($this->callbacks, $extension->getCallbacks());
        $this->courseTypes = array_merge($this->courseTypes, $extension->getCourseTypes());
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
}
