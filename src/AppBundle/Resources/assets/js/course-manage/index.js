import {closeCourse, deleteCourse, publishCourse, showSettings, deleteTask, publishTask, unpublishTask} from './help';
import sortable from 'common/sortable';

if ($('#sortable-list').length) {
  sortable({
    element: '#sortable-list'
  });
}
closeCourse();
deleteCourse();
deleteTask();
publishTask();
unpublishTask();
publishCourse();
// deleteTask();
showSettings();
