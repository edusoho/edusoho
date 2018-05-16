import CustomFullCalendar from 'app/common/calendar/custom-full-calendar';
import LiveTooltipComp from 'app/common/calendar/comp/tooltip/live-tooltip-comp';
import ClickComp from 'app/common/calendar/comp/click-comp';
import SelectComp from 'app/common/calendar/comp/select-comp';
import Api from 'common/api';

new CustomFullCalendar({
  'selectable': true,
  'calendarContainer': '#calendar',
  'dataApi': Api.teacherLiveCourse.search, //需要使用 common/api/index.js 指定的路由
  'attrs': {
    'title': 'title',
    'start': 'startTime',
    'end': 'endTime'
  },
  'dateParams': { 'start': 'startTime_GE', 'end': 'endTime_LT' },
  'currentTime': $('#todayDateStr').html(),
  'components': [
    new SelectComp(),
    // new RightClickComp('{url}'),
    // new LiveTooltipComp(),
    // new ClickComp('{url}') //routing course_show
  ],
  'defaultView': 'agendaWeek', // 'agendaWeek'
});



// 暂时测试处理
// new CustomFullCalendar({
//   'calendarContainer': '#calendar',
//   'currentTime': $('#todayDateStr').html(),
//   'defaultView': 'agendaWeek',
//   'dataApi': Api.teacherLiveCourse.search,
//   'components': [
//     new SelectComp(),
//   ]
// });