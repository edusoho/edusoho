import 'codeages-design';

async function hanldeCanOnApp() {
  const { only_learning_on_APP } = await $.ajax({
    url: '/api/settings/course',
    headers: {
      Accept: 'application/vnd.edusoho.v2+json'
    }
  })

  if (only_learning_on_APP === 1) {
    cd.confirm({
      title: Translator.trans('activity.video.only_can_app_title'),
      content: Translator.trans('activity.video.only_can_app_desc'),
      okText: Translator.trans('site.confirm'),
      cancelText: false
    }).on('ok', () => {
      $('.js-back-link', window.parent.document)[0].click()
    });
  }
}

hanldeCanOnApp()