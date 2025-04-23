$('#startDate, #endDate').datetimepicker({
  autoclose: true,
  language: document.documentElement.lang
});

$('#startDate').datetimepicker().on('changeDate', function() {
  $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0,16));
});

$('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0,16));

$('#endDate').datetimepicker().on('changeDate', function() {
  $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0,16));
});

$('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0,16));

$('.course-order-js-export-btn').on('click', async function () {

  const $form = $('#user-search-form');

  // 获取表单内的所有参数并转为对象
  const rawParams = Object.fromEntries(new FormData($form[0]));

  // 添加额外的参数
  rawParams.start = rawParams.start || 0;

  // 过滤掉 undefined 的参数
  const params = Object.fromEntries(
    Object.entries(rawParams).filter(([_, v]) => v !== undefined)
  );
  // 发起请求
  console.log(params);
  try {
    const query = new URLSearchParams(params).toString();
    const verificationResponse = await fetch(`/secondary/verification?exportFileName=courseOrder&targetFormId=${params['courseId']}&${query}`);
    const html = await verificationResponse.text();
    $('modal').empty();
    // 显示 modal
    $('#modal').html(html).modal('show');
  } catch (error) {
    console.error('请求出错:', error);
  }
});
