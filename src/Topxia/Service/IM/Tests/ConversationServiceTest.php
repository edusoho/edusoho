<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;

class ConversationServiceTest extends BaseTestCase
{
    public function testGetConversationByMemberIds()
    {
        $data = array(
            'no'        => 'abcdefg',
            'memberIds' => array(1, 2)
        );

        $added = $this->getConversationService()->addConversation($data);

        $getted = $this->getConversationService()->getConversationByMemberIds(array(2, 1));

        $this->assertEquals($added, $getted);
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
