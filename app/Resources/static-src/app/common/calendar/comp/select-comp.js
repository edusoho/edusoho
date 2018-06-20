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
        status: 'created',
        start: startDate.format(),
        end: endDate.format(),
        date: startDate.format('l'),
        startTime: startDate.format('HH:mm'),
        endTime: endDate.format('HH:mm'),
      };
      $(options['calendarContainer']).fullCalendar('renderEvent', self.events);
    };

    options['eventClick'] = (event, jsEvent, view) => {
      const current = this;
      const $target = $(jsEvent.currentTarget);
      const currentEvent = current.getParams(event);
      currentEvent.start = event.start;
      currentEvent.end = event.end;
      if (event.status) {
        currentEvent.status = event.status;
      }
      const $clickTarget = $target.find('.fc-bg');
      if ($target.hasClass('fc-tooltip') || $target.hasClass('calendar-before')) {
        return;
      }

      const data = self.convertTime(currentEvent);

      if (currentEvent.status === 'reserved') {
        data.member = currentEvent.member;
        data.cancelUrl = currentEvent.cancelUrl;
        data.target_name = currentEvent.target_name;
        event.cancelUrl = currentEvent.cancelUrl;
        self.event = event;
        self.cancelPopover($clickTarget, event, data, options);
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

    // 拖拽位置调整时间
    options['eventDragStart'] = (event, jsEvent, ui, view) => {
      $('.js-arrangement-popover').remove();
    };

    // 创建时间不得超过一天
    options['selectConstraint'] = {
      start: '00:01',
      end: '23:59'
    };

    // 禁止选择过去时间
    options['selectAllow'] = (selectInfo) => {
      return moment().diff(selectInfo.start) <= 0;
    };

    // 禁止将时间拖拽到过去
    options['eventAllow'] = (dropInfo, draggedEvent) => {
      return moment().diff(dropInfo.start) <= 0;
    };

    // 拖拽最小时间段的设置
    options['eventResize'] = (event, delta, revertFunc) => {
      $('.js-arrangement-popover').remove();
      const start = moment(event.start._i).format('X');
      const end = moment(event.end._i).format('X');
      const timeRange = end - start;
      const minTime = moment.duration(options.snapDuration) / 1000;
      const minMinutes = minTime / 60;
      if (timeRange < minTime) {
        cd.message({ type: 'danger', message: Translator.trans(`最少预约时间段为${minMinutes}分钟`) });
        revertFunc();
      }
    };

    self._initEvent(options);

    return options;
  }

  _initEvent(options) {
    $('body').on('change', '.js-time-start', event => this.changeStartTime(event, options));
    $('body').on('change', '.js-time-end', event => this.changeEndTime(event, options));
    $('body').on('click', '.js-cancel-btn', event => this.cancelReservation(event, options));
    $('body').on('click', '.js-button-group', event => this.clickOtherPos(event));
  }

  cancelPopover($target, event, data, options) {
    let cancelTemplate = '';
    let disabledStatus = '';
    const currentTime = moment(options.currentTime).format('X');
    const startTime = moment(event.start._i).format('X');
    const deltaTime = options.cancelLimitTime * 60;
    const delta = startTime - currentTime;
    if (delta < deltaTime) {
      cancelTemplate = `<span class="color-danger js-cancel-tip">开始前${options.cancelLimitTime}分钟，不可取消</span>`;
      disabledStatus = 'disabled';
    }

    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="mvm">
                  <div class="cd-dark-minor text-overflow mbm"><span class="cd-dark-major">任务：</span>${data.target_name}</div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">时间：</span>${data.date} ${data.startTime}  - ${data.endTime} </div>
                  <div class="cd-dark-minor mbm"><span class="cd-dark-major">学员：</span>${data.member}</div>
                  <div class="cd-dark-minor"><span class="cd-dark-major">状态：</span>已预约</div>
                </div>
                <div class="arrangement-popover__operate clearfix">${cancelTemplate}<button class="pull-right cd-btn cd-btn-sm cd-btn-primary js-cancel-btn" data-url="${data.cancelUrl}" type="button" ${disabledStatus}>取消预约</button></div>`,
      template: `<div class="popover arrangement-popover arrangement-popover--long js-arrangement-popover"><div class="arrow"></div>
                <div class="arrangement-popover-content popover-content">
                </div>
              </div>`,
      trigger: 'click'
    });
    $target.popover('show');
    $('.js-arrangement-popover').prevAll('.js-arrangement-popover').remove();
  }


  cancelReservation(event, options) {
    $('.js-arrangement-popover').remove();
    cd.modal({
      el: '#cd-modal',
      ajax: true,
      url: $(event.target).data('url'),
      maskClosable: false,
    }).on('ok', ($modal, modal) => {
      const $cancelBtn = $('.js-cancel-period');
      $cancelBtn.button('loading');
      const mode = $modal.find('.cd-radio.checked').find('[name="title"]').val();
      const url = $modal.find('.js-cancel-period').data('url');
      let self = this;
      $.post(url, {mode:mode}, function (res) {
        $cancelBtn.button('reset');
        if (mode === 'toCreated') {
          self.changeStatusToCreated(event, options);
        }

        if (mode === 'toCancelled') {
          self.changeStatusToCancelled(event, options);
        }


        modal.trigger('close');
      });
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
    const time = moment(eventData.start).format();
    const date = moment(eventData.start).format('l');
    const startTime = moment(eventData.start).format('HH:mm');
    const endTime = moment(eventData.end).format('HH:mm');
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

  clickOtherPos(event) {
    $('.js-arrangement-popover').remove();
  }

  changeStartTime(event, options) {
    this.changeTime(event, options, true);
  }

  changeEndTime(event, options) {
    this.changeTime(event, options);
  }

  //取消
  changeStatusToCreated(event, options) {
    this.event.status = 'created';
    this.event.className = [''];
    $(options['calendarContainer']).fullCalendar('updateEvent', this.event);
  }

  changeStatusToCancelled(event, options) {
    this.event.status = 'cancelled';
    this.event.className = ['fc-status-event fc-tooltip fc-cancel-event'];
    $(options['calendarContainer']).fullCalendar('updateEvent', this.event);
  }

  // 添加输入时间的验证规则
  changeTime(event, options, flag) {
    const $target = $(event.target);
    const date = $target.parent().data('time').substr(0, 11);
    const targetVal = date + $target.val();
    const siblingsVal = date + $target.siblings().val();
    // 输入格式错误
    const reg = /^((0[0-9])|(1[0-9])|(2[0-3]))\:([0-5][0-9])$/;
    const regExp = new RegExp(reg);
    if(!regExp.test($target.val())) {
      cd.message({ type: 'danger', message: Translator.trans('validate_old.right_time_tip') });
      $target.val('');
      return;
    }

    const targetTimeStamp = moment(targetVal).format('X');
    const siblingsTimeStamp = moment(siblingsVal).format('X');
    const changeTargetTime = moment(targetTimeStamp * 1000).format();
    const changesiblingsTargetTime = moment(siblingsTimeStamp * 1000).format();

    const minTime = date + options.minTime;
    const maxTime = date + options.maxTime;

    // 设置日历显示时间
    const currentTimeStamp = moment(options.currentTime).format('X');
    if (targetTimeStamp < moment(minTime).format('X') || targetTimeStamp > moment(maxTime).format('X') || targetTimeStamp < currentTimeStamp) {
      cd.message({ type: 'danger', message: Translator.trans('请输入有效时间') });
      $target.val('');
      return;
    }

    // 单节课最小时间
    const timeRange = options.snapDuration;
    const timeArray = timeRange.split(':');
    const milliSeconds = 3600 * Number(timeArray[0]) + 60 * Number(timeArray[1]);
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
    }
    $(options['calendarContainer']).fullCalendar('updateEvent', this.event);
  }

  _getParamNames() {
    return ['start_time', 'end_time', 'status', 'member', 'courseId', 'cancelUrl', 'target_name'];
  }

  _getParamPrefix() {
    return 'click';
  }
}