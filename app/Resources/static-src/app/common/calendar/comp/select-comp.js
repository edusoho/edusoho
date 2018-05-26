import Comp from './comp';

/**
 * 左键按下，拖动选择
 * 如 new SelectComp()
 */
export default class SelectComp extends Comp {

  registerAction(options) {
    const self = this;
    options['selectable'] = true;
    // 禁止预约时间重复（创建后）
    options['eventOverlap'] = false;
    // 禁止选择预约时间重复（创建过程中）
    options['selectOverlap'] = false;
    options['select'] = (startDate, endDate, jsEvent, view, resource) => {
      // 选中后触发组件
      $('.js-arrangement-popover').remove();
      self.events = {
        start: startDate.format(),
        end: endDate.format(),
      };
      $(options['calendarContainer']).fullCalendar('renderEvent', self.events);
    };

    options['eventClick'] = (event, jsEvent, view) => {
      const $target = $(jsEvent.currentTarget);
      const $clickTarget = $target.find('.fc-bg');
      if ($target.hasClass('fc-tooltip')) {
        return;
      }
      if ($target.hasClass('fc-ordered-event')) {
        self.cancelPopover($clickTarget, event);
      }

      if(!event.type) {
        self.clickPopover($clickTarget, event);
        event.start = self.events.start;
        event.end = self.events.end;
        self.event = event;
      }

    };
    // 预约时间可拖拽
    options['editable'] = true;
    // 缩放调整时间
    options['eventResize'] = (event, jsEvent, ui, view) => {
      $('.js-arrangement-popover').remove();
    };
    // 拖拽位置调整时间
    options['eventDragStart'] = (event, jsEvent, ui, view) => {
      $('.js-arrangement-popover').remove();
    };

    self._initEvent(options);

    return options;
  }

  _initEvent(options) {
    $('body').on('change', '.js-time-start', event => this.changeStartTime(event, options));
    $('body').on('change', '.js-time-end', event => this.changeEndTime(event, options));
    $('body').on('click', '.js-cancel-btn', event => this.cancelReservation(event));
  }

  cancelPopover($target, event) {
    let cancelTemplate = '';
    let disabledStatus = '';
    if (event.cancelTime) {
      cancelTemplate = `<span class="color-danger js-cancel-tip">开始前${event.cancelTime}分钟，不可取消</span>`;
      disabledStatus = 'disabled';
    }

    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-mv8">
                  <div class="cd-dark-minor text-overflow cd-mb8"><span class="cd-dark-major">任务：</span>${event.event}</div>
                  <div class="cd-dark-minor cd-mb8"><span class="cd-dark-major">时间：</span>${event.start.format('Y年M月D日')} ${event.start.format('HH:mm')}  - ${event.end.format('HH:mm')} </div>
                  <div class="cd-dark-minor cd-mb8"><span class="cd-dark-major">学员：</span>${event.member}</div>
                  <div class="cd-dark-minor"><span class="cd-dark-major">状态：</span>${event.type}</div>
                </div>
                <div class="arrangement-popover__operate clearfix">${cancelTemplate}<button class="pull-right cd-btn cd-btn-sm cd-btn-primary js-cancel-btn" type="button" ${disabledStatus}>取消预约</button></div>`,
      template: `<div class="popover arrangement-popover arrangement-popover--long js-arrangement-popover"><div class="arrow"></div>
                <div class="arrangement-popover-content popover-content">
                </div>
              </div>`,
      trigger: 'click'
    });
    $target.popover('show');
    $('.js-arrangement-popover').prevAll('.js-arrangement-popover').remove();
  }


  cancelReservation(event) {
    $('.js-arrangement-popover').remove();
    cd.modal({
      el: '#cd-modal',
      ajax: false,
      url: '',
      maskClosable: false,
    }).on('ok', ($modal, modal) => {
      console.log('确定后的回调');
      modal.trigger('close');
    }).on('cancel', ($modal, modal) => {
      console.log('关闭后的回调');
    });
  }

  clickPopover($target, event) {
    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-text-medium cd-mb8">${Translator.trans('arrangement.course_time')}</div>
                <div class="cd-dark-minor cd-mb8">${event.start.format('l')}</div>
                <div class="cd-mb8" data-time="${event.start.format()}"><input class="arrangement-popover__time js-time-start form-control" value=${event.start.format('HH:mm')} maxlength='5' data-time="${event.start.format()}" name="startTime"> — <input class="arrangement-popover__time js-time-end form-control" name="endTime" maxlength='5' data-time="${event.end.format()}" value=${event.end.format('HH:mm')}></div>`,
      template: `<div class="popover arrangement-popover js-arrangement-popover"><div class="arrow"></div>
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'click'
    });
    $target.popover('show');
    $('.js-arrangement-popover').prevAll('.js-arrangement-popover').remove();
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

  _getParamNames() {
    return ['event', 'startTime', 'endTime', 'date'];
  }

  _getParamPrefix() {
    return 'click';
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