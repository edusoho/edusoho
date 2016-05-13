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

        $added = $this->getConversationService()->addConversation($data);

        $getted = $this->getConversationService()->getConversationByMemberIds(array(2,1));

        $this->assertEquals($added, $getted);
    }

    public function testAddMyConversation()
    {
        $data = array(
            'no' => 'abcdefg',
            'userId' => 1,
        );

        $added = $this->getConversationService()->addMyConversation($data);

        $this->assertEquals($data['no'], $added['no']);
    }

    public function testUpdateMyConversationByNo()
    {
        $data = array(
            'no' => 'abcdefg',
            'userId' => 1,
        );

        $this->getConversationService()->addMyConversation($data);

        $updated = $this->getConversationService()->updateMyConversationByNo($data['no'], array(
            'updatedTime' => 1234567890
        ));

        $this->assertEquals(1234567890, $updated['updatedTime']);
    }

    public function testSearchMyConversations()
    {
        $data = array(
            array(
                'no' => 'abcdefg',
                'userId' => 1,
            ),
            array(
                'no' => 'uvwxyz',
                'userId' => 1,
            ),
        );

        foreach ($data as $key => $value) {
            $this->getConversationService()->addMyConversation($value);
        }

        $conditions = array(
            'userId' => 1
        );

        $searched = $this->getConversationService()->searchMyConversations(
            $conditions,
            array('updatedTime', 'DESC'),
            0,
            20
        );

        $total = $this->getConversationService()->searchMyConversationCount($conditions);

        $this->assertEquals($total, count($searched));
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
