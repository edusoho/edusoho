export const publish = (element, info) => {
  $('body').on('click', element, (evt) => {
    const $target = $(evt.target);
    cd.confirm({
      title: Translator.trans(info.title),
      content: Translator.trans(info.hint),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close')
    }).on('ok', () => {
      $.post($target.data('url'), (data) => {
        if (data.success) {
          cd.message({ type: 'success', message: Translator.trans(info.success) });
          location.reload();
        } else {
          cd.message({ type: 'danger', message: Translator.trans(info.fail) + ':' + data.message, delay: '5000' });
        }
      });
    });
  });
};