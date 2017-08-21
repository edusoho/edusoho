import notify from 'common/notify';

$(document).on('click', '[data-toggle="notify"]', function() {
  notify('danger', '这是警告消息<a href="http://demo.edusoho.com" class="notify-action">操作</a>');
})
