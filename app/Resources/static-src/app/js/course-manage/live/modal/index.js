$('#cd-tabs a').click(function () {
  $.get($(this).data('url'), function (response) {
    $('#modal').html(response);
  });
});

