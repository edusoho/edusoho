import notify from 'common/notify';

$(document).on('click', '[data-toggle="notify"]', function() {
  notify('success', '这是警告消息<a href="http://baidu.com" class="notify-action">操作</a>');
})
