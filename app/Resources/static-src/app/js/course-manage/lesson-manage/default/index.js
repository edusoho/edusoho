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
<<<<<<< Updated upstream
    this.$element.find('.js-task-manage-item').first().addClass('active').find('.js-settings-list').stop().slideDown(500);
=======
    const self = this;
    const $firstDom = self.$element.find('.js-task-manage-item').first();
    $firstDom.addClass('active').find('.js-settings-list').stop().slideDown(500);
    // $firstDom.find('.js-settings-placeholder').addClass('unfold-anmation');
    $firstDom.find('.js-settings-placeholder').removeClass('hidden');
>>>>>>> Stashed changes
    this.$element.on('click', '.js-item-content', (event) => {
      let $this = $(event.currentTarget);
      let $li = $this.closest('.js-task-manage-item');
      if ($li.hasClass('active')) {
<<<<<<< Updated upstream
        $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
        $li.siblings('.js-task-manage-item.active').removeClass('active').find('.js-settings-list').hide();
=======
        $li.removeClass('active').find('.js-settings-list').stop().slideUp(250);
        $li.find('.js-settings-placeholder').addClass('hidden');
        // $li.find('.js-settings-placeholder').removeClass('unfold-anmation').addClass('fold-anmation');
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
        $li.find('.js-settings-placeholder').removeClass('hidden');
        // $li.find('.js-settings-placeholder').removeClass('fold-anmation').addClass('unfold-anmation');
        const $dom = $li.siblings('.js-task-manage-item.active');
        $dom.removeClass('active').find('.js-settings-list').hide();
        $dom.find('.js-settings-placeholder').addClass('hidden');
        // $dom.find('.js-settings-placeholder').removeClass('unfold-anmation').addClass('fold-anmation');
>>>>>>> Stashed changes
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
