const $loginModal = $('#login-modal');

function loginAgain() {
  if ($('meta[name=wechat_login_bind]').attr('content') != 0) {
    window.location.href = '/login';

    return;
  }

  $('.modal').modal('hide');
  $loginModal.modal('show');

  $.get($loginModal.data('url'), function (html) {
    $loginModal.html(html);
  });
}

export {
  loginAgain
};