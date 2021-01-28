define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  // require('../widget/category-select').run('course');
  var CourseSetClone = require('../course-set/clone');
    require('jquery.select2-css');
    require('jquery.select2');
  exports.run = function (options) {

    var csl = new CourseSetClone();

    var $table = $('#course-table');
    $table.on('click', '.cancel-recommend-course', function () {
      $.post($(this).data('url'), function (html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('admin.course.cancel_recommend_success_hint'));
      });
    });

    $table.on('click', '.js-course-set-clone', function () {
      var $this = $(this);
      var courseSetId = ($(this).closest('tr').attr('id')).split('-')[2];
      $.ajax({
        type: 'get',
        url: $this.data('url'),
        success: function(resp) {
          $('#modal').html(resp).modal();
        }
      });
    });

    $table.on('click', '.close-course', function () {
      var user_name = $(this).data('user');
      if (!confirm(Translator.trans('admin.course.close_course_hint'))) return false;
      $.post($(this).data('url'), function (html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('admin.course.close_success_hint'));
      });
    });

    $table.on('click', '.publish-course', function() {
      var studentNum = $(this).closest('tr').next().val();
      if (!confirm(Translator.trans('admin.course.publish_hint'))) return false;
      $.post($(this).data('url'), function(response) {
        if (!response['success'] && response['message']) {
          Notify.danger(response['message']);
        } else {
          var $tr = $(response);
          $table.find('#' + $tr.attr('id')).replaceWith($tr);
          Notify.success(Translator.trans('admin.course.publish_success_hint'));
        }
      }).error(function(e) {
        var res = e.responseJSON.error.message || Translator.trans('admin.course.unknow_error_hint');
        Notify.danger(res);
      });
    });

    $table.on('click', '.delete-course', function() {
      var chapter_name = $(this).data('chapter');
      var part_name = $(this).data('part');
      var user_name = $(this).data('user');
      var $this = $(this);
      if (!confirm(Translator.trans('admin.course.delete_hint')))
        return;
      var $tr = $this.parents('tr');
      $.post($this.data('url'), function(data) {
        if (data.code > 0) {
          Notify.danger(data.message);
        } else if (data.code == 0) {
          $tr.remove();
          Notify.success(data.message);
        } else {
          $('#modal').modal('show').html(data);
        }
      });
    });

    $table.on('click', '.remove-course', function() {
      var $this = $(this);
      if (!confirm(Translator.trans('admin.course.remove_hint')))
        return;
      var $tr = $this.parents('tr');
      $.post($this.data('url'), function(data) {
        if (data.code > 0) {
          Notify.danger(data.message);
        } else if (data.code == 0) {
          $tr.remove();
          Notify.success(data.message);
        } else {
          $('#modal').modal('show').html(data);
        }
      });
    });

    $table.find('.copy-course[data-type="live"]').tooltip();

    $table.on('click', '.copy-course[data-type="live"]', function (e) {
      e.stopPropagation();
    });
      $('[data-toggle="tooltip"]').tooltip();
      $('[data-toggle="popover"]').popover({
          html: true,
          trigger: 'hover'
      });
      var $tagContainer = $('#tag');

      $tagContainer.select2({
          ajax: {
              url: $tagContainer.data('url'),
              dataType: 'json',
              quietMillis: 100,
              data: function (term, page) {
                  return {
                      q: term,
                      page_limit: 10
                  };
              },
              results: function (data) {
                  var results = [];
                  $.each(data, function (index, item) {
                      results.push({
                          id: item.id,
                          name: item.name,
                      });
                  });

                  return {
                      results: results
                  };

              }
          },
          initSelection: function (element, callback) {
              $item = element.data('tagValue');
              callback($item);
          },
          formatSelection: function (item) {
              $('[name=tagId]').val(item.id);
              return item.name;
          },
          formatResult: function (item) {
              return item.name;
          },
          allowClear: true,
          placeholder: Translator.trans('admin.course_manage.manage.tags_select.placeholder')
      });
      $tagContainer.on("change",function(e){
          $('[name=tagId]').val($tagContainer.val());
      });
  };

});
