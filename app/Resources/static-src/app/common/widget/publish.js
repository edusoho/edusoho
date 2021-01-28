export const publish = (element, info) => {
  $('body').on('click', element, (evt) => {
    const $target = $(evt.target);

    let confirmPublish = function() {
      cd.confirm({
        title: Translator.trans(info.title),
        content: Translator.trans(info.hint),
        okText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.cancel')
      }).on('ok', () => {
        $.post($target.data('url'), (data) => {
          if (data.success) {
            cd.message({ type: 'success', message: Translator.trans(info.success), delay: '1000' });
            location.reload();
          } else {
            cd.message({ type: 'danger', message: Translator.trans(info.fail) + ':' + data.message, delay: '5000' });
          }
        });
      });
    };

    if ($target.data('preUrl')) {
      $.post($target.data('preUrl'), (data) => {
        if (data.success) { //多教学计划课程中的默认教学计划未设置过名称
          let loading = cd.loading({ isFixed: true });
          $('#modal').html(loading).modal({
            backdrop: 'static',
            keyboard: false
          }).load($target.data('saveUrl'));
        } else {
          confirmPublish();
        }
      });
    } else {
      confirmPublish();
    }
  });
};