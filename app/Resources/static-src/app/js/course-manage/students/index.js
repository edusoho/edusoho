import BatchSelect from 'app/common/widget/batch-select';
new BatchSelect($('#student-table-container'));

class Students {
  constructor() {
    this.initTooltips();
    this.initDeleteActions();
    this.initFollowActions();
    this.initBatchUpdateActions();
  }

  initTooltips() {
    $('#refund-coin-tips').popover({
      html: true,
      trigger: 'hover',//'hover','click'
      placement: 'left',//'bottom',
      content: $('#refund-coin-tips-html').html()
    });
  }

  initDeleteActions() {
    $('body').on('click', '.js-remove-student', function(evt) {
      if (!confirm(Translator.trans('course.manage.student_delete_hint'))) {
        return;
      }
      $.post($(evt.target).data('url'), function (data) {
        if (data.success) {
          cd.message({ type: 'success', message: Translator.trans('member.delete_success_hint') });
          location.reload();
        } else {
          cd.message({ type: 'danger', message: Translator.trans('member.delete_fail_hint') + ':' + data.message });
        }
      });
    });
  }

  initFollowActions() {
    $('#course-student-list').on('click', '.follow-student-btn, .unfollow-student-btn', function () {
      let $this = $(this);
      $.post($this.data('url'), function () {
        $this.hide();
        if ($this.hasClass('follow-student-btn')) {
          $this.parent().find('.unfollow-student-btn').show();
          cd.message({ type: 'success', message: Translator.trans('user.follow_success_hint') });
        } else {
          $this.parent().find('.follow-student-btn').show();
          cd.message({ type: 'success', message: Translator.trans('user.unfollow_success_hint') });
        }
      });

    });
  }

  initBatchUpdateActions() {
    let getSelectIds = function () {
      let ids = [];
      $('#course-student-list').find('[data-role="batch-item"]:checked').each(function () {
        ids.push(this.value);
      });

      return ids;
    };

    $('#student-table-container').on('click', '#batch-update-expiry-day', function () {
      let ids = getSelectIds();
      if (ids.length === 0) {
        cd.message({type: 'danger', message: Translator.trans('course.manage.student.add_expiry_day.select_tips')});
        return;
      }
      $.get($(this).data('url'), {ids: ids}, function (html) {
        $('#modal').html(html).modal('show');
      });
    }).on('click', '#batch-remove', function () {
      let ids = getSelectIds();
      if (ids.length === 0) {
        cd.message({type: 'danger', message: Translator.trans('course.manage.student.batch_remove.select_tips')});
        return;
      }
      if (!confirm(Translator.trans('course.manage.students_delete_hint'))) {
        return;
      }
      $.post($(this).data('url'), {studentIds: ids}, function (resp) {
        if (resp.success) {
          cd.message({ type: 'success', message: Translator.trans('member.delete_success_hint') });
          location.reload();
        } else {
          cd.message({ type: 'danger', message: Translator.trans('member.delete_fail_hint') + ':' + resp.message });
        }
      });
    });
  }
}

new Students();