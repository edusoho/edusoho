let $element = $('#global-player');
import EsMessenger from 'app/common/messenger';

let play = new QiQiuYun.Player({
  id: 'global-player',
  playServer: app.cloudPlayServer,
  resNo: $element.data('resNo'),
  token: $element.data('token'),
  user: {
    id: $element.data('userId'),
    name: $element.data('userName')
  }
});

let messenger = new EsMessenger({
    name: 'parent',
    project: 'PlayerProject',
    type: 'child'
  });

play.on("video.timeupdate", (mes) => {
  messenger.sendToParent("video.timeupdate", mes);
});