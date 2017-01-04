import 'babel-polyfill';
import 'jquery';
import 'bootstrap';
import 'bootstrap-notify';

import 'common/bootstrap-modal-hack';
import 'common/script';

$('[data-toggle="popover"]').popover({
  html:true,
});

$('[data-toggle="tooltip"]').tooltip({
  html:true,
});


$(document).ajaxSend(function(a, b, c) {
  if (c.type == 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});

