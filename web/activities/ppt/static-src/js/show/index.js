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

initPptPlayer();



$('.js-change-ppt-btn').on('click', (event) => {
  console.log('点击数时间');
  currentPPTPlayer = currentPPTPlayer === 'img' ? 'slide' : 'img';
  initPptPlayer();
})


function initPptPlayer() {
  if ($element.data('imgType') === 'onlyImg') {
    initPPTNormalPlayer();
  } else {
    if (currentPPTPlayer ===  'img') {
      initPPTImgPlayer();
    } else if (currentPPTPlayer === 'slide') {
      initPPTNormalPlayer();
    }
  }
}


function initPPTImgPlayer() {
  let images = $element.data('imageInfo');
  const imgPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    // 环境配置
    // playServer: app.cloudPlayServer,
    // sdkBaseUri: app.cloudSdkBaseUri,
    // disableDataUpload: app.cloudDisableLogReport,
    // disableSentry: app.cloudDisableLogReport,
    // resNo: $element.data('resNo'),
    // token: $element.data('token'),
    // user: {
    //   id: $element.data('userId'),
    //   name: $element.data('userName')
    // },
    source: {
      type: 'ppt',
      args: {
        player: "ppt",
        images,
        type: "img",
      }
    },
  });

  imgPlayer.on('img.poschanged', (obj) => {
    const page = Number(obj.pageNum);
  });

  imgPlayer.on('img.ready', () => {
    imgPlayer.setCurrentPage(1);
  });
}

// 包括旧版的img播放器和ppt播放器
function initPPTNormalPlayer() {
  new QiQiuYun.Player({
    id: 'activity-ppt-content',
    // 环境配置
    playServer: app.cloudPlayServer,
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  });
}