<?php

namespace Biz\User\Event;

use AppBundle\Common\MathToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\Goods\GoodsEntityFactory;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return [
            'classroom.join' => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
            'classroom.quit' => 'onClassroomQuit',
        ];
    }

    public function onClassroomJoin(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

        $this->publishJoinStatus($classroom, $userId, 'become_student');
        $this->syncCourseStudents($classroom, $userId);
        $this->countClassroomIncome($classroom);
    }

    public function onClassroomGuest(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        // publish status
        $this->publishJoinStatus($classroom, $userId, 'become_auditor');
        //add user to classroom courses
        // $this->syncCourseStudents($classroom, $userId);
    }

    public function onClassroomQuit(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->countClassroomIncome($classroom);
    }

    private function simplifyClassroom($classroom)
    {
        return [
            'id' => $classroom['id'],
            'title' => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about' => StringToolkit::plain($classroom['about'], 100),
            'price' => $classroom['price'],
        ];
    }

    private function syncCourseStudents($classroom, $userId)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        if (empty($courses)) {
            return;
        }

        foreach ($courses as $course) {
            $member = $this->getMemberService()->getCourseMember($course['id'], $userId);
            if (empty($member)) {
                $this->getMemberService()->becomeStudentByClassroomJoined($course['id'], $userId);
            }
        }
    }

    private function publishJoinStatus($classroom, $userId, $type)
    {
        $status = [
            'type' => $type,
            'classroomId' => $classroom['id'],
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'private' => 'published' == $classroom['status'] ? 0 : 1,
            'userId' => $userId,
            'properties' => [
                'classroom' => $this->simplifyClassroom($classroom),
            ],
        ];

        $status['private'] = 1 == $classroom['showable'] ? $status['private'] : 1;
        $this->getStatusService()->publishStatus($status);
    }

    private function countClassroomIncome($classroom)
    {
        $specs = $this->getGoodsEntityFactory()->create('classroom')->getSpecsByTargetId($classroom['id']);
        $conditions = [
            'target_id' => $specs['id'],
            'target_type' => 'classroom',
            'statuses' => ['paid', 'success', 'finished'],
        ];

        $income = $this->getOrderFacadeService()->sumOrderPayAmount($conditions);
        $income = MathToolkit::simple($income, 0.01);

        $this->getClassroomDao()->update($classroom['id'], ['income' => $income]);
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->getBiz();

        return $biz['goods.entity.factory'];
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->getBiz()->service('OrderFacade:OrderFacadeService');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->getBiz()->service('Classroom:ClassroomDao');
    }
}
