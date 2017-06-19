import notify from 'common/notify';

class Students {
  constructor() {
    this.initTooltips();
    this.initDeleteActions();
    this.initFollowActions();
    this.initExportActions();
    this.initExpiryDayActions();
  }

  initTooltips() {
    $("#refund-coin-tips").popover({
      html: true,
      trigger: 'hover',//'hover','click'
      placement: 'left',//'bottom',
      content: $("#refund-coin-tips-html").html()
    });
  }

  initDeleteActions() {
    $('body').on('click', '.js-remove-student', function (evt) {
      if (!confirm(Translator.trans('是否确定删除该学员？'))) {
        return;
      }
      $.post($(evt.target).data('url'), function (data) {
        if (data.success) {
          notify('success', '移除成功');
          location.reload();
        } else {
          notify('danger', '移除失败：' + data.message);
        }
      });
    });
  }

  initFollowActions() {
    $("#course-student-list").on('click', '.follow-student-btn, .unfollow-student-btn', function () {
      var $this = $(this);
      $.post($this.data('url'), function () {
        $this.hide();
        if ($this.hasClass('follow-student-btn')) {
          $this.parent().find('.unfollow-student-btn').show();
          notify('success', '关注成功');
        } else {
          $this.parent().find('.follow-student-btn').show();
          notify('success', '取消关注成功');
        }
      });

    });
  }

  initExportActions() {
    $('#export-students-btn').on('click',  () =>{
      let $exportBtn = $('#export-students-btn');
      $exportBtn.button('loading');
      $.get($exportBtn.data('datasUrl'), { start: 0 },  (response)=> {
        if (response.status === 'getData') {
          this.exportStudents(response.start, response.fileName);
        } else {
          $exportBtn.button('reset');
          location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
        }
      });
    });
  }


  initExpiryDayActions() {
      $('.js-expiry-days').on('click', () => {
          notify('danger', '只有按天数设置的学习有效期，才可手动增加有效期。');
      });
  }

  exportStudents(start, fileName) {
    var start = start || 0,
      fileName = fileName || '';

    $.get($('#export-students-btn').data('datasUrl'), { start: start, fileName: fileName }, function (response) {
      if (response.status === 'getData') {
        exportStudents(response.start, response.fileName);
      } else {
        $('#export-students-btn').button('reset');
        location.href = $('#export-students-btn').data('url') + '&fileName=' + response.fileName;
      }
    });
  }
}

new Students();
