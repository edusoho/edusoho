import ActivityEmitter from 'app/js/activity/activity-emitter';

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
let currentPPTPlayer = $element.data('type') || 'slide';
let tokenUrl = $element.data('tokenUrl');
const images = $element.data('imageInfo');
const totalPagesNumber = Number(images.length);
const finishType = $element.data('finishType');
const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端


const iosFullScreen = () => {
  if (isIOS) {
    if ($('.js-change-ppt-btn').length != 0) {
      $('.js-change-ppt-btn').toggleClass('hidden');
    }
    $('#task-content-iframe', parent.document).toggleClass('ios-ppt-full-screen');
  }
};

const initPptPlayer = (flag) => {
  // 清空内容后切换
  $element.empty();
  if ($element.data('imgType') === 'onlyImg') {
    initPPTNormalPlayer();
  } else {
    if (currentPPTPlayer === 'img') {
      initPPTImgPlayer();
    } else if (currentPPTPlayer === 'slide') {
      if (flag) {
        changePPTNormalPlayer();
      } else {
        initPPTNormalPlayer();
      }
    }
  }
};

const toggleText = (type) => {
  if (!$('.js-change-ppt-btn').length || type === '') {
    return;
  }
  const $toggleBtn = $('.js-change-ppt-btn');
  const textStr = `course.plan_task.activity_ppt_animation_${type}`;
  $toggleBtn.html(Translator.trans(textStr)).attr('disabled', false);
};

// 触发任务finish状态
const endFinishTip = (pageNumber) => {
  if ($element.data('finishType') === 'end') {
    if (totalPagesNumber === 1) {
      emitter.emit('finish', {page: 1});
    } else {
      const page = Number(pageNumber);
      if (totalPagesNumber === page) {
        emitter.emit('finish', {page});
      }
    }
  }
};

const newPlayer = (token) => {
  let finalToken = token ? token : $element.data('token');
  const pptPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    // 环境配置
    playServer: app.cloudPlayServer,
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: finalToken,
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  });

  pptPlayer.on('slide.ready', (data) => {
    const type = token ? 'img' : '';
    toggleText(type);
    endFinishTip();
  });

  pptPlayer.on('slide.pagechanged', (data) => {
    endFinishTip(data.page);
  });

  pptPlayer.on('img.requestFullscreen', () => {
    iosFullScreen();
  });

  pptPlayer.on('slide.requestFullscreen', () => {
    iosFullScreen();
  });

  // 监听老图片
  pptPlayer.on('img.ready', () => {
    endFinishTip();
  });

  pptPlayer.on('img.poschanged', (data) => {
    endFinishTip(data.pageNum);
  });
};

// 兼容老ppt，默认就是时候img-player，不用切换
const initPPTNormalPlayer = () => {
  newPlayer();
};

const initPPTImgPlayer = () => {
  const imgPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    source: {
      type: 'ppt',
      args: {
        player: 'ppt',
        images,
        type: 'img',
      }
    },
  });

  imgPlayer.on('img.ready', () => {
    const type = 'slide';
    toggleText(type);
    endFinishTip();
  });

  imgPlayer.on('img.requestFullscreen', () => {
    iosFullScreen();
  });

  imgPlayer.on('img.poschanged', (data) => {
    endFinishTip(data.pageNum);
  });
};

// img 切换成 slide播放器
const changePPTNormalPlayer = () => {
  $.get(tokenUrl).then(res => {
    newPlayer(res.result.token);
  });
};

initPptPlayer();


$('.js-change-ppt-btn').on('click', (event) => {
  const $target = $(event.target);
  $target.html(Translator.trans('site.loading')).attr('disabled', true);
  currentPPTPlayer = currentPPTPlayer === 'img' ? 'slide' : 'img';
  $element.data('type', currentPPTPlayer);
  initPptPlayer(true);
});
