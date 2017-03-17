import {
  taskSortable,
  courseFunctionRemask,
  closeCourse,
  deleteCourse,
  showSettings,
  deleteTask,
  publishTask,
  unpublishTask
} from './help';

$('[data-help="popover"]').popover();

taskSortable('#sortable-list');
courseFunctionRemask();
closeCourse();
deleteCourse(store);
deleteTask();
publishTask();
unpublishTask();
showSettings();
