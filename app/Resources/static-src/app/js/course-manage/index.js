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

taskSortable('#sortable-list');
courseFunctionRemask();
closeCourse();
deleteCourse(store);
deleteTask();
publishTask();
unpublishTask();
showSettings();
