if ($('#checkin-data').length) {
  $.get($('#checkin-data').data('url'), function (response) {
    console.log(response);
  });
}

if ($('#learn-time-data').length) {
  $.get($('#learn-time-data').data('url'), function (response) {
    console.log(response);
  });
}