import { publishCourse,showSettings } from 'app/js/course-manage/help';
import Intro from './intro';

publishCourse();
showSettings();
console.log($('.js-task-manage-item:first'));
$('.js-task-manage-item:first').trigger('mouseenter');

setTimeout(function() {
  let intro = new Intro();
  intro.introType();
}, 500);


