import NormalManage from './NormalManage.js';
import sortList from 'common/sortable';
import notify from 'common/notify';

export default class Manage extends NormalManage {
  constructor(element) {
    super(element);
    this._defaultEvent();
  }

   _defaultEvent() {
      this._showLesson();
      this._publish();
   }

  _showLesson() {
    this.$element.find('.js-task-manage-item').first().addClass('active').find('.js-settings-list').stop().slideDown(500);
     this.$element.on('click', '.js-item-content', (event) => {
      let $this = $(event.currentTarget);
      let $li = $this.closest('.js-task-manage-item');
      if ($li.hasClass('active')) {
        $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
        $li.siblings(".js-task-manage-item.active").removeClass('active').find('.js-settings-list').hide();
      }
    });
  }

  _publish() {
    this.$element.on('click', '.unpublish-item', (event) => {
      let $this = $(event.target);
      $.post($this.data('url'), function (data) {   
        let $parentLi = $this.closest('.js-task-manage-item');
        $parentLi.find('.publish-item, .js-delete, .publish-status').removeClass('hidden');
        $parentLi.find('.unpublish-item').addClass('hidden');
        notify('success', Translator.trans('course.manage.task_unpublish_success_hint'));
      }).fail(function(data){
        notify('danger', Translator.trans('course.manage.task_unpublish_fail_hint') + ':' + data.responseJSON.error.message);
      });
    })

    this.$element.on('click', '.publish-item', (event) => {
      $.post($(event.target).data('url'), function (data) {
        let $parentLi = $(event.target).closest('.task-manage-item');
        notify('success', Translator.trans('course.manage.task_publish_success_hint'));
        $parentLi.find('.publish-item, .js-delete, .publish-status').addClass('hidden')
        $parentLi.find('.unpublish-item').removeClass('hidden')
      }).fail(function(data){
        notify('danger', Translator.trans('course.manage.task_publish_fail_hint') + ':' + data.responseJSON.error.message);
      });
    })
  }
}