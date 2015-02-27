<?php
namespace Topxia\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;

class InteractiveEvent extends Event
{
    private $request;
    private $context;

    public function __construct(Request $request, $context)
    {
        $this->request = $request;
        $this->context = $context;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getContext()
    {
        return $this->context;
    }
}
