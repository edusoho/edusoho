$('.js-course-learn-data').on('click', '.pagination li', function () {
  let url = $(this).data('url');

  if (typeof (url) !== 'undefined') {
    $.get(url, (data) => {
      $('#modal').html(data);
    });
  }
});

$('#status-select').on('change', function () {
  $('#modal').load($(this).find("option:selected").data('url'));
})
