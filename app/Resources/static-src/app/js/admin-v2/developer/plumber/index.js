import notify from 'common/notify';

if ($(".js-operator-plumber").length) {
  $("body").on('click', '.js-operator-plumber', function () {
    $(this).attr('disabled', true);
    let action = $(this).data('action');
    notify('warning', Translator.trans('admin_v2.developer.plumber_operate_hint'));

    $.post($('#plumber-info').data('url'), {
      action: action,
      _csrf_token: $('meta[name=csrf-token]').attr('content')
    }, function (response) {
      if (action == 'stop') {
        window.location.reload();
        return;
      }
      getPlumberInfo();
      $(this).removeAttr('disabled');
    });
  });
}

function getPlumberInfo() {
  setTimeout(function () {
    $.get($('#plumber-info').data('url'), function (template) {
      console.log(template);
      if (template.length) {
        $('#plumber-info').html(template);
        return;
      }

      getPlumberInfo();
    });
  }, 1000);
}
