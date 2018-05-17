import Comp from './comp';

/**
 * 左键按下，拖动选择
 * 如 new SelectComp()
 */
export default class SelectComp extends Comp {

  registerAction(options) {
    let self = this;
    options['selectable'] = true;
    options['select'] = function(startDate, endDate, jsEvent, view, resource) {
      // 选中后触发组件
      $('.arrangement-popover').remove();
      self._createEvent(startDate, endDate, options, jsEvent);
      $(options['calendarContainer']).fullCalendar('renderEvent', self.events);
    };

    options['eventClick'] = function(event, jsEvent, view) {
      const $target = $(jsEvent.target);
      $target.popover({
        container: 'body',
        html: true,
        content: `<div class="cd-text-medium cd-mb8">排课时间：</div>
                <div class="schedule-popover-content__time js-date-again cd-dark-minor cd-mb8">${event.start.format('l')}</div>
                <div class="cd-mb8" data-time="${event.start.format()}"><input class="time-input js-time-start form-control" value=${event.start.format('HH:mm')} maxlength='5' data-time="${event.start.format()}" name="startTime"> — <input class="time-input js-time-end form-control" name="endTime" maxlength='5' data-time="${event.end.format()}" value=${event.end.format('HH:mm')}></div>`,
        template: `<div class="popover arrangement-popover">
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
        trigger: 'toggle'
      });
      $target.popover('show');
      $('.arrangement-popover').prevAll('.arrangement-popover').remove();
      event.start = self.events.start;
      event.end = self.events.end;
      self.event = event;
    };

    self._initEvent(options);

    return options;

  }

  // 如何在select选中阶段修改时间区间
  _createEvent(startDate, endDate, options, jsEvent) {
    const $target = $(jsEvent.target);
    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-text-medium cd-mb8">排课时间：</div>
                <div class="schedule-popover-content__time js-date-again cd-dark-minor cd-mb8">${startDate.format('l')}</div>
                <div class="cd-mb8" data-time="${startDate.format()}"><input class="time-input js-time-start form-control" value=${startDate.format('HH:mm')} maxlength='5' data-time="${startDate.format()}" name="startTime"> — <input class="time-input js-time-end form-control" name="endTime" maxlength='5' data-time="${startDate.format()}" value=${startDate.format('HH:mm')}></div>`,
      template: `<div class="popover arrangement-popover">
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'toggle'
    });
    // $target.popover('show');

    $('.arrangement-popover').prevAll('.arrangement-popover').remove();
    this.events = {
      start: startDate.format(),
      end: endDate.format(),
    };

  }

  _initEvent(options) {
    $('body').on('change', '.js-time-start', event => this.changeStartTime(event, options));
    $('body').on('change', '.js-time-end', event => this.changeEndTime(event, options));
  }

  changeStartTime(event, options) {
    this.changeTime(event, options, true);
  }

  changeEndTime(event, options) {
    this.changeTime(event, options);
  }

  // 添加输入时间的验证规则
  changeTime(event, options, flag) {
    const $target = $(event.target);
    const date = $target.parent().data('time').substr(0, 11);
    const $targetVal = date + $target.val();
    const $siblingsVal = date + $target.siblings().val();
    this.regRule($target.val());
    if (flag) {
      if (Date.parse($targetVal) >= Date.parse($siblingsVal)) {
        cd.message({ type: 'danger', message: Translator.trans('validate_old.date_check.message') });
        return;
      }
      this.event.start = $targetVal;
      this.event.end = $siblingsVal;
    } else {
      if (Date.parse($targetVal) <= Date.parse($siblingsVal)) {
        cd.message({ type: 'danger', message: Translator.trans('validate_old.date_and_time_check.message') });
        return;
      }
      this.event.end = $targetVal;
      this.event.start = $siblingsVal;
    }
    $(options['calendarContainer']).fullCalendar('updateEvent', this.event);
  }

  regRule(value) {
    const reg = /^((0[0-9])|(1[0-9])|(2[0-3]))\:([0-5][0-9])$/;
    const regExp = new RegExp(reg);
    if(!regExp.test(value)) {
      cd.message({ type: 'danger', message: Translator.trans('validate_old.right_time_tip') });
      return;
    }
  }
}