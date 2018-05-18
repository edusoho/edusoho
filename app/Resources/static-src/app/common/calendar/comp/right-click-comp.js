import Comp from './comp';

/**
 * 右键 点击 删除框 删除
 * 如 new rightClickComp()
 */
export default class rightClickComp extends Comp {

  registerAction(options) {

    options['eventContextmenu'] = function(event, jsEvent, view) {
      const $target = $(jsEvent.currentTarget);
      const $popover = $('.js-arrangement-popover');
      if ($popover.length) {
        $popover.remove();
      }
      $target.popover({
        container: 'body',
        html: true,
        content: `<div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">${Translator.trans('site.delete')}</span></div>`,
        template: `<div class="popover schedule-popover delete-popover js-delete-item" data-id="${ event._id }">
                      <div class="schedule-popover-content delete-popover-content popover-content">
                      </div>
                    </div>`,
        trigger: 'click'
      });
      $target.popover('show');
      $('.js-delete-item').prevAll('.js-delete-item').remove();
      return false;
    };

    this.deleteEvent(options);
    this.clickOtherPos();

    return options;
  }

  deleteEvent(options) {
    // 删除对应id的项
    $('body').on('click', '.js-delete-item', (event) => {
      const $target = $(event.target);
      const id = $target.parents('.js-delete-item').data('id');
      $(options['calendarContainer']).fullCalendar('removeEvents', id);
    });
  }

  clickOtherPos() {
    $('body').click(() => {
      $('.js-delete-item').remove();
    });
  }

}