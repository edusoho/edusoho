import BaseManage from './../BaseManage';

class DefaultManage extends BaseManage {
  constructor($container) {
    super($container);
    this._defaultEvent();
  }

  _defaultEvent() {
    this._showLesson();
  }

  _showLesson() {
    this.$element.on('click', '.js-item-content', (event) => {
      let $this = $(event.currentTarget);
      let $li = $this.closest('.js-task-manage-item');
      if ($li.hasClass('active')) {
        $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
        $li.siblings('.js-task-manage-item.active').removeClass('active').find('.js-settings-list').hide();
      }
    });
  }
}

new DefaultManage('#sortable-list');
