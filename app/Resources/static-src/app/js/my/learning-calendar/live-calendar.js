import CustomFullCalendar from 'app/common/calendar/custom-full-calendar';
import LiveTooltipComp from 'app/common/calendar/comp/tooltip/live-tooltip-comp';
import ClickComp from 'app/common/calendar/comp/click-comp';
import Api from 'common/api';

new CustomFullCalendar({
  'calendarContainer': '#calendar',
  'dataApi': Api.studentLiveCourse.search, //需要使用 common/api/index.js 指定的路由
  'attrs': {
    'title': 'title',
    'start': 'startTime',
    'end': 'endTime'
  },
  'dateParams': {'start': 'startTime_GE', 'end': 'endTime_LT'},
  'currentTime': $('#todayDateStr').html(),
  'components': [
    new LiveTooltipComp(),
    new ClickComp('{url}') //routing course_show
  ],
  'defaultView': 'month' // 'agendaWeek'
});