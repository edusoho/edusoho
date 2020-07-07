<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class PageCourse extends AbstractResource
{
    const DEFAULT_DISPLAY_COUNT = 5;

    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Page\PageCourseFilter", mode="public")
     */
    public function get(ApiRequest $request, $portal, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $user = $this->getCurrentUser();
        $member = null;
        if (!empty($user['id'])) {
            $apiRequest = new ApiRequest('/api/me/course_members/'.$courseId, 'GET', []);
            $member = $this->invokeResource($apiRequest);
        }
        $course['member'] = $member;
        $course['learnedCompulsoryTaskNum'] = empty($member) ? 0 : $member['learnedCompulsoryTaskNum'];

        $this->getOCUtil()->single($course, ['creator', 'teacherIds']);
        $this->getOCUtil()->single($course, ['courseSetId'], 'courseSet');
        $course['access'] = $this->getCourseService()->canJoinCourse($courseId);

        $course['courseItems'] = $this->container->get('api.util.item_helper')->convertToLeadingItemsV2(
            $this->getCourseService()->findCourseItems($courseId),
            $course,
            $request->getHttpRequest()->isSecure(),
            $request->query->get('fetchSubtitlesUrls', 0),
            $request->query->get('onlyPublished', 0)
        );

        $course['allowAnonymousPreview'] = $this->getSettingService()->get('course.allowAnonymousPreview', 1);
        $course['courses'] = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSet']['id']);
        $course['courses'] = ArrayToolkit::sortPerArrayValue($course['courses'], 'seq');
        $course['progress'] = $this->getLearningDataAnalysisService()->makeProgress($course['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
        $course['reviews'] = $this->searchCourseReviews($course['id']);

        if ($this->isPluginInstalled('vip') && $course['vipLevelId'] > 0) {
            $apiRequest = new ApiRequest('/api/plugins/vip/vip_levels/'.$course['vipLevelId'], 'GET', []);
            $course['vipLevel'] = $this->invokeResource($apiRequest);
        }

        return $course;
    }

    protected function searchCourseReviews($courseId)
    {
        $reviews = $this->invokeResource(new ApiRequest(
            '/api/reviews',
            'GET',
            [
                'targetType' => 'course',
                'targetId' => $courseId,
                'parentId' => 0,
                'offset' => 0,
                'limit' => self::DEFAULT_DISPLAY_COUNT,
                'orderBys' => ['updatedTime' => 'DESC'],
            ]
        ));

        $this->getOCUtil()->multiple($reviews, ['userId']);
        $this->getOCUtil()->multiple($reviews, ['targetId'], 'course');

        foreach ($reviews as &$review) {
            $review['posts'] = $this->invokeResource(new ApiRequest(
                '/api/reviews',
                'GET',
                [
                    'parentId' => $review['id'],
                    'offset' => 0,
                    'limit' => self::DEFAULT_DISPLAY_COUNT,
                    'orderBys' => ['updatedTime' => 'DESC'],
                ]
            ));
            $this->getOCUtil()->multiple($review['posts'], ['userId']);
            $this->getOCUtil()->multiple($review['posts'], ['targetId'], 'course');

            array_filter($review['posts'], function (&$post) {
                $post['course'] = $post['target'];
                unset($post['target']);
            });

            $review['course'] = $review['target'];
            unset($review['target']);
        }

        return $reviews;
    }

    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }
}
