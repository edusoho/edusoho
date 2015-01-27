<?php 
namespace Custom\Service\CourseCarousel;

interface CourseCarouselService{
	
	public function getCourseCarouselByCode($code);
	
	public function findAllCourseCarousels();

    public function initCourseCarousels();

    public function edit($code,$fields);
}