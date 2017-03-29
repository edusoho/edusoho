import { publishCourse,showSettings } from 'app/js/course-manage/help';
import Intro from './intro';
publishCourse();
setTimeout(function() {
  let intro = new Intro();
  intro.introType();
}, 500);


