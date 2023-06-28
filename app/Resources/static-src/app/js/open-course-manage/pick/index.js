import '../tab-manage';

let ids = [];
let $searchForm = $('.form-search');
let $sure = $('#sure');

$sure.on('click', function () {
  $sure.button('submiting').addClass('disabled');

  $.ajax({
    type: 'post',
    url: $('#sure').data('url'),
    data: { 'ids': ids },
    async: false,
    success: function (response) {
      if (!response['result']) {
        $sure.removeClass('disabled');
        cd.message({ type: 'danger', message: response['message']});
      } else {
        $('.modal').modal('hide');
        window.location.reload();
      }
    }
  });

});

$('#search').on('click', function () {

  $.get($searchForm.data('url'), $searchForm.serialize(), function (data) {

    $('#modal').html(data);
  });
});

$('#enterSearch').keydown(function (event) {

  if (event.keyCode == 13) {
    $.get($searchForm.data('url'), $searchForm.serialize(), function (data) {
      $('#modal').html(data);
    });
    return false;
  }
});



$('#all-courses').on('click', function () {
  $('input[name="key"]').val('');
  $.post($(this).data('url'), $('.form-search').serialize(), function (data) {
    $('#modal').html(data);
  });


});
ids = localStorage.getItem('ids') ? localStorage.getItem('ids').split(',') : [];
$('.js-course-wide-list .js-course-item').each(function (i, element){
	const id = element.getAttribute('data-id');
	if (ids.includes(id)) {
		element.className += ' select'
    $('.js-course-metas-' + id).show();
	}
});
$('.row').on('click', '.course-item ', function () {

  let id = $(this).data('id');

  if ($(this).hasClass('enabled')) {
    return;
  }

  if ($(this).hasClass('select')) {

    $(this).removeClass('select');
    $('.course-metas-' + id).hide();

    ids = $.grep(ids, function (val, key) {
      if (val != id)
        return true;
    }, false);
		localStorage.setItem('ids', ids);

  } else {
    $(this).addClass('select');
    $('.course-metas-' + id).show();
    ids.push(id);
		localStorage.setItem('ids', ids)
  }
});



