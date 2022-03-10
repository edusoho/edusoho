let $element = $('#global-player');
new QiQiuYun.Player({
  id: 'global-player',
  playServer: app.cloudPlayServer,
  sdkBaseUri: app.cloudSdkBaseUri,
  disableDataUpload: app.cloudDisableLogReport,
  disableSentry: app.cloudDisableLogReport,
  resNo: $element.data('resNo'),
  token: $element.data('token'),
  user: {
    id: $element.data('userId'),
    name: $element.data('userName'),
  },
  clientType: $element.data('clientType')
});

console.log($element.data('userId'));
console.log($element.data('userName'));
console.log($element.data('clientType'));