$('.js-report-submit').on('click', function () {
  let $this = $(this);
  let targetType = $this.data('targetType');
  let targetId = $this.data('targetId');
  let reportUrl = $this.data('url');
  if ($('[name=reportTag]:checked').val() === undefined) {
    cd.message({type: 'warning', message: '至少选择一个举报项'});
    return ;
  }
  $.ajax({
    url: reportUrl,
    type: 'POST',
    async: false,
    dataType: 'json',
    data: {targetType: targetType, targetId: targetId, reportTag: $('[name=reportTag]:checked').val()},
    success: function(res) {
      let contentTarget = $this.data('contentTarget');
      $(`#${contentTarget}`).append('<span style="color: red;">(已举报)</span>');
      cd.message({type: 'success', message: '举报成功'});
      $('.js-review-report').html(
        `<div>
            <h2 class="text-center">你的举报我们已经收到了，会尽快处理……</h2>
            <div class="text-center">
              <a data-dismiss="modal" aria-hidden="true" class="btn btn-info">关闭</a>
            </div>
          </div>
        `);
    },
    error: function(){
    }
  });
});