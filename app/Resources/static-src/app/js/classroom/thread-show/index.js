import ThreadShowWidget from 'app/js/thread/thread-show';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';

var threadShowWidget = new ThreadShowWidget({
  element: '.class-detail-content'
});

new AttachmentActions($('#thread-post-form'));

var $onlyTeacherBtnHtml = $('.js-only-teacher-div').html();
$('.class-detail-content').find('.js-all-post-head').append($onlyTeacherBtnHtml);

$('.class-detail-content').on('click', '.js-only-teacher', function () {
  var $self = $(this);
  var $filter = $self.hasClass('active') ? '' : '?adopted=1';
  var $url = $self.data('url') + $filter;
  document.location.href = $url;
});

var $userIds = '';
$('.class-detail-content').find('.thread-post').each(function () {
  $userIds += $(this).data('userId') + ',';
});
$userIds = $userIds.substring(0, $userIds.length - 1);
$.get($('#isTeachersUrl').val() + '?ids=' + $userIds, function (ids) {
  var $idArray = ids.split(',');
  for (var i = 0; i < $idArray.length; i++) {
    $('.class-detail-content').find('.user-id-' + $idArray[i]).each(function () {
      $(this).addClass('teacher');
    });
  }
});




