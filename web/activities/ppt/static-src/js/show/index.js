import ActivityEmitter from 'app/js/activity/activity-emitter';
import Api from 'common/api';

const emitter = new ActivityEmitter();
let url = $('.js-cloud-url').data('url');
(function (url) {
  window.QiQiuYun || (window.QiQiuYun = {});
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url + '?' + ~~(Date.now() / 1000 / 60), false); // 可设置缓存时间。当前缓存时间为1分钟。
  xhr.send(null);
  var firstScriptTag = document.getElementsByTagName('script')[0];
  var script = document.createElement('script');
  script.text = xhr.responseText;
  firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
})(url);

let $element = $('#activity-ppt-content');
let typeList = [];
let currentType = '';
let totalPagesNumber = '';
const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端

const iosFullScreen = () => {
  if (isIOS) {
    if ($('.js-change-ppt-btn').length != 0) {
      $('.js-change-ppt-btn').toggleClass('hidden');
    }
    $('#task-content-iframe', parent.document).toggleClass('ios-ppt-full-screen');
  }
};

const initPptPlayer = () => {
  // 清空内容后切换
  $element.empty();
  const pptPlayer = newPlayer();

  $('.js-change-ppt-btn').on('click', (event) => {
    const $target = $(event.target);
    $target.html(Translator.trans('site.loading')).attr('disabled', true);
    currentType = currentType === 'ppt-img' ? 'ppt-slide' : 'ppt-img';
    pptPlayer.switchPlayerType(currentType);
  });
};

const toggleText = (type) => {
  if (!$('.js-change-ppt-btn').length || type === '') {
    return;
  }
  type = type === 'ppt-img' ? 'ppt-slide' : 'ppt-img';
  const $toggleBtn = $('.js-change-ppt-btn');
  const textStr = `course.plan_task.activity_ppt_animation_${type}`;
  $toggleBtn.html(Translator.trans(textStr)).attr('disabled', false);
};

// 触发任务finish状态
const endFinishTip = (pageNumber) => {
  if ($element.data('finishType') === 'end') {
    if (totalPagesNumber === 1) {
      emitter.emit('finish', { page: 1 });
    } else {
      const page = Number(pageNumber);
      if (totalPagesNumber === page) {
        emitter.emit('finish', { page });
      }
    }
  }
};

const newPlayer = async () => {
  const playerConfig = {
    id: 'activity-ppt-content',
    // 环境配置
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  };
  const watermark = await Api.watermark.get('task');
  if (watermark.text) {
    playerConfig.fingerprint = {
      html: watermark.text,
      color: watermark.color,
      alpha: watermark.alpha,
    };
  }

  const pptPlayer = new QiQiuYun.Player(playerConfig);

  pptPlayer.on('ready', () => {
    toggleText(currentType);
    endFinishTip();
  });

  pptPlayer.on('pagechanged', (data) => {
    const page = currentType === 'ppt-slide' ? data.page : data.pageNum;
    endFinishTip(page);
  });

  pptPlayer.on('requestFullscreen', () => {
    iosFullScreen();
  });

  //播放器第一次加载时，可以获取能够播放的类型列表
  pptPlayer.on('sourceChanged', (data) => {
    console.log(data);
    typeList = data.typeList;
    currentType = typeList[0];
    totalPagesNumber = Number(data.resource.length);
    if (typeList.length > 1) {
      $('.js-change-ppt-btn').removeClass('hidden');
      toggleText(currentType);
    }
  });

  return pptPlayer;
};

initPptPlayer();
