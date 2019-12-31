<?php

namespace AppBundle\Twig;

use AppBundle\Common\ServiceToolkit;
use Biz\Course\Service\CourseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->biz = $container->get('biz');
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('currency', array($this, 'currency')),
            new \Twig_SimpleFilter('json_encode_utf8', array($this, 'jsonEncodeUtf8')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('services', array($this, 'buildServiceTags')),
            new \Twig_SimpleFunction('classroom_services', array($this, 'buildClassroomServiceTags')),
            new \Twig_SimpleFunction('count', array($this, 'count')),
            new \Twig_SimpleFunction('course_count', array($this, 'courseCount')),
            new \Twig_SimpleFunction('course_cover', array($this, 'courseCover')),
            new \Twig_SimpleFunction('open_course_cover', array($this, 'openCourseCover')),
            new \Twig_SimpleFunction('course_set_cover', array($this, 'courseSetCover')),
            //@deprecated 请勿使用，后续将删除  2017-03-30
            //@see WebExtension#avatarPath
            new \Twig_SimpleFunction('user_avatar', array($this, 'userAvatar')),
            new \Twig_SimpleFunction('course_price', array($this, 'coursePrice')),
            new \Twig_SimpleFunction('log_trans', array($this, 'logTrans')),
            new \Twig_SimpleFunction('is_new_classroom_cover_size', array($this, 'isNewClassroomCoverSize')),
        );
    }

    /*
     * 返回金额的货币表示
     * @param money 金额，单位：分
     *
     */
    public function currency($money)
    {
        //当前仅考虑中文的货币处理；
        if (0 == $money) {
            return '0';
        }

        return sprintf('%.2f', $money);
    }

    /**
     * json_encode($arr, JSON_UNESCAPED_UNICODE) 需要PHP5.4以上版本，所以自己写一个以便支持PHP5.3.
     *
     * @param  $arr
     *
     * @return string
     */
    public function jsonEncodeUtf8($arr)
    {
        if (empty($arr)) {
            return '[]';
        }

        $encoded = json_encode($arr);

        return preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
            return html_entity_decode('&#x'.$matches[1].';', ENT_COMPAT, 'UTF-8');
        }, $encoded);
    }

    public function logTrans($message, $data)
    {
        $translator = $this->container->get('translator');
        $parameters = array();

        if (isset($data['context'])) {
            $data = $data['context'];
        }

        if (is_string($data)) {
            return $message;
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $parameters['%'.$key.'%'] = $value;
            }
        }

        $message = $translator->trans($message, $parameters);
        unset($parameters);
        unset($data);

        return $message;
    }

    public function buildServiceTags($selectedTags)
    {
        $tags = ServiceToolkit::getServicesByCodes(
            array('homeworkReview', 'testpaperReview', 'teacherAnswer', 'liveAnswer')
        );

        $tags = $this->transServiceTags($tags);

        if (empty($selectedTags)) {
            return $tags;
        }
        foreach ($tags as &$tag) {
            if (in_array($tag['code'], $selectedTags)) {
                $tag['active'] = 1;
            }
        }

        return $this->sortTags($tags);
    }

    public function transServiceTags($tags)
    {
        foreach ($tags as &$tag) {
            $tag['shortName'] = $this->container->get('translator')->trans($tag['shortName']);
            $tag['fullName'] = $this->container->get('translator')->trans($tag['fullName']);
            $tag['summary'] = $this->container->get('translator')->trans($tag['summary']);
        }

        return $tags;
    }

    public function buildClassroomServiceTags($selectedTags)
    {
        $tags = ServiceToolkit::getServicesByCodes(
            array('homeworkReview', 'testpaperReview', 'teacherAnswer', 'liveAnswer', 'event', 'workAdvise')
        );

        $tags = $this->transServiceTags($tags);

        if (empty($selectedTags)) {
            return $tags;
        }
        foreach ($tags as &$tag) {
            //为了兼容course和classroom的数据保存格式
            if (in_array($tag['code'], $selectedTags)) {
                $tag['active'] = 1;
            }
        }

        return $this->sortTags($tags);
    }

    public function courseCount($courseSetId)
    {
        return $this->getCourseService()->countCourses(array('courseSetId' => $courseSetId));
    }

    public function courseCover($course, $type = 'middle')
    {
        $courseSet = null;
        if (!empty($course)) {
            if (!empty($course['courseSet'])) {
                $courseSet = $course['courseSet'];
            } else {
                $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            }
        }

        return $this->courseSetCover($courseSet, $type);
    }

    public function openCourseCover($openCourse, $type)
    {
        $cover = !empty($openCourse[$type.'Picture']) ? $openCourse[$type.'Picture'] : null;

        if (empty($cover)) {
            $settings = $this->getSettingService()->get('default');
            $cover = !empty($settings['course.png']) ? $settings['course.png'] : null;
        }

        return $cover;
    }

    public function userAvatar($user, $type = 'middle')
    {
        $avatar = !empty($user[$type.'Avatar']) ? $user[$type.'Avatar'] : null;

        if (empty($avatar)) {
            $setting = $this->getSettingService()->get('default');
            $avatar = !empty($setting['avatar.png']) ? $setting['avatar.png'] : null;
        }

        return $avatar;
    }

    public function courseSetCover($courseSet, $type = 'middle')
    {
        $coverPath = null;
        if (!empty($courseSet)) {
            $cover = $courseSet['cover'];
            if (!empty($cover) && !empty($cover[$type])) {
                $coverPath = $cover[$type];
            }
        }

        if (empty($coverPath)) {
            $settings = $this->getSettingService()->get('default');
            $coverPath = !empty($settings['course.png']) && !empty($settings['defaultCoursePicture']) ? $settings['course.png'] : null;
        }

        return $coverPath;
    }

    public function coursePrice($course)
    {
        $price = $course['price'];
        $coin = $this->getSettingService()->get('coin');
        if (!empty($coin['coin_enabled'])) {
            if (isset($coin['price_type']) && 'Coin' == $coin['price_type']) {
                if ($price > 0) {
                    $cashRate = empty($coin['coin_rate']) ? 1 : $coin['coin_rate'];
                    $coinName = empty($coin['coin_name']) ? '虚拟币' : $coin['coin_name'];

                    return '价格：'.($price * $cashRate).$coinName;
                } else {
                    return '免费';
                }
            }
        }
        if ($price > 0) {
            return "价格：{$price}元";
        } else {
            return '免费';
        }
    }

    public function isNewClassroomCoverSize($classroom)
    {
        if (empty($classroom['largePicture'])) {
            return true;
        }
        $version = ClassroomService::COVER_SIZE_VERSION;
        if (strpos($classroom['largePicture'], '?version='.$version)) {
            return true;
        }

        return false;
    }

    protected function sortTags($tags)
    {
        if (empty($tags)) {
            return $tags;
        }
        usort($tags, function ($t1, $t2) {
            return $t2['active'] - $t1['active'];
        });

        return $tags;
    }

    public function count($arr)
    {
        if (empty($arr)) {
            return 0;
        }

        return count($arr);
    }

    public function getName()
    {
        return 'app_twig';
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
