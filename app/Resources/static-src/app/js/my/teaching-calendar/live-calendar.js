import CustomFullCalendar from 'app/common/custom-full-calendar'

new CustomFullCalendar({
  'calendarContainer': '#calendar',
  'dataUrl': '/api/courses',
  'attrs': {
    'title': 'title',
    'start': 'createdTime',
    'end': 'updatedTime'
  },
  'currentTime': $("#todayDateStr").html(),
  'tooltipParams': '{id},{title}'
});