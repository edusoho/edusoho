<?php

namespace AppBundle\Twig;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use Biz\AI\Util\AgentToken;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AIExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('ai_agent_token', [$this, 'makeAIAgentToken']),
            new \Twig_SimpleFunction('is_show_ai_agent', [$this, 'isShowAIAgent']),
            new \Twig_SimpleFunction('ai_teacher_domain', [$this, 'getAiTeacherDomain']),
        ];
    }

    public function makeAIAgentToken()
    {
        return (new AgentToken())->make();
    }

    public function isShowAIAgent($courseId)
    {
        if (!$this->getCourseMemberService()->isCourseStudent($courseId, $this->getCurrentUser()->getId())) {
            return false;
        }
        $course = $this->getCourseService()->getCourse($courseId);
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        if ($this->container->get('web.twig.course_extension')->isMemberExpired($course, $courseMember)) {
            return false;
        }
        $studyPlanConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);

        return !empty($studyPlanConfig['isActive']);
    }

    public function getAiTeacherDomain($courseId)
    {
        $studyPlanConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);

        return $studyPlanConfig['domainId'] ?? '';
    }

    /**
     * @return CurrentUser
     */
    private function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }
}
