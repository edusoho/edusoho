$('.js-generate-replay').on('click', (event) => {
  const $this = $(event.currentTarget);
  const url = $this.data('url');
  if (!url) return;
  Promise.resolve($.post(url))
    .then(success => {
      cd.message({ type: 'success', message: Translator.trans('course.manage.live_replay_generate_success')});
      window.location.reload();
    })
    .catch(response => {
      const error = JSON.parse(response.responseText);
      const code = error.code;
      const message = error.error;
      cd.message({ type: 'danger', message: Translator.trans('course.manage.live_replay_generate_error')});
    });
});