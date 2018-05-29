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

      // 两种形式选中的时候，我可以有属性。 状态值， 开始时间和结束时间
      // 选中后触发组件
      $('.js-arrangement-popover').remove();
      self.events = {
        start: startDate.format(),
        end: endDate.format(),
        date: startDate.format('l'),
        startTime: startDate.format('HH:mm'),
        endTime: endDate.format('HH:mm'),
        status: 'created',
      };
      $(options['calendarContainer']).fullCalendar('renderEvent', self.events);
    };

    options['eventClick'] = (event, jsEvent, view) => {
      const current = this;
      console.log(event);
      const $target = $(jsEvent.currentTarget);
      const currentEvent = current.getParams(event);
      const $clickTarget = $target.find('.fc-bg');
      if ($target.hasClass('fc-tooltip')) {
        return;
      }

      const data = self.convertTime(currentEvent);

      if (currentEvent.status === 'reserved') {
        data.member = currentEvent.member;
        self.cancelPopover($clickTarget, event, data);
      }

      if (currentEvent.status === 'created' || event.status === 'created') {
        self.clickPopover($clickTarget, data);
        event.start = data.startTime;
        event.end = data.endTime;
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

    // 拖拽创建时间不得超过一天
    options['selectConstraint'] = {
      start: '00:01',
      end: '23:59'
    };

    self._initEvent(options);

    return options;
  }

  _initEvent(options) {
    $('body').on('change', '.js-time-start', event => this.changeStartTime(event, options));
    $('body').on('change', '.js-time-end', event => this.changeEndTime(event, options));
    $('body').on('click', '.js-cancel-btn', event => this.cancelReservation(event));
  }

  cancelPopover($target, event, data) {
    console.log(event);
    console.log(data);
    let cancelTemplate = '';
    let disabledStatus = '';
    if (event.cancelTime) {
      cancelTemplate = `<span class="color-danger js-cancel-tip">开始前${event.cancelTime}分钟，不可取消</span>`;
      disabledStatus = 'disabled';
    }

    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="mvm">
                  <div class="cd-dark-minor text-overflow mbm"><span class="cd-dark-major">任务：</span>${event.title}</div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">时间：</span>${data.date} ${data.startTime}  - ${data.endTime} </div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">学员：</span>${event.member}</div>
                  <div class="cd-dark-minor"><span class="cd-dark-major">状态：</span>已预约</div>
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

  getParams(event) {
    const currentEvent = this._generateParams(event);
    return currentEvent;
  }

  convertTime(eventData) {
    const self = this;
    const startTimeStamp = eventData.start_time * 1000;
    const endTimeStamp = eventData.end_time * 1000;
    const time = eventData.start_time ? moment(startTimeStamp).format(): self.events.start;
    const date = eventData.start_time ? moment(startTimeStamp).format('l'): self.events.date;
    const startTime = eventData.start_time ? moment(startTimeStamp).format('HH:mm'): self.events.startTime;
    const endTime = eventData.end_time ? moment(endTimeStamp).format('HH:mm'): self.events.endTime;

    const data = {
      time: time,
      date: date,
      startTime: startTime,
      endTime: endTime,
      status: eventData.status ? eventData.status : self.events.status
    };

    return data;
  }

  clickPopover($target, data) {
    console.log(data);
    const current = this;
    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-text-medium mvm">排课时间：</div>
                <div class="cd-dark-minor mbm">${data.date}</div>
                <div class="mbm" data-time="${data.time}"><input class="arrangement-popover__time js-time-start form-control" value="${data.startTime}" maxlength='5' data-time="${data.startTime}" name="startTime"> — <input class="arrangement-popover__time js-time-end form-control" name="endTime" maxlength='5' data-time="${data.endTime}" value="${data.endTime}"></div>`,
      template: `<div class="popover arrangement-popover js-arrangement-popover"><div class="arrow"></div>
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'click'
    });
    $target.popover('show');
    $('.js-arrangement-popover').prevAll('.js-arrangement-popover').remove();
  }

  changeStartTime(data, options) {
    this.changeTime(event, options, true);
  }

  changeEndTime(event, options) {
    this.changeTime(event, options);
  }

  // 添加输入时间的验证规则
  changeTime(event, options, flag) {
    const $target = $(event.target);
    const date = $target.parent().data('time').substr(0, 11);
    const targetVal = date + $target.val();
    const siblingsVal = date + $target.siblings().val();
    const targetTimeStamp = Date.parse(targetVal);
    const siblingsTimeStamp = Date.parse(siblingsVal);
    console.log(siblingsTimeStamp);
    const changeTargetTime = moment(targetTimeStamp).format();
    const changesiblingsTargetTime = moment(siblingsTimeStamp).format();

    // 输入格式错误
    this.regRule($target, $target.val());

    const minTime = date + options.minTime;
    const maxTime = date + options.maxTime;

    // 设置日历显示时间
    if (targetTimeStamp < Date.parse(minTime) || targetTimeStamp > Date.parse(maxTime)) {
      cd.message({ type: 'danger', message: Translator.trans('请输入有效时间') });
      $target.val('');
      return;
    }

    // 单节课最小时间
    const timeRange = options.snapDuration;
    const timeArray = timeRange.split(':');
    const milliSeconds = (3600 * Number(timeArray[0]) + 60 * Number(timeArray[1])) * 1000;
    const minutes = Number(timeArray[0]) * 60 + Number(timeArray[1]);

    // 最少预约时间
    if (Math.abs(siblingsTimeStamp - targetTimeStamp) < milliSeconds) {
      cd.message({ type: 'danger', message: Translator.trans(`最少预约时间段为${minutes}分钟`) });
      $target.val('');
      return;
    }

    // 开始时间和结束时间的判断
    if (flag) {
      if (targetTimeStamp >= siblingsTimeStamp) {
        cd.message({ type: 'danger', message: Translator.trans('validate_old.date_check.message') });
        $target.val('');
        return;
      }
      this.event.start = changeTargetTime;
      this.event.end = changesiblingsTargetTime;
    } else {
      if (targetTimeStamp <= siblingsTimeStamp) {
        cd.message({ type: 'danger', message: Translator.trans('validate_old.date_and_time_check.message') });
        $target.val('');
        return;
      }
      this.event.end = changeTargetTime;
      this.event.start = changesiblingsTargetTime;
      console.log(this.event.end + 'end');
      console.log(this.event.start + 'start');
    }
    console.log(this.event);
    $(options['calendarContainer']).fullCalendar('updateEvent', this.event);
  }

  _getParamNames() {
    return ['start_time', 'end_time', 'status', 'member'];
  }

  _getParamPrefix() {
    return 'click';
  }

  regRule($target, value) {
    const reg = /^((0[0-9])|(1[0-9])|(2[0-3]))\:([0-5][0-9])$/;
    const regExp = new RegExp(reg);
    if(!regExp.test(value)) {
      cd.message({ type: 'danger', message: Translator.trans('validate_old.right_time_tip') });
      $target.val('');
      return;
    }
  }
}