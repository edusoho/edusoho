import { publishCourse } from 'app/js/course-manage/help';
import Intro from './intro';

//发布教学计划
publishCourse();
setTimeout(function() {
	let intro = new Intro();
	intro.introType();
}, 500);


