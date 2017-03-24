import {
  taskSortable,
  courseFunctionRemask,
  closeCourse,
  deleteCourse,
  showSettings,
  deleteTask,
  publishTask,
  unpublishTask,
  updateTaskNum
} from './help';

$('[data-help="popover"]').popover();
let sortableList = '#sortable-list';
taskSortable(sortableList);
updateTaskNum(sortableList);
closeCourse();
deleteCourse();
deleteTask();
publishTask();
unpublishTask();
showSettings();
// @TODO拆分，这个js被几个页面引用了有的页面根本不用js


