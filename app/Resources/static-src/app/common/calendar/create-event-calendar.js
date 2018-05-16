import Test from 'app/common/calendar/comp/right-click-comp';

export default class CustomFullCalendar {
  constructor(options = {}) {
    this.options = options;
    this.events = [];
    this._init();
  }

  _init() {
    const self = this;
    let calendarOptions = {
      selectable: true,
      header: {
        left: '',
        center: 'title',
        right: 'prev,today,next'
      },
      defaultDate: this.options['currentTime'],
      eventLimit: true, // allow "more" link when too many events
      locale: this.options['locale'],
      defaultView: this.options['defaultView'],
      allDaySlot: false,
      //默认移动８点位置
      scrollTime: '08:00:00',
      eventRender: function(event, element) {
        new Test(event, element);
      },

      select: function(startDate, endDate, jsEvent, view, resource) {
        // 选中后触发组件
        self.createEvent(startDate, endDate);
        console.log(self.events);
        calendarOptions['events'] = self.events;
        $(self.options['calendarContainer']).fullCalendar(calendarOptions);
      },
    };
    if (calendarOptions['defaultView'] == 'agendaWeek') {
      calendarOptions['columnFormat'] = 'ddd DD';
    }

    $(this.options['calendarContainer']).fullCalendar(calendarOptions);
  }


  createEvent(startDate, endDate) {
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

    this.initEvent();
    const event = {
      start: startDate.format(),
      end: endDate.format()
    };
    this.events.push(event);
  }

  deleteEvent() {

  }

  initEvent() {
    // 修改时间后 发送请求
    $('.js-time-start').change((event) => {
      const $target = $(event.target);
    });

    $('.js-time-end').change((event) => {
      const $target = $(event.target);
    });
  }

}