import Test from 'app/common/calendar/comp/right-click-comp';

export default class CustomFullCalendar {
  constructor(options = {}) {
    this.options = options;
    this.events = [];
    this._init();
    this.initEvent();
    this.deleteEvent();
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
        // new Test(event, element, self);
        element.bind('contextmenu', function(event) {
          const $target = $(event.currentTarget);
          console.log(event.pageX);
          console.log(event.pageY);
          // $(self.options['calendarContainer']).fullCalendar('removeEvents', () => {
          //   console.log('1111');
          //   return true;
          // });

          $('body').append(`<div class="delete-popover" style="top: ${event.pageY}px; left: ${event.pageX}px"><div class="schedule-popover-content delete-popover-content popover-content"><div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">删除</span></div></div></div>`);
          // cd.confirm({
          //   title: '删除',
          //   content: '确定要删除该空余时间段吗',
          //   okText: Translator.trans('site.confirm'),
          //   cancelText: Translator.trans('site.close'),
          // }).on('ok', () => {
          //   $(self.options['calendarContainer']).fullCalendar('removeEvents', () => {
          //     console.log('1111');
          //     return true;
          //   });
          // });
          // $target.popover({
          //   container: 'body',
          //   html: true,
          //   content: '<div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">删除</span></div>',
          //   template: `<div class="popover schedule-popover delete-popover">
          //             <div class="schedule-popover-content delete-popover-content popover-content">
          //             </div>
          //           </div>`,
          //   trigger: 'click'
          // });
          // $target.popover('show');


          return false;
        });
      },
      eventClick: function (calEvent, jsEvent, view) {
        // 静态删除 数据
        console.log(calEvent);
        console.log(jsEvent.button);
        console.log('点击');
        console.log(view);
        const $jsEvent = $(jsEvent.target);
        console.log($jsEvent);
        // $jsEvent.popover({
        //   container: 'body',
        //   html: true,
        //   content: '<div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">删除</span></div>',
        //   template: `<div class="popover schedule-popover delete-popover">
        //               <div class="schedule-popover-content delete-popover-content popover-content">
        //               </div>
        //             </div>`,
        //   trigger: 'click'
        // });
        // $jsEvent.popover('show');
        // console.log(self.options);
        console.log(calEvent._id);
        $('.js-delete-item').click(function() {
          console.log(calEvent._id);
          $(self.options['calendarContainer']).fullCalendar('removeEvents', calEvent._id);
        });


      },

      select: function(startDate, endDate, jsEvent, view, resource) {
        $('.delete-popover').addClass('hidden');
        // 选中后触发组件
        self.createEvent(startDate, endDate, jsEvent);
        console.log(self.events);
        $(self.options['calendarContainer']).fullCalendar('renderEvent', self.events);
        // self.initEvent();
        // $(this.options['calendarContainer']).fullCalendar('updateEvent', this.events);
      },
    };
    if (calendarOptions['defaultView'] == 'agendaWeek') {
      calendarOptions['columnFormat'] = 'ddd DD';
    }

    $(this.options['calendarContainer']).fullCalendar(calendarOptions);
  }


  createEvent(startDate, endDate, jsEvent) {
    console.log($(jsEvent.target));
    // $(jsEvent.target).popover({
    //   container: 'body',
    //   html: true,
    //   content: `<div class="cd-text-medium cd-mb8">排课时间：</div>
    //             <div class="schedule-popover-content__time js-date cd-dark-minor cd-mb8">${startDate.format('l')}</div>
    //             <div class="cd-mb8" data-time="${startDate.format()}"><input class="time-input js-time-start form-control" value=${startDate.format('HH:mm')} name="startTime"> — <input class="time-input js-time-end form-control" name="endTime"  data-time="${endDate.format()}" value=${endDate.format('HH:mm')}></div>`,
    //   template: `<div class="popover arrangement-popover">
    //               <div class="arrangement-popover-content popover-content">
    //               </div>
    //             </div>`,
    //   trigger: 'click'
    // });
    // $(jsEvent.target).popover('show');

    // $('.arrangement-popover').prevAll('.arrangement-popover').remove();
    this.events = {
      start: startDate.format(),
      end: endDate.format(),
    };
    this.initEvent();
  }

  deleteEvent() {
    const self = this;
    $('body').on('click', '.js-delete-item', (event) => {
      const $target = $(event.target);
      $(self.options['calendarContainer']).fullCalendar('removeEvents', () => {
        return true;
      });
      $target.parents('.delete-popover').remove();
    });
  }

  initEvent() {
    $('.js-time-start').change((event) => {
      const $target = $(event.target);
      const date = $target.parent().data('time');
      const newStart = date.substr(0, 11) + $target.val();
      console.log(newStart);
      const updateEvent = {
        start: newStart,
        end: $target.next().data('time')
      };
      console.log(updateEvent);
      this.events.start = newStart;
      // $(this.options['calendarContainer']).fullCalendar('renderEvent', this.events);
      $(this.options['calendarContainer']).fullCalendar('updateEvent', this.events);

    });

    $('.js-time-end').change((event) => {
      const $target = $(event.target);
      const date = $target.parent().data('time');
      const newEnd = date.substr(0, 11) + $target.val();
      console.log(newEnd);
      // this.events = {
      //   start: $target.prev().data('time'),
      //   end: newEnd
      // };
      this.events.end = newEnd;
      // $(this.options['calendarContainer']).fullCalendar('renderEvent', this.events);
      $(this.options['calendarContainer']).fullCalendar('updateEvent', this.events);
    });

  }

}