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
	$chapter.nextUntil('.js-task-manage-chapter').animate({ height: 'toggle', opacity: 'toggle' }, 'normal');
	toggleIcon($chapter, 'es-icon-keyboardarrowdown', 'es-icon-keyboardarrowup');
});




