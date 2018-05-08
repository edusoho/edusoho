define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  require('../widget/category-select').run('course');
  var CourseSetClone = require('../course-set/clone');

  exports.run = function (options) {

    var csl = new CourseSetClone();

    var $table = $('#course-table');
    $table.on('click', '.cancel-recommend-course', function () {
      $.post($(this).data('url'), function (html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('课程推荐已取消！'));
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
      if (!confirm(Translator.trans('您确认要关闭此课程吗？课程关闭后，仍然还在有效期内的学员将可以继续学习。'))) return false;
      $.post($(this).data('url'), function (html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('课程关闭成功！'));
      });
    });

    $table.on('click', '.publish-course', function() {
      var studentNum = $(this).closest('tr').next().val();
      if (!confirm(Translator.trans('您确认要发布此课程吗？'))) return false;
      $.post($(this).data('url'), function(response) {
        if (!response['success'] && response['message']) {
          Notify.danger(response['message']);
        } else {
          var $tr = $(response);
          $table.find('#' + $tr.attr('id')).replaceWith($tr);
          Notify.success(Translator.trans('课程发布成功！'));
        }
      }).error(function(e) {
        var res = e.responseJSON.error.message || '未知错误';
        Notify.danger(res);
      });
    });

    $table.on('click', '.delete-course', function() {
      var chapter_name = $(this).data('chapter');
      var part_name = $(this).data('part');
      var user_name = $(this).data('user');
      var $this = $(this);
      if (!confirm(Translator.trans('删除课程，将删除课程的章节、课时、学员等信息。真的要删除该课程吗？')))
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

    if ($('#course_tags').length > 0) {
      $('#course_tags').select2({
        ajax: {
          url: app.arguments.tagMatchUrl + '#',
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
                id: item.name,
                name: item.name
              });
            });

            return {
              results: results
            };

          }
        },
        initSelection: function (element, callback) {
          var data = [];
          $(element.val().split(',')).each(function () {
            data.push({
              id: this,
              name: this
            });
          });
          callback(data);
        },
        formatSelection: function (item) {
          return item.name;
        },
        formatResult: function (item) {
          return item.name;
        },
        multiple: true,
        maximumSelectionSize: 20,
        placeholder: Translator.trans('请输入标签'),
        width: '162px',
        createSearchChoice: function () {
          return null;
        },
      });
    }
  };

});
