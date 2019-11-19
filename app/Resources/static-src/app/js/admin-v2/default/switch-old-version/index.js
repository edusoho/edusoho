const $switchBtn = $('.js-switch-version-btn');
$switchBtn.on('click', () => {
  cd.confirm({
    title: Translator.trans('admin.switch_old_version.title'),
    content: Translator.trans('admin.switch_old_version.confirm_message'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.cancel'),
    className: '',
  }).on('ok', () => {
    $switchBtn.button('loading');
    $.post($switchBtn.data('url'), (res) => {
      if (res.status == 'success' && res.url) {
        window.location.href = res.url;
      }
    });
  }).on('cancel', () => {});
});


const iframe = document.getElementById('iframe');
iframe.onload = () => {
  iframe.contentWindow.postMessage(JSON.stringify({
    actions: 'init'
  }), '*');
};

const onMessage = function (event) {
  try {
    let handleData = event.data;
    if (typeof event.data !== 'object') {
      handleData = JSON.parse(event.data);
    }
    if (handleData.actions === 'reload') {
      $switchBtn.prop('disabled', false);
    }
  } catch (e) {
    console.log('catch');
  }
};

if (window.addEventListener) {
  window.addEventListener('message', onMessage, false);
} else {
  window.attachEvent('onmessage', onMessage);
}