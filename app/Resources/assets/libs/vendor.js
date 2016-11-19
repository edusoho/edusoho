import './vendor.less'; //先用es原来的全局样式，因为要考虑主题等

import 'jquery';
import 'bootstrap';
import 'bootstrap-notify';

import 'common/bootstrap-modal-hack';


$('[data-toggle="popover"]').popover();

$('[data-toggle="tooltip"]').tooltip();

$(document).ajaxSend(function(a, b, c) {
  if (c.type == 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});
