/**
 * @param options 
    {
        data: [   //与dataApi有一个必填
            {
                title: 'Meeting',
                start: '2017-11-12 10:30:00',
                end: '2017-11-12 12:30:00'
            }
        ],

        dataApi: Api.course.search, 
            //与data有一个必填, 用于获取数据，每次获取数据时，会带上开始和结束时间，见 dateParams
            // 需要使用 common/api/index.js 指定的路由

        calendarContainer: '#calendar',  
            //必填，日历控件容器

        attrs: {'title': 'name', 'start': 'createdTime', 'end': 'finishedTime'},
            //当 dataApi有值时，必填， 用于处理接口返回的数据，
            //  例子的意思为将返回json中的
            //    name ==> 标题，
            //    createdTime ==> 开始时间,
            //    finishedTime ==> 结束时间
            //    时间格式必须为 yyyy-MM-dd HH:mm:ss

        dateParams: {'start': 'createdTime_GE', 'end': 'createdTime_LT'},
            //当 dataApi有值时，可指定请求url中的时间参数名字
            // start 是当前页日历开始时间， 例子中使用 startTime_GE 属性
            // end 是当前页日历结束时间， 例子中使用 createdTime_LT 属性
            // 后台搜索的数据需符合 start <= 时间 < end
            // 不填时，使用例子中的默认值

        currentTime: '2017-11-12',
            //必填，用于计算json中的时间是否为过去，今天还是未来
            // 各种时间会加上相应的class
            // 过去 ==> calendar-before
            // 今天 ==> calendar-today
            // 未来 ==> calendar-future

        defaultView: 'month',
            //默认显示为月, 范围为 month,agendaWeek,agendaDay,listWeek

        switchers: 'month,agendaWeek',  
            //显示到日历顶部中间，用于切换
            // 范围为 month,agendaWeek,agendaDay,listWeek
            // 显示分为 月，周，日，日程，如果要显示多个，以逗号分割即可，如month,listweek
        
        components: [new LiveTooltipComp(), new ClickComp('/course/{id}')]
            // 非必填，用于出发事件的组件，如tooltip, 点击跳转事件
 * }
 */
let current;
export default class CustomFullCalendar {
  constructor(options = {}) {
    current = this;
    this.options = options;

    if (this._verifyNeccessaryFields()) {
      this._fillDefaultFields();
      this._init();
    }
  }

  _init() {
    let calendarOptions = {
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
      scrollTime: '08:00:00', //默认移动到　８点位置
    };

    if (calendarOptions['defaultView'] == 'agendaWeek') {
      calendarOptions['columnFormat'] = 'ddd DD';
    }

    if (typeof this.options['switcher'] != 'undefined') {
      calendarOptions['headers']['left'] = this.options['switchers'];
    }

    if (typeof this.options['data'] != 'undefined') {
      let events = this.options['data'];
      calendarOptions['events'] = this._generateEventOtherAttrs(events, events);
    }

    if (this.options['locale'] != 'en') {
      require('libs/fullcalendar/locale/' + this.options['locale']);
    }

    if (typeof this.options['dataApi'] != 'undefined') {
      calendarOptions['lazyFetching'] = true;
      calendarOptions['events'] = this._ajaxLoading;
    } else if (typeof this.options['data'] != 'undefined') {
      calendarOptions['events'] = this.options['data'];
    }

    calendarOptions = this._registerCompActions(calendarOptions);
    this.calendarOptions = calendarOptions;

    $(this.options['calendarContainer']).fullCalendar(calendarOptions);
  }

  _ajaxLoading(start, end, timezone, callback) {
    $('.fc-day-header span').hide();
    let startTimeAttr = current.options['dateParams']['start'];
    let endTimeAttr = current.options['dateParams']['end'];
    let params = {};
    params[startTimeAttr] = current._getDateStartUnixTime(start);
    params[endTimeAttr] = current._getDateStartUnixTime(end);
    params['limit'] = 1000;
    current.options['dataApi']({
      data: params
    }).then((result) => {
      let calEvents = [];
      for (let i = 0; i < result['data'].length; i++) {
        calEvents.push(current._generateEventInitValues(result['data'][i]));
      }
      calEvents = current._generateEventOtherAttrs(calEvents, result['data']);
      callback(calEvents);
    }).catch((res) => {
      console.log('error callback')
    });
  }

  _generateEventOtherAttrs(events, data) {
    for (let i = 0; i < events.length; i++) {
      $.extend(events[i], this._generateEventCompValues(data[i]));
      events[i] = this._addDateClassToEvent(events[i]);
    }

    return events;
  }

  _addDateClassToEvent(event) {

    let startUnixTime = this._getDateStartUnixTime(moment(event['start']));
    let currentUnixTime = this._getDateStartUnixTime(moment());
    let endUnixTime = this._getDateStartUnixTime(moment(event['end']));

    if (endUnixTime < currentUnixTime) {
      event['className'].push('calendar-before');
    } else if (currentUnixTime < startUnixTime) {
      event['className'].push('calendar-future');
    } else  {
      event['className'].push('calendar-today');
    }
    return event;
  }

  _verifyNeccessaryFields() {
    if (typeof this.options['data'] == 'undefined' && typeof this.options['dataApi'] == 'undefined') {
      console.log('custom-full-calendar: no "data" or "dataApi" in options');
      return false;
    }

    if (typeof this.options['dataApi'] != 'undefined' && typeof this.options['attrs'] == 'undefined') {
      console.log('custom-full-calendar: no "attrs" in options');
      return false;
    }

    if (typeof this.options['calendarContainer'] == 'undefined') {
      console.log('custom-full-calendar: no "calendarContainer" in options');
      return false;
    }

    if (typeof this.options['currentTime'] == 'undefined') {
      console.log('custom-full-calendar: no "currentTime" in options');
      return false;
    }

    return true;
  }

  _fillDefaultFields() {
    this._fillIfEmpty({
      'defaultView': 'month',
      'locale': 'zh-cn',
      'dateParams': {
        'start': 'createdTime_GE',
        'end': 'createdTime_LT'
      },
      'components': []
    });
  }

  _fillIfEmpty(params) {
    for (let key in params) {
      if (typeof this.options[key] == 'undefined' || this.options[key] == null) {
        this.options[key] = params[key];
      }
    }
  }

  /*
   * 获取所给时间的0小时0分0秒的时间戳 
   * @param momentObj 
   */
  _getDateStartUnixTime(momentObj) {
    let dateStr = momentObj.format('YYYY-MM-DD HH:mm:ss');
    return moment(dateStr).unix();
  }

  _registerCompActions(calendarOptions) {
    for (let i = 0; i < this.options['components'].length; i++) {
      calendarOptions = this.options['components'][i].registerAction(calendarOptions);
    }
    return calendarOptions;
  }

  _generateEventInitValues(singleResult) {
    let copiedFields = ['title', 'start', 'end'];
    let singleEvent = {};
    for (let i = 0; i < copiedFields.length; i++) {
      let fieldName = copiedFields[i];
      singleEvent[fieldName] = singleResult[this.options['attrs'][fieldName]];
    }
    singleEvent['className'] = [];
    return singleEvent;
  }

  _generateEventCompValues(singleResult) {
    let singleEvent = {};
    for (let i = 0; i < this.options['components'].length; i++) {
      $.extend(singleEvent, this.options['components'][i].generateEventValues(singleResult));
    }
    return singleEvent;
  }

}