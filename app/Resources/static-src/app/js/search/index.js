import notify from 'common/notify';

let $errorType = $('.with-error').data('type');
if ($errorType === 'cloudSearchError') {
  notify('danger', Translator.trans('cloud_search.network.error_message'),{delay:2000});
}