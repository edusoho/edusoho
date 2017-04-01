<?php

namespace AppBundle\Twig;

class EventReportExtension extends \Twig_Extension
{
    protected $container;


    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            new \Twig_SimpleFunction('event_report_twig', array($this, 'getEventReportTwig'), $options),
        );
    }

    public function getEventReportTwig($eventName, $subjectType, $subjectId, $params = array())
    {
        $html = "<div id='event-report' data-event-name=\"{$eventName}\" data-subject-type=\"{$subjectType}\" 
                    data-subject-id=\"{$subjectId}\" ";
        foreach ($params as $key => $value) {
            $html .= "data-{$key}=\"{$value}\"";
        }
        $html .= '></div>';

        return $html;
    }
}