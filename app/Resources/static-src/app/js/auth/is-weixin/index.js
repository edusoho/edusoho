var ua = window.navigator.userAgent.toLowerCase();
if (ua.match(/MicroMessenger/i) == 'micromessenger') {
  var url = '/login';
  var inviteCode = $('#invite_code');
  if (inviteCode.length > 0) {
    url = url + '?inviteCode=' + inviteCode.val();
  }

  window.location.href = url;
}