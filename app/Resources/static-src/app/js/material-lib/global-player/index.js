let $element = $('#global-player');
new QiQiuYun.Player({
  id: 'global-player',
  playServer: app.cloudPlayServer,
  resNo: $element.data('resNo'),
  token: $element.data('token'),
  user: {
    id: $element.data('userId'),
    name: $element.data('userName')
  }
});