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

courseFunctionRemask();
closeCourse();
deleteCourse(store);
deleteTask();
publishTask();
unpublishTask();
showSettings();
