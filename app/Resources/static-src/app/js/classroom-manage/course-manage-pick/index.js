var ids = [];

$('[data-toggle="tooltip"]').tooltip();

$('#sure').on('click', function () {
  $('#sure').button('submiting').addClass('disabled');

  $.ajax({
    type: 'post',
    url: $('#sure').data('url'),
    data: 'ids=' + ids,
    async: false,
    success: function (data) {

      $('.modal').modal('hide');
      window.location.reload();
    }

  });

});

$('#search').on('click', function () {

  if ($('[name=key]').val() != '') {

    $.post($(this).data('url'), $('.form-search').serialize(), function (data) {

      $('.courses-list').html(data);
      recheckSelectedCourse();
    });
  }

});

$('.courses-list').on('click', '.pagination li', function () {
  var url = $(this).data('url');
  if (typeof (url) !== 'undefined') {
    $.post(url, $('.form-search').serialize(), function (data) {
      $('.courses-list').html(data);

      recheckSelectedCourse();
    });
  }
});

$('#enterSearch').keydown(function (event) {

  // if (event.keyCode == 13) {

  //   $.post($(this).data('url'), $('.form-search').serialize(), function (data) {

  //     $('.courses-list').html(data);

  //   });
  //   return false;
  // }
});

$('#all-courses').on('click', function () {

  $.post($(this).data('url'), $('.form-search').serialize(), function (data) {
    $('.js-enter-search').val('');
    $('.courses-list').html(data);

    recheckSelectedCourse();
  });

});

$('.courses-list').on('change', '.js-course-select', function () {
  var id = $(this).val();
  var sid = $(this).attr('id').split('-')[2];
  for (var i = 0; i < ids.length; i++) {
    var idArr = ids[i].split(':');
    if (idArr[0] == sid) {
      ids[i] = sid + ':' + id;
      break;
    }
  }

  var price = $(this).find(':selected').data('price');
  $('.js-price-' + sid).html(price);
});

$('.courses-list').on('click', '.course-item-cbx', function () {

  var $course = $(this).parent();
  var sid = $course.data('id');//courseSet.id

  if ($course.hasClass('enabled')) {
    return;
  }
  var id = $('#course-select-' + sid).val();
  if ($course.hasClass('select')) {
    $course.removeClass('select');
    // $('.course-metas-'+sid).hide();

    ids = $.grep(ids, function (val, key) {

      if (val != sid + ':' + id)
        return true;
    }, false);
  } else {
    $course.addClass('select');

    // $('.course-metas-'+sid).show();

    ids.push(sid + ':' + id);
  }
});

function recheckSelectedCourse() {
  for (var i = 0; i < ids.length; i++) {
    var idArr = ids[i].split(':');
    var sid = idArr[0];
    var id = idArr[1];
    $('[name=course-' + sid + ']').attr('checked', 'checked');
    $('[data-id=' + sid + ']').addClass('select');
    $('#course-select-' + sid).val(id);
  }
}