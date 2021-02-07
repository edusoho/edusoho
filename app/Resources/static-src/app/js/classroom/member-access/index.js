$('#member-access').on('click', function(event) {
  $.get($(this).data('url'), function (html) {
    $('#modal').modal('show').html(html);
  });
});