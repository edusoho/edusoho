import notify from 'common/notify';

$('ul[role="tablist"]').on('click', 'li[role="presentation"]', function () {
  $('li[role="presentation"]').attr('class', '');
  $(this).attr('class', 'active');

  $('.es-piece').attr('style', 'display: none; visibility: hidden; ');
  let label = $(this).attr('id');
  $('div[data-label="' + label + '"]').attr('style', '');
});

$('#js-course-select-btn').on('click', function (e) {
  var chosenCourse = $(this);
  chosenCourse.attr('disabled', 'true');
  chosenCourse.text('处理中...');

  $.post($(this).data('chooseUrl'), {courseSetData: $(this).data('courseSet')}, function (response) {
    if (response.status === 'repeat') {
      notify('danger', Translator.trans('已选择过该课程'));
    } else if (response.status === true) {
      notify('success', '已选择，请到“课程管理”查看并进行营销配置');
    } else {
      notify('danger', Translator.trans('意外错误，操作失败，请联系管理员处理！'));
      return;
    }
    chosenCourse.attr('disabled', 'true');
    chosenCourse.attr('style', 'width: 130px; background-color: #CCCCCC; border-color: #CCCCCC');
    chosenCourse.text('已选择');
  }).error(function () {
    chosenCourse.text('选择');
    chosenCourse.attr('disabled', false);
    notify('danger', Translator.trans('网络波动，请重试！'));
  });
});