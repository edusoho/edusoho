<?php
namespace AppBundle\Extension;

abstract class Extension implements ExtensionInterface
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

}