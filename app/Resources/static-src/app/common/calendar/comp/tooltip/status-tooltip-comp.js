import BaseTooltipComp from './base-tooltip-comp';

export default class StatusTooltipComp extends BaseTooltipComp {

  registerAction(options) {
    let current = this;
    options['eventMouseover'] = function(event, jsEvent, view) {
      current._showTip(current._generateParams(event), event, jsEvent);
    };
    options['eventMouseout'] = function(event, jsEvent, view) {
      current._hideTip(current._generateParams(event), event, jsEvent);
    };

    options['eventRender'] = function(event, element) {
      if (event.type) {
        const startTime = element.find('.fc-time').data('start');
        const finalVal = startTime + '（' + event.type + '）';
        element.find('.fc-time span').text(finalVal);
        element.find('.fc-title').addClass('hidden');
      }
    };

    return options;
  }

  _getParamNames() {
    return ['title', 'startTime', 'endTime', 'member', 'type'];
  }

  _showTip(params, event, jsEvent) {
    const $target = $(jsEvent.currentTarget);
    if (!$target.hasClass('fc-tooltip')) {
      return;
    }

    const time = params.startTime.substr(0, 10);
    const date = moment(time).format('L');
    const weekDay = moment(time).format('ddd');
    const startTime = params.startTime.substr(10, 6);
    const endTime = params.endTime.substr(10, 6);

    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="mvm">
                  <div class="cd-dark-minor text-overflow mbm"><span class="cd-dark-major">任务：</span>${params.title}</div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">时间：</span>${date} ${startTime} - ${endTime}</div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">学员：</span>${params.member}</div>
                  <div class="cd-dark-minor"><span class="cd-dark-major">状态：</span>${params.type}</div>
                </div>`,
      template: `<div class="popover arrangement-popover arrangement-popover--long"><div class="arrow"></div>
                  <div class="arrangement-popover-content popover-content">
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