import notify from 'common/notify';

$('.js-generate-replay').on('click', (event) => {
  const $this = $(event.currentTarget);
  const url = $this.data('url');
  if (!url) return;
  Promise.resolve($.post(url))
    .then(success => {
      notify('success', '生成录制回放成功');
      window.location.reload();
    })
    .catch(response => {
      const error = JSON.parse(response.responseText);
      const code = error.code;
      const message = error.error;
      notify('danger', '发生了异常，请稍后重试');
    });
});
