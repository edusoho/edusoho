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
const totalPagesNumber = images.length;
console.log(totalPagesNumber);

const initPptPlayer = (flag) => {
  // 清空内容后切换
  $element.empty();
  if ($element.data('imgType') === 'onlyImg') {
    initPPTNormalPlayer();
  } else {
    if (currentPPTPlayer ===  'img') {
      initPPTImgPlayer();
    } else if (currentPPTPlayer === 'slide') {
      if (flag) {
        changePPTNormalPlayer();
      } else {
        initPPTNormalPlayer();
      }
    }
  }
}

const newPlayer = (token) => {
  let finalToken = token ? token: $element.data('token');
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
    if (data.total === 1) {
      emitter.emit('finish', data);
    }
  })

  pptPlayer.on('slide.pagechanged', (data) => {
    if (data.page === data.total) {
      emitter.emit('finish', data);
    }
  });

  // 监听老图片
  pptPlayer.on('img.poschanged', (data) => {
    const page = Number(data.pageNum);
    if (page === totalPagesNumber) {
      console.log('finish');
      emitter.emit('finish', { page });
    }
  });
}



// 兼容老ppt，默认就是时候img-player，不用切换
const initPPTNormalPlayer = () => {
  newPlayer();
}

const initPPTImgPlayer = () => {
  const imgPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    source: {
      type: 'ppt',
      args: {
        player: "ppt",
        images,
        type: "img",
      }
    },
  });

  imgPlayer.on('img.poschanged', (data) => {
    const page = Number(data.pageNum);
    if (page === totalPagesNumber) {
      emitter.emit('finish', { page });
    }
  });
}

// img 切换成 slide播放器
const changePPTNormalPlayer = () => {
  $.get(tokenUrl).then(res => {
    newPlayer(res.result.token);
  })
}

initPptPlayer();


$('.js-change-ppt-btn').on('click', (event) => {
  const $target = $(event.target);
  currentPPTPlayer = currentPPTPlayer === 'img' ? 'slide' : 'img';
  $element.data('type', currentPPTPlayer);
  const text = currentPPTPlayer === 'img' ?  Translator.trans('course.plan_task.activity_ppt_animation_slide'): Translator.trans('course.plan_task.activity_ppt_animation_img');
  $target.text(text);
  initPptPlayer(true);
})