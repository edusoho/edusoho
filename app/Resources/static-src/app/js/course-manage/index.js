import {
  taskSortable,
  courseFunctionRemask,
  closeCourse,
  deleteCourse,
  showSettings,
  deleteTask,
  publishTask,
  unpublishTask,
  updateTaskNum,
  TaskListHeaderFixed
} from './help';
import { chapterAnimate } from 'app/common/widget/chapter-animate';

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
TaskListHeaderFixed();
// @TODO拆分，这个js被几个页面引用了有的页面根本不用js

$('.js-batch-add').hover(()=>{
  $('.js-batch-add').popover('show');
})

chapterAnimate('#sortable-list','.js-task-manage-chapter','es-icon-keyboardarrowdown','es-icon-keyboardarrowup') ;




