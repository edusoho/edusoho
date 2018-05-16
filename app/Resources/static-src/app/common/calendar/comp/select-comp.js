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
      self._createEvent(startDate, endDate);
      console.log(self.events);
      options['events'] = self.events;
      // $(options['calendarContainer']).fullCalendar(options);
      $(options['calendarContainer']).fullCalendar('renderEvent', self.events);
    };

    return options;
  }

  _createEvent(startDate, endDate) {
    const $target = $('.fc-highlight');
    const targetTop = $target.css('top');
    const targetBottom = $target.css('bottom');
    const targetLength = $target.height();
    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-text-medium cd-mb8">排课时间：</div>
                <div class="schedule-popover-content__time cd-dark-minor cd-mb8">${startDate.format('l')}</div>
                <div class="cd-mb8"><input class="time-input js-time-start form-control" value=${startDate.format('HH:mm')} name="startTime"> — <input class="time-input js-time-end form-control" name="endTime" value=${endDate.format('HH:mm')}></div>`,
      template: `<div class="popover arrangement-popover">
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'toggle'
    });
    $target.popover('show');

    $('.arrangement-popover').prevAll('.arrangement-popover').remove();

    this._initEvent();
    this.events = {
      start: startDate.format(),
      end: endDate.format()
    };
    // this.events.push(event);
  }

  _initEvent() {
    // 修改时间后 发送请求
    $('.js-time-start').change((event) => {
      const $target = $(event.target);
    });

    $('.js-time-end').change((event) => {
      const $target = $(event.target);
    });
  }
}