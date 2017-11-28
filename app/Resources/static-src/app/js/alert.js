import Cookies from 'js-cookie';

$(document).on('click.alert.close', '[data-dismiss="alert"]', function() {
  let $this = $(this);
  let cookie = $this.data('cookie');
  if (cookie) {
    Cookies.set(cookie, 'true');
  }
});
