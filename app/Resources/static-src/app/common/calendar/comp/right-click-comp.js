import Comp from './comp';

/**
 * 右键 点击 删除框 删除
 * 如 new rightClickComp()
 */
export default class rightClickComp extends Comp {

  generateEventValues(singleResult) {
    let compParamNames = this._getParamNames();
    let event = {};
    for (let i = 0; i < compParamNames.length; i++) {
      let fieldName = compParamNames[i];
      event[fieldName] = singleResult[fieldName];
    }

    return this._appendAdditionalAttr(event);
  }

  registerAction(options) {

    options['eventContextmenu'] = (event, jsEvent, view) => {
      const $target = $(jsEvent.currentTarget);

      const $popover = $('.js-arrangement-popover');
      if ($popover.length) {
        $popover.remove();
      }
      if (event.status !== 'created') {
        return;
      }
      $target.popover({
        container: 'body',
        html: true,
        content: `<div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">${Translator.trans('site.delete')}</span></div>`,
        template: `<div class="popover schedule-popover delete-popover js-delete-popover" data-id="${ event._id }">
                      <div class="schedule-popover-content delete-popover-content popover-content">
                      </div>
                    </div>`,
        trigger: 'click'
      });
      $target.popover('show');
      $('.js-delete-popover').prevAll('.js-delete-popover').remove();
      return false;
    };

    this.deleteEvent(options);
    this.clickOtherPos();

    return options;
  }

  deleteEvent(options) {
    // 删除对应id的项
    $('body').on('click', '.js-delete-popover', (event) => {
      const $target = $(event.currentTarget);
      const id = $target.data('id');
      $(options['calendarContainer']).fullCalendar('removeEvents', id);
    });
  }

  clickOtherPos() {
    $('body').click(() => {
      $('.js-delete-popover').remove();
    });
  }

  _getParamNames() {
    return ['status'];
  }

  _getParamPrefix() {
    return 'contextmenu';
  }

}