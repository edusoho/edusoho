export const EventReport = () => {
  let subjectId = $("#event").data('subjectId');
  let userId = $("#event").data('userId');
  $.post('/event/dispatch', {eventName: 'course.preview', subjectId: subjectId, subjectType: 'course', userId: userId});
}
