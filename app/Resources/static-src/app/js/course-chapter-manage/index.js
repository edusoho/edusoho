import notify from 'common/notify';

$('#chapter-title-field').on('keypress', function (e) {
  if ((e.keyCode || e.which) === 13) {
    e.preventDefault();
  }
});

let sortList = function ($list) {
  let data = $list.sortable('serialize').get();
  let lessonNum = 0;
  let chapterNum = 0;
  let unitNum = 0;

  $list.find('.task-manage-unit, .task-manage-chapter').each(function () {
    let $item = $(this);
    if ($item.hasClass('item-lesson')) {
      lessonNum++;
      $item.find('.number').text(lessonNum);
    } else if ($item.hasClass('task-manage-unit')) {
      unitNum++;
      $item.find('.number').text(unitNum);
    } else if ($item.hasClass('task-manage-chapter')) {
      chapterNum++;
      unitNum = 0;
      $item.find('.number').text(chapterNum);
    }
  });

  $.post($list.data('sortUrl'), { ids: data }, function (response) {

  });
};


$('#course-chapter-btn').on('click', function () {
  let $this = $(this);
  let _this = this;
  let $form = $('#course-chapter-form');
  let validator = $form.validate({
    rules: {
      title: 'required'
    },
    ajax: true,
    currentDom: $this,
    submitSuccess: function (html) {
      $this.closest('.modal').modal('hide');
      if (!$('.js-task-empty').hasClass('hidden')) {
        $('.js-task-empty').addClass('hidden');
      }
      let $item = $('#' + $(html).attr('id'));

      if ($item.length) {
        $item.replaceWith(html);
        notify('success', Translator.trans('site.save_success_hint'));
      } else {
        let $parent = $('#' + $form.data('parentid'));
        if ($parent.length) {
          let add = 0;
          $parent.nextAll().each(function () {
            if ($(this).hasClass('task-manage-chapter')) {
              $(this).before(html);
              add = 1;
              return false;
            }

          });
          if (add != 1) {
            $('#sortable-list').append(html);
          }
        } else {
          $('#sortable-list').append(html);
        }

        let $list = $('#sortable-list');
        sortList($list);
      }
    },
  });
});