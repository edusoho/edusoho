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

import { toggleIcon } from 'app/common/widget/chapter-animate';
import notify from "common/notify";

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

$('#sortable-list').on('click', '.js-chapter-toggle-show', (event) => {
  let $this = $(event.currentTarget);
  let $chapter = $this.closest('.js-task-manage-chapter');
  $chapter.nextUntil('.js-task-manage-chapter').animate({ height: 'toggle', opacity: 'toggle' }, "normal");
  toggleIcon($chapter, 'es-icon-keyboardarrowdown', 'es-icon-keyboardarrowup');
});

$('input[name="isShowPublish"]').change(function(){
  let url = $(this).data('url');

  let status = $(this).is(':checked') ? 0 : 1;
  $.post(url, {status:status})
  .success(function(response) {
    notify('success', Translator.trans('site.save_success_hint'));
  })
  .error(function(response){
    notify('error', response.error.message);
  })
})




