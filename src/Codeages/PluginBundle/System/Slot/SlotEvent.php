<?php
namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\EventDispatcher\GenericEvent;

class SlotEvent extends GenericEvent
{
    protected $contents = array();

    public function __construct(array $arguments = array())
    {
        $this->arguments = $arguments;
    }

    public function addContent($content)
    {
        $this->contents[] = $content;
    }

    public function getContents()
    {
        return implode('', $this->contents);
    }

}
