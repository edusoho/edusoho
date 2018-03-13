import 'store';
$('.js-course-avatar').on('click', function () {
  store.set('COURSE-GUEST-PAGE-URL', window.location.href);
  this.href = $(this).data('url');
});