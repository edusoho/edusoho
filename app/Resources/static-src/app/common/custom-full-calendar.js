import ajax from '../../common/api/ajax'

/**
 * @param options 
    {
        data: [   //与dataUrl有一个必填
            {
                title: 'Meeting',
                start: '2017-11-12 10:30:00',
                end: '2017-11-12 12:30:00'
            }
        ],

        dataUrl: 'teacher/live/schedules', 
            //与data有一个必填

        calendarContainer: '#calendar',  
            //必填，日历控件容器

        attrs: {'title': 'name', 'start': 'createdTime', 'end': 'finishsedTime'},
            //当 dataUrl有值时，必填， 用于处理接口返回的数据，
            //  例子的意思为将返回json中的
            //    name ==> 标题，
            //    createdTime ==> 开始时间,
            //    finishedTime ==> 结束时间
            //    时间格式必须为 yyyy-MM-dd HH:mm:ss

        dateParams: {'start': 'createdTime_GE', 'end': 'createdTime_LT'},
            //当 dataUrl有值时，可指定请求url中的时间参数名字
            // start 是当前页日历开始时间， 例子中使用 startTime_GE 属性
            // end 是当前页日历结束时间， 例子中使用 createdTime_LT 属性
            // 后台搜索的数据需符合 createdTime_GE <= 时间 < createdTime_LT
            // 不填时，使用例子中的默认值

        currentTime: '2017-11-12',
            //必填，用于计算json中的时间是否为过去，今天还是未来
            // 各种时间会加上相应的class
            // 过去 ==> calendar-before
            // 今天 ==> calendar-today
            // 未来 ==> calendar-future

        clickUrlTemplate: 'teacher/{id}',
            //非必填， 填了， 表示点击会跳转到相应的url
            // {id} 表示使用了id参数，必须在dataUrl或data内的json内有相应的参数

        display: 'month',  
            //默认值为month, 范围为 month,agendaWeek,agendaDay,listWeek
            // 显示分为 月，周，日，日，如果要显示多个，以逗号分割即可，如month,listweek
            // 如果只显示一种，则隐藏按钮的显示

        tooltipParams: '{title},{id}',
            //非必填，填了鼠标移到/离开单元格上会触发 showTipFormatFunc/shideTooltipFormatFunc
            // 表示tooltip接受的参数, 参数从 data 或 dataUrl 中返回的数据中获得，取相应的属性

        showTipFormatFunc: function
            //非必填，显示tooltip使用的函数，默认使用 _showTip 方法

        hideTooltipFormatFunc: function
            //非必填，隐藏tooltip使用的函数，默认使用 _hideTip 方法
 * }
 */
let currentFullCalendar;
export default class CustomFullCalendar {
  constructor(options = {}) {
    currentFullCalendar = this;
    this.options = options;

    if (this._verifyNeccessaryFields()) {
      this._fillDefaultFields();
      this._init();
    }
  }

  _init() {
    let calendarOptions = {
      header: {
        left: this.options['display'],
        center: 'title',
        right: 'prev,today,next'
      },
      defaultDate: this.options['currentTime'],
      navLinks: true, // can click day/week names to navigate views
      editable: true,
      eventLimit: true, // allow "more" link when too many events
      locale: this.options['locale']
    };
    if (typeof this.options['data'] != 'undefined') {
      calendarOptions['events'] = this.options['data'];
    }

    if (this.options['locale'] != 'en') {
      require('libs/fullcalendar/locale/' + this.options['locale']);
    }

    if (typeof this.options['dataUrl'] != 'undefined') {
      calendarOptions['lazyFetching'] = true;
      calendarOptions['events'] = this._ajaxLoading;
    }

    if (typeof this.options['tooltipParams'] != 'undefined') {
      calendarOptions['eventMouseover'] = this._dealShowTip;
      calendarOptions['eventMouseout'] = this._dealHideTip;
    }

    $(this.options['calendarContainer']).fullCalendar(calendarOptions);

    this._hideDisplayDatesIfNeed();
  }

  _ajaxLoading(start, end, timezone, callback) {
    let startTimeAttr = currentFullCalendar.options['dateParams']['start'];
    let endTimeAttr = currentFullCalendar.options['dateParams']['end'];
    let params = {};
    params[startTimeAttr] = currentFullCalendar._getStartUnixTime(start);
    params[endTimeAttr] = currentFullCalendar._getEndUnixTime(end);

    ajax({
      url: currentFullCalendar.options['dataUrl'],
      type: 'GET',
      data: params,
      success: function(result) {
        let calEvents = [];
        for (let i = 0; i < result['data'].length; i++) {
          calEvents.push(currentFullCalendar._generateEvent(result['data'][i]));
        }
        callback(calEvents);
      }
    });
  }

  _generateEvent(singleResult) {
    let copiedFields = ['title', 'start', 'end'];
    copiedFields = copiedFields.concat(this._generateTooltipParamsNames());
    let singleEvent = {};
    for (let i = 0; i < copiedFields.length; i++) {
      let fieldName = copiedFields[i];
      if (typeof this.options['attrs'][fieldName] != 'undefined') {
        singleEvent[fieldName] = singleResult[this.options['attrs'][fieldName]];
      } else {
        let tooltipName = this._getTooltipOriginalParamName(fieldName);
        singleEvent[fieldName] = singleResult[tooltipName];
      }
    }
    return singleEvent;
  }

  _verifyNeccessaryFields() {
    if (typeof this.options['data'] == 'undefined' && typeof this.options['dataUrl'] == 'undefined') {
      console.log('custom-full-calendar: no "data" or "dataUrl" in options');
      return false;
    }

    if (typeof this.options['dataUrl'] != 'undefined' && typeof this.options['attrs'] == 'undefined') {
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
    this._fillIfEmpty('locale', 'zh-cn');
    this._fillIfEmpty('display', 'month');
    this._fillIfEmpty('dateParams', {
      'start': 'createdTime_GE',
      'end': 'createdTime_LT'
    });
  }

  _fillIfEmpty(key, defaultValue) {
    if (typeof this.options[key] == 'undefined' || this.options[key] == null) {
      this.options[key] = defaultValue;
    }
  }

  // 只显示了月或周或日或日程，则隐藏按钮
  _hideDisplayDatesIfNeed() {
    if (this.options['display'].indexOf(',') == -1) {
      $(this.options['calendarContainer']).find('.fc-left').hide();
    }
  }

  _getStartUnixTime(start) {
    let result = moment.unix(start.unix());
    let dateStr = result.format('YYYY-MM-DD');

    return moment(dateStr).unix();
  }

  _getEndUnixTime(end) {
    let result = moment.unix(end.unix());
    let dateStr = result.format('YYYY-MM-DD');

    return moment(dateStr).unix();
  }

  _dealShowTip(event, jsEvent, view) {
    let params = currentFullCalendar._generateTooltipParams(event);
    if (currentFullCalendar.options['showTipFormatFunc']) {
      currentFullCalendar.options['showTipFormatFunc'](params, event, jsEvent);
    } else {
      currentFullCalendar._showTip(params, event, jsEvent);
    }
  }

  _dealHideTip(event, jsEvent, view) {
    let params = currentFullCalendar._generateTooltipParams(event);
    if (currentFullCalendar.options['hideTipFormatFunc']) {
      currentFullCalendar.options['hideTipFormatFunc'](params, event, jsEvent);
    } else {
      currentFullCalendar._hideTip(params, event, jsEvent);
    }
  }

  _showTip(params, event, jsEvent) {
    console.log(params);
  }

  _hideTip(params, event, jsEvent) {
    console.log(params);
  }

  _generateTooltipParamsNames() {
    if (typeof this.options['paramNames'] == 'undefined') {
      let unformatedParamsNames = this.options['tooltipParams'].split(',');
      let paramNames = [];
      for (let i = 0; i < unformatedParamsNames.length; i++) {
        let paramName = unformatedParamsNames[i].split('{')[1].split('}')[0];
        paramNames.push(this._getTooltipFormatedParamName(paramName));
      }
      this.options['paramNames'] = paramNames;
    }
    return this.options['paramNames'];
  }

  _generateTooltipParams(event) {
    let params = {};
    let paramNames = this._generateTooltipParamsNames();
    for (let i = 0; i < paramNames.length; i++) {
      let paramName = paramNames[i];
      let tooltipName = this._getTooltipOriginalParamName(paramName);
      params[tooltipName] = event[paramName];
    }
    return params;
  }

  _getTooltipOriginalParamName(paramName) {
    return paramName.split('tooltip_')[1];
  }

  _getTooltipFormatedParamName(paramName) {
    return 'tooltip_' + paramName;
  }

}