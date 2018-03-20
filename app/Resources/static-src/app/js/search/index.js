import notify from 'common/notify';
echo.init();

let $errorType = $('.with-error').data('type');
console.log(12111);
if ($errorType === 'cloudSearchError') {
  console.log(12144411);
  notify('danger', Translator.trans('网络不给力，为您切换极简搜索模式'),{delay:2000});
}