import notify from 'common/notify';

class Students {
  constructor() {
    this.initTooltips();
    this.initDeleteActions();
    this.initFollowActions();
    this.initExportActions();
  }

  initTooltips(){
    $("#refund-coin-tips").popover({
        html: true,
        trigger: 'hover',//'hover','click'
        placement: 'left',//'bottom',
        content: $("#refund-coin-tips-html").html()
    });
  }

  initDeleteActions(){
    $('body').on('click', '.js-remove-student', function(evt) {
      if (!confirm(Translator.trans('是否确定删除该学员？'))) {
        return;
      }
      $.post($(evt.target).data('url'), function(data) {
        if (data.success) {
          notify('success', '移除成功');
          location.reload();
        } else {
          notify('danger', '移除失败：' + data.message);
        }
      });
    });
  }

  initFollowActions(){
    $("#course-student-list").on('click', '.follow-student-btn, .unfollow-student-btn', function() {
        var $this = $(this);
        $.post($this.data('url'), function(){
            $this.hide();
            if ($this.hasClass('follow-student-btn')) {
                $this.parent().find('.unfollow-student-btn').show();
            } else {
                $this.parent().find('.follow-student-btn').show();
            }
        });
        
    });
  }

  initExportActions(){
    $('#export-students-btn').on('click', function(){
        $(this).button('loading');
        var self = $(this);
        $.get($(this).data('datasUrl'), {start:0}, function(response) {
            if (response.status === 'getData') {
                exportStudents(response.start, response.fileName);
            } else {
                self.button('reset');
                location.href = self.data('url')+'?fileName='+response.fileName;
            }
        });
    });
  }
}

new Students();
