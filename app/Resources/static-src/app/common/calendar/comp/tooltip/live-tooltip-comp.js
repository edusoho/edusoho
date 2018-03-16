import BaseTooltipComp from './base-tooltip-comp';

export default class LiveTooltip extends BaseTooltipComp {

  _getParamNames() {
    return ['event', 'startTime', 'endTime', 'date'];
  }

  _showTip(params, event, jsEvent) {
    console.log('show live tip', params);
    const $target = $(jsEvent.currentTarget);
    const time = params.startTime.substr(0, 10);
    const date = moment(time).format('L');
    const weekDay = moment(time).format('ddd');
    const startTime = params.startTime.substr(10, 6);
    const endTime = params.endTime.substr(10, 6);
    let popoverType;
    if ($target.hasClass('calendar-before')) {
      popoverType = 'schedule-popover--before';
    } else if ($target.hasClass('calendar-today')) {
      popoverType = 'schedule-popover--today';
    } else if ($target.hasClass('calendar-future')) {
      popoverType = 'schedule-popover--future';
    }
    $target.popover({
      container: 'body',
      html: true,
      content: `<i class="es-icon es-icon-history pull-left"></i>
                <div class="schedule-popover-content__item cd-mb8">
                  <span class="schedule-popover-content__time cd-dark-major">${date} ${weekDay}</span>
                  <div class="schedule-popover-content__time cd-dark-minor">${startTime} ~ ${endTime}</div>
                </div>
                <i class="es-icon es-icon-eventnote pull-left"></i>
                <div class="cd-dark-minor schedule-popover-content__item">${params.event}</div>`,
      template: `<div class="popover schedule-popover ${popoverType}" role="tooltip">
                  <div class="schedule-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'hover'
    });
    $target.popover('show');
  }

  _hideTip(params, event, jsEvent) {
    console.log('hide live tip', params);
  }
}