import {hiddenUnpublishTask, addLesson} from './../header-util';
import BaseManage from './../BaseManage';
import { TaskListHeaderFixed } from 'app/js/course-manage/help';

class DefaultManage extends BaseManage {
  constructor($container) {
    super($container);
    this._defaultEvent();
  }

  _defaultEvent() {
    this._showLesson();
  }

  _sortRules($item, container) {
    return true;
  }

  _showLesson() {
    this.$element.find('.js-task-manage-item').first().addClass('active').find('.js-settings-list').stop().slideDown('fast');
    this.$element.on('click', '.js-item-content', (event) => {
      let $this = $(event.currentTarget);
      let $li = $this.closest('.js-task-manage-item');
      if ($li.hasClass('active')) {
        $li.removeClass('active').find('.js-settings-list').stop().slideUp('fast');
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown('fast');
        $li.siblings('.js-task-manage-item.active').removeClass('active').find('.js-settings-list').stop().slideUp('fast');
      }
    });
  }

  afterAddItem($elm) {
    if ($elm.find('.js-item-content').length > 0) {
      $elm.find('.js-item-content').trigger('click');
    }
  }
}

new DefaultManage('#sortable-list');
hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
