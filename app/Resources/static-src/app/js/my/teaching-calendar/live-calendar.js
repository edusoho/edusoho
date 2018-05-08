import CustomFullCalendar from 'app/common/calendar/custom-full-calendar';
import LiveTooltipComp from 'app/common/calendar/comp/tooltip/live-tooltip-comp';
import ClickComp from 'app/common/calendar/comp/click-comp';
import RightClickComp from 'app/common/calendar/comp/right-click-comp';
import Api from 'common/api';

const select = (startDate, endDate, jsEvent, view, resource) => {
  console.log('1111');
  alert('高亮');
  alert('selected ' + startDate.format() + ' to ' + endDate.format() + ' on resource ');
};

new CustomFullCalendar({
  'calendarContainer': '#calendar',
  'dataApi': Api.teacherLiveCourse.search, //需要使用 common/api/index.js 指定的路由
  'attrs': {
    'title': 'title',
    'start': 'startTime',
    'end': 'endTime'
  },
  'dateParams': {'start': 'startTime_GE', 'end': 'endTime_LT'},
  'currentTime': $('#todayDateStr').html(),
  'components': [
    new RightClickComp('{url}'),
    new LiveTooltipComp(),
    // new ClickComp('{url}') //routing course_show
  ],
  'defaultView': 'agendaWeek', // 'agendaWeek'
  'selectable': true,
  'select': select,
});