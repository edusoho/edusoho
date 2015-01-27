<?php
namespace Custom\Service\CourseCarousel\Impl;
use Custom\Service\CourseCarousel\CourseCarouselService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class CourseCarouselServiceImpl extends BaseService implements CourseCarouselService
{

    public function getCourseCarouselByCode($code)
    {
        if(!in_array($code, array('recommendCourse','latestCourse','categoryCourse','columnCourse'))){
            throw $this->createServiceException('课程轮播code错误！');
        }
        $courseCarousels=$this->findAllCourseCarousels();
        return $courseCarousels[$code];
    }

    public function findAllCourseCarousels()
    {
        $courseCarousels=$this->getSettingService()->get('courseCarousels');
        if(empty($courseCarousels)){
            $courseCarousels=$this->initCourseCarousels();
        }
        uasort($courseCarousels, function($courseCarousel1, $courseCarousel2){
            return $courseCarousel1['seq'] > $courseCarousel2['seq'];
        });
        return $courseCarousels;
    }

    public function initCourseCarousels()
    {
        $courseCarousels['recommendCourse']=array(
            'name'=>'精品课程',
            'code'=>'recommendCourse',
            'seq'=>1,
            'display'=>1,
            'categoryId'=>0,
            'columnId'=>0
        );
        $courseCarousels['latestCourse']=array(
            'name'=>'最新课程',
            'code'=>'latestCourse',
            'seq'=>2,
            'display'=>1,
            'categoryId'=>0,
            'columnId'=>0
        );
        $courseCarousels['categoryCourse']=array(
            'name'=>'分类课程',
            'code'=>'categoryCourse',
            'seq'=>3,
            'display'=>1,
            'categoryId'=>1,
            'columnId'=>0
        );
        $courseCarousels['columnCourse']=array(
            'name'=>'专栏课程',
            'code'=>'columnCourse',
            'seq'=>4,
            'display'=>1,
            'categoryId'=>0,
            'columnId'=>1
        );
        $this->getSettingService()->set('courseCarousels',$courseCarousels);
        return $courseCarousels;
    }

    public function edit($code,$fields)
    {
        if(!in_array($code, array('recommendCourse','latestCourse','categoryCourse','columnCourse'))){
            throw $this->createServiceException('课程轮播code错误！');
        }
        $courseCarousels=$this->findAllCourseCarousels();
        $fields['code']=$code;
        $courseCarousels[$code]=$fields;
        $this->getSettingService()->set('courseCarousels',$courseCarousels);

    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}