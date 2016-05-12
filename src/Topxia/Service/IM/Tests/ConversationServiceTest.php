<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

class ConversationServiceTest extends BaseTestCase
{

    public function testGetConversationByMemberIds()
    {
        $data = array(
            'no' => 'abcdefg',
            'memberIds' => array(1,2),
        );

        $conversationAdded = $this->getConversationService()->addConversation($data);

        $conversationGetted = $this->getConversationService()->getConversationByMemberIds(array(2,1));

        $this->assertEquals($conversationAdded, $conversationGetted);
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
