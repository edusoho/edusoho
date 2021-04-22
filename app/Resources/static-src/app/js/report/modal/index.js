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
      cd.message({type: 'success', message: '举报成功'});
    },
    error: function(){
    }
  });
});