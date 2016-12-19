<?php
namespace Biz\Announcement\Processor;

use Codeages\Biz\Framework\Context\Biz;

abstract class AnnouncementProcessor
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public abstract function checkManage($targetId);

    public abstract function checkTake($targetId);

    public abstract function getTargetShowUrl();

	public abstract function announcementNotification($targetId, $targetObject, $targetObjectShowUrl);

	public abstract function tryManageObject($targetId);
	
	public abstract function getTargetObject($targetId);

	public abstract function getShowPageName($targetId);

}