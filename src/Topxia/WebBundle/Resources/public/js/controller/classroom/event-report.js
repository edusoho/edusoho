define(function (require, exports, module) {

  exports.run = function () {
    let subjectId = $("#event").data('subjectId');
    let userId = $("#event").data('userId');
    $.post('/event/dispatch', {eventName: 'classroom.view', subjectId: subjectId, subjectType: 'classroom', userId: userId});
  };

});