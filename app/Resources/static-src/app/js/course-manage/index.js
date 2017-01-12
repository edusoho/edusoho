import {closeCourse, deleteCourse, publishCourse, showSettings, deleteTask, publishTask, unpublishTask} from './help';
import sortable from 'common/sortable';
import 'store';
const COURSE_FUNCTION_REMASK = 'COURSE-FUNCTION-REMASK'; //课程改版功能提醒

if ($('#sortable-list').length) {
  sortable({
    element: '#sortable-list'
  });
}

console.log(store);

if(!store.get(COURSE_FUNCTION_REMASK)) {
  store.set(COURSE_FUNCTION_REMASK,true);
  console.log($('#course-function-modal'));
  $('#course-function-modal').modal('show');
}

closeCourse();
deleteCourse(store);
publishCourse();
deleteTask();
publishTask();
unpublishTask();
showSettings();
