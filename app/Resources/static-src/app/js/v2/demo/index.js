import notify from 'common/notify';

$(document).on('click', '[data-toggle="notify"]', function() {
  notify('danger', Translator.trans('notify.danger_operation_message'));
});

cd.table({
  cb($target, url) {
    $.get(url).done(function(html) {
      $target.html(html);
    }).fail(function() {
      notify('danger', Translator.trans('site.response_error'));
    });
  }
});

