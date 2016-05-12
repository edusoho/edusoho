<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

class MyConversationServiceTest extends BaseTestCase
{

    public function testAddAndGet()
    {
        $data = array(
            'no' => 'abcdefg',
            'userId' => 1,
        );

        $added = $this->getMyConversationService()->addMyConversation($data);

        $getted1 = $this->getMyConversationService()->getMyConversation($added['id']);

        $getted2 = $this->getMyConversationService()->getMyConversationByNo($data['no']);

        $this->assertEquals($added, $getted1, $getted2);
    }

    public function testFindMyConversationsByUserId()
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
            $this->getMyConversationService()->addMyConversation($value);
        }

        $found = $this->getMyConversationService()->findMyConversationsByUserId(1);

        $this->assertCount(2, $found);
    }

    public function testUpdateMyConversationByNo()
    {
        $data = array(
            'no' => 'abcdefg',
            'userId' => 1,
        );

        $this->getMyConversationService()->addMyConversation($data);

        $updated = $this->getMyConversationService()->updateMyConversationByNo($data['no'], array(
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
            $this->getMyConversationService()->addMyConversation($value);
        }

        $conditions = array(
            'userId' => 1
        );

        $searched = $this->getMyConversationService()->searchMyConversations(
            $conditions,
            array('updatedTime', 'DESC'),
            0,
            20
        );

        $total = $this->getMyConversationService()->searchMyConversationCount($conditions);

        $this->assertEquals($total, count($searched));
    }

    protected function getMyConversationService()
    {
        return $this->getServiceKernel()->createService('IM.MyConversationService');
    }
}
