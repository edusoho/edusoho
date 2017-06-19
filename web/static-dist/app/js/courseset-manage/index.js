webpackJsonp(["app/js/courseset-manage/index"],[
/* 0 */
/***/ (function(module, exports) {

	import { publishCourse } from 'app/js/course-manage/help';
	import Intro from './intro';
	
	//发布教学计划
	publishCourse();
	setTimeout(function () {
	  var intro = new Intro();
	  intro.introType();
	}, 500);

/***/ })
]);