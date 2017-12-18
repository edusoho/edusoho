import CustomFullCalendar from 'app/common/calendar/custom-full-calendar'
import LiveTooltipComp from 'app/common/calendar/comp/tooltip/live-tooltip-comp'
import ClickComp from 'app/common/calendar/comp/click-comp'

new CustomFullCalendar({
  'calendarContainer': '#calendar',
  'dataUrl': '/api/courses',
  'attrs': {
    'title': 'title',
    'start': 'createdTime',
    'end': 'updatedTime'
  },
  'currentTime': $('#todayDateStr').html(),
  'components': [
    new LiveTooltipComp(),
    new ClickComp('/course/{id}') //routing course_show
  ],
  'defaultView': 'month'
});