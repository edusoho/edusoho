import {closeCourse, deleteCourse, publishCourse, showSettings,deleteTask} from './help';
import sortable from 'common/sortable';

if($('#sortable-list').length){
	sortable({
	  element : '#sortable-list'
	});
}
closeCourse();
deleteCourse();
publishCourse();
deleteTask();
showSettings();
