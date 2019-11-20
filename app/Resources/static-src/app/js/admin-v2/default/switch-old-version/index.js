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
      const url = $('.js-question-section').data('url');
      $.post(url, (res) => {
        if (res.status == 'success' && res.url) {
          window.location.href = res.url;
        }
      });
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