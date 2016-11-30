import {closeCourse, deleteCourse, publishCourse, showSettings} from './help';
import sortable from 'common/sortable';


sortable({
  element : '#sortable-list'
});

closeCourse();
deleteCourse();
publishCourse();
// deleteTask();
//sortList();
showSettings();
